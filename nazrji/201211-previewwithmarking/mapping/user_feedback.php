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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/staff_student_auth.inc';
  require '../include/demo_replace.inc';
  require '../include/mapping.inc';
  require '../include/errors.inc';
  require '../include/feedback.inc';
  require_once '../include/sort.inc';
  
  check_var('id', 'GET', true, false);
  
  if ($userObject->has_role('Demo')) {
    $demo = true;
  } else {
    $demo = false;
  }
  
  $showReflection = true;
  if ($userObject->has_role(array('Staff','SysAdmin'))) {
    if (isset($_GET['userID']) and $_GET['userID'] != '') {
      $userID = $_GET['userID'];  //TODO Why is it doing this?
    } else {
      display_error($string['idmissing'], $string['idmissing_msg'], false, true, false);
    }
  }

  //check the feedback has been released !!!
  if ($userObject->has_role('Student')=) {
    $result = $mysqli->prepare("SELECT property_id, date FROM feedback_release, properties WHERE properties.property_id=feedback_release.paper_id AND crypt_name=? AND date < NOW()");
    $result->bind_param('s', $_GET['id']);
    $result->execute();
    $result->bind_result($paperID, $date);
    $result->fetch();
    $result->close();
    if ($date == '') {
      header("HTTP/1.0 404 Not Found");
      exit;
    }
  } else {
    $result = $mysqli->prepare("SELECT property_id FROM properties WHERE crypt_name=?");
    $result->bind_param('s', $_GET['id']);
    $result->execute();
    $result->bind_result($paperID);
    $result->fetch();
    $result->close();
  }

  if (!isset($_GET['ordering'])) {
    $ordering = 'screen';
    $direction = 'asc';
  }

  // Get some paper properties
  $paper_title = $paper_type = $moduleID = $session = $pass_mark = $random_mark = $total_mark = $marking = $exam_duration = $start_date = $end_date = '';
  getPaperProperties($mysqli, $paperID);
  //check the paper is valid
  if ($paper_title == '') {
    header("HTTP/1.0 404 Not Found");
    exit;
  }
  
  //check the user sat the paper!
  if ($paper_type == '5') {
    $result = $mysqli->prepare("SELECT DATE_FORMAT(started,'%H:%i:%s') AS started, NULL AS updated FROM log5 WHERE q_paper=? AND userID=? LIMIT 1");
  } elseif ($paper_type == '4') {
    $result = $mysqli->prepare("SELECT DATE_FORMAT(started,'%H:%i:%s') AS started, NULL AS updated FROM log4 WHERE q_paper=? AND userID=? LIMIT 1");
  } else {
    $result = $mysqli->prepare("SELECT DATE_FORMAT(started,'%H:%i:%s') AS started, DATE_FORMAT(updated,'%H:%i:%s') AS updated FROM log$paper_type WHERE q_paper=? AND userID=? ORDER BY screen DESC LIMIT 1");
  }
  $result->bind_param('ii', $paperID, $userObject->get_user_ID());
  $result->execute();
  $result->bind_result($started, $updated);
  $result->store_result();
  $result->fetch();
  if ($result->num_rows == 0) {
    header("HTTP/1.0 404 Not Found");
    exit;
  }
  $result->close();

  $start_seconds = (substr($started,0,2) * 60 * 60) + (substr($started,3,2) * 60) + substr($started,6,2);
  $updated = (substr($updated,0,2) * 60 * 60) + (substr($updated,3,2) * 60) + substr($updated,6,2);
  $time_spent = $updated - $start_seconds;

  $result = $mysqli->prepare("SELECT username, title, initials, surname FROM users WHERE id=?");
  $result->bind_param('i', $userObject->get_user_ID());
  $result->execute();
  $result->bind_result($tmp_username, $title, $initials, $surname);
  $result->fetch();
  $result->close();
  $student_name = $title . ' ' . demo_replace($initials,$demo) . ' ' . demo_replace($surname,$demo);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
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
    <table style="position:relative; border: 1px solid #808080; box-shadow: 2px 2px 2px #C0C0C0; z-index:10; float:right; top:10px; right:10px; font-size:90%; background-color:#FFFFEE; margin-bottom:5px">
    <tr><td colspan="2" style="padding-left:10px; padding-right:10px"><strong><?php echo $string['key']; ?></strong></td></tr>
    <tr><td style="padding-left:10px"><img src="../artwork/ok_comment.png" width="16" height="16" alt="Completely/Mostly acquired" /></td><td style="padding-right:10px"><?php echo $string['greenicon']; ?></td></tr>
    <tr><td style="padding-left:10px"><img src="../artwork/minor_comment.png" width="16" height="16" alt="Partically acquired" /></td><td style="padding-right:10px"><?php echo $string['ambericon']; ?></td></tr>
    <tr><td style="padding-left:10px"><img src="../artwork/major_comment.png" width="16" height="16" alt="Mostly not acquired" /></td><td style="padding-right:10px"><?php echo $string['redicon']; ?></td></tr>
    <tr><td style="padding-left:10px" colspan="2"><?php echo $string['relativekey']; ?></td></tr>
    <tr><td style="padding-left:10px" colspan="2"><?php echo $string['question']; ?></td></tr>
    </table>
  <?php
  echo "<table class=\"header\" style=\"position:absolute; top:0px; left:0px; font-size:90%\">\n";
  echo "<tr><th style=\"padding:10px\"><div style=\"font-size:220%; font-weight:bold\">$paper_title</div>\n";
  echo "<div><strong>$student_name " . $string['feedback'] . "</strong></div></th></tr>\n";
  echo "<tr><th class=\"bevel\"></th></tr>\n";
  
  //get Cohort Data
  $chort_question_data = getCohortData($mysqli, $moduleID, $start_date, $end_date, '%', '%', '%', $paperID, $paper_type, '');

  //get users log data excluding exclued questions
  $qid_list = '';
  $question_data = array();
  
  $startedSQL = '';
  if (isset($_GET['started'])) {
    $startedSQL = ' AND started = "' . $_GET['started'] . '"';;
  }
  
  if($paper_type == '4') {
    $result = $mysqli->prepare("SELECT log4.q_id, rating as mark,score_method FROM log$paper_type LEFT JOIN questions ON log4.q_id = questions.q_id WHERE  log4.q_id NOT IN (SELECT q_id FROM question_exclude WHERE q_paper=?) AND userID=? AND q_paper=? $startedSQL ORDER BY  log4.q_id, started");
  } else {
    $result = $mysqli->prepare("SELECT q_id, mark, totalpos FROM log$paper_type WHERE q_id NOT IN (SELECT q_id FROM question_exclude WHERE q_paper=?) AND userID=? AND q_paper=? $startedSQL ORDER BY q_id, started");
  }

  $result->bind_param('iii', $paperID, $userObject->get_user_ID(), $paperID);
  $result->execute();
  $result->bind_result($q_id, $mark, $totalpos);
  $total_student_mark = 0;
  while ($result->fetch()) {
    if(is_string($totalpos)) {
      $question_data[$q_id]['totalpos'] = count(explode('|',$totalpos)) - 2;
    } else {
      $question_data[$q_id]['totalpos'] = $totalpos;
    }
    $total_student_mark += $mark;
    $question_data[$q_id]['mark'] = $mark;
    $qid_list .= $q_id . ',';
  }
  $result->close();
  
  $objectives = array();
  $qid_list = substr($qid_list,0,-1);
  $objByModule = getObjectivesByMapping($moduleID, $session, $paperID, $qid_list, $mysqli);
  unset($objByModule['none_of_the_above']);
  if (count($objByModule) > 0) {
    foreach ($objByModule as $module => $mappings) {
      foreach ($mappings as $id => $mappingData) {
        if( $mappingData['session']['class_code'] != '') {
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

        }
        $objectives[$id]['ratio'] = $objectives[$id]['mark_sum']/$objectives[$id]['totalpos_sum'];
        $objectives[$id]['chort_totalpos_sum'] = $chort_question_data[$q_id]['totalpos'];
        $objectives[$id]['chort_mark_sum'] = $chort_question_data[$q_id]['mark'];
        $objectives[$id]['chort_ratio'] = $chort_question_data[$q_id]['mark'] / $chort_question_data[$q_id]['totalpos'];
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
  <br />
  <br />
  <br />
  <br />
  <h1><?php echo $string['learningobjectives']; ?></h1>
  <p><?php echo $string['explanation']; ?></p>
  <?php
  
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%; line-height:150%\">\n";
  echo "<tr><td style=\"border-top: 1px solid #D6E5F5\">&nbsp;</td><td style=\"border-top: 1px solid #D6E5F5\"><img src=\"../artwork/vertical_spacer.gif\" width=\"1\" height=\"21\" alt=\"\" /></td><td colspan=\"3\" style=\"border-top: 1px solid #D6E5F5; color:#1E3287\">&nbsp;<nobr>" . $string['yourmark'] . "&nbsp;</nobr></td><td style=\"border-top: 1px solid #D6E5F5\"><img src=\"../artwork/vertical_spacer.gif\" width=\"1\" height=\"21\" alt=\"\" /></td><td style=\"border-top: 1px solid #D6E5F5; color:#1E3287\">&nbsp;" . $string['relative'] . "&nbsp;</td><td style=\"border-top: 1px solid #D6E5F5\"><img src=\"../artwork/vertical_spacer.gif\" width=\"1\" height=\"21\" alt=\"\" /></td><td style=\"border-top: 1px solid #D6E5F5; color:#1E3287\"><nobr>&nbsp;" . $string['qno'] . "&nbsp;</nobr></td><td style=\"border-top: 1px solid #D6E5F5\"><img src=\"../artwork/vertical_spacer.gif\" width=\"1\" height=\"21\" alt=\"\" /></td><td style=\"border-top: 1px solid #D6E5F5; color:#1E3287; text-align:center\">" . $string['objective'] . "</td></tr>";
  foreach($objectives as $id => $obj_data) {
    $session_string = '';
    if ($obj_data['ratio'] > 0.799) {
     $img_src = '../artwork/ok_comment.png';
    } elseif ($obj_data['ratio'] > 0.499) {
     $img_src = '../artwork/minor_comment.png';
    } else {
     $img_src = '../artwork/major_comment.png';
    }
    
    if ($obj_data['mark_sum'] == '') $obj_data['mark_sum'] = 0;
    
    //cohort performance comparison
    $comparison = round($objectives[$id]['mark_sum'] - ( $objectives[$id]['totalpos_sum'] * ($objectives[$id]['chort_mark_sum']/$objectives[$id]['chort_totalpos_sum'])),1);
    if($comparison == 0) {
      $comparison = '0';
    } else if($comparison > 0) {
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
    echo "<tr><td>" . $string['examlength'] . "</td><td>" . formatsec($exam_duration*60) . "</td></tr>\n";
    echo "<tr><td>" . $string['timespent'] . "</td><td>" . formatsec($time_spent) . "</td></tr>\n";
  }
  echo "</table>\n";
  
  $mysqli->close();
?>
</body>
</html>
