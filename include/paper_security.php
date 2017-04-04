<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* Collection of functions which handle the different aspects of paper security.
* Used in start.php, finish.php, save_screen.php and fire_evacuation.php amongst
* others.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

/**
 * Check that the current IP address of the user is in one of the allowed
 * computer labs.
 * @param string $paper_type  - Type of the assessment (e.g. 0 = Formative, 1 = Progress Test, etc).
 * @param string $lab_needed  - The IDs of the labs assigned to the current paper.
 * @param string $address     - The IP Address/Client Name to be checked against the labs
 * @param string $pword       - The password set on the assessment (optional)
 * @param array $string       - Language translation strings.
 * @param object $db          - The MySQL connection.
 * 
 * @return int	- 1 = lab requires low bandwidth, 0 = lab is high bandwidth
 */
function check_labs($paper_type, $lab_needed, $address, $pword, $string, $db) {
  $low_bandwidth = 0;
  $lab_name = NULL;
  $lab_id = NULL;
  $notice = UserNotices::get_instance();

  if ($lab_needed != '') {
    $stmt = $db->prepare("SELECT low_bandwidth FROM client_identifiers WHERE address = ? AND lab IN ($lab_needed)");
    $stmt->bind_param('s', $address);
    $stmt->execute();
    $stmt->bind_result($low_bandwidth);
    $stmt->store_result();
    $stmt->fetch();
    if ($stmt->num_rows == 0) {
      $notice->access_denied($db, $string, $string['denied_location'], true, true);
    }
    $stmt->close();
  } else {
    // Exit if a summative exam is on no labs and no password. There has to be some for of security.
    if ($paper_type == '2' and trim($pword) == '') {
      $notice->access_denied($db, $string, $string['specificpassword'], true, true);
    }
  }

  return $low_bandwidth;
}

/**
 * Check if there is a password on the assessment and if so will work out whether it needs
 * to show the password entry form.
 * @param int $paperID      - Paper id.
 * @param string $password  - Type of the assessment (e.g. 0 = Formative, 1 = Progress Test, etc).
 * @param array $string     - Language translation strings.
 * @param bool $show_form   - If true then it will display a form for the student to enter the password.
 *                            Files like user_index.php can ask the student for a password by start.php
 *                            and finish.php should not ask, password should already be set.
 * @param object $db        - The MySQL connection.
 */
function check_paper_password($paperID, $password, $string, $db, $show_form = false) {
  if ($password != '') {
    if (!isset($_SESSION['paperpwd']) or $password != $_SESSION['paperpwd']) {
      if ($show_form) {
        $paperproperties = new PaperProperties($db);
        $decrypt_password = preg_replace("/^$paperID/", '', $paperproperties->decrypt_password($password));
        if (isset($_POST['paperpwd']) and $_POST['paperpwd'] == $decrypt_password) {
          $_SESSION['paperpwd'] = $password;
        } else {
          $notice = UserNotices::get_instance();
          $notice->display_notice($string['passwordrequired'], $string['enterpw'], '/artwork/fingerprint_48.png', '#C00000', true, true);
          echo render_password_form($string);
          $notice->exit_php();
        }
      } else {
        $notice = UserNotices::get_instance();
        $notice->access_denied($db, $string, $string['specificpassword'], true, true);
      }
    }
  }
}

/**
 * Check if the assessment can be taken at the current time. This time window is relaxed: 1 min
 * before the set start time and 60 mins after the end.
 * @param string $start_date  - Start date/time of the assessment.
 * @param string $end_date    - End date/time of the assessment.
 * @param array $string       - Language translation strings.
 * @param object $db          - The MySQL connection.
 */
function check_datetime($start_date, $end_date, $string, $db) {
  $notice = UserNotices::get_instance();

  // Allow 1 minute before the start time of the assessment.
  // Allow 60 minutes after the end time of the assessment.
  if ((time()+60) < $start_date or (time()-3600) > $end_date) {
    $msg = sprintf($string['error_time'], date('d/m/Y H:i',$start_date), date('d/m/Y H:i',$end_date));
    $fullmsg = $msg . '<br /><br /><input type="button" name="close" value="' . $string['ok'] . '" onclick="window.close()" class="OK" />';
    $notice->display_notice_and_exit($db, $string['accessdenied'], $fullmsg, $msg, '/artwork/summative_scheduling.png', '#C00000', true, true);
  }
}

function check_finished($propertyObj, $userObj, $string, $db) {
  $notice = UserNotices::get_instance();
  
  if ($propertyObj->get_paper_type() != '2' and $propertyObj->get_paper_type() != '1') {
    return true;
  }
  
  $userID = $userObj->get_user_ID();
  $paperID = $propertyObj->get_property_id();
  
  $stmt = $db->prepare("SELECT UNIX_TIMESTAMP(completed) FROM log_metadata WHERE userID = ? AND paperID = ?");
  $stmt->bind_param('ii', $userID, $paperID);
  $stmt->execute();
  $stmt->bind_result($completed);
  $stmt->fetch();
  $stmt->close();
  
  if ($completed != '') {
    $msg = sprintf($string['alreadycompleted'], date('d/m/Y H:i', $completed));
    $fullmsg = $msg . '<br /><br /><input type="button" name="close" value="' . $string['ok'] . '" onclick="window.close()" class="OK" />';
    $notice->display_notice_and_exit($db, $string['accessdenied'], $fullmsg, $msg, '/artwork/square_exclamation_48.png', '#C00000', true, true);
  }
  
}

function check_staff_modules($moduleID, $userObject) {
  $on_module = false;
  $modIDs = array_keys($moduleID);
  
  $staff_mods = $userObject->get_staff_accessable_modules();
    
  foreach ($modIDs as $modID) {
    if (isset($staff_mods[$modID])) {
      $on_module = true;
      break;
    }
  }
  return $on_module;
}

function check_modules($userObj, $moduleIDs, $calendar_year, $string, $db) {
  $notice = UserNotices::get_instance();
  $configObject = Config::get_instance();
  $yearutils = new yearutils($db);
  $attempt = 1;
  $usern = $userObj->get_username();

  if (stripos($usern, 'user') !== 0) {  // Do not check modules for guest accounts (e.g. user1...user100).
     if (count($moduleIDs) > 0) {
      $cal_year_sql = '';
      if ($calendar_year != '') $cal_year_sql = "AND calendar_year = '$calendar_year'";

      $stmt = $db->prepare("SELECT moduleid, attempt FROM modules_student, modules WHERE modules_student.idMod = modules.id AND userID = ? AND idMod IN (" . implode(',', $moduleIDs) . ") $cal_year_sql");
      $stmt->bind_param('i', $userObj->get_user_ID());
      $stmt->execute();
      $stmt->bind_result($moduleid, $attempt);
      $stmt->store_result();
      if ($stmt->num_rows == 0) {
        if ($calendar_year == '') $calendar_year = $yearutils->get_current_session(); //'current year';
        $html = '';
        foreach ($moduleIDs as $modID) {
          $mod_details = module_utils::get_full_details_by_ID($modID, $db);
          if ($html == '') {
            $html = $mod_details['moduleid'];
          } else {
            $html .= ', ' . $mod_details['moduleid'];
          }
        }
        $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
        $notice->display_notice_and_exit($db, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
      } else {
        $stmt->fetch();
      }
      $stmt->close();
    } else {
      $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
      $notice->display_notice_and_exit($db, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
    }
  }

  return $attempt;
}

function check_metadata($property_id, $userObj, $moduleIDs, $string, $db) {
	if (!$userObj->is_temporary_account()) {			// Do not check metadata security if temporary account
		$notice = UserNotices::get_instance();
		$configObject = Config::get_instance();
		$metadata = Paper_utils::get_metadata($property_id, $db);

		foreach ($metadata as $security_type=>$security_value) {
			if (!$userObj->has_metadata($moduleIDs, $security_type, $security_value)) {
				$tmp_string = sprintf($string['error_metadata'], $security_type, $security_value);
				$msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
				$notice->display_notice_and_exit($db, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
			}
		}
	}
}

function render_password_form($string) {
  $url = $_SERVER['PHP_SELF'];
  if ($_SERVER['QUERY_STRING'] != '') {
    $url .= '?' . $_SERVER['QUERY_STRING'];
  }

  $html = "<form action=\"$url\" method=\"post\" autocomplete=\"off\">";
  $html .= "<p style=\"margin-left:70px\">";
  $html .= "      <input type=\"password\" name=\"paperpwd\" id=\"paperpwd\" /><br />";
  $html .= "      <input type=\"submit\" value=\"{$string['ok']}\" class=\"ok\" style=\"width:100px\" />";
  $html .= "    </p>";
  $html .= "  </form>";
  $html .= "</body>";
  $html .= "</html>";

  return $html;
}
?>