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
 * Timer for remote summative exams.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 */
class RemoteSummativeTimer extends Timer
{
    /**
     * RemoteSummativeTimer constructor.
     *
     * @param \LogMetadata $log_metadata
     * @param int $exam_duration
     * @param ?int $special_needs_percentage
     * @param PaperProperties $properties
     */
    public function __construct(
        LogMetadata $log_metadata,
        int $exam_duration,
        ?int $special_needs_percentage
    ) {
        parent::__construct($log_metadata, $exam_duration, $special_needs_percentage);
    }

    /**
     * Calculates the exam duration for the user taking into account any breaks taken.
     *
     * @return int
     */
    protected function calculateExamDuration(): int
    {
        $exam_duration = parent::calculateExamDuration();

        if (!Config::get_instance()->get_setting('core', 'paper_pause_exam')) {
            // We do not need to add any break time in.
            return $exam_duration;
        }

        $userid = $this->log_start_time->getUserID();
        $breaks = null;

        /* @var \UserObject $current_user */
        $current_user = UserObject::get_instance();
        if ($current_user->get_user_ID() == $userid) {
            // We can save a database query by getting the value from the current user object.
            $breaks = $current_user->getRequiresBreaks();
        } else {
            // We need to query the special needs record for the user.
            $sql = 'SELECT break_time FROM special_needs WHERE userID = ?';
            $query = Config::get_instance()->db->prepare($sql);
            $query->bind_param('i', $userid);
            $query->execute();
            $query->bind_result($breaks);
            $query->fetch();
            $query->close();
        }

        if (!empty($breaks)) {
            // User should get breaks.
            $remaining = LogBreakTime::getBreak($userid, $this->log_start_time->getPaperID());
            $max_break = LogBreakTime::calculateBreakTime($this->exam_duration * 60, $this->special_needs_percentage, $breaks);
            if ($remaining !== -1) {
                $breaks_taken = round($max_break - $remaining);
                $exam_duration += $breaks_taken;
            }
        }

        return $exam_duration;
    }
}
