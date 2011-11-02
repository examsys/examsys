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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require 'display_functions.inc';
require '../include/reviews.inc';
require '../include/errors.inc';
require '../include/media.inc';
require '../config/start.inc';

check_var('id', 'GET', true, false);

if ($stmt = $mysqli->prepare("SELECT background, foreground, textsize, marks_color, themecolor, labelcolor, font FROM special_needs WHERE userid=?")) {
  $stmt->bind_param('i', $userID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font);
  $stmt->fetch();
}
$stmt->close();

// Extract the get variables.
if (isset($_GET['no_screens'])) {
  $no_screens = $_GET['no_screens'];
  $current_screen = $_GET['current_screen'];
  $sessionid = $_GET['sessionid'];
  $previous = $_GET['previous'];
  $userid = $_GET['userid'];
  $surname = $_GET['surname'];
}

if ($stmt = $mysqli->prepare("SELECT background, foreground, textsize, marks_color, themecolor, labelcolor, font FROM special_needs WHERE userid=?")) {
  $stmt->bind_param('i', $userID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font);
  $stmt->fetch();
}
$stmt->close();

// Get how many screens make up the question paper.
$screen_data = array();
$stmt = $mysqli->prepare("SELECT property_id, labs, paper_title, paper_type, paper_prologue, marking, screen, q_type, UNIX_TIMESTAMP(start_date), UNIX_TIMESTAMP(end_date), bgcolor, fgcolor, themecolor, labelcolor, bidirectional, calculator, moduleID, calendar_year, UNIX_TIMESTAMP(external_review_deadline), UNIX_TIMESTAMP(internal_review_deadline), latex_needed, password FROM (properties, papers, questions) WHERE properties.property_id=papers.paper AND crypt_name=? AND papers.question=questions.q_id ORDER BY screen");
$stmt->bind_param('s', $_GET['id']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($property_id, $labs, $paper_title, $paper_type, $paper_prologue, $marking, $screen, $q_type, $start_date, $end_date, $paper_bgcolor, $paper_fgcolor, $paper_themecolor, $paper_labelcolor, $bidirectional, $calculator, $moduleID, $calendar_year, $external_review_deadline, $internal_review_deadline, $latex_needed, $password);
while ($stmt->fetch()) {
  $no_screens = $screen;
  $original_paper_type = $paper_type;
  if ($q_type != 'info') {
    if (!isset($screen_data[$no_screens])) { 
      $screen_data[$no_screens] = 1;
    } else {
      $screen_data[$no_screens]++;
    }
  } 
  // If set overwrite the default colours with the current users' special settings
  if (!isset($bgcolor) or $bgcolor == 'NULL' or $bgcolor == '') $bgcolor = $paper_bgcolor;
  if (!isset($fgcolor) or $fgcolor == 'NULL' or $fgcolor == '') $fgcolor = $paper_fgcolor;
  if (!isset($textsize) or $textsize == 'NULL' or $textsize == '') $textsize = 90;
  if (!isset($marks_color) or $marks_color == 'NULL' or $marks_color == '') $marks_color = '#808080';
  if (!isset($themecolor) or $themecolor == 'NULL' or $themecolor == '') $themecolor = $paper_themecolor;
  if (!isset($labelcolor) or $labelcolor == 'NULL' or $labelcolor == '') $labelcolor = $paper_labelcolor;
  if (!isset($font) or $font== 'NULL' or $font == '') $font = 'Arial';
  $attempt = 1; //default attempt to 1 overwritten if the student is resit candidate

  if ($userroles == 'External Examiner') {
    $review_type = 'External';
    $review_deadline = $external_review_deadline;
  } else { 
    $review_type = 'Internal';
    $review_deadline = $internal_review_deadline;
  }
}
$stmt->free_result();
$stmt->close();

// Extract the posted variables.
$restart = 0;
$current_screen = 1;
if (isset($_POST['sessionid'])) {
  if (isset($_POST['next'])) {
    $current_screen = $_POST['current_screen'];
  } elseif (isset($_POST['prev'])) {
    $current_screen = $_POST['current_screen'] - 2;
  } elseif (isset($_POST['jump_screen'])) {
    $current_screen = $_POST['jump_screen'];
  }
  $sessionid = $_POST['sessionid'];
} else {
  if (($paper_type == '1' or $paper_type == '2' or $paper_type == '3') and !isset($_GET['mode'])) {  //Mode is used for staff preview.
    $stmt = $mysqli->prepare("SELECT DATE_FORMAT(MAX(started),\"%Y%m%d%H%i%s\") AS started FROM log$paper_type WHERE q_paper=? AND userID=? GROUP BY screen DESC LIMIT 1");
    $stmt->bind_param('ii', $property_id, $userID);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($sessionid);
    if ($stmt->num_rows == 1) {
      $row = $stmt->fetch();
      $stmt->free_result();
      $restart = 1;
    } else {
      $sessionid = date("YmdHis", time());
    }
    $stmt->close();
  } else {
    $sessionid = date("YmdHis", time());
  }
}

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\n\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
echo "<html>\n<head>\n<title>$paper_title</title>\n";
?>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="imagetoolbar" content="false">

<style type="text/css">
body {background-color:<?php echo $bgcolor; ?>; color:<?php echo $fgcolor; ?>; padding:0px; margin:0px; border:0px; font-family:Arial,sans-serif; font-size:<?php echo $textsize; ?>%}
li {margin-left:15px; margin-right:15px; font-family:Arial,sans-serif; font-size:100%}
<?php
if (($bgcolor != 'white' and $bgcolor != '#FFFFFF') or ($fgcolor != 'black' and $fgcolor != '#000000')) {
  echo "select, input {background-color:$bgcolor; color:$fgcolor; font-family:Arial,sans-serif; font-size:100%}\n";
} else {
  echo "select, input {font-family:Arial,sans-serif; font-size:100%}\n";
}
?>
table {font-size:100%}
p {margin-top:0px; padding-top:0px}
pre {
white-space: pre-wrap; /* css-3 */
white-space: -moz-pre-wrap !important; /* Mozilla, since 1999 */
white-space: -pre-wrap; /* Opera 4-6 */
white-space: -o-pre-wrap; /* Opera 7 */
word-wrap: break-word; /* Internet Explorer 5.5+ */
}
.raised_tbl {background-color:#5582D2; border-left:solid #90C8FF 1px; border-right:solid #003060 1px; border-top:solid #90C8FF 1px; border-bottom:solid #003060 1px}
.paper {margin-left:0px; font-size:180%; color:white; font-weight:bold}
.q_no {width:40px; text-align:right; vertical-align:top}
.theme {font-size:150%; padding-left:4px; font-weight:bold; color:<?php echo $themecolor; ?>}
.notes {font-size:80%; color:<?php echo $labelcolor; ?>}
.mk {color:<?php echo $marks_color; ?>; font-size:80%}
.active {color:<?php echo $fgcolor; ?>}
.inactive {color:#C0C0C0}
.scrno {width:18px; text-align:center; background-color:#003366; font-size:80%}
.scrnocur {width:18px; text-align:center; background-color:#C00000; font-size:80%}
.likert_button {text-align:center; width:40px; vertical-align:top}
.unanswered {background-color:white}
.matrix {border:1px solid #808080; border-collapse:collapse}
.matrix td {border:1px solid #808080}
.extmatch li {padding-bottom:14px; vertical-align:text-bottom; list-style-type:lower-roman}
textarea {font-size:100%}
</style>

<script src="start.js" type="text/javascript"></script>
<script language="JavaScript" src="../javascript/flash_include.js"></script>
<script type="text/javascript" src="/javascript/jquery-1.6.1.min.js"></script>
<?php if ($latex_needed == 1) {?>
<script type="text/javascript" src="/tools/mee/mee/js/mee_src.js"></script>
<?php }?>
<script language="JavaScript" type="text/javascript">
  window.history.go(1);
<?php
  if ($bidirectional == 0) {
?>
  function confirmSubmit() {
    var agree = confirm("Have you completed all the questions on this screen, you will NOT be able to go back.\nAre you sure you wish to continue?");
    if (agree) {
      document.body.style.cursor = 'wait';
      return true;
    } else {
      return false;
    }
  }
<?php
  } else {
?>
  function jumpScreen() {
    document.questions.button_pressed.value='previous';
    document.questions.action="start.php?id=<?php echo $_GET['id']; ?>";
    document.questions.submit();
  }
<?php
  }
?>
</script>
</head>
<body onload="refreshparent(); StartClock()" onunload="KillClock()">
<?php
if ($current_screen < $no_screens) {
  echo "<form method=\"post\" name=\"questions\" action=\"" . $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'];
} else {
  echo "<form method=\"post\" name=\"questions\" action=\"finish.php?id=" . $_GET['id'];
}
echo '" onsubmit="return confirmSubmit()">';   // Warning message only in linear navigation mode.
?>
  <table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
  <tr><td valign="top">
  <?php
  if (isset($_POST['old_screen']) and $_POST['old_screen'] != '' and time() <= $review_deadline) {  
    record_comments($property_id, $_POST['old_screen'], $mysqli, $_POST, $userID, $review_type);
  }

  echo $top_table_html;
  echo '<tr><td><div class="paper">' . $paper_title . '</div>';
  $question_offset = 0;
  if ($no_screens > 1) {
    echo '<table cellspacing="1" cellpadding="1" border="0" style="font-weight:bold; color:white"><tr>';
    for ($i=1; $i<=$no_screens; $i++) {
      echo "<td title=\"" . $screen_data[$i];
      if ($i == $current_screen) {
        if ($screen_data[$i] == 1) {
          echo " question\" class=\"scrnocur\">";
        } else {
          echo " questions\" class=\"scrnocur\">";
        }
      } else {
        if ($screen_data[$i] == 1) {
          echo " question\" class=\"scrno\">";
        } else {
          echo " questions\" class=\"scrno\">";
        }
        if ($i < $current_screen) $question_offset += $screen_data[$i];
      }
      echo "$i</td>\n";
    }
    echo '</tr></table>';
  }
  echo '</td>';
  echo $logo_html;
  
  if (time() > $review_deadline) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\"><tr><td style=\"height:32px; text-align:right; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\"><img src=\"../artwork/late_warning_icon.png\" style=\"padding-top:2px\" width=\"28\" height=\"28\" alt=\"Locked\" />&nbsp;&nbsp;</td><td style=\"height:32px; vertical-align:middle; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\"><strong>Deadline Expired</strong>&nbsp;&nbsp;&nbsp;You may view this assessment but you cannot change your comments as the deadline has expired.</td></tr></table>\n";
  }
  
  $previous_duration = 0;
  $screen_pre_submitted = 0;
  $reviews_array = array();
  $result = $mysqli->prepare("SELECT q_id, category, comment, duration, action, response FROM review_comments WHERE q_paper=? AND screen=? AND reviewer=$userID");
  $result->bind_param('ii', $property_id, $current_screen);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $category, $comment, $previous_duration, $action, $response);
  while ($review_row = $result->fetch()) {
    $reviews_array[$q_id]['category'] = $category;
    $reviews_array[$q_id]['comment'] = $comment;
    $reviews_array[$q_id]['action'] = $action;
    $reviews_array[$q_id]['response'] = $response;
  }
  $result->close();

  $old_leadin = '';
  $old_q_type = '';
  $old_q_id = 0;
  $question_no = 0;
  $q_displayed = 0;
  $marks = 0;
  $old_theme = '';
  $previous_q_type = '';

  $question_data = $mysqli->prepare("SELECT q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes, display_pos, q_option_order FROM papers, questions, options WHERE paper=? AND screen=? AND papers.question=questions.q_id AND questions.q_id=options.o_id ORDER BY display_pos, id_num");
  $question_data->bind_param('ii', $property_id, $current_screen);
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $display_pos, $q_option_order);
  $num_rows = $question_data->num_rows;
  echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
  echo "<col width=\"40\"><col>\n";
  $q_no = 0;
  //build the questions_array
  while ($row = $question_data->fetch()) {
    if ($q_no == 0 or $questions_array[$q_no]['q_id'] != $q_id or $questions_array[$q_no]['display_pos'] != $display_pos) {
      $q_no++;
      $questions_array[$q_no]['theme'] = trim($theme);
      $questions_array[$q_no]['scenario'] = trim($scenario);
      $questions_array[$q_no]['leadin'] = trim($leadin);
      $questions_array[$q_no]['notes'] = trim($notes);
      $questions_array[$q_no]['q_type'] = $q_type;
      $questions_array[$q_no]['q_id'] = $q_id;
      $questions_array[$q_no]['display_pos'] = $display_pos;
      $questions_array[$q_no]['score_method'] = $score_method;
      $questions_array[$q_no]['display_method'] = $display_method;
      $questions_array[$q_no]['q_media'] = $q_media;
      $questions_array[$q_no]['q_media_width'] = $q_media_width;
      $questions_array[$q_no]['q_media_height'] = $q_media_height;
      $questions_array[$q_no]['q_option_order'] = $q_option_order;
      $questions_array[$q_no]['dismiss'] = '';
    }
    $questions_array[$q_no]['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
  }
  $question_data->close();
  
  //display the questions
  foreach($questions_array as &$question) {
    if ($screen_pre_submitted == 1 and $q_displayed == 0) echo "<tr><td colspan=\"2\"><span style=\"background-color:#FFC0C0\">&nbsp;&nbsp;&nbsp;&nbsp;</span> = unanswered question</td></tr>\n";
    if ($q_displayed == 0 and $current_screen == 1 and $paper_prologue != '') echo '<tr><td colspan="2" style="padding:20px; text-align:justify">' . $paper_prologue . '</td></tr>';
    if ($q_displayed == 0 and $question['theme'] == '') echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    if ($question['q_type'] == 'random') randomQOverwrite2($question, $paper_type, $user_answers, $current_screen);
    //if ($question['q_type'] == 'branching') branchingQOverwrite2($question,$paper_type,$user_answers,$current_screen);
    display_question($question, $paper_type, $current_screen, $previous_q_type, $question_no, $question_offset);	
    $previous_q_type = $question['q_type'];
    $q_displayed++;
  }
  
  echo "</table></td></tr>\n<tr><td valign=\"bottom\">\n<br />\n";

  $current_screen++;
  echo "<input type=\"hidden\" name=\"current_screen\" value=\"$current_screen\" />\n";
  echo "<input type=\"hidden\" name=\"sessionid\" value=\"$sessionid\" />\n";
  echo "<input type=\"hidden\" name=\"page_start\" value=\"" . date("YmdHis", time()) . "\" />\n";
  echo "<input type=\"hidden\" name=\"old_screen\" value=\"" . ($current_screen - 1) . "\" />\n";
  echo "<input type=\"hidden\" name=\"previous_duration\" value=\"$previous_duration\" />\n";
  echo "<input type=\"hidden\" name=\"button_pressed\" value=\"\" />\n";

  echo $bottom_html;
  echo '<input type="text" style="background-color:transparent; text-align:center; font-size:80%; color:white; border:0px" id="theTime" size="8" /></td><td align="right">';
  if ($bidirectional == 1 and $no_screens > 1) {
    if ($current_screen > 2) echo "<input type=\"submit\" name=\"prev\" onclick=\"document.questions.button_pressed.value='previous'; document.questions.action='start.php?id=" . $_GET['id'] . "'\" style=\"width:120px\" value=\"&nbsp;&lt; Screen " . ($current_screen - 2) . "&nbsp;\" />&nbsp;";
    if ($original_paper_type == '0' or $original_paper_type == '1' or $original_paper_type == '2') {
      echo "<select name=\"jump_screen\" onchange=\"jumpScreen()\">";
      for ($i=1; $i<=$no_screens; $i++) {
        if ($i == ($current_screen - 1)) {
          echo "<option value=\"$i\" selected>$i</option>";
        } else {
          echo "<option value=\"$i\">$i</option>";
        }
      }
      echo "</select>&nbsp;";
    }
  }
  if ($current_screen > $no_screens) {
    echo "<input type=\"submit\" style=\"width:120px; font-weight:bold\" name=\"next\" onclick=\"document.questions.button_pressed.value='finish';\" value=\"" . $string['finish'] . "\" />&nbsp;\n";
  } else {
    echo "<input type=\"submit\" style=\"width:120px\" name=\"next\" value=\"" . $string['screen'] . " $current_screen &gt;\" />&nbsp;\n";
  }
  echo '</td></tr></table>';
  $mysqli->close();
?>
</td></tr></table>
</form>
</body>
</html>