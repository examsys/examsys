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
* Standards Setting report in CSV format.
*
* @author Richard Whitefoot (UEA)
* @version 1.0
* @package
*/
/*
require '../include/staff_auth.inc';
require_once '../include/errors.inc';
require '../include/std_set_functions.inc';
require_once '../include/std_set_shared_functions.inc';
require_once '../classes/paperproperties.class.php';
*/

require '../include/staff_auth.inc';
require '../include/media.inc';
require '../include/std_set_functions.inc';
require_once '../include/errors.inc';
require_once '../classes/exclusion.class.php';
require_once '../classes/paperproperties.class.php';

$displayDebug = false; //disable debud output in this script as it effects the output

/**
 * Output standards setting review line in CSV format
 * @param array $review - review details
 * @param array $string - language settings
 * @return string
 */
function displayReviewCsv($review, $string) {

  if ($review['review_total'] == $review['total_marks']) {
    $rowOutcome = 'Ok';
  } else {
    $rowOutcome = 'Review Total != Total Marks';
  }

  if ($review['group_review'] != 'No') {
    $rowOutcome = "Group review";
  }
  
  $output = '';
  $output = addslashes($rowOutcome) . ",";

  if ($review['distinction_score'] != 'n/a') {
    $review['distinction_score'] .= '%';
  }

  if ($review['group_review'] != 'No') {
    $output .= "Group review,";
  } else {
    $output .= addslashes($review['name']) . ",";
  }
  if ($review['distinction_score'] == '0.000000%') {
    $review['distinction_score'] = 'top 20%';
  }

  $output .= addslashes($review['display_date']) . ",";
  $output .= addslashes($review['pass_score']) . ",";
  $output .= addslashes($review['distinction_score']) . ",";
  $output .= addslashes($review['review_total']) . ",";
  $output .= addslashes($review['total_marks']) . ",";
  $output .= addslashes($review['method']) . ",";

  $output .= "\n";

  return $output;
}

$paperID    = check_var('paperID', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($_GET['paperID'], $mysqli, $string);

$paper_title    = $propertyObj->get_paper_title();
$paper_type     = $propertyObj->get_paper_type();
$paper_prologue = $propertyObj->get_paper_prologue();

$total_mark   = $propertyObj->get_total_mark();

$reviews = get_reviews($mysqli, 'index', $paperID, $total_mark, $no_reviews);

$csv = '';

#header('Pragma: public');
#header("Content-type: application/vnd.ms-excel");
#header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $_GET['paperID']) . "_standards_setting.csv");

$percent_decimals = $configObject->get('percent_decimals');

if (is_array($reviews)) {

  $csv .= addslashes($string['validate']) . ",";
  $csv .= addslashes($string['standardsetter']) . ",";
  $csv .= addslashes($string['date']) . ",";
  $csv .= addslashes($string['passscore']) . ",";
  $csv .= addslashes($string['distinction']) . ",";
  $csv .= addslashes($string['reviewmarks']) . ",";
  $csv .= addslashes($string['papertotal']) . ",";
  $csv .= addslashes($string['method']) . ",";
  $csv .= "\n";

  $csv .= ",,,,,,,\n";

  foreach ($reviews as $review) {
    $csv .= displayReviewCsv($review, $string);

    echo "<pre>";
    print_r($review);
    echo "</pre>";
  }


##########

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

echo "<p>paperID : " . $paperID . "</p>";

$stmt = $mysqli->prepare("SELECT screen, q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes FROM papers, questions, options WHERE paper=? AND papers.question=questions.q_id AND questions.q_id=options.o_id ORDER BY display_pos, id_num");
$stmt->bind_param('i', $paperID);
$stmt->execute();
$stmt->store_result();
$num_rows = $stmt->num_rows;
$stmt->bind_result($screen, $q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes);  

#echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"text-align:left\">\n";

while ($stmt->fetch()) {

echo "<br>screen : " . $screen;
echo "<br>q_type : " . $q_type;
echo "<br>q_id : " . $q_id;
echo "<br>score_method : " . $score_method;
echo "<br>display_method : " . $display_method;
echo "<br>marks_correct : " . $marks_correct;
echo "<br>marks_incorrect : " . $marks_incorrect;
echo "<br>marks_partial : " . $marks_partial;
echo "<br>theme : " . $theme;
echo "<br>scenario : " . $scenario;
echo "<br>leadin : " . $leadin;
echo "<br>correct : " . $correct;
echo "<br>option_text : " . $option_text;
echo "<br>q_media : " . $q_media;
echo "<br>q_media_width : " . $q_media_width;
echo "<br>q_media_height : " . $q_media_height;
echo "<br>o_media : " . $o_media;
echo "<br>o_media_width : " . $o_media_width;
echo "<br>o_media_height : " . $o_media_height;
echo "<br>notes : " . $notes;
 # if ($prologue_show == 1 and $paper_prologue != '') {
 #   echo '<tr><td colspan="2" style="padding:20px; text-align:justify">' . $paper_prologue . '</td></tr>';
 #   $prologue_show = 0;
 # }
  echo "line 160";
 # if ($question_no == 0) echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
  if ($old_q_id != $q_id) {          // New Question
    // Print the options of the previous question
    $li_set = 0;
    if ($old_leadin != '') {
    #  if ($li_set == 1) echo "</td></tr>\n";
      $excluded = $exclusions->get_exclusions_by_qid($old_q_id);

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

### @TO DO: CHECK DB STRUCTURE DIRECT IF CAN'T GET WORKING
   #   exit();
  echo "line 185";

      display_options($old_screen, $options_array, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_notes, $paper_type, 'modified_angoff', $reviews, $excluded, true);
      
      if ($old_screen != $screen) {
        echo '<tr><td colspan="2">';
        echo '<div class="screenbrk"><span class="scr_no">' . $string['screen'] . '&nbsp;' . $screen . '</span></div>';
        echo '</td></tr>';
      }
    }
    $question_no++;
    if (($old_q_type == 'likert' and $q_type != 'likert') or ($old_q_type != 'likert' and $q_type == 'likert')) echo "</table>\n<br />\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";

    if ($theme != '') {
      if ($old_q_type == 'likert') echo '</table><br /><table cellpadding="4" cellspacing="0" border="0" width="100%">';  // Close off table if last question was likert scale.
      echo '<tr><td colspan="2" class="theme">' . $theme . '</td></tr>';
    }

    if ($notes != '' and $q_type != 'likert') echo '<tr><td></td><td class="notes"><img src="notes_icon.gif" width="16" height="16" alt="' . ucwords($string['note']) . '" />&nbsp;<strong>' . $string['note'] . ':</strong>&nbsp;' . $notes . '</td></tr>';

    if ($scenario != '' and $q_type != 'extmatch' and $q_type != 'matrix' and $q_type != 'likert') {
      echo '<tr><td class="q_no">' . $question_no . '.&nbsp;</td><td valign="top">' . $scenario . '<br /><br />';
      $li_set = 1;
    }
    if ($q_media != '' and $q_media != NULL and $q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'flash' and $q_type != 'extmatch') {
      if (substr($q_media, -4) == '.gif' or substr($q_media, -4) == '.jpg' or substr($q_media, -4) == 'jpeg' or substr($q_media, -4) == '.png') {
        if ($li_set == 0) echo '<tr><td class="q_no">' . $question_no . '.&nbsp;</td><td>';
        $li_set = 1;
        echo "<p align=\"center\">" . display_media($q_media, $q_media_width, $q_media_height, '') . "</p>\n";
      } else {
        if ($li_set == 0) {
          echo '<tr><td class="q_no">' . $question_no . '.&nbsp;</td><td>';
        }
        $li_set = 1;
        echo "<p>" . display_media($q_media, $q_media_width, $q_media_height, '') . "</p>\n";
      }
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

##########




} else {
  $csv .= strip_tags($string['nostandardsset']);
}

echo mb_convert_encoding($csv, "UTF-16LE", "UTF-8");

$mysqli->close();
?>