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
 *  Class to handle question exclusions on papers.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class Exclusion
{
    private $db;
    private $paper_id;

    /**
     * Called when the object is unserialised.
     */
    public function __wakeup()
    {
        // The serialised database object will be invalid,
        // this object should only be serialised during an error report,
        // so adding the current database connect seems like a waste of time.
        $this->db = null;
    }

    /**
     * @param int $paperID  - ID of the current paper.
     * @param object $db    - Link to mysqli
     */
    public function __construct($paperID, $db)
    {
        $this->db = $db;
        $this->paper_id = $paperID;
    }

    /**
     * Load all exclusions for the current paper.
     */
    public function load()
    {
        $this->excluded = array();
        $result = $this->db->prepare('SELECT q_id, parts FROM question_exclude WHERE q_paper = ? ORDER BY q_id');
        $result->bind_param('i', $this->paper_id);
        $result->execute();
        $result->bind_result($q_id, $parts);
        while ($result->fetch()) {
            $this->excluded[$q_id] = $parts;
        }
        $result->close();
    }

    /**
     * Clear the database of any past exclusions from the current paper.
     */
    public function clear_all_exclusions()
    {
        if ($result = $this->db->prepare('DELETE FROM question_exclude WHERE q_paper = ?')) {
            $result->bind_param('i', $this->paper_id);
            $result->execute();
            $result->close();
        } else {
            display_error('Question_exclude Delete Error', $this->db->error);
        }
    }

    /**
     * Insert a question exclusion record into the database.
     */
    public function add_exclusion($q_id, $status)
    {
        $userObj = UserObject::get_instance();

        if ($result = $this->db->prepare("INSERT INTO question_exclude VALUES (NULL, ?, ?, ?, {$userObj->get_user_ID()}, NOW(), '')")) {
            $result->bind_param('iis', $this->paper_id, $q_id, $status);
            $result->execute();
            $result->close();
        } else {
            display_error('Question_exclude Insert Error 1', $this->db->error);
        }
    }

    /**
     * Get an exclusion for a specific question ID.
     * @param int $q_id - Question ID to look up
     * @return string - which parts of a question have been excluded.
     */
    public function get_exclusions_by_qid($q_id)
    {
        if (!isset($this->excluded[$q_id])) {
            return '0000000000000000000000000000000000000000';        // No exclusions set, return blank zeros.
        } else {
            return $this->excluded[$q_id];
        }
    }

    /**
     * Get an exclusion for a specific question ID and part.
     * @param int $q_id - Question ID to look up
     * @param int $part - Which part (character) of the exclusion string to return.
     * @return string - a particular part of an excluded question.
     */
    public function get_exclusion_part_by_qid($q_id, $part)
    {
        if (!isset($this->excluded[$q_id])) {
            return '0';       // No exclusions set, return blank zeros.
        } else {
            return mb_substr($this->excluded[$q_id], $part, 1);
        }
    }

    /**
     * Works out if a question is excluded or not.
     * @param int $q_id - Question ID to look up
     * @return bool - true or false if the question has any exclusions.
     */
    public function is_question_excluded($q_id)
    {
        if (isset($this->excluded[$q_id]) and mb_strpos($this->excluded[$q_id], '1') !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Works out if aspecific part of a question is excluded or not.
     * @param int $q_id - Question ID to look up
     * @param int $part - The part we ant to test
     * @return bool - true or false if the question/part is excluded.
     */
    public function is_question_part_excluded($q_id, $part)
    {
        if (isset($this->excluded[$q_id]) and mb_substr($this->excluded[$q_id], $part, 1) === '1') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Counts how many questions (not items) have been excluded.
     * @return int - count of how many questions are excluded.
     */
    public function get_excluded_no()
    {
        return count($this->excluded);
    }
}
