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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

$root = str_replace('/include', '/', str_replace('\\', '/', dirname(__FILE__)));
$root = "$root/../";
require_once $root . 'include/load_config.php';
require_once $cfg_web_root . 'include/auth.inc';
require_once $cfg_web_root . 'include/custom_error_handler.inc';
require_once $cfg_web_root . 'lang/' . $language . '/include/common.php';   // Include common language file that all scripts need

$displayDebug = false; //XML call so debug info messes up the output

if (!isset($_GET['url'])) {
  $action = '';
  $parms = '';
} else {
  if (substr_count($_GET['url'], '/') > 0) {
    list($action, $parms) = explode('/', $_GET['url'], 2);
  } else {
    $action = $_GET['url'];
  }
}
if ($action == 'getModulePaperList') {
  // Force a staff DB connection for getModulePaperList
  
  $mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), 
                                     $configObject->get('cfg_db_staff_user'), 
                                     $configObject->get('cfg_db_staff_passwd'), 
                                     $configObject->get('cfg_db_database'), 
                                     $configObject->get('cfg_db_charset'), 
                                     UserNotices::get_instance(), 
                                     $configObject->get('dbclass'));
   
  $result =  $mysqli->select_db($configObject->get('cfg_db_database'));
  
} else {
  require '../include/staff_student_auth.inc';
}
require './restAPI.class.php';

Class webServiceRestAPI extends restAPI {

  var $db;
  private $qtypes = array(
					'0' => 'Formative Quiz',
					'1' => 'Progress Test',
					'2' => 'Summative Exam',
					'3' => 'Survey (Questionnaire)',
					'4' => 'OSCE Station',
					'5' => 'Offline Paper',
          '6' => 'Peer Review'
					);

  public function __construct($mysqli) {
    $this->db = $mysqli;
    parent::__construct();
  }

	/**
	 * Structures the XML output from the array
	 * @param object $xml - XMLWriter object
	 * @param array $data - The array to be converted to XML
	 * @param array $tmp_tag - Contains the XML tag to be used within the root tag.
	 *
	 */
  function write(XMLWriter $xml, $data, $tmp_tag){
    if (!is_array($data)) {
      return; 
    }
    foreach ($data as $key => $value){
      if (is_array($value)) {
        if (is_numeric($key)) {
          $xml->startElement($tmp_tag);
        } else {
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
      $res = $this->db->prepare("SELECT id FROM users WHERE username = ? AND roles LIKE '%Staff%'");
    } else {
      $res = $this->db->prepare("SELECT id FROM users WHERE username = ? AND roles = 'Student'");
    }
    $res->bind_param('s', $username);
    $res->execute();
    $res->bind_result($tmp_userID);
    $res->fetch();
    $res->close();
    
    return $tmp_userID;  
  }

  public function processRequest() {
    if (!isset($_GET['url'])) {
      $action = '';
      $parms = '';
    } else {
      if ( substr_count($_GET['url'], '/') > 0) {
        list($action, $parms) = explode('/',$_GET['url'],2);
      } else {
        $action = $_GET['url'];
      }
    }
    
    switch($action) {
      case 'getAvailableFeedback':
        // Process URL
        $username = '';
        $module = '';
        $tmp = explode('/', $parms);
        if (isset($tmp[0])) $username = $tmp[0];
        if (isset($tmp[1])) $module = $tmp[1];
        if ($username == '') {
          $this->sendResponse(400, '', '');
        } else {
          // Return the module Available Feedback
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
          $this->data = $this->getOwnerPaperList($username, $types);
          if ($this->data == '') {
            $this->sendResponse(400, '', '');
          } else {
            $this->sendResponse(200, $this->formatData($this->data, 'paperlist', 'paper'), $this->http_accept);
          }
        }
        break;
      case 'getModulePaperList':
        $team = '';
        $types = '';
        $tmp = explode('/', $parms);
        if (isset($tmp[0])) $team = $tmp[0];

        if ($team == '') {
          $this->sendResponse(400, '', '');
        } else {
          $this->data = $this->getModulePaperList($team);

          if ($this->data == '') {
            $this->sendResponse(400, '', '');
          } else {
            $this->sendResponse(200, $this->formatData($this->data, 'paperlist', 'paper'), $this->http_accept);
          }
        }
        break;
      case 'createAccount':
        $this->data = $this->createAccount();
        if ($this->data === 'AccessDenied') {
          $this->sendResponse(401, '', '');
        } elseif ($this->data === false) {
          $this->sendResponse(409, '', '');
        } elseif (!is_numeric($this->data)) {
          $this->sendResponse(200, $this->formatData(array('ERROR' => $this->data), 'user', 'paper'), $this->http_accept);
        } else {
          $this->sendResponse(200, $this->formatData(array('userID' => $this->data), 'user', 'paper'), $this->http_accept);
        }
        break;
			case'getQStatsLastWeek':
				$this->data = $this->getQStatsLastWeek();

				if ($this->data == '') {
					$this->sendResponse(400, '', '');
				} else {
					$this->sendResponse(200, $this->formatData($this->data, 'questionperformance', 'question', 'part'), $this->http_accept);
				}
			  break;
			case 'getQStatsbyPaper':
			  $paperID = null;
        $tmp = explode('/', $parms);
        if (isset($tmp[0])) $paperID = explode(',', $tmp[0]);
				
        if ($paperID == null) {
          $this->sendResponse(400, '', '');
        } else {
          $this->data = $this->getQStatsbyPaper($paperID);

          if ($this->data == '') {
            $this->sendResponse(400, '', '');
          } else {
            $this->sendResponse(200, $this->formatData($this->data, 'questionperformance', 'question', 'part'), $this->http_accept);
          }
        }
			  break;
      default:
        // If we get here the action is unsupported so give a HTTP 405 bad request
        $this->sendResponse(405, '', '');
        break;
    }
  }
	
	public function getQStatsLastWeek() {
      $userObject=UserObject::get_instance();
      if (!$userObject->has_role(array('SysAdmin', 'Admin', 'Staff'))) {
        return '';
      }
	  $papers = array();
	
	  $sql = "SELECT property_id FROM properties WHERE paper_type = '2' AND start_date > SUBDATE(NOW(), INTERVAL 4 WEEK) AND end_date < NOW() AND deleted IS NULL ORDER BY start_date";
		$res = $this->db->prepare($sql);
    $res->execute();
    $res->store_result();
    $res->bind_result($paperID);
    while ($res->fetch()) {
		  $papers[] = $paperID;
		}
		$res->close();
		
		$stats = $this->getQStatsbyPaper($papers);
		
		return $stats;
	}

  public function getQStatsbyPaper($paperID) {
    $userObject=UserObject::get_instance();
    if (!$userObject->has_role(array('SysAdmin', 'Admin', 'Staff'))) {
      return '';
    }
    
	  $stats = array();
		
		$stat_no = 0;
		$old_guid = 0;
			
		$sql = array();
		$bind_types = array();
		$bind_values = array();
		foreach ($paperID as $id) {
		     $sql[] = '?';
		     $bind_types[] = 'i';
		     $bind_values[] = &$id;
		}
        $params = implode(', ', $sql);
        $sql = 'SELECT
            guid, percentage, cohort_size, taken, part_no, p, d, paperID
        FROM
            questions, performance_main, performance_details
        WHERE
            questions.q_id = performance_main.q_id AND
            performance_main.id = performance_details.perform_id AND
            paperID IN (' . $params .')
        ORDER BY questions.q_id, perform_id, part_no';
        $res = $this->db->prepare($sql);
        $bind_types = implode('', $bind_types);
		call_user_func_array(array($res, "bind_param"), array_merge(array($bind_types), $bind_values));
    $res->execute();
    $res->store_result();
    $res->bind_result($guid, $percentage, $cohort_size, $taken, $part_no, $p, $d, $paperID);
		if ($res->num_rows == 0) {
      return json_encode($this->db->error);
    } else {
      while ($res->fetch()) {
			
				if ($old_guid != $guid) {
					$stat_no++;
					$stats[$stat_no]['guid'] = $guid;
					$stats[$stat_no]['paperID'] = $paperID;
					$stats[$stat_no]['percentage'] = $percentage;
					$stats[$stat_no]['cohort_size'] = $cohort_size;
					$stats[$stat_no]['taken'] = $taken;
				}
				
        $stats[$stat_no]['parts']['part' . $part_no] = array('p' => $p/100, 'd' => $d/100);
								
				$old_guid = $guid;
			}
    }
    $res->close();
		
    return $stats;
	}

  public function getModulePaperList($moduleID) {
    global $protocol, $configObject;
    
    $idMod = module_utils::get_idMod($moduleID, $this->db);
    
    $papers = array();
    $paper_no = 0;
    $sql = "SELECT 
            properties.property_id, paper_title, paper_type, start_date, end_date, created, MAX(screen), title, surname, crypt_name 
          FROM 
            properties, papers, users, properties_modules 
          WHERE 
            properties.property_id = properties_modules.property_id AND
            properties.paper_ownerID = users.id AND 
            properties.property_id = papers.paper AND  
            idMod = ? AND 
            paper_type != '2' AND 
            deleted IS NULL AND 
            retired IS NULL 
          GROUP BY 
            property_id 
          ORDER BY 
            paper_title";

    $res = $this->db->prepare($sql);
    $res->bind_param('i', $idMod);
    $res->execute();
    $res->store_result();
    $res->bind_result($property_id, $paper_title, $paper_type, $start_date, $end_date, $created, $screens, $title, $surname, $crypt_name);
    if ($res->num_rows == 0) {
      return json_encode($this->db->error);
    } else {
      while ($res->fetch()) {
        $papers[$paper_no]['id'] = $crypt_name;
        $papers[$paper_no]['title'] = $paper_title;
        $papers[$paper_no]['type'] = $this->qtypes[$paper_type];
        $papers[$paper_no]['staff_url'] = NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/paper/details.php?paperID=' . $property_id;
        $papers[$paper_no]['student_url'] = NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/paper/user_index.php?id=' . $crypt_name;
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


  public function getAvailableFeedback ($username,$moduleID) {
    $allowaccess = false;
    $tmp_userID = $this->getUserID($username, false);
    $userObject=UserObject::get_instance();
    
    if ($userObject->has_role('SysAdmin')) {
      $allowaccess = true;
    } else if ($userObject->has_role('Staff')) {
      $allowaccess = true;
    } else if ($userObject->has_role('Student') and $tmp_userID == $userObject->get_user_ID()) {
      // Students can only list their own feedabck
      $allowaccess = true;
    }
    
    if ( $allowaccess == false ) {
      return '';
    }
    
    if ($moduleID != '') {
      $idMod = module_utils::get_idMod($moduleID, $this->db);
      if ($idMod == false) {
        return "Unknown Module";
      }
    }
    
    $paper_no = 0;
    $old_yearID = -1;
    $papers = array();
    if ($moduleID == '') {
      $sql = "SELECT 
                      paper_id, 
                      date, 
                      UNIX_TIMESTAMP(date) AS is_live, 
                      paper_type, 
                      paper_title, 
                      start_date, 
                      end_date, 
                      properties.calendar_year, 
                      crypt_name, 
                      moduleId 
               FROM feedback_release 
               LEFT JOIN properties ON feedback_release.paper_id = properties.property_id 
               LEFT JOIN properties_modules ON properties.property_id =  properties_modules.property_id 
               LEFT JOIN modules_student ON modules_student.idMod = properties_modules.idMod
               LEFT JOIN modules ON modules.id = properties_modules.idMod 
               WHERE 
                      modules_student.userID=?";
      $res = $this->db->prepare($sql);
      $res->bind_param('i', $tmp_userID);  
    } else {
      $sql = "SELECT 
                      paper_id, 
                      date, 
                      UNIX_TIMESTAMP(date) AS is_live, 
                      paper_type, 
                      paper_title, 
                      start_date, 
                      end_date, 
                      properties.calendar_year, 
                      crypt_name,
                      moduleId  
               FROM feedback_release 
               LEFT JOIN properties ON feedback_release.paper_id = properties.property_id 
               LEFT JOIN properties_modules ON properties.property_id = properties_modules.property_id 
               LEFT JOIN modules_student ON modules_student.idMod = properties_modules.idMod 
               LEFT JOIN modules ON modules.id = properties_modules.idMod 
               WHERE 
                      modules_student.userID=? AND 
                      modules_student.idMod=?";
      $res = $this->db->prepare($sql);
      $res->bind_param('ii', $tmp_userID, $idMod);
    }
    $res->execute();
    $res->store_result();
    $res->bind_result($paperID, $date, $is_live, $paper_type, $paper_title, $start_date, $end_date, $calendar_year, $crypt_name, $moduleID);
    
    while ($res->fetch()) {
      
      if ($is_live < time()) {
        // Have they sat the paper?
        $log = $this->db->prepare("SELECT userID FROM log_metadata WHERE userID=? AND paperID=? LIMIT 1");
        $log->bind_param('ii', $tmp_userID, $paperID);
        $log->execute();
        $log->store_result();
        $log->bind_result($log_userID);

        if ($log->num_rows != 1) {
          $log->close();
          continue;
        } else {
          if ($userObject->has_role('Student')) {
            $papers[$paper_no]['feedback_url'] = 'https://' . $_SERVER['SERVER_NAME'] . '/mapping/user_feedback.php?id=' .  $crypt_name;
          } else {
            $papers[$paper_no]['feedback_url'] = 'https://' . $_SERVER['SERVER_NAME'] . '/mapping/user_feedback.php?id=' .  $crypt_name . '&userID=' . $tmp_userID;
          }
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
    global $configObject;
    
    $allowaccess = false;
    $userObject = UserObject::get_instance();
    $tmp_userID = $this->getUserID($username, true);

    if ($userObject->has_role('SysAdmin') or $userObject->has_role('Admin')) {
      $allowaccess = true;
    } else if ($userObject->has_role('Staff') and $tmp_userID == $userObject->get_user_ID()) {
      $allowaccess = true;
    } else if ($userObject->has_role('Student')) {
      // Students can not access this function
      $allowaccess = false;
    }
    
    if ( $allowaccess == false ) {
      return '';
    }
    
    if ($tmp_userID == '') {
      return '';
    }
    
    $staff_modules = UserUtils::list_staff_modules_by_userID($tmp_userID, $this->db);
    if(count($staff_modules) == 0) {
      // User is not on any teams. stop!!
      return array();
    }
    $staff_modules_ids_str = ' OR idMod IN (' . implode(',',array_keys($staff_modules)) . ') ';
    
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
    $res = $this->db->prepare("SELECT 
                                  properties.property_id, paper_title, paper_type, start_date, end_date, created, MAX(screen), title, surname, crypt_name 
                               FROM properties, papers, users, properties_modules 
                               WHERE 
                                  properties.property_id = properties_modules.property_id AND
                                  properties.paper_ownerID=users.id AND 
                                  properties.property_id=papers.paper AND 
                                  (paper_ownerID=? $staff_modules_ids_str) $typeSQL AND 
                                  deleted IS NULL 
                               GROUP BY property_id ORDER BY paper_title");
    $res->bind_param('i', $tmp_userID);
    $res->execute();
    $res->store_result();
    $res->bind_result($property_id, $paper_title, $paper_type, $start_date, $end_date, $created, $screens, $title, $surname, $crypt_name);
    if ($res->num_rows == 0) {
      return json_encode($this->db->error);
    } else {
      while ($res->fetch()) {
        $papers[$paper_no]['id'] = $crypt_name;
        $papers[$paper_no]['title'] = $paper_title;
        $papers[$paper_no]['type'] = $this->qtypes[$paper_type];
        $papers[$paper_no]['staff_url'] = NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/paper/details.php?paperID=' . $property_id;
        $papers[$paper_no]['student_url'] = NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/paper/user_index.php?id=' . $crypt_name;
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
    $userObject = UserObject::get_instance();
    if (!$userObject->has_role('SysAdmin')) {
      return 'AccessDenied';
    }
    
    if (!isset($_POST['data'])) {
      return 'No data';
    }

    $xml = new SimpleXMLElement($_POST['data']);
    $fields = array('username', 'password', 'firstnames', 'title', 'surname', 'email', 'course', 'gender', 'yearofstudy', 'roles');
    
    foreach ($fields as $field) {
      if (isset($xml->$field) and $xml->$field != '') {
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
    
    $success = UserUtils::create_user($username, $password, $title, $firstnames, $surname, $email, $course, $gender, $yearofstudy, $roles, $studentid, $this->db);
    
    if ($success === false) {
      return false;
    } else {
      return $success;
    }
  }
  
  function __destruct() {
    parent::__destruct();
  }

}

$rest = new webServiceRestAPI($mysqli);
$rest->processRequest();
?>