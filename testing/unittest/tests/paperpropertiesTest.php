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

/**
 * Test paperproperties class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class PaperPropertiesTest extends unittestdatabase
{

    /**
     * @var array Storage for paper data in tests
     */
    private $pid1;

    /**
     * @var array Storage for paper data in tests
     */
    private $pid2;

    /**
     * @var array Storage for paper data in tests
     */
    private $pid3;

    /**
     * @var array Storage for paper data in tests
     */
    private $pid4;

    /*
     * @var array Storage for question data in tests
     */
    private $question;

    /*
     * @var array Storage for question data in tests
     */
    private $question2;

    /*
     * @var array Storage for question data in tests
     */
    private $question3;

    /*
     * @var array Storage for question data in tests
     */
    private $question4;

    /*
     * @var array Storage for question data in tests
     */
    private $question5;

    /*
     * @var array Storage for question data in tests
     */
    private $question6;

    /*
    * @var array Storage for question/paper data in tests
    */
    private $qpaper;

    /*
    * @var array Storage for question/paper data in tests
    */
    private $qpaper2;

    /*
     * @var array Storage for options data in tests
     */
    private $options;

    /*
     * @var array Storage for options data in tests
     */
    private $options2;

    /*
     * @var array Storage for options data in tests
     */
    private $options3;

    /*
     * @var array Storage for log data in tests
     */
    private $log;

    /*
     * @var array Storage for log data in tests
     */
    private $log2;

    /*
     * @var array Storage for log data in tests
     */
    private $log3;

    /*
     * @var array Storage for log data in tests
     */
    private $log4;

    /*
     * @var array Storage for log data in tests
     */
    private $log5;

    /*
     * @var array Storage for log data in tests
     */
    private $log6;

    /*
     * @var array Storage for log data in tests
     */
    private $log7;

    /*
     * @var array Storage for log data in tests
     */
    private $log8;

    /*
     * @var array Storage for log data in tests
     */
    private $log9;

    /*
     * @var array Storage for log data in tests
     */
    private $log10;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2015, 'academic_year' => '2015/16'));
        $datagenerator = $this->get_datagenerator('modules', 'core');
        $mod = $datagenerator->create_module(array('fullname' => 'Test module 3', 'moduleid' => 'TEST3', 'timed_exams' => 1));
        $datagenerator->create_enrolment(array('userid' => $this->student['id'], 'moduleid' => $mod['id'], 'calendar_year' => 2015));
        $datagenerator->create_enrolment(array('userid' => $this->student['id'], 'moduleid' => $this->module, 'calendar_year' => 2015));
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $this->config->set_setting('cfg_summative_mgmt', false, \Config::BOOLEAN);
        $this->pid1 = $datagenerator->create_paper(
            array(
                'papertitle' => 'Test summative 1',
                'calendaryear' => 2015,
                'modulename' => 'Training Module',
                'paperowner' => 'admin',
                'papertype' => '2',
                'remote' => '1',
                )
        );
        $settings = $datagenerator->set_post_creation_settings($this->pid1['id'], array('rubric' => 'This is a rubric'));
        $this->pid1 = array_merge($this->pid1, $settings);
        $this->pid2 = $datagenerator->create_paper(array('papertitle' => 'Test summative 2',
            'calendaryear' => 2015,
            'password' => 'EC1VbYJtOq8NsidA+q60rDEzjZZ8eHmHm6dEtfVBpeQ=',
            'modulename' => 'Test module 3',
            'paperowner' => 'admin',
            'papertype' => '2',
            'duration' => 60));
        $this->pid3 = $datagenerator->create_paper(array('papertitle' => 'Test progressive',
            'calendaryear' => 2015,
            'duration' => 60,
            'modulename' => 'Test module 3',
            'paperowner' => 'admin',
            'papertype' => '1'));
        $this->pid4 = $datagenerator->create_paper(array('papertitle' => 'Test formative',
            'calendaryear' => 2015,
            'modulename' => 'Test module 3',
            'paperowner' => 'admin',
            'papertype' => '0'));
        $datagenerator = $this->get_datagenerator('questions', 'core');
        $this->question = $datagenerator->create_question(array('user' => 'admin',
            'type' => 'enhancedcalc',
            'leadin' => 'test'));
        $datagenerator->add_question_to_paper(array('paper' => $this->pid2['id'], 'question' => $this->question['id'], 'screen' => 1, 'displaypos' => 1));
        $logdatagenerator = $this->get_datagenerator('log', 'core');
        $meta = $logdatagenerator->create_metadata(array('userID' => $this->student['id'], 'paperID' => $this->pid2['id'], 'year' => 1, 'started' => '2015-01-01 09:00:00'));
        $meta2 = $logdatagenerator->create_metadata(array('userID' => $this->admin['id'] , 'paperID' => $this->pid2['id'], 'started' => '2015-02-01 10:00:00'));
        $this->log = $logdatagenerator->create_summative(array('q_id' => $this->question['id'], 'metadataID' => $meta['id'], 'screen' => 1, 'user_answer' => 1));
        $this->log2 = $logdatagenerator->create_summative(array('q_id' => $this->question['id'], 'metadataID' => $meta2['id'], 'mark' => 1));
        $this->question2 = $datagenerator->create_question(array('user' => 'admin',
            'type' => 'enhancedcalc',
            'theme' => 'test theme',
            'leadin' => 'test leadin',
            'scenario' => 'test scenario',
            'notes' => 'test notes',
            'display_method' => null,
            'score_method' => 'Allow partial Marks',
            'settings' => '{"strictdisplay":true,"strictzeros":false,"dp":"0","tolerance_full":"0","fulltoltyp":"#","tolerance_partial":"0","parttoltyp":"#","marks_partial":0,"marks_incorrect":0,"marks_correct":1,"marks_unit":0,"show_units":true,"answers":[{"formula":"$A*$B","units":"cm"}],"vars":{"$A":{"min":"2","max":"10","inc":"1","dec":"0"},"$B":{"min":"5","max":"10","inc":"1","dec":"0"}}}'));
        $this->log3 = $logdatagenerator->create_summative(array('q_id' => $this->question2['id'], 'metadataID' => $meta['id'], 'screen' => 2, 'user_answer' => 2));
        $this->log4 = $logdatagenerator->create_summative(array('q_id' => $this->question2['id'], 'metadataID' => $meta2['id']));
        $datagenerator->add_question_to_paper(array('paper' => $this->pid2['id'], 'question' => $this->question2['id'], 'screen' => 2, 'displaypos' => 1));
        $this->qpaper = $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question2['id'], 'screen' => 1, 'displaypos' => 1));
        $this->question3 = $datagenerator->create_question(array('user' => 'admin',
            'type' => 'enhancedcalc',
            'leadin' => 'test 3'));
        $this->log5 = $logdatagenerator->create_summative(array('q_id' => $this->question3['id'], 'metadataID' => $meta['id'], 'screen' => 3, 'user_answer' => 5, 'mark' => 1));
        $this->log6 = $logdatagenerator->create_summative(array('q_id' => $this->question3['id'], 'metadataID' => $meta2['id']));
        $datagenerator->add_question_to_paper(array('paper' => $this->pid2['id'], 'question' => $this->question3['id'], 'screen' => 3, 'displaypos' => 1));
        $this->question4 = $datagenerator->create_question(array('user' => 'admin',
            'type' => 'enhancedcalc',
            'leadin' => 'test 4'));
        $this->log7 = $logdatagenerator->create_summative(array('q_id' => $this->question4['id'] , 'metadataID' => $meta['id'], 'screen' => 4, 'user_answer' => 7, 'mark' => 1));
        $this->log8 = $logdatagenerator->create_summative(array('q_id' => $this->question4['id'] , 'metadataID' => $meta2['id'], 'mark' => 1));
        $datagenerator->add_question_to_paper(array('paper' => $this->pid2['id'], 'question' => $this->question4['id'] , 'screen' => 4, 'displaypos' => 1));
        $this->question5 = $datagenerator->create_question(array('user' => 'admin',
            'type' => 'area',
            'leadin' => 'test 5'));
        $this->log9 = $logdatagenerator->create_summative(array('q_id' => $this->question5['id'], 'metadataID' => $meta['id'], 'screen' => 5, 'user_answer' => 10));
        $this->log10 = $logdatagenerator->create_summative(array('q_id' => $this->question5['id'], 'metadataID' => $meta2['id']));
        $datagenerator->add_question_to_paper(array('paper' => $this->pid2['id'], 'question' => $this->question5['id'], 'screen' => 5, 'displaypos' => 1));
        $this->question6 = $datagenerator->create_question(array('user' => 'admin',
            'type' => 'mcq',
            'theme' => 'test theme 2',
            'leadin' => 'test leadin 2',
            'scenario' => 'test scenario 2',
            'notes' => 'test notes 2',
            'score_method' => 'Mark per Option',
            'display_method' => 'vertical',
            'q_option_order' => 'random',
            'q_media' => '1517406311.png',
            'q_media_width' => 480,
            'q_media_height' => 105,
            'settings' => '[]'));
        $this->qpaper2 = $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question6['id'], 'screen' => 1, 'displaypos' => 2));
        $this->options = $datagenerator->add_options_to_question(array('question' => $this->question6['id'],
            'option_text' => 'true',
            'correct' => 1,
            'o_media' => '1517409282.jpg',
            'o_media_width' => 951,
            'o_media_height' => 121,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $this->options2 = $datagenerator->add_options_to_question(array('question' => $this->question6['id'],
            'option_text' => 'false',
            'correct' => 1,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $this->options3 = $datagenerator->add_options_to_question(array('question' => $this->question6['id'],
            'option_text' => 'maybe',
            'correct' => 1,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $this->config->set_setting('cfg_summative_mgmt', true, \Config::BOOLEAN);
    }

    /**
     * Test setting paper password
     * @group paper
     */
    public function testSetPassword()
    {
        // Load user id 1.
        $this->set_active_user($this->admin['id']);
        // Set new password.
        $newpassword = 'newpassword';
        $properties = PaperProperties::get_paper_properties_by_id($this->pid2['id'], $this->db, '');
        $properties->set_password($newpassword);
        $properties->save();
        // Check password updating.
        $paperproperty = PaperProperties::get_paper_properties_by_id($this->pid2['id'], $this->db, ''); // Get a fresh property object to check if password saved
        $savedencryptedpass = $paperproperty->get_password();
        $this->assertNotEquals($newpassword, $savedencryptedpass);
        $savedpass = $paperproperty->get_decrypted_password();
        $this->assertEquals($newpassword, $savedpass);
        $actual = $this->query(array('columns' => array('type', 'typeID', 'editor', 'new', 'old', 'part'), 'table' => 'track_changes'));
        $expected = array(
            0 => array(
                'type' => 'Paper',
                'typeID' =>  $this->pid2['id'],
                'editor' =>  $this->admin['id'],
                'new' =>  '********',
                'old' => '********',
                'part' => 'password'
            )
        );
        // Check track changes masks password.
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test calculation question getter - all users
     * @group paper
     */
    public function testGetEnhancedcalcQuestionsAll()
    {
        // Load user id 1.
        $this->set_active_user($this->admin['id']);
        $properties = PaperProperties::get_paper_properties_by_id($this->pid2['id'], $this->db, '');
        $expected = array($this->question['id'], $this->question2['id'], $this->question3['id']);
        $this->assertEquals($expected, $properties->get_enhancedcalc_questions(0));
    }

    /**
     * Test calculation question getter - all users after unmarked_enhancedcalc called for student
     * @group paper
     */
    public function testGetEnhancedcalcQuestionsAll2()
    {
        // Load user id 1.
        $this->set_active_user($this->admin['id']);
        $properties = PaperProperties::get_paper_properties_by_id($this->pid2['id'], $this->db, '');
        $properties->unmarked_enhancedcalc(0);
        $properties->unmarked_enhancedcalc(1);
        $expected = array($this->question['id'], $this->question2['id'], $this->question3['id']);
        $this->assertEquals($expected, $properties->get_enhancedcalc_questions(0));
    }

    /**
     * Test calculation question getter - student users
     * @group paper
     */
    public function testGetEnhancedcalcQuestionsStudent()
    {
        // Load user id 1.
        $this->set_active_user($this->admin['id']);
        $properties = PaperProperties::get_paper_properties_by_id($this->pid2['id'], $this->db, '');
        $expected = array($this->question['id'], $this->question2['id']);
        $this->assertEquals($expected, $properties->get_enhancedcalc_questions(1));
    }

    /**
     * Test calculation question getter - student users after unmarked_enhancedcalc called for all
     * @group paper
     */
    public function testGetEnhancedcalcQuestionsStudent2()
    {
        // Load user id 1.
        $this->set_active_user($this->admin['id']);
        $properties = PaperProperties::get_paper_properties_by_id($this->pid2['id'], $this->db, '');
        $properties->unmarked_enhancedcalc(1);
        $properties->unmarked_enhancedcalc(0);
        $expected = array($this->question['id'], $this->question2['id']);
        $this->assertEquals($expected, $properties->get_enhancedcalc_questions(1));
    }

    /**
     * Test building a paper
     * @group paper
     */
    public function testBuildPaper()
    {
        $properties = PaperProperties::get_paper_properties_by_id($this->pid1['id'], $this->db, '');
        $expected = array(
            1 => array(
                'assigned_number' => 1,
                'no_on_screen' => 1,
                'screen' => $this->qpaper['screen'],
                'theme' => $this->question2['theme'],
                'scenario' => $this->question2['scenario'],
                'leadin' => $this->question2['leadin'],
                'notes' => $this->question2['notes'],
                'q_type' => $this->question2['q_type'],
                'q_id' => $this->question2['id'],
                'display_pos' => $this->qpaper['displaypos'],
                'score_method' => $this->question2['score_method'],
                'display_method' => $this->question2['display_method'],
                'settings' => $this->question2['settings'],
                'q_media' => $this->question2['q_media'],
                'q_media_width' => $this->question2['q_media_width'],
                'q_media_height' => $this->question2['q_media_height'],
                'q_media_alt' => $this->question2['q_media_alt'],
                'q_media_num' => $this->question2['q_media_num'],
                'q_option_order' => $this->question2['q_option_order'],
                'dismiss' => '',
                'correct_fback' => $this->question2['correct_fback'],
                'options' => array(0 => array(
                    'correct' => null,
                    'option_text' => null,
                    'o_media' => '',
                    'o_media_width' => '',
                    'o_media_height' => '',
                    'o_media_alt' => '',
                    'marks_correct' => null,
                    'marks_incorrect' => null,
                    'marks_partial' => null
                )),
            ),
            2 => array(
                'assigned_number' => 2,
                'no_on_screen' => 2,
                'screen' => $this->qpaper2['screen'],
                'theme' => $this->question6['theme'],
                'scenario' => $this->question6['scenario'],
                'leadin' => $this->question6['leadin'],
                'notes' => $this->question6['notes'],
                'q_type' => $this->question6['q_type'],
                'q_id' => $this->question6['id'],
                'display_pos' => $this->qpaper2['displaypos'],
                'score_method' => $this->question6['score_method'],
                'display_method' => $this->question6['display_method'],
                'settings' => $this->question6['settings'],
                'q_media' => $this->question6['q_media'],
                'q_media_width' => $this->question6['q_media_width'],
                'q_media_height' => $this->question6['q_media_height'],
                'q_media_alt' => $this->question6['q_media_alt'],
                'q_media_num' => $this->question6['q_media_num'],
                'q_option_order' => $this->question6['q_option_order'],
                'dismiss' => '',
                'correct_fback' => $this->question6['correct_fback'],
                'options' => array(0 => array(
                    'correct' => $this->options['correct'],
                    'option_text' => $this->options['option_text'],
                    'o_media' => $this->options['o_media'],
                    'o_media_width' => $this->options['o_media_width'],
                    'o_media_height' => $this->options['o_media_height'],
                    'o_media_alt' => $this->options['o_media_alt'],
                    'marks_correct' => $this->options['marks_correct'],
                    'marks_incorrect' => $this->options['marks_incorrect'],
                    'marks_partial' => $this->options['marks_partial']
                ),
                    1 => array(
                        'correct' => $this->options2['correct'],
                        'option_text' => $this->options2['option_text'],
                        'o_media' => $this->options2['o_media'],
                        'o_media_width' => $this->options2['o_media_width'],
                        'o_media_height' => $this->options2['o_media_height'],
                        'o_media_alt' => $this->options2['o_media_alt'],
                        'marks_correct' => $this->options2['marks_correct'],
                        'marks_incorrect' => $this->options2['marks_incorrect'],
                        'marks_partial' => $this->options['marks_partial']
                    ),
                    2 => array(
                        'correct' => $this->options3['correct'],
                        'option_text' => $this->options3['option_text'],
                        'o_media' => $this->options3['o_media'],
                        'o_media_width' => $this->options3['o_media_width'],
                        'o_media_height' => $this->options3['o_media_height'],
                        'o_media_alt' => $this->options3['o_media_alt'],
                        'marks_correct' => $this->options3['marks_correct'],
                        'marks_incorrect' => $this->options3['marks_incorrect'],
                        'marks_partial' => $this->options3['marks_partial']
                    ),
                )
            ));
        $this->assertEquals($expected, $properties->build_paper(false, null, null));
    }

    /**
     * Test building a paper = question preview
     * @group paper
     */
    public function testBuildPaperQuestionPreview()
    {
        $properties = PaperProperties::get_paper_properties_by_id($this->pid1['id'], $this->db, '');
        $expected = array(1 => array(
            'assigned_number' => 1,
            'no_on_screen' => 1,
            'screen' => $this->qpaper['screen'],
            'theme' => $this->question2['theme'],
            'scenario' => $this->question2['scenario'],
            'leadin' => $this->question2['leadin'],
            'notes' => $this->question2['notes'],
            'q_type' => $this->question2['q_type'],
            'q_id' => $this->question2['id'],
            'display_pos' => $this->qpaper['displaypos'],
            'score_method' => $this->question2['score_method'],
            'display_method' => $this->question2['display_method'],
            'settings' => $this->question2['settings'],
            'q_media' => $this->question2['q_media'],
            'q_media_width' => $this->question2['q_media_width'],
            'q_media_height' => $this->question2['q_media_height'],
            'q_media_alt' => $this->question2['q_media_alt'],
            'q_media_num' => $this->question2['q_media_num'],
            'q_option_order' => $this->question2['q_option_order'],
            'dismiss' => '',
            'correct_fback' => $this->question2['correct_fback'],
            'options' => array(0 => array(
                'correct' => null,
                'option_text' => null,
                'o_media' => '',
                'o_media_width' => '',
                'o_media_height' => '',
                'o_media_alt' => '',
                'marks_correct' => null,
                'marks_incorrect' => null,
                'marks_partial' => null
            )),
        ));
        $this->assertEquals($expected, $properties->build_paper(true, $this->question2['id'], 1));
    }

    /**
     * Test display timer
     * @group paper
     */
    public function testDisplayTimer()
    {
        // Summative - no timed modules
        $properties = PaperProperties::get_paper_properties_by_id($this->pid1['id'], $this->db, '');
        $this->assertFalse($properties->display_timer());
        // Summative - timed modules
        $properties = PaperProperties::get_paper_properties_by_id($this->pid2['id'], $this->db, '');
        $this->assertTrue($properties->display_timer());
        // Progressive - timed
        $properties = PaperProperties::get_paper_properties_by_id($this->pid3['id'], $this->db, '');
        $this->assertTrue($properties->display_timer());
        // Formative - not timed
        $properties = PaperProperties::get_paper_properties_by_id($this->pid4['id'], $this->db, '');
        $this->assertFalse($properties->display_timer());
    }

    /**
     * Test get student list for paper
     * @group paper
     */
    public function testGetUserList()
    {
        $expected = array($this->student['id']);
        $startdate = '2015-01-01 00:00:00';
        $enddate = '2015-02-01 11:00:00';
        $percentile = 100;
        $studentonly = true;
        $modules = '';
        $properties = PaperProperties::get_paper_properties_by_id($this->pid2['id'], $this->db, '');
        // Students only.
        $this->assertEquals($expected, $properties->get_user_list($startdate, $enddate, $percentile, $studentonly, $modules));
        $expected = array($this->admin['id'], $this->student['id']);
        $studentonly = false;
        // All.
        $this->assertEquals($expected, $properties->get_user_list($startdate, $enddate, $percentile, $studentonly, $modules));
        // Top 40%.
        $expected = array($this->admin['id']);
        $percentile = 40;
        $this->assertEquals($expected, $properties->get_user_list($startdate, $enddate, $percentile, $studentonly, $modules));
        $percentile = 100;
        // Out of range.
        $expected = array();
        $startdate = '2017-01-01 00:00:00';
        $enddate = '2017-02-01 11:00:00';
        $this->assertEquals($expected, $properties->get_user_list($startdate, $enddate, $percentile, $studentonly, $modules));
        // Not in module.
        $startdate = '2015-01-01 00:00:00';
        $enddate = '2015-02-01 11:00:00';
        $modules = '2';
        $this->assertEquals($expected, $properties->get_user_list($startdate, $enddate, $percentile, $studentonly, $modules));
        // In module.
        $expected = array($this->student['id']);
        $modules = '1';
        $this->assertEquals($expected, $properties->get_user_list($startdate, $enddate, $percentile, $studentonly, $modules));
    }

    /**
     * Test get assessment data for paper
     * @group paper
     */
    public function testPaperAssessmentData()
    {
        $expected[0][1][$this->question['id']] = $this->log['user_answer'];
        $expected[0][2][$this->question2['id']] = $this->log3['user_answer'];
        $expected[0][3][$this->question3['id']] = $this->log5['user_answer'];
        $expected[0][4][$this->question4['id']] = $this->log7['user_answer'];
        $expected[0][5][$this->question5['id']] = $this->log9['user_answer'];
        $expected[0]['userID'] = $this->student['id'];
        $expected[0]['username'] = 'test1';
        $expected[0]['course'] = 'TEST2';
        $expected[0]['year'] = 1;
        $expected[0]['started'] = '2015-01-01 09:00:00';
        $expected[0]['title'] = 'Dr';
        $expected[0]['surname'] = 'User1';
        $expected[0]['first_names'] = 'A';
        $expected[0]['name'] = 'User1,A';
        $expected[0]['gender'] = null;
        $expected[0]['student_id'] = '1234567890';
        $properties = PaperProperties::get_paper_properties_by_id($this->pid2['id'], $this->db, '');
        $course = 'TEST2';
        $startdate = '2015-01-01 00:00:00';
        $enddate = '2015-02-01 11:00:00';
        $percentile = 100;
        $studentonly = true;
        $modules = '';
        $demo = false;
        $student_list = implode(',', $properties->get_user_list($startdate, $enddate, $percentile, $studentonly, $modules));
        $this->assertEquals($expected, $properties->get_paper_assessment_data($course, $startdate, $enddate, $student_list, $studentonly, $demo));
    }

    /**
     * Test get paper details
     * @group paper
     */
    public function testGetPaperQuestions()
    {
        $properties = PaperProperties::get_paper_properties_by_id($this->pid1['id'], $this->db, '');
        $expected[0]['ID'] = $this->question2['id'];
        $expected[0]['type'] = $this->question2['q_type'];
        $expected[0]['screen'] = $this->qpaper['screen'];
        $expected[0]['correct'] = ',';
        $expected[0]['correct_text'] = "\t";
        $expected[0]['score_method'] = $this->question2['score_method'];
        $expected[0]['settings'] = $this->question2['settings'];
        $expected[1]['ID'] = $this->question6['id'];
        $expected[1]['type'] = $this->question6['q_type'];
        $expected[1]['screen'] = $this->qpaper2['screen'];
        $expected[1]['correct'] = ',' . $this->options['correct'];
        $expected[1]['correct_text'] = "\t" . $this->options['option_text'] . "\t" . $this->options2['option_text'] . "\t" . $this->options3['option_text'];
        $expected[1]['score_method'] = $this->question6['score_method'];
        $expected[1]['settings'] = $this->question6['settings'];
        $this->assertEquals($expected, $properties->get_paper_questions());
    }

    /**
     * Test get remote summative details
     * @group paper
     */
    public function testGetRemoteSummativePaperProperties(): void
    {
        $expected = array();
        $property_object = new PaperProperties($this->db);
        $property_object->set_property_id($this->pid1['id']);
        $property_object->set_paper_title($this->pid1['papertitle']);
        $tz = new DateTimeZone($this->config->get('cfg_timezone'));
        $startdatetime = new DateTime($this->pid1['start_date'], $tz);
        $property_object->set_start_date($startdatetime->getTimestamp());
        $enddatetime = new DateTime($this->pid1['end_date'], $tz);
        $property_object->set_end_date($enddatetime->getTimestamp());
        $property_object->set_exam_duration($this->pid1['duration']);
        $property_object->set_calendar_year($this->pid1['calendaryear']);
        $property_object->set_password($this->pid1['password']);
        $property_object->set_timezone($this->pid1['timezone']);
        $property_object->set_display_start_date();
        $property_object->set_display_start_time();
        $property_object->set_display_end_date();
        $property_object->set_display_end_time();
        $property_object->set_rubric($this->pid1['rubric']);
        $expected[] = $property_object;
        $this->assertEquals($expected, PaperProperties::getRemoteSummativePaperProperties($this->db));
    }

    /**
     * Test if paper type is enabled
     * @group paper
     */
    public function testIsEnabled(): void
    {
        // Disable paper type.
        $papertypes = array(
            'formative' => 1,
            'progress' => 1,
            'summative' => 0,
            'survey' => 1,
            'osce' => 1,
            'offline' => 1,
            'peer_review' => 1
        );
        $this->config->set_setting('paper_types', $papertypes, Config::ASSOC);
        $properties = PaperProperties::get_paper_properties_by_id($this->pid2['id'], $this->db, '');
        $this->assertFalse($properties->isEnabled());
        // Enable paper type.
        $papertypes = array(
            'formative' => 1,
            'progress' => 1,
            'summative' => 1,
            'survey' => 1,
            'osce' => 1,
            'offline' => 1,
            'peer_review' => 1
        );
        $this->config->set_setting('paper_types', $papertypes, Config::ASSOC);
        $properties = PaperProperties::get_paper_properties_by_id($this->pid2['id'], $this->db, '');
        $this->assertTrue($properties->isEnabled());
    }

    /**
     * Test inserting and updating settings.
     * @group paper
     */
    public function testUpdateSetting(): void
    {
        $properties = PaperProperties::get_paper_properties_by_id($this->pid1['id'], $this->db, array());
        $queryTable = $this->query(
            array(
                'columns' => array('paperid', 'setting', 'value'),
                'table' => 'paper_settings'
            )
        );
        $expectedTable = array(
            0 => array(
                'setting' => 'remote_summative',
                'value' => '1',
                'paperid' => $this->pid1['id'],
            ),
            1 => array(
                'setting' => 'remote_summative',
                'value' => '0',
                'paperid' => $this->pid2['id'],
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
        $properties->updateSetting('remote_summative', 0, $this->pid1['id']);
        $queryTable = $this->query(
            array(
                'columns' => array('paperid', 'setting', 'value'),
                'table' => 'paper_settings'
            )
        );
        $expectedTable = array(
            0 => array(
                'setting' => 'remote_summative',
                'value' => '0',
                'paperid' => $this->pid1['id'],
            ),
            1 => array(
                'setting' => 'remote_summative',
                'value' => '0',
                'paperid' => $this->pid2['id'],
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test getting a setting
     * @group paper
     */
    public function testGetSetting(): void
    {
        $properties = PaperProperties::get_paper_properties_by_id($this->pid1['id'], $this->db, array());
        $this->assertEquals('1', $properties->getSetting('remote_summative'));
        $properties2 = PaperProperties::get_paper_properties_by_id($this->pid2['id'], $this->db, array());
        $this->assertEquals('0', $properties2->getSetting('remote_summative'));
        $this->expectExceptionMessage('invalid_paper_setting');
        $this->assertEquals('1', $properties->getSetting('doesnotexit'));
    }

    /**
     * Test getting a papers minimum availability.
     * @group paper
     */
    public function testGetMinAvailability(): void
    {
        $properties = PaperProperties::get_paper_properties_by_id($this->pid1['id'], $this->db, array());
        // Exam has no duration so expect 0.
        $this->assertEquals(0, $properties->getMinAvailability());
        // Passing duration.
        $duration = 100;
        $et = UserUtils::getExtraTime($this->student['id'], $this->db);
        $breaktime = 1 + ($et['breaktime'] / 100);
        $extatime = 1 + ($et['extratime'] / 100);
        $totalexamtime = $duration * $extatime;
        $expected = round($totalexamtime * $breaktime);
        $this->assertEquals($expected, $properties->getMinAvailability($duration));
        $this->config->set_setting('paper_breaktime_mins', true, \Config::BOOLEAN);
        $breaktime = ceil($totalexamtime / 60) * $et['breaktime'];
        $expected = round($totalexamtime + $breaktime);
        $this->assertEquals($expected, $properties->getMinAvailability($duration));
        $this->config->set_setting('paper_breaktime_mins', false, \Config::BOOLEAN);
        $properties = PaperProperties::get_paper_properties_by_id($this->pid2['id'], $this->db, array());
        // Exam has a duration.
        $totalexamtime = $properties->get_exam_duration() * $extatime;
        $breaktime = 1 + ($et['breaktime'] / 100);
        $expected = round($totalexamtime * $breaktime);
        $properties = PaperProperties::get_paper_properties_by_id($this->pid2['id'], $this->db, '');
        $this->assertEquals($expected, $properties->getMinAvailability());
        $this->config->set_setting('paper_breaktime_mins', true, \Config::BOOLEAN);
        $breaktime = ceil($totalexamtime / 60) * $et['breaktime'];
        $expected = round($totalexamtime + $breaktime);
        $this->assertEquals($expected, $properties->getMinAvailability());
    }
}
