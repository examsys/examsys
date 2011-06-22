<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/media.inc';
require 'angoff_group_functions.inc';

$rater_query = '';
$rater_names = array();
$review_string = '';
if (isset($_GET['reviewers'])) {
  $paperID = $_GET['paperID'];
  $module = $_GET['module'];
  $folder = $_GET['folder'];
  $reviews = explode(';',$_GET['reviewers']);
  $row_no = 0;
  foreach ($reviews as $individual_review) {
    $parts = explode(',',$individual_review);
    if ($row_no == 0) {
      $setterID = $parts[0];
      $dateID = $parts[1];
      $rater_query = " AND ((setterID=$parts[0] AND std_set=$parts[1])";
    } else {
      if ($rater_query == '') {
        $rater_query = " AND ((setterID=$parts[0] AND std_set=$parts[1])";
      } else {
        $rater_query .= " OR (setterID=$parts[0] AND std_set=$parts[1])";
      }
      $review_string .= ';' . $individual_review;
      $rater_names[] = $parts[0];
    }
    $row_no++;
  }
} else {
  $paperID = $_POST['paperID'];
  $module = (empty($_POST['module'])) ? '' : $_POST['module'];
  $folder = (empty($_POST['folder'])) ? '' : $_POST['folder'];
  $setterID = (empty($_POST['setterID'])) ? '' : $_POST['setterID'];
  $dateID = (empty($_POST['setterID'])) ? '' : $_POST['dateID'];
  for ($i=1; $i<=100; $i++) {
    if (isset($_POST["member$i"])) {
      $review_string .= ';' . $_POST["member$i"];
      $parts = explode(',',$_POST["member$i"]);
      if ($rater_query == '') {
        $rater_query = " AND ((setterID=$parts[0] AND std_set=$parts[1])";
      } else {
        $rater_query .= " OR (setterID=$parts[0] AND std_set=$parts[1])";
      }
      $rater_names[] = $parts[0];
    }
  }
}
$reviews = array();
$review_string = substr($review_string,1);

$hidden_fields = '';
if ($setterID != '') {
  $query_string = "SELECT std_set, rating, questionID FROM standards_setting WHERE paperID=$paperID AND setterID=$setterID AND std_set=$dateID";
  $results = $mysqli->query($query_string);
  while ($row = $results->fetch_assoc()) {
    $questionID = $row['questionID'];
    $reviews[$questionID] = $row['rating'];
  }
  $results->close();
  
  $query_string = "SELECT question, std FROM (papers, questions) WHERE paper=$paperID AND papers.question=questions.q_id";
  $results = $mysqli->query($query_string);
  while ($row = $results->fetch_assoc()) {
    $hidden_fields .= "<input type=\"hidden\" name=\"old" . $row['question'] . "\" value=\"" . $row['std'] . "\" />";
  }
  $results->close();
}

if ($rater_query != '') {
  $query_string = "SELECT std_set, rating, setterID, method, title, initials, surname, questionID FROM (standards_setting, users) WHERE standards_setting.setterID=users.id AND paperID=$paperID $rater_query) ORDER BY std_set, setterID";
  $results = $mysqli->query($query_string);
  while ($row = $results->fetch_assoc()) {
    $tmp_userID = $row['setterID'];
    $questionID = $row['questionID'];
    $reviews[$tmp_userID][$questionID] = $row['rating'];
    $reviews[$tmp_userID]['name'] = $row['title'] . ' ' . $row['surname'];
  }
  $results->close();
}

// Get how many screens make up the question paper.
if (!isset($no_screens)) {
  $screen_data = array();
  $paper_properties = $mysqli->query("SELECT DISTINCT paper_title, paper_type, paper_prologue, marking, screen, leadin, start_date, end_date, bgcolor, fgcolor, themecolor, labelcolor, bidirectional FROM (properties, papers, questions) WHERE papers.question=questions.q_id AND properties.property_id=papers.paper AND paper=$paperID ORDER BY screen, display_pos");
  if (!$paper_properties) {
    echo $mysqli->error;
    $mysqli->close();
    exit();
  }
  while ($row = $paper_properties->fetch_assoc()) {
    $no_screens = strval($row['screen']);
    $screen_data[$no_screens] = (isset($screen_data[$no_screens])) ? $screen_data[$no_screens] + 1 : 1;
    $bgcolor = $row['bgcolor'];
    $fgcolor = $row['fgcolor'];
    $themecolor = $row['themecolor'];
    $labelcolor = $row['labelcolor'];
    $bidirectional = $row['bidirectional'];
    $marking = $row['marking'];
    $paper_type = $row['paper_type'];
    $paper_prologue = $row['paper_prologue'];
    $paper_title = $row['paper_title'];
  }
  $paper_properties->close();
  $current_screen = 1;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>Standards Setting</title>
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="imagetoolbar" content="false">
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style type="text/css">
  body {background-color:<?php echo $bgcolor; ?>; color:<?php echo $fgcolor; ?>; padding:0px; margin:0px; border:0px; font-family:Arial,sans-serif; font-size:90%}
  li {margin-left:15px; margin-right:15px; font-family:Arial,sans-serif; font-size:100%}
  select, input {font-size:100%}
  table {font-size:100%}
  .raised_tbl {background-color:#5582D2; border-left:solid #90C8FF 1px; border-right:solid #003060 1px; border-top:solid #90C8FF 1px; border-bottom:solid #003060 1px}
  .paper {margin-left:0px; font-family:Arial,sans-serif; font-size:180%; color:white; font-weight: bold}
  .question_no {width:40px; text-align:right; vertical-align:top}
  .theme {font-size:150%; font-weight:bold; color:<?php echo $themecolor; ?>}
  .notes {font-size:80%; color:<?php echo $labelcolor; ?>}
  .no_marks {color:#808080; font-size:80%}
  .active {color:<?php echo $fgcolor; ?>}
  .inactive {color:#C0C0C0}
  .heading {background-color:#EBEADB; color:black; font-family:Arial,sans-serif}
</style>
<script src="../javascript/staff_help.js" type="text/javascript"></script>

</head>
<body>
	<form method="post" name="questions" action="record_review.php?group=true">
  <table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
  <tr><td valign="top">
  <?php
  echo "\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  echo "<tr><td style=\"background-color:#F1F5FB\"><div class=\"breadcrumb\"><a href=\"../index.php\">Home</a>";
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
  }
  echo "&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"../paper/details.php?paperID=$paperID&module=$module&folder=$folder\">$paper_title</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./index.php?paperID=$paperID&module=$module&folder=$folder\">Standards Setting</a></div>";
  $helpID = 98;
  echo '<div style="font-family:Arial,sans-serif; font-size:200%; color:black; font-weight:bold; margin-left:10px">' . $paper_title . '</div><div style="position:relative; left:12px; top:-3px; font-size:8pt">Standards Setting: Angoff Method - Group review</div>';
  echo "</td><td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp($helpID); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n";
  echo "<tr style=\"height:4px\"><td colspan=\"2\" valign=\"top\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n</table>\n";
?>
  <br />
  <div align="center">
  <table cellpadding="4" cellspacing="0" border="0" width="90%" style="background-color:#DFE8FF; border:1px solid #5582D2;">
  <tr>
  <td style="margin:0px">Use the light blue dropdown lists next to each question to indicate the percentage of <strong>borderline</strong> candidates expected to get each question correct.<br /><br /><img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="!" /> = reviews differ by more than 10%</td>
  </tr>
  </table>
  </div>
  <br />
<?php
  $query_string = "SELECT screen, q_type, q_id, score_method, marks, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes FROM papers, questions, options WHERE paper=$paperID AND papers.question=questions.q_id AND questions.q_id=options.o_id ORDER BY display_pos, id_num";
  $question_data = $mysqli->query($query_string);
  $num_rows = $question_data->num_rows;
  $old_leadin = '';
  $old_q_type = '';
  $old_q_id = 0;
  $question_no = 0;
  $old_theme = '';
  $old_screen = 1;
  $question_offset = 1;
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  while ($row = $question_data->fetch_assoc()) {
    if ($question_no == 0 and $current_screen == 1 and $paper_prologue != '') {
      echo '<tr><td colspan="2" style="padding: 20px; text-align:justify">' . $paper_prologue . '</td></tr>';
    }

    if ($question_no == 0) echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    if ($old_q_id != $row['q_id']) {          // New Question
      // Print the options of the previous question
      $li_set = 0;
      if ($old_leadin != '') {
        if ($li_set == 1) echo "</td></tr>\n";
        display_options($options_array, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_notes, $paper_type, 'modified_angoff', $setterID);
        
        if ($old_screen != $row['screen']) {
          echo '<tr><td colspan="2"><table cellpadding="0" cellspacing="1" border="0" style="width:100%; height:70px; border-top:1px solid #B5C4DF; background-image:url(\'../artwork/screen_no_background.gif\'); background-repeat:repeat-x">';
          echo "<tr>\n<td width=\"20\">&nbsp;</td>\n";
          echo "<td style=\"vertical-align:top; font-size:90%; font-weight:bold; color:#15428B\">Screen&nbsp;" . $row['screen'] . "</td>\n</tr>\n";
          echo '</table></td></tr>';
        }
      }
      if (($old_q_type == 'likert' and $row['q_type'] != 'likert') or ($old_q_type != 'likert' and $row['q_type'] == 'likert')) echo "</table>\n<br />\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";

      if ($row['theme'] != '') {
        if ($old_q_type == 'likert') echo '</table><br /><table cellpadding="4" cellspacing="0" border="0" width="100%">';  // Close off table if last question was likert scale.
        echo '<tr><td class="question_no">&nbsp;</td><td><p class="theme">' . $row['theme'] . '</p></td></tr>';
      }

      if ($row['notes'] != '' and $row['q_type'] != 'likert') echo '<tr><td></td><td class="notes"><img src="notes_icon.gif" width="14" height="14" alt="Note" />&nbsp;<strong>NOTE:</strong>&nbsp;' . $row['notes'] . '</td></tr>';

      if ($row['scenario'] != '' and $row['q_type'] != 'extmatch' and $row['q_type'] != 'matrix' and $row['q_type'] != 'likert') {
        echo '<tr><td class="question_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td valign="top"><p>' . $row['scenario'] . '</p>';
        $li_set = 1;
      }
      if ($row['q_media'] != '' and $row['q_media'] != NULL and $row['q_type'] != 'hotspot' and $row['q_type'] != 'labelling' and $row['q_type'] != 'flash' and $row['q_type'] != 'extmatch') {
        if (substr($row['q_media'], -4) == '.gif' or substr($row['q_media'], -4) == '.jpg' or substr($row['q_media'], -4) == 'jpeg' or substr($row['q_media'], -4) == '.png') {
          if ($li_set == 0) echo '<tr><td class="question_no">' . $question_no . '.&nbsp;</td><td>';
          $li_set = 1;
          echo "<p align=\"center\">" . display_media($row['q_media'],$row['q_media_width'],$row['q_media_height'],$question_no) . "</p>\n";
        } else {
          if ($li_set == 0) {
            echo '<tr><td class="question_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td>';
          }
          $li_set = 1;
          echo "<p>" . display_media($row['q_media'],$row['q_media_width'],$row['q_media_height'],$question_no) . "</p>\n";
        }
      }
      if ($row['q_type'] != 'likert') {
        if ($li_set == 0) {
          echo '<tr><td class="question_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td>';
        }
        $li_set = 1;
        echo '<p>' . $row['leadin'] . '</p>';
      }

      $old_leadin = $row['leadin'];
      $old_scenario = $row['scenario'];
      $old_notes = $row['notes'];
      $old_q_type = $row['q_type'];
      $old_q_id = $row['q_id'];
      $old_theme = $row['theme'];
      $old_screen = $row['screen'];
      $options_array = array();          // Clear options array
      $question_no++;
    }

    $options_array[] = $row;
  }         // End of While loop
  $question_data->close();

  // Print the options for the last question on the screen.
  display_options($options_array, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_notes, $paper_type, 'modified_angoff', $setterID);

  echo '</td></tr></table></td></tr>';
  echo "<tr><td colspan=\"2\" style=\"border-top:dotted #808080 1px; color:#808080; font-size:90%; font-weight:bold\">&nbsp;</td>\n</tr>\n";
  echo '</table>';
  echo '<input type="hidden" name="module" value="' . $module . '" />';
  echo '<input type="hidden" name="folder" value="' . $folder . '" />';
  echo '<input type="hidden" name="paperID" value="' . $paperID . '" />';
  echo '<input type="hidden" name="setterID" value="' . $setterID . '" />';
  echo '<input type="hidden" name="dateID" value="' . $dateID . '" />';
  echo '<input type="hidden" name="review_string" value="' . $review_string . '" />';
  echo "<input type=\"hidden\" name=\"method\" value=\"Modified Angoff\" />\n";
  $mysqli->close();
?>
<div align="center">
<input type="checkbox" name="alterpassmark" value="1" checked /> Update paper pass mark<br />
<input type="submit" name="submit" value="Save Ratings" style="width:150px" />&nbsp;<input onclick="javascript: history.back()" type="button" name="cancel" value="Cancel" style="width:100px" />
</div>
<br />
<?php echo $hidden_fields ?>
</form>
</body>
</html>