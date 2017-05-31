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
* This script can only be called from a paper in 'summative' mode from one of the four green fire exit icons displayed in 'start.php'.
*  It does three main things:
*        1) record the current screen data to the 'log' table,
*        2) blank the screen to prevent plagiarism among evacuating examinees, and
*        3) has a 'continue' button at the bottom of the screen with passes the correct parameters back to 'start.php' if the
*           examinees are allowed to re-enter the building.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_student_auth.inc';
require_once '../include/marking_functions.inc';
require_once '../include/errors.php';
require_once '../include/paper_security.php';

$id = check_var('id', 'GET', true, false, true);

$userObject = UserObject::get_instance();

// Get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($id, $mysqli, $string, true);

$property_id    = $propertyObj->get_property_id();
$paper_type     = $propertyObj->get_paper_type();
$labs           = $propertyObj->get_labs();
$start_date     = $propertyObj->get_start_date();
$end_date       = $propertyObj->get_end_date();
$calendar_year  = $propertyObj->get_calendar_year();
$password       = $propertyObj->get_password();

/*
 *
 * Setup some feature related flags
 *
 */
// Are we in a staff test and preview mode?
$is_preview_mode = ( $userObject->has_role(array('Staff','SysAdmin')) and isset( $_REQUEST['mode'] ) and $_REQUEST['mode'] == 'preview' );
// Are we in a staff test and preview mode and on the first screen?
$is_preview_mode_first_launch = ( $is_preview_mode == true and isset($_GET['mode']) and $_GET['mode'] == 'preview' );
// Are we in a staff single question testmode
$is_question_preview_mode = ( isset($_GET['q_id']) );

/*
* Set the default colour scheme for this paper and allow current users' special settings to override
* $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color are passed by reference!!
*/
$bgcolor = $fgcolor = $textsize = $marks_color = $themecolor = $labelcolor = $font = $unanswered_color = $dismiss_color = '';
$propertyObj->set_paper_colour_scheme($userObject, $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color, $dismiss_color);


$original_paper_type = $paper_type; //store the original paper type - needed to retrieve answers from the correct log and functionality related decisions
$attempt = 1; //default attempt to 1 overwritten if the student is resit candidate
$modIDs = array_keys(Paper_utils::get_modules($property_id, $mysqli));

$current_address = NetworkUtils::get_client_address();
$moduleID = $propertyObj->get_modules();

if ($userObject->has_role('Staff') and check_staff_modules($moduleID, $userObject)) {
  // No further security checks.
} else {    // Treat as student with extra security checks.
  // Check for additional password on the paper
  check_paper_password($propertyObj->get_property_id(), $password, $string, $mysqli);

  // Check time security
  check_datetime($start_date, $end_date, $string, $mysqli);

  // Check room security
  $low_bandwidth = check_labs(  $propertyObj->get_paper_type(),
                                $propertyObj->get_labs(),
                                $current_address,
                                $propertyObj->get_password(),
                                $string,
                                $mysqli
                              );

  // Get modules if the user is a student and the paper is not formative
  $attempt = check_modules($userObject, $modIDs, $calendar_year, $string, $mysqli);

  // Check for any metadata security restrictions
  check_metadata($property_id, $userObject, $modIDs, $string, $mysqli);
}

// Get lab info used in log metadata
$lab_factory = new LabFactory($mysqli);
if ($lab_object = $lab_factory->get_lab_based_on_client($current_address)){
  $lab_name = $lab_object->get_name();
  $lab_id = $lab_object->get_id();
}

if (time() > $end_date and ($paper_type == '1' or $paper_type == '2')) {
  $paper_type = '_late';
}

// Lookup previous sessionid from log_metadata.started property_id
$log_metadata = new LogMetadata($userObject->get_user_ID(), $propertyObj->get_property_id(), $mysqli);
$sessionid = $log_metadata->get_session_id();

$metadataID = $log_metadata->get_metadata_id();
/*
* Save any posted answers
*
* N.B if Ajax saving is enabled: After a successful Ajax save the form is posted as the user moves to the next screen
*                                with dont_record set to true so this is not executed
*/
if ($is_question_preview_mode == false) {
  if ((isset($_POST['old_screen']) and $_POST['old_screen'] != '') and (!isset($_GET['dont_record']) or $_GET['dont_record'] != true)) {
    record_marks($propertyObj->get_property_id(), $mysqli, $propertyObj->get_paper_type(), $metadataID);
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;</title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style>
    body {text-align:center}
    .norun {font-weight:bold; margin-bottom:250px}
  </style>
</head>
<body>
  <form method="post" name="questions" action="start.php?id=<?php echo $id ?>&dont_record=true" autocomplete="off">

  <p style="font-size:200%; color:#008000"><?php echo $string['top_msg'] ?></p>
  <p class="norun"><?php echo $string['donotrun'] ?></p>
  <p><strong><?php echo $string['bottom_msg'] ?> </strong><input type="submit" name="next" value="<?php echo $string['continue'] ?>" class="ok" /></p>
<?php
  echo "<input type=\"hidden\" name=\"current_screen\" value=\"" . ($_POST['current_screen'] - 1) . "\" />\n";
  if (isset($_POST['sessionid'])) {
    echo "<input type=\"hidden\" name=\"sessionid\" value=\"" . $_POST['sessionid'] . "\" />\n";
  } else {
    echo "<input type=\"hidden\" name=\"sessionid\" value=\"" . date("YmdHis", time()) . "\" />\n";
  }
  echo "<input type=\"hidden\" name=\"page_start\" value=\"" . date("YmdHis", time()) . "\" />\n";
  echo "<input type=\"hidden\" name=\"old_screen\" value=\"" . ($_POST['current_screen'] - 1) . "\" />\n";
  echo "<input type=\"hidden\" name=\"previous_duration\" value=\"" . $_POST['previous_duration'] . "\" />\n";
  echo "<input type=\"hidden\" name=\"button_pressed\" value=\"\" />\n";
  echo "<input type=\"hidden\" name=\"fire_alarm\" value=\"1\" />\n";

  $mysqli->close();
?>
</form>
</body>
</html>
