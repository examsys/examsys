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
 * Accomodation breaks accessor class
 * the table 'breaks' records when a break was taken on a paper by a student.
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2020 The University of Nottingham
 */
class Breaks
{
    /** The accomodation break type. */
    public const TYPE_ACCOMIDATION = 1;

    /**
     * Add an accomodation break for the user to the paper.
     * @param int $userID user id
     * @param int $paperID paper id
     * @return int
     */
    public static function addBreak(int $userID, int $paperID): int
    {
        $configObject = Config::get_instance();
        $result = $configObject->db->prepare('INSERT INTO breaks VALUES (NULL, ?, ?, NOW())');
        $result->bind_param('ii', $userID, $paperID);
        $result->execute();
        $result->close();
        return $configObject->db->insert_id;
    }

    /**
     * Get date of an accomodation break.
     * @param int $breakID break id
     * @return ?string
     */
    public static function getBreak(int $breakID): ?string
    {
        $configObject = Config::get_instance();
        $date_format = $configObject->get('cfg_long_date_time');

        $result = $configObject->db->prepare("
            SELECT
                DATE_FORMAT(break_taken, '" . $date_format . "')
            FROM
                breaks
            WHERE
                id = ?
            ");
        $result->bind_param('i', $breakID);
        $result->execute();
        $result->bind_result($break_taken);
        $result->fetch();
        $result->close();

        return $break_taken;
    }

    /**
     * Get all accomodation breaks on a paper.
     * @param int $paperID paper id
     * @return array
     */
    public static function getAllBreaks(int $paperID): array
    {
        $configObject = Config::get_instance();
        $notes = array();
        $result = $configObject->db->prepare('
            SELECT
                userID,
                id
            FROM
                breaks
            WHERE
                paperID = ?
            ORDER BY
                break_taken
            ');
        $result->bind_param('i', $paperID);
        $result->execute();
        $result->bind_result($userID, $breakID);
        while ($result->fetch()) {
            $notes[$userID][] = $breakID;
        }
        $result->close();

        return $notes;
    }
}
