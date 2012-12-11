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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once '../classes/paperutils.class.php';

function getCohortData($mysqli, $moduleIDs, $startdate, $enddate, $repcourse, $repmodule, $repyear, $paperID, $paper_type, $direction, &$student_no = 0, &$user_total = 0, $percent = 100) {
  global $qid_list;
  
  $users_on_modules = '';

  // Get all the users on the module(s) the paper is on.
  if (is_array($moduleIDs)) {
    $moduleIDs_in = "'" . implode("','",array_keys($moduleIDs)) . "'";
    $mod_query = $mysqli->prepare("SELECT userID, moduleid FROM modules_student,modules WHERE modules.id = modules_student.idMod and idMod IN ($moduleIDs_in)");
    $mod_query->execute();
    $mod_query->bind_result($tmp_userID, $tmp_moduleid);
    $mod_query->store_result();
    while ($mod_query->fetch()) {
	  if (isset($repmodule) and $repmodule != '' and $tmp_moduleid != $repmodule) {
      continue; //this user is not on the module set in repmodule so dont put them in the array
	  }
	  if ($users_on_modules == '') {
        $users_on_modules = "'" . $tmp_userID . "'";
      } else {
        $users_on_modules .= ",'" . $tmp_userID . "'";
      }
    }
    $mod_query->close();
  }
  $student_modules_sql = '';
  if ($users_on_modules != '' and isset($repmodule) and $repmodule != '') {
    $student_modules_sql = " AND log$paper_type.userID IN ($users_on_modules)";
  } 

  // Get order of the class.
  if ($paper_type == '0') {
    if ($users_on_modules != '') {
      $student_modules_sql1 = " AND log0.userID IN ($users_on_modules)";
      $student_modules_sql2 = " AND log1.userID IN ($users_on_modules)";
    } else {
      $student_modules_sql1 = '';
      $student_modules_sql2 = '';
    }
    $marks_query = "(SELECT log0.userID, sum(mark) AS total_mark FROM (log0, log_metadata) WHERE log0.started=log_metadata.started AND log0.q_paper=log_metadata.paperID AND log0.userID=log_metadata.userID AND q_paper=$paperID AND log_metadata.started>='$startdate' AND log_metadata.started<='$enddate' AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE '%staff%' AND student_grade NOT LIKE '%nhs%' AND year LIKE '" . $repyear . "'$student_modules_sql1 GROUP BY log0.userID, q_paper, log0.started) UNION ALL (SELECT log1.userID, sum(mark) AS total_mark FROM (log1, log_metadata) WHERE log1.started=log_metadata.started AND log1.q_paper=log_metadata.paperID AND log1.userID=log_metadata.userID AND q_paper=" . $paperID . " AND log_metadata.started>='$startdate' AND log_metadata.started<='$enddate' AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE '%staff%' AND student_grade NOT LIKE '%nhs%' AND year LIKE '" . $repyear . "'$student_modules_sql2 GROUP BY log1.userID, q_paper, log1.started) ORDER BY total_mark " . $direction;
  } elseif ($paper_type == '4') {
    $marks_query = "SELECT log$paper_type.userID, sum(rating) AS total_mark FROM (log$paper_type, log_metadata) WHERE log$paper_type.started=log_metadata.started AND log$paper_type.q_paper=log_metadata.paperID AND log$paper_type.userID=log_metadata.userID AND q_paper=$paperID AND DATE_ADD(log_metadata.started, INTERVAL 2 MINUTE)>='$startdate' AND log_metadata.started<='$enddate' AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE '%staff%' AND student_grade NOT LIKE '%nhs%' AND student_grade LIKE '" . $repcourse . "' AND year LIKE '" . $repyear . "'$student_modules_sql GROUP BY log$paper_type.userID, q_paper, log$paper_type.started ORDER BY total_mark " . $direction;
  } else {
    $marks_query = "SELECT log$paper_type.userID, sum(mark) AS total_mark FROM (log$paper_type, log_metadata) WHERE log$paper_type.started=log_metadata.started AND log$paper_type.q_paper=log_metadata.paperID AND log$paper_type.userID=log_metadata.userID AND q_paper=$paperID AND DATE_ADD(log_metadata.started, INTERVAL 2 MINUTE)>='$startdate' AND log_metadata.started<='$enddate' AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE '%staff%' AND student_grade NOT LIKE '%nhs%' AND student_grade LIKE '" . $repcourse . "' AND year LIKE '" . $repyear . "'$student_modules_sql GROUP BY log$paper_type.userID, q_paper, log$paper_type.started ORDER BY total_mark " . $direction;
  }

  $student_no = 0;
  $student_list = '';
  
  $result = $mysqli->prepare($marks_query);
  $result->execute();
  $result->store_result();
  $result->bind_result($tmp_userID, $total_mark);
  $user_total = $result->num_rows;
  
  //only take the required number of users if $percent is not 100%
  $user_limit = ($percent != 100) ? floor($user_total * ($percent/100)) : $user_total;
  
  while ($result->fetch() and $student_no <= $user_limit) {
    if ($student_list == '') {
      $student_list = "'$tmp_userID'";
    } else {
      $student_list .= ",'$tmp_userID'";
    }
    $student_no++;
  }
  $result->close();
  
  //get the excluded questions
  $excluded = array();
  $result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper=?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $parts);
  if ($result->num_rows > 0) {
    while ($result->fetch()) {
      $excluded[] = $q_id;
    }
    $excluded = 'AND q_id NOT IN (' . implode(',',$excluded) . ') ';
  } else {
    $excluded = '';
  }
  $result->close();
  
  //get users log data
  $qid_list = '';
  $question_data = array();
  if ($student_list != '') {
    $result = $mysqli->prepare("SELECT q_id, SUM(mark) AS mark, SUM(totalpos) AS totalpos FROM log$paper_type WHERE userID IN ($student_list) $excluded AND q_paper=$paperID GROUP BY q_id ORDER BY q_id");
    $result->execute();
    $result->bind_result($q_id, $mark, $totalpos);
    while ($result->fetch()) {
      $question_data[$q_id]['totalpos'] = $totalpos;
      $question_data[$q_id]['mark'] = $mark;    
      $qid_list .= $q_id . ',';
    }
    $result->close();
  }
  return $question_data;
}

function getPaperProperties($db,$paperID) {
  global $paper_title, $paper_type, $moduleID, $session, $pass_mark, $random_mark, $total_mark, $marking, $exam_duration, $start_date, $end_date;
  $result = $db->prepare("SELECT paper_title, paper_type, calendar_year, pass_mark, random_mark, total_mark, marking, exam_duration, start_date, end_date FROM properties WHERE property_id=?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($paper_title, $paper_type, $session, $pass_mark, $random_mark, $total_mark, $marking, $exam_duration, $start_date, $end_date);
  $result->fetch();
  $result->close();

  $moduleID = Paper_utils::get_modules($paperID, $db);
}

function formatsec($seconds) {
  $diff_hour = ($seconds / 60) / 60;
  $tmp_position = strpos($diff_hour, '.');
  if ($tmp_position > 0) $diff_hour = substr($diff_hour, 0, $tmp_position);
  if ($diff_hour > 0) $seconds -= ($diff_hour * 60) * 60;
  $diff_min = $seconds / 60;
  $tmp_position = strpos($diff_min, '.');
  if ($tmp_position > 0) $diff_min = substr($diff_min, 0, $tmp_position);
  if ($diff_min > 0) $seconds -= $diff_min * 60;
  $diff_sec = $seconds;
  $timestring = '';
  if ($diff_hour < 10) $timestring = '0';
  $timestring .= "$diff_hour:";
  if ($diff_min < 10) $timestring .= '0';
  $timestring .= "$diff_min:";
  if ($diff_sec < 10) $timestring .= '0';
  $timestring .= $diff_sec;
  return $timestring;
}

?>