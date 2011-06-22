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
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

  require '../include/staff_student_auth.inc';
  require '../include/demo_replace.inc';
  require '../include/mapping.inc';
  require '../include/errors.inc';
  require '../include/feedback.inc';
  
  if (strpos($userroles,'Demo') !== false) {
    $demo = true;
  } else {
    $demo = false;
  }
  
  $paperID = $_GET['paperID'];
  $showReflection = true;
  if ((strpos($userroles,'Staff') !== false or strpos($userroles,'SysAdmin') !== false) AND $_GET['userID'] != '') {
    $userID = $_GET['userID'];
    $showReflection = false;
  }

  //check the feedback has been released !!!
  if (strpos($userroles,'Student') !== false) {
    $result = $mysqli->prepare("SELECT date FROM feedback_release WHERE paper_id=? AND date < NOW()");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($date);
    $result->fetch();
    $result->close();
    if ($date == '') {
      header("HTTP/1.0 404 Not Found");
      exit;
    }
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
  $result->bind_param('ii', $paperID, $userID);
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
  $result->bind_param('i', $userID);
  $result->execute();
  $result->bind_result($tmp_username, $title, $initials, $surname);
  $result->fetch();
  $result->close();
  $student_name = $title . ' ' . demo_replace($initials,$demo) . ' ' . demo_replace($surname,$demo);

  /*$distribution = array();
  for ($i=1; $i<=100; $i++) $distribution[$i] = 0;

  if($paper_type == '4') {
    $result = $mysqli->prepare("SELECT username, SUM(rating) AS mark, log$paper_type.userID, DATE_FORMAT(started,'%d/%m/%Y %H:%i') AS started FROM log$paper_type, users WHERE log$paper_type.userID=users.id AND roles='Student' AND q_paper=? AND started>='" . $exam_day . "000000' AND started<'" . $exam_day . "235959' GROUP BY log$paper_type.userID");
  } else {
    $result = $mysqli->prepare("SELECT username, SUM(mark) AS mark, log$paper_type.userID, DATE_FORMAT(started,'%d/%m/%Y %H:%i') AS started FROM log$paper_type, users WHERE log$paper_type.userID=users.id AND roles='Student' AND q_paper=? AND started>='" . $exam_day . "000000' AND started<'" . $exam_day . "235959' GROUP BY log$paper_type.userID");
  }
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($tmp_username, $mark, $tmp_userID, $started);
  while ($row = $result->fetch()) {
    if ($random_mark > 0 and $marking == 1) {
      $temp_location = round((($mark-$random_mark)/($total_mark-$random_mark))*100);
      if ($tmp_userID == $username) {
        $plotuser = round((($mark-$random_mark)/($total_mark-$random_mark))*100);
      }
    } else {
      $temp_location = round(($mark/$total_mark)*100);
      if ($tmp_userID == $username) {
        $plotuser = round(($mark/$total_mark)*100);
      }
    }
    if ($temp_location >= 0) {
      $distribution[$temp_location]++;
    }
  }
  $result->close();*/

 ?>
  <html>
  <head>
  <title>Exam Feedback</title>
  <style style="text/css">
    body {font-family:Arial,sans-serif; font-size:90%; color:black; background-color:white; margin:10px; background-image:url(../artwork/grey_bar.png); background-repeat:repeat-x}
    td {font-size:100%}
    img {border: none;}
    .q_no {text-align:right; vertical-align:top; cursor:pointer}
    .divider {font-family:Arial,sans-serif; font-size:90%; font-weight:bold}
    .mapping {font-size:90%;color:#FF6300;font-weight:normal}
    a {text-decoration:none}
    li {list-style:none; padding-bottom:5px}
    p {padding:5px}
    h1 {font-size:150%; font-weight:bold}
    .r {text-align:right}
    .c {text-align:center}
  </style>
  </head>
  <body>
    <table style="font-size:100%; border: 1px solid #C0C0C0; float:right; font-size:90%; background-color:#FFFFE1; filter: progid:DXImageTransform.Microsoft.Shadow(direction=120,color=gray,strength=4)">
    <tr><td colspan="2" style="padding-left:10px; padding-right:10px"><strong>Key</strong></td></tr>
    <tr><td style="padding-left:10px"><img src="../artwork/ok_comment.png" width="16" height="16" alt="Completely/Mostly acquired" /></td><td style="padding-right:10px">Acquisition of 80-100% of specific objective</td></tr>
    <tr><td style="padding-left:10px"><img src="../artwork/minor_comment.png" width="16" height="16" alt="Partically acquired" /></td><td style="padding-right:10px">Acquisition of 50-79% of specific objective</td></tr>
    <tr><td style="padding-left:10px"><img src="../artwork/major_comment.png" width="16" height="16" alt="Mostly not acquired" /></td><td style="padding-right:10px">Acquisition of 0-49% of specific objective</td></tr>
    <tr><td style="padding-left:10px"><img src="../artwork/small_link.png" width="12" height="12" alt="Shortcut" /></td><td style="padding-right:10px"><a href="" onclick="return false;">hyperlink</a> - jump to section in the NLE for further details</td></tr>
    <tr><td style="padding-left:10px" colspan="2"><strong>Relative</strong> - number of marks above '+' or below '-' relative to the mean of the cohort</td></tr>
    <tr><td style="padding-left:10px" colspan="2"><strong>Q no</strong> - number of questions mapped to objective</td></tr>
    </table>
  <?php
  echo "<div style=\"font-size:170%; font-weight:bold\">$paper_title</div>\n";
  echo "<div><strong>$student_name Feedback</strong></div>\n";
  
  //get Cohort Data
  $chort_question_data = getCohortData($mysqli,$moduleID,$start_date,$end_date,'%','%','%',$paperID,$paper_type,'');
  
  //get users log data excluding exclued questions
  $qid_list = '';
  $question_data = Array();
  
  $startedSQL = '';
  if(isset($_GET['started'])) {
    $startedSQL = ' AND started = "' . $_GET['started'] . '"';;
  }
  
  if($paper_type == '4') {
    $result = $mysqli->prepare("SELECT log4.q_id, rating as mark,score_method FROM log$paper_type LEFT JOIN questions ON log4.q_id = questions.q_id WHERE  log4.q_id NOT IN (SELECT q_id FROM question_exclude WHERE q_paper=?) AND userID=? AND q_paper=? $startedSQL ORDER BY  log4.q_id, started");
  } else {
    $result = $mysqli->prepare("SELECT q_id, mark, totalpos FROM log$paper_type WHERE q_id NOT IN (SELECT q_id FROM question_exclude WHERE q_paper=?) AND userID=? AND q_paper=? $startedSQL ORDER BY q_id, started");
  }

  $result->bind_param('iii', $paperID, $userID, $paperID);
  $result->execute();
  $result->bind_result($q_id, $mark, $totalpos);
  $total_student_mark = 0;
  while ($row = $result->fetch()) {
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
    foreach($objByModule as $module => $mappings) {
      foreach($mappings as $id => $mappingData) {
        if( $mappingData['session']['class_code'] != '') {
          $sessiontitle = $mappingData['session']['class_code'];
        } else {
          $sessiontitle = $mappingData['session']['title'];
        }
        $objectives[$id] = $mappingData;
        foreach($mappingData['mapped'] as $q_id) {
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
    $sortby = 'ratio';
    $ordering = 'desc';
    $objectives = array_csort($objectives,$sortby,$ordering);
  }

  //Display the feedback
  ?>
  <br />
  <br />
  <h1>Learning Objectives</h1>
  <p>Below is a list of all the unique learning objectives tested by this paper. Because multiple questions may test the same objective it is possible to have partial acquisition of an objective. Use the results below to concentrate on red <img src="../artwork/major_comment.png" width="16" height="16" alt="Mostly not acquired" /> and amber <img src="../artwork/minor_comment.png" width="16" height="16" alt="Mostly not acquired" /> objectives you have not fully mastered.</p>
  <?php
  if (count($objectives) == 0) {
    echo "<p style=\"background-color:#FFC0C0; border:1px solid #C00000; padding:10px; color:#800000\">This paper has not been mapped to any learning objectives.</p>\n</body>\n</html>\n";
    exit;
  }
  
  echo "<blockquote><table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" style=\"font-size:100%\">\n";
  echo "<tr><th style=\"border-bottom: 1px solid #C0C0C0\">&nbsp;</th><th colspan=\"3\" style=\"border-bottom: 1px solid #C0C0C0\">Your Mark</th><th style=\"border-bottom: 1px solid #C0C0C0\">Relative</th><th style=\"border-bottom: 1px solid #C0C0C0\">Q&nbsp;no</th><th style=\"border-bottom: 1px solid #C0C0C0\">Objective</th></tr>";
  foreach($objectives as $id => $obj_data) {
    $session_string = '';
    if($obj_data['ratio'] > 0.799) {
     $img_src = '../artwork/ok_comment.png';
    } else if ($obj_data['ratio'] > 0.499) {
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
    if($obj_data['session']['source_url'] != '') {
      $session_string = "&nbsp;&nbsp;<a target=\"_blank\" href=\"" . $obj_data['session']['source_url'] . "\"><img src=\"../artwork/small_link.png\" width=\"12\" height=\"12\" /></a>&nbsp;<a target=\"_blank\" href=\"" . $obj_data['session']['source_url'] . "\">" . $obj_data['session']['sessiontitle'] . "</a>";
    }

    echo "<tr><td><img src=\"$img_src\" width=\"16\" height=\"16\" />&nbsp;</td><td class=\"r\">" . $obj_data['mark_sum'] . '</td><td>out&nbsp;of</td><td>' . $obj_data['totalpos_sum'] . "</td><td class=\"r\">$comparison</td><td class=\"c\">" . $obj_data['questions'] . "</td><td>" . $obj_data['content'] . " $session_string</td></tr>\n";
  }
  echo "</table></blockquote>\n";

  echo "<h1>Summary Information</h1>";
  echo "<table style=\"font-size:100%\">\n";
  echo "<tr><td>Paper Title</td><td>$paper_title</td></tr>\n";
  echo "<tr><td>Started at</td><td>$started</td></tr>\n";
  //display student marks
  if($paperID == 2501) {
    if ($marking == 1) {
      $adjusted = round((($total_student_mark-$random_mark)/($total_mark-$random_mark))*100);
      echo "<tr><td>Your Mark</td><td>$total_student_mark out of $total_mark (adjusted $adjusted" . "%)</td></tr>\n";  
      echo "<tr><td>Random Mark</td><td>" . round($random_mark) . "</td></tr>\n";
    } else {
      $per = round((($total_student_mark)/($total_mark))*100);
      echo "<tr><td>Your Mark</td><td>$total_student_mark out of $total_mark ($per" . "%)</td></tr>\n";  
   
    }
  }
  if ($paper_type < '3') {
    echo "<tr><td>Exam Length</td><td>" . formatsec($exam_duration*60) . "</td></tr>\n";
    echo "<tr><td>Time spent</td><td>" . formatsec($time_spent) . "</td></tr>\n";
  }
  echo "</table>\n";
  
  
  $result = $mysqli->prepare("SELECT vle_api FROM modules WHERE moduleid = ?");
  $result->bind_param('s',$moduleID);
  $result->execute();
  $result->bind_result($vle_api);
  $result->fetch();  
  $result->close();
  if($vle_api == 'NLE') {
    //display reflection
    $insighturl = "http://www.nle.nottingham.ac.uk/insight/manage_reflections.php?related_type=examfeedback&related_to=$paperID&moduleID=$moduleID&session=$session";

    if ($showReflection == true) {
      echo "<iframe style=\"border: 0px solid #ffffff;\" src =\"$insighturl\" frameborder=\"0\" width=\"100%\" height=\"500\">\n";
      echo "  <p>Your browser does not support iframes.</p>\n";
      echo "</iframe>\n";
    }

    // Insert into Log (on the NLE)
    $ip = gethostbyname('www.nle.nottingham.ac.uk');
    $mysqliNLE = new mysqli($ip, 'notts_nle', '', 'mediguides');

    if ($result = $mysqliNLE->prepare("INSERT INTO log VALUES (NULL,'$tmp_username','Assessment Feedback',?,NOW(),'" . $_SERVER['REMOTE_ADDR'] . "')")) {
      $result->bind_param('s', $paper_title);
      $result->execute();
      $result->close();
    } else {
      display_error("NLE Log Insert Error",$mysqliNLE->error);
    }
    $mysqliNLE->close();
    $mysqli->close();
  }
  
  ?>
</body>
</html>
