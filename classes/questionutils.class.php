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
 * Utility class for question related functions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class QuestionUtils
{
    /**
     * Does a given Question ID exist in the question bank.
     * @param integer $q_id the question ID to be searched for.
     * @param resource $db the database connection.
     * @return string The leadin
     */
    public static function question_exists($q_id, $db)
    {
        $stmt = $db->prepare('SELECT q_id FROM questions WHERE q_id = ? AND deleted IS NULL LIMIT 1');
        $stmt->bind_param('i', $q_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($tmp_q_id);
        $stmt->fetch();
        $exists = ($stmt->num_rows == 0) ? false : true;
        $stmt->close();

        return $exists;
    }

    /**
     * Does a given Question ID exist on a specific paper.
     * @param integer $q_id the question ID to be searched for.
     * @param integer $paperID the paper ID to be searched for.
     * @param resource $db the database connection.
     * @return string The leadin
     */
    public static function question_exists_on_paper($q_id, $paperID, $db)
    {
        $stmt = $db->prepare('SELECT q_id FROM questions, papers WHERE papers.question = questions.q_id AND paper = ? AND q_id = ? LIMIT 1');
        $stmt->bind_param('ii', $paperID, $q_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($tmp_q_id);
        $stmt->fetch();
        $exists = ($stmt->num_rows == 0) ? false : true;
        $stmt->close();

        return $exists;
    }

    /**
     * Get the owner ID for a particular question.
     * @param integer $q_id the question ID to be looked up.
     * @param resource $db the database connection.
     * @return string The leadin
     */
    public static function get_ownerID($q_id, $db)
    {
        $stmt = $db->prepare('SELECT ownerID FROM questions WHERE q_id = ? LIMIT 1');
        $stmt->bind_param('i', $q_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($ownerID);
        $stmt->fetch();
        $stmt->close();

        return $ownerID;
    }

    /**
     * Get the leading for a give question ID
     * @param integer $q_id the question ID to be looked up.
     * @param resource $db the database connection.
     * @return string The leadin
     */
    public static function get_leadin($q_id, $db)
    {
        $stmt = $db->prepare('SELECT leadin FROM questions WHERE q_id = ? LIMIT 1');
        $stmt->bind_param('i', $q_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($leadin);
        $stmt->fetch();
        $leadin = ($stmt->num_rows == 0) ? '' : $leadin;
        $stmt->close();

        return $leadin;
    }

    /**
     * Strip tags from the leading string (if it doesn't contain equations) and trim length
     * @param $leadin
     * @param $limit - character limit
     * @return string
     */
    public static function clean_leadin($leadin, $limit = 160)
    {
        $texteditorplugin = \plugins\plugins_texteditor::get_editor();
        // Check if editor has clean rule i.e. for equations.
        if ($texteditorplugin->clean_leadin($leadin)) {
            $leadin = strip_tags($leadin);
            if (mb_strlen($leadin) > $limit and $limit != 0) {
                $leadin = mb_substr($leadin, 0, $limit) . '...';
            }
        } else {
            $leadin = trim(str_replace('&nbsp;', ' ', $leadin));
        }
        $leadin = trim($leadin);

        return $leadin;
    }

    /**
     * returns an array of id/keywords that the question is on
     * @param intager $q_id the id of the questions
     * @param resource $db the database connection.
     * @return array of keywords
     */
    public static function get_keywords($q_id, $db)
    {
        $keywords = array();

        $stmt = $db->prepare('SELECT keywordID, keyword FROM keywords_question, keywords_user WHERE q_id = ? and keywords_question.keywordID = keywords_user.id');
        $stmt->bind_param('i', $q_id);
        $stmt->execute();
        $stmt->bind_result($keywordID, $keyword);
        while ($res = $stmt->fetch()) {
            $keywords[$keywordID] = $keyword;
        }
        $stmt->close();

        return $keywords;
    }

    /**
     * returns an array of question IDs/module IDs
     * @param array $q_ids list of questions to check
     * @param resource $db the database connection.
     * @return array of modules keyed on q_id
     */
    public static function multi_get_modules($q_ids, $db)
    {
        $modules = array();

        $stmt = $db->prepare('SELECT q_id, idMod FROM questions_modules WHERE q_id IN (' . implode(',', $q_ids) . ')');
        $stmt->execute();
        $stmt->bind_result($q_id, $idMod);
        while ($res = $stmt->fetch()) {
            $modules[$q_id][$idMod] = $idMod;
        }
        $stmt->close();

        return $modules;
    }

    /**
     * returns an array of modules/teams that the question is on
     * @param integer $q_id the id of the questions
     * @param resource $db the database connection.
     * @return array of modules keyed on idMod
     */
    public static function get_modules($q_id, $db)
    {
        $modules = array();

        $stmt = $db->prepare('SELECT idMod, moduleID FROM questions_modules, modules WHERE q_id = ? AND questions_modules.idMod = modules.id');
        $stmt->bind_param('i', $q_id);
        $stmt->execute();
        $stmt->bind_result($idMod, $moduleID);
        while ($res = $stmt->fetch()) {
            $modules[$idMod] = $moduleID;
        }
        $stmt->close();

        return $modules;
    }

    /**
     * Update the modules for a question bast on the modules that the papers it is part of are on
     * @param integer $q_id the id of the questions.
     * @param resource $db the database connection.
     * @return void
     */
    public static function update_modules_from_papers($q_id, $db)
    {
        $sql = <<<SQL
      SELECT DISTINCT idMod
      FROM papers, properties, properties_modules
      WHERE properties.property_id = properties_modules.property_id
      AND properties.property_id = paper
      AND question = ?
      AND deleted is NULL
SQL;
        $update = $db->prepare($sql);
        $update->bind_param('i', $q_id);
        $update->execute();
        $update->bind_result($tmp_idMod);
        $on_idMod = array();
        while ($update->fetch()) {
            $on_idMod[$tmp_idMod] = $tmp_idMod;
        }
        $update->close();

        // Questions may be on modules the current users is not in - should we exclude these from the delete
        $update = $db->prepare('DELETE FROM questions_modules WHERE q_id = ?');
        $update->bind_param('i', $q_id);
        $update->execute();
        $update->close();

        QuestionUtils::add_modules($on_idMod, $q_id, $db);
    }

    /**
     * Updates the modules on a question removes modules if the user has permission to do so and then adds in the new modules
     * @param $modules an array of modules keyed on idMod
     * @param $q_id the id of the question
     * @param resource $db the database connection.
     * @param object $userObj the currently authenticated user object.
     * @return void
     */
    public static function update_modules($modules, $q_id, $db, $userObj)
    {
        $user_can_delete = '';
        if (!$userObj->has_role('SysAdmin')) {    // If SysAdmin no restrictions in deleting.
            $staff_modules = $userObj->get_staff_modules();
            if (count($staff_modules) > 0) {
                $user_can_delete = 'AND idMod IN (' . implode(',', array_keys($staff_modules)) . ')'; //users can only remove modules if they are on the team
            }
        }

        $editProperties = $db->prepare("DELETE FROM questions_modules WHERE q_id = ? $user_can_delete");
        $editProperties->bind_param('i', $q_id);
        $editProperties->execute();
        $editProperties->close();

        QuestionUtils::add_modules($modules, $q_id, $db);
    }

    /**
     * Add modules to a question ignoring any duplicates
     * @param $modules an array of modules keyed on idMod
     * @param $q_id the id of the question
     * @param resource $db the database connection.
     * @return void
     */
    public static function add_modules($modules, $q_id, $db)
    {
        $update = $db->prepare('INSERT INTO questions_modules VALUES(?, ?) ON DUPLICATE KEY UPDATE idMod = idMod');
        foreach ($modules as $idMod => $ModuleID) {
            $update->bind_param('ii', $q_id, $idMod);
            $update->execute();
        }
        $update->close();
    }

    /**
     * add keywords to a question
     * @param $keywords an array of keywords keyed on IDs
     * @param $q_id the id of the question
     * @param resource $db the database connection.
     * @return void
     */
    public static function add_keywords($keywords, $q_id, $db)
    {
        $update = $db->prepare('INSERT INTO keywords_question VALUES (?, ?)');
        foreach ($keywords as $keywordID => $keyword) {
            $update->bind_param('ii', $q_id, $keywordID);
            $update->execute();
        }
        $update->close();
    }

    /**
     * remove a module from a question
     * @param $idMod an array of modules to remove keyed on idMod
     * @param $q_id the id of the question or property_id
     * @param resource $db the database connection.
     * @return void
     */
    public static function remove_modules($modules, $q_id, $db)
    {
        $update = $db->prepare('DELETE FROM questions_modules WHERE q_id = ? AND idMod = ?');
        foreach ($modules as $idMod => $ModuleID) {
            $update->bind_param('ii', $q_id, $idMod);
            $update->execute();
        }
        $update->close();
    }

    /**
     * remove a question from rogo
     * Normal Questions - sets the deleted field we don't actuality delete the row form the questions table
     * Random Questions - deletes the rows in random_link to ensure random questions cannot use the deleted question
     * @param $q_id the id of the question or property_id
     * @param resource $db the database connection.
     * @return void
     */
    public static function delete_question($q_id, $db)
    {
        $delete = $db->prepare('UPDATE questions SET deleted = NOW() WHERE q_id = ?');
        $delete->bind_param('i', $q_id);
        $delete->execute();
        $delete->close();

        $delete_random = $db->prepare('DELETE FROM random_link where q_id = ?');
        $delete_random->bind_param('i', $q_id);
        $delete_random->execute();
        $delete_random->close();
    }

    public static function lock_question($q_id, $db)
    {
        $lock = $db->prepare('UPDATE questions SET locked = NOW() WHERE q_id = ? AND locked IS NULL');
        $lock->bind_param('i', $q_id);
        $lock->execute();
        $lock->close();
    }

    /**
     * Unlock a question
     * @param integer $q_id question id
     * @param mysqli $db database connection
     */
    public static function unlock_question($q_id, $db)
    {
        $lock = $db->prepare('UPDATE questions SET locked = NULL WHERE q_id = ?');
        $lock->bind_param('i', $q_id);
        $lock->execute();
        $lock->close();
    }

    /**
     * Check if a question has been answered in a summative exam by a student
     * @param integer $q_id question id
     * @param mysqli $db database connection
     * @return bool true if answered, false otherwise
     */
    public static function question_answered_in_summative($q_id, $db)
    {
        $result = $db->prepare("SELECT TRUE FROM log2 l, log_metadata m, users u,
        user_roles ur JOIN roles r ON ur.roleid = r.id
        WHERE l.metadataID = m.id AND m.userID = u.id AND l.q_id = ? AND 
        r.name = 'Student' AND u.id = ur.userid
        LIMIT 1");
        $result->bind_param('i', $q_id);
        $result->execute();
        $result->bind_result($hasrow);
        $result->fetch();
        if ($hasrow) {
            $result->close();
            return true;
        }
        $result->close();
        return false;
    }

    /**
     * Get the number of questions assigned to a given status
     * @param  integer $status_id Status ID
     * @param  mysqli $db        DB link
     * @return integer           Number of questions assigned to the status
     */
    public static function get_question_count_by_status($status_id, $db)
    {
        $query = $db->prepare('SELECT count(q_id) FROM questions WHERE status = ?');
        $query->bind_param('i', $status_id);
        $query->execute();
        $query->bind_result($count);
        $query->fetch();
        $query->close();

        return $count;
    }

    /**
     * Function to get available options text for question
     *
     * @param int $qid question identifier
     * @param mysqli $db
     * @return array option_text for supplied option
     */
    public static function get_options_text($qid, $db)
    {
        $options = $db->prepare('SELECT option_text FROM options WHERE o_id = ?');
        $options->bind_param('i', $qid);
        $options->execute();
        $options->store_result();
        $options->bind_result($optionstext);
        $optionsarray = array();
        while ($options->fetch()) {
            $optionsarray[] = $optionstext;
        }
        $options->close();
        return $optionsarray;
    }

    /**
     * Function to get type of question
     *
     * @param int $qid question identifier
     * @param mysqli $db
     * @return string question type
     */
    public static function get_question_type($qid, $db)
    {
        $type = $db->prepare('SELECT q_type FROM questions WHERE q_id = ?');
        $type->bind_param('i', $qid);
        $type->execute();
        $type->bind_result($qtype);
        $type->fetch();
        $type->close();
        return $qtype;
    }

    /**
     * Based on the parent random block id get the possible questions based on type.
     *
     * @param int $q_id question
     * @param string $q_type question type
     * @return array $possible list of possible questions
     */
    public static function get_random_question($q_id, $q_type)
    {
        $configObject = Config::get_instance();
        $possible = array();
        $random = $configObject->db->prepare('SELECT q.q_id FROM questions q, random_link r WHERE q.q_id = r.q_id AND q_type = ? and r.id = ?');
        $random->bind_param('is', $q_id, $q_type);
        $random->execute();
        $random->bind_result($random_id);
        while ($random->fetch()) {
            $possible[] = $random_id;
        }
        $random->close();
        return $possible;
    }

    /**
     * Based on the parent keyword block id get the possible questions based on type.
     *
     * @param int $q_id question
     * @param string $q_type question type
     * @return array $possible list of possible questions
     */
    public static function get_keyword_question($q_id, $q_type)
    {
        $configObject = Config::get_instance();
        $possible = array();
        $random = $configObject->db->prepare('SELECT q_id FROM questions WHERE q_type = ? AND q_id in ('
            . 'SELECT keywords_question.q_id FROM keywords_question, keywords_link WHERE keywordID = keyword_id AND keywords_link.q_id = ?)');
        $random->bind_param('is', $q_id, $q_type);
        $random->execute();
        $random->bind_result($random_id);
        while ($random->fetch()) {
            $possible[] = $random_id;
        }
        $random->close();
        return $possible;
    }

    /**
     * Is the question in a random block
     * @param int $q_id question
     * @param mysqli $db
     * @return array the random blocks the question appears in
     */
    public static function is_in_random_block($q_id, $db)
    {
        $questions = array();
        // We are checking the question is on a paper in order to display the list of papers to the end user.
        $query = $db->prepare('SELECT question FROM questions, random_link, papers WHERE question = questions.q_id AND '
            . "questions.q_id = random_link.id AND q_type ='random' AND random_link.q_id = ?");
        $query->bind_param('i', $q_id);
        $query->execute();
        $query->bind_result($question);
        while ($query->fetch()) {
            $questions[] = $question;
        }
        $query->close();
        return $questions;
    }

    /**
     * Is the question in a keyword block
     * @param int $q_id question
     * @param mysqli $db
     * @return array the keyword blocks the question appears in
     */
    public static function is_in_keyword_block($q_id, $db)
    {
        $questions = array();
        // We are checking the question is on a paper in order to display the list of papers to the end user.
        $query = $db->prepare('SELECT question FROM keywords_question, keywords_link, papers WHERE question = keywords_link.q_id AND '
            . 'keywordID = keyword_id AND keywords_question.q_id = ?');
        $query->bind_param('i', $q_id);
        $query->execute();
        $query->bind_result($question);
        while ($query->fetch()) {
            $questions[] = $question;
        }
        $query->close();
        return $questions;
    }

    /**
     * Get settings for question
     * @param integer $qid
     * @return string
     */
    public static function get_settings($qid)
    {
        $configObject = Config::get_instance();
        $result = $configObject->db->prepare('SELECT settings FROM questions WHERE q_id = ?');
        $result->bind_param('i', $qid);
        $result->execute();
        $result->bind_result($settings);
        $result->fetch();
        $result->close();
        return $settings;
    }

    /**
     * Get correct answer for question
     * Used when question is a random block and method for getting correct answer varies.
     * @param array $question question data to be updated
     * @param integer $id question id
     * @param mysqli $db db connection
     * @return mixed
     */
    public static function get_correct_answer($question, $id, $db)
    {
        $result = $db->prepare('SELECT q_id, q_type, correct, option_text, score_method FROM questions LEFT JOIN options ON questions.q_id = options.o_id  WHERE questions.q_id = ? ORDER BY id_num');
        $result->bind_param('i', $id);
        $result->execute();
        $result->store_result();
        $result->bind_result($q_id, $q_type, $correct, $option_text, $score_method);
        $question['correct'] = '';
        $question['correct_text'] = '';
        while ($result->fetch()) {
            $question['ID'] = $q_id;
            $question['type'] = $q_type;
            $question['score_method'] = $score_method;
            $question['correct'] = self::fix_correct($q_type, $correct, $question['correct'], $option_text);
            $question['option_text'] = $option_text;
            $question['correct_text'] .= "\t" . $option_text;
        }
        $result->close();
        return $question;
    }

    /**
     * Some question type store the correct answer oddly so lets find it
     * @param string $q_type question type
     * @param string $correct question.correct
     * @param string $old_correct what was supplied as correct
     * @param string $option_text option.option_text
     * @return string
     */
    public static function fix_correct($q_type, $correct, $old_correct, $option_text)
    {
        if ($q_type === 'blank') {
            // Fill in the blank questions only ever have one entry in the option table,
            // the blanks that need to be filled in are stored in the option_text field of the table.
            $old_correct = '';
            // All of the areas a student needs to fill in are surrounded by [blank][/blank]
            // with each option displayed to a student as a comma separated list.
            $split1 = explode('[blank', $option_text);
            for ($i = 1; $i < count($split1); $i++) {
                // The first entry in the comma separated list is the correct answer.
                $split2 = explode(',', mb_substr($split1[$i], 1, mb_strpos($split1[$i], '[/blank]') - 1));
                $old_correct .= ',' . $split2[0];
            }
        } elseif ($q_type == 'mcq' or $q_type == 'enhancedcalc') {
            $old_correct = ',' . $correct;
        } elseif ($q_type != 'extmatch' and $q_type != 'matrix') {
            $old_correct .= ',' . $correct;
        } else {
            $old_correct = ',' . str_replace('|', ',', $correct);
            // If there is a comma at the end remove it.
            if (mb_substr($old_correct, -1, 1) == ',') {
                $old_correct = mb_substr($old_correct, 0, mb_strlen($old_correct) - 1);
            }
        }

        return $old_correct;
    }

    /**
     * Check one scenario against another for similarity. Hardcoded to above 95% at present.
     * @param string $scenario1 First scenario to compare
     * @param string $scenario2 Second scenario to compare
     *
     * @return bool
     */
    public static function is_scenario_similar(string $scenario1, string $scenario2)
    {
        similar_text(strip_tags($scenario1), strip_tags($scenario2), $similarity);
        if ($similarity > 95) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the question media as an array of MediaObjects
     * @param integer $qid question id
     * @return array
     */
    public static function getMedia(int $qid): array
    {
        $configObject = Config::get_instance();
        $mediasql = $configObject->db->prepare('SELECT id, source, width, height, alt, ownerid, num
            FROM media, questions_media 
            WHERE media.id = questions_media.mediaid
            AND questions_media.qid = ?
            ORDER BY num ASC');
        $mediasql->bind_param('i', $qid);
        $mediasql->execute();
        $mediasql->store_result();
        $mediasql->bind_result(
            $q_media_id,
            $q_media_source,
            $q_media_width,
            $q_media_height,
            $q_media_alt,
            $q_media_owner,
            $q_media_num
        );
        $media = array();
        while ($mediasql->fetch()) {
            $media[] = new \MediaObject(
                $q_media_id,
                $q_media_source,
                $q_media_width,
                $q_media_height,
                $q_media_alt,
                $q_media_owner,
                $q_media_num,
            );
        }
        $mediasql->free_result();
        $mediasql->close();
        return $media;
    }

    /**
     * Get the question media as an array of strings
     * @param integer $qid question id
     * @return array
     */
    public static function getMediaAsString(int $qid): array
    {
        $media = self::getMedia($qid);
        $mediaarray = array(
            'source' => [],
            'width' => [],
            'height' => [],
            'alt' => [],
            'num' => [],
            'owner' => [],
            'id' => [],
        );
        foreach ($media as $m) {
            $mediaarray['id'][] = $m->id;
            $mediaarray['source'][] = $m->source;
            $mediaarray['width'][] = $m->width;
            $mediaarray['height'][] = $m->height;
            $mediaarray['alt'][] = $m->alt;
            $mediaarray['owner'][] = $m->owner;
            $mediaarray['num'][] = $m->num;
        }
        $mediaarray['source'] = implode('|', $mediaarray['source']);
        $mediaarray['width'] = implode('|', $mediaarray['width']);
        $mediaarray['height'] = implode('|', $mediaarray['height']);
        $mediaarray['alt'] = implode('|', $mediaarray['alt']);
        $mediaarray['owner'] = implode('|', $mediaarray['owner']);
        $mediaarray['num'] = implode('|', $mediaarray['num']);
        $mediaarray['id'] = implode('|', $mediaarray['id']);
        return $mediaarray;
    }

    /**
     * Get the option media
     * @param integer $oid option id
     * @return MediaObject|bool
     */
    public static function getOptionMedia(int $oid)
    {
        $media = false;
        $configObject = Config::get_instance();
        $mediasql = $configObject->db->prepare(
            'SELECT id, source, width, height, alt, ownerid
            FROM media, options_media 
            WHERE media.id = options_media.mediaid
            AND options_media.oid = ?
            ORDER BY oid ASC'
        );
        $mediasql->bind_param('i', $oid);
        $mediasql->execute();
        $mediasql->store_result();
        $mediasql->bind_result(
            $o_media_id,
            $o_media_source,
            $o_media_width,
            $o_media_height,
            $o_media_alt,
            $o_media_owner
        );
        $rows = $mediasql->num_rows;
        if ($rows == 1) {
            $mediasql->fetch();
            $media = new \MediaObject(
                $o_media_id,
                $o_media_source,
                $o_media_width,
                $o_media_height,
                $o_media_alt,
                $o_media_owner,
                0
            );
        }
        $mediasql->free_result();
        $mediasql->close();
        return $media;
    }
}
