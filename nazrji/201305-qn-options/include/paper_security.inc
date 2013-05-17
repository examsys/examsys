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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once $cfg_web_root . '/classes/usernotices.class.php';
require_once $cfg_web_root . '/classes/networkutils.class.php';
require_once $cfg_web_root . '/classes/moduleutils.class.php';
require_once $cfg_web_root . '/classes/paperutils.class.php';
require_once $cfg_web_root . '/classes/lab_factory.class.php';
require_once $cfg_web_root . '/classes/lab.class.php';
require_once $cfg_web_root . '/classes/dateutils.class.php';

function check_labs($paper_type, $lab_needed, $address, $pword, $string, $db) {
  $low_bandwidth = 0;
  $lab_name = NULL;
  $lab_id = NULL;
  $notice = UserNotices::get_instance();

  if ($lab_needed != '') {
    $stmt = $db->prepare("SELECT low_bandwidth FROM ip_addresses WHERE address=? AND lab IN ($lab_needed)");
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
    // Exit if a summative exam is on no labs and no password.
    if ($paper_type == '2' and trim($pword) == '') {
      $notice->access_denied($db, $string, $string['specificpassword'], true, true);
    }
  }

  return $low_bandwidth;
}

function check_paper_password($password, $string, $show_form = false) {
  global $mysqli;

  if ($password != '') {
    if (!isset($_COOKIE['paperpwd']) or $password != $_COOKIE['paperpwd']) {
      if ($show_form) {
        if (isset($_POST['paperpwd']) and $_POST['paperpwd'] == $password) {
          @setcookie('paperpwd', $_POST['paperpwd']);
        } else {
          $notice = UserNotices::get_instance();
          $notice->display_notice($string['passwordrequired'], $string['enterpw'], '/artwork/blue_padlock_64.png', '#316AC5', true, true);
          echo render_password_form($string);
          $notice->exit_php();
        }
      } else {
        $notice = UserNotices::get_instance();
        $notice->access_denied($mysqli, $string, $string['specificpassword'], true, true);
      }
    }
  }
}

function check_datetime($start_date, $end_date) {
  global $string, $mysqli;

  $notice = UserNotices::get_instance();

  if ((time()+120) < $start_date or (time()-3600) > $end_date) {
    $tmp_string = sprintf($string['error_time'], date('d/m/Y H:i',$start_date), date('d/m/Y H:i',$end_date));
    $notice->access_denied($mysqli, $string, $tmp_string, true, true);
  }
}

function check_modules($userObj, $moduleIDs, $calendar_year, $db) {
  global $string;

  $notice = UserNotices::get_instance();

  $attempt = 1;
  $usern = $userObj->get_username();

  if (stripos($usern, 'user') !== 0) {
     if (count($moduleIDs) > 0) {
      $cal_year_sql = '';
      if ($calendar_year != '') $cal_year_sql = "AND calendar_year = '$calendar_year'";

      $stmt = $db->prepare("SELECT moduleid, attempt FROM modules_student, modules WHERE modules_student.idMod = modules.id AND userID = ? AND idMod IN (" . implode(',', $moduleIDs) . ") $cal_year_sql");
      $stmt->bind_param('i', $userObj->get_user_ID());
      $stmt->execute();
      $stmt->bind_result($moduleid, $attempt);
      $stmt->store_result();
      if ($stmt->num_rows == 0) {
        if ($calendar_year == '') $calendar_year = date_utils::get_current_academic_year(); //'current year';
        $html = '';
        foreach ($moduleIDs as $modID) {
          $mod_details = module_utils::get_full_details_by_ID($modID, $db);
          if ($html == '') {
            $html = $mod_details['moduleid'];
          } else {
            $html .= ', ' . $mod_details['moduleid'];
          }
        }
        $tmp_string = sprintf($string['notregistered'], $userObj->get_title(), $userObj->get_surname(), $userObj->get_username(), $html, $calendar_year);
        $notice->access_denied($db, $string, $tmp_string, true, true);
      } else {
        $stmt->fetch();
      }
      $stmt->close();
    } else {
      $notice->access_denied($db, $string, $string['error_module'], true, true);
    }
  }

  return $attempt;
}

function check_metadata($property_id, $userObj, $moduleIDs, $string, $db) {
  $notice = UserNotices::get_instance();
  
  $metadata = Paper_utils::get_metadata($property_id, $db);

  foreach ($metadata as $security_type=>$security_value) {
    if (!$userObj->has_metadata($moduleIDs, $security_type, $security_value)) {
      $tmp_string = sprintf($string['error_metadata'], $security_type, $security_value);
      $notice->access_denied($db, $string, $tmp_string, true, true);
    }
  }
}

function render_password_form($string) {
  $url = $_SERVER['PHP_SELF'];
  if ($_SERVER['QUERY_STRING'] != '') {
    $url .= '?' . $_SERVER['QUERY_STRING'];
  }

  $html = "<form action=\"$url\" method=\"post\">";
  $html .= "<p style=\"margin-left:70px\">";
  $html .= "      <input type=\"password\" name=\"paperpwd\" id=\"paperpwd\" /><br />";
  $html .= "      <input type=\"submit\" value=\"{$string['pwcontinue']}\" />";
  $html .= "    </p>";
  $html .= "  </form>";
  $html .= "</body>";
  $html .= "</html>";

  return $html;
}
?>