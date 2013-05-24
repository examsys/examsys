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
 * Repository class for the log_extra_time table
 *
 * @author Ben Parish
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

class LogExtraTime {

  /**
   * @var LogLabEndTime $log_lab_end_time
   */
  private $log_lab_end_time;

  /**
   * @var UserObject $student_object
   */
  private $student_object;

  /**
   * @var mysqli $db
   */
  private $db;
  private $msg;

  private $log_extra_time_cache;
  private $use_cache = false;

  /**
   * @return LogLabEndTime $log_lab_end_time
   * @return array    $student_object
   * @return mysqli        $db
   */
  public function __construct(LogLabEndTime $log_lab_end_time, $student_object, mysqli $db, $cached = false) {

    $this->log_lab_end_time = $log_lab_end_time;
    $this->student_object = $student_object;
    $this->db = $db;

    if ($cached) {
      $this->populate_cache();
      $this->use_cache = true;
    }

  }

  /**
   * Retrieve and store all records in the extra time log for the paper to avoid large number of queries
   */
  private function populate_cache() {

    $paper_id = $this->get_paper_id();

    $query = 'SELECT extra_time, userID, labID FROM log_extra_time WHERE paperID = ?';
    $stmt = $this->db->prepare($query);
    $stmt->bind_param('i', $paper_id);
    $stmt->execute();
    $stmt->store_result();

    $bindResult = $stmt->bind_result($extra_time_secs, $userID, $labID);

    while ( $stmt->fetch() ) {
      $this->log_extra_time_cache[$userID][$labID]['extra_time_secs'] =  $extra_time_secs;
    }
    $stmt->close();
  }

  /**
   * @return int
   */
  public function get_extra_time_secs() {

    $lab_id = $this->get_lab_id();
    $student_id = $this->get_student_id();
    $paper_id = $this->get_paper_id();

    if ($this->use_cache) {
      $extra_time_secs = false;
      if(isset($this->log_extra_time_cache[$student_id][$lab_id]['extra_time_secs'])) {
        return $this->log_extra_time_cache[$student_id][$lab_id]['extra_time_secs'];
      } else {
        return false;
      }
    }

    $query = 'SELECT extra_time FROM log_extra_time WHERE labID = ? AND userID = ? AND paperID = ?';
    $stmt = $this->db->prepare($query);
    $stmt->bind_param('iii', $lab_id, $student_id, $paper_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows < 1) {
      $stmt->close();

      return false;
    }

    $bindResult = $stmt->bind_result($extra_time_secs);

    $stmt->fetch();
    $stmt->close();

    return $extra_time_secs;

  }

  /**
   * @param int $invigilator_id
   * @param int $extra_time_minutes
   */
  public function save($invigilator_id, $extra_time_minutes) {

    if ($extra_time_minutes === 0) {
      return 0;
    }

    $query = 'INSERT INTO log_extra_time (labID, paperID, invigilatorID, userID, extra_time) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE extra_time = ?';

    $stmt = $this->db->prepare($query);

    $lab_id = $this->get_lab_id();
    $paper_id = $this->get_paper_id();
    $student_id = $this->get_student_id();

    $extra_time_seconds = $extra_time_minutes * 60;

    $stmt->bind_param('iiiiii', $lab_id, $paper_id, $invigilator_id, $student_id, $extra_time_seconds, $extra_time_seconds);

    $stmt->execute();
    $stmt->close();

    return $extra_time_seconds;

  }

  public function delete($invigilator_id) {
    $query = 'DELETE FROM log_extra_time WHERE labID = ? AND paperID = ? AND invigilatorID = ? AND userID = ?';

    $stmt = $this->db->prepare($query);

    $lab_id = $this->get_lab_id();
    $paper_id = $this->get_paper_id();
    $student_id = $this->get_student_id();

    $stmt->bind_param('iiii', $lab_id, $paper_id, $invigilator_id, $student_id);

    $stmt->execute();
    $stmt->close();
  }

  /**
   * @return int
   */
  public function get_paper_exam_duration() {
    return $this->log_lab_end_time->get_paper_exam_duration();
  }

  public function get_paper_exam_start_time() {
    return $this->log_lab_end_time->get_paper_start_datetime();
  }

  /**
   * @return int
   */
  private function get_paper_id() {
    return $this->log_lab_end_time->get_paper_id();
  }

  /**
   * @return int
   */
  private function get_lab_id() {
    return $this->log_lab_end_time->get_lab_id();
  }

  /**
   * @return int
   */
  private function get_student_id() {
    return $this->student_object['user_ID'];
  }

  /**
   * @return int
   */
  private function get_user_id() {
    return $this->student_object['get_user_ID'];
  }

  /**
   * used in cached mode to change the student of intrest
   */
  public function set_student_object($student_object) {
    $this->student_object = $student_object;
  }

  /**
   * @return int
   */
  public function get_students_special_needs_percentage() {
    return $this->student_object['special_needs_percentage'];
  }

  /**
   * @return int
   */
  private function get_paper_end_timestamp() {
    return $this->log_lab_end_time->get_paper_end_timestamp();
  }

  /**
   * @return int
   */
  public function get_session_end_datetime() {
    return $this->log_lab_end_time->get_session_end_date_datetime();
  }

  /**
   * @return int
   */
  public function get_default_session_end_datetime() {
    return $this->log_lab_end_time->calculate_default_session_end_datetime();
  }
}