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
 * Question metadata helper functions
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 */
class QuestionsMetadata
{
    /**
     * Get question metadata
     * @param string $type the type
     * @param int $qid the question identifier
     * @return string
     */
    public static function get(string $type, int $qid)
    {
        $configObject = Config::get_instance();
        $sql = $configObject->db->prepare('SELECT value FROM questions_metadata WHERE type = ? and questionID = ?');
        $sql->bind_param('si', $type, $qid);
        $sql->execute();
        $sql->store_result();
        $sql->bind_result($value);
        $rows = $sql->num_rows;
        $sql->fetch();
        if ($rows == 0) {
            $return = '';
        } else {
            $return = $value;
        }
        $sql->close();
        return $return;
    }

    /**
     * Set a question metadata
     * @param string $type type
     * @param int $qid question identifier
     * @param string $value value
     */
    public static function set(string $type, int $qid, string $value)
    {
        $configObject = Config::get_instance();
        $current = self::get($type, $qid);
        if ($value != $current) {
            if ($current === '') {
                $sql = $configObject->db->prepare(
                    'INSERT INTO questions_metadata (id, questionID, type, value) VALUES (NULL, ?, ?, ?)'
                );
                $sql->bind_param('iss', $qid, $type, $value);
            } else {
                $sql = $configObject->db->prepare(
                    'UPDATE questions_metadata SET value = ? WHERE questionID = ? AND type = ?'
                );
                $sql->bind_param('sis', $value, $qid, $type);
            }
            $sql->execute();
            $sql->close();
        }
    }
}
