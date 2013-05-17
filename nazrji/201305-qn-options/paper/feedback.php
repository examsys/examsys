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
* Completes final log of the last screen to the 'logX' table and then will display feedback if the paper is in 'formative' 
* mode or will display a confirmation notice to the examinee stating all answers and marks have been successfully recorded.
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_student_auth.inc';
require_once '../include/calculate_marks.inc';
require_once '../include/errors.inc';
require_once '../include/mapping.inc';
require_once '../include/finish_functions.inc';
require_once '../include/paper_security.inc';
require_once '../include/media.inc';
require_once '../classes/paperutils.class.php';
require_once '../classes/logmetadata.class.php';
require_once '../classes/paperproperties.class.php';
require_once '../classes/logger.class.php';

check_var('id', 'GET', true, false, false);

//get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli);
if ($propertyObj == false) {  // No properties found, this crypt_name
  $notice->access_denied($mysqli, $string, $string['error_paper'], true, true);     //this will exit php
}

$paperID    = $propertyObj->get_property_id();
$paper_type = $propertyObj->get_paper_type();
if (isset($_GET['type'])) {
  $log_type = $_GET['type'];
} else {
  $log_type = $propertyObj->get_paper_type();
}

$bgcolor = $fgcolor = $textsize = $marks_color = $themecolor = $labelcolor = $font = $unanswered_color = '';
$propertyObj->set_paper_colour_scheme($userObject, $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color);

// Check if paper can be released date wise
if (!$propertyObj->is_question_fb_released()) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

//lookup previous sessionid from log_metadata.started property_id
if (isset($_GET['userid'])) {
  if ($userObject->has_role(array('SysAdmin', 'Admin', 'Staff'))) {
    $log_metadata = new LogMetadata($_GET['userid'], $paperID, $mysqli);
  } else {
    $notice->access_denied($mysqli, $string, $string['norights'], true, true);
  }
} else {
  $log_metadata = new LogMetadata($userObject->get_user_ID(), $paperID, $mysqli);
}
$log_metadata->get_record();
$sessionid = $log_metadata->get_session_id();
$metadataid = $log_metadata->get_metadata_id();

if ($sessionid === null) {
  $notice->access_denied($mysqli, $string, $string['nottaken'], true, true);
}

$preview_q_id = (isset($_GET['q_id'])) ? $_GET['q_id'] : null;
$moduleID = Paper_utils::get_modules($paperID, $mysqli);

if ($userObject->has_role('Student')) {
  // Check for additional password on the paper
  check_paper_password($propertyObj->get_password(), $string, true);
  
  $display_correct_answer     = 1;
  $display_question_mark      = 1;
  $display_students_response  = 1;
  $display_feedback           = 1;
} else {
  $display_correct_answer     = $propertyObj->get_display_correct_answer();
  $display_question_mark      = $propertyObj->get_display_question_mark();
  $display_students_response  = $propertyObj->get_display_students_response();
  $display_feedback           = $propertyObj->get_display_feedback();
}

$pass_mark = $propertyObj->get_pass_mark();

$logger = new Logger($mysqli);
if ($userObject->has_role('Student')) {
  $logger->record_access($userObject->get_user_ID(), 'Question-based feedback report', $paperID);  // Students write in the paperID
} else {
  $logger->record_access($userObject->get_user_ID(), 'Question-based feedback report', '/paper/feedback.php?' . $_SERVER['QUERY_STRING']);    // Staff write in the URL details
}

require '../config/finish.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <meta http-equiv="imagetoolbar" content="no">
  <meta http-equiv="imagetoolbar" content="false">

  <title><?php echo $string['examscript'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/start.css" />
  <link rel="stylesheet" type="text/css" href="../css/finish.css" />
  <link rel="stylesheet" type="text/css" href="../css/key.css" />
<?php
  $css = '';
  if ($userObject->is_special_needs() and $bgcolor != '#FFFFFF') {
    $css .= "select,input{background-color:$bgcolor;color:$fgcolor;font-family:$font,sans-serif}\n";
  }
  if (($bgcolor != '#FFFFFF' and $bgcolor != 'white') or ($fgcolor != '#000000' and $fgcolor != 'black') or $textsize != 90) {
    $css .= "body {background-color:$bgcolor;color:$fgcolor;font-size:$textsize%}\n";
    $css .= ".staffview {\nbackground: -moz-linear-gradient(top, #FF8282, $bgcolor);\nbackground: -webkit-linear-gradient(top, #FF8282, $bgcolor);\nbackground-image: -ms-linear-gradient(top, #FF8282 0%, $bgcolor 100%);\nfilter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#FF8282', endColorstr='$bgcolor');\n}\n";
  }
  if ($font != 'Arial') {
    if (strpos($font,' ') === false) {
      $css .= "body {font-family:$font,sans-serif}\n";
      $css .= "pre {font-family:$font,sans-serif}\n";
    } else {
      $css .= "body {font-family:'$font',sans-serif}\n";
      $css .= "pre {font-family:'$font',sans-serif}\n";
    }
  }
  if ($themecolor != '#316AC5') {
    $css .= ".theme {color:$themecolor}\n";
    $css .= ".objH {color:$themecolor}\n";
  }
  if ($labelcolor != '#316AC5') {
    $css .= ".fback {color:$labelcolor}\n";
    $css .= ".label {color:$labelcolor}\n";
  }
  if ($css != '') {
    echo "<style type=\"text/css\">\n$css\n</style>\n";
  }
  
  if ($propertyObj->get_latex_needed() == 1) {
    echo "<script type=\"text/javascript\" src=\"../js/jquery-1.6.1.min.js\"></script>";
    echo "<script type=\"text/javascript\" src=\"../tools/mee/mee/js/mee_src.js\"></script>";
  }
?>
  <script type="text/javascript" src="../js/ie_fix.js"></script>
  <script type="text/javascript" src="../js/student_help.js"></script>
  <script type="text/javascript" src="../js/flash_include.js"></script>
  <script language="JavaScript">
    window.history.go(1);
  </script>
</head>
<body>
<?php
  $current_screen = 1;

  if (isset($_GET['userid'])) {
    $temp_userID = $_GET['userid'];
  } else {
    $temp_userID = $userObject->get_user_ID();
  }
  $old_q_id = 0;
  $old_screen = 0;
  
  echo $top_table_html;
  echo '<tr><td><div class="paper">' . $propertyObj->get_paper_title() . '</div></td>';
  echo $logo_html;
  echo '</table>';
  
  display_feedback($sessionid, $temp_userID, $paperID, $paper_type, $log_type, $propertyObj->get_paper_title(), $propertyObj->get_paper_postscript(), $propertyObj->get_marking(), $userObject, $metadataid, $mysqli, $preview_q_id);

  echo "</body>\n</html>";
  $mysqli->close();
?>