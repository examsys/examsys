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
 * Class for the timer logic
 * @author Ben Parish
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class Timer
{
    /** @var LogMetadata The user log metadata for the exam. */
    protected $log_start_time;

    /** @var int The duration of the exam. */
    protected $exam_duration;

    /** @var \DateTime The start time of the exam. */
    protected $start_datetime;

    /** @var int|null The percentage extra time the user gets on an exam. */
    protected $special_needs_percentage;

    /**
     * Constructor for the timer.
     *
     * @param LogStartTime $log_start_time
     * @param int $exam_duration
     * @param int|null $special_needs_percentage
     */
    public function __construct($log_metadata, $exam_duration, $special_needs_percentage)
    {
        $this->log_start_time = $log_metadata;
        $this->exam_duration  = $exam_duration;
        $this->special_needs_percentage  = $special_needs_percentage;
    }

    /**
     * Start the timer.
     *
     * @return void
     */
    public function start()
    {
        $metadataID = $this->log_start_time->get_metadata_id();
        $this->log_start_time->get_record($metadataID, true);
    }

    /**
     * Checks if the exam timer is started.
     *
     * @return bool
     */
    public function is_started()
    {
        return ($this->get_start_datetime() !== null);
    }


    /**
     * This never seems to be used.
     *
     * @deprecated since version 7.1.0
     */
    public function reset()
    {
        $this->log_start_time->set_started_to_null();
        $this->start_datetime = null;
    }

    /**
     * Calculates the time remaining to the user.
     *
     * @return int
     */
    protected function calculateExamDuration(): int
    {
        $exam_duration_mins = $this->exam_duration;
        $exam_duration_secs = $exam_duration_mins * 60;

        if ($this->special_needs_percentage > 0) {
            $exam_duration_secs += $exam_duration_secs * $this->special_needs_percentage / 100;
        }

        // Ensure the return value is an integer.
        return ceil($exam_duration_secs);
    }

    /**
     * Calculates the remaining time available to the user.
     *
     * @param bool $allow_negative If false the minimum value is zero (default: false)
     * @return int
     */
    public function calculate_remaining_time(bool $allow_negative = false)
    {
        $exam_duration_secs = $this->calculateExamDuration();

        // get existing start time or create a new one
        $start_datetime = $this->get_start_datetime();

        if ($start_datetime === null or $start_datetime === false) {
            $remaining_time_secs = $exam_duration_secs;
        } else {
            $start_timestamp     = $start_datetime->getTimestamp();
            $now_datetime        = new DateTime();
            $now_timestamp       = $now_datetime->getTimestamp();
            $time_elapsed_secs   = $now_timestamp - $start_timestamp;
            $remaining_time_secs = $exam_duration_secs - $time_elapsed_secs;
        }

        if (!$allow_negative and $remaining_time_secs < 1) {
            $remaining_time_secs = 0;
        }

        return ceil($remaining_time_secs);
    }

    /**
     * Gets the time the user started the exam.
     *
     * @return DateTime
     */
    public function get_start_datetime()
    {
        if ($this->start_datetime == null) {
            $this->start_datetime = $this->log_start_time->get_start_datetime();
        }

        return $this->start_datetime;
    }
}
