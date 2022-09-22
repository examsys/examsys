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
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class ToiletBreaks
{
    public static function add_toilet_break($userID, $paperID, $db)
    {
        $result = $db->prepare('INSERT INTO toilet_breaks VALUES (NULL, ?, ?, NOW())');
        $result->bind_param('ii', $userID, $paperID);
        $result->execute();
        $result->close();
    }

    public static function toilet_break_by_id($breakID, $db)
    {
        $configObject = Config::get_instance();
        $date_format = $configObject->get('cfg_long_date_time');

        $result = $db->prepare("SELECT DATE_FORMAT(break_taken, '" . $date_format . "') FROM toilet_breaks WHERE id = ?");
        $result->bind_param('i', $breakID);
        $result->execute();
        $result->bind_result($break_taken);
        $result->fetch();
        $result->close();

        return $break_taken;
    }

    public static function get_all_breaks_by_paper($paperID, $db)
    {
        $notes = array();
        // Query any student toilet breaks for the current paper
        $result = $db->prepare('SELECT userID, id FROM toilet_breaks WHERE paperID = ? ORDER BY break_taken');
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
