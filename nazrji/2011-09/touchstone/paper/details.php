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
* Displays tasks for the papers frame (papers_menu.php).
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

ob_start('ob_gzhandler');
require '../include/staff_auth.inc';
require '../include/question_types.inc';
require '../include/errors.inc';
require '../include/calculate_marks.inc';

check_var('paperID', 'GET', true, false);

$paperID = $_GET['paperID'];

function findDecisionQ($question_array,$sourceID) {
  $source_question_no = 0;
  $tmp_q_no = 0;
  foreach ($question_array as $question) {
    if ($question['type'] != 'info') $tmp_q_no++;
    if ($question['q_id'] == $sourceID) $source_question_no = $tmp_q_no;
  }
  return $source_question_no;
}

function checkProblems($p_type, $q_type, $score_method, &$temp_array, $scenario, $q_media, $row_no, $question_marks, $q_id, $tmp_excluded, $option_text, $correct_array, $status) {
  global $string;

  if (!isset($tmp_excluded) and ($status == 'Normal' or $status == 'Experimental' or $status == 'Beta')) {
    if ($score_method == 'SelectedPositive' and $q_type == 'mrq') {
      if ($question_marks > (count($option_text) / 2)) $temp_array[$row_no]['warnings'] = $string['toomanycorrect'];
    } elseif ($q_type == 'dichotomous') {
      if ($question_marks < count($option_text)) $temp_array[$row_no]['warnings'] = sprintf($string['dichotomouswarning'], $question_marks, count($option_text));
    } elseif ($q_type == 'mcq' and $correct_array[0] == '') {
      $temp_array[$row_no]['warnings'] = $string['nocorrect'];
    } elseif ($q_type == 'calculation' and $correct_array[0] == '') {
      $temp_array[$row_no]['warnings'] = $string['nocorrect'];
    } elseif ($q_type == 'extmatch' or $q_type == 'matrix') {
      $matching_scenarios = explode('|', $scenario);
      $matching_media = explode('|', $q_media);
      $text_scenarios = 0;
      for ($part_id=0; $part_id<count($matching_scenarios); $part_id++) {
        if (trim(strip_tags($matching_scenarios[$part_id])) != '') $text_scenarios++;
      }
      $media_scenarios = 0;
      for ($part_id=1; $part_id<count($matching_media); $part_id++) {
        if ($matching_media[$part_id] != '') $media_scenarios++;
      }
      $scenario_no = max($text_scenarios, $media_scenarios);
      if ($question_marks < $scenario_no) $temp_array[$row_no]['warnings'] = $string['answermissing'];
    }
    if ($q_type == 'mcq' and $score_method == 'vertical_other' and $p_type != '3') {
      $temp_array[$row_no]['warnings'] = $string['mcqsurvey'];
    }
  }
}

function randomDetails($questionID) {
  global $mysqli;

  $question_no = 0;
  $random_questions = array();
  $old_q_id = '';
  $old_score_method = '';
  $old_q_media_width = '';
  $old_q_media_height = '';
  $old_correct = array();
  $old_option_text = array();

  $result = $mysqli->prepare("SELECT theme, options1.option_text, leadin, scenario, q_media_width, q_media_height, options2.correct, options2.marks_correct, options2.option_text, q_type, display_method, score_method, DATE_FORMAT(last_edited,'$cfg_short_date'), status FROM options AS options1, questions, options AS options2 WHERE options1.option_text=questions.q_id AND questions.q_id=options2.o_id AND options1.o_id=? ");
  $result->bind_param('i', $questionID);
  $result->execute();
  $result->store_result();
  if ($result->num_rows > 0) {
    $result->bind_result($theme, $q_id, $leadin, $scenario, $q_media_width, $q_media_height, $correct, $marks, $option_text, $q_type, $display_method, $score_method, $display_last_edited, $status);
    while ($row=$result->fetch()) {
      if ($old_q_id != $q_id and $old_q_id != '') {
        $old_leadin = trim(str_replace('&nbsp;',' ',(strip_tags($old_leadin))));
        if (strlen($old_leadin) > 160) $old_leadin = substr($old_leadin,0,160) . '...';
        $random_questions[$question_no]['theme'] = $old_theme;
        $random_questions[$question_no]['q_id'] = $old_q_id;
        $random_questions[$question_no]['type'] = $old_q_type;
        $random_questions[$question_no]['leadin'] = $old_leadin;
        $random_questions[$question_no]['scenario'] = $old_scenario;
        $random_questions[$question_no]['scenario'] = $old_scenario;
        $random_questions[$question_no]['correct'] = $old_correct;
        $random_questions[$question_no]['status'] = $old_status;
        $random_questions[$question_no]['display_last_edited'] = $display_last_edited;
        $random_questions[$question_no]['marks'] = qMarks($old_q_type, '', $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method);
        $random_questions[$question_no]['random_mark'] = qRandomMarks($old_q_type, '', $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
        $old_correct = array();
        $old_option_text = array();
        $question_no++;
      }
      $old_theme = $theme;
      $old_q_id = $q_id;
      $old_q_type = $q_type;
      $old_leadin = $leadin;
      $old_scenario = $scenario;
      $old_status = $status;
      $old_marks = $marks;
      $old_correct[] = $correct;
      $old_option_text[] = $option_text;
      $old_display_method = $display_method;
      $old_score_method = $score_method;
      $old_q_media_width = $q_media_width;
      $old_q_media_height = $q_media_height;
    }

    // Write out the last question.
    $old_leadin = trim(str_replace('&nbsp;',' ',(strip_tags($old_leadin))));
    if (strlen($old_leadin) > 160) $old_leadin = substr($old_leadin,0,160) . '...';
    $random_questions[$question_no]['theme'] = $old_theme;
    $random_questions[$question_no]['q_id'] = $old_q_id;
    $random_questions[$question_no]['type'] = $old_q_type;
    $random_questions[$question_no]['leadin'] = $old_leadin;
    $random_questions[$question_no]['scenario'] = $old_scenario;
    $random_questions[$question_no]['correct'] = $old_correct;
    $random_questions[$question_no]['status'] = $old_status;
    $random_questions[$question_no]['display_last_edited'] = $display_last_edited;
    $random_questions[$question_no]['marks'] = qMarks($old_q_type, '', $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method);
    $random_questions[$question_no]['random_mark'] = qRandomMarks($old_q_type, '', $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
  }
  $result->close();

  return $random_questions;
}

function random_qMarks($random_questions) {
  $min = 999;
  $max = 0;

  foreach ($random_questions as $individual_question) {
    if ($individual_question['marks'] > $max) $max = $individual_question['marks'];
    if ($individual_question['marks'] < $min) $min = $individual_question['marks'];
  }

  if ($min == $max) {
    return $min;
  } else {
    return 'ERR';
  }
}

function changeScreenNo($mysqlidb) {
  $screen = $_GET['screen'];

  // Change the screen number of the actual question.
  if ($result = $mysqlidb->prepare("UPDATE papers SET screen=? WHERE paper=? AND p_id=?")) {
    $result->bind_param('iii', $screen, $_GET['paperID'], $_GET['questionID']);
    $result->execute();
    $result->close();
  } else {
    display_error("Papers Update Error 1", $mysqlidb->error);
  }

  // Increase screen number of any questions further down the paper with a lower screen number.
  if ($result = $mysqlidb->prepare("UPDATE papers SET screen=? WHERE screen < ? AND paper=? AND display_pos > ?")) {
    $result->bind_param('iiii', $screen, $screen, $_GET['paperID'],  $_GET['display_pos']);
    $result->execute();
    $result->close();
  } else {
    display_error("Papers Update Error 2", $mysqlidb->error);
  }

  // Decrease screen number of any questions further up the paper with a higher screen number.
  if ($result = $mysqlidb->prepare("UPDATE papers SET screen=? WHERE screen > ? AND paper=? AND display_pos < ?")) {
    $result->bind_param('iiii', $screen, $screen, $_GET['paperID'],  $_GET['display_pos']);
    $result->execute();
    $result->close();
  } else {
    display_error("Papers Update Error 3", $mysqlidb->error);
  }
}
if (isset($_GET['change_screen'])) {
  changeScreenNo($mysqli);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html onscroll="scrollXY();" onclick="qOff(); hideMenus(); hideAssStatsMenu(event);">
<head>
<title>TouchStone<?php echo " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style style="text/css">
  .qline {padding-bottom:3px;cursor:pointer;line-height:150%}
  .q_no {text-align:right;vertical-align:top;cursor:pointer;width:40px;padding-right:6px}
  .d {text-align:right;padding-left:6px;padding-right:4px;vertical-align:top}
  .theme {font-weight:bold;color:#316AC5}
  .rnd {padding-bottom:3px;background-color:#DCE7F5;cursor:pointer}
  .s {padding-left:10px}
  .t {white-space:nowrap; vertical-align:top; padding-left:6px; padding-right:6px}
  .m {text-align:right; vertical-align:top; padding-right:4px}
  .errmk {color:red;text-align:right;vertical-align:top}
</style>

<script src="../javascript/staff_help.js" type="text/javascript"></script>
<script src="/touchstone/javascript/MathJaxConfig.js"></script>
<script language="JavaScript">
  function selQ(questionNo, questionID, lineID, qType, screenNo, pID, current_pos, prev_screen, next_screen, current_screen, menuID, subparts, evt) {
    tmp_ID = document.PapersMenu.oldQuestionID.value;
    if (tmp_ID != '') {
      document.getElementById('link' + tmp_ID).style.backgroundColor = 'white';
      document.getElementById('link' + tmp_ID).style.color = 'black';
    }
    document.getElementById('menu2a').style.display = 'none';
    if (menuID == 'menu2b') {
      document.getElementById('menu2c').style.display = 'none';
    } else {
      document.getElementById('menu2b').style.display = 'none';
    }
    document.getElementById(menuID).style.display = 'block';

    document.PapersMenu.questionNo.value = questionNo;
    document.PapersMenu.questionID.value = questionID;
    document.PapersMenu.qType.value = qType;
    document.PapersMenu.screenNo.value = screenNo;
    document.PapersMenu.pID.value = pID;

    document.PapersMenu.current_pos.value = current_pos;
    document.PapersMenu.prev_screen.value = prev_screen;
    if (prev_screen == '') {
      document.getElementById('promotetext').style.color = '#808080';
      document.getElementById('promoteicon').src = '../artwork/promote_disabled.gif';
    } else {
      document.getElementById('promotetext').style.color = 'black';
      document.getElementById('promotetext').style.hover = 'blue';
      document.getElementById('promoteicon').src = '../artwork/promote.gif';
    }
    document.PapersMenu.next_screen.value = next_screen;
    if (next_screen == '') {
      document.getElementById('demotetext').style.color = '#808080';
      document.getElementById('demoteicon').src = '../artwork/demote_disabled.gif';
    } else {
      document.getElementById('demotetext').style.color = 'black';
      document.getElementById('demotetext').style.hover = 'blue';
      document.getElementById('demoteicon').src = '../artwork/demote.gif';
    }
    document.PapersMenu.current_screen.value = current_screen;

    document.getElementById('link' + lineID).style.backgroundColor = '#B3C8E8';
    document.PapersMenu.oldQuestionID.value = lineID;

    if (qType == 'random') {
      var row = '';
      for (i=1; i<=subparts; i++) {
        row = document.getElementById('r' + lineID + '_' + i);
        if (row.style.display == 'none') {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      }
    }
    hideMenus();
    
    document.getElementById('stats_menu').style.display = 'none';
    document.getElementById('copy_submenu').style.display = 'none';
    document.getElementById('change_screen_submenu').style.display='none';

    evt.cancelBubble = true;
  }

  function lon(lineID) {
    if (lineID != document.PapersMenu.oldQuestionID.value) {
      document.getElementById('link' + lineID).style.backgroundColor = '#EEEEEE';
    }
  }

  function loff(lineID) {
    if (lineID != document.PapersMenu.oldQuestionID.value) {
      document.getElementById('link' + lineID).style.backgroundColor = '';
    }
  }

  function edQ(questionNo, questionID, qType) {
    document.location = "../question/edit/" +  qType + ".php?q_id=" + questionID + "&qNo=" + questionNo + "&paperID=<?php echo $paperID; ?>&folder=<?php if(isset($_GET['folder'])) echo $_GET['folder']; ?>&module=<?php if(isset($_GET['module'])) echo $_GET['module']; ?>&calling=paper&scrOfY=" + document.getElementById('scrOfY').value;
  }

  function qOff() {
    document.getElementById('menu2a').style.display = 'block';
    document.getElementById('menu2b').style.display = 'none';
    document.getElementById('menu2c').style.display = 'none';
    tmp_ID = document.PapersMenu.oldQuestionID.value;
    if (tmp_ID != '') {
      document.getElementById('link' + tmp_ID).style.backgroundColor = 'white';
    }

    document.getElementById('stats_menu').style.display = 'none';
    document.getElementById('copy_submenu').style.display = 'none';
    document.getElementById('change_screen_submenu').style.display='none';

    hideMenus();
  }

  function scrollXY() {
    var scrOfX = 0, scrOfY = 0;
    if( typeof( window.pageYOffset ) == 'number' ) {
      //Netscape compliant
      scrOfY = window.pageYOffset;
      scrOfX = window.pageXOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
      //DOM compliant
      scrOfY = document.body.scrollTop;
      scrOfX = document.body.scrollLeft;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
      //IE6 standards compliant mode
      scrOfY = document.documentElement.scrollTop;
      scrOfX = document.documentElement.scrollLeft;
    }
    document.getElementById('scrOfY').value = scrOfY;
  }
</script>
</head>
<body onscroll="scrollXY();"<?php if (isset($_GET['scrOfY'])) echo ' onload="window.scrollTo(0,' . $_GET['scrOfY'] . ');"'; ?>>

<?php
  $result = $mysqli->prepare("SELECT paper_title, moduleID, pass_mark, users.title, users.initials, users.surname, moduleID, folder, random_mark, total_mark, marking, paper_ownerID, DATE_FORMAT(start_date,'%Y%m%d%H%i') AS start_date, DATE_FORMAT(start_date,'$cfg_long_date_time') AS display_start_date, DATE_FORMAT(end_date,'%Y%m%d%H%i') AS end_date, paper_type, deleted, latex_needed FROM (properties, users) WHERE property_id=? AND paper_ownerID=users.id LIMIT 1");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($paper_title, $moduleID, $pass_mark, $title, $initials, $surname, $tmp_module, $tmp_folder, $random_mark, $total_mark, $marking, $paper_ownerID, $start_date, $display_start_date, $end_date, $paper_type, $deleted, $latex_needed);
  $result->fetch();
  $result->close();
  
  $paper_owner = $title  . ' ' . $initials . ', ' . $surname;

  if (!isset($paper_title)) {
  ?>
    <div id="left-sidebar" class="sidebar">
    </div>
    <div id="content" class="content" style="font-size:80%"><br />
  <?php
    echo "<div style=\"position:absolute; left:230px; top:10px\"><img src=\"../artwork/orange_alert_48.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px; font-weight:normal; color:#4465A2; font-size:160%\">Paper not Found</h1>\n";
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"height:1px; border:none; margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<div style=\"margin-left:60px\">For further assistance contact: <a href=\"mailto:$support_email\">$support_email</a></div>\n";
    echo "</div>\n</body>\n</html>\n";
    $mysqli->close();
    exit;
  }
  if ($deleted != '') {
  ?>
    <div id="left-sidebar" class="sidebar">
    </div>
    <div id="content" class="content" style="font-size:80%"><br />
  <?php
    echo "<div style=\"position:absolute;left:230px;top:10px\"><img src=\"../artwork/full_bin.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px;font-weight:normal;color:#4465A2;font-size:160%\">Paper Deleted</h1>\n";
    $deleted_parts = explode('[deleted',$paper_title);
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"height:1px;border:none;margin-left:60px;color:#C0C0C0;background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">Paper <strong>" . $deleted_parts[0] . "</strong> has been deleted.</p>\n\n<ul style=\"margin-left:80px\">\n";
    if ($paper_ownerID == $userID) {
      echo "<li>It can still be recovered from the <a href=\"/touchstone/delete/recycle_list.php\" style=\"color:blue\">recycle bin</a>.</li>\n";
    } else {
      $result = $mysqli->prepare("SELECT title, surname, email FROM users WHERE id=?");
      $result->bind_param('i', $paper_ownerID);
      $result->execute();
      $result->bind_result($tmp_title, $tmp_surname, $tmp_email);
      $result->fetch();
      $result->close();
      echo "<li>You do not own this paper, you will need to get <a href=\"mailto:$tmp_email\" style=\"color:blue\">$tmp_title $tmp_surname</a> to recover it.</li>\n";
    }
    echo "</ul>";
    echo "</div>\n</body>\n</html>\n";
    $mysqli->close();
    exit;
  }

  require '../include/paper_options.inc';
?>
<div id="content" class="content" style="font-size:80%">

<?php
  // Promoting/Demoting questions
  $q_highlight = 0;

  if (isset($_GET['old_pos']) AND isset($_GET['new_pos']) AND $_GET['old_pos'] != $_GET['new_pos']) {
    $old_pos = $_GET['old_pos'];
    $new_pos = $_GET['new_pos'];
    $old_screen = $_GET['old_screen'];
    $new_screen = $_GET['new_screen'];
    $result = $mysqli->prepare("UPDATE papers SET display_pos=9999 WHERE display_pos=? AND paper=?");
    $result->bind_param('ii', $new_pos, $paperID);
    $result->execute();
    $result->close();

    $result = $mysqli->prepare("UPDATE papers SET display_pos=?, screen=? WHERE display_pos=? AND paper=?");
    $result->bind_param('iiii', $new_pos, $new_screen, $old_pos, $paperID);
    $result->execute();
    $result->close();

    $result = $mysqli->prepare("UPDATE papers SET display_pos=?, screen=? WHERE display_pos=9999 AND paper=?");
    $result->bind_param('iii', $old_pos, $old_screen, $paperID);
    $result->execute();
    $result->close();

    $q_highlight = $new_pos;
  } elseif (isset($_GET['old_screen']) AND isset($_GET['new_screen']) AND $_GET['old_screen'] != $_GET['new_screen']) {
    $old_pos = $_GET['old_pos'];
    $new_pos = $_GET['new_pos'];
    $old_screen = $_GET['old_screen'];
    $new_screen = $_GET['new_screen'];
    $result = $mysqli->prepare("UPDATE papers SET screen=? WHERE display_pos=? AND paper=?");
    $result->bind_param('iii', $new_screen, $old_pos, $paperID);
    $result->execute();
    $result->close();

    $q_highlight = $new_pos;
  }

  // Log the hit in recent_papers.
  $result = $mysqli->prepare("INSERT INTO recent_papers (userID, paperID, accessed) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE accessed=NOW();");
  $result->bind_param('ii', $userID, $paperID);
  $result->execute();
  $result->close();

  // Get any questions to exclude.
  $excluded = array();
  $result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($q_id, $parts);
  while ($row = $result->fetch()) {
    $excluded[$q_id] = $parts;
  }
  $result->close();

  if (date("YmdHis", time()) >= $start_date and date("YmdHis", time()) <= $end_date) {
    $active_date = 1;
  } else {
    $active_date = 0;
  }
  if (date("YmdHis", time()) >= $start_date and $paper_type == '2') {
    $summative_lock = 1;
  } else {
    $summative_lock = 0;
  }
  $old_p_id = 0;
  $row_no = 0;
  $row_no2 = 0;
  $old_display_pos = -1;
  $temp_array = array();
  $latex = 0;
  $old_q_id = 0;
  $old_q_type  = '';
  $old_marks  = 0;
  $old_option_text = '';
  $old_correct  = '';
  $old_display_method = '';
  $old_score_method  = '';
  $old_q_media  = '';
  $old_q_media_width = '';
  $old_q_media_height = '';
  $old_scenario  = '';
  $total_random_mark = 0;
  $total_marks  = 0;
  $options = 0;
  $neg_marking = false;
  
  // Get the questions (if any).
  $result = $mysqli->prepare("SELECT theme, q_group, ownerID, p_id, q_id, q_type, screen, leadin, scenario, option_text, correct, display_method, score_method, q_media, q_media_width, q_media_height, marks_correct, marks_incorrect, DATE_FORMAT(last_edited,'$cfg_short_date') AS display_last_edited, display_pos, status, correct_fback, feedback_right, locked FROM (papers, questions) LEFT JOIN options ON questions.q_id = options.o_id WHERE paper=? AND papers.question=questions.q_id ORDER BY screen, display_pos, o_id");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->store_result();
  $result->bind_result($theme, $q_group, $ownerID, $p_id, $q_id, $q_type, $screen, $leadin, $scenario, $option_text, $correct, $display_method, $score_method, $q_media, $q_media_width, $q_media_height, $marks_correct, $marks_incorrect, $display_last_edited, $display_pos, $status, $correct_fback, $feedback_right, $locked);
  $temp_array = array();
  while ($result->fetch()) {
    // latex check [tex]
    if ($latex == 0) {
      if (strpos($leadin,'[tex]') !== false or strpos($scenario,'[tex]') !== false or strpos($option_text,'[tex]') !== false or strpos($score_method,'[tex]') !== false or strpos($correct_fback,'[tex]') !== false or strpos($feedback_right,'[tex]') !== false) {
        $latex = 1;
      }
    }
    // latex check $$
    if ($latex == 0) {
      if (strpos($leadin,'$$') !== false or strpos($scenario,'$$') !== false or strpos($option_text,'$$') !== false or strpos($score_method,'$$') !== false or strpos($correct_fback,'$$') !== false or strpos($feedback_right,'$$') !== false) {
        $latex = 1;
      }
    }
    // Check for negative marking
    if ($marks_incorrect < 0) {
      $neg_marking = true;
    }

    if ($old_q_id != $q_id or $old_display_pos != $display_pos) {
      if($old_display_pos != -1) {
        $temp_array[$row_no2]['options'] = $options;
      }
      $options = 0;
      if ($old_q_type == 'random') {
        $temp_array[$row_no2]['original_marks'] = random_qMarks($temp_array[$row_no2]['random']);
        if ($temp_array[$row_no2]['status'] != 'Experimental') {
          $temp_array[$row_no2]['marks'] = $temp_array[$row_no2]['original_marks'];
          $total_random_mark += $temp_array[$row_no2]['random'][0]['random_mark'];
        }
      } else {
        if (isset($excluded[$old_q_id])) {
          $tmp_exclude = $excluded[$old_q_id];
        } else {
          $tmp_exclude = '';
        }
        $temp_array[$row_no2]['original_marks'] = qMarks($old_q_type, $tmp_exclude, $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method);
        if ($row_no2 > 0 and $temp_array[$row_no2]['status'] != 'Experimental') {
          $temp_array[$row_no2]['marks'] = $temp_array[$row_no2]['original_marks'];
          $total_random_mark += qRandomMarks($old_q_type, $tmp_exclude, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
        }
      }
      if ($row_no2 > 0 and $temp_array[$row_no2]['status'] != 'Experimental') $total_marks += $temp_array[$row_no2]['marks'];
      $temp_array[$row_no2]['display_method'] = $old_display_method;
      $temp_array[$row_no2]['score_method'] = $old_score_method;
      if ($row_no2 > 0 and $paper_type < 3) checkProblems($paper_type, $old_q_type, $old_score_method, $temp_array, $old_scenario, $old_q_media, $row_no2, $temp_array[$row_no2]['original_marks'], $old_q_id, $excluded[$old_q_id], $old_option_text, $old_correct, $temp_array[$row_no2]['status']);
      $old_correct = array();
      $old_option_text = array();
      $old_marks = 0;
      $row_no2++;

      $row_no++;
      $temp_array[$row_no]['theme'] = $theme;
      $temp_array[$row_no]['screen'] = $screen;
      $temp_array[$row_no]['q_type'] = $q_type;
      $temp_array[$row_no]['leadin'] = trim(str_replace('&nbsp;',' ',(strip_tags($leadin))));
      if (strlen($temp_array[$row_no]['leadin']) > 160) $temp_array[$row_no]['leadin'] = substr($temp_array[$row_no]['leadin'],0,160) . '...';
      $temp_array[$row_no]['scenario'] = $scenario;
      $temp_array[$row_no]['p_id'] = $p_id;
      $temp_array[$row_no]['q_id'] = $q_id;
      $temp_array[$row_no]['display_last_edited'] = $display_last_edited;
      $temp_array[$row_no]['q_media'] = $q_media;
      $temp_array[$row_no]['q_media_width'] = $q_media_width;
      $temp_array[$row_no]['q_media_height'] = $q_media_height;
      $temp_array[$row_no]['ownerID'] = $ownerID;
      $temp_array[$row_no]['display_pos'] = $display_pos;
      $temp_array[$row_no]['correct'] = $correct;
      $temp_array[$row_no]['q_group'] = $q_group;
      $temp_array[$row_no]['status'] = $status;
      $temp_array[$row_no]['warnings'] = '';
      $temp_array[$row_no]['random'] = array();
      $old_random_mark = $random_mark;
      $old_total_marks = $total_mark;

      if ($q_type == 'random') {
        $temp_array[$row_no]['random'] = randomDetails($q_id);
      }

      if ($summative_lock == 1 AND $locked == '') {
        $editPaper = $mysqli->prepare("UPDATE questions SET locked=NOW() WHERE q_id=? AND locked IS NULL");
        $editPaper->bind_param('i', $q_id);
        $editPaper->execute();
        $editPaper->close();
      }
      // Set the question team to that of the paper if null.
      if ($q_group == '') {
        $module_list = explode(',',$moduleID);

        $editPaper = $mysqli->prepare("UPDATE questions SET q_group=? WHERE q_id=?");
        $editPaper->bind_param('si', $module_list[0], $q_id);
        $editPaper->execute();
        $editPaper->close();
      }
      
      // Unlock code - emergency use only!
      if (isset($_GET['unlock']) AND $_GET['unlock'] == '1' AND strpos($userroles,'SysAdmin') !== false) {
        $tmp_date = new DateTime();
        $tmp_date->modify('+28 day');
        $tmp_start_date = $tmp_date->format('Ymd' . '100000');
        $tmp_end_date = $tmp_date->format('Ymd' . '100000');        

        // Update the paper date so that it does not immediately re-lock
        $editPaper = $mysqli->prepare("UPDATE properties SET start_date=?, end_date=? WHERE property_id=?");
        $editPaper->bind_param('ssi', $tmp_start_date, $tmp_end_date, $paperID);
        $editPaper->execute();
        $editPaper->close();
        
        // Update the questions to take lock off
        $editPaper = $mysqli->prepare("UPDATE questions SET locked=NULL WHERE q_id=?");
        $editPaper->bind_param('i', $q_id);
        $editPaper->execute();
        $editPaper->close();
        $summative_lock = 0;
      }

      //prevent php errors by populating $excluded[$q_id]
      if (!isset($excluded[$q_id])) {
        $excluded[$q_id] = NULL;
      }
    }
    $old_q_id = $q_id;
    $old_display_pos = $display_pos;
    $old_q_type = $q_type;
    $old_display_method = $display_method;
    $old_score_method = $score_method;
    $old_correct[] = $correct;
    $old_scenario = $scenario;
    $old_q_media = $q_media;
    $old_q_media_width = $q_media_width;
    $old_q_media_height = $q_media_height;
    $old_option_text[] = $option_text;
    $old_marks = $marks_correct;
    if (!empty($option_text) or (!empty($correct) and (in_array($q_type, array('labelling', 'hotspot', 'timedate')))) or in_array($q_type, array('info', 'likert', 'flash'))) $options++;
  }
  $result->close();
  
  if ($row_no > 0) {
    $temp_array[$row_no]['options'] = $options;
    if ($old_q_type == 'random') {
      $temp_array[$row_no2]['original_marks'] = random_qMarks($temp_array[$row_no2]['random']);
      if ($temp_array[$row_no2]['status'] != 'Experimental') {
        $temp_array[$row_no2]['marks'] = $temp_array[$row_no2]['original_marks'];
        $total_random_mark += $temp_array[$row_no2]['random'][0]['random_mark'];
      }
    } else {
      $temp_array[$row_no2]['original_marks'] = qMarks($old_q_type, $excluded[$old_q_id], $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method);
      if ($temp_array[$row_no2]['status'] != 'Experimental') {
        $temp_array[$row_no2]['marks'] = $temp_array[$row_no2]['original_marks'];
        $total_random_mark += qRandomMarks($old_q_type, $excluded[$old_q_id], $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
      }
    }
    if ($temp_array[$row_no2]['status'] != 'Experimental') $total_marks += $temp_array[$row_no2]['marks'];
    $temp_array[$row_no2]['display_pos'] = $old_display_pos;
    $temp_array[$row_no2]['score_method'] = $old_score_method;
    if ($paper_type < 3) checkProblems($paper_type, $old_q_type, $old_score_method, $temp_array, $old_scenario, $old_q_media, $row_no2, $temp_array[$row_no2]['original_marks'], $old_q_id, $excluded[$old_q_id], $old_option_text, $old_correct, $temp_array[$row_no2]['status']);

    if (($total_random_mark != $old_random_mark or $total_marks != $old_total_marks or $latex != $latex_needed) and $paper_type != '3') {   // Calculate random and total marks
      $result = $mysqli->prepare("UPDATE properties SET random_mark=?, total_mark=?, latex_needed=? WHERE property_id=?");
      $result->bind_param('diii', $total_random_mark, $total_marks, $latex, $_GET['paperID']);
      $result->execute();
      $result->close();
    }
  }

  if (isset($_GET['module']) and $_GET['module'] != '') {
    $module = $_GET['module'];
    $folder = '';
    $paper_modules = explode(',',$module);
    if (count($paper_modules) > 0) {     // Paper is on multiple modules
      if (strpos($userroles,'Admin') !== false) {
        $module = $paper_modules[0];
      } else {
        for ($i=count($paper_modules)-1; $i>0; $i--) {
          if (in_array($paper_modules[$i], $teams)) {
            $module = $paper_modules[$i];
          }
        }
      }
    }
  } elseif (isset($_GET['folder'])) {
    $folder = $_GET['folder'];
    $result = $mysqli->prepare("SELECT name FROM folders WHERE id=? LIMIT 1");
    $result->bind_param('i', $folder);
    $result->execute();
    $result->bind_result($folder_name);
    $result->fetch();
    $result->close();
    
    $module = '';
  } else {
    $paper_modules = explode(',',$tmp_module);  // Get the modules off the paper properties
    $module = $paper_modules[0];
    $folder = '';
  }

  if (strpos($userroles,'Admin') === false) {
    $OKmodules = array();
    $module_split = explode(',',$module);
    foreach ($module_split as $individual_module) {
      if (in_array($individual_module, $teams)) {
        $OKmodules[] = $individual_module;
      }
    }
    $module = implode(',',$OKmodules);
  }

  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  echo "<tr><td style=\"background-color:#F1F5FB\" colspan=\"5\"><div class=\"breadcrumb\">";
  if ($module != '') {
    echo '<a href="../index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $module . '">' . $module . '</a>';
  } elseif ($folder != '') {
    echo '<a href="../index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } else {
    echo '<a href="../index.php">' . $string['home'] . '</a>';
  }
  echo "</div><div onclick=\"qOff()\" style=\"font-size:220%; font-weight:bold; margin-left:10px\">$paper_title</div>";
  echo "</td><td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(1); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></td></tr>\n";
  echo "<tr><td colspan=\"3\" style=\"background-color:#F1F5FB;font-size:90%;padding-left:10px\"><strong>" . $string['start'] . ":</strong> $display_start_date</td><td colspan=\"3\" style=\"background-color:#F1F5FB;text-align:right;font-size:90%\"><strong>" . $string['owner'] . ":</strong> $paper_owner&nbsp;</td></tr>\n";
  ?>
    <tr>
    <td style="background-color:#F1F5FB;width:40px" colspan="2">&nbsp;</td>
    <td style="background-color:#F1F5FB"><?php echo $string['question']; ?></td>
    <td style="background-color:#F1F5FB;width:120px"><img src="../artwork/header_vertical_line.gif" width="2" height="15" border="0" />&nbsp;<?php echo $string['type']; ?>&nbsp;</td>
    <td style="background-color:#F1F5FB;width:50px"><img src="../artwork/header_vertical_line.gif" width="2" height="15" border="0" />&nbsp;<?php echo $string['marks']; ?>&nbsp;</td>
    <td style="background-color:#F1F5FB;width:100px"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" />&nbsp;<?php echo $string['modified']; ?>&nbsp;</td>
    </tr>
    <tr><td colspan="6" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" /></td></tr>
  <?php

  if ($summative_lock == 1) {
    echo "<tr><td colspan=\"2\" style=\"height:32px; text-align:right; background-image:url('../artwork/locked_gradient.png'); background-repeat:repeat-x\"><img src=\"../artwork/paper_locked_padlock.png\" width=\"19\" height=\"24\" alt=\"Locked\" />&nbsp;&nbsp;</td><td colspan=\"3\" style=\"height:32px; vertical-align:middle; background-image:url('../artwork/locked_gradient.png'); background-repeat:repeat-x\">" . $string['paperlockedwarning'] . " <a href=\"#\" class=\"blacklink\" onclick=\"launchHelp(189); return false;\">Click for more details.</a></td><td style=\"text-align:right; background-image:url('../artwork/locked_gradient.png'); background-repeat:repeat-x\">";
    if (strpos($userroles,'Admin') !== false) {
      $record_no = 0;
      $result = $mysqli->prepare("SELECT COUNT(log_metadata.id) FROM log_metadata, users WHERE paperID=? AND log_metadata.userID=users.id AND roles='Student'");
      $result->bind_param('i', $paperID);
      $result->execute();
      $result->bind_result($record_no);
      $result->fetch();
      $result->close();
   
      if ($record_no == 0) {
        echo '<span style="align:right"><input type="button" name="unlock" value="' . $string['unlock'] . '" onclick="window.location=\'details.php?paperID=' . $paperID . '&module=' . $module . '&folder=' . $folder . '&scrOfY=0&unlock=1\'" /></span>';
      } else {
        echo '<span style="align:right"><input type="button" name="unlock" value="' . $string['unlock'] . '" disabled /></span>';
      }
    }
    echo "</td></tr>\n";
  } elseif ($paper_type == '2') {
    $tmp_hour = substr($display_start_date,0,2);
    if (substr($tmp_hour,0,1) == '0') $tmp_hour = substr($tmp_hour,1,1);
    if (substr($display_start_date,12,4) > (date("Y")+1)) {
      echo "<tr><td colspan=\"2\" style=\"height:32px; text-align:right; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\"><img src=\"../artwork/late_warning_icon.png\" style=\"padding-top:2px\" width=\"28\" height=\"28\" alt=\"Locked\" />&nbsp;&nbsp;</td><td colspan=\"7\" style=\"height:32px; vertical-align:middle; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\">";
      printf($string['farfuturewarning'], $display_start_date); 
      echo "</td></tr>\n";
    } elseif ($tmp_hour < $cfg_hour_warning) {
      echo "<tr><td colspan=\"2\" style=\"height:32px; text-align:right; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\"><img src=\"../artwork/late_warning_icon.png\" style=\"padding-top:2px\" width=\"28\" height=\"28\" alt=\"Locked\" />&nbsp;&nbsp;</td><td colspan=\"7\" style=\"height:32px; vertical-align:middle; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\">";
      printf($string['earlywarning'], $cfg_hour_warning);
      echo "</td></tr>\n";
    }
  }

  $screen_marks = 0;
  $old_screen = 0;
  $question_number = 0;
  $marks_incorrect_error = false;
  $paper_warnings = array();
  for ($x=1; $x<=$row_no; $x++) {
    if($temp_array[$x]['options'] == 0) $temp_array[$x]['warnings'] .= $string['nooptionsdefined'];
    if ($temp_array[$x]['status'] == 'Incomplete') $paper_warnings['Incomplete'][] = $question_number + 1;
    if ($temp_array[$x]['status'] == 'Beta') $paper_warnings['Beta'][] = $question_number + 1;
    if ($temp_array[$x]['status'] == 'Retired') $paper_warnings['Retired'][] = $question_number + 1;
    if ($old_screen != $temp_array[$x]['screen']) {
      if ($old_screen > 0) {
        $tmp_screen_mean = ($total_marks == 0) ? 0 : ($screen_marks / $total_marks);
        if ($paper_type == '2' and $question_number > 2 and $tmp_screen_mean * 100 > 25 and $screen_marks > 3) {
          echo "\n<tr><td colspan=\"5\" style=\"font-weight:bold; color:#C00000\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"Warning\" border=\"0\" />&nbsp;";
          $percent = round(($screen_marks / $total_marks) * 100);
          printf($string['markswarning'], $old_screen, $screen_marks, $percent);
          echo "</td></tr>\n";
        }
      }
      $screen_marks = 0;
      if ($old_screen < ($temp_array[$x]['screen'] - 1)) {
        for ($missing=1; $missing<($temp_array[$x]['screen'] - $old_screen); $missing++) {
          echo "<tr><td colspan=\"6\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#C00000\"><tr><td style=\"font-weight:bold\"><nobr>" . $string['screen'] . " " . ($old_screen + $missing) . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#C00000; background-color:#C00000; width:100%\" /></td></tr></table></td></tr>\n";
          echo '<tr><td colspan="6" style="height:55px; background-image:url(../artwork/no_questions_gradient.png); repeat:repeat-x; background-color:#FFC0C0; padding-left:15px; padding-top:4x">' . $string['noquestionscreen'] . '</td></tr>';
        }
      }
      echo '<tr><td colspan="6" style="height:10px"></td></tr>';
      echo "<tr><td colspan=\"6\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $string['screen'] . " " . $temp_array[$x]['screen'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
    }
    $old_screen = $temp_array[$x]['screen'];
    $teamOK = false;
    if ($temp_array[$x]['ownerID'] == $userID or $paper_ownerID == $userID or strpos($userroles,'SysAdmin') !== false) {
      $teamOK = true;
    } else {
      foreach ($teams as $individual_team) {
        if ($individual_team == $temp_array[$x]['q_group']) $teamOK = true;
      }
    }

    if ($q_highlight == $temp_array[$x]['display_pos']) {
      echo "<script language=\"JavaScript\">\n";
      echo "document.getElementById('menu2a').style.display = 'none';\n";
      echo "document.getElementById('menu2c').style.display = 'none';\n";
      echo "document.getElementById('menu2b').style.display = 'block';\n";
      echo "document.PapersMenu.questionNo.value = '" . ($question_number+1) . "';\n";
      echo "document.PapersMenu.questionID.value = '" . $temp_array[$x]['q_id'] . "';\n";
      echo "document.PapersMenu.qType.value = '" . $temp_array[$x]['q_type'] . "';\n";
      echo "document.PapersMenu.screenNo.value = '" . $temp_array[$x]['screen'] . "';\n";
      echo "document.PapersMenu.pID.value = '" . $temp_array[$x]['p_id'] . "';\n";
      echo "document.PapersMenu.current_pos.value = " . $temp_array[$x]['display_pos'] . ";\n";
      echo "document.PapersMenu.prev_screen.value = '" . $temp_array[$x - 1]['screen'] . "';\n";
      if ($temp_array[$x - 1]['screen'] == '') {
        echo "document.getElementById('promotetext').style.color = '#808080';\n";
        echo "document.getElementById('promoteicon').src = '../artwork/promote_disabled.gif';\n";
      } else {
        echo "document.getElementById('promotetext').style.color = '#black';\n";
        echo "document.getElementById('promoteicon').src = '../artwork/promote.gif';\n";
      }
      echo "document.PapersMenu.next_screen.value = '" . $temp_array[$x + 1]['screen'] . "';\n";
      if ($temp_array[$x + 1]['screen'] == '') {
        echo "document.getElementById('demotetext').style.color = '#808080';\n";
        echo "document.getElementById('demoteicon').src = '../artwork/demote_disabled.gif';\n";
      } else {
        echo "document.getElementById('demotetext').style.color = '#black';\n";
        echo "document.getElementById('demoteicon').src = '../artwork/demote.gif';\n";
      }
      echo "document.PapersMenu.current_screen.value = '" . $temp_array[$x]['screen'] . "';\n";
      echo "document.PapersMenu.oldQuestionID.value = '$x';\n";
      echo "</script>\n";
    }
    if ($temp_array[$x]['status'] == 'Experimental' or $temp_array[$x]['status'] == 'Retired') {
      $forecolor = '#808080';
    } elseif ($temp_array[$x]['marks'] == 0 and $temp_array[$x]['q_type'] != 'info' and $paper_type != '3' and $paper_type != '4' and $excluded[$temp_array[$x]['q_id']] != NULL) {
      $forecolor = 'red';
    } else {
      $forecolor = 'black';
    }

    if (trim($temp_array[$x]['theme']) != '') {
      echo "<tr><td></td><td></td><td colspan=\"4\" class=\"theme\">" . trim($temp_array[$x]['theme']) . "</td></tr>\n";
    }

    echo "<tr id=\"link$x\" onmouseover=\"lon($x)\" onmouseout=\"loff($x)\" class=\"qline\" style=\"";
    if ($q_highlight == $temp_array[$x]['display_pos']) {
      echo '; background-color:#B3C8E8';
    } else {
      echo '; color:' . $forecolor;
    }

    $prevous_screen = '';
    $next_screen = '';
    if ($teamOK == true) {
      if (isset($temp_array[$x - 1]['screen'])) {
        $prevous_screen = $temp_array[$x - 1]['screen'];
      }
      $next_screen = '';
      if (isset($temp_array[$x + 1]['screen'])) {
        $next_screen = $temp_array[$x + 1]['screen'];
      }

      if ($summative_lock == 1) {
        echo "\" onclick=\"selQ(" . ($question_number+1) . ",'" . $temp_array[$x]['q_id'] . "','$x','" . $temp_array[$x]['q_type'] . "','" . $temp_array[$x]['screen'] . "','" . $temp_array[$x]['p_id'] . "'," . $temp_array[$x]['display_pos'] . ",'" . $prevous_screen . "','" . $next_screen . "','" . $temp_array[$x]['screen'] . "','menu2c'," . count($temp_array[$x]['random']) . ",event);\" ondblclick=\"edQ(" . ($question_number+1) . "," . $temp_array[$x]['q_id'] . ",'" . $temp_array[$x]['q_type'] . "');\">";
      } else {
        echo "\" onclick=\"selQ(" . ($question_number+1) . ",'" . $temp_array[$x]['q_id'] . "','$x','" . $temp_array[$x]['q_type'] . "','" . $temp_array[$x]['screen'] . "','" . $temp_array[$x]['p_id'] . "'," . $temp_array[$x]['display_pos'] . ",'" . $prevous_screen . "','" . $next_screen . "','" . $temp_array[$x]['screen'] . "','menu2b'," . count($temp_array[$x]['random']) . ",event);\" ondblclick=\"edQ(" . ($question_number+1) . "," . $temp_array[$x]['q_id'] . ",'" . $temp_array[$x]['q_type'] . "');\">";
      }
    } else {
      echo "\" onclick=\"selQ(" . ($question_number+1) . ",'" . $temp_array[$x]['q_id'] . "','$x','" . $temp_array[$x]['q_type'] . "','" . $temp_array[$x]['screen'] . "','" . $temp_array[$x]['p_id'] . "'," . $temp_array[$x]['display_pos'] . ",'" . $prevous_screen . "','" . $next_screen . "','" . $temp_array[$x]['screen'] . "','menu2c'," . count($temp_array[$x]['random']) . ",event);\" ondblclick=\"edQ(" . ($question_number+1) . "," . $temp_array[$x]['q_id'] . ",'" . $temp_array[$x]['q_type'] . "');\">";
    }

    if ($temp_array[$x]['q_type'] == 'random') {
      $dice_no = rand(1,6);
      if ($temp_array[$x]['leadin'] == '') $temp_array[$x]['leadin'] = 'Random question block';
      echo '<td style="width:16px; padding-left:2px"><img src="../artwork/dice' . $dice_no . '.png" width="14" height="14" alt="folder" border="0" /></td>';
    } else {
      echo '<td></td>';
    }

    if ($temp_array[$x]['q_type'] == 'info') {
      echo '<td class="q_no"><img src="../artwork/black_white_info_icon.png" width="6" height="12" alt="Info" />&nbsp;</td>';
    } else {
      $question_number++;
      echo "<td class=\"q_no\">&nbsp;$question_number.</td>";
    }
    
    if ($temp_array[$x]['q_type'] == 'random') {
      echo "<td>" . $temp_array[$x]['leadin'] . "</td>";
    } elseif ($temp_array[$x]['q_type'] == 'branching') {
      if ($temp_array[$x]['leadin'] == '') {
        echo "<td>Branching question set based on Q" . findDecisionQ($temp_array,$temp_array[$x]['scenario']) . "</td>";
      } else {
        echo "<td>" . $temp_array[$x]['leadin'] . " (Q" . findDecisionQ($temp_array,$temp_array[$x]['scenario']) . ")</td>";
      }
    } elseif ($temp_array[$x]['leadin'] != '') {
      echo "<td>" . $temp_array[$x]['leadin'];
      if ($excluded[$temp_array[$x]['q_id']] != NULL) echo ' <img src="../artwork/exclude_small.gif" width="15" height="11" alt="Excluded" />';
      if ($temp_array[$x]['warnings'] != '') echo '<span style="color:#C00000; font-weight:bold">&nbsp;<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="' . $string['warning'] . '" border="0" />&nbsp;' . $temp_array[$x]['warnings'] . '</span>';
      echo "</td>";
    } elseif (strpos($temp_array[$x]['q_media'],'.swf') !== false) {
      echo "<td><img src=\"../artwork/flash_icon.png\" width=\"48\" height=\"48\" alt=\"Embedded Flash object\" border=\"0\" /></td>";
    } elseif (strpos($temp_array[$x]['q_media'],'.flv') !== false) {
      echo "<td><img src=\"../artwork/flash_icon.png\" width=\"48\" height=\"48\" alt=\"Embedded Flash object\" border=\"0\" /></td>";
    } else {
      echo "<td><img src=\"../media/" . $temp_array[$x]['q_media'] . "\" width=\"" . ($temp_array[$x]['q_media_width'] / 3) . "\" height=\"" . ($temp_array[$x]['q_media_height'] /3) . "\" alt=\"Media file\" border=\"1\" /></td>";
    }

    echo '<td class="t">';
    // Display position out of sync.
    if ($x <> $temp_array[$x]['display_pos']) {
      $temp_array[$x]['display_pos'] = $x;
      $editPaper = "UPDATE papers SET display_pos=$x WHERE p_id=" . $temp_array[$x]['p_id'];
      if (!$mysqli->query($editPaper)) {
        display_error("Paper order Error","Problem with query: $editPaper");
      }
    }
    //echo fullQuestionType($temp_array[$x]['q_type']) . '</td>';
    echo $string[$temp_array[$x]['q_type']] . '</td>';
    if ($paper_type == '3') {
      echo '<td style="text-align:right; vertical-align:top; color:#C0C0C0">n/a</td>';
    } elseif ($paper_type == '4') {
      $temp_array[$x]['score_method'] = str_replace('|',',',$temp_array[$x]['score_method']);
      $temp_array[$x]['score_method'] = str_replace(',false','',$temp_array[$x]['score_method']);
      echo '<td style="text-align:right; vertical-align:top; color:#808080">' . $temp_array[$x]['score_method'] . '</td>';
    } elseif ($temp_array[$x]['q_type'] == 'info' or $temp_array[$x]['q_type'] == 'keyword_based') {
      echo '<td>&nbsp;</td>';
    } else {
      if ($temp_array[$x]['status'] !== 'Experimental' and $temp_array[$x]['marks'] === 'ERR') {
        echo '<td style="text-align:right; vertical-align:top"><img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="' . $string['variablenomarks'] . '" border="0" /></td>';
        $marks_incorrect_error = true;
      } elseif ($temp_array[$x]['status'] === 'Experimental') {
        echo '<td style="text-align:right; vertical-align:top">N/A</td>';
      } else {
        echo '<td style="text-align:right; vertical-align:top">' . $temp_array[$x]['marks'] . '</td>';
      }
    }
    if ($temp_array[$x]['status'] !== 'Experimental') {
    	$screen_marks += $temp_array[$x]['marks'];
    }
    echo '<td class="d">' . $temp_array[$x]['display_last_edited'] . '</td>';
    echo "</tr>\n";

    if ($temp_array[$x]['q_type'] == 'random') {
      $sub_question = 1;
      foreach ($temp_array[$x]['random'] as $random_question) {
        echo "<tr style=\"display:none\" ondblclick=\"edQ(" . ($question_number+1) . "," . $random_question['q_id'] . ",'" . $random_question['type'] . "');\" id=\"r" . $x . "_" . $sub_question . "\"><td></td><td></td><td class=\"s\">&#149&nbsp;" . $random_question['leadin'] . "</td><td class=\"t\">" . fullQuestionType($random_question['type']) . "</td>";
        if ($temp_array[$x]['marks'] == 'ERR') {
          echo "<td class=\"errmk\">" . $random_question['marks'] . "</td>";
        } else {
          echo "<td class=\"m\">" . $random_question['marks'] . "</td>";
        }
        echo "<td class=\"d\">" . $random_question['display_last_edited'] . "</td></tr>\n";
        $sub_question++;
      }
    }
  }

  if ($total_marks != 0) {
    if ($paper_type == '2' and $question_number > 2 and ($screen_marks / $total_marks) * 100 > 25 and $screen_marks > 3) {
      echo "\n<tr><td colspan=\"5\" style=\"font-weight:bold; color:#C00000\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" />&nbsp;";
      $percent = round(($screen_marks / $total_marks) * 100);
      printf($string['markswarning'], $old_screen, $screen_marks, $percent);
      echo "</td></tr>\n";
    }
    if ($row_no > 0 and $paper_type != '3' and $paper_type != '4') {
      echo "<tr><td colspan=\"4\"></td><td style=\"border-top:1px solid black\" align=\"right\">";
      if ($marks_incorrect_error == true) {
        echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="' . $string['variablenomarks'] . '" border="0" />';
      } else {
        echo $total_marks;
      }
      echo "</td><td style=\"color:#808080\"><nobr>&nbsp;&nbsp;" . $string['passmark'] . ":&nbsp;$pass_mark%&nbsp;</nobr></td></tr>\n";
    }
  }


  // Final paper warnings.
  if ($paper_type == '2') {
    if ($summative_lock == 1) {
      $warning_types = array('Incomplete','Beta');
    } else {
      $warning_types = array('Incomplete','Beta','Retired');
    }
    foreach ($warning_types as $warning_type) {
      if (isset($paper_warnings[$warning_type]) AND count($paper_warnings[$warning_type]) > 0) {
        echo "<tr><td colspan=\"6\" style=\"color:#C00000\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" />&nbsp;<strong>The following questions are '$warning_type':</strong> ";
        foreach ($paper_warnings[$warning_type] as $question_warning) {
          echo ' Q' . $question_warning;
        }
        echo "</td></tr>\n";
      }
    }
  }
  
  if ($marking == 1 and $neg_marking == true) {     // Can't use random mark with negative marking
    $editPaper = $mysqli->prepare("UPDATE properties SET marking=0 WHERE property_id=?");
    $editPaper->bind_param('i', $paperID);
    $editPaper->execute();
    $editPaper->close();
  }
  $mysqli->close();
?>
</table>
</div>

</body>
</html>
