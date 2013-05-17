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
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_student_auth.inc';

require_once '../include/demo_replace.inc';
require_once '../include/mapping.inc';
require_once '../include/errors.inc';
require_once '../include/feedback.inc';
require_once '../include/sort.inc';
require_once '../include/calculate_marks.inc';

require_once '../classes/logger.class.php';
require_once '../classes/paperproperties.class.php';
require_once '../classes/paperutils.class.php';

check_var('id', 'GET', true, false, false);

$logger = new Logger($mysqli);

if ($userObject->has_role('Demo')) {
  $demo = true;
} else {
  $demo = false;
}

if (isset($_GET['userID'])) {
  if ($userObject->has_role(array('SysAdmin', 'Admin', 'Staff'))) {
    if ($_GET['userID'] != '') {
      $userID = $_GET['userID'];
    } else {
      display_error($string['idmissing'], $string['idmissing_msg'], false, true, false);
    }
  } else {  // Student is trying to hack into another students userID on the URL.
    header("HTTP/1.0 404 Not Found");
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  }
} else {
  $userID = $userObject->get_user_ID();
}

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli);

if (!$propertyObj) {
  header("HTTP/1.0 404 Not Found");
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

//check the feedback has been released !!!
if ($userObject->has_role('Student')) {
  if (!$propertyObj->is_objective_fb_released()) {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  }
}

if (!isset($_GET['ordering'])) {
  $ordering = 'screen';
  $direction = 'asc';
}

$paperID        = $propertyObj->get_property_id();
$paper_title    = $propertyObj->get_paper_title();
$paper_type     = $propertyObj->get_paper_type();
$session        = $propertyObj->get_calendar_year();
$pass_mark      = $propertyObj->get_pass_mark();
$random_mark    = $propertyObj->get_random_mark();
$total_mark     = $propertyObj->get_total_mark();
$marking        = $propertyObj->get_marking();
$exam_duration  = $propertyObj->get_exam_duration();
$start_date     = $propertyObj->get_start_date();
$end_date       = $propertyObj->get_end_date();

if ($userObject->has_role('Student')) {
  $logger->record_access($userObject->get_user_ID(), 'Objectives-based feedback report', $paperID);  // Students write in the paperID
} else {
  $logger->record_access($userObject->get_user_ID(), 'Objectives-based feedback report', '/mapping/user_feedback.php?' . $_SERVER['QUERY_STRING']);    // Staff write in the URL details
}
$moduleID = Paper_utils::get_modules($paperID, $mysqli);

//check the user sat the paper!
$bound = false;
if ($paper_type == '0' or $paper_type == '1') {
  $result = $mysqli->prepare("SELECT DATE_FORMAT(started,'%H:%i:%s') AS started, DATE_FORMAT(updated,'%H:%i:%s') AS updated FROM log0, log_metadata WHERE log0.metadataID = log_metadata.id AND paperID = ? AND userID = ? UNION SELECT DATE_FORMAT(started,'%H:%i:%s') AS started, DATE_FORMAT(updated,'%H:%i:%s') AS updated FROM log1, log_metadata WHERE log1.metadataID = log_metadata.id AND paperID = ? AND userID = ? LIMIT 1");
  $result->bind_param('iiii', $paperID, $userID, $paperID, $userID);
  $bound = true;
} elseif ($paper_type == '4') {
  $result = $mysqli->prepare("SELECT DATE_FORMAT(started,'%H:%i:%s') AS started, NULL AS updated FROM log4 WHERE q_paper = ? AND userID = ? LIMIT 1");
} elseif ($paper_type == '5') {
  $result = $mysqli->prepare("SELECT DATE_FORMAT(started,'%H:%i:%s') AS started, NULL AS updated FROM log5, log_metadata WHERE log$paper_type.metadataID = log_metadata.id AND paperID = ? AND userID = ? LIMIT 1");
} else {
  $result = $mysqli->prepare("SELECT DATE_FORMAT(started,'%H:%i:%s') AS started, DATE_FORMAT(updated,'%H:%i:%s') AS updated FROM log$paper_type, log_metadata WHERE log$paper_type.metadataID = log_metadata.id AND paperID = ? AND userID = ? ORDER BY screen DESC LIMIT 1");
}
if (!$bound) {
  $result->bind_param('ii', $paperID, $userID);
}
$result->execute();
$result->bind_result($started, $updated);
$result->store_result();
$result->fetch();
if ($result->num_rows == 0) {
  header("HTTP/1.0 404 Not Found");
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
$result->close();

$start_seconds  = (substr($started,0,2) * 60 * 60) + (substr($started,3,2) * 60) + substr($started,6,2);
$updated        = (substr($updated,0,2) * 60 * 60) + (substr($updated,3,2) * 60) + substr($updated,6,2);
$time_spent     = $updated - $start_seconds;

$result = $mysqli->prepare("SELECT username, title, initials, surname FROM users WHERE id = ?");
$result->bind_param('i', $userID);
$result->execute();
$result->bind_result($tmp_username, $title, $initials, $surname);
$result->fetch();
$result->close();
$student_name = $title . ' ' . demo_replace($initials, $demo) . ' ' . demo_replace($surname, $demo);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['examfeedback']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
  <style type="text/css">
    body {font-size:90%}
    h1 {margin-left:5px;}
    td {font-size:100%}
    .q_no {text-align:right; vertical-align:top; cursor:pointer}
    .divider {font-size:90%; font-weight:bold}
    .mapping {font-size:90%;color:#FF6300;font-weight:normal}
    a {text-decoration:none}
    li {list-style:none; padding-bottom:5px}
    p {padding:5px}
    h1 {font-size:120%; font-weight:bold; color:#1E3287}
    .r {text-align:right}
    .c {text-align:center}
    .symbol {width:24px; text-align:center}
  </style>
</head>
<body>
    <table style="position:relative; border: 1px solid #808080; -moz-border-radius:4px; -webkit-border-radius:4px; border-radius:4px; box-shadow:3px 3px 3px rgba(100, 100, 100, 0.50); z-index:10; float:right; top:10px; right:10px; font-size:90%; background-color:#FFFFEE; margin-bottom:8px; padding-left:6px; padding-right:6px">
    <tr><td><img src="../artwork/ok_comment.png" width="16" height="16" alt="Completely/Mostly acquired" /></td><td><?php echo $string['greenicon']; ?></td></tr>
    <tr><td><img src="../artwork/minor_comment.png" width="16" height="16" alt="Partically acquired" /></td><td><?php echo $string['ambericon']; ?></td></tr>
    <tr><td><img src="../artwork/major_comment.png" width="16" height="16" alt="Mostly not acquired" /></td><td><?php echo $string['redicon']; ?></td></tr>
    <tr><td colspan="2"><?php echo $string['relativekey']; ?></td></tr>
    <tr><td colspan="2"><?php echo $string['question']; ?></td></tr>
    </table>
  <?php
  echo "<div style=\"position:absolute; top:0px; left:0px\">\n";
  echo "<table class=\"header\">\n";
  echo "<tr><th style=\"padding:10px\"><div style=\"font-size:220%; font-weight:bold\">$paper_title</div>\n";
  echo "<div><strong>$student_name " . $string['feedback'] . "</strong></div></th></tr>\n";
  echo "<tr><th class=\"bevel\"></th></tr>\n";

  if ($userObject->has_role(array('SysAdmin', 'Admin', 'Staff')) and !isset($_GET['userID'])) {
    echo "<tr><td class=\"yellowwarn\"><div style=\"margin-left:10px\">" . $string['staffmsg'] . "</div></td></tr>\n";
  }


  //get Cohort Data
  $tmp_start_date  = DateTime::createFromFormat('U', $start_date);
  $tmp_end_date    = DateTime::createFromFormat('U', $end_date);
  $chort_question_data = getCohortData($mysqli, $moduleID, $tmp_start_date->format('YmdHis'), $tmp_end_date->format('YmdHis'), '%', '%', '%', $paperID, $paper_type, '');

  //get users log data excluding exclued questions
  $qid_list = '';
  $question_data = array();

  $startedSQL = '';
  if (isset($_GET['started'])) {
    $startedSQL = ' AND started = "' . $_GET['started'] . '"';;
  }

  if ($paper_type == '0' or $paper_type == '1') {
    $sql = "SELECT q_id, mark, totalpos, started FROM log0, log_metadata WHERE log0.metadataID = log_metadata.id AND q_id NOT IN (SELECT q_id FROM question_exclude WHERE paperID = ?) AND userID = ? AND paperID = ? $startedSQL UNION SELECT q_id, mark, totalpos, started FROM log1, log_metadata WHERE log1.metadataID = log_metadata.id AND q_id NOT IN (SELECT q_id FROM question_exclude WHERE paperID = ?) AND userID = ? AND paperID = ? $startedSQL ORDER BY q_id, started";
  } elseif ($paper_type == '4') {
    $sql = "SELECT q_id, rating, NULL, NULL AS totalpos FROM log4 WHERE q_id NOT IN (SELECT q_id FROM question_exclude WHERE q_paper = ?) AND userID = ? AND q_paper = ? $startedSQL ORDER BY q_id, started";
  } else {
    $sql = "SELECT q_id, mark, totalpos, NULL FROM log$paper_type, log_metadata WHERE log$paper_type.metadataID = log_metadata.id AND q_id NOT IN (SELECT q_id FROM question_exclude WHERE paperID = ?) AND userID = ? AND paperID = ? $startedSQL ORDER BY q_id, started";
  }
  $result = $mysqli->prepare($sql);
  if ($paper_type == '0' or $paper_type == '1') {
    $result->bind_param('iiiiii', $paperID, $userID, $paperID, $paperID, $userID, $paperID);
  } else {
    $result->bind_param('iii', $paperID, $userID, $paperID);
  }
  $result->execute();
  $result->bind_result($q_id, $mark, $totalpos, $tmp_started);
  $total_student_mark = 0;
  while ($result->fetch()) {
    if (is_string($totalpos)) {
      $question_data[$q_id]['totalpos'] = count(explode('|', $totalpos)) - 2;
    } else {
      $question_data[$q_id]['totalpos'] = $totalpos;
    }
    $total_student_mark += $mark;
    $question_data[$q_id]['mark'] = $mark;
    $qid_list .= $q_id . ',';
  }
  $result->close();

  if ($paper_type == '4') {   // Get the maximum marks for OSCE station questions.
    $result = $mysqli->prepare("SELECT q_id, q_type, display_method, score_method FROM questions, papers WHERE papers.question = questions.q_id AND paper = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($q_id, $q_type, $display_method, $score_method);
    $total_student_mark = 0;
    while ($result->fetch()) {
      $question_marks = 1;
      $question_data[$q_id]['totalpos'] = qMarks($q_type, '', $question_marks, '', '', $display_method, $score_method);
    }
  }

  $objectives = array();
  $qid_list = substr($qid_list,0,-1);
  $objByModule = getObjectivesByMapping($moduleID, $session, $paperID, $qid_list, $mysqli);

  unset($objByModule['none_of_the_above']);

  if (count($objByModule) > 0) {
    foreach ($objByModule as $module => $mappings) {
      foreach ($mappings as $id => $mappingData) {
        if ($mappingData['session']['class_code'] != '') {
          $sessiontitle = $mappingData['session']['class_code'];
        } else {
          $sessiontitle = $mappingData['session']['title'];
        }
        $objectives[$id] = $mappingData;
        foreach ($mappingData['mapped'] as $q_id) {
          if (isset($objectives[$id]['questions'])) {
            $objectives[$id]['questions']++;
          } else {
            $objectives[$id]['questions'] = 1;
          }
          if (isset($objectives[$id]['totalpos_sum'])) {
            $objectives[$id]['totalpos_sum'] += $question_data[$q_id]['totalpos'];
          } else {
            $objectives[$id]['totalpos_sum'] = $question_data[$q_id]['totalpos'];
          }
          if (isset($objectives[$id]['mark_sum'])) {
            $objectives[$id]['mark_sum'] += $question_data[$q_id]['mark'];
          } else {
            $objectives[$id]['mark_sum'] = $question_data[$q_id]['mark'];
          }
          $objectives[$id]['session']['sessiontitle'] = $sessiontitle;

          // Just in case the is no cohort data because the paper has not been sat, set zeros to stop errors further down.
          if (!isset($chort_question_data[$q_id]['totalpos'])) $chort_question_data[$q_id]['totalpos'] = 0;
          if (!isset($chort_question_data[$q_id]['mark'])) $chort_question_data[$q_id]['mark'] = 0;

          if (isset($objectives[$id]['chort_totalpos_sum'])) {
            $objectives[$id]['chort_totalpos_sum'] += $chort_question_data[$q_id]['totalpos'];
          } else {
            $objectives[$id]['chort_totalpos_sum'] = $chort_question_data[$q_id]['totalpos'];
          }
          if (isset($objectives[$id]['chort_mark_sum'])) {
            $objectives[$id]['chort_mark_sum'] += $chort_question_data[$q_id]['mark'];
          } else {
            $objectives[$id]['chort_mark_sum'] = $chort_question_data[$q_id]['mark'];
          }
        }
        if ($objectives[$id]['totalpos_sum'] == 0) {
          $objectives[$id]['ratio'] = 0;
        } else {
          $objectives[$id]['ratio'] = $objectives[$id]['mark_sum'] / $objectives[$id]['totalpos_sum'];
        }

        if ($objectives[$id]['chort_totalpos_sum'] == 0) {
          $objectives[$id]['chort_ratio'] = 0;
        } else {
          $objectives[$id]['chort_ratio'] = $objectives[$id]['chort_mark_sum'] / $objectives[$id]['chort_totalpos_sum'];
        }
      }
    }
    $objectives = array_csort($objectives, 'ratio', 'desc');
  }

  if (count($objectives) == 0) {
    echo "<tr><td class=\"redwarn\" style=\"height:30px; padding-left:10px\">" . $string['notmapped'] . "</td></tr></table>\n</body>\n</html>\n";
    exit;
  }
  echo "</table>\n";

  //Display the feedback
  ?>
  <h1><?php echo $string['learningobjectives']; ?></h1>
  <p><?php echo $string['explanation']; ?></p>
  <?php

  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%; line-height:150%\">\n";
  echo "<tr><td style=\"border-top: 1px solid #D6E5F5\">&nbsp;</td><td style=\"border-top: 1px solid #D6E5F5\"><img src=\"../artwork/vertical_spacer.gif\" width=\"1\" height=\"21\" alt=\"\" /></td><td colspan=\"3\" style=\"border-top: 1px solid #D6E5F5; color:#1E3287\">&nbsp;<nobr>" . $string['yourmark'] . "&nbsp;</nobr></td><td style=\"border-top: 1px solid #D6E5F5\"><img src=\"../artwork/vertical_spacer.gif\" width=\"1\" height=\"21\" alt=\"\" /></td><td style=\"border-top: 1px solid #D6E5F5; color:#1E3287\">&nbsp;" . $string['relative'] . "&nbsp;</td><td style=\"border-top: 1px solid #D6E5F5\"><img src=\"../artwork/vertical_spacer.gif\" width=\"1\" height=\"21\" alt=\"\" /></td><td style=\"border-top: 1px solid #D6E5F5; color:#1E3287\"><nobr>&nbsp;" . $string['qno'] . "&nbsp;</nobr></td><td style=\"border-top: 1px solid #D6E5F5\"><img src=\"../artwork/vertical_spacer.gif\" width=\"1\" height=\"21\" alt=\"\" /></td><td style=\"border-top: 1px solid #D6E5F5; color:#1E3287; text-align:center\">" . $string['objective'] . "</td></tr>";
  foreach ($objectives as $id => $obj_data) {
    $session_string = '';
    if ($obj_data['ratio'] >= 0.8) {
     $img_src = '../artwork/ok_comment.png';
    } elseif ($obj_data['ratio'] >= 0.5) {
     $img_src = '../artwork/minor_comment.png';
    } else {
     $img_src = '../artwork/major_comment.png';
    }

    if ($obj_data['mark_sum'] == '') $obj_data['mark_sum'] = 0;

    //cohort performance comparison
    if ($objectives[$id]['chort_totalpos_sum'] == 0) {
      $comparison = 0;
    } else {
      $comparison = round($objectives[$id]['mark_sum'] - ( $objectives[$id]['totalpos_sum'] * ($objectives[$id]['chort_mark_sum'] / $objectives[$id]['chort_totalpos_sum'])), 1);
    }
    if ($comparison == 0) {
      $comparison = '0';
    } elseif ($comparison > 0) {
      $comparison = '+' . $comparison;
    } else {
      $comparison = $comparison;
    }
    /*
    if ($obj_data['session']['source_url'] != '') {
      $session_string = "&nbsp;&nbsp;<a target=\"_blank\" href=\"" . $obj_data['session']['source_url'] . "\"><img src=\"../artwork/small_link.png\" width=\"12\" height=\"12\" /></a>&nbsp;<a target=\"_blank\" href=\"" . $obj_data['session']['source_url'] . "\">" . $obj_data['session']['sessiontitle'] . "</a>";
    }
    */

    echo "<tr><td class=\"symbol\"><img src=\"$img_src\" width=\"16\" height=\"16\" /></td><td></td><td class=\"r\">" . $obj_data['mark_sum'] . "</td><td>&nbsp;" . $string['outof'] . "&nbsp;</td><td>" . $obj_data['totalpos_sum'] . "</td><td></td><td class=\"r\">$comparison</td><td></td><td class=\"c\">" . $obj_data['questions'] . "</td><td></td><td>" . $obj_data['content'] . " $session_string</td></tr>\n";
  }
  echo "</table>\n";

  echo "<h1>" . $string['summaryinformation'] . "</h1>";
  echo "<table style=\"font-size:100%; margin-left:4px\">\n";
  echo "<tr><td>" . $string['papertitle'] . "</td><td>$paper_title</td></tr>\n";
  echo "<tr><td>" . $string['startedat'] . "</td><td>$started</td></tr>\n";

  //display student marks
  if ($paper_type < '3') {
    echo "<tr><td>" . $string['examlength'] . "</td><td>" . formatsec($exam_duration * 60) . "</td></tr>\n";
    echo "<tr><td>" . $string['timespent'] . "</td><td>" . formatsec($time_spent) . "</td></tr>\n";
  }
  echo "</table>\n</div>\n";

  $mysqli->close();
?>
</body>
</html>
