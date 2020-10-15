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
 * @return int - 1 = lab requires low bandwidth, 0 = lab is high bandwidth
 */
function check_labs($paper_type, $lab_needed, $address, $pword, $string, $db)
{
    $low_bandwidth = 0;
    $lab_name = null;
    $lab_id = null;
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
function check_paper_password($paperID, $password, $string, $db, $show_form = false)
{
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
 * @param boolean $first_start - If the exam is being started, rather than continued.
 *                               We should not allow new starts after the end time.
 *                               Optional. Default: false.
 */
function check_datetime($start_date, $end_date, $string, $db, $first_start = false)
{
    $notice = UserNotices::get_instance();
    $end_comparison = time();
    if (!$first_start) {
        // If the exam has already been started allow access for 60 minutes after the end of the exam.
        $end_comparison -= 3600;
    }
    // Allow 1 minute before the start time of the assessment.
    if ((time() + 60) < $start_date or $end_comparison > $end_date) {
        $configObject = Config::get_instance();
        $format = $configObject->get('cfg_short_datetime_php');
        $msg = sprintf($string['error_time'], date($format, $start_date), date($format, $end_date));
        $fullmsg = $msg . '<br /><br /><input type="button" name="close" value="' . $string['ok'] . '" onclick="close_window()" class="OK" />';
        $notice->display_notice_and_exit($db, $string['accessdenied'], $fullmsg, $msg, '/artwork/summative_scheduling.png', '#C00000', true, true);
    }
}

/**
 * Check if the paper has been completed
 * @param object $propertyObj paper object
 * @param object $userObj user object
 * @param array $string tranlsations
 * @param object $db database object
 */
function check_finished($propertyObj, $userObj, $string, $db)
{
    $notice = UserNotices::get_instance();
  
    if ($propertyObj->get_paper_type() != '2' and $propertyObj->get_paper_type() != '1') {
        return true;
    }
  
    $userID = $userObj->get_user_ID();
    $paperID = $propertyObj->get_property_id();
  
    $stmt = $db->prepare('SELECT UNIX_TIMESTAMP(completed) FROM log_metadata WHERE userID = ? AND paperID = ?');
    $stmt->bind_param('ii', $userID, $paperID);
    $stmt->execute();
    $stmt->bind_result($completed);
    $stmt->fetch();
    $stmt->close();
  
    if ($completed != '') {
        $configObject = Config::get_instance();
        $format = $configObject->get('cfg_short_datetime_php');
        $msg = sprintf($string['alreadycompleted'], date($format, $completed));
        $fullmsg = $msg . '<br /><br /><input type="button" name="close" value="' . $string['ok'] . '" onclick="close_window()" class="OK" />';
        $notice->display_notice_and_exit($db, $string['accessdenied'], $fullmsg, $msg, '/artwork/square_exclamation_48.png', '#C00000', true, true);
    }
}

function check_staff_modules($moduleID, $userObject)
{
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

/**
 * Check if the user is enrolled on the module
 * @param object $userObj user object
 * @param array $moduleIDs list of modules
 * @param integer $calendar_year year of module enrolment
 * @param array $string tranlsations
 * @param object $db database object
 * @return integer
 */
function check_modules($userObj, $moduleIDs, $calendar_year, $string, $db)
{
    $notice = UserNotices::get_instance();
    $attempt = 1;
    $usern = $userObj->get_username();
    $contactemail = support::get_email();
    if (stripos($usern, 'user') !== 0) {  // Do not check modules for guest accounts (e.g. user1...user100).
        if (count($moduleIDs) > 0) {
            $cal_year_sql = '';
            if ($calendar_year != '') {
                $cal_year_sql = "AND calendar_year = '$calendar_year'";
            }

            $stmt = $db->prepare('SELECT moduleid, attempt FROM modules_student, modules WHERE modules_student.idMod = modules.id AND userID = ? AND idMod IN (' . implode(',', $moduleIDs) . ") $cal_year_sql");
            $stmt->bind_param('i', $userObj->get_user_ID());
            $stmt->execute();
            $stmt->bind_result($moduleid, $attempt);
            $stmt->store_result();
            if ($stmt->num_rows == 0) {
                $moduleslist = '';
                foreach ($moduleIDs as $modID) {
                    $mod_details = module_utils::get_full_details_by_ID($modID, $db);
                    if ($moduleslist == '') {
                         $moduleslist = $mod_details['moduleid'];
                    } else {
                        $moduleslist .= ', ' . $mod_details['moduleid'];
                    }
                }
                 $reason = sprintf($string['notregistered'], $userObj->get_first_names(), $userObj->get_surname(), $userObj->get_username(), $moduleslist);
                 $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
                 $notice->display_notice_and_exit($db, $string['pagenotfound'], $msg, $reason, '../artwork/page_not_found.png', '#C00000', true, true);
            } else {
                  $stmt->fetch();
            }
            $stmt->close();
        } else {
            $reason = sprintf($string['nomodules'], $userObj->get_first_names(), $userObj->get_surname(), $userObj->get_username());
            $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
            $notice->display_notice_and_exit($db, $string['pagenotfound'], $msg, $reason, '../artwork/page_not_found.png', '#C00000', true, true);
        }
    }

    return $attempt;
}

/**
 * Check if the paper metadata security settings are met
 * @param object $property_id paper object
 * @param object $userObj    user object
 * @param array  $moduleIDs  list of modules
 * @param array  $string     tranlsations
 * @param object $db         database object
 */
function check_security_metadata($property_id, $userObj, $moduleIDs, $string, $db)
{
    $contactemail = support::get_email();
    if (!$userObj->is_temporary_account()) {          // Do not check metadata security if temporary account
        $notice = UserNotices::get_instance();
        $metadata = Paper_utils::get_security_metadata($property_id, $db);

        foreach ($metadata as $security_type => $security_value) {
            if (!$userObj->has_metadata($moduleIDs, $security_type, $security_value)) {
                $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
                $reason = sprintf($string['error_metadata'], $userObj->get_first_names(), $userObj->get_surname(), $userObj->get_username(), $security_type, $security_value);
                $notice->display_notice_and_exit($db, $string['pagenotfound'], $msg, $reason, '../artwork/page_not_found.png', '#C00000', true, true);
            }
        }
    }
}

/**
 * Check Safe Exam Browser header strings if enabled and set
 *
 * @param int      $property_id paper object
 * @param object   $userObj     user object
 * @param string[] $string      translations
 * @param \mysqli  $db          database object
 * @return boolean
 */
function check_seb_headers($property_id, $userObj, $string, $db, $exit = true) {
    $properties = PaperProperties::get_paper_properties_by_id($property_id, $db, $string);
    $configObject = Config::get_instance();
    if ($configObject->get_setting('core', 'paper_seb_enabled') and $properties->getSetting('seb_enabled')) {
        $metadata = Paper_utils::get_metadata($db, $property_id, 'seb_hash');

        if (!empty($metadata)) {
            $hashes = [];
            $url = Url::fromGlobals();

            foreach ($metadata['seb_hash'] as $hash) {
                $hashes[] = hash('sha256', $url->getCanonical() . $hash);
            }

            if (empty($_SERVER['HTTP_X_SAFEEXAMBROWSER_CONFIGKEYHASH']) or !in_array($_SERVER['HTTP_X_SAFEEXAMBROWSER_CONFIGKEYHASH'], $hashes)) {
                if ($exit) {
                    // This needs tidying up to ensure errors are correctly shown
                    $notice = UserNotices::get_instance();
                    $msg = sprintf($string['sebblurb'], $userObj->get_first_names(), $userObj->get_surname(), $userObj->get_username());
                    $notice->display_notice_and_exit($db, $string['accessdenied'], $msg, $reason, '../artwork/access_denied.png', '#C00000', true, true);
                } else {
                    return false;
                }
            }
        }
    }
    return true;
}

/**
 * Check current IP address with that of attempt in log.
 * @param integer $paperid paper identifer
 * @param string $current_address
 * @param array $string tranlsations
 * @param object $userObj user object
 * @param object $db database object
 * @param string $papertype the paper type
 */
function check_ipmismatch($paperid, $current_address, $string, $userObj, $db, $papertype)
{
    $log_metadata = new LogMetadata($userObj->get_user_ID(), $paperid, $db);
    $log_metadata->get_record('', false);
    $blurb = $string['ipmismatchblurb'];
    $properties = PaperProperties::get_paper_properties_by_id($paperid, $db, $string);
    if ($properties->getSetting('remote_summative')) {
        $blurb = $string['remoteipmismatchblurb'];
    }
    // Warn user they are logged into mulitple devices in this exam and log them out.
    if (!is_null($log_metadata->get_ipaddress()) and $current_address !== $log_metadata->get_ipaddress()) {
        $msg = sprintf($blurb, $userObj->get_first_names(), $userObj->get_surname(), $userObj->get_username(), $log_metadata->get_ipaddress());
        $notice = UserNotices::get_instance();
        $notice->display_notice_and_exit($db, $string['ipmismatchtitle'], $msg, $msg, '../artwork/page_not_found.png', '#C00000');
    }
}

function render_password_form($string)
{
    $url = $_SERVER['PHP_SELF'];
    if ($_SERVER['QUERY_STRING'] != '') {
        $url .= '?' . $_SERVER['QUERY_STRING'];
    }

    $html = "<form action=\"$url\" method=\"post\" autocomplete=\"off\">";
    $html .= '<p style="margin-left:70px">';
    $html .= '      <input type="password" name="paperpwd" id="paperpwd" /><br />';
    $html .= "      <input type=\"submit\" value=\"{$string['ok']}\" class=\"ok\" style=\"width:100px\" />";
    $html .= '    </p>';
    $html .= '  </form>';
    $html .= '</body>';
    $html .= '</html>';

    return $html;
}
