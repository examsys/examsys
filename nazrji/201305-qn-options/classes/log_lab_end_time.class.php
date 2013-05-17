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
 * Repository class for the log_lab_end_time table
 *
 * @author Ben Parish
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

class LogLabEndTime {

  /**
   * @var Lab $lab_id
   */
  private $lab_id;

  private $msg;

  /**
   * @var PropertyObject $property_object Object holding the properties for the paper
   */
  private $property_object;

  /**
   * @var mysqli $db
   */
  private $db;

  private $end_datetime_cached = false;
  private $start_timestamp = false;

  /**
   * @param Lab $lab_id
   * @param PropertyObject $property_object Object holding the properties for the paper
   * @param mysqli $db
   */
  public function __construct($lab_id, $property_object, $db) {

    $this->lab_id = $lab_id;
    $this->property_object = $property_object;
    $this->db = $db;
  }

  /**
   * Gets the exam session's current end time stored when the invigilator clicked 'Start'
   *
   * @return DateTime
   */
  public function get_session_end_date_datetime() {

    if ($this->end_datetime_cached == false) {
      $this->msg = '';

      $start_datetime = $this->get_paper_start_datetime();

      if ($start_datetime === false ) {   // Unscheduled summative paper?
        return false;
      }

      $start_timestamp = $start_datetime->getTimestamp();

      $query = 'SELECT start_time AS start_timestamp, end_time AS end_timestamp FROM log_lab_end_time WHERE labID = ? AND paperID = ? AND end_time > ? ORDER BY id DESC LIMIT 1';
      $stmt = $this->db->prepare($query);
      $lab_id = $this->get_lab_id();
      $paper_id = $this->get_paper_id();
      $stmt->bind_param('iii', $lab_id, $paper_id, $start_timestamp);
      $stmt->execute();
      $stmt->store_result();
      $bindResult = $stmt->bind_result($this->start_timestamp, $end_timestamp);

      // No result
      if ($stmt->num_rows <= 0) {
        $stmt->close();
        return false;
      }

      $stmt->fetch();
      $stmt->close();

      $this->end_datetime_cached = new DateTime();

      $this->end_datetime_cached->setTimestamp($end_timestamp);
    }

     return clone $this->end_datetime_cached;

  }

  /**
   * Calculate the end time for a paper and record it in the database
   * @param  integer  $invigilator_id ID of the invigilator setting the end time for the paper
   * @param  string   $time           Time at which to end the exam as an interval from midnight in interval_spec format
   * @return DateTime                 DateTime object representing new end time
   */
  public function save($invigilator_id, $time = NULL) {

    $this->msg = '';

    $query = 'INSERT INTO log_lab_end_time (labID, invigilatorID, paperID, start_time, end_time) VALUES (?, ?, ?, ?, ?)';

    $stmt = $this->db->prepare($query);
    if(is_null($time)) {
      $start_time_datetime = new DateTime();

      $end_datetime = $this->calculate_end_datetime($start_time_datetime);
      $start_date = time();
    } else {
      if ($this->start_timestamp === false) {
        $this->start_timestamp = $start_date = time();
      } else {
        $start_date = $this->start_timestamp;
      }

      $dispzone = new DateTimeZone($this->property_object->get_timezone());
      $end_datetime = new DateTime("now", $dispzone);

      $end_datetime->setTime(0, 0, 0);
      $dateinterval = new DateInterval($time);

      $end_datetime->add($dateinterval);

      $curtz1 = new DateTime();
      $curtz = $curtz1->getTimezone();
      $end_datetime->setTimezone($curtz);
    }
    $end_time = $end_datetime->getTimestamp();
    $tz = $this->property_object->get_timezone();

    $lab_id = $this->get_lab_id();
    $paper_id = $this->get_paper_id();

    $stmt->bind_param('iiiii', $lab_id, $invigilator_id, $paper_id, $start_date, $end_time);

    $stmt->execute();
    $stmt->close();

    // Update cached end time
    $this->end_datetime_cached = $end_datetime;

    return $end_datetime;
  }

  public function delete() {

    $this->msg = '';

    $query = 'DELETE FROM log_lab_end_time WHERE labID = ? AND paperID = ?';

    $stmt = $this->db->prepare($query);

    $paper_id = $this->get_paper_id();

    $stmt->bind_param('ii', $this->labID, $paper_id);

    $stmt->execute();
    $stmt->close();
  }

  public function get_message() {
    return $this->msg;
  }

  /**
   * Takes current time and adds the exam duration to it to get the end time for the current session
   * @param  DateTime $start_datetime   Start time of current session
   * @return DateTime                   End time of the current session
   */
  private function calculate_end_datetime(DateTime $start_datetime) {

    $exam_duration_mins = $this->get_paper_exam_duration();
    $exam_duration_secs = $exam_duration_mins * 60;
    $paper_end_datetime = $this->get_paper_end_datetime();

    // Add extra time
    $date_interval = new DateInterval('PT' . $exam_duration_secs . 'S');
    $start_datetime->add($date_interval);

    return $start_datetime;
  }

  /**
   * This is called if there is no record in log_lab_end_time
   * It then defaults to using paper's start time and then adds the exam duration to get the end time
   * @return DateTime
   */
  public function calculate_default_session_end_datetime() {
    $start_datetime = $this->property_object->get_start_date();
    $duration = $this->property_object->get_exam_duration() * 60;
    $end_timestamp = $start_datetime + $duration;
    return DateTime::createFromFormat('U', $end_timestamp);
  }

  /**
   * @return int
   */
  public function get_paper_id() {
    return $this->property_object->get_property_id();
  }

  /**
   * @return int
   */
  public function get_lab_id() {
    return $this->lab_id;
  }

  /**
   * @return int
   */
  public function get_paper_exam_duration() {
    return $this->property_object->get_exam_duration();
  }

  /**
   * @return int
   */
  public function get_paper_exam_paper_type() {
    return $this->property_object->get_paper_type();
  }

  /**
   * @return DateTime
   */
  public function get_paper_start_datetime() {
    $start_date = $this->property_object->get_start_date();
    return DateTime::createFromFormat('U', $start_date);
  }

  /**
   * @return DateTime
   */
  public function get_paper_end_datetime() {
    $end_date = $this->property_object->get_end_date();

    return DateTime::createFromFormat('U', $end_date);
  }

  /**
   * @return int
   */
  public function get_paper_end_timestamp() {
    $paper_end_datetime = $this->get_paper_end_datetime();
    if ($paper_end_datetime === false) {
      return false;
    }

    return $paper_end_datetime->getTimestamp();
  }

  /**
   * Get the date/time of when the paper was started or paper default start time if not already started
   * @return DateTime When the exam was started for this lab
   */
  public function get_started_timestamp() {
    if ($this->start_timestamp === false) {
      return $this->property_object->get_start_date();
    } else {
      return $this->start_timestamp;
    }
  }
}





























