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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$metadataID = check_var('metadataID', 'GET', true, false, true);
$userID     = check_var('userID', 'GET', true, false, true);
$paperID    = check_var('paperID', 'GET', true, false, true);

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
if ($properties->get_paper_type() != '1') {   // Only allow timer reset of Progress Test papers.
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$log_metadata = new LogMetadata($userID, $paperID, $mysqli);

if ($log_metadata->get_record($metadataID) === false) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
$dateObj = $log_metadata->get_start_datetime();

$user_details = UserUtils::get_user_details($userID, $mysqli);
if ($user_details === false) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$name = $user_details['title'] . ' ' . $user_details['surname'] . ', ' . $user_details['first_names'] . ' (' . $user_details['student_id'] . ')';

$display_format = $configObject->get('cfg_long_date_php') . ' ' . $configObject->get('cfg_short_time_php');

$started = $dateObj->format($display_format);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['resettimer'] ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />
  <style>
    body {background-image: url('../artwork/reset_timer_48.png')}
  </style>
</head>

<body>

<p><?php echo $string['msg1']; ?>
<p><?php echo $name . ' - ' . $started; ?></p>
<br />

<div style="text-align:right">
<form action="do_reset_timer.php" method="post" autocomplete="off">
<input type="hidden" name="metadataID" value="<?php echo $metadataID; ?>" />
<input type="hidden" name="userID" value="<?php echo $userID; ?>" />
<input type="hidden" name="paperID" value="<?php echo $paperID; ?>" />
<input class="ok" type="submit" name="submit" value="<?php echo $string['reset'] ?>" /><input class="cancel" type="button" name="cancel" value="<?php echo $string['cancel'] ?>" onclick="window.close();" />
</form>
</div>


</body>
</html>
<?php
$mysqli->close();
?>