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
require_once '../include/errors.php';

//HTML5 part
require_once '../lang/' . $language . '/question/edit/hotspot_correct.php';
require_once '../lang/' . $language . '/question/edit/area.php';
require_once '../lang/' . $language . '/paper/hotspot_answer.php';
require_once '../lang/' . $language . '/paper/hotspot_question.php';
require_once '../lang/' . $language . '/paper/label_answer.php';
$jstring = $string; //to pass it to JavaScript HTML5 modules
//HTML5 part

$paperID = check_var('paperID', 'REQUEST', true, false, true);

$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$rater_query   = '';
$rater_names   = array();
$review_string = '';

if (isset($_GET['reviewers'])) {
  $module = (isset($_GET['module'])) ? $_GET['module'] : '';
  $folder = (isset($_GET['folder'])) ? $_GET['folder'] : '';
  
	$review_string = $_GET['reviewers'];
} else {
  $module = (empty($_POST['module'])) ? '' : $_POST['module'];
  $folder = (empty($_POST['folder'])) ? '' : $_POST['folder'];
  $setterID = (empty($_POST['setterID'])) ? '' : $_POST['setterID'];
  for ($i=1; $i<=100; $i++) {
    if (isset($_POST["member$i"])) {
      $std_setID = $_POST["member$i"];
      if ($rater_query == '') {
        $rater_query = " AND ((std_set.id=$std_setID)";
      } else {
        $rater_query .= " OR (std_set.id=$std_setID)";
      }
			if ($review_string == '') {
				$review_string = $std_setID;
			} else {
				$review_string .= ',' . $std_setID;
			}
    }
  }
	$rater_query .= ')';
}
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


if (isset($_GET['reviewers']) and $_GET['reviewers'] != '') {
  $stmt = $mysqli->prepare("SELECT rating, setterID, method, title, surname, questionID FROM (std_set, std_set_questions, users) WHERE std_set.setterID = users.id AND std_set.id = std_set_questions.std_setID AND std_set.id IN (" . $_GET['reviewers'] . ") ORDER BY std_set, setterID");
  $stmt->execute();
  $stmt->bind_result($rating, $setter_id, $method, $title, $surname, $questionID);
  while ($stmt->fetch()) {
    $tmp_userID = $setter_id;
    $reviews['user'][$tmp_userID][$questionID] = $rating;
    $reviews['user'][$tmp_userID]['name'] = $title . ' ' . $surname;
  }
  $stmt->close();
} else {
  $stmt = $mysqli->prepare("SELECT rating, setterID, method, title, surname, questionID FROM (std_set, std_set_questions, users) WHERE std_set.setterID = users.id AND std_set.id = std_set_questions.std_setID $rater_query ORDER BY std_set, setterID");
  $stmt->execute();
  $stmt->bind_result($rating, $setter_id, $method, $title, $surname, $questionID);
  while ($stmt->fetch()) {
    $tmp_userID = $setter_id;
    $reviews['user'][$tmp_userID][$questionID] = $rating;
    $reviews['user'][$tmp_userID]['name'] = $title . ' ' . $surname;
  }
  $stmt->close();
}

// Get some properties of the paper.
$paper_title		= $propertyObj->get_paper_title();
$paper_type			= $propertyObj->get_paper_type();
$paper_prologue = $propertyObj->get_paper_prologue();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
	<title>Rog&#333;: <?php echo $string['standardssetting'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/start.css" />
  <link rel="stylesheet" type="text/css" href="../css/finish.css" />
  <link rel="stylesheet" type="text/css" href="../css/key.css" />
  <link rel="stylesheet" type="text/css" href="../css/std_setting.css" />
  <style>
		table {table-layout:auto}
		#maincontent {height:auto}
  </style>
  
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
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
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu(98);

?>
  <div id="maincontent">
	<form method="post" name="questions" action="record_review.php?group=true" autocomplete="off">
 
  <?php
  echo "\n<div class=\"head_title\">\n";
  echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
  echo "<div class=\"breadcrumb\"><a href=\"../index.php\">{$string['home']}</a>";
  if ($folder != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } elseif ($module != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $module . '">' . module_utils::get_moduleid_from_id($module, $mysqli) . '</a>';
  }
  echo "<img src=\"../artwork/breadcrumb_arrow.png\" class=\"breadcrumb_arrow\" alt=\"-\" />";

  if($userObject->has_role('Standards Setter')) {
    echo $paper_title;
  } else {
    echo "<a href=\"../paper/details.php?paperID=$paperID&module=$module&folder=$folder\">$paper_title</a>";
  }

  echo "<img src=\"../artwork/breadcrumb_arrow.png\" class=\"breadcrumb_arrow\" alt=\"-\" /><a href=\"./index.php?paperID=$paperID&module=$module&folder=$folder\">{$string['standardssetting']}</a></div>";
  echo '<div class="page_title">' . $string['standardssetting'] . ': ' . $string['angoffmethod'] . ' - ' . $string['groupreview'] . '</div>';
  echo "</div>\n";
?>
  <br />
  <div align="center">
  <table cellpadding="4" cellspacing="0" border="0" width="90%" class="key">
  <tr>
  <td style="margin:0px"><?php echo $string['percentagemsg'] ?><br /><br /><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="!" /><?php echo $string['warningmsg'] ?></td>
  </tr>
  </table>
<?php
if (isset($reviews['user']) and count($rater_names) > count($reviews['user'])) {
?>
  </div>
  <div class="key"><?php echo $string['changedmsg']; ?></div>
  <br />
<?php
}
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

echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"text-align:left\">\n";

while ($stmt->fetch()) {
  if ($prologue_show == 1 and $paper_prologue != '') {
    echo '<tr><td colspan="2" style="padding:20px; text-align:justify">' . $paper_prologue . '</td></tr>';
    $prologue_show = 0;
  }
  
  if ($question_no == 0) echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
  if ($old_q_id != $q_id) {          // New Question
    // Print the options of the previous question
    $li_set = 0;
    if ($old_leadin != '') {
      if ($li_set == 1) echo "</td></tr>\n";
      $excluded = $exclusions->get_exclusions_by_qid($old_q_id);
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

// Print the options for the last question on the screen.
$excluded = $exclusions->get_exclusions_by_qid($old_q_id);
display_options($old_screen, $options_array, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_notes, $paper_type, 'modified_angoff', $reviews, $excluded, true);

echo '</td></tr></table>';
echo '<br />';
echo '<input type="hidden" name="module" value="' . $module . '" />';
echo '<input type="hidden" name="folder" value="' . $folder . '" />';
echo '<input type="hidden" name="paperID" value="' . $paperID . '" />';
echo '<input type="hidden" name="setterID" value="' . $setterID . '" />';
echo '<input type="hidden" name="review_string" value="' . $review_string . '" />';
if (isset($_GET['std_setID'])) {
	echo '<input type="hidden" name="std_setID" value="' . $_GET['std_setID'] . '" />';
}
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