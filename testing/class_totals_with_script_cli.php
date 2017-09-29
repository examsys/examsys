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
* This script is mimics the actions of class_totals_with_script_ajax.php via the command line.
* We can run this script as a cron job and so not have to remember to go to the site every day to run the test.
*
* @author Joseph Baxter
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

include_once dirname(__DIR__) . '/include/load_config.php';
$language = LangUtils::getLang($cfg_web_root);
require_once 'classes/class_totals.php';

$cfg_web_host = $configObject->get('cfg_web_host');
$support_email = $configObject->get('support_email');
$cfg_cron_user = $configObject->get('cfg_cron_user');
$cfg_cron_passwd = $configObject->get('cfg_cron_passwd');

// DB connection.
$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $configObject->get('cfg_db_sysadmin_user'),
  $configObject->get('cfg_db_sysadmin_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'),
  $configObject->get('notice'), $configObject->get('dbclass'), $configObject->get('cfg_db_port'));

// Exit if not on command line.
if (php_sapi_name() != 'cli') {
  require '../include/sysadmin_auth.inc';
  $msg = sprintf($string['furtherassistance'], $support_email, $support_email);
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['accessdenied'], '/artwork/page_not_found.png', '#C00000', true, true);
}

if ($cfg_cron_user == null or $cfg_cron_passwd == null) {
  echo 'This script requires the cron use to be set-up';
  exit;
}

set_time_limit(0);

// Timestamp function for logging.
function timestamp() {
  $time = microtime(true);
  $microsecond = sprintf("%06d", ($time - floor($time)) * 1000000);
  $datetime = new DateTime( date('Y-m-d H:i:s.'.$microsecond, $time) );

  return $datetime->format("Y-m-d H:i:s.u");
}

echo "\n" . timestamp() . ": Starting class totals check.\n";

// rootpath my be required depending on web server setup.
//$rootpath =  basename(dirname(dirname(__FILE__)));
$rootpath = '';
$userresult = $mysqli->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$userresult->bind_param('s', $cfg_cron_user);
$userresult->execute();
$userresult->bind_result($userid);
$userresult->fetch();
$userresult->close();
$end_dateSQL = 'NOW()';
$start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 DAY)';
$server = 'https://' . $cfg_web_host . '/';
$class_totals = new class_totals();
$configObject->db = $mysqli;
$userObject = new UserObject($configObject, $configObject->db);
$userObject->load($userid);
// Process the papers in range checking the totals.
$class_totals->process_papers($configObject->db, $cfg_cron_user, $cfg_cron_passwd, $rootpath, $userid, $start_dateSQL, $end_dateSQL, $server, $string, $userObject);
// Get any failures.
$status = 'failure';
$testresult = $mysqli->prepare("SELECT user_id, paper_id, errors FROM class_totals_test_local WHERE status = ? and user_id = $userid");
$testresult->bind_param('s', $status);
$testresult->execute();
$testresult->bind_result($user_id, $paper_id, $errors);
// Log and email errors to support.
$message = '';
while ($testresult->fetch()) {
  $errors = strip_tags($errors);
  $message .= 'Failure: user - ' . $user_id . ', paper - ' . $paper_id . ', error - '. $errors . "\n";
}
$testresult->close();
$headers = "From: $support_email\n";
$headers .= "MIME-Version: 1.0\nContent-type: text/plain; charset=UTF-8\n";
$subject = 'Rogo Summative Exam check';
if ($message != '') {
  echo $message;
  $sent = mail($support_email, $subject, $message, $headers);
  if ($sent) {
    echo "Email sent to $support_email";
  }
}
$mysqli->close();
echo "\n" . timestamp() . ": Finishing class totals check.\n";
