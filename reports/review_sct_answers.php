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
require_once '../include/errors.php';

$paperID = check_var('paperID', 'REQUEST', true, false, true);

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$paper_title = $properties->get_paper_title();

function saveResponseData($optID, $experts, $max_experts, $db) {
  $marks = ($max_experts > 0) ? $experts / $max_experts : 0;
  $stmt = $db->prepare("UPDATE options SET correct = ?, marks_correct = ? WHERE id_num = ?");
  $stmt->bind_param('sdi', $experts, $marks, $optID);
  $stmt->execute();
  $stmt->close();
}

function display_question($question, &$question_no, $reviews, &$string, $db) {
  $question_no++;
  $configObj = Config::get_instance();
  $questiondata = \questiondata::get_datastore('sct');
  $render = new render($configObj);
  if ($question['scenario'] != '') {
    echo "<tr><td class=\"q_no\">" . $question_no . ".&nbsp;</td><td style=\"background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold; padding:2px; color:#000040\">{$string['clinicalvignette']}</td></tr>\n";
    echo '<tr><td style="vertical-align:top; text-align:right"></td><td>';
    if ($question['notes'] != '') echo '<p class="note"><img src="../artwork/notes_icon.gif" width="16" height="16" alt="' . ucwords($string['note']) . '" />&nbsp;<strong>' . $string['note'] . ':</strong>&nbsp;' . $question['notes'] . '</p>';
    echo $question['scenario'] . "<br />\n<br />";
    $li_set = 1;
  }
  if ($question['q_media'] != '') {
    if ($li_set == 0) {
      echo '<tr><td class="q_no">' . $question_no . '.&nbsp;</td><td>';
    }
    echo '<div class="mediadiv">';
    $questiondata->set_media($question['q_media'], $question['q_media_width'], $question['q_media_height'], '');
    $render->render($questiondata, $string, 'paper/media.html');
    echo "</div>\n";
    $li_set = 1;
  }

  $sct_parts = explode('~',$question['leadin']);
  echo '<table cellpadding="2" cellspacing="0" border="0" style="width:100%">';
  $sct_titles = array(1 => $string['hypothesis'], 2 => $string['investigation'], 3 => $string['prescription'], 4 => $string['intervention'], 5 => $string['treatment']);
  echo "<tr><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">" . $sct_titles[$question['display_method']] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">{$string['newinformation']}</td></tr>\n";
  echo "<tr><td style=\"width:49%; vertical-align:top\">" . $sct_parts[0] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; vertical-align:top\">" . $sct_parts[1] . "</td></tr>\n";
  echo "</table>\n";

  echo '<p><strong>';
  echo $string['thenthis'] . ' ';
  echo mb_strtolower($sct_titles[$question['display_method']], 'UTF-8');
  echo ' ' . $string['becomes'] . ':';
  echo '</strong></p>';
  echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';

  $no_experts = 0;
  $max_experts = 0;
  for ($i=1; $i<=count($question['options']); $i++) {
    if (isset($reviews[$question['q_id']][$i])) {
      $no_experts += $reviews[$question['q_id']][$i];
      if($reviews[$question['q_id']][$i] > $max_experts) $max_experts = $reviews[$question['q_id']][$i];
    }
  }

  $part_id = 0;
  foreach ($question['options'] as $optionID => $option_text) {
    $part_id++;
    echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td style=\"color:#808080\">";
    if (isset($reviews[$question['q_id']][$part_id])) {
      $review_no = $reviews[$question['q_id']][$part_id];
    } else {
      $review_no = 0;
    }
    echo $review_no . ' ' . $string['outof'] . ' ' . $no_experts;
    echo "</td><td>$option_text</td></tr>\n";
    if (isset($_POST['submit'])) {
      saveResponseData($optionID, $review_no, $max_experts, $db);
    }
  }
  echo "</table>\n</blockquote>\n";

  echo "<span style=\"color:#808080\">{$string['briefreasonwhy']}</span><br /><ul>";
  if (isset($reviews[$question['q_id']]) and count($reviews[$question['q_id']]['reason']) > 0) {
    foreach($reviews[$question['q_id']]['reason'] as $comment) {
      if (trim($comment) != '') {
        echo "<li>$comment</li>\n";
      }
    }
  } else {
      echo "<li>{$string['nocomments']}</li>\n";
  }
  echo "</ul></td></tr>\n";
  echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title($string['sctresponses']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    li {margin-left:15px;margin-right:15px;font-size:100%}
    pre {font-family:Arial,sans-serif; font-size:100%}
    .q_no {width:40px; text-align:right;vertical-align:top}
    .theme {font-size:150%; padding-left:4px;font-weight:bold;color:#316AC5}
    .note {color:#C00000}
    .mk {color:#808080;font-size:80%}
    .h {background-color:#F1F5FB; color:black}
  </style>

  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
?>
<div id="content">

<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a>
<?php
    if (isset($_GET['folder']) and $_GET['folder'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper_title . '</a></div>';
?>
  <div class="page_title"><?php echo $string['sctresponses'] ?></div>
</div>

<table cellspacing="0" cellpadding="2" border="0" style="width:100%">
<col width="40"><col>
<?php
  //Capture reviewer data
  $reviewer_data = array();
  $reviewer_list = array();

  $stmt = $mysqli->prepare("SELECT reviewer_name, q_id, answer, reason FROM sct_reviews WHERE paperID = ?");
  $stmt->bind_param('i', $paperID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($reviewer_name, $q_id, $answer, $reason);
  while ($stmt->fetch()) {
    if (isset($reviewer_data[$q_id][$answer])) {
      $reviewer_data[$q_id][$answer]++;
    } else {
      $reviewer_data[$q_id][$answer]=1;
    }
    $reviewer_data[$q_id]['reason'][$reviewer_name] = $reason;
  }
  $stmt->close();

  // Build the questions_array
  $old_q_id = '';
  $q_no = 0;
  $question_no = 0;

  $stmt = $mysqli->prepare("SELECT q_id, theme, leadin, scenario, notes, display_method, q_media, q_media_width, q_media_height, q_option_order, option_text, id_num FROM (papers, questions, options) WHERE papers.paper=? AND papers.question=questions.q_id AND questions.q_id=options.o_id AND q_type='sct' ORDER BY display_pos, id_num");
  $stmt->bind_param('i', $paperID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($q_id, $theme, $leadin, $scenario, $notes, $display_method, $q_media, $q_media_width, $q_media_height, $q_option_order, $option_text, $id_num);
  while ($stmt->fetch()) {
    if ($old_q_id != $q_id) {
      $q_no++;
      $questions_array[$q_no]['theme'] = trim($theme);
      $questions_array[$q_no]['scenario'] = trim($scenario);
      $questions_array[$q_no]['leadin'] = trim($leadin);
      $questions_array[$q_no]['notes'] = trim($notes);
      $questions_array[$q_no]['q_id'] = $q_id;
      $questions_array[$q_no]['display_method'] = $display_method;
      $questions_array[$q_no]['q_media'] = $q_media;
      $questions_array[$q_no]['q_media_width'] = $q_media_width;
      $questions_array[$q_no]['q_media_height'] = $q_media_height;
      $questions_array[$q_no]['q_option_order'] = $q_option_order;
    }
    $questions_array[$q_no]['options'][$id_num] = $option_text;

    $old_q_id = $q_id;
  }
  $stmt->close();

  // Display the questions
  foreach($questions_array as &$question) {
    if ($question['theme'] == '') echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    display_question($question, $question_no, $reviewer_data, $string, $mysqli);
  }
?>
</table>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?paperID=' . $paperID; ?>" autocomplete="off">
<div style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['savetobank'] ?>" class="ok" style="width:250px" /></div>
</form>
</div>
    
</body>
</html>
