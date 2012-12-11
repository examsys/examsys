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
* Displays tasks for the papers frame (papers_menu.php).
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

// TODO: error handling for AJAX calls

ob_start('ob_gzhandler');
require '../include/staff_auth.inc';
require '../include/question_types.inc';
require '../include/errors.inc';
require '../include/calculate_marks.inc';
require_once '../classes/questionutils.class.php';
require_once '../classes/paperutils.class.php';
require_once '../classes/moduleutils.class.php';

check_var('paperID', 'GET', true, false);
$paperID = $_GET['paperID'];

// Unlock code - emergency use only!
if (isset($_GET['unlock']) and $_GET['unlock'] == '1' and $userObject->has_role('SysAdmin')) {
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
  $editPaper = $mysqli->prepare("UPDATE questions INNER JOIN papers ON questions.q_id=papers.question AND paper=? SET questions.locked=NULL");
  $editPaper->bind_param('i', $paperID);
  $editPaper->execute();
  $editPaper->close();
  $summative_lock = false;
}

$result = $mysqli->prepare("SELECT DATE_FORMAT(created,'%Y%m%d%H%i%S') AS created, paper_title, pass_mark, users.title, users.initials, users.surname, folder, random_mark, total_mark, marking, paper_ownerID, DATE_FORMAT(start_date,'%H') as start_hour, DATE_FORMAT(start_date,'%Y%m%d%H%i') AS start_date, DATE_FORMAT(start_date,'{$configObject->get('cfg_long_date_time')}') AS display_start_date, DATE_FORMAT(end_date,'%Y%m%d%H%i') AS end_date, paper_type, deleted, latex_needed FROM (properties, users) WHERE property_id=? AND paper_ownerID=users.id LIMIT 1");
$result->bind_param('i', $paperID);
$result->execute();
$result->bind_result($created, $paper_title, $pass_mark, $title, $initials, $surname, $tmp_folder, $random_mark, $total_mark, $marking, $paper_ownerID, $tmp_start_hour, $start_date, $display_start_date, $end_date, $paper_type, $deleted, $latex_needed);
$result->fetch();
$result->close();

function check_duplicates($q_screens) {
  global $string;

  foreach ($q_screens as $q_screen=>$qs) {
    if (count($qs) > 1) {
      echo "<tr><td colspan=\"2\" class=\"warnicon\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td colspan=\"4\" class=\"warn\"><strong>Duplicate questions:</strong> Q" . implode(', Q', $qs) . "</td></tr>\n";
    }
  }
}

function checkProblems($p_type, $q_type, $score_method, &$temp_array, $scenario, $q_media, $row_no, $question_marks, $q_id, $tmp_excluded, $option_text, $o_media, $correct_array, $status) {
  global $string;

  if (!isset($tmp_excluded) and ($status == 'Normal' or $status == 'Experimental' or $status == 'Beta')) {
    if ($score_method == 'SelectedPositive' and $q_type == 'mrq') {
      if ($question_marks > (count($option_text) / 2)) $temp_array[$row_no]['warnings'] = $string['toomanycorrect'];
    } elseif ($q_type == 'dichotomous') {
      if ($score_method == 'Mark per Option' and $question_marks < count($option_text)) $temp_array[$row_no]['warnings'] = sprintf($string['dichotomouswarning'], $question_marks, count($option_text));
    } elseif ($p_type != 3 and ($q_type == 'mcq' or $q_type == 'calculation') and $correct_array[0] == '') {
      $temp_array[$row_no]['warnings'] = $string['nocorrect'];
    } elseif ($p_type != 3 and $q_type == 'mrq' and !in_array('y', $correct_array)) {
      $temp_array[$row_no]['warnings'] = $string['nocorrect'];
    } elseif ($p_type != 3 and $q_type == 'textbox' and $question_marks == 0) {
      $temp_array[$row_no]['warnings'] = $string['zeromarks'];
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
      if ($score_method == 'Mark per Option' and $question_marks < $scenario_no) $temp_array[$row_no]['warnings'] = $string['answermissing'];
    } elseif ($q_type == 'labelling') {
      if (!have_valid_labels($temp_array[$row_no]['correct'])) {
        $temp_array[$row_no]['warnings'] = $string['nolabels'];
      }
    }
    if ($q_type == 'mcq' and $score_method == 'vertical_other' and $p_type != '3') {
      $temp_array[$row_no]['warnings'] = $string['mcqsurvey'];
    }
  }
}

/**
 * Check if a labelling question has any labels added to the canvas
 * @param $correct Correct answer string for the question
 * @return bool
 */
function have_valid_labels($correct) {
  $ok = false;

  $tmp_first_split = explode(';', $correct);
  $tmp_second_split = explode('$', $tmp_first_split[11]);

  for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
    if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
      $ok = true;
      break;
    }
  }

  return $ok;
}

function randomDetails($questionID) {
  global  $configObject, $mysqli;

  $question_no = 0;
  $random_questions = array();
  $old_q_id = '';
  $old_score_method = '';
  $old_q_media_width = '';
  $old_q_media_height = '';
  $old_correct = array();
  $old_option_text = array();

  $result = $mysqli->prepare("SELECT theme, options1.option_text, leadin, scenario, q_media_width, q_media_height, options2.correct, options2.marks_correct, options2.option_text, q_type, display_method, score_method, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}'), status FROM options AS options1, questions, options AS options2 WHERE options1.option_text=questions.q_id AND questions.q_id=options2.o_id AND options1.o_id=? ");
  $result->bind_param('i', $questionID);
  $result->execute();
  $result->store_result();
  if ($result->num_rows > 0) {
    $result->bind_result($theme, $q_id, $leadin, $scenario, $q_media_width, $q_media_height, $correct, $marks, $option_text, $q_type, $display_method, $score_method, $display_last_edited, $status);
    while ($result->fetch()) {
      if ($old_q_id != $q_id and $old_q_id != '') {
        $old_leadin = QuestionUtils::clean_leadin($old_leadin);
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
        $random_questions[$question_no]['random_mark'] = qRandomMarks($old_q_type, '', $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
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
    $old_leadin = QuestionUtils::clean_leadin($old_leadin);
    $random_questions[$question_no]['theme'] = $old_theme;
    $random_questions[$question_no]['q_id'] = $old_q_id;
    $random_questions[$question_no]['type'] = $old_q_type;
    $random_questions[$question_no]['leadin'] = $old_leadin;
    $random_questions[$question_no]['scenario'] = $old_scenario;
    $random_questions[$question_no]['correct'] = $old_correct;
    $random_questions[$question_no]['status'] = $old_status;
    $random_questions[$question_no]['display_last_edited'] = $display_last_edited;
    $random_questions[$question_no]['marks'] = qMarks($old_q_type, '', $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method);
    $random_questions[$question_no]['random_mark'] = qRandomMarks($old_q_type, '', $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
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

/**
 * Check the parts of a question to see if they contain equations and therefore need to include LaTeX processing code
 * @param $leadin
 * @param $scenario
 * @param $option_text
 * @param $score_method
 * @param $correct_fback
 * @param $feedback_right
 * @return int
 */
function check_latex($leadin, $scenario, $option_text, $score_method, $correct_fback, $feedback_right) {
  $latex = 0;

  // latex check [tex]
  if (strpos($leadin,'[tex]') !== false or strpos($scenario,'[tex]') !== false or strpos($option_text,'[tex]') !== false or strpos($score_method,'[tex]') !== false or strpos($correct_fback,'[tex]') !== false or strpos($feedback_right,'[tex]') !== false) {
    $latex = 1;
  }

  // latex check [tex]
  if (strpos($leadin,'[texi]') !== false or strpos($scenario,'[texi]') !== false or strpos($option_text,'[texi]') !== false or strpos($score_method,'[texi]') !== false or strpos($correct_fback,'[texi]') !== false or strpos($feedback_right,'[texi]') !== false) {
    $latex = 1;
  }

  // latex check $$
  if (strpos($leadin,'$$') !== false or strpos($scenario,'$$') !== false or strpos($option_text,'$$') !== false or strpos($score_method,'$$') !== false or strpos($correct_fback,'$$') !== false or strpos($feedback_right,'$$') !== false) {
    $latex = 1;
  }

  // latex check class="mee" (with or without quotes)
  if (check_latex_class(array($leadin, $scenario, $option_text, $score_method, $correct_fback, $feedback_right))) {
    $latex = 1;
  }

  return $latex;
}

/**
 * @param $candidates Array of candidate strings to check for inclusion of the MEE class
 * @return bool True if at least one of the candidates contains the class
 */
function check_latex_class($candidates) {
  foreach ($candidates as $candidate) {
    if (strpos($candidate,'class="mee"') !== false or strpos($candidate,'class=mee') !== false) {
      return true;
    }
  }
  return false;
}

/**
 * Check the random questions on the paper to see if they require LaTeX
 * @param $q_ids
 * @param $mysqli
 * @return int
 */
function check_latex_random($q_ids, $mysqli) {
  $q_ids = implode(',', $q_ids);
  $latex = 0;

  $result = $mysqli->prepare("SELECT leadin, scenario, option_text, score_method, correct_fback, feedback_right FROM questions INNER JOIN options ON questions.q_id = options.o_id WHERE questions.q_id IN ($q_ids)");
  $result->execute();
  $result->store_result();
  $result->bind_result($leadin, $scenario, $option_text, $score_method, $correct_fback, $feedback_right);
  while ($result->fetch()) {
    $latex = check_latex($leadin, $scenario, $option_text, $score_method, $correct_fback, $feedback_right);
    if ($latex == 1) {
      break;
    }
  }

  return $latex;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html onscroll="scrollXY();" onclick="hideMenus(); hideAssStatsMenu(event);">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rogō<?php echo ' ' . $configObject->get('rogo_version') . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/screen.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
  <link rel="stylesheet" type="text/css" href="../css/tipTip.css" />

  <!--[if lt IE 8]>
  <style type="text/css">
    td.ie-fullwidth {
      width: 100%!important;
    }
    #content td.t, td.t {
      width:158px;
      min-width:158px
    }
  </style>
  <![endif]-->

  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery-ui.1.8.16.min.js"></script>
  <script type="text/javascript" src="../js/jquery.tipTip.minified.js"></script>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
  <script type="text/javascript" src="../js/jquery.rquerystring.js"></script>
<script defer="defer" type="text/javascript">
  var paperID='<?php echo $_GET['paperID'] ?>';

  function addQID(qID, pID, clearall) {
    if (clearall) {
      document.PapersMenu.questionID.value = ',' + qID;
      document.PapersMenu.pID.value = ',' + pID;
    } else {
      document.PapersMenu.questionID.value = document.PapersMenu.questionID.value + ',' + qID;
      document.PapersMenu.pID.value = document.PapersMenu.pID.value + ',' + pID;
    }
  }

  function subQID(qID, pID) {
    var tmpq = ',' + qID;
    var tmpp = ',' + pID;
    document.PapersMenu.questionID.value = document.PapersMenu.questionID.value.replace(tmpq, '');
    document.PapersMenu.pID.value = document.PapersMenu.pID.value.replace(tmpp, '');
  }

  function clearAll() {
    $('.highlight').removeClass('highlight');
  }

  function selQ(questionNo, questionID, lineID, qType, screenNo, pID, current_pos, menuID, subparts, evt) {
    document.getElementById('menu2a').style.display = 'none';
    if (menuID == '2b') {
      document.getElementById('menu2c').style.display = 'none';
    } else {
      document.getElementById('menu2b').style.display = 'none';
    }
    document.getElementById('menu' + menuID).style.display = 'block';

    document.PapersMenu.questionNo.value = questionNo;
    document.PapersMenu.qType.value = qType;
    document.PapersMenu.screenNo.value = screenNo;
    document.PapersMenu.current_pos.value = current_pos;

    if (evt.ctrlKey == false) {
      clearAll();
      $('#link_' + lineID).addClass('highlight');
      addQID(questionID, pID, true);
    } else {
      if ($('#link_' + lineID).hasClass('highlight')) {
        $('#link_' + lineID).removeClass('highlight');
        subQID(questionID, pID);
      } else {
        $('#link_' + lineID).addClass('highlight');
        addQID(questionID, pID, false);
      }
    }

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

    if (evt != null) {
      evt.cancelBubble = true;
    }

    if (typeof deActivateAddBreak != 'undefined') {
      var deleteLink = $('#delete_break');
      deActivateDelete(deleteLink);
      var addLink = $('#add_break');
      activateAddBreak(addLink);
    }

    if (document.PapersMenu.questionID.value == '') {
      qOff();
    }
  }

  function edQ(questionNo, questionID, qType) {
    var loc = "../question/edit/index.php?q_id=" + questionID + "&paperID=<?php echo $paperID; ?>&folder=<?php if(isset($_GET['folder'])) echo $_GET['folder']; ?>&module=<?php if(isset($_GET['module'])) echo $_GET['module']; ?>&calling=paper&scrOfY=" + document.getElementById('scrOfY').value;
    if (qType == 'random' || qType == 'keyword_based') {
      loc += '&type=' + qType;
    }
    document.location = loc;
  }

  function qOff() {
    document.getElementById('menu2a').style.display = 'block';
    document.getElementById('menu2b').style.display = 'none';
    document.getElementById('menu2c').style.display = 'none';
    clearAll();

    document.getElementById('stats_menu').style.display = 'none';
    document.getElementById('copy_submenu').style.display = 'none';
    document.getElementById('change_screen_submenu').style.display='none';

    hideMenus();

    if (typeof deActivateAddBreak != 'undefined') {
      var addLink = $('#add_break');
      deActivateAddBreak(addLink);
    }
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
<?php
  $max_screen = 0;

  $result = $mysqli->prepare("SELECT MAX(screen) AS screen, MAX(display_pos), paper_ownerID, paper_title, pass_mark, users.title, users.initials, users.surname, folder, random_mark, total_mark, marking, paper_ownerID, DATE_FORMAT(start_date,'%Y%m%d%H%is') AS start_date, DATE_FORMAT(start_date,'{$configObject->get('cfg_long_date_time')}') AS display_start_date, DATE_FORMAT(end_date,'%Y%m%d%H%i') AS end_date, paper_type, deleted, latex_needed, retired, crypt_name, labs, calendar_year, exam_duration, display_question_mark, fullscreen, externals, internal_reviewers FROM (properties, users) LEFT JOIN papers ON properties.property_id=papers.paper WHERE property_id=? AND paper_ownerID=users.id GROUP BY paper_title LIMIT 1");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($max_screen, $max_display_pos, $paper_ownerID, $paper_title, $pass_mark, $title, $initials, $surname, $tmp_folder, $random_mark, $total_mark, $marking, $paper_ownerID, $start_date, $display_start_date, $end_date, $paper_type, $deleted, $latex_needed, $retired, $crypt_name, $labs, $session, $exam_duration, $display_question_mark, $fullscreen, $externals, $internal_reviewers);
  $result->fetch();
  $result->close();

  $paper_owner = $title  . ' ' . $initials . ', ' . $surname;

  if (date("YmdHis", time()) >= $start_date and date("YmdHis", time()) <= $end_date) {
    $active_date = 1;
  } else {
    $active_date = 0;
  }

  if (date("YmdHis", time()) >= $start_date and $paper_type == '2' and $start_date !== null) {
    $summative_lock = true;
  } else {
    $summative_lock = false;
  }

  if (!$summative_lock) {
?>
  <script type="text/javascript" src="../js/jquery.paperdetails.js"></script>
<?php
  }
?>
</head>
<body onscroll="scrollXY();"<?php if (isset($_GET['scrOfY'])) echo ' onload="window.scrollTo(0,' . $_GET['scrOfY'] . ');"'; ?> onselectstart="return false">

<?php
  if (!isset($paper_title)) {
  ?>
    <div id="left-sidebar" class="sidebar">
    </div>
    <div id="content" class="content"><br />
  <?php
    echo "<div style=\"position:absolute; left:230px; top:10px\"><img src=\"../artwork/orange_alert_48.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 class=\"midblue_header\" style=\"margin-left:70px;font-size:160%\">" . $string['papernotfound'] . "</h1>\n";
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"height:1px; border:none; margin-left:70px; color:#C0C0C0; background-color:#C0C0C0\" />\n<div style=\"margin-top:10px; margin-left:70px\">" . sprintf($string['furtherassistance'], $support_email, $support_email). "</div>\n";
    echo "</div>\n</body>\n</html>\n";
    $mysqli->close();
    exit;
  }
  if ($deleted != '') {
  ?>
    <div id="left-sidebar" class="sidebar">
    </div>
    <div id="content" class="content"><br />
  <?php
    echo "<div style=\"position:absolute;left:230px;top:10px\"><img src=\"../artwork/full_bin.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 class=\"midblue_header\" style=\"margin-left:70px;font-size:160%\">" . $string['paperdeleted'] . "</h1>\n";
    $deleted_parts = explode('[deleted', $paper_title);
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"height:1px;border:none;margin-left:70px;color:#C0C0C0;background-color:#C0C0C0\" />\n<p style=\"margin-top:10px; margin-left:70px\">" . sprintf($string['deleted_msg1'], $deleted_parts[0]) . "</p>\n\n<ul style=\"margin-left:80px\">\n";
    if ($paper_ownerID == $userObject->get_user_ID()) {
      echo "<li>" . $string['deleted_msg2'] . "</li>\n";
    } else {
      $result = $mysqli->prepare("SELECT title, surname, email FROM users WHERE id=?");
      $result->bind_param('i', $paper_ownerID);
      $result->execute();
      $result->bind_result($tmp_title, $tmp_surname, $tmp_email);
      $result->fetch();
      $result->close();
      echo "<li>" . sprintf($string['deleted_msg3'], $tmp_email, $tmp_title, $tmp_surname). "</li>\n";
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

  // Log the hit in recent_papers.
  $result = $mysqli->prepare("INSERT INTO recent_papers (userID, paperID, accessed) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE accessed=NOW();");
  $result->bind_param('ii', $userObject->get_user_ID(), $paperID);
  $result->execute();
  $result->close();

  // Get any questions to exclude.
  $excluded = array();
  $result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($q_id, $parts);
  while ($result->fetch()) {
    $excluded[$q_id] = $parts;
  }
  $result->close();

  $old_p_id = 0;
  $row_no = 0;
  $row_no2 = 0;
  $old_display_pos = -1;
  $temp_array = array();
  $latex = 0;
  $old_q_id = 0;
  $old_q_type  = '';
  $old_marks  = 0;
  $old_option_text = array();
  $old_o_media = array();
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
  $rnd_q_ids = array();

  // Get the questions (if any).
  $result = $mysqli->prepare("SELECT theme, ownerID, p_id, q_id, q_type, screen, leadin, scenario, option_text, o_media, correct, display_method, score_method, q_media, q_media_width, q_media_height, marks_correct, marks_incorrect, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS display_last_edited, display_pos, status, correct_fback, feedback_right, locked FROM (papers, questions) LEFT JOIN options ON questions.q_id = options.o_id WHERE paper=? AND papers.question=questions.q_id ORDER BY screen, display_pos, o_id");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->store_result();
  $result->bind_result($theme, $ownerID, $p_id, $q_id, $q_type, $screen, $leadin, $scenario, $option_text, $o_media, $correct, $display_method, $score_method, $q_media, $q_media_width, $q_media_height, $marks_correct, $marks_incorrect, $display_last_edited, $display_pos, $status, $correct_fback, $feedback_right, $locked);
  $temp_array = array();
  while ($result->fetch()) {

    if ($latex == 0) {
      if ($q_type == 'random') {
        $rnd_q_ids[] = $option_text;
      } else {
        $latex = check_latex($leadin, $scenario, $option_text, $score_method, $correct_fback, $feedback_right);
      }
    }
    // Check for negative marking
    if ($marks_incorrect < 0) {
      $neg_marking = true;
    }

    if ($old_q_id != $q_id or $old_display_pos != $display_pos) {
      if ($old_display_pos != -1) {
        $temp_array[$row_no2]['options'] = $options;
        if (empty($old_o_media)) {
          $temp_array[$row_no2]['o_media'] = array();
        } else {
          $temp_array[$row_no2]['o_media'] = $old_o_media;
        }
      }
      $options = 0;
      if ($old_q_type == 'random') {
        $temp_array[$row_no2]['original_marks'] = random_qMarks($temp_array[$row_no2]['random']);
        if ($temp_array[$row_no2]['status'] != 'Experimental') {
          $temp_array[$row_no2]['marks'] = $temp_array[$row_no2]['original_marks'];
          if (count($temp_array[$row_no2]['random']) > 0) {
            $total_random_mark += $temp_array[$row_no2]['random'][0]['random_mark'];
          }
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
          $total_random_mark += qRandomMarks($old_q_type, $tmp_exclude, $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
        }
      }
      if ($row_no2 > 0 and $temp_array[$row_no2]['status'] != 'Experimental') $total_marks += $temp_array[$row_no2]['marks'];
      $temp_array[$row_no2]['display_method'] = $old_display_method;
      $temp_array[$row_no2]['score_method'] = $old_score_method;
      if ($row_no2 > 0 and $paper_type < 3) checkProblems($paper_type, $old_q_type, $old_score_method, $temp_array, $old_scenario, $old_q_media, $row_no2, $temp_array[$row_no2]['original_marks'], $old_q_id, $excluded[$old_q_id], $old_option_text, $old_o_media, $old_correct, $temp_array[$row_no2]['status']);
      $old_correct = array();
      $old_option_text = array();
      $old_o_media = array();
      $old_marks = 0;
      $row_no2++;

      $row_no++;
      $temp_array[$row_no]['theme'] = $theme;
      $temp_array[$row_no]['screen'] = $screen;
      $temp_array[$row_no]['q_type'] = $q_type;
      $temp_array[$row_no]['leadin'] = $leadin;
      $temp_array[$row_no]['leadin'] = QuestionUtils::clean_leadin($temp_array[$row_no]['leadin']);

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
      $temp_array[$row_no]['questions_modules'] = QuestionUtils::get_modules($q_id, $mysqli);
      $temp_array[$row_no]['status'] = $status;
      $temp_array[$row_no]['warnings'] = '';
      $temp_array[$row_no]['random'] = array();
      $old_random_mark = $random_mark;
      $old_total_marks = $total_mark;

      if ($q_type == 'random') {
        $temp_array[$row_no]['random'] = randomDetails($q_id);
      }

      if ($summative_lock and $locked == '') {
        QuestionUtils::lock_question($q_id, $mysqli);
      }

      // Set the question modules to that of the paper if the question is not on a module.
      if (count($temp_array[$row_no]['questions_modules']) == 0) {
        $paper_modules = Paper_utils::get_modules($_GET['paperID'], $mysqli);
        QuestionUtils::add_modules($paper_modules, $q_id, $mysqli);
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
    if (trim($o_media != '')) $old_o_media[] = $o_media;
    $old_marks = $marks_correct;
    if (!empty($option_text) or (!empty($correct) and (in_array($q_type, array('labelling', 'hotspot', 'area', 'true_false')))) or in_array($q_type, array('info', 'likert', 'flash'))) $options++;
  }
  $result->close();

  if ($row_no > 0) {
    $temp_array[$row_no]['options'] = $options;
    $temp_array[$row_no]['o_media'] = $old_o_media;
    if ($old_q_type == 'random') {
      $temp_array[$row_no2]['original_marks'] = random_qMarks($temp_array[$row_no2]['random']);
      if ($temp_array[$row_no2]['status'] != 'Experimental') {
        $temp_array[$row_no2]['marks'] = $temp_array[$row_no2]['original_marks'];
        $total_random_mark += isset($temp_array[$row_no2]['random'][0]['random_mark']) ?  $temp_array[$row_no2]['random'][0]['random_mark'] : 0;
      }
    } else {
      $temp_array[$row_no2]['original_marks'] = qMarks($old_q_type, $excluded[$old_q_id], $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method);
      if ($temp_array[$row_no2]['status'] != 'Experimental') {
        $temp_array[$row_no2]['marks'] = $temp_array[$row_no2]['original_marks'];
        $total_random_mark += qRandomMarks($old_q_type, $excluded[$old_q_id], $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
      }
    }
    if ($temp_array[$row_no2]['status'] != 'Experimental') $total_marks += $temp_array[$row_no2]['marks'];
    $temp_array[$row_no2]['display_pos'] = $old_display_pos;
    $temp_array[$row_no2]['score_method'] = $old_score_method;
    if ($paper_type < 3) checkProblems($paper_type, $old_q_type, $old_score_method, $temp_array, $old_scenario, $old_q_media, $row_no2, $temp_array[$row_no2]['original_marks'], $old_q_id, $excluded[$old_q_id], $old_option_text, $old_o_media, $old_correct, $temp_array[$row_no2]['status']);

    // If we had random questions on paper need to check if they need LaTeX
    if ($latex == 0 and count($rnd_q_ids) > 0) {
      $latex = check_latex_random($rnd_q_ids, $mysqli);
    }

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
    $paper_modules = explode(',', $module);
    if (count($paper_modules) > 0) {     // Paper is on multiple modules
      if ($userObject->has_role('Admin')) {
        $module = $paper_modules[0];
      } else {
        for ($i=count($paper_modules)-1; $i>0; $i--) {
          if (in_array($paper_modules[$i], $staff_modules)) {
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
    $paper_modules = Paper_utils::get_modules($_GET['paperID'],$mysqli);  // Get the modules from paper properties
    $module = array_slice($paper_modules,0,1);
    $module = $module[0];
    $folder = '';
  }

  if ($userObject->has_role('Admin')) {
    $OKmodules = array();
    $module_split = explode(',', $module);
    foreach ($module_split as $individual_module) {
      if (in_array(strtoupper($individual_module), $staff_modules)) {
        $OKmodules[] = $individual_module;
      }
    }
    $module = implode(',', $OKmodules);
  }

  echo "<table style=\"table-layout: fixed\" class=\"header\" id=\"sortable\">\n";

  //blank row to preserve table layout when using table-layout: fixed - needed to increase ie8 latex rendering speed
  echo "<tr><td class=\"icon\"></td><td class=\"q_no\"></td><td></td><td class=\"t\"></td><td class=\"m\"></td><td class=\"d\"></td></tr>";

  echo "<tr><th colspan=\"5\"><div class=\"breadcrumb\">";
  if ($module != '') {
    $module_code = module_utils::get_moduleid_from_id($module, $mysqli);
    echo '<a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $module . '">' . $module_code . '</a>';
  } elseif ($folder != '') {
    echo '<a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } else {
    echo '<a href="../staff/index.php">' . $string['home'] . '</a>';
  }
  echo '</div><div onclick="qOff()" style="font-size:220%; font-weight:bold; margin-left:10px"';
  if ($retired != '') {
    echo ' class="retired"';
  }
  echo '>' . $paper_title . '</div>';
  echo "</th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(1); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></th></tr>\n";
  if ($retired == '') {
    echo "<tr>\n";
  } else {
    echo "<tr class=\"retired\">\n";
  }
  if ($userObject->has_role('Demo')) {
    $paper_owner = 'Mr J, Bloggs';
  }
  echo "<th colspan=\"3\" style=\"font-size:90%;padding-left:10px\"><strong>" . $string['start'] . ":</strong> ";
  if ($display_start_date == '') {
    echo '<span style="color:#808080">&lt;unscheduled&gt;</span>';
  } else {
    echo $display_start_date;
  }
  echo "</th><th colspan=\"3\" style=\"text-align:right;font-size:90%\"><strong>" . $string['owner'] . ":</strong> $paper_owner&nbsp;</th></tr>\n";
  if ($retired == '') {
    echo '<tr class="details-head">';
  } else {
    echo '<tr class="details-head retired">';
  }
  ?>
    <th class="icon">&nbsp;</th>
    <th>&nbsp;</th>
    <th class="q-cell"><?php echo $string['question']; ?></th>
    <th class="t vert_div">&nbsp;<?php echo $string['type']; ?>&nbsp;</th>
    <th class="m vert_div">&nbsp;<?php echo $string['marks']; ?>&nbsp;</th>
    <th class="d vert_div">&nbsp;<?php echo $string['modified']; ?>&nbsp;</th>
    </tr>
    <tr><th colspan="6" class="bevel"></th></tr>
  <?php

  if ($summative_lock) {
    echo "<tr><td colspan=\"2\" style=\"text-align:right; vertical-align:middle\"><div class=\"yellowwarn\"><img src=\"../artwork/paper_locked_padlock.png\" width=\"19\" height=\"24\" alt=\"Locked\" style=\"position:relative; top:2px\" />&nbsp;&nbsp;</div></td><td colspan=\"3\" style=\"vertical-align:middle\"><div class=\"yellowwarn\">" . $string['paperlockedwarning'] . " <a href=\"#\" class=\"blacklink\" onclick=\"launchHelp(189); return false;\">". $string['paperlockedclick'] ."</a></div></td><td style=\"text-align:right\"><div class=\"yellowwarn\">";
    if ($userObject->has_role('Admin')) {
      $record_no = 0;
      $result = $mysqli->prepare("SELECT COUNT(log_metadata.id) FROM log_metadata, users WHERE paperID=? AND log_metadata.userID=users.id AND roles='Student'");
      $result->bind_param('i', $paperID);
      $result->execute();
      $result->bind_result($record_no);
      $result->fetch();
      $result->close();

      if ($record_no == 0) {
        echo '<input type="button" name="unlock" value=" ' . $string['unlock'] . ' " onclick="window.location=\'details.php?paperID=' . $paperID . '&module=' . $module . '&folder=' . $folder . '&scrOfY=0&unlock=1\'" />';
      } else {
        echo '<input type="button" name="unlock" value=" ' . $string['unlock'] . ' " disabled />';
      }
    }
    echo "&nbsp;</div></td></tr>\n";
  } elseif ($paper_type == '2' and $start_date !== null) {
    $tmp_hour = $tmp_start_hour;
    if (substr($tmp_hour,0,1) == '0') $tmp_hour = substr($tmp_hour,1,1);
    if (substr($display_start_date,6,4) > (date("Y")+1)) {
      echo "<tr><td colspan=\"2\" style=\"text-align:right; vertical-align:middle\" class=\"redwarn\"><img src=\"../artwork/late_warning_icon.png\" style=\"padding-top:1px; padding-right:10px\" width=\"28\" height=\"28\" alt=\"Locked\" /></td><td colspan=\"4\" class=\"redwarn\">";
      printf($string['farfuturewarning'], $display_start_date);
      echo "</td></tr>\n";
    } elseif ( $tmp_hour < $configObject->get( 'cfg_hour_warning' ) ) {
      echo "<tr><td colspan=\"2\" style=\"text-align:right; vertical-align:middle\" class=\"redwarn\"><img src=\"../artwork/late_warning_icon.png\" style=\"padding-top:1px; padding-right:10px\" width=\"28\" height=\"28\" alt=\"Locked\" /></td><td colspan=\"4\" class=\"redwarn\">";
      printf($string['earlywarning'], $configObject->get( 'cfg_hour_warning' ) );
      echo "</td></tr>\n";
    }
  }

  $q_screen = array();
  $screen_marks = 0;
  $old_screen = 0;
  $question_number = 0;
  $marks_incorrect_error = false;
  $paper_warnings = array();
  for ($x=1; $x<=$row_no; $x++) {
    if ($temp_array[$x]['options'] == 0 and isset($temp_array[$x]['o_media']) and count($temp_array[$x]['o_media']) == 0) $temp_array[$x]['warnings'] .= $string['nooptionsdefined'];
    if ($temp_array[$x]['status'] == 'Incomplete') $paper_warnings['Incomplete'][] = $question_number + 1;
    if ($temp_array[$x]['status'] == 'Beta') $paper_warnings['Beta'][] = $question_number + 1;
    if ($temp_array[$x]['status'] == 'Retired') $paper_warnings['Retired'][] = $question_number + 1;
    if ($old_screen != $temp_array[$x]['screen']) {
      if ($old_screen > 0) {
        $tmp_screen_mean = ($total_marks == 0) ? 0 : ($screen_marks / $total_marks);
      }
      $screen_marks = 0;
      if ($old_screen < ($temp_array[$x]['screen'] - 1)) {
        for ($missing=1; $missing<($temp_array[$x]['screen'] - $old_screen); $missing++) {
          echo '<tr id="link_break' . ($old_screen + $missing) . '" class="breakline qline screenerror"><td colspan="6" class="ie-fullwidth"><h4><span class="opaque">' . $string['screen'] . '&nbsp' . ($old_screen + $missing) . '</span></h4></td></tr>';
          echo '<tr><td colspan="6" style="height:55px; background-image:url(../artwork/no_questions_gradient.png); repeat:repeat-x; background-color:#FFC0C0; padding-left:15px; padding-top:4x">' . $string['noquestionscreen'] . '</td></tr>';
        }
      }
      echo '<tr id="link_break' . $temp_array[$x]['screen'] . '" class="breakline qline"><td colspan="6" class="ie-fullwidth"><h4><span class="subsect opaque">' . $string['screen'] . '&nbsp' . $temp_array[$x]['screen'] . '&nbsp;</span></h4></td></tr>';
    }
    $old_screen = $temp_array[$x]['screen'];

    if ($q_highlight == $temp_array[$x]['display_pos']) {
      echo "<script defer language=\"JavaScript\">\n";
      echo "document.getElementById('menu2a').style.display = 'none';\n";
      echo "document.getElementById('menu2c').style.display = 'none';\n";
      echo "document.getElementById('menu2b').style.display = 'block';\n";
      echo "document.PapersMenu.questionNo.value = '" . ($question_number+1) . "';\n";
      echo "document.PapersMenu.questionID.value = '" . $temp_array[$x]['q_id'] . "';\n";
      echo "document.PapersMenu.qType.value = '" . $temp_array[$x]['q_type'] . "';\n";
      echo "document.PapersMenu.screenNo.value = '" . $temp_array[$x]['screen'] . "';\n";
      echo "document.PapersMenu.pID.value = '" . $temp_array[$x]['p_id'] . "';\n";
      echo "document.PapersMenu.current_pos.value = " . $temp_array[$x]['display_pos'] . ";\n";
      echo "document.PapersMenu.oldQuestionID.value = '$x';\n";
      echo "</script>\n";
    }

    $higlight_class = '';
    if ($temp_array[$x]['status'] == 'Experimental' or $temp_array[$x]['status'] == 'Retired') {
      $higlight_class = ' experimental';
    } elseif ($temp_array[$x]['marks'] == 0 and $temp_array[$x]['q_type'] != 'info' and $paper_type != '3' and $paper_type != '4' and $excluded[$temp_array[$x]['q_id']] != NULL) {
      $higlight_class = ' excluded';
    }

    $theme_class = '';
    $theme_str = '';
    if (trim($temp_array[$x]['theme']) != '') {
      $theme_class = ' q_theme';
      $theme_str = "<h4 class=\"theme\">" . trim($temp_array[$x]['theme']) . "</h4>\n";
    }

    echo "<tr id=\"link_$x\" class=\"link_$x qline{$theme_class}";
    if ($q_highlight == $temp_array[$x]['display_pos']) {
      echo '; background-color:#B3C8E8';
    } else {
      echo $higlight_class;
    }

    $prevous_screen = '';
    $next_screen = '';
    if ($temp_array[$x]['q_type'] != 'info') {
      $q_screen[$temp_array[$x]['q_id']][] = ($question_number+1);
    }

    if (isset($temp_array[$x - 1]['screen'])) {
      $prevous_screen = $temp_array[$x - 1]['screen'];
    }
    $next_screen = '';
    if (isset($temp_array[$x + 1]['screen'])) {
      $next_screen = $temp_array[$x + 1]['screen'];
    }

    if ($summative_lock) {
      echo "\" onclick=\"selQ(" . ($question_number+1) . ",'" . $temp_array[$x]['q_id'] . "',$x,'" . $temp_array[$x]['q_type'] . "'," . $temp_array[$x]['screen'] . "," . $temp_array[$x]['p_id'] . "," . $temp_array[$x]['display_pos'] . ",'2c'," . count($temp_array[$x]['random']) . ",event);\" ondblclick=\"edQ(" . ($question_number+1) . "," . $temp_array[$x]['q_id'] . ",'" . $temp_array[$x]['q_type'] . "');\">";
    } else {
      echo "\" onclick=\"selQ(" . ($question_number+1) . ",'" . $temp_array[$x]['q_id'] . "',$x,'" . $temp_array[$x]['q_type'] . "'," . $temp_array[$x]['screen'] . "," . $temp_array[$x]['p_id'] . "," . $temp_array[$x]['display_pos'] . ",'2b'," . count($temp_array[$x]['random']) . ",event);\" ondblclick=\"edQ(" . ($question_number+1) . "," . $temp_array[$x]['q_id'] . ",'" . $temp_array[$x]['q_type'] . "');\">";
    }

    echo '<td>';
    if ($temp_array[$x]['q_type'] == 'random') {
      $dice_no = rand(1,6);
      if ($temp_array[$x]['leadin'] == '') $temp_array[$x]['leadin'] = 'Random question block';
      echo '<img src="../artwork/dice' . $dice_no . '.png" width="14" height="14" alt="folder" border="0" style="position:relative; left:1px;" />';
    }
    echo '</td>';

    if ($temp_array[$x]['q_type'] == 'info') {
      echo '<td class="q_no"><img src="../artwork/black_white_info_icon.png" width="6" height="12" alt="Info" />&nbsp;</td>';
    } else {
      $question_number++;
      echo "<td class=\"q_no\">$question_number.</td>";
    }

    echo "<td class=\"l\">";
    echo $theme_str;
    if ($temp_array[$x]['q_type'] == 'random') {
      echo $temp_array[$x]['leadin'];
      if ($temp_array[$x]['warnings'] != '') echo '<span style="color:#C00000; font-weight:bold">&nbsp;<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="' . $string['warning'] . '" border="0" />&nbsp;' . $temp_array[$x]['warnings'] . '</span>';
    } elseif ($temp_array[$x]['leadin'] != '') {
      echo $temp_array[$x]['leadin'];
      if ($excluded[$temp_array[$x]['q_id']] != NULL) echo ' <img src="../artwork/exclude_small.gif" width="15" height="11" alt="Excluded" />';
      if ($temp_array[$x]['warnings'] != '') echo '<span style="color:#C00000; font-weight:bold">&nbsp;<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="' . $string['warning'] . '" border="0" />&nbsp;' . $temp_array[$x]['warnings'] . '</span>';
    } elseif (strpos($temp_array[$x]['q_media'],'.swf') !== false) {
      echo "<img src=\"../artwork/flash_icon.png\" width=\"48\" height=\"48\" alt=\"Embedded Flash object\" border=\"0\" />";
    } elseif (strpos($temp_array[$x]['q_media'],'.flv') !== false) {
      echo "<img src=\"../artwork/flash_icon.png\" width=\"48\" height=\"48\" alt=\"Embedded Flash object\" border=\"0\" />";
    } else {
      echo "<img src=\"../media/" . $temp_array[$x]['q_media'] . "\" width=\"" . ($temp_array[$x]['q_media_width'] / 3) . "\" height=\"" . ($temp_array[$x]['q_media_height'] /3) . "\" alt=\"Media file\" border=\"1\" />";
    }
    echo "</td>";

    echo '<td class="t">';
    // Display position out of sync.
    if ($x <> $temp_array[$x]['display_pos']) {
      $temp_array[$x]['display_pos'] = $x;
      $editPaper = "UPDATE papers SET display_pos=$x WHERE p_id=" . $temp_array[$x]['p_id'];
      if (!$mysqli->query($editPaper)) {
        display_error("Paper order Error","Problem with query: $editPaper");
      }
    }

    echo $string[$temp_array[$x]['q_type']] . '</td>';
    if ($paper_type == '3' or $paper_type == '6') {
      echo '<td style="text-align:right; vertical-align:top; color:#C0C0C0">' . $string['na'] . '</td>';
    } elseif ($paper_type == '4') {
      $temp_array[$x]['score_method'] = str_replace('|',',',$temp_array[$x]['score_method']);
      $temp_array[$x]['score_method'] = str_replace(',false','',$temp_array[$x]['score_method']);
      echo '<td style="text-align:right; vertical-align:top">' . $temp_array[$x]['marks'] . '</td>';
    } elseif ($temp_array[$x]['q_type'] == 'info' or $temp_array[$x]['q_type'] == 'keyword_based') {
      echo '<td>&nbsp;</td>';
    } else {
      if ($temp_array[$x]['status'] !== 'Experimental' and $temp_array[$x]['marks'] === 'ERR') {
        // Only ever get in here for random questions
        if (count($temp_array[$x]['marks']) > 0) {
          echo '<td style="text-align:right; vertical-align:top"><img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" title="' . $string['variablenomarks'] . '" alt="' . $string['variablenomarks'] . '" border="0" /></td>';
        }
        $marks_incorrect_error = true;
      } elseif ($temp_array[$x]['status'] === 'Experimental') {
        echo '<td style="text-align:right; vertical-align:top">' . $string['na'] . '</td>';
      } else {
        echo '<td class="m">' . $temp_array[$x]['marks'] . '</td>';
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
        echo "<tr style=\"display:none\" ondblclick=\"edQ(" . ($question_number+1) . "," . $random_question['q_id'] . ",'" . $random_question['type'] . "');\" id=\"r" . $x . "_" . $sub_question . "\"><td></td><td></td><td class=\"s\">&#149&nbsp;" . $random_question['leadin'] . "</td><td class=\"t\">" . fullQuestionType($random_question['type'], $string) . "</td>";
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
    if ($row_no > 0 and $paper_type != '3' and $paper_type != '4') {
      echo "<tr><td colspan=\"4\"></td><td id=\"marks_total\" style=\"border-top:1px solid black; padding-right:4px\" align=\"right\">";
      if ($marks_incorrect_error == true) {
        echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="' . $string['variablenomarks'] . '" border="0" />';
      } else {
        echo $total_marks;
      }
      echo "</td><td><nobr>&nbsp;&nbsp;" . $string['passmark'] . ":&nbsp;$pass_mark%&nbsp;</nobr></td></tr>\n";
      echo "<tr><td colspan=\"4\"></td><td style=\"color:#808080; text-align:right\">" . round($total_random_mark,2) . "&nbsp;</td><td style=\"color:#808080\">(" . round(((round($total_random_mark,2) / $total_marks) * 100), 0) . "%) " . $string['randommark'] . "</td></tr>\n";
    }
  }

  if ($paper_type != '3') {
    check_duplicates($q_screen);
  }

  // Final paper warnings.
  if ($paper_type == '2') {
    if ($summative_lock) {
      $warning_types = array('Incomplete','Beta');
    } else {
      $warning_types = array('Incomplete','Beta','Retired');
    }
    foreach ($warning_types as $warning_type) {
      if (isset($paper_warnings[$warning_type]) and count($paper_warnings[$warning_type]) > 0) {
        echo "<tr><td colspan=\"2\" class=\"warnicon\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td colspan=\"4\" class=\"warn\"><strong>The following questions are '$warning_type':</strong> ";
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

<div id="response"></div>
</div>

</body>
</html>
