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
 *
 * Utility class for Textbox Marking related functionality
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class textbox_marking_utils
{
    /**
     * Returns an array of user IDs who are down for second marking.
     *
     * @param $paperID - ID of the paper to be used
     * @param $db      - Database connection
     * @return array   - List of users who are set for remarking.
     */
    public static function get_remark_users($paperID, $db)
    {
        $remark_array = array();

        $result = $db->prepare('SELECT userID FROM textbox_remark WHERE paperID = ?');
        $result->bind_param('i', $paperID);
        $result->execute();
        $result->bind_result($userID);
        while ($result->fetch()) {
            $remark_array[$userID] = true;
        }
        $result->close();

        return $remark_array;
    }

    /**
     * Converts a time/date from 20140301103059 into 01/03/2014 10:30.
     *
     * Please use date_utils::rogoToDisplay instead. This function will be removed in the future.
     *
     * @param string $original - The date that needs to be convered.
     * @return string
     * @deprecated since 7.2.0
     */
    public static function nicedate($original)
    {
        return date_utils::rogoToDisplay($original);
    }

    /**
     * Highlight key terms in user answer.
     *
     * @param array $settings question settings
     * @param string $answer user answer
     * @return string
     */
    public static function higlightterms($settings, $answer)
    {
        if (isset($settings['terms'])) {
            $correct_answers = json_decode($settings['terms']);
            if (!is_null($correct_answers)) {
                foreach ($correct_answers as $single_answer) {
                    $answer = str_ireplace(
                        $single_answer,
                        '<span class="highlight">' . $single_answer . '</span>',
                        $answer
                    );
                }
            }
        }
        return $answer;
    }
}
