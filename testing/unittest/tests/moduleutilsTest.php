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
 * Test moduleytils class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class moduleutilstest extends unittestdatabase
{
    /**
     * @var integer Storage for user id in tests
     */
    private $user;

    /**
     * @var integer Storage for module id in tests
     */
    private $module2;

    /**
     * @var integer Storage for school id in tests
     */
    private $school2;

    /**
     * @var array Storage for paper data in tests
     */
    private $pid;

    /**
     * @var array Storage for paper data in tests
     */
    private $pid2;

    /**
     * @var integer Storage for module data in tests
     */
    private $module3;

    /**
     * @var integer Storage for module data in tests
     */
    private $module4;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $this->user = $this->get_user_id('test2');
        $this->module2 = $this->get_module_id('SYSTEM');
        $this->school2 = $this->get_school_id('Training');
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2016, 'academic_year' => '2016/17'));
        $datagenerator = $this->get_datagenerator('modules', 'core');
        $datagenerator->create_enrolment(array('userid' => $this->student['id'], 'moduleid' => $this->module , 'calendar_year' => 2016));
        $datagenerator->create_enrolment(array('userid' => $this->student['id'], 'moduleid' => $this->module2, 'calendar_year' => 2016));
        $datagenerator->create_enrolment(array('userid' => $this->user, 'moduleid' => $this->module2, 'calendar_year' => 2016));
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $this->pid = $datagenerator->create_paper(array('papertitle' => 'Test create formative',
            'calendaryear' => 2016,
            'modulename' => 'Training Module',
            'paperowner' => 'admin',
            'papertype' => '0',
            'duration' => 60,
            'labs' => '1'));
        $this->pid2 = $datagenerator->create_paper(array('papertitle' => 'Test create formative 2',
            'calendaryear' => 2016,
            'modulename' => array('Training Module', 'Online Help'),
            'paperowner' => 'admin',
            'papertype' => '0',
            'duration' => 60,
            'labs' => '1'));
        $datagenerator = $this->get_datagenerator('modules', 'core');
        $this->module3 = $datagenerator->create_module(
            array(
                'moduleid' => 'TEST',
                'fullname' => 'Another test module',
                'schoolID' => $this->school2
            )
        );
        $this->module4 = $datagenerator->create_module(
            array(
                'moduleid' => 'TEST2',
                'fullname' => 'Yet another test module',
                'schoolID' => $this->school2,
                'academic_year_start' => '03/01'
            )
        );
    }

    /**
     * Test get modules paper assoicated with
     * @group gradebook
     */
    public function test_get_modules_for_paper()
    {
        $modules = array(array('moduleid' => 'TRAIN', 'fullname' => 'Training Module', 'externalid' => 'abc123def'), array('moduleid' => 'SYSTEM', 'fullname' => 'Online Help', 'externalid' => null));
        $this->assertEquals($modules, module_utils::get_modules_for_paper($this->pid2['id'], $this->student['id'], $this->db));
        $modules = array(array('moduleid' => 'TRAIN', 'fullname' => 'Training Module', 'externalid' => 'abc123def'));
        $this->assertEquals($modules, module_utils::get_modules_for_paper($this->pid['id'], $this->student['id'], $this->db));
        $modules = array();
        $this->assertEquals($modules, module_utils::get_modules_for_paper($this->pid['id'], $this->user, $this->db));
    }

    /**
     * Test get full details using internal id
     * @group modules
     */
    public function test_get_full_details_internalid()
    {
        $detailsarray = array('idMod' => $this->module2,
            'moduleid' => 'SYSTEM',
            'fullname' => 'Online Help',
            'school' => 'Training',
            'active' => 1,
            'vle_api' => null,
            'checklist' => 'peer,external,stdset,mapping',
            'sms' => '',
            'selfenroll' => 0,
            'schoolid' => 2,
            'neg_marking' => null,
            'ebel_grid_template' => null,
            'timed_exams' => 0,
            'exam_q_feedback' => 1,
            'add_team_members' => 1,
            'map_level' => 7,
            'academic_year_start' => '07/01',
            'externalid' => null,
            'syncpreviousyear' => 0);
        $details = module_utils::get_full_details('internal', $this->module2, $this->db);
        $this->assertEquals($detailsarray, $details);
    }

    /**
     * Test get full details using wrapper function get_full_details_by_ID
     * @group modules
     */
    public function test_get_full_details_by_ID()
    {
        $detailsarray = array('idMod' => $this->module2,
            'moduleid' => 'SYSTEM',
            'fullname' => 'Online Help',
            'school' => 'Training',
            'active' => 1,
            'vle_api' => null,
            'checklist' => 'peer,external,stdset,mapping',
            'sms' => '',
            'selfenroll' => 0,
            'schoolid' => $this->school2,
            'neg_marking' => null,
            'ebel_grid_template' => null,
            'timed_exams' => 0,
            'exam_q_feedback' => 1,
            'add_team_members' => 1,
            'map_level' => 7,
            'academic_year_start' => '07/01',
            'externalid' => null,
            'syncpreviousyear' => 0);
        $details = module_utils::get_full_details_by_ID($this->module2, $this->db);
        $this->assertEquals($detailsarray, $details);
    }

    /**
     * Test get full details using external id
     * @group modules
     */
    public function test_get_full_details_externalid()
    {
        $detailsarray = array('idMod' => $this->module,
            'moduleid' => 'TRAIN',
            'fullname' => 'Training Module',
            'school' => 'Training',
            'active' => 1,
            'vle_api' => '',
            'checklist' => 'mapping',
            'sms' => 'test ExamSys api',
            'selfenroll' => 0,
            'schoolid' => $this->school2,
            'neg_marking' => null,
            'ebel_grid_template' => null,
            'timed_exams' => 0,
            'exam_q_feedback' => 1,
            'add_team_members' => 1,
            'map_level' => 7,
            'academic_year_start' => '07/01',
            'externalid' => 'abc123def',
            'syncpreviousyear' => 1);
        $details = module_utils::get_full_details('external', 'abc123def', $this->db, 'test ExamSys api');
        $this->assertEquals($detailsarray, $details);
    }

    /**
     * Test get full details using invalid id type
     * @group modules
     */
    public function test_get_full_details_invalid()
    {
        $details = module_utils::get_full_details('placeholder', $this->module2, $this->db);
        $this->assertFalse($details);
    }

    /**
     * Test get modules with prev year sync enabled
     * @group modules
     */
    public function test_get_sync_previous_year_modules()
    {
        $expected = array('abc123def');
        $actual = module_utils::get_sync_previous_year_modules('test ExamSys api');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test get check if previous year enabled for module
     * @group modules
     */
    public function test_check_sync_previous_year()
    {
        $actual = module_utils::check_sync_previous_year($this->module2);
        $expected = 0;
        $this->assertEquals($expected, $actual);
        $actual = module_utils::check_sync_previous_year($this->module);
        $expected = 1;
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test get accessibility info
     * @group modules
     */
    public function testStudentAccessiblityInfo()
    {
        $modules = array($this->module, $this->module2);
        $actual = module_utils::getStudentAccessiblityInfo(implode(',', $modules), 2016);
        $expected[$this->student['id']]['user_ID'] = $this->student['id'];
        $expected[$this->student['id']]['surname'] = $this->student['surname'];
        $expected[$this->student['id']]['first_names'] = $this->student['first_names'];
        $expected[$this->student['id']]['title'] = $this->student['title'];
        $et = UserUtils::getExtraTime($this->student['id'], $this->db);
        $expected[$this->student['id']]['extra_time_percentage'] = $et['extratime'];
        $expected[$this->student['id']]['medical'] = null;
        $expected[$this->student['id']]['breaks'] = 'yes';
        $expected[$this->user]['user_ID'] = $this->user;
        $expected[$this->user]['surname'] = 'User2';
        $expected[$this->user]['first_names'] = 'A';
        $expected[$this->user]['title'] = 'Dr';
        $expected[$this->user]['extra_time_percentage'] = null;
        $expected[$this->user]['medical'] = null;
        $expected[$this->user]['breaks'] = null;
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test get module year start
     * @group modules
     */
    public function testGetAcademicYearStart(): void
    {
        // Default.
        $this->assertEquals(
            $this->config->get_setting('core', 'system_academic_year_start'),
            module_utils::getAcademicYearStart($this->module3['id'])
        );
        // Module set.
        $this->assertEquals(
            $this->module4['academic_year_start'],
            module_utils::getAcademicYearStart($this->module4['id'])
        );
        // Invalid module.
        $this->expectExceptionMessage('You must provide a valid module');
        module_utils::getAcademicYearStart(9999999999);
    }
}
