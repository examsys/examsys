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
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/print_functions.inc';
require '../include/media.inc';
require '../config/index.inc';
require_once '../include/errors.inc';

check_var('id', 'GET', true, false);

function randomQOverwrite(&$questions, $random_q_data, $paper_type, $user_answers, $current_screen, $q_no) {
  global $mysqli, $used_questions;
 
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

function branchingQOverwrite(&$questions,$branching_q_data,$paper_type,$user_answers,$current_screen,$userObject) {
  global $mysqli, $DISABLEDuserID, $sessionid;
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
      $stmt->bind_param('iisi',$userObject->get_user_ID(), $current_screen, $sessionid, $op_qid);
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

// Get how many screens make up the question paper.
$screen_data = array();
$row_no = 0;
$stmt = $mysqli->prepare("SELECT property_id, labs, paper_title, paper_type, paper_prologue, marking, screen, UNIX_TIMESTAMP(start_date), UNIX_TIMESTAMP(end_date), bgcolor, fgcolor, themecolor, labelcolor, bidirectional, calendar_year, latex_needed, password FROM (properties, papers, questions) WHERE properties.property_id=papers.paper AND crypt_name=? AND papers.question=questions.q_id AND q_type != 'info' ORDER BY screen");
$stmt->bind_param('s', $_GET['id']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($property_id, $labs, $paper_title, $paper_type, $paper_prologue, $marking, $screen, $start_date, $end_date, $paper_bgcolor, $paper_fgcolor, $paper_themecolor, $paper_labelcolor, $bidirectional, $calendar_year, $latex_needed, $password);
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
  }
}
$stmt->free_result();
$stmt->close();

$current_screen = 1;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
<head>
  <?php
  if ($paper_type == '3') {
    echo "<title>" . $string['survey'] . "</title>\n";
  } else {
    echo "<title>" . $string['assessment'] . "</title>\n";
  }
  ?>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="imagetoolbar" content="no">
  <meta http-equiv="imagetoolbar" content="false">
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_page_charset ?>" />
  <meta http-equiv="pragma" content="no-cache" />

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/print.css" />

  <style type="text/css">
  <?php
    if (isset($_GET['break']) and $_GET['break'] == 1) {
      echo ".qtable {page-break-after:always}\n";
    } else {
      echo ".qtable {page-break-after:auto}\n";
    }
  ?>
  </style>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
  <script type="text/javascript" src="../js/start.js"></script>
  <script type="text/javascript" src="../js/flash_include.js"></script>
  <script language="JavaScript">
    $(document).ready(function() {
      window.print();
    });
  </script>
</head>
<body>#

  <table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr><td valign="top">
<?php
  echo '<tr><td><div class="paper">' . $paper_title . '</div>';
  echo '</td><td align="right" width="167">' . $logo_html . '</td></tr></table>';

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
      branchingQOverwrite($questions_array,$question,$paper_type,$user_answers,$current_screen,$userObject);
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
  
  $mysqli->close();
?>

</body>
</html>