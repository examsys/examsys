<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/
require $_SERVER['DOCUMENT_ROOT'] . 'touchstone/include/staff_student_auth.inc';
require $_SERVER['DOCUMENT_ROOT'] . 'touchstone/config/config.inc';
require './restAPI.class';

Class TouchStoneRestAPI extends restAPI {

  var $db;

  public function __construct($mysqli) {
    $this->db = $mysqli;
    parent::__construct();
  }


  public function processRequest() {
    //we only output jason from touchstone from now
	$this->http_accept = 'application/json';

    list($action,$parms) = explode('/',$_GET['url'],2);
	switch($action) {
      case 'getAvailableFeedback':
	    //process url
      $username = '';
      $module = '';
	    $tmp = explode('/',$parms);
      if(isset($tmp[0])) $username = $tmp[0];
      if(isset($tmp[1])) $module = $tmp[1];
		if($username == '') {
		  $this->sendResponse(400,'','');
		} else {
	    //return the module Available Feedback
		  $this->data = $this->getAvailableFeedback($username,$module);
		  if($this->data == '') {
		    $this->sendResponse(400,'','');
		  } else {
	        $this->sendResponse(200,json_encode($this->data),$this->http_accept);
		  }
		}
	  break;
	  default:
      case 'getExamInformation':
	    //process url
	    list($paperid) = explode('/',$parms);
		if($username == '') {
		  $this->sendResponse(400,'','');
		} else {
	      //return the module Available Feedback
		  $this->data = $this->getExamInformation($paperid);
		  if($this->data == '') {
		    $this->sendResponse(400,'','');
		  } else {
	        $this->sendResponse(200,json_encode($this->data),$this->http_accept);
		  }
		}
	  break;
	    //if we get here the action is unsupported so give a http 400 bad request
	    $this->sendResponse(405,'','');
	  break;
	}
  }

  public function getAvailableFeedback ($username,$moduleID) {
	$qtypes = array(
					'0' => 'Formative Self-Assessment',
					'1' => 'Progress Test',
					'2' => 'Summative Exam',
					'3' => 'Survey (Questionnaire)',
					'4' => 'OSCE',
					'5' => 'Offline'
					);

    $sql = "SELECT id FROM users WHERE username='$username'";
    $res = $this->db->query($sql);
    $row = $res->fetch_assoc();
    $tmp_userID = $row['id'];

    $moduleID = '%' . $moduleID . '%';
    $sql = "SELECT student_modules.moduleID,properties.calendar_year,paper_id,date,UNIX_TIMESTAMP(date) AS is_live,paper_type,paper_title,start_date,end_date,properties.calendar_year FROM feedback_release LEFT JOIN properties ON feedback_release.paper_id = properties.property_id LEFT JOIN student_modules ON properties.moduleID LIKE CONCAT('%',student_modules.moduleID,'%') AND properties.calendar_year = student_modules.calendar_year WHERE userID=$tmp_userID AND properties.moduleID LIKE '$moduleID'";
    $res = $this->db->query($sql);

    $i = 0;
    $old_yearID = -1;
    $papers = Array();
    while($row = $res->fetch_assoc()) {

      if($row['is_live'] < time()) {
        //have they sat the paper?
        $sql = "SELECT userID FROM log" . $row['paper_type'] ." WHERE userID=$tmp_userID AND q_paper = " . $row['paper_id'] . ' LIMIT 1';
        $tmp[] = $sql;
        $log = $this->db->query($sql);
        if($log->num_rows != 1) {
          $log->close();
          continue;
        } else {
          $papers[$row['calendar_year']][$row['moduleID']][$i]['feedback_url'] = 'https://' . $_SERVER['SERVER_NAME'] . '/touchstone/mapping/user_feedback.php?paperID=' .  $row['paper_id'] . '&userID=' . $tmp_userID;
          $log->close();
        }

      } else {
		$papers[$row['calendar_year']][$row['moduleID']][$i]['feedback_url'] = '';
      }

	  $papers[$row['calendar_year']][$row['moduleID']][$i]['calendar_year'] = $row['calendar_year'];
	  $papers[$row['calendar_year']][$row['moduleID']][$i]['release_date'] = $row['date'];
	  $papers[$row['calendar_year']][$row['moduleID']][$i]['paper_title'] = $row['paper_title'];
	  $papers[$row['calendar_year']][$row['moduleID']][$i]['start_date'] = $row['start_date'];
	  $papers[$row['calendar_year']][$row['moduleID']][$i]['paper_type'] = $qtypes[$row['paper_type']];

      $i++;

	  $old_yearID = $row['calendar_year'] . $row['moduleID'];
    }
    $res->close();

    return $papers;
  }

  public function getExamInformation($paperID) {

    $qtypes = array(
					'0' => 'Formative Self-Assessment',
					'1' => 'Progress Test',
					'2' => 'Summative Exam',
					'3' => 'Survey (Questionnaire)',
					'4' => 'OSCE',
					'5' => 'Offline'
					);

    $sql = "SELECT moduleID,calendar_year,paper_type,paper_title,start_date FROM properties WHERE property_id='$paperID'";
    $res = $this->db->query($sql);

    if(!$res) {
      return json_encode($this->db->error);
    }
    $paper = Array();
    while($row = $res->fetch_assoc()) {
      $paper['feedback_url'] = 'https://' . $_SERVER['SERVER_NAME'] . '/touchstone/mapping/user_feedback.php?paperID=' .  $paperID;
      $paper['calendar_year'] = $row['calendar_year'];
      $paper['paper_title'] = $row['paper_title'];
      $paper['paper_type'] = $qtypes[$row['paper_type']];
      $paper['start_date'] = $row['start_date'];
    }
    $res->close();

    return $paper;
  }

  function __destruct() {
    parent::__destruct();
  }

}

$mysqli = new mysqli($cfg_db_host , $cfg_db_username, $cfg_db_passwd, $cfg_db_database);
$rest = new TouchStoneRestAPI($mysqli);
$rest->processRequest();
?>