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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_student_auth.inc';
require_once '../include/calculate_marks.inc';
require_once '../include/errors.php';
require_once '../include/mapping.inc';
require_once '../include/finish_functions.inc';
require_once '../include/paper_security.php';
require_once '../include/media.inc';

//HTML5 part
require_once '../lang/' . $language . '/paper/finish.php';
$jstring = $string; //to pass it to JavaScript HTML5 modules
//HTML5 part

check_var('id', 'GET', true, false, false);

ob_start();

//get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli, $string, true);

$paperID    = $propertyObj->get_property_id();
$paper_type = $propertyObj->get_paper_type();
if (isset($_GET['type'])) {
  $log_type = $_GET['type'];
} else {
  $log_type = $propertyObj->get_paper_type();
}

$bgcolor = $fgcolor = $textsize = $marks_color = $themecolor = $labelcolor = $font = $unanswered_color = $dismiss_color = '';
$propertyObj->set_paper_colour_scheme($userObject, $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color, $dismiss_color);

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
$metadataid = $log_metadata->get_metadata_id();

if ($metadataid === null) {
  $notice->access_denied($mysqli, $string, $string['nottaken'], true, true);
}

$preview_q_id = (isset($_GET['q_id'])) ? $_GET['q_id'] : null;
$moduleID = Paper_utils::get_modules($paperID, $mysqli);

if ($userObject->has_role('Student')) {
  // Check for additional password on the paper
  check_paper_password($propertyObj->get_property_id(), $propertyObj->get_password(), $string, $mysqli, true);
  
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
  $logger->record_access($userObject->get_user_ID(), 'Question-based feedback report', '/students/question_feedback.php?' . $_SERVER['QUERY_STRING']);    // Staff write in the URL details
}

require '../config/finish.inc';
?>
<!DOCTYPE html>
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
  ?>
  
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/student_help.js"></script>
  <?php
  if ($propertyObj->get_latex_needed() == 1) {
    echo "<script type=\"text/javascript\" src=\"../js/jquery-migrate-1.2.1.min.js\"></script>\n";
    echo "<script type=\"text/javascript\" src=\"../tools/mee/mee/js/mee_src.js\"></script>\n";
  }  
  $render = new render($configObject);
  $render->render_html5_js(json_encode($jstring));
?>
</head>
<body>
<?php
  $current_screen = 1;

  if (isset($_GET['userID'])) {
    if ($userObject->has_role(array('SysAdmin', 'Admin', 'Staff'))) {
      if ($_GET['userID'] != '') {
        $userID = $_GET['userID'];
      } else {
        display_error($string['idmissing'], $string['idmissing_msg'], false, true, false);
      }
    } else {  // Student is trying to hack into another students userID on the URL.
      header("HTTP/1.0 404 Not Found");
      $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
      $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
    }
  } else {
    $userID = $userObject->get_user_ID();
  }
  
  $old_q_id = 0;
  $old_screen = 0;
  
  echo $top_table_html;
  echo '<tr><td><div class="paper">' . $propertyObj->get_paper_title() . '</div></td>';
  echo $logo_html;
  echo '</table>';
  
  // Get any marking override for the paper
  $overrides = array();
  $sql = "SELECT m.q_id, title, surname, date_marked, new_mark_type, adjmark
          FROM marking_override m INNER JOIN users u ON m.marker_id = u.id
          INNER JOIN log{$log_type} l ON m.log_id = l.id
          WHERE user_id = ? AND paper_id = ?";
  $result = $mysqli->prepare($sql);
  $result->bind_param('ii', $userID, $paperID);
  $result->execute();
  $result->store_result();
  $result->bind_result($o_q_id, $o_title, $o_surname, $o_date_marked, $o_new_mark_type, $o_adjmark);
  while($result->fetch()) {
    $overrides[$o_q_id] = array('q_id' => $o_q_id, 'title' => $o_title, 'surname' => $o_surname, 'date_marked' => $o_date_marked, 'new_mark_type' => $o_new_mark_type, 'adjmark' => $o_adjmark);
  }
  $result->close();
  
  $status_array = QuestionStatus::get_all_statuses($mysqli, $string, true);
  display_feedback($propertyObj, $userID, $log_type, $userObject, $log_metadata, $mysqli, $status_array, $overrides, $preview_q_id);

  echo "</body>\n</html>";
  $mysqli->close();
  ob_end_flush();
?>