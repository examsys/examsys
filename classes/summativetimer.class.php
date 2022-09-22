<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class containing the timer logic for summative exams
 * @author Ben Parish
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class SummativeTimer
{
    /*
    * @var LogExtraTime $log_extra_time
    */

    private $log_extra_time;
    private $exam_duration;
    private $start_time;

    /*
    * @param LogExtraTime $log_extra_time
    */

    public function __construct(LogExtraTime $log_extra_time)
    {
        $this->log_extra_time = $log_extra_time;
    }

    /*
    * This takes the end time for the student's exam session and calculates the time remaining by
    * subtracting the current time stamp from the session end time stamp. It then adds any
    * special needs allowance
    *
    * @param bool $allow_negative If false the minimum value is zero (default: false)
    * @return int|false
    */
    public function calculate_remaining_time_secs(bool $allow_negative = false)
    {

        //has the lab got an end time set?
        $session_end_datetime = $this->get_session_end_datetime();

        if ($session_end_datetime === false) {
            // Invigilator hasn't pressed Start - we aren't timing the exam
            return false;
        }

        $session_end_timestamp = $session_end_datetime->getTimestamp();

        //has the student been given extra time?
        $extra_time = $this->get_extra_time_secs();

        $now_timestamp = time();

        $special_needs_secs = $this->calculate_special_needs_secs();

        $remaining_time_secs = $session_end_timestamp - $now_timestamp + $extra_time + $special_needs_secs;

        if (!$allow_negative and $remaining_time_secs < 1) {
            $remaining_time_secs = 0;
        }

        return ceil($remaining_time_secs);
    }

    /*
    * @return int
    */
    private function get_paper_exam_start_time()
    {
        return $this->log_extra_time->get_paper_exam_start_time();
    }

    /*
    * @return int
    */
    private function get_paper_exam_duration()
    {
        return $this->log_extra_time->get_paper_exam_duration();
    }

    /*
    * @return int
    */
    private function get_extra_time_secs()
    {
        $extra_time = $this->log_extra_time->get_extra_time_secs();

        if ($extra_time === false) {
            return false;
        }
        return $extra_time;
    }

    /*
    * @return int
    */
    private function calculate_special_needs_secs()
    {
        $students_special_needs_percentage = $this->log_extra_time->get_students_special_needs_percentage();
        $exam_duration                     = $this->get_paper_exam_duration();

        return ( $exam_duration * 60  ) * ( $students_special_needs_percentage / 100 );
    }

    /*
    * return int
    */
    private function get_session_end_datetime()
    {
        return $this->log_extra_time->get_session_end_datetime();
    }

    /*
    * return int
    */
    private function get_default_session_end_datetime()
    {
        return $this->log_extra_time->get_default_session_end_datetime();
    }
}
