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

namespace testing\datagenerator;

use Exception;
use UserUtils;
use QuestionUtils;
use random_utils;

/**
 * Generates Rogo paper.
 *
 * @author Yijun Xue <yijun.xue@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class questions extends generator
{

    /**
     * Create a new question
     *  Since _fields_required had not been used in question creating process, required fields are hard coded in HTML in webpage....
     *  here have to use hard code sql to create question.
     *
     * @param array $data mandatory question data
     * @return array
     * @throws data_error If passed parameter is invalid
     * @throws no_database
     * @throws not_found
     */
    public function insert_question($data)
    {
        loader::get('papers');
        $username = $data['user'];
        $userid = UserUtils::username_exists($username, $this->db);

        $defaults = array(
            'q_type' => null,
            'theme' => '',
            'leadin' => 'test question leadin',
            'notes' => '',
            'display_method' => 'vertical',
            'ownerID' => $userid,
            'q_media_id' => -1,
            'q_media' => '',
            'q_media_width' => '',
            'q_media_height' => '',
            'q_media_alt' => '',
            'q_media_owner' => $userid,
            'q_media_num' => '',
            'creation_date' => date('Y-m-d H:i:s'),
            'last_edited' => date('Y-m-d H:i:s'),
            'bloom' => null,
            'scenario' => 'defult scenario',
            'scenario_plain' => 'defult scenario_plain',
            'leadin_plain' => '',
            'checkout_time' => null,
            'checkout_authorID' => '',
            'deleted' => false,
            'locked' => null,
            'std' => '',
            'status' => 1,
            'q_option_order' => 'display order',
            'score_method' => 'Mark per Option',
            'settings' => '',
            'guid' => uniqid(),
            'keywords' => '',
            'options' => '',
            'paper' => '',
        );
        $qdata = $this->set_defaults_and_clean($defaults, $data);
        $now = date('Y-m-d H:i:s');
        if ($qdata['deleted']) {
            $qdata['deleted'] = $now;
        } else {
            $qdata['deleted'] = null;
        }
        $sqlquery = '
            INSERT INTO questions (
                q_type, theme, scenario, scenario_plain, leadin, leadin_plain, notes,
                correct_fback, incorrect_fback, score_method, display_method, q_option_order,
                std, bloom, ownerID, checkout_time, checkout_authorID, creation_date, last_edited,
                locked, deleted, status, settings, guid)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ';
        try {
            $result = $this->db->prepare($sqlquery);
            $result->bind_param(
                'ssssssssssssssisisssssss',
                $qdata['q_type'],
                $qdata['theme'],
                $qdata['scenario'],
                $qdata['scenario_plain'],
                $qdata['leadin'],
                $qdata['leadin_plain'],
                $qdata['notes'],
                $qdata['correct_fback'],
                $qdata['incorrect_fback'],
                $qdata['score_method'],
                $qdata['display_method'],
                $qdata['q_option_order'],
                $qdata['std'],
                $qdata['bloom'],
                $qdata['ownerID'],
                $qdata['checkout_time'],
                $qdata['checkout_authorID'],
                $qdata['creation_date'],
                $qdata['last_edited'],
                $qdata['locked'],
                $qdata['deleted'],
                $qdata['status'],
                $qdata['settings'],
                $qdata['guid']
            );
            $result->execute();
            $qdata['id'] = $this->db->insert_id;
            $result->close();
            if ($qdata['q_media'] != '') {
                $id = \media_handler::insertMedia(
                    $qdata['q_media'],
                    $qdata['q_media_width'],
                    $qdata['q_media_height'],
                    $qdata['q_media_alt'],
                    $qdata['q_media_owner']
                );
                $qdata['q_media_id'] = $id;
                $qdata['q_media_num'] = 0;
                if ($id !== -1) {
                    \media_handler::linkQuestionToMedia($qdata['q_media_id'], $qdata['id'], $qdata['q_media_num']);
                }
            }
            // Keywords may be provided as a json array.
            if (!empty($qdata['keywords'])) {
                $keywordparams['questionid'] = $qdata['id'];
                $keywordparams['keywords'] = json_decode($qdata['keywords']);
                $this->addKeywordsToQuestion($keywordparams);
            }
            // Options may be passed as a json array.
            if (!empty($qdata['options'])) {
                $decode = json_decode($qdata['options'], false);
                if (is_array($decode)) {
                    foreach ($decode as $opt) {
                        $opt->question = $qdata['id'];
                        $this->add_options_to_question((array) $opt);
                    }
                } else {
                    $decode->question = $qdata['id'];
                    $this->add_options_to_question((array) $decode);
                }
            }
            // Paper details may be provided as a json array.
            if (!empty($qdata['paper'])) {
                $paperparams = json_decode($qdata['paper'], true);
                $paperparams['question'] = $qdata['id'];
                $paperparams['paper'] = \PaperUtils::getPaperId($paperparams['paper']);
                $this->add_question_to_paper($paperparams);
            }
            return $qdata;
        } catch (Exception $e) {
            echo 'Error No: ' . $e->getCode() . ' - ' . $e->getMessage() . '<br />';
            echo nl2br($e->getTraceAsString());
            throw new data_error('MySQL error ' . $this->_mysqli->error . "<br /> Query:<br /> $sqlquery", $this->_mysqli->errno);
        }
    }

    /**
     * Create a new question
     *
     * @parm array $parameters
     *  string parameters[paperowner]
     *  string parameters[type]
     * @throws data_error If passed parameter is invalid
     * @throws no_database
     * @throws not_found
     * @return array
     */
    public function create_question($parameters)
    {

        $types = \QuestionEdit::$types;
        // Basic check mandatory parameters for creating question.
        if (empty($parameters['type']) or (!in_array($parameters['type'], $types)) or empty($parameters['user']) or empty($parameters['leadin'])) {
            throw new data_error('Must pass list of question type and title ');
        } else {
            $parameters['q_type'] = $parameters['type'];
            unset($parameters['type']);
            $parameters['leadin_plain'] = strip_tags($parameters['leadin']);
            if (!empty($parameters['scenario'])) {
                $parameters['scenario_plain'] = strip_tags($parameters['scenario']);
            }
            return $this->insert_question($parameters);
        }
    }

    /**
     * Add a question to a paper
     * @param array parameters
     *  string parameters[paper]
     *  string parameters[question]
     *  string parameters[screen]
     *  string parameters[displaypos]
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function add_question_to_paper($parameters)
    {
        if (empty($parameters['paper'])) {
            throw new data_error('paper must be provided');
        }
        if (empty($parameters['question'])) {
            throw new data_error('question must be provided');
        }
        if (empty($parameters['screen'])) {
            throw new data_error('screen must be provided');
        }
        if (empty($parameters['displaypos'])) {
            throw new data_error('display position must be provided');
        }
        \Paper_utils::add_question($parameters['paper'], $parameters['question'], $parameters['screen'], $parameters['displaypos'], $this->db);
        return $parameters;
    }

    /**
     * Add options to question
     * @param array parameters
     *  integer parameters[question]
     *  options are (option_text, o_media, o_media_width, o_media_width, feedback_right, feedback_wrong, correct, marks_correct, marks_incorrect, marks_partial)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function add_options_to_question($parameters)
    {
        if (empty($parameters['question'])) {
            throw new data_error('question must be provided');
        }
        $defaults = array(
            'option_text' => null,
            'o_media_id' => -1,
            'o_media' => '',
            'o_media_width' => '',
            'o_media_height' => '',
            'o_media_alt' => '',
            'o_media_owner' => QuestionUtils::get_ownerID($parameters['question'], $this->db),
            'feedback_right' => null,
            'feedback_wrong' => null,
            'correct' => null,
            'marks_correct' => null,
            'marks_incorrect' => null,
            'marks_partial' => null,
        );
        $settings = $this->set_defaults_and_clean($defaults, $parameters);

        $result = $this->db->prepare('
            INSERT INTO options (
                o_id,
                option_text,
                feedback_right,
                feedback_wrong,
                correct,
                marks_correct,
                marks_incorrect,
                marks_partial
                ) 
                VALUE
                (?, ?, ?, ?, ?, ?, ?, ?)
            ');
        $result->bind_param(
            'issssddd',
            $parameters['question'],
            $settings['option_text'],
            $settings['feedback_right'],
            $settings['feedback_wrong'],
            $settings['correct'],
            $settings['marks_correct'],
            $settings['marks_incorrect'],
            $settings['marks_partial']
        );
        $result->execute();
        $settings['id_num'] = $this->db->insert_id;
        $result->close();
        if ($settings['o_media'] != '') {
            $id = \media_handler::insertMedia(
                $settings['o_media'],
                $settings['o_media_width'],
                $settings['o_media_height'],
                $settings['o_media_alt'],
                $settings['o_media_owner'],
            );
            $settings['o_media_id'] = $id;
            if ($id !== -1) {
                \media_handler::linkOptionToMedia(
                    $settings['o_media_id'],
                    $settings['id_num'],
                );
            }
        }
        $settings['question'] = $parameters['question'];
        return $settings;
    }

    /**
     * Add a question to a module
     * @param array parameters
     *  string parameters[module]
     *  string parameters[question]
     * @throws data_error If passed parameter is invalid
     */
    public function add_to_module($parameters)
    {
        if (empty($parameters['question'])) {
            throw new data_error('question must be provided');
        }
        if (empty($parameters['module'])) {
            throw new data_error('module must be provided');
        }
        if (is_array($parameters['module'])) {
            $modules = $parameters['module'];
        } else {
            $modules = array($parameters['module']);
        }
        QuestionUtils::add_modules($modules, $parameters['question'], $this->db);
    }

    /**
     * Add a question to a random block
     * @param array parameters
     *  string parameters[block]
     *  string parameters[question]
     * @throws data_error If passed parameter is invalid
     */
    public function add_to_random_block($parameters)
    {
        if (empty($parameters['question'])) {
            throw new data_error('question must be provided');
        }
        if (empty($parameters['block'])) {
            throw new data_error('block must be provided');
        }

        if (!random_utils::insert_random_link($parameters['block'], $parameters['question'], $this->db)) {
            throw new data_error('question ' . $parameters['question'] . ' not inserted into random block');
        }
    }

    /**
     * Add keywords to question
     * @param array parameters
     *  int parameters[id] the question id
     *  array parameters[keywords] keywords
     * @throws data_error If passed parameter is invalid
     */
    public function addKeywordsToQuestion(array $parameters): void
    {
        $questionid = $parameters['questionid'];
        $keywords = $parameters['keywords'];

        if (empty($questionid)) {
            throw new data_error('Adding keywords failed as question (id ' . $questionid . ') does not exit');
        }

        $keywordsstring = implode('","', $keywords);
        $keywordsstring = '"' . $keywordsstring . '"';
        $keywordarray = array();

        $sql = 'SELECT id FROM keywords_user WHERE keyword IN (' . $keywordsstring . ')';
        $result = $this->db->prepare($sql);
        $result->execute();
        $result->bind_result($keywordid);
        while ($result->fetch()) {
            $keywordarray[] = $keywordid;
        }
        $result->close();

        QuestionUtils::add_keywords($keywordarray, $questionid, $this->db);
    }
}
