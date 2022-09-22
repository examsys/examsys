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
 *  Class to handle standard setting routines.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class StandardSetting
{
    private $db;

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

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function get_ratings_by_question($std_setID)
    {
        $std_set_array = array();

        $result = $this->db->prepare('SELECT questionID, rating FROM std_set_questions WHERE std_setID = ?');
        $result->bind_param('i', $std_setID);
        $result->execute();
        $result->bind_result($questionID, $rating);
        while ($result->fetch()) {
            $std_set_array[$questionID] = $rating;
        }
        $result->close();

        return $std_set_array;
    }

    public function get_pass_distinction($std_setID)
    {
        $result = $this->db->prepare('SELECT pass_score, distinction_score FROM std_set WHERE id = ? LIMIT 1');
        $result->bind_param('i', $std_setID);
        $result->execute();
        $result->bind_result($pass_score, $distinction_score);
        $result->fetch();
        $result->close();

        return array('pass_score' => $pass_score, 'distinction_score' => $distinction_score);
    }

    /**
     * Get standard setting data for a paper
     *
     * @param $paper_id int Paper ID
     *
     * @return array List of standard setting entries associated with paper
     */
    public static function get_std_set(int $paper_id)
    {
        $db = Config::get_instance()->db;
        $std_settings_from = $db->prepare('SELECT id, setterID, std_set, method, group_review, pass_score, distinction_score'
            . ' FROM std_set WHERE paperID = ?');
        $std_settings_from->bind_param('i', $paper_id);
        $std_settings_from->execute();
        $std_settings_from->store_result();
        $std_settings_from->bind_result($std_id, $setter_id, $std_set, $method, $group_review, $pass_score, $distinction_score);
        $results = [];
        while ($std_settings_from->fetch()) {
            $results[$std_id] = compact('setter_id', 'std_set', 'method', 'group_review', 'pass_score', 'distinction_score');
        }
        return $results;
    }

    /**
     * Create new standard setting data for a paper
     *
     * @param $paper_id int
     * @param $setter_id int
     * @param $std_set string
     * @param $method string
     * @param $group_review string
     * @param $pass_score float
     * @param $distinction_score float
     *
     * @return int New standard settings auto-increment ID
     */
    public static function new_std_set(int $paper_id, int $setter_id, string $std_set, string $method, string $group_review, float $pass_score, float $distinction_score)
    {
        $db = Config::get_instance()->db;
        $std_settings_to = $db->prepare('INSERT INTO std_set (setterID, paperID, std_set, method, group_review, pass_score,'
            . ' distinction_score) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $std_settings_to->bind_param('iisssdd', $setter_id, $paper_id, $std_set, $method, $group_review, $pass_score, $distinction_score);
        $std_settings_to->execute();
        $new_std_id = $db->insert_id;
        return $new_std_id;
    }

    /**
     * Copy individual question ratings from one standard set to another for the same question IDs
     *
     * @param $old_std_id int
     * @param $new_std_id int
     */
    protected static function copy_std_set_ratings_linked(int $old_std_id, int $new_std_id)
    {
        $db = Config::get_instance()->db;
        $std_settings_ratings_transfer = $db->prepare('INSERT INTO std_set_questions (std_setID, questionID, rating)'
            . ' SELECT ?, questionID, rating FROM std_set_questions WHERE std_setID = ?');
        $std_settings_ratings_transfer->bind_param('ii', $new_std_id, $old_std_id);
        $std_settings_ratings_transfer->execute();
        $std_settings_ratings_transfer->close();
    }

    /**
     * Copy individual question ratings from one standard set to another for different question IDs
     * @param $old_std_id int
     * @param $new_std_id int
     * @param $qid_lookup array key/value array of old question IDs => new question IDs
     */
    protected static function copy_std_set_ratings_copied(int $old_std_id, int $new_std_id, array $qid_lookup)
    {
        $db = Config::get_instance()->db;
        $std_settings_ratings_from = $db->prepare('SELECT questionID, rating FROM std_set_questions WHERE std_setID = ?');
        $std_settings_ratings_from->bind_param('i', $old_std_id);
        $std_settings_ratings_from->execute();
        $std_settings_ratings_from->store_result();
        $std_settings_ratings_from->bind_result($old_qid, $rating);

        $std_settings_ratings_to = $db->prepare('INSERT INTO std_set_questions (std_setID, questionID, rating) VALUES (?, ?, ?)');

        while ($std_settings_ratings_from->fetch()) {
            if (!isset($qid_lookup[$old_qid])) {
                // May be a better way of handling this, open to suggestions, but want to ensure we don't get broken data
                throw new \coding_exception('Error copying from old std_set to new std_set - new question ID lookup not found for ' . $old_qid);
            }
            $std_settings_ratings_to->bind_param('iis', $new_std_id, $qid_lookup[$old_qid], $rating);
            $std_settings_ratings_to->execute();
        }

        $std_settings_ratings_from->close();
        $std_settings_ratings_to->close();
    }

    /**
     * Copy standard settings from one exam to another with linked questions
     *
     * @param $old_paper_id int
     * @param $new_paper_id int
     */
    public static function copy_std_setting_to_paper_linked(int $old_paper_id, int $new_paper_id)
    {
        $db = Config::get_instance()->db;
        $std_settings_from = self::get_std_set($old_paper_id);
        foreach ($std_settings_from as $old_std_id => $std_setting_data) {
            $new_std_id = self::new_std_set(
                $new_paper_id,
                $std_setting_data['setter_id'],
                $std_setting_data['std_set'],
                $std_setting_data['method'],
                $std_setting_data['group_review'],
                $std_setting_data['pass_score'],
                $std_setting_data['distinction_score'],
                $db
            );
            self::copy_std_set_ratings_linked($old_std_id, $new_std_id);
        }
    }

    /**
     * Copy standard settings from one exam to another with copied questions
     *
     * @param $old_paper_id int
     * @param $new_paper_id int
     * @param $old_qids array
     * @param $new_qids array
     */
    public static function copy_std_setting_to_paper_copied(int $old_paper_id, int $new_paper_id, array $old_qids, array $new_qids)
    {
        $std_settings_from = self::get_std_set($old_paper_id);

        // Create QID lookup table
        $qid_lookup = [];
        foreach ($old_qids as $i => $qid) {
            $qid_lookup[$qid] = $new_qids[$i];
        }

        foreach ($std_settings_from as $old_std_id => $std_setting_data) {
            $new_std_id = self::new_std_set(
                $new_paper_id,
                $std_setting_data['setter_id'],
                $std_setting_data['std_set'],
                $std_setting_data['method'],
                $std_setting_data['group_review'],
                $std_setting_data['pass_score'],
                $std_setting_data['distinction_score']
            );
            self::copy_std_set_ratings_copied($old_std_id, $new_std_id, $qid_lookup);
        }
    }
}
