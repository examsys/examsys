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
 * Log Break Time Accessor Class
 * the table 'log_break_time' records the amount of break time a student has left per paper.
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2020 The University of Nottingham
 */
class LogBreakTime
{
    /**
     * Set break time
     * @param int $userid user id
     * @param int $paperid paper id
     * @param int $time break time in seconds
     * @throws Exception
     */
    public static function setBreak(int $userid, int $paperid, int $time): void
    {
        $configObject = Config::get_instance();
        if (self::getBreak($userid, $paperid) === -1) {
            $result = $configObject->db->prepare('
                INSERT INTO log_break_time (paperID, userID, time) VALUES (?, ?, ?)
            ');
            $result->bind_param('iii', $paperid, $userid, $time);
        } else {
            $result = $configObject->db->prepare('
                UPDATE log_break_time SET time = ? WHERE paperID = ? AND userID = ?
            ');
            $result->bind_param('iii', $time, $paperid, $userid);
        }
        $result->execute();
        if ($configObject->db->error) {
            throw new Exception('breaks_update_error');
        }
    }

    /**
     * Get break time (seconds) available to a student on an exam
     * -1 returned if the student has never paused the exam.
     * @param int $userid user id
     * @param int $paperid paper id
     * @return int
     */
    public static function getBreak(int $userid, int $paperid): int
    {
        $configObject = Config::get_instance();
        $result = $configObject->db->prepare('
            SELECT
                time
            FROM 
                log_break_time 
            WHERE
                userID = ?
            AND
                paperID = ?
        ');
        $result->bind_param('ii', $userid, $paperid);
        $result->execute();
        $result->bind_result($time);
        $result->fetch();
        $result->close();
        if (!is_null($time)) {
            return $time;
        } else {
            return -1;
        }
    }

    /**
     * Delete users break time
     * @param int $userid user id
     * @param int $paperid paper id
     */
    public static function deleteBreak(int $userid, int $paperid): void
    {
        $configObject = Config::get_instance();
        $result = $configObject->db->prepare('
            DELETE FROM log_break_time WHERE paperID = ? AND userID = ?
        ');
        $result->bind_param('ii', $paperid, $userid);
        $result->execute();
    }

    /**
     * Calculates the total amount of break time based on the criteria.
     *
     * @param int $standard_duration The standard duration of the exam.
     * @param int|null $special_needs The percentage of extra time a user gets for the exam.
     * @param int|null $userbreaks The amount of break time a user is eligible to take.
     * @return int
     */
    public static function calculateBreakTime(int $standard_duration, ?int $special_needs, ?int $userbreaks): int
    {
        if (!is_null($special_needs) and $special_needs > 0) {
            $extratime = 1 + ($special_needs / 100);
        } else {
            $extratime = 1;
        }

        $examtime = $standard_duration * $extratime;

        if (Config::get_instance()->get_setting('core', 'paper_breaktime_mins')) {
            $durationsecs = (ceil($examtime / 3600) * $userbreaks) * 60;
        } else {
            if (!is_null($userbreaks) and $userbreaks > 0) {
                $durationsecs = $examtime * ($userbreaks / 100);
            } else {
                $durationsecs = 0;
            }
        }
        return $durationsecs;
    }
}
