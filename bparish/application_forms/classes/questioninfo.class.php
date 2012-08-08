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
* Utility class for question information related functions
* 
* @author Anthony Brown and Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/


Class question_info {
  
  /**
   * Get the question information
   * @param integer $q_id
   * @param object $db
   * @return formated HTML for display of question information
   */
  static function full_question_information($q_id, $db) {
    global $cfg_short_date, $cfg_long_date_time, $userroles, $string;
    
    $html = '';
     
    $question_data = $db->prepare("SELECT email, title, surname, initials, DATE_FORMAT(creation_date,\"%d/%m/%Y %H:%i\") AS creation_date, DATE_FORMAT(last_edited,\"%d/%m/%Y %H:%i\") AS last_edited, DATE_FORMAT(locked,\"$cfg_long_date_time\") AS locked, q_group, q_type, std, status FROM (users, questions) WHERE users.id=questions.ownerID AND q_id=? LIMIT 1");
    $question_data->bind_param('i', $_GET['q_id']);
    $question_data->execute();
    $question_data->bind_result($email, $title, $surname, $initials, $creation_date, $last_edited, $locked, $q_group, $q_type, $std, $status);
    $question_data->store_result();
    $question_data->fetch();
    $question_data->close(); 
    
    if ($q_group == '') $q_group = '<span style="color:#808080">' . $string['na'] . '</span>';
    if ($locked == '') $locked = '<span style="color:#808080">' . $string['na'] . '</span>';

    if (strpos($userroles,'Demo') !== false) {
      $owner = 'Dr J, Bloggs (<a href="">joe.bloggs@uni.ac.uk</a>)';
    } else {
      $owner = "$title $initials $surname (<a href=\"mailto:$email\">$email</a>)";
    }
    echo "<tr><td style=\"width:70px\">" . $string['author'] . "</td><td>$owner</td></tr>\n";
    echo "<tr><td>" . $string['created'] . "</td><td>$creation_date</td></tr>\n";
    echo "<tr><td>" . $string['modified'] . "</td><td>$last_edited</td></tr>\n";
    echo "<tr><td>" . $string['locked'] . "</td><td>$locked</td></tr>\n";
    echo "<tr><td>" . $string['teams'] . "</td><td>$q_group</td></tr>\n";
    echo "<tr><td>" . $string['copies'] . "</td><td></td></tr>\n";
    echo "</table>\n";
    
    echo "<div style=\"margin:5px; display:block; height:95px; overflow-y:scroll; border:1px solid #95AEC8; font-size:100%; background-color:white\">\n<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" style=\"width:100%\">";
    echo "<tr><th>" . $string['type'] . "</th><th>" . $string['papername'] . "</th><th>" . $string['questionno'] . "</th></tr>\n";
    
    $data = question_info::check_copies($q_id, $db);  
    $rows = count($data);
    for ($i=0; $i<$rows; $i++) {
      if (isset($data[$i]['paperID'])) {
        echo "<tr><td>" . $string['copyof'] . "</td><td><a href=\"\" onclick=\"loadPaper('" . $data[$i]['paperID'] . "')\">" . $data[$i]['paper_title'] . "</a></td><td>" . $data[$i]['question_no'] . "</td></tr>\n";
      } else {
        echo "<tr><td>" . $string['copyof'] . "</td><td colspan=\"2\">Question ID #" . $data[$i]['question_id'] . "</td></tr>\n";
      }
    }
    
    unset($data);

    $data = question_info::check_copied($q_id, $db);
    $rows = count($data);
    for ($i=0; $i<$rows; $i++) {
      if (isset($data[$i]['paperID'])) {
        echo "<tr><td>" . $string['sourcefor'] . "</td><td><a href=\"\" onclick=\"loadPaper('" . $data[$i]['paperID'] . "')\">" . $data[$i]['paper_title'] . "</a></td><td>" . $data[$i]['question_no'] . "</td></tr>\n";
      } else {
        echo "<tr><td>" . $string['sourcefor'] . "</td><td colspan=\"2\">Question ID #" . $data[$i]['question_id'] . "</td></tr>\n";
      }
    }
    
    echo "</table>\n</div>\n";
    
    echo "<table style=\"width:100%\">\n";
    echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    echo "<tr><td>" . $string['followingpapers'] . "</td><td style=\"text-align:right\"><input type=\"button\" name=\"longitudinal\" value=\"Longitudinal\" onclick=\"openLongitudinal($q_id);\" /></td></tr>\n";
    echo "</table>\n";
    
    echo "<div style=\"margin:5px; display:block; height:195px; overflow-y:scroll; border:1px solid #95AEC8; font-size:100%; background-color:white\">\n<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" style=\"width:100%\">";
    echo "<tr><th></th><th>" . $string['papername'] . "</th><th>" . $string['screenno'] . "</th><th>" . $string['examdate'] . "</th><th>" . $string['cohort'] . "</th><th></th><th>" . $string['p'] . "</th><th>" . $string['d'] . "</th></tr>\n";

    $performance_array = question_info::question_performance($q_id, $db);
    
    foreach ($performance_array as $paper => $performance) {
      echo "<tr><td><img src=\"../artwork/" . $performance['icon'] . "\" width=\"16\" height=\"16\" border=\"0\" alt=\"0\" /></td>";
      echo "<td><a href=\"\" onclick=\"loadPaper('$paper')\">" . $performance['title'] . "</a></td>";
      echo "<td class=\"num\">" . $performance['screen'] . "</td>";
      
      if (isset($performance['performance'][1]['taken'])) {
        echo "<td>" . $performance['performance'][1]['taken'] . "</td><td class=\"num\">" . $performance['performance'][1]['cohort'] . "</td><td style=\"text-align:right\">" . question_info::display_parts($performance['performance'], $q_type) . "</td><td class=\"num\">" . question_info::display_p($performance['performance'], $q_type) . "</td><td class=\"num\">" . question_info::display_d($performance['performance'], $q_type) . "</td>";
      } else {
        echo "<td></td><td></td><td></td><td></td><td></td>";
      }
      echo "</tr>\n";    
    }
 
    return $html;
  }

  /**
   * Form an array of question performance data.
   * @param integer $q_id
   * @param object $db
   * @return array of performance data
   */
  static function question_performance($q_id, $db) {
    global $cfg_short_date, $cfg_long_date_time, $userroles, $string;

    $icons = array('formative', 'progress', 'summative', 'survey', 'osce', 'offline', 'peer');
    $performance = array();
  
    //get performace data from all papers this question has appered on
    $result = $db->prepare("SELECT paperID, cohort_size, DATE_FORMAT(taken,\"$cfg_short_date\"), part_no, p, d FROM performance_main, performance_details WHERE performance_main.id=performance_details.perform_id AND q_id=?");
    $result->bind_param('i', $q_id);
    $result->execute();
    $result->bind_result($paperID, $cohort_size, $taken, $part_no, $p, $d);
    while ($result->fetch()) {
      $performance[$paperID]['performance'][$part_no] = array('cohort'=>$cohort_size, 'taken'=>$taken, 'p'=>$p, 'd'=>$d);
    }
    $result->close();

    $result = $db->prepare("SELECT property_id, paper_title, paper_type, screen, calendar_year FROM (papers, properties) WHERE properties.property_id=papers.paper AND question=? AND deleted IS NULL");
    $result->bind_param('i', $q_id);
    $result->execute();
    $result->bind_result($property_id, $paper_title, $paper_type, $screen, $calendar_year);
    $result->store_result();
    while ($result->fetch()) {
      $performance[$property_id]['title'] = $paper_title;
      $performance[$property_id]['icon'] = $icons[$paper_type] . '_16.gif';
      $performance[$property_id]['screen'] = $screen;
      $performance[$property_id]['calendar_year'] = $calendar_year;
    }
    $result->close();
  
    return $performance;
  }

  /**
   * Determine if a question type can have multiple parts.
   * @param text $type
   * @return true or false
   */
  static function multi_part_question($type) {
    if ($type == 'blank' or $type == 'dichotomous' or $type == 'extmatch' or $type == 'hotspot' or $type == 'labelling' or $type == 'matrix') {
      return true;
    } else {
      return false;
    }
  }
  
  /**
   * If question is multipart then Roman numerals will be returned.
   * @param array $perform_data
   * @param text $q_type
   * @return formatted HTML
   */
  static function display_parts($perform_data, $q_type) {
    $html = '';
    $numerals = array('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii');
    if (question_info::multi_part_question($q_type)) {
      for ($i=0; $i<count($perform_data); $i++) {
        $html .= $numerals[$i] . '.<br />';
      }
    }
    
    return $html;
  }
  
  /**
   * Format and output P values for a question.
   * @param array $perform_data
   * @param text $q_type
   * @return formatted value for P.
   */
  static function display_p($perform_data, $q_type) {
    $html = '';
    
    if (question_info::multi_part_question($q_type)) {
      foreach ($perform_data as $single_data) {
        $html .= question_info::p_warning(number_format($single_data['p']/100, 2)) . '<br />';
      }
    } else {
      $html = question_info::p_warning(number_format($perform_data[1]['p']/100, 2));
    }
    
    return $html;
  }
  
  /**
   * Check and add a warning to P values.
   * @param real $value
   * @return the original number or a warning if less than 0.2
   */
  static function p_warning($value) {
    if ($value < 0.2) {
      return '<span style="color:#C00000">' . $value . '</span>';
    } else {
      return $value;
    }
  }
    
  /**
   * Format and output D values for a question.
   * @param array $perform_data
   * @param text $q_type
   * @return formatted value for D.
   */
  static function display_d($perform_data, $q_type) {
    $html = '';
    
    if (question_info::multi_part_question($q_type)) {
      foreach ($perform_data as $single_data) {
        $html .= question_info::d_warning(number_format($single_data['d']/100, 2)) . '<br />';
      }
    } else {
      $html = question_info::d_warning(number_format($perform_data[1]['d']/100, 2));
    }
    
    return $html;
  }
    
  /**
   * Check and add a warning to D values.
   * @param real $value
   * @return the original number or a warning if less than 0.15
   */
  static function d_warning($value) {
    if ($value <= 0.15) {
      return '<span style="color:#C00000">' . $value . '</span>';
    } else {
      return $value;
    }
  }
    
  /**
   * Find the details a 'source' question where the current question was copied from.
   * @param integer $q_id
   * @param object $db
   * @return array with details of the source question.
   */
  static function check_copies($q_id, $db) {
    $row_number = 0;
    $data_no = 0;
    $data = array();
    
    // Get the ID of the original question.
    $copy_data = $db->prepare("SELECT old FROM track_changes WHERE type='Copied Question' AND typeID = ? LIMIT 1");
    $copy_data->bind_param('i', $q_id);
    $copy_data->execute();
    $copy_data->bind_result($copyID);
    $copy_data->store_result();
    $copy_data->fetch();
    $copy_data->close();
        
    if (isset($copyID)) {
      // Look up what paper it was used on.
      $copy_question_no = 0;
      $row_no = 1;
      $copy_data = $db->prepare("SELECT property_id, paper_title, question, q_type FROM (papers, properties, questions) WHERE properties.property_id=papers.paper AND papers.question=questions.q_id AND paper=(SELECT paper FROM papers WHERE question=? LIMIT 1) ORDER BY screen, display_pos");
      $copy_data->bind_param('i', $copyID);
      $copy_data->execute();
      $copy_data->bind_result($copy_paperID, $copy_paper_title, $copy_question, $copy_q_type);
      $copy_data->store_result();
      while ($copy_data->fetch()) {
        if ($copy_q_type != 'info') $row_number++;
        if ($copy_question == $copyID) $copy_question_no = $row_number;
      }
      $copy_data->close();
      if ($copy_question_no == 0) {
        $data[$data_no]['question_id'] = $copyID;
      } else {
        $data[$data_no]['paperID'] = (int)$copy_paperID;
        $data[$data_no]['paper_title'] = $copy_paper_title;
        $data[$data_no]['question_no'] = $copy_question_no;
        $data[$data_no]['question_id'] = (int)$copyID;
      }
      $data_no++;
    }
    
    return $data;
  }
  
  /**
   * Find the details copied questions which use the current question as a 'source'.
   * @param integer $q_id
   * @param object $db
   * @return array with details of the copied questions.
   */
  static function check_copied($q_id, $db) {
    $data_no = 0;
    $data = array();

    // Get the ID of the original question.
    $ids = array();
    $copy_data = $db->prepare("SELECT typeID FROM track_changes WHERE type='Copied Question' AND old = ? AND typeID != ?");
    $copy_data->bind_param('ii', $q_id, $q_id);
    $copy_data->execute();
    $copy_data->bind_result($typeID);
    $copy_data->store_result();
    while ($copy_data->fetch()) {
      $ids[] = $typeID;
    }
    $copy_data->close();
    
    foreach ($ids as $copyID) {
      // Look up what paper it was used on.
      $copy_question_no = 0;
      $row_number = 0;
      $row_no = 1;
      $copy_data = $db->prepare("SELECT property_id, paper_title, question, q_type FROM (papers, properties, questions) WHERE properties.property_id=papers.paper AND papers.question=questions.q_id AND paper=(SELECT paper FROM papers WHERE question=? LIMIT 1) ORDER BY screen, display_pos");
      $copy_data->bind_param('i', $copyID);
      $copy_data->execute();
      $copy_data->bind_result($copy_paperID, $copy_paper_title, $copy_question, $copy_q_type);
      $copy_data->store_result();
      while ($copy_data->fetch()) {
        if ($copy_q_type != 'info') $row_number++;
        if ($copy_question == $copyID) $copy_question_no = $row_number;
      }
      $copy_data->close();
      if ($copy_question_no == 0) {
        $data[$data_no]['question_id'] = $copyID;
      } else {
        $data[$data_no]['paperID'] = (int)$copy_paperID;
        $data[$data_no]['paper_title'] = $copy_paper_title;
        $data[$data_no]['question_no'] = $copy_question_no;
        $data[$data_no]['question_id'] = (int)$copyID;
      }
      $data_no++;
    }
    
    return $data;
  }

}
?>