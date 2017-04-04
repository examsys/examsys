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
* Displays an overview of summative and offline reports for a student
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once '../include/demo_replace.inc';
require_once '../include/calculate_marks.inc';

$paperID  = check_var('paperID', 'GET', true, false, true);
$userID   = check_var('userID', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$log_type = $propertyObj->get_paper_type();

if ($log_type != '2' and $log_type != '4' and $log_type != '5') {   // Exit if wrong type of paper
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$results_cache = new ResultsCache($mysqli);

$medians       = $results_cache->get_median_question_marks_by_paper($paperID);

$student_marks = $results_cache->get_student_question_marks_by_paper($userID, $log_type, $paperID);

if (count($student_marks) == 0) {   // Exit if the student does not have any marks
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$old_q_id           = 0;
$old_display_pos    = -1;
$old_q_type         = '';
$old_option_text    = array();
$old_correct        = array();
$old_display_method = '';
$old_score_method   = '';
$old_marks          = 0;
$tmp_exclude        = '';
$total_marks        = 0;

$questions_marks    = array();

// Get the questions (if any).
$result = $mysqli->prepare("SELECT theme, ownerID, p_id, q_id, q_type, screen, leadin, scenario, option_text, o_media, correct, display_method, score_method, q_media, q_media_width, q_media_height, marks_correct, marks_incorrect, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS display_last_edited, display_pos, status, correct_fback, feedback_right, locked, settings FROM (papers, questions) LEFT JOIN options ON questions.q_id = options.o_id WHERE paper=? AND papers.question=questions.q_id ORDER BY screen, display_pos, o_id");
$result->bind_param('i', $paperID);
$result->execute();
$result->store_result();
$result->bind_result($theme, $ownerID, $p_id, $q_id, $q_type, $screen, $leadin, $scenario, $option_text, $o_media, $correct, $display_method, $score_method, $q_media, $q_media_width, $q_media_height, $marks_correct, $marks_incorrect, $display_last_edited, $display_pos, $status, $correct_fback, $feedback_right, $locked, $settings);
while ($result->fetch()) {
  if ($old_q_id != $q_id or $old_display_pos != $display_pos) {
    $question_marks[$old_q_id] = qMarks($old_q_type, $tmp_exclude, $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method);

    $old_correct      = array();
    $old_option_text  = array();
  }
  
  $old_q_id           = $q_id;
  $old_display_pos    = $display_pos;
  $old_q_type         = $q_type;
  $old_option_text[]  = $option_text;
  $old_correct[]      = $correct;
  $old_marks          = $marks_correct;
  $old_display_method = $display_method;
  $old_score_method   = $score_method;
}
$result->close();

$question_marks[$old_q_id] = qMarks($old_q_type, $tmp_exclude, $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method);

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

<title><?php $string['personalcohortperformance'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style>
body {font-size:90%}
li {padding-bottom:10px}
.label {position:relative; padding:0; margin:0; width:110px; height:11px}
</style>

<script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../js/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>

</head>
<body>
<div style="position:relative; width:260px; height:88px; border: 2px solid #FCE699; z-index:10; float:right; top:10px; right:10px; font-size:75%; padding:5px; line-height:100%; background-color:#FFFFEE; color:#404040">
<img src="../artwork/barchart_key.png" width="218" height="65" alt="Key" style="position:relative; top:10px; left:0" />
<div style="left:100px; top:-67px" class="label"><?php echo $string['availablemarks']; ?></div>
<div style="left:155px; top:-9px" class="label"><?php echo $string['studentsmark']; ?></div>
<div style="left:60px; top:-9px; width:160px" class="label"><?php echo $string['medianclassmark']; ?></div>
</div>

<div style="position:absolute; top:0; left:0; width:100%">
<?php
$demo = is_demo($userObject);
$student_details = UserUtils::get_user_details($userID, $mysqli);
$name = demo_replace($student_details['title'], $demo) . ' ' . demo_replace($student_details['surname'], $demo) . ', ' . demo_replace($student_details['first_names'], $demo) . ' (' . demo_replace($student_details['student_id'], $demo) . ')';

echo "<table class=\"header\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"font-size:90%\">\n";
echo "<tr><th><div style=\"padding-left:10px; font-size:200%; font-weight:bold\">" . $propertyObj->get_paper_title() . "</div><div style=\"padding-left:10px\">$name</div></th></tr>\n";
echo "</table>\n<ol>";

// Get the questions on the paper
$q_no = 1;

$result = $mysqli->prepare("SELECT q_id, theme, leadin, q_type FROM questions, papers WHERE papers.paper = ? AND papers.question = questions.q_id AND q_type != 'info' ORDER BY screen, display_pos");
$result->bind_param('i', $paperID);
$result->execute();
$result->bind_result($q_id, $theme, $leadin, $q_type);
while ($result->fetch()) {
  echo "<li>$leadin";
  
  if (substr(strtolower($leadin), -4) == '</p>') {

  } elseif (substr(strtolower($leadin), -6) == '</div>') {
    echo '<br />';
  } else {
    echo '<br /><br />';
  }
  
  echo '<img src="draw_barchart.php?tpm=' . $question_marks[$q_id] . '&mark=' . $student_marks[$q_id] . '&median=' . $medians[$q_id] . '" width="300" height="65" alt="" />';
  echo "</li>\n";
}
$result->close();


$mysqli->close();
?>
</ol>
</div>
</body>
</html>
