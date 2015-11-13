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
require '../include/media.inc';
require '../include/std_set_functions.inc';
require_once '../include/errors.inc';
require_once '../classes/exclusion.class.php';
require_once '../classes/paperproperties.class.php';

//HTML5 part
require_once '../lang/' . $language . '/question/edit/hotspot_correct.txt';
require_once '../lang/' . $language . '/question/edit/area.txt';
require_once '../lang/' . $language . '/paper/hotspot_answer.txt';
require_once '../lang/' . $language . '/paper/hotspot_question.txt';
require_once '../lang/' . $language . '/paper/label_answer.txt';
$jstring = $string; //to pass it to JavaScript HTML5 modules
//HTML5 part

$paperID = check_var('paperID', 'REQUEST', true, false, true);

$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$rater_query   = '';
$rater_names   = array();
$review_string = '';

$reviews = array();


$setterID = '';
if (isset($_GET['std_setID'])) {    // Load a pre-existing group set
  $result = $mysqli->prepare("SELECT rating, questionID FROM std_set_questions WHERE std_setID = ?");
  $result->bind_param('i', $_GET['std_setID']);
  $result->execute();
  $result->bind_result($rating, $questionID);
  while ($result->fetch()) {
    $reviews[$questionID] = $rating;
  }
  $result->close();
}

echo "<pre>";
print_r($_GET);
echo "</pre>";

#if (isset($_GET['reviewers']) and $_GET['reviewers'] != '') {
  $stmt = $mysqli->prepare("SELECT rating, setterID, method, title, surname, questionID FROM (std_set, std_set_questions, users) WHERE std_set.setterID = users.id AND std_set.id = std_set_questions.std_setID AND std_set.paperID = " . $paperID. " ORDER BY std_set, setterID");
  $stmt->execute();
  $stmt->bind_result($rating, $setter_id, $method, $title, $surname, $questionID);
  while ($stmt->fetch()) {
    $tmp_userID = $setter_id;
    $reviews['user'][$tmp_userID][$questionID] = $rating;
    $reviews['user'][$tmp_userID]['name'] = $title . ' ' . $surname;
  }
  $stmt->close();

// Get some properties of the paper.
$paper_title    = $propertyObj->get_paper_title();
$paper_type     = $propertyObj->get_paper_type();
$paper_prologue = $propertyObj->get_paper_prologue();

// Get any questions to exclude.
$exclusions = new Exclusion($paperID, $mysqli);
$exclusions->load();

$old_leadin       = '';
$old_q_type       = '';
$old_theme        = '';
$old_q_id         = 0;
$question_no      = 0;
$old_screen       = 1;
$prologue_show    = 1;

$stmt = $mysqli->prepare("SELECT screen, q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes FROM papers, questions, options WHERE paper=? AND papers.question=questions.q_id AND questions.q_id=options.o_id ORDER BY display_pos, id_num");
$stmt->bind_param('i', $paperID);
$stmt->execute();
$stmt->store_result();
$num_rows = $stmt->num_rows;
$stmt->bind_result($screen, $q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes);  

#echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"text-align:left\">\n";

while ($stmt->fetch()) {
 # if ($prologue_show == 1 and $paper_prologue != '') {
   # echo '<tr><td colspan="2" style="padding:20px; text-align:justify">aaaaaaa' . $paper_prologue . '</td></tr>';
   # $prologue_show = 0;
  #}
  
  if ($question_no == 0) echo "\n";
  if ($old_q_id != $q_id) {          // New Question
    // Print the options of the previous question
    $li_set = 0;
    if ($old_leadin != '') {
      if ($li_set == 1) echo "</td></tr>\n";
      $excluded = $exclusions->get_exclusions_by_qid($old_q_id);
     # display_options($old_screen, $options_array, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_notes, $paper_type, 'modified_angoff', $reviews, $excluded, true);

echo "<p><h1>LINE 120</h1></p>";
      echo "<br>1:" . $old_screen;
      echo "<br>2:<pre>";
      print_r($options_array);
      echo "</pre><br>3:" . $old_q_id;
      echo "<br>4:" . $old_theme;
      echo "<br>5:" . $old_scenario;
      echo "<br>6:" . $old_leadin;
      echo "<br>7:" . $old_notes;
      echo "<br>8:" . $paper_type;
      echo "<br>9:" . 'modified_angoff';
      echo "<br>10:<pre>";
      print_r($reviews);
      echo "</pre><br>11:" . $excluded;


    }
    $question_no++;

    if (($old_q_type == 'likert' and $q_type != 'likert') or ($old_q_type != 'likert' and $q_type == 'likert')) {
      echo "\n";
    }

    if ($theme != '') {
      if ($old_q_type == 'likert') echo '\n';  // Close off table if last question was likert scale.
      echo $theme . "\n";
    }

    if ($notes != '' and $q_type != 'likert') echo '<tr><td></td><td class="notes"><img src="notes_icon.gif" width="16" height="16" alt="' . ucwords($string['note']) . '" />&nbsp;<strong>' . $string['note'] . ':</strong>&nbsp;' . $notes . '</td></tr>';

    if ($scenario != '' and $q_type != 'extmatch' and $q_type != 'matrix' and $q_type != 'likert') {
      echo '<tr><td class="q_no">' . $question_no . '.&nbsp;</td><td valign="top">' . $scenario . '<br /><br />';
      $li_set = 1;
    }

    if ($q_type != 'likert' and $q_type != 'calculation' and $q_type != 'info') {
      if ($li_set == 0) {
        echo '<tr><td class="q_no">' . $question_no . '.&nbsp;</td><td>';
      }
      $li_set = 1;
      echo $leadin;
    }
    if ($q_type == 'info') {
      if ($li_set == 0) echo '<tr><td colspan="2" style="padding-left:20px; padding-right:20px">' . $leadin;
      $li_set = 1;
      $question_no--;
    }
  
    $old_leadin     = $leadin;
    $old_scenario   = $scenario;
    $old_notes      = $notes;
    $old_q_type     = $q_type;
    $old_q_id       = $q_id;
    $old_theme      = $theme;
    $old_screen     = $screen;
    $options_array  = array();          // Clear options array
  }

  $options_array[] = array('q_type'=>$q_type, 'score_method'=>$score_method, 'display_method'=>$display_method, 'correct'=>$correct, 'scenario'=>$scenario, 'q_media'=>$q_media, 'q_media_width'=>$q_media_width, 'q_media_height'=>$q_media_height, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
}         // End of While loop
$stmt->close();

// Print the options for the last question on the screen.
$excluded = $exclusions->get_exclusions_by_qid($old_q_id);
#display_options($old_screen, $options_array, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_notes, $paper_type, 'modified_angoff', $reviews, $excluded, true);
echo "<p><h1>LINE 185</h1></p>";
echo "<br>1:" . $old_screen;
      echo "<br>2:<pre>";
      print_r($options_array);
      echo "</pre><br>3:" . $old_q_id;
      echo "<br>4:" . $old_theme;
      echo "<br>5:" . $old_scenario;
      echo "<br>6:" . $old_leadin;
      echo "<br>7:" . $old_notes;
      echo "<br>8:" . $paper_type;
      echo "<br>9:" . 'modified_angoff';
      echo "<br>10:<pre>";
      print_r($reviews);
      echo "</pre><br>11:" . $excluded;

$mysqli->close();
?>