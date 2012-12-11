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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

function check_whether_student_has_finished( LogMetadata $log_metadata ){
  global $string;

  $has_finished_paper = $log_metadata->is_users_paper_completed();

  if( $has_finished_paper ){

    $notice = UserNotices::get_instance();

    $notice->display_access_denied( $string['paper_completed'], $output_header = false);

  }

}

function check_labs($paper_type, $labs, $pword, $mysqlidb, $notice = NULL) {
  global $string;

  if(is_null($notice)) {
    $notice = UserNotices::get_instance();
  }

  $low_bandwidth = 0;

  if ($labs != '') {
    $stmt = $mysqlidb->prepare("SELECT low_bandwidth FROM ip_addresses WHERE address=? AND lab IN ($labs)");
    $stmt->bind_param('s', $_SERVER['REMOTE_ADDR']);
    $stmt->execute();
    $stmt->bind_result($low_bandwidth);
    $stmt->store_result();
    $stmt->fetch();
    if ($stmt->num_rows == 0) {
      $notice->display_access_denied($string['denied_location'], $output_header = false);
    }
    $stmt->close();
  } else {
    // Exit if a summative exam is on no labs and no password.
    if ($paper_type == '2' and trim($pword) == '') exit;
  }

  return $low_bandwidth;
}

function check_paper_password($password, $show_form=false, $notice = NULL) {
  global $string;

  if(is_null($notice)) {
    $notice = UserNotices::get_instance();
  }

  if ($password != '') {
    if (!isset($_COOKIE['paperpwd']) or $password != $_COOKIE['paperpwd']) {
      if ($show_form) {
        if (isset($_POST['paperpwd']) and $_POST['paperpwd'] == $password) {
          setcookie('paperpwd', $_POST['paperpwd']);
        } else {
          UserNotices::display_notice($string['passwordrequired'], '', '/artwork/key_48.png', '#C00000');

          $html = render_password_form($string);

          echo $html;
          exit;
        }
      } else {
        $notice->display_access_denied($string['specificpassword'], false);
      }
    }
  }
}

function check_datetime($start_date, $end_date, $notice = NULL) {
  global $string;

  if(is_null($notice)) {
    $notice = UserNotices::get_instance();
  }

  if ((time()+120) < $start_date or (time()-3600) > $end_date) {
    $tmp_string = sprintf($string['error_time'], date('d/m/Y H:i',$start_date), date('d/m/Y H:i',$end_date));
    $notice->display_access_denied($tmp_string, $output_header = false);
  }
}

function check_modules($userObj, $moduleIDs, $calendar_year, $db, $notice = NULL) {
  global $string;

  if(is_null($notice)) {
    $notice = UserNotices::get_instance();
  }

  $attempt = 1;
  $usern = $userObj->get_username();
  if (isset($_SERVER['PHP_AUTH_USER'])) {
    $usern = $_SERVER['PHP_AUTH_USER'];
  }

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
        if ($calendar_year == '') $calendar_year = 'current year';
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
        $notice->display_access_denied($tmp_string, $output_header = false);
      } else {
        $stmt->fetch();
      }
      $stmt->close();
    } else {
      $notice->display_access_denied($string['error_module'], false);
    }
  }

  return $attempt;
}

// Needs fixing
function check_metadata($property_id, $userObj, $moduleIDs, $db, $notice = NULL) {
  global $string;

  if(is_null($notice)) {
    $notice = UserNotices::get_instance();
  }

  $stmt = $db->prepare("SELECT name, value FROM paper_metadata_security WHERE paperID = ?");
  $stmt->bind_param('i', $property_id);
  $stmt->execute();
  $stmt->bind_result($security_type, $security_value);
  $stmt->store_result();
  while ($stmt->fetch()) {
    $check_security = $db->prepare("SELECT users_metadata.userID FROM users_metadata, modules WHERE users_metadata.moduleid = modules.id AND modules.id IN (" . implode(',', $moduleIDs) . ") AND userID = ? AND type = ? AND value=?");
    $check_security->bind_param('iss', $userObj->get_user_ID(), $security_type, $security_value);
    $check_security->execute();
    $check_security->store_result();
    if ($check_security->num_rows == 0) {
      $tmp_string = sprintf($string['error_metadata'], $security_type, $security_value);
      $notice->display_access_denied($tmp_string, $output_header = false);
    }
    $check_security->close();
  }
  $stmt->close();
}

function render_password_form($string) {
  $url = $_SERVER['PHP_SELF'];
  if ($_SERVER['QUERY_STRING'] != '') {
    $url .= '?' . $_SERVER['QUERY_STRING'];
  }

  /*$html = <<< FORM
    <form action="{$url}" method="post">
      <p style="margin-left:60px">
        <label for="paperpwd">{$string['enterpw']}</label><br />
        <input type="password" name="paperpwd" id="paperpwd" /><br />
        <input type="submit" value="{$string['pwcontinue']}" />
      </p>
    </form>
  </body>
</html>
FORM;*/

  return $html;
}
?>