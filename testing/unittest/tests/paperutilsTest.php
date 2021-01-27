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

use testing\unittest\unittestdatabase;

/**
 * Test paperutils class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class paperutilstest extends unittestdatabase
{
    /** @var array Storage for paper data in tests. */
    private $pid1;

    /** @var array Storage for paper data in tests. */
    private $pid2;

    /** @var array Storage for user data in tests. */
    private $user1;

    /** @var array Storage for user data in tests. */
    private $user2;

    /** @var array Storage for question data in tests. */
    private $question1;

    /** @var array Storage for question data in tests. */
    private $question2;

    /** @var array Storage for question data in tests. */
    private $question3;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2015, 'academic_year' => '2015/16'));
        $datagenerator->create_academic_year(array('calendar_year' => 2016, 'academic_year' => '2016/17'));
        $datagenerator->create_academic_year(array('calendar_year' => 2017, 'academic_year' => '2017/18'));
        $datagenerator = $this->get_datagenerator('users', 'core');
        $this->user1 = $datagenerator->create_user(array('surname' => 'staff', 'username' => 'staff1', 'grade' => 'University Lecturer', 'initials' => 'a',
            'title' => 'Dr', 'email' => 'staff1@example.com', 'gender' => 'Male', 'first_names' => 'a', 'yearofstudy' => null, 'roles' => 'Staff'));
        $this->user2 = $datagenerator->create_user(array('surname' => 'staff2', 'username' => 'staff2', 'grade' => 'University Lecturer', 'initials' => 'a',
            'title' => 'Dr', 'email' => 'staff2@example.com', 'gender' => 'Male', 'first_names' => 'a', 'yearofstudy' => null, 'roles' => 'Staff'));
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $this->pid1 = $datagenerator->create_paper(array('papertitle' => 'Paper 1',
            'created' => '2017-01-09 14:30:00',
            'duration' => 90,
            'calendaryear' => 2015,
            'paperowner' => 'staff1',
            'papertype' => '2',
            'modulename' => 'Training Module'));
        $this->pid2 = $datagenerator->create_paper(array('papertitle' => 'Paper 2',
            'created' => '2017-01-09 14:31:00',
            'duration' => 90,
            'calendaryear' => 2016,
            'paperowner' => 'staff1',
            'papertype' => '2',
            'modulename' => array('Training Module', 'Online Help')));
        $datagenerator = $this->get_datagenerator('modules', 'core');
        $datagenerator->create_module(array('moduleid' => 'ABC300', 'fullname' => 'Test module 3', 'schoolID' => 1));
        $datagenerator->create_module_team(array('moduleid' => 'TRAIN', 'username' => 'staff1'));
        $datagenerator->create_module_team(array('moduleid' => 'ABC300', 'username' => 'staff2'));
        $datagenerator = $this->get_datagenerator('questions', 'core');
        $this->question1 = $datagenerator->create_question(array('user' => 'admin',
            'type' => 'enhancedcalc',
            'theme' => 'test theme',
            'leadin' => 'test leadin',
            'scenario' => 'test scenario',
            'notes' => 'test_notes',
            'display_method' => '',
            'score_method' => 'Allow partial Marks',
            'settings' => '{"strictdisplay":true,"strictzeros":false,"dp":"0","tolerance_full":"0","fulltoltyp":"#","tolerance_partial":"0","parttoltyp":"#","marks_partial":0,"marks_incorrect":0,"marks_correct":1,"marks_unit":0,"show_units":true,"answers":[{"formula":"$A*$B","units":"cm"}],"vars":{"$A":{"min":"2","max":"10","inc":"1","dec":"0"},"$B":{"min":"5","max":"10","inc":"1","dec":"0"}}}'));
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question1['id'], 'screen' => 1, 'displaypos' => 2));
        $this->question2 = $datagenerator->create_question(array('user' => 'admin',
            'type' => 'enhancedcalc',
            'theme' => 'test theme 3',
            'leadin' => 'test leadin 3',
            'scenario' => 'test scenario 3',
            'notes' => 'test_notes 3',
            'display_method' => '',
            'score_method' => 'Allow partial Marks',
            'settings' => '{"strictdisplay":true,"strictzeros":false,"dp":"0","tolerance_full":"0","fulltoltyp":"#","tolerance_partial":"0","parttoltyp":"#","marks_partial":0,"marks_incorrect":0,"marks_correct":1,"marks_unit":0,"show_units":true,"answers":[{"formula":"$A-$B","units":"cm"}],"vars":{"$A":{"min":"ans2","max":"ans2","inc":"1","dec":"0"},"$B":{"min":"5","max":"10","inc":"1","dec":"0"}}}'));
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question2['id'], 'screen' => 2, 'displaypos' => 3));
        $this->question3 = $datagenerator->create_question(array('user' => 'admin',
            'type' => 'mcq',
            'theme' => 'test theme 2',
            'leadin' => 'test leadin 2',
            'scenario' => 'test scenario 2',
            'notes' => 'test_notes 2',
            'display_method' => 'vertical',
            'score_method' => 'Mark per Option',
            'display_method' => 'random',
            'q_media' => '1517406311.png',
            'q_media_width' => 480,
            'q_media_height' => 105,
            'settings' => '[]'));
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question3['id'], 'screen' => 1, 'displaypos' => 1));
        $datagenerator->add_options_to_question(array('question' => $this->question3['id'],
            'option_text' => 'true',
            'correct' => 1,
            'o_media' => '1517409282.jpg',
            'o_media_width' => 951,
            'o_media_height' => 121,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $datagenerator->add_options_to_question(array('question' => $this->question3['id'],
            'option_text' => 'false',
            'correct' => 1,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $datagenerator->add_options_to_question(array('question' => $this->question3['id'],
            'option_text' => 'maybe',
            'correct' => 1,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $datagenerator = $this->get_datagenerator('objective', 'core');
        $sess = $datagenerator->create_session(array('idMod' => $this->module, 'calendar_year' => 2015, 'occurrence' => '2017-01-09 11:00:00'));
        $sess2 = $datagenerator->create_session(array('idMod' => $this->module, 'calendar_year' => 2017, 'occurrence' => '2018-01-09 11:00:00'));
        $datagenerator->create_objective(array('idMod' => $this->module, 'identifier' => $sess['identifier'], 'objective' => 'a', 'calendar_year' => 2015));
        $datagenerator->create_objective(array('idMod' => $this->module, 'identifier' => $sess['identifier'], 'objective' => 'b', 'calendar_year' => 2015, 'sequence' => 2));
        $datagenerator->create_objective(array('idMod' => $this->module, 'identifier' => $sess['identifier'], 'objective' => 'c', 'calendar_year' => 2015, 'sequence' => 3));
        $datagenerator->create_objective(array('idMod' => $this->module, 'identifier' => $sess2['identifier'], 'objective' => 'a', 'calendar_year' => 2017));
        $datagenerator->create_objective(array('idMod' => $this->module, 'identifier' => $sess2['identifier'], 'objective' => 'b', 'calendar_year' => 2017, 'sequence' => 2));
        $datagenerator->create_objective(array('idMod' => $this->module, 'identifier' => $sess2['identifier'], 'objective' => 'c', 'calendar_year' => 2017, 'sequence' => 3));
        $datagenerator = $this->get_datagenerator('gradebook', 'core');
        $datagenerator->create_paper(array('paperid' => $this->pid1['id'], 'timestamp' => '2015-09-16 16:09:16'));
        $datagenerator->create_paper(array('paperid' => $this->pid2['id'], 'timestamp' => '2016-09-16 16:09:16'));
    }

    /**
     * Test complete paper deletion
     * @group assessment
     */
    public function test_complete_delete_paper()
    {
        // Check successful deletion.
        $this->assertTrue(Paper_utils::complete_delete_paper($this->pid2['id'], $this->db));
        $querypropertiestable = $this->query(array('table' => 'properties', 'orderby' => array('property_id'), 'columns' => array('property_id')));
        $querypropertiesmodulestable = $this->query(array('table' => 'properties_modules'));
        $expectedpropertiestable = array(
            0 => array(
                'property_id' => $this->pid1['id']
            )
        );
        $expectedpropertiesmodulestable = array(
            0 => array(
                'property_id' => $this->pid1['id'],
                'idMod' => $this->module
            )
        );
        // Check properties table deletion.
        $this->assertEquals($expectedpropertiestable, $querypropertiestable);
        // Check properties_modules table deletion.
        $this->assertEquals($expectedpropertiesmodulestable, $querypropertiesmodulestable);
    }

    /**
     * Test get papers by session
     * @group gradebook
     */
    public function test_get_papers_by_session()
    {
        $papers = array($this->pid2['id']);
        $this->assertEquals($papers, Paper_utils::get_papers_by_session('2016', 2, $this->db));
        $papers = array();
        $this->assertEquals($papers, Paper_utils::get_papers_by_session('2016', 1, $this->db));
    }

    /**
     * Test get finalised papers
     * @group gradebook
     */
    public function test_get_finalised_papers()
    {
        $papers = array($this->pid2['id']);
        $this->assertEquals($papers, Paper_utils::get_finalised_papers('2016', 2, $this->db));
        $papers = array();
        $this->assertEquals($papers, Paper_utils::get_finalised_papers('2016', 1, $this->db));
    }

    /**
     * Test get available papers - paper type provided
     * @group assessment
     */
    public function test_get_available_papers_type()
    {
        // Load user.
        $this->set_active_user($this->user1['id']);
        $order = 'paper_title';
        $direction = 'asc';
        $papers = array();
        $created1 = ' ' . date($this->config->get('cfg_long_date_php'));
        $created2 = ' ' . date($this->config->get('cfg_long_date_php'));
        $papers[$this->pid1['id']] = array('paper_title' => $this->pid1['papertitle'], 'paper_type' => $this->pid1['papertype'],
            'created' => $created1, 'title' => $this->user1['title'], 'initials' => $this->user1['initials'], 'surname' => $this->user1['surname']);
        $papers[$this->pid1['id']]['moduleid'][0] = 'TRAIN';
        $papers[$this->pid2['id']] = array('paper_title' => $this->pid2['papertitle'], 'paper_type' => $this->pid2['papertype'],
            'created' => $created2, 'title' => $this->user1['title'], 'initials' => $this->user1['initials'], 'surname' => $this->user1['surname']);
        $papers[$this->pid2['id']]['moduleid'][0] = 'SYSTEM';
        $papers[$this->pid2['id']]['moduleid'][1] = 'TRAIN';
        $this->assertEquals($papers, PaperUtils::get_available_papers($this->userobject, $order, $direction, '2', null));
    }

    /**
     * Test get available papers - team id provided
     * @group assessment
     */
    public function test_get_available_papers_team()
    {
        // Load user.
        $this->set_active_user($this->user1['id']);
        $order = 'paper_title';
        $direction = 'asc';
        $papers = array();
        $created1 = ' ' . date($this->config->get('cfg_long_date_php'));
        $created2 = ' ' . date($this->config->get('cfg_long_date_php'));
        $papers[$this->pid1['id']] = array('paper_title' => $this->pid1['papertitle'], 'paper_type' => $this->pid1['papertype'],
            'created' => $created1, 'title' => $this->user1['title'], 'initials' => $this->user1['initials'], 'surname' => $this->user1['surname']);
        $papers[$this->pid1['id']]['moduleid'][0] = 'TRAIN';
        $papers[$this->pid2['id']] = array('paper_title' => $this->pid2['papertitle'], 'paper_type' => $this->pid2['papertype'],
            'created' => $created2, 'title' => $this->user1['title'], 'initials' => $this->user1['initials'], 'surname' => $this->user1['surname']);
        $papers[$this->pid2['id']]['moduleid'][0] = 'TRAIN';
        $this->assertEquals($papers, PaperUtils::get_available_papers($this->userobject, $order, $direction, null, $this->module));
    }

    /**
     * Test get available papers - paper type provided, non available
     * @group assessment
     */
    public function test_get_available_papers_type_none()
    {
        // Load user.
        $this->set_active_user($this->user2['id']);
        $order = 'paper_title';
        $direction = 'asc';
        $papers = array();
        $this->assertEquals($papers, PaperUtils::get_available_papers($this->userobject, $order, $direction, '2', null));
    }

    /**
     * Test get available papers - team id provided, non available
     * @group assessment
     */
    public function test_get_available_papers_team_none()
    {
        // Load user.
        $this->set_active_user($this->user2['id']);
        $order = 'paper_title';
        $direction = 'asc';
        $papers = array();
        $this->assertEquals($papers, PaperUtils::get_available_papers($this->userobject, $order, $direction, null, 0));
    }

    /**
     * Test get available papers - team id and paper type not provided
     * @group assessment
     */
    public function test_get_available_papers_null()
    {
        // Load user.
        $this->set_active_user($this->user2['id']);
        $order = 'paper_title';
        $direction = 'asc';
        $papers = array();
        $this->assertEquals($papers, PaperUtils::get_available_papers($this->userobject, $order, $direction, null, null));
    }

    /**
     * Test get copy paper properties
     * @group assessment
     */
    public function test_copyProperties()
    {
        // Load user.
        $this->set_active_user($this->user1['id']);
        $postparams['paperID'] = $this->pid2['id'];
        $postparams['paper_type'] = 1;
        $postparams['new_paper'] = 'paper copy test';
        $postparams['session'] = 2017;
        $moduleIDs = null;
        $calendar_year = $new_calendar_year = '';
        $papercopy = PaperUtils::copyProperties($calendar_year, $new_calendar_year, $moduleIDs, $postparams);
        $this->assertEquals(2016, $papercopy['calendar_year']);
        $this->assertEquals(2017, $papercopy['new_calendar_year']);
        $this->assertEquals(array(1 => 'TRAIN', 2 => 'SYSTEM'), $papercopy['moduleIDs']);
        $querypropertiestable = $this->query(array('table' => 'properties', 'columns' => array('property_id', 'paper_title', 'calendar_year', 'paper_type', 'paper_ownerID', 'exam_duration')));
        $querypropertiesmodulestable = $this->query(array('table' => 'properties_modules', 'orderby' => array('property_id')));
        $expectedpropertiestable = array(
            0 => array(
                'property_id' => $this->pid1['id'],
                'paper_title' => $this->pid1['papertitle'],
                'calendar_year' => $this->pid1['session'],
                'paper_type' => '2',
                'paper_ownerID' =>  $this->user1['id'] ,
                'exam_duration' =>  90
            ),
            1 => array(
                'property_id' => $this->pid2['id'],
                'paper_title' => $this->pid2['papertitle'],
                'calendar_year' => $this->pid2['session'],
                'paper_type' => '2',
                'paper_ownerID' =>  $this->user1['id'] ,
                'exam_duration' =>  90
            ),
            2 => array(
                'property_id' => $papercopy['new_paper_id'],
                'paper_title' => 'paper copy test',
                'calendar_year' => 2017,
                'paper_type' => '1',
                'paper_ownerID' =>  $this->user1['id'] ,
                'exam_duration' =>  90
            )
        );
        $this->assertEquals($expectedpropertiestable, $querypropertiestable);
        $expectedpropertiesmodulestable = array(
            0 => array(
                'property_id' => $this->pid1['id'],
                'idMod' => $this->module
            ),
            1 => array(
                'property_id' => $this->pid2['id'],
                'idMod' => $this->module
            ),
            2 => array(
                'property_id' => $this->pid2['id'],
                'idMod' => $this->get_module_id('SYSTEM')
            ),
            3 => array(
                'property_id' => $papercopy['new_paper_id'],
                'idMod' => $this->module
            ),
            4 => array(
                'property_id' => $papercopy['new_paper_id'],
                'idMod' => $this->get_module_id('SYSTEM')
            ),
        );
        $this->assertEquals($expectedpropertiesmodulestable, $querypropertiesmodulestable);
    }

    /**
     * Test get copy objectives between sessions
     * @group assessment
     */
    public function test_copy_between_sessions()
    {
        $this->set_active_user($this->user1['id']);
        $postparams['paperID'] = $this->pid1['id'];
        $postparams['paper_type'] = 1;
        $postparams['new_paper'] = 'paper copy test';
        $postparams['session'] = 2017;
        $moduleIDs = null;
        $calendar_year = $new_calendar_year = '';
        $papercopy = PaperUtils::copyProperties($calendar_year, $new_calendar_year, $moduleIDs, $postparams);
        // Need require until mapping made a class.
        $cfg_web_root = $this->config->get('cfg_web_root');
        require_once $cfg_web_root . 'include/mapping.inc';
        $old_course = getObjectives($papercopy['moduleIDs'], $papercopy['calendar_year'], $this->pid1['id'], '', $this->db);
        $new_course = getObjectives($papercopy['moduleIDs'], $papercopy['new_calendar_year'], $this->pid1['id'], '', $this->db);
        $mappings_copy_objID = Paper_utils::copy_between_sessions($old_course, $new_course);
        $expected_mappings = array(123 => 126, 124 => 127, 125 => 128);
        $this->assertEquals($expected_mappings, $mappings_copy_objID);
    }

    /**
     * Test get copy objectives between sessions, vle mistmatch
     * @group assessment
     */
    public function test_copy_between_sessions_mismatch()
    {
        $this->set_active_user($this->user1['id']);
        $postparams['paperID'] = $this->pid2['id'];
        $postparams['paper_type'] = 1;
        $postparams['new_paper'] = 'paper copy test';
        $postparams['session'] = 2017;
        $moduleIDs = null;
        $calendar_year = $new_calendar_year = '';
        $papercopy = PaperUtils::copyProperties($calendar_year, $new_calendar_year, $moduleIDs, $postparams);
        // Need require until mapping made a class.
        $cfg_web_root = $this->config->get('cfg_web_root');
        require_once $cfg_web_root . 'include/mapping.inc';
        // Fake getObjectives return. Ideally we would mock the CMAP response but that involves more rework.
        $old_course = array('A14ACE' => array(
            'ab0a3310-c125-11e2-bcdc-005056ad00ea' => array(
                'identifier' => '16605',
                'guid' => 'ab0a3310-c125-11e2-bcdc-005056ad00ea',
                'class_code' => '',
                'title' => 'Generic skills',
                'occurrance' => 'Non-timetabled',
                'calendar_year' => 2016,
                'VLE' => '', // null VLE
                'source_url' => '',
                'mapped' => 0,
                'objectives' => array(
                    1 => array(
                        'content' => 'Communicate clearly, sensitively and effectively with patients and their relatives or carers, and with other health care providers.',
                        'id' => '16606',
                        'guid' => 'ab0a33a6-c125-11e2-bcdc-005056ad00ea',
                        'mapped' => 0
                    )
                )
            )
        )
        );
        $new_course = array('A14ACE' => array(
            'ab0a3310-c125-11e2-bcdc-005056ad00ea' => array(
                'identifier' => '16607',
                'guid' => 'ab0a3310-c125-11e2-bcdc-005056ad00ea',
                'class_code' => '',
                'title' => 'Generic skills',
                'occurrance' => 'Non-timetabled',
                'calendar_year' => 2017,
                'VLE' => 'UoNCM',
                'source_url' => '',
                'mapped' => 0,
                'objectives' => array(
                    1 => array(
                        'content' => 'Communicate clearly, sensitively and effectively with patients and their relatives or carers, and with other health care providers.',
                        'id' => '16608',
                        'guid' => 'ab0a33a6-c125-11e2-bcdc-005056ad00ea',
                        'mapped' => 0
                    )
                )
            )
        )
        );
        $mappings_copy_objID = Paper_utils::copy_between_sessions($old_course, $new_course);
        $expected_mappings = array();
        $this->assertEquals($expected_mappings, $mappings_copy_objID);
    }

    /**
     * Test get copy objectives between sessions - cmap objectives
     * @group assessment
     */
    public function test_copy_between_sessions_cmap()
    {
        $this->set_active_user($this->user1['id']);
        $postparams['paperID'] = $this->pid2['id'];
        $postparams['paper_type'] = 1;
        $postparams['new_paper'] = 'paper copy test';
        $postparams['session'] = 2017;
        $moduleIDs = null;
        $calendar_year = $new_calendar_year = '';
        $papercopy = PaperUtils::copyProperties($calendar_year, $new_calendar_year, $moduleIDs, $postparams);
        // Fake getObjectives return. Ideally we would mock the CMAP response but that involves more rework.
        $old_course = array('A14ACE' => array(
            'ab0a3310-c125-11e2-bcdc-005056ad00ea' => array(
                'identifier' => '16605',
                'guid' => 'ab0a3310-c125-11e2-bcdc-005056ad00ea',
                'class_code' => '',
                'title' => 'Generic skills',
                'occurrance' => 'Non-timetabled',
                'calendar_year' => 2016,
                'VLE' => 'UoNCM',
                'source_url' => '',
                'mapped' => 0,
                'objectives' => array(
                    1 => array(
                        'content' => 'Communicate clearly, sensitively and effectively with patients and their relatives or carers, and with other health care providers.',
                        'id' => '16606',
                        'guid' => 'ab0a33a6-c125-11e2-bcdc-005056ad00ea',
                        'mapped' => 0
                    )
                )
            )
        )
        );
        $new_course = array('A14ACE' => array(
            'ab0a3310-c125-11e2-bcdc-005056ad00ea' => array(
                'identifier' => '16607',
                'guid' => 'ab0a3310-c125-11e2-bcdc-005056ad00ea',
                'class_code' => '',
                'title' => 'Generic skills',
                'occurrance' => 'Non-timetabled',
                'calendar_year' => 2017,
                'VLE' => 'UoNCM',
                'source_url' => '',
                'mapped' => 0,
                'objectives' => array(
                    1 => array(
                        'content' => 'Communicate clearly, sensitively and effectively with patients and their relatives or carers, and with other health care providers.',
                        'id' => '16608',
                        'guid' => 'ab0a33a6-c125-11e2-bcdc-005056ad00ea',
                        'mapped' => 0
                    )
                )
            )
        )
        );
        $mappings_copy_objID = Paper_utils::copy_between_sessions($old_course, $new_course);
        $expected_mappings = array(16606 => '16608');
        $this->assertEquals($expected_mappings, $mappings_copy_objID);
    }

    /**
     * Test get copy objectives between sessions - cmap objectives, no mappings
     * @group assessment
     */
    public function test_copy_between_sessions_cmap_nomappings()
    {
        $this->set_active_user($this->user1['id']);
        $postparams['paperID'] = $this->pid2['id'];
        $postparams['paper_type'] = 1;
        $postparams['new_paper'] = 'paper copy test';
        $postparams['session'] = 2017;
        $moduleIDs = null;
        $calendar_year = $new_calendar_year = '';
        $papercopy = PaperUtils::copyProperties($calendar_year, $new_calendar_year, $moduleIDs, $postparams);
        // Fake getObjectives return. Ideally we would mock the CMAP response but that involves more rework.
        $old_course = array('A14ACE' => array(
            'ab0a3310-c125-11e2-bcdc-005056ad00ea' => array(
                'identifier' => '16605',
                'guid' => 'ab0a3310-c125-11e2-bcdc-005056ad00ea',
                'class_code' => '',
                'title' => 'Generic skills',
                'occurrance' => 'Non-timetabled',
                'calendar_year' => 2016,
                'VLE' => 'UoNCM',
                'source_url' => '',
                'mapped' => 0,
                'objectives' => array(
                    1 => array(
                        'content' => 'Communicate clearly, sensitively and effectively with patients and their relatives or carers, and with other health care providers.',
                        'id' => '16606',
                        'guid' => 'ab0a33a6-c125-11e2-bcdc-005056ad00ea',
                        'mapped' => 0
                    )
                )
            )
        )
        );
        $new_course = array('A14ACE' => array(
            'ab0a3310-c125-11e2-bcdc-005056ad00ea' => array(
                'identifier' => '16607',
                'guid' => 'ab0a3310-c125-11e2-bcdc-005056ad00ea',
                'class_code' => '',
                'title' => 'Generic skills',
                'occurrance' => 'Non-timetabled',
                'calendar_year' => 2017,
                'VLE' => 'UoNCM',
                'source_url' => '',
                'mapped' => 0,
                'objectives' => array(
                    1 => array(
                        'content' => 'Content does not match',
                        'id' => '16608',
                        'guid' => 'ab0a33a6-c125-11e2-bcdc-005056ad00ea',
                        'mapped' => 0
                    )
                )
            )
        )
        );
        $mappings_copy_objID = Paper_utils::copy_between_sessions($old_course, $new_course);
        $expected_mappings = array();
        $this->assertEquals($expected_mappings, $mappings_copy_objID);
    }

    /**
     * Test creating list of parent link calc question in a paper
     * @group assessment
     */
    public function test_get_linked_question_parents()
    {
        $expected = array(2);
        // Build paper.
        $properties = PaperProperties::get_paper_properties_by_id($this->pid1['id'], $this->db, '');
        $tmp_questions_array = $properties->build_paper(false, null, null);
        // Mock paper start fudge.
        foreach ($tmp_questions_array as $question) {
            if ($question['q_type'] == 'enhancedcalc') {
                require_once 'plugins/questions/enhancedcalc/enhancedcalc.class.php';
                $question['object'] = new EnhancedCalc($this->config);
                $question['object']->load($question);
            }
            $questions_array[] = $question;
        }
        unset($tmp_questions_array);
        $this->assertEquals($expected, PaperUtils::get_linked_question_parents($questions_array));
    }

    /**
     * Tests that the correct session is parsed from the paper title.
     *
     * @param string $title The title of the paper.
     * @param string|bool $expected The expected value.
     * @dataProvider academic_year_from_title_data
     */
    public function test_academic_year_from_title($title, $expected)
    {
        $this->assertEquals($expected, Paper_utils::academic_year_from_title($title));
    }

    /**
     * Data for the academic_year_from_title tests.
     *
     * @return array
     */
    public function academic_year_from_title_data()
    {
        return [
            '4 Digit year' => ['Fun paper 2019/2020', '2019/20'],
            '4 Digit year in middle' => ['Fun paper 2019/2020 with some text after the year', '2019/20'],
            '4 Digit year double session' => ['Fun paper 2019/2021', '2019/21'],
            '4 Digit year with spaces' => ['Fun paper 2019 / 2020', '2019/20'],
            '4 Digit year with spaces in middle' => ['Fun paper 2019 / 2020 and some more text', '2019/20'],
            '4/2 Digit year' => ['Fun paper 2019/20', '2019/20'],
            '2 Digit year' => ['Fun paper 19/20', '2019/20'],
            '2 Digit year double session' => ['Fun paper 19/21', '2019/20'], // Not sure this makes sense...
            'No year' => ['Fun paper', false],
            'Numbers that are not a year 1' => ['D24C08 Fun paper', false],
            'Numbers that are not a year 2' => ['201920 Fun paper', false],
            'Invalid 4 Digit year 1' => ['Fun paper 2019/ 2020', false],
            'Invalid 4 Digit year 2' => ['Fun paper 2019 /2020', false],
            '- separator' => ['2019-20', '2019/20'],
            '* separator' => ['2019*20', '2019/20'], // Do we really want to support so many separation characters?
            '? separator' => ['2019?20', '2019/20'],
            '( separator' => ['2019(20', '2019/20'],
            '. separator' => ['2019.20', '2019/20'],
        ];
    }

    /**
     * Tests getting the paper id given the paper title
     * @group assessment
     */
    public function testGetPaperId(): void
    {
        $this->assertEquals($this->pid1['id'], Paper_utils::getPaperId($this->pid1['papertitle']));
        $this->assertNull(Paper_utils::getPaperId('Paper 3'));
    }

    /**
     * Tests attempting to add a question to a paper with an invalid question id
     * @group assessment
     */
    public function testAddQuestionInvalid(): void
    {
        $this->expectException(coding_exception::class);
        Paper_utils::add_question($this->pid1['id'], 0, 2, 1, $this->db);
    }

    /**
     * Tests attempting to add a question to a paper with valid data
     * @group assessment
     */
    public function testAddQuestionValid(): void
    {
        Paper_utils::add_question($this->pid1['id'], $this->question1['id'], 2, 4, $this->db);
        $papers = $this->query(array('table' => 'papers','columns' => array('paper, question, screen, display_pos')));
        $expected = array(
            0 => array(
                'paper' => $this->pid1['id'],
                'question' => $this->question1['id'],
                'screen' => 1,
                'display_pos' => 2
            ),
            1 => array(
                'paper' => $this->pid1['id'],
                'question' => $this->question2['id'],
                'screen' => 2,
                'display_pos' => 3
            ),
            2 => array(
                'paper' => $this->pid1['id'],
                'question' => $this->question3['id'],
                'screen' => 1,
                'display_pos' => 1
            ),
            3 => array(
                'paper' => $this->pid1['id'],
                'question' => $this->question1['id'],
                'screen' => 2,
                'display_pos' => 4
            ),
        );
        $this->assertEquals($expected, $papers);
    }
}
