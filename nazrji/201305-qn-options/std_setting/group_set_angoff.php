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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/media.inc';
require '../include/std_set_functions.inc';
require_once '../include/errors.inc';

$paperID = check_var('paperID', 'REQUEST', true, false, true);

$rater_query   = '';
$rater_names   = array();
$review_string = '';

if (isset($_GET['reviewers'])) {
  $module = (isset($_GET['module'])) ? $_GET['module'] : '';
  $folder = (isset($_GET['folder'])) ? $_GET['folder'] : '';
  $prev_reviews = explode(';',$_GET['reviewers']);
  $row_no = 0;
  foreach ($prev_reviews as $individual_review) {
    $parts = explode(',',$individual_review);
    if ($row_no == 0) {
      $setterID = $parts[0];
      $dateID = $parts[1];
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

if ($setterID != '') {
  $result = $mysqli->prepare("SELECT std_set, rating, questionID FROM standards_setting WHERE paperID = ? AND setterID = ? AND std_set = ?");
  $result->bind_param('iis', $paperID, $setterID, $dateID);
  $result->execute();
  $result->bind_result($std_set, $rating, $questionID);
  while ($result->fetch()) {
    $questionID = $questionID;
    $reviews[$questionID] = $rating;
  }
  $result->close();
}

if ($rater_query != '') {
  $stmt = $mysqli->prepare("SELECT rating, setterID, method, title, surname, questionID FROM (standards_setting, users) WHERE standards_setting.setterID = users.id AND paperID = ? $rater_query) ORDER BY std_set, setterID");
  $stmt->bind_param('i', $paperID);
  $stmt->execute();
  $stmt->bind_result($rating, $setter_id, $method, $title, $surname, $questionID);
  while($stmt->fetch()) {
    $tmp_userID = $setter_id;
    $reviews['user'][$tmp_userID][$questionID] = $rating;
    $reviews['user'][$tmp_userID]['name'] = $title . ' ' . $surname;
  }
  $stmt->close();
}

// Get how many screens make up the question paper.
if (!isset($no_screens)) {
  $screen_data = array();
  
  $paper_properties = $mysqli->prepare("SELECT DISTINCT paper_title, paper_type, paper_prologue, marking, screen, bgcolor, fgcolor, themecolor, labelcolor, bidirectional FROM (properties, papers, questions) WHERE papers.question=questions.q_id AND properties.property_id=papers.paper AND paper=? ORDER BY screen, display_pos");
  $paper_properties->bind_param('i', $paperID);
  $paper_properties->execute();
  $paper_properties->bind_result($paper_title, $paper_type, $paper_prologue, $marking, $screen, $bgcolor, $fgcolor, $themecolor, $labelcolor, $bidirectional);
  while ($paper_properties->fetch()) {
    $no_screens = strval($screen);
    $screen_data[$no_screens] = (isset($screen_data[$no_screens])) ? $screen_data[$no_screens] + 1 : 1;
  }
  $paper_properties->close();
  $current_screen = 1;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
	<title><?php echo $string['standardssetting'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/start.css" />
  <link rel="stylesheet" type="text/css" href="../css/finish.css" />
  <link rel="stylesheet" type="text/css" href="../css/key.css" />
  <style>
  table {table-layout:auto}
  #maincontent {height:auto}
  </style>
  
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
  <script type="text/javascript" src="../js/ie_fix.js"></script>
  <script type="text/javascript" src="../js/flash_include.js"></script>
  <script type="text/javascript" src="../js/jquery.flash_q.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
</head>
<body>
  <div id="maincontent">
	<form method="post" name="questions" action="record_review.php?group=true">
  <table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr><td valign="top">
  <?php
  echo "\n<table class=\"header\">\n";
  echo "<tr><th><div class=\"breadcrumb\"><a href=\"../staff/index.php\">{$string['home']}</a>";
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } elseif (isset($_POST['module']) and $_POST['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_POST['module'] . '">' . module_utils::get_moduleid_from_id($_POST['module'], $mysqli) . '</a>';
  }
  echo "&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"../paper/details.php?paperID=$paperID&module=$module&folder=$folder\">$paper_title</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./index.php?paperID=$paperID&module=$module&folder=$folder\">{$string['standardssetting']}</a></div>";
  $helpID = 98;
  echo '<div style="font-size:200%; color:black; font-weight:bold; margin-left:10px">' . $paper_title . '</div><div style="position:relative; left:12px; top:-3px; font-size:90%">' . $string['standardssetting'] . ': ' . $string['angoffmethod'] . ' - ' . $string['groupreview'] . '</div>';
  echo "</th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp($helpID); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"{$string['help']}\" border=\"0\" /></a></th></tr>\n";
  echo "<tr><th colspan=\"2\" class=\"bevel\"></th></tr>\n</table>\n";
?>
  <br />
  <div align="center">
  <table cellpadding="4" cellspacing="0" border="0" width="90%" class="key">
  <tr>
  <td style="margin:0px"><?php echo $string['percentagemsg'] ?><br /><br /><img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="!" /><?php echo $string['warningmsg'] ?></td>
  </tr>
  </table>
<?php
if (count($rater_names) > count($reviews['user'])) {
?>
  </div>
  <div class="key"><?php echo $string['changedmsg']; ?></div>
  <br />
<?php
}
// Get any questions to exclude.
$excluded = array();
$result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper = ?");  // FIXME
$result->bind_param('i', $paperID);
$result->execute();
$result->bind_result($q_id, $parts);
while ($result->fetch()) {
  $excluded[$q_id] = $parts;
}
$result->close();

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

echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"text-align:left\">\n";

while ($stmt->fetch()) {
  if ($prologue_show == 1 and $current_screen == 1 and $paper_prologue != '') {
    echo '<tr><td colspan="2" style="padding:20px; text-align:justify">' . $paper_prologue . '</td></tr>';
    $prologue_show = 0;
  }
  
  if ($question_no == 0) echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
  if ($old_q_id != $q_id) {          // New Question
    // Print the options of the previous question
    $li_set = 0;
    if ($old_leadin != '') {
      if ($li_set == 1) echo "</td></tr>\n";
      display_options($options_array, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_notes, $paper_type, 'modified_angoff', $reviews, $excluded, true);
      
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

    if ($notes != '' and $q_type != 'likert') echo '<tr><td></td><td class="notes"><img src="notes_icon.gif" width="14" height="14" alt="' . ucwords($string['note']) . '" />&nbsp;<strong>' . $string['note'] . ':</strong>&nbsp;' . $notes . '</td></tr>';

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

// Print the options for the last question on the screen.
display_options($options_array, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_notes, $paper_type, 'modified_angoff', $reviews, $excluded, true);

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
<input type="checkbox" name="alterpassmark" value="1" checked /> <?php echo $string['updatepassmark'] ?><br />
<input type="submit" name="submit" value="<?php echo $string['saveratings'] ?>" style="width:150px" />&nbsp;<input onclick="javascript: history.back()" type="button" name="cancel" value="<?php echo $string['cancel'] ?>" style="width:100px" />
</div>
<br />
</form>
</div>
</body>
</html>