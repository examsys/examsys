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
* Handles paper display and the recording of marks to the 'logX' tables. Uses functions within 'display_functions.inc' to process specific 
* types of questions. Start.php continues calling itself while there are further screens to be displayed and then calls 'finish.php'
* to end.
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/print_functions.inc';
require '../include/media.inc';
require_once '../include/errors.inc';

check_var('id', 'GET', true, false);


function randomQOverwrite(&$questions, $random_q_data, $paper_type, $user_answers, $current_screen, $q_no) {
  global $mysqli, $used_questions;
 
  $selected_q_id = '';
  if(isset($user_answers[$current_screen])) {
    //match user's answers with random question ID.
    $question_on_screen = array_keys($user_answers[$current_screen]);
    $selected_q_id = current($question_on_screen);
    for ($i=1; $i<$q_no; $i++) {
      $selected_q_id = next($question_on_screen);
    }
  }
  
  if ($selected_q_id == '') {
    // Generate a random question ID.
    $random_q_no = count($random_q_data['options']);
    $try = 0;
    $unique = false;
    while ($unique == false and $try < 9999) {
      $selected_no = rand(0,$random_q_no-1);
      $selected_q_id = $random_q_data['options'][$selected_no]['option_text'];
      if (!isset($used_questions[$selected_q_id])) $unique = true;
      $try++;
    }
    $used_questions[$selected_q_id] = 1;
  }
  
  // Look up selected question and overwrite data.
  $question_data = $mysqli->prepare("SELECT q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes, q_option_order FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
  $question_data->bind_param('i', $selected_q_id);
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $q_option_order);
  while ($question_data->fetch()) {
    if (!isset($question['q_id']) or $question['q_id'] != $q_id) {
      $question['theme'] = $theme;
      $question['scenario'] = $scenario;
      $question['leadin'] = $leadin;
      $question['notes'] = $notes;
      $question['q_type'] = $q_type;
      $question['q_id'] = $q_id;
      $question['display_pos'] = $q_no;
      $question['score_method'] = $score_method;
      $question['display_method'] = $display_method;
      $question['q_media'] = $q_media;
      $question['q_media_width'] = $q_media_width;
      $question['q_media_height'] = $q_media_height;
      $question['q_option_order'] = $q_option_order;
      $question['dismiss'] = '';
    }
    $question['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
  }
  $questions[] = $question;
  echo "\n<input type=\"hidden\" name=\"q" . $q_no . "_randomID\" value=\"" . $question['q_id'] ."\" />\n";
}

function branchingQOverwrite(&$questions,$branching_q_data,$paper_type,$user_answers,$current_screen) {
  global $mysqli, $userID, $sessionid;
  $previous_user_answer = '';
  for ($screen=1; $screen<=count($user_answers); $screen++) {
    foreach ($user_answers[$screen] as $questionID=>$past_answer) {
      if ($branching_q_data['scenario'] == $questionID) $previous_user_answer = $past_answer;
    }
  }
  $target_questionIDs = explode(',',$branching_q_data['options'][$previous_user_answer-1]['option_text']);
  
  // Remove any additional records from log, if user goes down different 'branch'.
  
  foreach ($branching_q_data['options'] as $individual_option) {
    //build a list of all optional questions
    $optional_qids .= $individual_option['option_text'] . ',';
  }
  $optional_qids = array_unique(explode(',',$optional_qids)); //get the unique qids
  foreach ($optional_qids as $op_qid) {
    if (!in_array($op_qid,$target_questionIDs) and isset($user_answers[$current_screen][$op_qid])) {
      //if any of the possible qids are set on this screen remove old answer as the user is no on a different branch 
      $stmt = $mysqli->prepare("DELETE FROM log$paper_type WHERE userid=? AND screen=? AND started=? AND q_id=?");
      $stmt->bind_param('iisi',$userID, $current_screen, $sessionid, $op_qid);
      $stmt->execute();
    }
  }
    
  foreach ($target_questionIDs as $target_questionID) {
    // Look up selected question and overwrite data.
    $question_data = $mysqli->prepare("SELECT q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes, q_option_order FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
    $question_data->bind_param('i', $target_questionID);
    $question_data->execute();
    $question_data->store_result();
    $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $q_option_order);
    $question = array();
    while ($question_data->fetch()) {
      if ($question['q_id'] != $q_id or $question['display_pos'] != $display_pos) {
        $question['theme'] = $theme;
        $question['scenario'] = $scenario;
        $question['leadin'] = $leadin;
        $question['notes'] = $notes;
        $question['q_type'] = $q_type;
        $question['q_id'] = $q_id;
        $question['display_pos'] = $display_pos;
        $question['score_method'] = $score_method;
        $question['display_method'] = $display_method;
        $question['q_media'] = $q_media;
        $question['q_media_width'] = $q_media_width;
        $question['q_media_height'] = $q_media_height;
        $question['q_option_order'] = $q_option_order;
        $question['dismiss'] = $dismiss;
      }
      $question['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
   }
   $questions[] = $question;
  }
  echo "\n<input type=\"hidden\" name=\"q" . $branching_q_data['q_id'] . '_' . ($previous_user_answer-1) . "_branchID\" value=\"" . ($previous_user_answer-1) . "\" />\n";
}

function keywordQOverwrite(&$questions, $random_q_data, $paper_type, $user_answers, $current_screen, $q_no) {
  global $mysqli, $used_questions, $string;
  
  $selected_q_id = '';
  if (isset($user_answers[$current_screen])) {
    //match user's answers with random question ID.
    $question_on_screen = array_keys($user_answers[$current_screen]);
    $selected_q_id = current($question_on_screen);
    for ($i=1; $i<$q_no; $i++) {
      $selected_q_id = next($question_on_screen);
    }
  }
  
  if ($selected_q_id == '') {
    // Generate a random question ID from keywords.
    $question_ids = array();
    $question_data = $mysqli->prepare("SELECT DISTINCT q_id FROM keywords_question WHERE keywordID=?");
    $question_data->bind_param('i', $random_q_data['options'][0]['option_text']);
    $question_data->execute();
    $question_data->bind_result($q_id);
    while ($question_data->fetch()) {
      $question_ids[] = $q_id;
    }
    $question_data->close();
    shuffle($question_ids);
    
    $try = 0;
    $unique = false;
    while ($unique == false and $try < count($question_ids)) {
      $selected_q_id = $question_ids[$try];
      if (!isset($used_questions[$selected_q_id])) $unique = true;
      $try++;
    }
    $used_questions[$selected_q_id] = 1;
  }
  
  if ($unique) {
    // Look up selected question and overwrite data.
    $question_data = $mysqli->prepare("SELECT q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes, q_option_order FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
    $question_data->bind_param('i', $selected_q_id);
    $question_data->execute();
    $question_data->store_result();
    $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $q_option_order);
    while ($question_data->fetch()) {
      if (!isset($question['q_id']) or $question['q_id'] != $q_id) {
        $question['theme'] = $theme;
        $question['scenario'] = $scenario;
        $question['leadin'] = $leadin;
        $question['notes'] = $notes;
        $question['q_type'] = $q_type;
        $question['q_id'] = $q_id;
        $question['display_pos'] = $q_no;
        $question['score_method'] = $score_method;
        $question['display_method'] = $display_method;
        $question['q_media'] = $q_media;
        $question['q_media_width'] = $q_media_width;
        $question['q_media_height'] = $q_media_height;
        $question['q_option_order'] = $q_option_order;
        $question['dismiss'] = '';
      }
      $question['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
    }
    echo "\n<input type=\"hidden\" name=\"q" . $q_no . "_randomID\" value=\"" . $question['q_id'] ."\" />\n";
  } else {
    $question['leadin'] = '<span style="color: #f00;">' . $string['error_keywords'] . '</span>';
    $question['q_type'] = 'keyword_based';
    $question['q_id'] = -1;
    $question['display_pos'] = $q_no;
    $question['theme'] = $question['scenario'] = $question['notes'] = $question['score_method'] = $question['q_media'] = '';
    $question['q_media_width'] = $question['q_media_height'] = $question['q_option_order'] = $question['dismiss'] = '';
    $question['options'][] = array();
  }
  $questions[] = $question;
}

if (isset($_POST['sessionid'])) require '../include/marking_functions.inc';

if ($special_needs == 1) {
  $stmt = $mysqli->prepare("SELECT background, foreground, textsize, marks_color, themecolor, labelcolor, font FROM special_needs WHERE userid=?");
  $stmt->bind_param('i',$userID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font);
  $stmt->fetch();
  $stmt->close();
}

// Get how many screens make up the question paper.
$screen_data = array();
$row_no = 0;
$stmt = $mysqli->prepare("SELECT property_id, labs, paper_title, paper_type, paper_prologue, marking, screen, UNIX_TIMESTAMP(start_date), UNIX_TIMESTAMP(end_date), bgcolor, fgcolor, themecolor, labelcolor, bidirectional, moduleID, calendar_year, latex_needed, password FROM (properties, papers, questions) WHERE properties.property_id=papers.paper AND crypt_name=? AND papers.question=questions.q_id AND q_type != 'info' ORDER BY screen");
$stmt->bind_param('s', $_GET['id']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($property_id, $labs, $paper_title, $paper_type, $paper_prologue, $marking, $screen, $start_date, $end_date, $paper_bgcolor, $paper_fgcolor, $paper_themecolor, $paper_labelcolor, $bidirectional, $moduleID, $calendar_year, $latex_needed, $password);
if ($stmt->num_rows == 0) {  // No record found, the paper can't exist
  access_denied($string['error_paper'], $output_header = false);
}
while ($stmt->fetch()) {
  $row_no++;
  $no_screens = $screen;
  if (!isset($screen_data[$no_screens])) { 
    $screen_data[$no_screens] = 1;
  } else {
    $screen_data[$no_screens]++;
  }
  if ($row_no == 1) {
    $original_paper_type = $paper_type;
    
    // If set overwrite the default colours with the current users' special settings
    if (!isset($bgcolor) or $bgcolor == 'NULL' or $bgcolor == '') $bgcolor = $paper_bgcolor;
    if (!isset($fgcolor) or $fgcolor == 'NULL' or $fgcolor == '') $fgcolor = $paper_fgcolor;
    if (!isset($textsize) or $textsize == 'NULL' or $textsize == '') $textsize = 90;
    if (!isset($marks_color) or $marks_color == 'NULL' or $marks_color == '') $marks_color = '#808080';
    if (!isset($themecolor) or $themecolor == 'NULL' or $themecolor == '') $themecolor = $paper_themecolor;
    if (!isset($labelcolor) or $labelcolor == 'NULL' or $labelcolor == '') $labelcolor = $paper_labelcolor;
    if (!isset($font) or $font== 'NULL' or $font == '') $font = 'Arial';
    $attempt = 1; //default attempt to 1 overwritten if the student is resit candidate
  }
}
$stmt->free_result();
$stmt->close();

// Extract the posted variables.
$restart = 0;
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
  $current_screen = 1;
  if (($paper_type == '1' or $paper_type == '2' or $paper_type == '3') and !isset($_GET['mode'])) {  //Mode is used for staff preview.
    $stmt = $mysqli->prepare("SELECT DATE_FORMAT(MAX(started),\"%Y%m%d%H%i%s\") AS started, MAX(screen) AS screen FROM log$paper_type WHERE q_paper=? AND userID=? GROUP BY screen DESC LIMIT 1");
    $stmt->bind_param('ii', $property_id, $userID);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($sessionid, $current_screen);
    if ($stmt->num_rows == 1) {
      $row = $stmt->fetch();
      $stmt->free_result();
      $restart = 1;
      if ($paper_type == '3') {
        $current_screen = 1;
      } elseif ($current_screen < $no_screens) {
        $current_screen++;
      }
    } else {
      $sessionid = date("YmdHis", time());
    }
    $stmt->close();
  } else {
    $sessionid = date("YmdHis", time());
  }
}

require '../config/start.inc';
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\n\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html>\n<head>\n";
if ($paper_type == '3') {
  echo "<title>" . $string['survey'] . "</title>\n";
} else {
  echo "<title>" . $string['assessment'] . "</title>\n";
}
?>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="imagetoolbar" content="false">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
<meta http-equiv="pragma" content="no-cache" />
<style type="text/css">
body {background-color:<?php echo $bgcolor; ?>;color:<?php echo $fgcolor; ?>;padding:0px;margin:0px;border:0px;font-family:<?php echo $font; ?>,sans-serif;font-size:<?php echo $textsize; ?>%}
li {margin-left:15px;margin-right:15px;font-size:100%}
<?php
if (($bgcolor != 'white' and $bgcolor != '#FFFFFF') or ($fgcolor != 'black' and $fgcolor != '#000000')) {
  echo "select,input{background-color:$bgcolor;color:$fgcolor;font-family:$font,sans-serif;font-size:100%}\n";
} else {
  echo "select,input{font-family:$font,sans-serif;font-size:100%}\n";
}
?>
table {font-size:100%}
p {margin-top:0px; padding-top:0px}
pre {font-family:<?php echo $font; ?>,sans-serif; font-size:100%}
.q_no {width:40px; text-align:right;vertical-align:top}
.theme {font-size:150%; padding-left:4px;font-weight:bold;color:<?php echo $themecolor; ?>}
.note {color:<?php echo $labelcolor; ?>}
.mk {color:<?php echo $marks_color; ?>;font-size:80%}
.act {color:<?php echo $fgcolor; ?>;text-decoration:none}
.inact {color:#A5A5A5;text-decoration:line-through}
.s0 {width:18px;text-align:center;background-color:#003366;font-size:80%}
.s1 {width:18px;text-align:center;background-color:#C00000;font-size:80%}
.unans {background-color:#FFC0C0}
.matrix {border:1px solid #808080; border-collapse:collapse}
.matrix td {border:1px solid #808080}
.extmatch li {padding-bottom:14px; vertical-align:text-bottom; list-style-type:lower-roman}
.paper {margin-left:0px; font-family:Arial,sans-serif; font-size:180%; color:black; font-weight:bold}
<?php
if ($paper_type == '3') echo ".likert_button {text-align:center;width:40px;vertical-align:top}\n";
if ($latex_needed == 1) echo ".latex {vertical-align:middle}\n";
?>
</style>
<script type="text/javascript" src="/js/jquery-1.6.1.min.js"></script>
<?php if ($latex_needed == 1) {?>
  <script type="text/javascript" src="/tools/mee/mee/js/mee_src.js"></script>
<?php }?>
<script language="JavaScript" src="../js/start.js"></script>

<script type="text/javascript">
var lang = {
<?php
$langstrings = array('msgselectable1', 'msgselectable2', 'msgselectable3', 'msgselectable4');
$first = true;
foreach ($langstrings as $langstring) {
  if (!$first) {
    echo ',';
  }
  echo "'{$langstring}':'{$string[$langstring]}'";
  $first = false;
}
?>
};
</script>

<script language="JavaScript" src="../js/flash_include.js"></script>
<script language="javascript">
  window.history.go(1);
<?php
  if ($original_paper_type == '2') {
?>
  function fire(scrno) {
    document.questions.button_pressed.value='previous';
    document.questions.action="fire_evacuation.php?id=<?php echo $_GET['id']; ?>";
    document.questions.submit();
  }
<?php
  }
  if ($bidirectional == '0') {
?>
  var submitted = false;
  function confirmSubmit() {
    if (submitted == true) {
      return false;
    }
    var agree = confirm("<?php echo $string['javacheck1']; ?>");
    if (agree) {
      document.body.style.cursor = 'wait';
      submitted = true;
      return true;
    } else {
      return false;
    }
  }
<?php
  } else {
?>
  var submitted = false;
  function confirmSubmit() {
	saveMath();
	if (submitted == true) {
      return false;
    }
    if (document.questions.button_pressed.value == 'finish') {
      var agree = confirm("<?php echo $string['javacheck2']; ?>");
      if (agree) {
        document.body.style.cursor = 'wait';
        submitted = true;
        return true;
      } else {
        return false;
      }
    } else {
      document.body.style.cursor = 'wait';
      submitted = true;
      return true;
    }
  }
  function jumpScreen() {
    document.questions.button_pressed.value='previous';
    document.questions.action="start.php?id=<?php echo $_GET['id']; ?>";
    if (confirmSubmit()) {
      document.questions.submit();
    }
  }
<?php
  }
?>
</script>
</head>
<?php
if (stripos($userroles,'Student') !== false) {
  echo '<body oncontextmenu="return false;" onload="StartClock();" onunload="KillClock()">';
} else {
  echo '<body onload="StartClock();" onunload="KillClock()">';
}
if ($current_screen < $no_screens) {
  echo "<form method=\"post\" name=\"questions\" action=\"" . $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] . "\"";
} else {
  echo "<form method=\"post\" name=\"questions\" action=\"finish.php?id=" . $_GET['id'] . "\"";
}
echo ' onsubmit="return confirmSubmit()">';   // Warning message only in linear navigation mode.
?>
  <table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr><td valign="top">
<?php
  echo '<tr><td class="raised_tbl"><div class="paper">' . $paper_title . '</div>';
  echo '</td><td align="center" class="raised_tbl" width="167"><img src="../artwork/black_uon_logo.png" width="167" height="70" alt="Logo" border="0" /></td></tr></table>';

  $user_answers = array();
  $previous_duration = 0;

  $old_leadin = '';
  $old_q_type = '';
  $old_q_id = 0;
  $question_no = 0;
  $q_displayed = 0;
  $marks = 0;
  $old_theme = '';
  $previous_q_type = '';

  $question_data = $mysqli->prepare("SELECT q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes, display_pos, q_option_order FROM papers, questions, options WHERE paper=? AND papers.question=questions.q_id AND questions.q_id=options.o_id ORDER BY display_pos, id_num");
  $question_data->bind_param('i', $property_id);
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $display_pos, $q_option_order);
  $num_rows = $question_data->num_rows;
  echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
  echo "<col width=\"40\"><col>\n";
  if ($original_paper_type == 2) {
    if (isset($low_bandwidth) and $low_bandwidth == 1) {
      echo '<tr><td colspan="2"><table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td><span style="text-align:center;font-weight:bold;background-color:#028F43;color:white;cursor:pointer" onclick="fire()" />&nbsp;Fire Exit&nbsp;</span></td><td style="text-align:right"><span style="text-align:center;font-weight:bold;background-color:#028F43;color:white;cursor:pointer" onclick="fire()" />&nbsp;Fire Exit&nbsp;</span></td></tr></table></td></tr>';
    } else {
      echo '<tr><td colspan="2"><table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td><img src="../artwork/fire_exit.png" width="32" height="32" alt="Fire Exit" style="cursor:hand" onclick="fire()" /></td><td style="text-align:right"><img src="../artwork/fire_exit.png" width="32" height="32" alt="Fire Exit" style="cursor:hand" onclick="fire()" /></td></tr></table></td></tr>';
    }
  }
  $q_no = 0;
  //build the questions_array
  $tmp_questions_array = array();
  while ($question_data->fetch()) {
    if ($q_no == 0 or $tmp_questions_array[$q_no]['q_id'] != $q_id or $tmp_questions_array[$q_no]['display_pos'] != $display_pos) {
      $q_no++;
      $tmp_questions_array[$q_no]['theme'] = trim($theme);
      $tmp_questions_array[$q_no]['scenario'] = trim($scenario);
      $tmp_questions_array[$q_no]['leadin'] = trim($leadin);
      $tmp_questions_array[$q_no]['notes'] = trim($notes);
      $tmp_questions_array[$q_no]['q_type'] = $q_type;
      $tmp_questions_array[$q_no]['q_id'] = $q_id;
      $tmp_questions_array[$q_no]['display_pos'] = $display_pos;
      $tmp_questions_array[$q_no]['score_method'] = $score_method;
      $tmp_questions_array[$q_no]['display_method'] = $display_method;
      $tmp_questions_array[$q_no]['q_media'] = $q_media;
      $tmp_questions_array[$q_no]['q_media_width'] = $q_media_width;
      $tmp_questions_array[$q_no]['q_media_height'] = $q_media_height;
      $tmp_questions_array[$q_no]['q_option_order'] = $q_option_order;
      $tmp_questions_array[$q_no]['dismiss'] = '';
      $used_questions[$q_id] = 1;
    }
    $tmp_questions_array[$q_no]['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
  } 
  $question_data->close();
  
  //look for braching and random questions and overwrite as needed
  $questions_array = array();
  $tmp_q_no = 0;
  foreach ($tmp_questions_array as &$question) {
    if ($question['q_type'] != 'info') {
      $tmp_q_no++;
    }
    if ($question['q_type'] == 'random') {
      randomQOverwrite($questions_array,$question,$paper_type,$user_answers,$current_screen,$tmp_q_no);
    } elseif ($question['q_type'] == 'branching') {
      branchingQOverwrite($questions_array,$question,$paper_type,$user_answers,$current_screen);
    } elseif ($question['q_type'] == 'keyword_based') {
      keywordQOverwrite($questions_array,$question,$paper_type,$user_answers,$current_screen,$tmp_q_no);
    } else {
      $questions_array[] = $question;
    }
  }
  unset($tmp_questions_array);
  
  //display the questions
  foreach($questions_array as &$question) {
    if ($q_displayed == 0 and $current_screen == 1 and $paper_prologue != '') echo '<tr><td colspan="2" style="padding:20px; text-align:justify">' . $paper_prologue . '</td></tr>';
    if ($q_displayed == 0 and $question['theme'] == '') echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    display_question($question, $paper_type, $current_screen, $previous_q_type, $question_no, $question_offset, $user_answers);	
    $previous_q_type = $question['q_type'];
    $q_displayed++;
  }
  
  echo "</table></td></tr>\n";

  $mysqli->close();
?>
</table>
</form>
</body>
</html>