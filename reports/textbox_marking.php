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
require '../include/errors.inc';
require '../include/media.inc';

require_once '../classes/stateutils.class.php';
require_once '../classes/folderutils.class.php';
require_once '../classes/paperproperties.class.php';

$state = $stateutil->getState($userObject->get_user_ID(), $mysqli);

$paperID    = check_var('paperID', 'GET', true, false, true);
$q_id       = check_var('q_id', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);
$phase      = check_var('phase', 'GET', true, false, true);

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$paper_type = $properties->get_paper_type();
$paper_title = $properties->get_paper_title();

// Check the question exists on the paper.
if (!QuestionUtils::question_exists_on_paper($q_id, $paperID, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

function displayMarks($id, $default, $log_record_id, $log, $halfmarks, $tmp_username, $marks, $string) {
  $html = '<select id="mark' . $id . '" name="mark' . $id . '" class="tbmark"><option value="NULL"></option>';
  $inc = 1;
  if ($halfmarks == true) $inc = 0.5;
  for ($i=0; $i<=$marks; $i+=$inc) {
    $display_i = $i;
    if ($i == 0.5) {
      $display_i = '&#189;';
    } elseif ($i - floor($i) > 0) {
      $display_i = floor($i) . '&#189;';
    }
    if ($i == $default and is_numeric($default)) {
      $html .= "<option value=\"$i\" selected>$display_i</option>";
    } else {
      $html .= "<option value=\"$i\">$display_i</option>";
    }
  }
  $html .= <<< HTML
</select>&nbsp;<span style="color:black">{$string['marks']}</span><br />&nbsp;
<input type="hidden" id="logrec{$id}" name="logrec{$id}" value="{$log_record_id}">
<input type="hidden" id="log{$id}" name="log{$id}" value="{$log}">
<input type="hidden" id="username{$id}" name="username{$id}" value="{$tmp_username}">
HTML;
  return $html;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['textboxmarking']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/textbox_marking.css" />
	<link rel="stylesheet" type="text/css" href="../css/start.css" />
  <link rel="stylesheet" type="text/css" href="../css/finish.css" />
  <style type="text/css">
	.warn_icon {width:12px; height:11px; padding-left:5px; padding-right:5px}
  <?php
  if (isset($state['hidemarked']) and $state['hidemarked'] == 'true') {
    echo ".marked {color:#808080;display:none}\n";
  } else {
    echo ".marked {color:#808080}\n";
  }
  ?>
  </style>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery-ui.1.8.16.min.js"></script>
  <script type="text/javascript" src="../js/jquery.textbox.js"></script>
  <script type="text/javascript" src="../js/ie_fix.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script type="text/javascript">
    langStrings = {'saveerror': '<?php echo $string['saveerror'] ?>'};
		
		$(document).ready(function() {
			window.location.hash = 'q_id<?php echo $_GET['q_id']; ?>';
			
			$('#hidemarked').click(function() {
			  $('.marked').toggle();
			});
			
		});
  </script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();

  $candidate_no = 0;
  if ($paper_type == '0' or $paper_type == '1' or $paper_type == '2') {
    // Get how many students took the paper.
    $result = $mysqli->prepare("SELECT DISTINCT lm.userID FROM log_metadata lm INNER JOIN users u ON lm.userID = u.id WHERE lm.paperID = ? AND DATE_ADD(lm.started, INTERVAL 2 MINUTE) >= ? AND lm.started <= ? AND (u.roles = 'Student' OR u.roles = 'graduate')");
    $result->bind_param('iss', $paperID, $startdate, $enddate);
    $result->execute();
    $result->bind_result($tmp_userID);
    while ($result->fetch()) {
      $candidate_no++;
    }
    $result->close();
  }

  $phase_description = '<strong>';
  if (!isset($_GET['phase'])) {
    $phase_description .= $string['finalisemarks'];
    $tmp_phase = '';
  } elseif ($_GET['phase'] == 1) {
    $phase_description .= $string['primarymarking'];
    $tmp_phase = '&phase=1';
  } elseif ($_GET['phase'] == 2) {
    $phase_description .= $string['secondmarking'];
    $tmp_phase = '&phase=2';
  }
  $phase_description .= ":</strong> " . number_format($candidate_no) . " " . $string['candidates'];

?>
<form id="content" action="<?php echo $_SERVER['PHP_SELF']; ?>?paperID=<?php echo $paperID; ?>&amp;q_id=<?php echo $_GET['q_id']; ?>&amp;startdate=<?php echo $startdate; ?>&amp;enddate=<?php echo $enddate; ?>&amp;module=<?php echo $_GET['module']; ?>&amp;folder=<?php echo $_GET['folder']; ?>&amp;phase=<?php echo $phase; ?>&amp;action=mark" method="post">
<input type="hidden" id="marker_id" name="marker_id" value="<?php echo $userObject->get_user_ID(); ?>" />
<input type="hidden" id="paper_id" name="paper_id" value="<?php echo $paperID; ?>" />
<input type="hidden" id="q_id" name="q_id" value="<?php echo $_GET['q_id']; ?>" />
<input type="hidden" id="phase" name="phase" value="<?php echo $phase; ?>" />
<?php

  echo "<table class=\"header\" style=\"font-size:90%\">\n<tr><th style=\"height:52px\">";
  echo '<div class="breadcrumb"><a href="../staff/index.php" target="_top">' . $string['home'] . '</a>';
  if (isset($_GET['folder']) and trim($_GET['folder']) != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $paperID . '">' . $paper_title . '</a></div><div style="margin-left:10px; font-size:220%">' . $phase_description . '</div></th>';
  echo "<th style=\"text-align:right; vertical-align:top\"><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\"><br /><input class=\"chk\" type=\"checkbox\" name=\"hidemarked\" id=\"hidemarked\" value=\"1\"";
  if (isset($state['hidemarked']) and $state['hidemarked'] == 'true') echo ' checked';
  echo "  /> " . $string['hidemarked'] . "&nbsp;</th></tr>\n";
  echo "</table>\n";

if ($phase == 2) {
  // Get the usernames of papers to second mark.
  $second_mark = array();

  $result = $mysqli->prepare("SELECT userID FROM textbox_remark WHERE paperID = ?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($remark_userID);
  while ($result->fetch()) {
    $second_mark[] = $remark_userID;
  }
  $result->close();
}

$half_marks = true;

?>
<div id="question_pane">
  <table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
  <tr><td valign="top">
  <?php

  echo '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; background-color:#5590CF">';
  echo '<tr><td><div class="paper">' . $paper_title . '</div>';
  $question_offset = 1;
	$no_screens = $properties->get_max_screen();
  if ($no_screens > 1) {
    echo '<table cellspacing="1" cellpadding="1" border="0" style="font-weight:bold; color:white"><tr>';
    for ($i=1; $i<=$no_screens; $i++) {
      echo "<td class=\"s0\">$i</td>\n";
    }
    echo '</tr></table>';
  }
  echo '</td></tr></table>';
	
	$marks_array = array();

  $question_data = $mysqli->prepare("SELECT screen, q_type, q_id, theme, scenario, leadin, q_media, q_media_width, q_media_height, notes, marks_correct, correct_fback FROM (papers, questions, options) WHERE paper = ? AND papers.question = questions.q_id AND questions.q_id = options.o_id ORDER BY display_pos, id_num");
  $question_data->bind_param('i', $_GET['paperID']);
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($screen, $q_type, $q_id, $theme, $scenario, $leadin, $q_media, $q_media_width, $q_media_height, $notes, $marks_correct, $correct_fback);
  $num_rows = $question_data->num_rows;
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
  echo "<col width=\"40\"><col>\n";
  $q_no = 0;
  $old_q_id = 0;
  $old_screen = 1;
  while ($question_data->fetch()) {
	  $marks_array[$q_id] = $marks_correct;
		
    if ($old_screen != $screen) {
      echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
      echo '<tr><td colspan="2"><div class="screenbrk"><span class="scr_no">' . $string['screen'] . '&nbsp;' . $screen . '</span></div></td></tr>';
    }

    if ($old_q_id != $q_id) {
      if ($q_no+1 == $_GET['qNo'] and $q_type != 'info') {
        $tmp_color = '#FFFFDD';
      } else {
        //$tmp_color = $bgcolor;
        $tmp_color = 'white';
      }
    
      $li_set = 0;
      echo "<tr><td colspan=\"2\"><a name=\"q_id$q_id\"></a>&nbsp;</td></tr>\n";

      if ($theme != '') echo '<tr><td colspan="2"><p class="theme">' . $theme . '</p></td></tr><tr><td colspan="2">&nbsp;</td></tr>';
      if (trim($notes) != '') echo '<tr><td></td><td class="note"><img src="../artwork/notes_icon.gif" width="14" height="14" alt="' . $string['note'] . '" />&nbsp;<strong>' . strtoupper($string['note']) . ':</strong>&nbsp;' . $notes . '</td></tr>';

      if ($scenario != '') {
        echo "<tr style=\"background-color:$tmp_color\"><td class=\"q_no\">";
        if ($q_type != 'info') {
          $q_no++;
          echo "<a name=\"q$q_no\">$q_no.&nbsp;</a>";
        }
        if ($properties->get_calculator() == 1) echo '<br /><a href="#" onclick="openCalculator(); return false;"><img src="../artwork/calc.png" width="18" height="24" alt="Calculator" /></a>';
        echo "</td><td>$scenario<br />\n<br />";
        $li_set = 1;
      }
      
      if ($q_type == 'info') {
        echo "<tr style=\"background-color:$tmp_color\"><td>";
        $li_set = 0;
      } elseif ($q_type != 'info' and $li_set == 0) {
        $q_no++;
        echo "<tr style=\"background-color:$tmp_color\"><td class=\"q_no\">";
        echo "<a name=\"q$q_no\">$q_no.&nbsp;</a>";
      }
      if ($li_set == 0) {
        echo "</td><td style=\"background-color:$tmp_color\">";
      }
      if ($q_media != '' and $q_media != NULL) {
        $media_list = explode('|', $q_media);
        $media_list_width = explode('|', $q_media_width);
        $media_list_height = explode('|', $q_media_height);
        for ($i=0; $i<count($media_list); $i++) {
          if ($media_list[$i] != '') {
            echo '<p align="center">' . display_media($media_list[$i], $media_list_width[$i], $media_list_height[$i], '') . "</p>\n";
          }
        }
      }
      echo "$leadin</td></tr>\n";
      
      if ($q_type != 'info') {
        echo "<tr style=\"background-color:$tmp_color\"><td></td><td class=\"mk\"><br />($marks_correct ". $string['marks'] .")</td></tr>\n";
        echo "<tr style=\"background-color:$tmp_color\"><td>&nbsp;</td><td class=\"fback\"><br />" . nl2br($correct_fback) . "</td></tr>\n";
      }
    }    
    $old_q_id = $q_id;
    $old_screen = $screen;
  }

  echo "</table></td></tr>\n<tr><td valign=\"bottom\">\n<br />\n";

?>
</td></tr></table>
</div>

<div id="answer_pane">
<table id="answers" cellpadding="0" cellspacing="0">
<?php
  $q_id = $_GET['q_id'];
	
  if ($paper_type == '0') {

    $sql = <<< SQL
SELECT 0 AS logtype, l.id, lm.userID, l.user_answer, t.mark
  FROM (log0 l, log_metadata lm, users u)
  LEFT JOIN textbox_marking t ON l.id = t.answer_id AND lm.paperID = t.paperID AND t.phase = ?
  WHERE lm.paperID = ?
  AND l.metadataID = lm.id
  AND (u.roles = 'Student' OR u.roles = 'graduate')
  AND u.id = lm.userID
  AND l.q_id = ?
  AND DATE_ADD(lm.started, INTERVAL 2 MINUTE) >= ?
  AND lm.started <= ?
UNION ALL
SELECT 1 AS logtype, l.id, lm.userID, l.user_answer, t.mark
  FROM (log1 l, log_metadata lm, users u)
  LEFT JOIN textbox_marking t ON l.id = t.answer_id AND lm.paperID = t.paperID AND t.phase = ?
  WHERE lm.paperID = ?
  AND l.metadataID = lm.id
  AND (u.roles = 'Student' OR u.roles = 'graduate')
  AND u.id = lm.userID
  AND l.q_id = ?
  AND DATE_ADD(lm.started, INTERVAL 2 MINUTE) >= ?
  AND lm.started <= ?
SQL;
    $result = $mysqli->prepare($sql);
    $result->bind_param('iiissiiiss', $phase, $paperID, $q_id, $startdate, $enddate, $phase, $paperID, $q_id, $startdate, $enddate);
  } else {
    $sql = <<< SQL
SELECT $paper_type AS logtype, l.id, lm.userID, l.user_answer, t.mark
FROM (log{$paper_type} l, log_metadata lm, users u)
LEFT JOIN textbox_marking t ON l.id = t.answer_id AND lm.paperID = t.paperID AND t.phase = ?
WHERE lm.paperID = ?
AND l.metadataID = lm.id
AND (u.roles = 'Student' OR u.roles = 'graduate')
AND u.id = lm.userID
AND l.q_id = ?
AND DATE_ADD(lm.started, INTERVAL 2 MINUTE) >= ?
AND lm.started <= ?;
SQL;
    $result = $mysqli->prepare($sql);
    $result->bind_param('iiiss', $phase, $paperID, $q_id, $startdate, $enddate);
  }
  $answer_no = 0;
  $result->execute();
  $result->store_result();
  $result->bind_result($logtype, $id, $tmp_userID, $user_answer, $student_mark);
  if ($result->num_rows == 0) {
    echo "<p>" . $string['nostudents'] . "</p>";
  }
  while ($result->fetch()) {
    if ($phase == 1 or ($phase == 2 and in_array($tmp_userID, $second_mark))) {
      $style = '';
      if (trim($user_answer) != '') {
        $answer_no++;
        if (is_numeric($student_mark)) {  // Marked previously so grey out.
           $style = ' class="marked"';
        }
        echo "<tr id=\"ans_" . $answer_no . "\"" . $style . "><td class=\"number\">$answer_no.</td><td class=\"student_ans\">" . nl2br($user_answer) . "<br />" . displayMarks($answer_no, $student_mark, $id, $logtype, $half_marks, $tmp_userID, $marks_array[$q_id], $string) . "</td></tr>\n";
      } else {
        $answer_no++;
        if (is_numeric($student_mark)) {  // Marked previously so grey out.
          $style = ' class="marked"';
        }
        echo "<tr" . $style . "><td style=\"vertical-align:top; text-align:right; border-bottom:1px solid #CBC7B8\">$answer_no.</td><td class=\"student_unans\"><img src=\"../artwork/small_yellow_warning_icon.gif\" alt=\"Warning\" class=\"warn_icon\" />".$string['noanswer']."<br />" . displayMarks($answer_no, $student_mark, $id, $logtype, $half_marks, $tmp_userID, 0, $string) . "</td></tr>\n";
      }
    }
  }
  $result->close();
?>
</table>

</div>
</form>
</body>
</html>
