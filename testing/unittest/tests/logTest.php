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

use testing\unittest\unittestdatabase;
use users\PaperList;

/**
 * Test assessment class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class logtest extends unittestdatabase
{
    /**
     * @var integer Storage for user id in tests
     */
    private $user;

    /**
     * @var array Storage for user data in tests
     */
    private $user2;

    /** @var array Storage for paper data in tests. */
    private $pid;

    /** @var array Storage for paper data in tests. */
    private $pid2;

    /** @var array Storage for paper data in tests. */
    private $pid3;

    /** @var array Storage for question data in tests. */
    private $question;

    /** @var array Storage for question data in tests. */
    private $question2;

    /** @var array Storage for meta log data in tests. */
    private $meta;

    /** @var array Storage for meta log data in tests. */
    private $meta2;

    /** @var array Storage for meta log data in tests. */
    private $meta3;

    /** @var array Storage for meta log data in tests. */
    private $meta4;

    /** @var array Storage for meta log data in tests. */
    private $meta5;

    /** @var array Storage for meta log data in tests. */
    private $meta6;

    /** @var array Storage for meta log data in tests. */
    private $meta7;

    /** @var array Storage for meta log data in tests. */
    private $meta8;

    /** @var array Storage for meta log data in tests. */
    private $log1;

    /** @var array Storage for meta log data in tests. */
    private $log2;

    /** @var array Storage for meta log data in tests. */
    private $log3;

    /** @var array Storage for meta log data in tests. */
    private $log4;

    /** @var array Storage for meta log data in tests. */
    private $log5;

    /** @var array Storage for meta log data in tests. */
    private $log6;

    /** @var array Storage for meta log data in tests. */
    private $log7;

    /** @var array Storage for meta log data in tests. */
    private $log8;

    /** @var array Storage for meta log data in tests. */
    private $log9;

    /** @var array Storage for meta log data in tests. */
    private $log10;

    /** @var array Storage for meta log data in tests. */
    private $log11;

    /** @var array Storage for meta log data in tests. */
    private $log12;

    /** @var array Storage for meta log data in tests. */
    private $log13;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $this->user = $this->get_user_id('test2');
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2018, 'academic_year' => '2018/19'));
        $datagenerator = $this->get_datagenerator('users', 'core');
        $this->user2 = $datagenerator->create_user(array('surname' => 'zstaff', 'username' => 'staff1', 'grade' => 'University Lecturer', 'initials' => 'a',
            'title' => 'Dr', 'email' => 'staff1@example.com', 'gender' => 'Male', 'first_names' => 'a', 'yearofstudy' => 1, 'roles' => 'Staff'));
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $this->pid = $datagenerator->create_paper(array('papertitle' => 'Paper 1',
            'calendaryear' => 2018,
            'paperowner' => 'admin',
            'papertype' => '3',
            'modulename' => 'Training Module'));
        $logdatagenerator = $this->get_datagenerator('log', 'core');
        $this->pid2 = $datagenerator->create_paper(array('papertitle' => 'Paper 2',
            'calendaryear' => 2018,
            'paperowner' => 'admin',
            'papertype' => '2',
            'modulename' => 'Training Module'));
        $this->pid3 = $datagenerator->create_paper(array('papertitle' => 'Paper 3',
            'calendaryear' => 2018,
            'paperowner' => 'admin',
            'papertype' => '0',
            'modulename' => 'Training Module'));
        $datagenerator = $this->get_datagenerator('questions', 'core');
        $this->question = $datagenerator->create_question(array('type' => 'mcq',
            'user' => 'admin',
            'status' => 1,
            'theme' => 'test theme',
            'scenario' => 'test scenario',
            'leadin' => 'test leadin',
            'notes' => 'test notes',
            'q_media' => '1517406311.png',
            'q_media_width' => 480,
            'q_media_height' => 105,
            'q_option_order' => 'random',
            'display_method' => 'vertical',
            'score_method' => 'Mark per Option'));
        $datagenerator->add_options_to_question(array('question' => $this->question['id'],
            'option_text' => 'true',
            'correct' => 1,
            'o_media' => '1517409282.jpg',
            'o_media_width' => 951,
            'o_media_height' => 121,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $datagenerator->add_options_to_question(array('question' => $this->question['id'],
            'option_text' => 'false',
            'correct' => 1,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $datagenerator->add_options_to_question(array('question' => $this->question['id'],
            'option_text' => 'maybe',
            'correct' => 1,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $this->question2 = $datagenerator->create_question(array('type' => 'mcq',
            'user' => 'admin',
            'status' => 1,
            'theme' => 'test theme',
            'scenario' => 'test scenario',
            'leadin' => 'test leadin',
            'notes' => 'test notes',
            'q_media' => '1517406311.png',
            'q_media_width' => 480,
            'q_media_height' => 105,
            'q_option_order' => 'random',
            'display_method' => 'vertical',
            'score_method' => 'Mark per Option'));
        $datagenerator->add_options_to_question(array('question' => $this->question2['id'],
            'option_text' => 'true',
            'correct' => 1,
            'o_media' => '1517409282.jpg',
            'o_media_width' => 951,
            'o_media_height' => 121,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $datagenerator->add_options_to_question(array('question' => $this->question2['id'],
            'option_text' => 'false',
            'correct' => 1,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $datagenerator->add_options_to_question(array('question' => $this->question2['id'],
            'option_text' => 'maybe',
            'correct' => 1,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $this->meta = $logdatagenerator->create_metadata(array('userID' => $this->student['id'], 'paperID' => $this->pid['id'], 'started' => '2018-01-01 00:00:00'));
        $this->meta2 = $logdatagenerator->create_metadata(array('userID' => $this->student['id'], 'paperID' => $this->pid2['id'], 'started' => '2018-01-01 00:00:00', 'year' => 2));
        $this->meta3 = $logdatagenerator->create_metadata(array('userID' => $this->user2['id'], 'paperID' => $this->pid2['id'], 'started' => '2018-01-04 00:00:00', 'year' => 1));
        $this->meta4 = $logdatagenerator->create_metadata(array('userID' => $this->student['id'], 'paperID' => $this->pid3['id'], 'started' => '2018-01-01 00:00:00', 'year' => 2));
        $this->meta5 = $logdatagenerator->create_metadata(array('userID' => $this->student['id'], 'paperID' => $this->pid3['id'], 'started' => '2017-01-01 00:00:00', 'year' => 2));
        $this->meta6 = $logdatagenerator->create_metadata(array('userID' => $this->user2['id'], 'paperID' => $this->pid3['id'], 'started' => '2016-01-01 00:00:00', 'year' => 1));
        $this->meta7 = $logdatagenerator->create_metadata(array('userID' => $this->user2['id'], 'paperID' => $this->pid3['id'], 'started' => '2016-01-01 00:00:00', 'year' => 1));
        $this->meta8 = $logdatagenerator->create_metadata(array('userID' => $this->user2['id'], 'paperID' => $this->pid3['id'], 'started' => '2016-01-01 00:00:00', 'year' => 1));
        $this->log1 = $logdatagenerator->create_late(array('metadataID' => $this->meta['id'], 'screen' => 1, 'q_id' => $this->question['id'], 'user_answer' => '2', 'duration' => 10, 'dismiss' => '1000', 'option_order' => '0,1,2,3'));
        $this->log2 = $logdatagenerator->create_late(array('metadataID' => $this->meta8['id'], 'screen' => 2, 'q_id' => $this->question2['id'], 'user_answer' => '2', 'duration' => 10, 'dismiss' => '1000', 'option_order' => '0,1,2,3'));
        $this->log3 = $logdatagenerator->create_summative(array('q_id' => $this->question['id'], 'metadataID' => $this->meta['id'], 'screen' => 1, 'user_answer' => '1', 'duration' => 5, 'dismiss' => '0000', 'option_order' => '0,1,2,3', 'mark' => 1));
        $this->log4 = $logdatagenerator->create_summative(array('q_id' => $this->question['id'], 'metadataID' => $this->meta2['id'], 'screen' => 1, 'user_answer' => '1', 'duration' => 5, 'dismiss' => '0000', 'option_order' => '0,1,2,3', 'mark' => 1));
        $this->log5 =  $logdatagenerator->create_summative(array('q_id' => $this->question2['id'], 'metadataID' => $this->meta2['id'], 'screen' => 2, 'user_answer' => '1', 'duration' => 5, 'dismiss' => '0000', 'option_order' => '0,1,2,3', 'mark' => 2));
        $this->log6 = $logdatagenerator->create_summative(array('q_id' => $this->question['id'], 'metadataID' => $this->meta3['id'], 'screen' => 1, 'user_answer' => '1', 'duration' => 5, 'dismiss' => '0000', 'option_order' => '0,1,2,3', 'mark' => 1));
        $this->log7 = $logdatagenerator->create_summative(array('q_id' => $this->question2['id'], 'metadataID' => $this->meta3['id'], 'screen' => 2, 'user_answer' => '0', 'duration' => 5, 'dismiss' => '0000', 'option_order' => '0,1,2,3', 'mark' => 0));
        $this->log8 = $logdatagenerator->create_summative(array('q_id' => $this->question['id'], 'metadataID' => $this->meta8['id'], 'screen' => 1, 'user_answer' => '1', 'duration' => 5, 'dismiss' => '0000', 'option_order' => '0,1,3,2', 'mark' => 1));
        $this->log9 = $logdatagenerator->create_progress(array('metadataID' => $this->meta5['id'], 'screen' => 1, 'q_id' => $this->question['id'], 'user_answer' => '1', 'duration' => 5, 'dismiss' => '0000', 'option_order' => '0,1,2,3', 'mark' => 1));
        $this->log10 = $logdatagenerator->create_progress(array('metadataID' => $this->meta6['id'], 'screen' => 1, 'q_id' => $this->question['id'], 'user_answer' => '3', 'duration' => 5, 'dismiss' => '0000', 'option_order' => '0,1,2,3', 'mark' => 0));
        $this->log11 = $logdatagenerator->create_formative(array('metadataID' => $this->meta2['id'], 'screen' => 2, 'q_id' => $this->question['id'], 'user_answer' => '1', 'duration' => 5, 'dismiss' => '0000', 'option_order' => '0,1,2,3'));
        $this->log12 = $logdatagenerator->create_formative(array('metadataID' => $this->meta4['id'], 'screen' => 1, 'q_id' => $this->question['id'], 'user_answer' => '1', 'duration' => 5, 'dismiss' => '0000', 'option_order' => '0,1,2,3', 'mark' => 4));
        $this->log13 = $logdatagenerator->create_formative(array('metadataID' => $this->meta7['id'], 'screen' => 1, 'q_id' => $this->question['id'], 'user_answer' => '4', 'duration' => 5, 'dismiss' => '0000', 'option_order' => '0,1,2,3', 'mark' => 2));
    }

    /**
     * Test retrieving previous answers - with restart
     * @group log
     */
    public function test_get_previous_answers()
    {
        $papertype = '0';
        $log = \log::get_paperlog($papertype);
        $do_restart = true;
        $current_screen = 1;
        $previous = array('used_questions' => array($this->question['id'] => $this->question['id']),
            'user_answers' => array(2 => array($this->question['id'] => $this->log11['user_answer'])),
            'user_dismiss' => array(2 => array($this->question['id'] => $this->log11['dismiss'])),
            'user_order' => array(2 => array($this->question['id'] => $this->log11['option_order'])),
            'previous_duration' => $this->log11['duration'],
            'screen_pre_submitted' => $current_screen,
            'current_screen' => $this->log11['screen']);
        $this->assertEquals($previous, $log->get_previous_answers($this->meta2['id'], $do_restart, $current_screen));
    }

    /**
     * Test retrieving previous answers from log late
     * @group log
     */
    public function test_get_previous_answers_late()
    {
        $papertype = '2';
        $log = \log::get_paperlog($papertype);
        $do_restart = false;
        $current_screen = 1;
        $previous = array('used_questions' => array($this->question['id'] => $this->question['id']),
            'user_answers' => array(1 => array($this->question['id'] => $this->log1['user_answer'])),
            'user_dismiss' => array(1 => array($this->question['id'] => $this->log1['dismiss'])),
            'user_order' => array(1 => array($this->question['id'] => $this->log1['option_order'])),
            'previous_duration' => $this->log1['duration'],
            'screen_pre_submitted' => $current_screen,
            'current_screen' => $this->log1['screen']);
        $this->assertEquals($previous, $log->get_previous_answers($this->meta['id'], $do_restart, $current_screen, true));
    }

    /**
     * Test retrieving previous answers from log late - multiple questions
     * @group log
     */
    public function test_get_previous_answers_late_complex()
    {
        $papertype = '2';
        $log = \log::get_paperlog($papertype);
        $do_restart = false;
        $current_screen = 1;
        $previous = array('used_questions' => array($this->question2['id'] => $this->question2['id'], $this->question['id'] => $this->question['id']),
            'user_answers' => array(1 => array($this->question['id'] => $this->log8['user_answer']), 2 => array($this->question2['id'] => $this->log2['user_answer'])),
            'user_dismiss' => array(1 => array($this->question['id'] => $this->log8['dismiss']), 2 => array($this->question2['id'] => $this->log2['dismiss'])),
            'user_order' => array(1 => array($this->question['id'] => $this->log8['option_order']), 2 => array($this->question2['id'] => $this->log2['option_order'])),
            'previous_duration' => $this->log8['duration'],
            'screen_pre_submitted' => $this->log8['screen'],
            'current_screen' => $current_screen);
        $this->assertEquals($previous, $log->get_previous_answers($this->meta8['id'], $do_restart, $current_screen, true));
    }

    /**
     * Test retrieving users in log for survey (where it is not supported)
     * @group log
     */
    public function test_get_log_users_survey()
    {
        $papertype = '3';
        $log = \log::get_paperlog($papertype);
        $this->assertEquals(array(), $log->get_log_users($this->pid['id'], '', '', array(), false));
    }

    /**
     * Test retrieving users in log for summative
     * @group log
     */
    public function test_get_log_users_summative()
    {
        $papertype = '2';
        $log = \log::get_paperlog($papertype);
        $expected[0]['userid'] = $this->user2['id'];
        $expected[0]['totalmark'] = 1.0;
        $expected[1]['userid'] = $this->student['id'];
        $expected[1]['totalmark'] = 3.0;
        // All users.
        $this->assertEquals($expected, $log->get_log_users($this->pid2['id'], '2018-01-01 00:00:00', '2018-01-05 01:00:00', array($this->student['id'], $this->user2['id']), false));
        // Students only.
        $expected = array();
        $expected[0]['userid'] = $this->student['id'];
        $expected[0]['totalmark'] = 3.0;
        $this->assertEquals($expected, $log->get_log_users($this->pid2['id'], '2018-01-01 00:00:00', '2018-01-05 01:00:00', array($this->student['id'], $this->user2['id']), true));
        // All users in timeframe
        $this->assertEquals($expected, $log->get_log_users($this->pid2['id'], '2018-01-01 00:00:00', '2018-01-01 01:00:00', array($this->student['id'], $this->user2['id']), false));
    }

    /**
     * Test retrieving users in log for formative
     * @group log
     */
    public function test_get_log_users_formative()
    {
        $papertype = '0';
        $log = \log::get_paperlog($papertype);
        $expected[0]['userid'] = $this->user2['id'];
        $expected[0]['totalmark'] = 0.0;
        $expected[1]['userid'] = $this->student['id'];
        $expected[1]['totalmark'] = 1.0;
        $expected[2]['userid'] = $this->user2['id'];
        $expected[2]['totalmark'] = 2.0;
        $expected[3]['userid'] = $this->student['id'];
        $expected[3]['totalmark'] = 4.0;
        // Progressive paper migrated to formative paper.
        // All users.
        $this->assertEquals($expected, $log->get_log_users($this->pid3['id'], '2016-01-01 00:00:00', '2018-01-05 01:00:00', array($this->student['id'], $this->user2['id']), false));
        // Students only.
        $expected = array();
        $expected[0]['userid'] = $this->student['id'];
        $expected[0]['totalmark'] = 1.0;
        $expected[1]['userid'] = $this->student['id'];
        $expected[1]['totalmark'] = 4.0;
        $this->assertEquals($expected, $log->get_log_users($this->pid3['id'], '2016-01-01 00:00:00', '2018-01-05 01:00:00', array($this->student['id'], $this->user2['id']), true));
        // All users in timeframe
        $this->assertEquals($expected, $log->get_log_users($this->pid3['id'], '2017-01-01 00:00:00', '2018-01-05 01:00:00', array($this->student['id'], $this->user2['id']), false));
    }

    /**
     * Test retrieving assessment data in log for survey (where it is not supported)
     * @group log
     */
    public function test_get_assessment_data_survey()
    {
        $papertype = '3';
        $log = \log::get_paperlog($papertype);
        $this->assertEquals(array(), $log->get_assessment_data($this->pid['id'], '', '', ''));
    }

    /**
     * Test retrieving assessment data in log for summative
     * @group log
     */
    public function test_get_assessment_data_summative()
    {
        $papertype = '2';
        $log = \log::get_paperlog($papertype);
        // Student only.
        $expected[0]['username'] = 'test1';
        $expected[0]['uID'] = $this->student['id'];
        $expected[0]['title'] = 'Dr';
        $expected[0]['surname'] = 'User1';
        $expected[0]['first_names'] = 'A';
        $expected[0]['grade'] = 'TEST2';
        $expected[0]['gender'] = null;
        $expected[0]['year'] = 2;
        $expected[0]['started'] = '2018-01-01 00:00:00';
        $expected[0]['question_ID'] = $this->question['id'];
        $expected[0]['user_answer'] = '1';
        $expected[0]['screen'] = 1;
        $expected[1]['username'] = 'test1';
        $expected[1]['uID'] = $this->student['id'];
        $expected[1]['title'] = 'Dr';
        $expected[1]['surname'] = 'User1';
        $expected[1]['first_names'] = 'A';
        $expected[1]['grade'] = 'TEST2';
        $expected[1]['gender'] = null;
        $expected[1]['year'] = 2;
        $expected[1]['started'] = '2018-01-01 00:00:00';
        $expected[1]['question_ID'] = $this->question2['id'];
        $expected[1]['user_answer'] = '1';
        $expected[1]['screen'] = 2;
        $users = array($this->student['id'], $this->user2['id']);
        $userstring = implode(',', $users);
        $this->assertEquals($expected, $log->get_assessment_data($this->pid2['id'], '2018-01-01 00:00:00', '2018-01-05 01:00:00', $userstring, '%', true));
        // All users in timeframe.
        $this->assertEquals($expected, $log->get_assessment_data($this->pid2['id'], '2018-01-01 00:00:00', '2018-01-01 01:00:00', $userstring));
        // All users in course.
        $this->assertEquals($expected, $log->get_assessment_data($this->pid2['id'], '2018-01-01 00:00:00', '2018-01-05 01:00:00', $userstring, 'TEST2', false));
        // All users.
        $expected[2]['username'] = $this->user2['username'];
        $expected[2]['uID'] = $this->user2['id'];
        $expected[2]['title'] = $this->user2['title'];
        $expected[2]['surname'] = $this->user2['surname'];
        $expected[2]['first_names'] = $this->user2['first_names'];
        $expected[2]['grade'] = $this->user2['grade'];
        $expected[2]['gender'] = $this->user2['gender'];
        $expected[2]['year'] = $this->user2['yearofstudy'];
        $expected[2]['started'] = '2018-01-04 00:00:00';
        $expected[2]['question_ID'] = $this->question['id'];
        $expected[2]['user_answer'] = '1';
        $expected[2]['screen'] = 1;
        $expected[3]['username'] = $this->user2['username'];
        $expected[3]['uID'] = $this->user2['id'];
        $expected[3]['title'] = $this->user2['title'];
        $expected[3]['surname'] = $this->user2['surname'];
        $expected[3]['first_names'] = $this->user2['first_names'];
        $expected[3]['grade'] = $this->user2['grade'];
        $expected[3]['gender'] = $this->user2['gender'];
        $expected[3]['year'] = $this->user2['yearofstudy'];
        $expected[3]['started'] = '2018-01-04 00:00:00';
        $expected[3]['question_ID'] = $this->question2['id'];
        $expected[3]['user_answer'] = '0';
        $expected[3]['screen'] = 2;
        $this->assertEquals($expected, $log->get_assessment_data($this->pid2['id'], '2018-01-01 00:00:00', '2018-01-05 01:00:00', $userstring));
    }

    /**
     * Test retrieving assessment data in log for formative
     * @group log
     */
    public function test_get_assessment_data_formative()
    {
        $papertype = '0';
        $log = \log::get_paperlog($papertype);
        // Student only.
        $expected[0]['username'] = 'test1';
        $expected[0]['uID'] = $this->student['id'];
        $expected[0]['title'] = 'Dr';
        $expected[0]['surname'] = 'User1';
        $expected[0]['first_names'] = 'A';
        $expected[0]['grade'] = 'TEST2';
        $expected[0]['gender'] = null;
        $expected[0]['year'] = 2;
        $expected[0]['started'] = '2017-01-01 00:00:00';
        $expected[0]['question_ID'] = $this->question['id'];
        $expected[0]['user_answer'] = '1';
        $expected[0]['screen'] = 1;
        $expected[1]['username'] = 'test1';
        $expected[1]['uID'] = $this->student['id'];
        $expected[1]['title'] = 'Dr';
        $expected[1]['surname'] = 'User1';
        $expected[1]['first_names'] = 'A';
        $expected[1]['grade'] = 'TEST2';
        $expected[1]['gender'] = null;
        $expected[1]['year'] = 2;
        $expected[1]['started'] = '2018-01-01 00:00:00';
        $expected[1]['question_ID'] = $this->question['id'];
        $expected[1]['user_answer'] = '1';
        $expected[1]['screen'] = 1;
        $users = array($this->student['id'], $this->user2['id']);
        $userstring = implode(',', $users);
        $this->assertEquals($expected, $log->get_assessment_data($this->pid3['id'], '2016-01-01 00:00:00', '2018-01-05 01:00:00', $userstring, '%', true));
        // All users in timeframe.
        $this->assertEquals($expected, $log->get_assessment_data($this->pid3['id'], '2017-01-01 00:00:00', '2018-01-05 01:00:00', $userstring));
        // All users in course.
        $this->assertEquals($expected, $log->get_assessment_data($this->pid3['id'], '2016-01-01 00:00:00', '2018-01-05 01:00:00', $userstring, 'TEST2', false));
        // All users.
        $expected[2]['username'] = $this->user2['username'];
        $expected[2]['uID'] = $this->user2['id'];
        $expected[2]['title'] = $this->user2['title'];
        $expected[2]['surname'] = $this->user2['surname'];
        $expected[2]['first_names'] = $this->user2['first_names'];
        $expected[2]['grade'] = $this->user2['grade'];
        $expected[2]['gender'] = $this->user2['gender'];
        $expected[2]['year'] = $this->user2['yearofstudy'];
        $expected[2]['started'] = $this->meta7['started'];
        $expected[2]['question_ID'] = $this->question['id'];
        $expected[2]['user_answer'] = $this->log13['user_answer'];
        $expected[2]['screen'] = $this->log13['screen'];
        $expected[3]['username'] = $this->user2['username'];
        $expected[3]['uID'] = $this->user2['id'];
        $expected[3]['title'] = $this->user2['title'];
        $expected[3]['surname'] = $this->user2['surname'];
        $expected[3]['first_names'] = $this->user2['first_names'];
        $expected[3]['grade'] = $this->user2['grade'];
        $expected[3]['gender'] = $this->user2['gender'];
        $expected[3]['year'] = $this->user2['yearofstudy'];
        $expected[3]['started'] = $this->meta6['started'];
        $expected[3]['question_ID'] = $this->question['id'];
        $expected[3]['user_answer'] = $this->log10['user_answer'];
        $expected[3]['screen'] = $this->log10['screen'];
        $this->assertEquals($expected, $log->get_assessment_data($this->pid3['id'], '2016-01-01 00:00:00', '2018-01-05 01:00:00', $userstring));
    }

    /**
     * Test previous assessment check
     * @group log
     */
    public function testHasPreviousAttempts()
    {
        // Has some previous attempts.
        $this->assertTrue(\log::hasPreviousAttempts($this->pid3['id'], $this->student['id']));
        // Does not have any previous attempts.
        $this->assertFalse(\log::hasPreviousAttempts($this->pid['id'], $this->user2['id']));
    }

    /**
     * Test load previous assessments - Does not have any previous attempts.
     * @group log
     */
    public function testLoadAttemptsNoAttempts()
    {
        $log = log::get_paperlog($this->pid['papertype']);
        $paperlist = new PaperList();
        $this->assertEquals(
            $paperlist,
            $log->loadAttempts(
                $this->pid['id'],
                $this->user2['id'],
                1,
                10,
                5
            )
        );
    }

    /**
     * Test load previous assessments - Has previous attempts - summative do not show percent.
     * @group log
     */
    public function testLoadAttemptsSummative()
    {
        $log = log::get_paperlog($this->pid2['papertype']);
        $paperlist = new PaperList();
        $paper = new \users\Paper();
        $date = date_create($this->meta2['started']);
        $paper->log_started = date_format($date, 'YmdHis');
        $paper->metadataID = $this->meta2['id'];
        $paper->max_screen = $this->log5['screen'];
        $paper->max_mark = round($this->log4['mark'] + $this->log5['mark'], 1);
        $paper->original_paper_type = $paper->paper_type = $this->pid2['papertype'];
        $paper->human_log_started = date_format($date, 'd/m/Y H:i');
        $paper->percent = '';
        $paperlist->add($paper);
        $this->assertEquals(
            $paperlist,
            $log->loadAttempts(
                $this->pid2['id'],
                $this->student['id'],
                1,
                10,
                5
            )
        );
    }

    /**
     * Test load previous assessments -  Has previous attempts - formative show percent
     * @group log
     */
    public function testLoadAttemptsFormative()
    {
        $log = log::get_paperlog($this->pid3['papertype']);
        $paperlist = new PaperList();
        $paper = new \users\Paper();
        $date = date_create($this->meta4['started']);
        $paper->log_started = date_format($date, 'YmdHis');
        $paper->metadataID = $this->meta4['id'];
        $paper->max_screen = $this->log12['screen'];
        $paper->max_mark = round($this->log12['mark'], 1);
        $paper->paper_type = $this->pid3['papertype'];
        $paper->human_log_started = date_format($date, 'd/m/Y H:i');
        $paper->percent = number_format(
            ($this->log12['mark']) / 10 * 100,
            1,
            '.',
            ','
        );
        $paper->percent .= '%';
        $paperlist->add($paper);
        $paper = new \users\Paper();
        $date = date_create($this->meta5['started']);
        $paper->log_started = date_format($date, 'YmdHis');
        $paper->metadataID = $this->meta5['id'];
        $paper->max_screen = $this->log9['screen'];
        $paper->max_mark = round($this->log9['mark'], 1);
        // Progress turned formative.
        $paper->paper_type = 0;
        $paper->original_paper_type = 1;
        $paper->human_log_started = date_format($date, 'd/m/Y H:i');
        $paper->percent = number_format(
            ($this->log9['mark'] / 10) * 100,
            1,
            '.',
            ','
        );
        $paper->percent .= '%';
        $paperlist->add($paper);
        $this->assertEquals(
            $paperlist,
            $log->loadAttempts(
                $this->pid3['id'],
                $this->student['id'],
                0,
                10,
                5
            )
        );
    }

    /**
     * Test load previous assessments - same as above with adjusted marking scheme
     * @group log
     */
    public function testLoadAttemptsFormativeAdjusted()
    {
        $log = log::get_paperlog($this->pid3['papertype']);
        $paperlist = new PaperList();
        $paper = new \users\Paper();
        $date = date_create($this->meta4['started']);
        $paper->log_started = date_format($date, 'YmdHis');
        $paper->metadataID = $this->meta4['id'];
        $paper->max_screen = $this->log12['screen'];
        $paper->max_mark = round($this->log12['mark'], 1);
        $paper->original_paper_type = $paper->paper_type = $this->pid3['papertype'];
        $paper->human_log_started = date_format($date, 'd/m/Y H:i');
        $adjusted = number_format(
            (($this->log12['mark'] - 5 ) / 5) * 100,
            1,
            '.',
            ','
        );
        if ($adjusted < 0) {
            $adjusted = 0;
        }
        $paper->percent = $adjusted . '%';
        $paperlist->add($paper);
        $paper = new \users\Paper();
        $date = date_create($this->meta5['started']);
        $paper->log_started = date_format($date, 'YmdHis');
        $paper->metadataID = $this->meta5['id'];
        $paper->max_screen = $this->log9['screen'];
        $paper->max_mark = round($this->log9['mark'], 1);
        // Progress turned formative.
        $paper->paper_type = 0;
        $paper->original_paper_type = 1;
        $paper->human_log_started = date_format($date, 'd/m/Y H:i');
        $adjusted = number_format(
            (($this->log9['mark'] - 5 ) / 5) * 100,
            1,
            '.',
            ','
        );
        if ($adjusted < 0) {
            $adjusted = 0;
        }
        $paper->percent = $adjusted . '%';
        $paperlist->add($paper);
        $this->assertEquals(
            $paperlist,
            $log->loadAttempts(
                $this->pid3['id'],
                $this->student['id'],
                1,
                10,
                5
            )
        );
    }
}
