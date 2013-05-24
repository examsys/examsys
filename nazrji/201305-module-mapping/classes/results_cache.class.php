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
 *  Class to handle results caching in the database.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once '../classes/mathsutils.class.php';

class ResultsCache {

  private $db;

  public function __construct($db) {
  	$this->db = $db;
  }

  public function should_cache($propertyObj, $percent, $absent, $percent, $absent, $paperID) {
    $paper_type = $propertyObj->get_paper_type();

    if ($percent != 100 or $absent == 1 or $paper_type == 0 or $paper_type == 1 or $paper_type == 3) {
      return false;
    }
    // TODO: add in a check for the past the end of the exam.
    $recache = true;

    $result = $this->db->prepare("SELECT cached FROM cache_paper_stats WHERE paperID = ? LIMIT 1");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($cached);
    $result->fetch();
    $result->close();

    if (isset($cached) and $cached != '') {
      $recache = false;
    }

    return $recache;
  }
  
  public function get_paper_cache($paperID) {
    $stats = array();
    
    $result = $this->db->prepare("SELECT max_mark, max_percent, min_mark, min_percent, q1, q2, q3, mean_mark, mean_percent, stdev_mark, stdev_percent FROM cache_paper_stats WHERE paperID = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($stats['max_mark'], $stats['max_percent'], $stats['min_mark'], $stats['min_percent'], $stats['q1'], $stats['q2'], $stats['q3'], $stats['mean_mark'], $stats['mean_percent'], $stats['stdev_mark'], $stats['stdev_percent']);
    $result->fetch();
    $result->close();
    
    return $stats;
  }
  
  public function get_paper_marks_by_student($userID) {
    $marks = array();

    $result = $this->db->prepare("SELECT paperID, percent FROM cache_student_paper_marks WHERE userID = ?");
    $result->bind_param('i', $userID);
    $result->execute();
    $result->bind_result($paperID, $percent);
    while ($result->fetch()) {
      $marks[$paperID] = $percent;
    }
    $result->close();
    
    return $marks;
  }
  
  public function get_paper_marks_by_paper($paperID) {
    $marks = array();

    $result = $this->db->prepare("SELECT userID, percent FROM cache_student_paper_marks WHERE paperID = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($userID, $percent);
    while ($result->fetch()) {
      $marks[$userID] = $percent;
    }
    $result->close();
    
    return $marks;
  }
  
  public function save_paper_cache($propertyObj, $percent, $absent, $stats, $paperID) {
    $result = $this->db->prepare("REPLACE INTO cache_paper_stats (paperID, cached, max_mark, max_percent, min_mark, min_percent, q1, q2, q3, mean_mark, mean_percent, stdev_mark, stdev_percent) VALUES (?, UNIX_TIMESTAMP(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $result->bind_param('iddddddddddd', $paperID, $stats['max_mark'], $stats['max_percent'], $stats['min_mark'], $stats['min_percent'], $stats['q1'], $stats['q2'], $stats['q3'], $stats['mean_mark'], $stats['mean_percent'], $stats['stddev_mark'], $stats['stddev_percent']);
    $result->execute();
    $result->close();
  }

  public function save_student_mark_cache($propertyObj, $percent, $absent, $user_results, $paperID) {
    $user_no = count($user_results);

    $result = $this->db->prepare("REPLACE INTO cache_student_paper_marks (paperID, userID, mark, percent) VALUES (?, ?, ?, ?)");
    for ($i=0; $i<$user_no; $i++) {
      $result->bind_param('iidd', $paperID, $user_results[$i]['tmp_userID'], $user_results[$i]['mark'], $user_results[$i]['adj_percent']);
      $result->execute();
    }
    $result->close();
  }

  public function save_median_question_marks($propertyObj, $percent, $absent, $q_medians, $paperID) {
    $result = $this->db->prepare("REPLACE INTO cache_median_question_marks (paperID, questionID, median, mean) VALUES (?, ?, ?, ?)");
    foreach ($q_medians as $q_id=>$median_array) {
      $median = MathsUtils::median($median_array);
      $mean   = MathsUtils::mean($median_array);

      $result->bind_param('iidd', $paperID, $q_id, $median, $mean);
      $result->execute();
    }
    $result->close();
  }
}