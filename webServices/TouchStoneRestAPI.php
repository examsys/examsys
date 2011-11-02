<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* @author Anthony Brown, Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/
$root = (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'] . '/';
require $root . 'include/staff_student_auth.inc';
require_once $root . 'config/config.inc';
require_once $root . 'classes/userutils.class.php';
require './restAPI.class';

Class TouchStoneRestAPI extends restAPI {

  var $db;
  private $qtypes = array(
					'0' => 'Formative Quiz',
					'1' => 'Progress Test',
					'2' => 'Summative Exam',
					'3' => 'Survey (Questionnaire)',
					'4' => 'OSCE Station',
					'5' => 'Offline Paper'
					);

  public function __construct($mysqli) {
    $this->db = $mysqli;
    parent::__construct();
  }

  function write(XMLWriter $xml, $data, $tmp_tag){
    foreach($data as $key => $value){
      if (is_array($value)){
        if (is_numeric($key)) {
          $xml->startElement($tmp_tag);
        } else {
          echo $key . '<br />';
          $xml->startElement($key);
        }
        $this->write($xml, $value, $tmp_tag);
        $xml->endElement();
        continue;
      }
      $xml->writeElement($key, $value);
    }
  }
      
  public function formatData($data, $root_tag, $tmp_tag) {
    if ($this->http_accept == 'json') {
      $data = json_encode($data);
    } else {
      //$data = array_flip($data);
      $xml = new XmlWriter();
      $xml->openMemory();
      $xml->startDocument('1.0', 'UTF-8');
      $xml->startElement($root_tag);

      $this->write($xml, $data, $tmp_tag);

      $xml->endElement();
      $data = $xml->outputMemory(true);
    }
    
    return $data;
  }
  
  public function getUserID($username, $staff = false) {
    if ($staff == true) {
      $res = $this->db->prepare("SELECT id FROM users WHERE username=? AND roles LIKE 'Staff%'");
    } else {
      $res = $this->db->prepare("SELECT id FROM users WHERE username=? AND roles = 'Student'");
    }
    $res->bind_param('s', $username);
    $res->execute();
    $res->bind_result($tmp_userID);
    $res->fetch();
    $res->close();
    
    return $tmp_userID;  
  }

  public function processRequest() {
    if (substr_count($_GET['url'], '/') > 0) {
      list($action, $parms) = explode('/',$_GET['url'],2);
    } else {
      $action = $_GET['url'];
    }
    switch($action) {
      case 'getAvailableFeedback':
        //process url
        $username = '';
        $module = '';
        $tmp = explode('/', $parms);
        if (isset($tmp[0])) $username = $tmp[0];
        if (isset($tmp[1])) $module = $tmp[1];
        if ($username == '') {
          $this->sendResponse(400, '', '');
        } else {
          //return the module Available Feedback
          $this->data = $this->getAvailableFeedback($username, $module);
          if ($this->data == '') {
            $this->sendResponse(400, '', '');
          } else {
            $this->sendResponse(200, $this->formatData($this->data, 'feedbacklist', 'paper'), $this->http_accept);
          }
        }
        break;
      case 'getOwnerPaperList':
        $username = '';
        $types = '';
        $tmp = explode('/', $parms);
        if (isset($tmp[0])) $username = $tmp[0];
        if (isset($tmp[1])) $types = $tmp[1];
        
        if ($username == '') {
          $this->sendResponse(400, '', '');
        } else {
          //return the module Available Feedback
          $this->data = $this->getOwnerPaperList($username, $types);
          if ($this->data == '') {
            $this->sendResponse(400, '', '');
          } else {
            $this->sendResponse(200, $this->formatData($this->data, 'paperlist', 'paper'), $this->http_accept);
          }
        }
        break;
      case 'createAccount':
        $this->data = $this->createAccount();
        if ($this->data === 'accessdenied') {
          $this->sendResponse(401, '', '');
        } elseif ($this->data === false) {
          $this->sendResponse(400, '', '');
        } else {
          $this->sendResponse(200, $this->formatData($this->data, 'user', 'paper'), $this->http_accept);
        }
        break;
      default:
        //if we get here the action is unsupported so give a http 400 bad request
        $this->sendResponse(405, '', '');
        break;
    }
  }

  public function getAvailableFeedback ($username,$moduleID) {
    $tmp_userID = $this->getUserID($username);
    
    $paper_no = 0;
    $old_yearID = -1;
    $papers = array();

    $moduleID = '%' . $moduleID . '%';
    $sql = "SELECT student_modules.moduleID, paper_id, date, UNIX_TIMESTAMP(date) AS is_live, paper_type, paper_title, start_date, end_date, properties.calendar_year FROM feedback_release LEFT JOIN properties ON feedback_release.paper_id = properties.property_id LEFT JOIN student_modules ON properties.moduleID LIKE CONCAT('%',student_modules.moduleID,'%') AND properties.calendar_year = student_modules.calendar_year WHERE userID=? AND properties.moduleID LIKE '$moduleID'";
    $res = $this->db->prepare($sql);
    $res->bind_param('i', $tmp_userID);
    $res->execute();
    $res->store_result();
    $res->bind_result($moduleID, $paperID, $date, $is_live, $paper_type, $paper_title, $start_date, $end_date, $calendar_year);
    while ($res->fetch()) {

      if ($is_live < time()) {
        //have they sat the paper?
        $log = $this->db->prepare("SELECT userID FROM log_metadata WHERE userID=? AND paperID=? LIMIT 1");
        $log->bind_param('ii', $tmp_userID, $paperID);
        $log->execute();
        $log->store_result();
        $log->bind_result($log_userID);

        if ($log->num_rows != 1) {
          $log->close();
          continue;
        } else {
          $papers[$paper_no]['feedback_url'] = 'https://' . $_SERVER['SERVER_NAME'] . '/mapping/user_feedback.php?paperID=' .  $paperID . '&userID=' . $tmp_userID;
          $log->close();
        }
      } else {
        $papers[$paper_no]['feedback_url'] = '';
      }
      
      $papers[$paper_no]['title'] = $paper_title;
      $papers[$paper_no]['type'] = $this->qtypes[$paper_type];
      $papers[$paper_no]['start_date'] = $start_date;
      $papers[$paper_no]['release_date'] = $date;
      $papers[$paper_no]['calendar_year'] = $calendar_year;
      $papers[$paper_no]['moduleID'] = $moduleID;

      $paper_no++;

    }
    $res->close();
    
    return $papers;
  }

  public function getOwnerPaperList($username, $types) {
    global $protocol;
        
    $tmp_userID = $this->getUserID($username, true);
    if ($tmp_userID == '') {
      return '';
    }
    
    $teams = getUserTeams($tmp_userID, $this->db);
    
    // Get the papers for the current owner
    $moduleSQL = '';
    if (count($teams) > 0) {
      foreach ($teams as $team) {
        $moduleSQL .= " OR moduleID LIKE '%$team%'";
      }
    }
    
    switch($types) {
      case 'formative':
        $typeSQL = " AND paper_type='0'";
        break;
      case 'progresstest':
        $typeSQL = " AND paper_type='1'";
        break;
      case 'summative':
        $typeSQL = " AND paper_type='2'";
        break;
      case 'survey':
        $typeSQL = " AND paper_type='3'";
        break;
      case 'osce':
        $typeSQL = " AND paper_type='4'";
        break;
      case 'offline':
        $typeSQL = " AND paper_type='5'";
        break;
      case 'notsummative':
        $typeSQL = " AND paper_type!='2'";
        break;
      default:  // return all paper types
        $typeSQL = '';
        break;
    }
    
    $papers = array();
    $paper_no = 0;
    $res = $this->db->prepare("SELECT property_id, paper_title, paper_type, start_date, end_date, created, MAX(screen), title, surname, crypt_name FROM properties, papers, users WHERE properties.paper_ownerID=users.id AND properties.property_id=papers.paper AND (paper_ownerID=? $moduleSQL)$typeSQL AND deleted IS NULL GROUP BY property_id ORDER BY paper_title");
    $res->bind_param('i', $tmp_userID);
    $res->execute();
    $res->store_result();
    $res->bind_result($property_id, $paper_title, $paper_type, $start_date, $end_date, $created, $screens, $title, $surname, $crypt_name);
    if ($res->num_rows == 0) {
      return json_encode($this->db->error);
    } else {
      while($res->fetch()) {
        $papers[$paper_no]['id'] = $crypt_name;
        $papers[$paper_no]['title'] = $paper_title;
        $papers[$paper_no]['type'] = $this->qtypes[$paper_type];
        $papers[$paper_no]['staff_url'] = $protocol . $_SERVER['HTTP_HOST'] . '/paper/details.php?paperID=' . $property_id;
        $papers[$paper_no]['student_url'] = $protocol . $_SERVER['HTTP_HOST'] . '/user_index.php?paperID=' . $property_id;
        $papers[$paper_no]['start_date'] = $start_date;
        $papers[$paper_no]['end_date'] = $end_date;
        $papers[$paper_no]['created'] = $created;
        $papers[$paper_no]['screens'] = $screens;
        $papers[$paper_no]['owner'] = $title . ' ' . $surname;
        $paper_no++;
      }
    }
    $res->close();
    
    return $papers;
  }

  public function createAccount() {
    global $userroles;
    
    if (strpos($userroles,'SysAdmin') === false) {
      return 'accessdenied';
    }
    
    $xml = new SimpleXMLElement($_POST['data']);
    $fields = array('username', 'password', 'firstnames', 'title', 'surname', 'email', 'course', 'gender', 'yearofstudy', 'roles');
    
    foreach ($fields as $field) {
      if (isset($xml->$field)) {
        $$field = $xml->$field;
      } else {
        return 'Missing data: ' . $field;
      }
    }
    
    if (isset($xml->studentid)) {
      $studentid = $xml->studentid;
    } else {
      $studentid = '';
    }
    if ($roles != 'Student' and $roles != 'Staff' and $roles != 'Staff,Admin' and $roles != 'Staff,SysAdmin') {
      return 'Incorrect value for roles: ' . $roles;
    }
    
    $success = UserUtils::createUser($username, $password, $title, $firstnames, $surname, $email, $course, $gender, $yearofstudy, $roles, $studentid, $this->db);
    
    if (!$success) {
      return false;
    } else {
      return true;
    }
  }
  
  function __destruct() {
    parent::__destruct();
  }

}

$mysqli = new mysqli($cfg_db_host , $cfg_db_username, $cfg_db_passwd, $cfg_db_database);
$rest = new TouchStoneRestAPI($mysqli);
$rest->processRequest();
?>