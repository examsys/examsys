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
* Class for the timer logic
* @author Ben Parish
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

class Timer {

  /**
   * @var LogStartTime $log_start_time
   */

  private $log_start_time;

  private $exam_duration;
  private $start_datetime;
  private $special_needs_percentage;

  /**
   * @param LogStartTime $log_start_time
   * @param int $exam_duration
   */
  public function __construct($log_metadata, $exam_duration, $special_needs_percentage) {
    $this->log_start_time = $log_metadata;
    $this->exam_duration  = $exam_duration;
    $this->special_needs_percentage  = $special_needs_percentage;
  }

  /**
   * @return DateTime
   */
  public function start(){
    return $this->log_start_time->insert();
  }

  /**
   * @return bool
   */
  public function is_started(){
    return ( $this->get_start_time() !== null );
  }


  public function reset(){
    $this->log_start_time->delete();
    $this->start_datetime = null;
  }

  /**
   * @return int
   */
  public function calculate_remaining_time() {

    $exam_duration_mins  = $this->exam_duration;
    $exam_duration_secs  = $exam_duration_mins * 60;

    if ($this->special_needs_percentage > 0) {
      $exam_duration_secs += $exam_duration_secs * $this->special_needs_percentage/100;
    }

    // get existing start time or create a new one
    $start_datetime      = $this->get_start_datetime();

    if($start_datetime === null or $start_datetime === false){
      $remaining_time_secs = $exam_duration_secs;
    } else {
      $start_timestamp     = $start_datetime->getTimestamp();
      $now_datetime        = new DateTime;
      $now_timestamp       = $now_datetime->getTimestamp();
      $time_elapsed_secs   = $now_timestamp - $start_timestamp;
      $remaining_time_secs = $exam_duration_secs - $time_elapsed_secs;
    }

    if( $remaining_time_secs < 1 ){
      $remaining_time_secs = 0;
    }

    return ceil($remaining_time_secs);

  }

  /**
   * @return DateTime
   */
  public  function get_start_datetime(){

    if( $this->start_datetime == null ){
      $this->start_datetime = $this->log_start_time->get_start_datetime();
    }

    return $this->start_datetime;
  }



}

?>