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
 * Test assessment class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class assessmenttest extends unittestdatabase
{

    /**
     * @var integer Storage for module id in tests
     */
    private $module2;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $this->module2 = $this->get_module_id('SYSTEM');
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2016, 'academic_year' => '2016/17'));
        $datagenerator = $this->get_datagenerator('labs', 'core');
        $datagenerator->create_campus(array('name' => 'Test Campus', 'isdefault' => 1));
        $datagenerator->create_lab(array('name' => 'Test lab', 'building' => 'Test building', 'room' => 1));
    }

    /**
     * Create a paper
     * @param string $papertitle paper title
     * @param integer $papertype paper type
     * @return paperid
     */
    private function create_paper($papertitle, $papertype)
    {
        $assessment = new assessment($this->db, $this->config);
        $paperowner = $this->admin['id'];
        $startdate = '2016-01-25 09:00:00';
        $enddate = '2016-01-25 10:00:00';
        $labs = '1';
        $duration = 60;
        $session = 2016;
        $modules = array($this->module);
        $timezone = 'Europe/London';
        return $assessment->create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone);
    }

    /**
     * Test assessemnt type values
     * @group assessment
     */
    public function test_get_type_value()
    {
        $assessment = new assessment($this->db, $this->config);
        $this->assertEquals($assessment::TYPE_FORMATIVE, $assessment->get_type_value('formative'));
        $this->assertEquals($assessment::TYPE_PROGRESS, $assessment->get_type_value('progress'));
        $this->assertEquals($assessment::TYPE_SUMMATIVE, $assessment->get_type_value('summative'));
        $this->assertEquals($assessment::TYPE_SURVEY, $assessment->get_type_value('survey'));
        $this->assertEquals($assessment::TYPE_OSCE, $assessment->get_type_value('osce'));
        $this->assertEquals($assessment::TYPE_OFFLINE, $assessment->get_type_value('offline'));
        $this->assertEquals($assessment::TYPE_PEERREVIEW, $assessment->get_type_value('peer_review'));
        $this->assertFalse($assessment->get_type_value('test'));
    }

    /**
     * Test assessemnt creation
     * @group assessment
     */
    public function test_create()
    {
        // Test summative paper creation- SUCCESS.
        $pid = $this->create_paper('Test create formative', 0);
        $this->assertIsInt($pid);
        // Test properties table is as expected.
        $queryTable = $this->query(array('columns' => array('paper_title', 'start_date', 'end_date', 'exam_duration',
            'calendar_year', 'timezone', 'paper_ownerID', 'labs', 'paper_type'), 'table' => 'properties'));
        $expectedTable = array(
            0 => array(
                'paper_title' => 'Test create formative',
                'start_date' => '2016-01-25 09:00:00',
                'end_date' => '2016-01-25 10:00:00',
                'exam_duration' => 60,
                'calendar_year' => 2016,
                'timezone' => 'Europe/London',
                'paper_ownerID' => $this->admin['id'],
                'labs' => '1',
                'paper_type' => '0'
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
        // Test properties_modules table is as expected.
        $queryTable = $this->query(array('table' => 'properties_modules'));
        $expectedTable = array(
            0 => array(
                'property_id' => $pid,
                'idMod' => $this->module,
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test unique paper title on paper creation
     * @group assessment
     */
    public function test_create_unique_paper_title()
    {
        $this->create_paper('Test schedule summative', 2);
        $this->expectExceptionMessage('NON_UNIQUE_TITLE');
        $this->create_paper('Test schedule summative', 2);
    }

    /**
     * Test unique paper title on paper creation - external system
     * @group assessment
     */
    public function test_create_unique_paper_title_ext()
    {
        $this->create_paper('Test schedule summative', 2);
        $assessment = new assessment($this->db, $this->config);
        $paperowner = $this->admin['id'];
        $startdate = '2016-01-25 09:00:00';
        $enddate = '2016-01-25 10:00:00';
        $labs = '1';
        $duration = 60;
        $session = 2016;
        $modules = array(1);
        $timezone = 'Europe/London';
        $externalid = 'A-000000001';
        $papertitle = 'Test schedule summative';
        $papertype = 2;
        $this->assertIsInt($assessment->create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $externalid));
    }

    /**
     * Test valid paper type on paper creation
     * @group assessment
     */
    public function test_create_valid_paper_type()
    {
        $this->expectExceptionMessage('INVALID_PAPER_TYPE');
        $this->create_paper('Test schedule summative', 1000);
    }

    /**
     * Test valid paper owner on paper creation
     * @group assessment
     */
    public function test_create_valid_owner()
    {
        $assessment = new assessment($this->db, $this->config);
        $papertitle = 'Test schedule summative';
        $papertype = 2;
        $paperowner = 999;
        $startdate = '2016-01-25 09:00:00';
        $enddate = '2016-01-25 10:00:00';
        $labs = '1';
        $duration = 60;
        $session = 2016;
        $modules = array(1);
        $timezone = 'Europe/London';
        $this->expectExceptionMessage('INVALID_USER');
        $assessment->create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone);
    }

    /**
     * Test valid paper owner type on paper creation
     * @group assessment
     */
    public function test_create_valid_owner_role()
    {
        $assessment = new assessment($this->db, $this->config);
        $papertitle = 'Test schedule summative';
        $paperowner = $this->student['id'];
        $papertype = 2;
        $startdate = '2016-01-25 09:00:00';
        $enddate = '2016-01-25 10:00:00';
        $labs = '1';
        $duration = 60;
        $session = 2016;
        $modules = array(1);
        $timezone = 'Europe/London';
        $this->expectExceptionMessage('INVALID_ROLE');
        $assessment->create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone);
    }

    /**
     * Test valid session on paper creation
     * @group assessment
     */
    public function test_create_valid_session()
    {
        $assessment = new assessment($this->db, $this->config);
        $papertitle = 'Test schedule summative';
        $paperowner = $this->admin['id'];
        $papertype = 2;
        $startdate = '2016-01-25 09:00:00';
        $enddate = '2016-01-25 10:00:00';
        $labs = '1';
        $duration = 60;
        $session = 0000;
        $modules = array(1);
        $timezone = 'Europe/London';
        $this->expectExceptionMessage('INVALID_SESSION');
        $assessment->create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone);
    }

    /**
     * Test valid dates on paper creation
     * @group assessment
     */
    public function test_create_valid_dates()
    {
        $assessment = new assessment($this->db, $this->config);
        $papertitle = 'Test schedule formative';
        $paperowner = $this->admin['id'];
        $papertype = 0;
        $enddate = '2016-01-25 09:00:00';
        $startdate = '2016-01-25 10:00:00';
        $labs = '1';
        $duration = 60;
        $session = 2016;
        $modules = array(1);
        $timezone = 'Europe/London';
        $this->expectExceptionMessage('INVALID_DATES');
        $assessment->create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone);
    }

    /**
     * Test no modules on paper creation
     * @group assessment
     */
    public function test_create_no_modules()
    {
        $assessment = new assessment($this->db, $this->config);
        $papertitle = 'Test schedule formative';
        $paperowner = $this->admin['id'];
        $papertype = 0;
        $enddate = '2016-01-25 10:00:00';
        $startdate = '2016-01-25 09:00:00';
        $labs = '1';
        $duration = 60;
        $session = 2016;
        $modules = array();
        $timezone = 'Europe/London';
        $this->expectExceptionMessage('INVALID_NO_MODULES');
        $assessment->create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone);
    }

    /**
     * Test assessemnt update
     * @group assessment
     */
    public function test_update()
    {
        // Test update paper - SUCCESS
        $this->config->set_setting('cfg_summative_mgmt', false, \Config::BOOLEAN);
        $papertitle = 'Test update summative';
        $papertype = 2;
        $id = $this->create_paper($papertitle, $papertype);
        $assessment = new assessment($this->db, $this->config);
        $paperowner = $this->admin['id'];
        $startdate = '2016-01-25 09:00:00';
        $enddate = '2016-01-25 10:30:00';
        $labs = '1';
        $duration = 90;
        $session = 2016;
        $modules = array($this->module2);
        $timezone = 'Europe/London';
        $userid = $this->admin['id'];
        $this->assertTrue($assessment->update($id, $papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid));
        // Test update summative max duration too large - SUCCESS
        $duration = 1000;
        $this->assertTrue($assessment->update($id, $papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid));
        // Test schedule table is as expected.
        $queryTable = $this->query(array('columns' => array('start_date', 'end_date', 'exam_duration'), 'table' => 'properties'));
        $expectedTable = array(
            0 => array(
                'start_date' => '2016-01-25 09:00:00',
                'end_date' => '2016-01-25 10:30:00',
                'exam_duration' => 779
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
        // Test update summative max duration too small - SUCCESS
        $duration = -1;
        $this->assertTrue($assessment->update($id, $papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid));
        // Test properties table is as expected.
        $queryTable = $this->query(array('columns' => array('start_date', 'end_date', 'exam_duration'), 'table' => 'properties'));
        $expectedTable = array(
            0 => array(
                'start_date' => '2016-01-25 09:00:00',
                'end_date' => '2016-01-25 10:30:00',
                'exam_duration' => 0
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
        // Test properties_modules table is as expected.
        $queryTable = $this->query(array('columns' => array('property_id', 'idMod'), 'table' => 'properties_modules'));
        $expectedTable = array(
            0 => array(
                'property_id' => $id,
                'idMod' => $this->module2,
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test unique paper title on paper update
     * @group assessment
     */
    public function test_update_unique_paper_title()
    {
        $papertitle = 'Test update summative';
        $papertype = 2;
        $id = $this->create_paper($papertitle, $papertype);
        $this->create_paper('Test schedule summative 2', $papertype);
        $assessment = new assessment($this->db, $this->config);
        $newtitle = 'Test schedule summative 2';
        $paperowner = $this->admin['id'];
        $startdate = '2016-01-25 09:00:00';
        $enddate = '2016-01-25 10:30:00';
        $labs = '1';
        $duration = 90;
        $session = 2016;
        $modules = array(1);
        $timezone = 'Europe/London';
        $userid = 1;
        $this->expectExceptionMessage('NON_UNIQUE_TITLE');
        $assessment->update($id, $newtitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid);
    }

    /**
     * Test unique paper title on paper update - external system
     * @group assessment
     */
    public function test_update_unique_paper_title_ext()
    {
        $papertitle = 'Test update summative';
        $papertype = 2;
        $id = $this->create_paper($papertitle, $papertype);
        $this->create_paper('Test schedule summative 2', $papertype);
        $assessment = new assessment($this->db, $this->config);
        $newtitle = 'Test schedule summative 2';
        $paperowner = $this->admin['id'];
        $startdate = '2016-01-25 09:00:00';
        $enddate = '2016-01-25 10:30:00';
        $labs = '1';
        $duration = 90;
        $session = 2016;
        $modules = array(1);
        $timezone = 'Europe/London';
        $userid = $this->admin['id'];
        $externalid = 'A-000000001';
        $this->assertTrue($assessment->update($id, $newtitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid, $externalid));
    }

    /**
     * Test valid paper owner on paper update
     * @group assessment
     */
    public function test_update_valid_owner()
    {
        $papertitle = 'Test update summative';
        $papertype = 2;
        $id = $this->create_paper($papertitle, $papertype);
        $assessment = new assessment($this->db, $this->config);
        $paperowner = 999;
        $startdate = '2016-01-25 09:00:00';
        $enddate = '2016-01-25 10:30:00';
        $labs = '1';
        $duration = 90;
        $session = 2016;
        $modules = array(1);
        $timezone = 'Europe/London';
        $userid = $this->admin['id'];
        $this->expectExceptionMessage('INVALID_USER');
        $assessment->update($id, $papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid);
    }

    /**
     * Test valid paper owner type on paper update
     * @group assessment
     */
    public function test_update_valid_owner_role()
    {
        $papertitle = 'Test update summative';
        $papertype = 2;
        $id = $this->create_paper($papertitle, $papertype);
        $assessment = new assessment($this->db, $this->config);
        $paperowner = $this->student['id'];
        $startdate = '2016-01-25 09:00:00';
        $enddate = '2016-01-25 10:30:00';
        $labs = '1';
        $duration = 90;
        $session = 2016;
        $modules = array(1);
        $timezone = 'Europe/London';
        $userid = $this->admin['id'];
        $this->expectExceptionMessage('INVALID_ROLE');
        $assessment->update($id, $papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid);
    }

    /**
     * Test valid session on paper update
     * @group assessment
     */
    public function test_update_valid_session()
    {
        $papertitle = 'Test update summative';
        $papertype = 2;
        $id = $this->create_paper($papertitle, $papertype);
        $assessment = new assessment($this->db, $this->config);
        $paperowner = $this->admin['id'];
        $startdate = '2016-01-25 09:00:00';
        $enddate = '2016-01-25 10:30:00';
        $labs = '1';
        $duration = 90;
        $session = 0000;
        $modules = array(1);
        $timezone = 'Europe/London';
        $userid = $this->admin['id'];
        $this->expectExceptionMessage('INVALID_SESSION');
        $assessment->update($id, $papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid);
    }

    /**
     * Test valid dates on paper update
     * @group assessment
     */
    public function test_update_valid_dates()
    {
        $this->config->set_setting('cfg_summative_mgmt', false, \Config::BOOLEAN);
        $papertitle = 'Test update summative';
        $papertype = 2;
        $id = $this->create_paper($papertitle, $papertype);
        $assessment = new assessment($this->db, $this->config);
        $paperowner = $this->admin['id'];
        $enddate = '2016-01-25 09:00:00';
        $startdate = '2016-01-25 10:30:00';
        $labs = '1';
        $duration = 90;
        $session = 2016;
        $modules = array(1);
        $timezone = 'Europe/London';
        $userid = $this->admin['id'];
        $this->expectExceptionMessage('INVALID_DATES');
        $assessment->update($id, $papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid);
    }

    /**
     * Test assessemnt scheduling
     * @group assessment
     */
    public function test_schedule()
    {
        $paperid = $this->create_paper('Test schedule summative', 2);
        $month = 1;
        $barriers = 0;
        $cohort_size = '11-20';
        $notes = 'Some interesting notes on this exam';
        $sittings = 1;
        $campus = 'Test campus';
        $assessment = new assessment($this->db, $this->config);
        // Test summative scheduke - SUCCESS.
        $this->assertIsInt($assessment->schedule($paperid, $month, $barriers, $cohort_size, $notes, $sittings, $campus));
        // Test formative schedule - FAIL.
        $title = 'Test schedule formative';
        $paperid2 = $this->create_paper('Test schedule formative', 0);
        $this->assertFalse($assessment->schedule($paperid2, $month, $barriers, $cohort_size, $notes, $sittings, $campus));
        // Test summative schedule unkown cohorts size - SUCCESS.
        $paperid3 = $this->create_paper('Test schedule summative 2', 2);
        $cohort_size = 'unknown';
        $this->assertIsInt($assessment->schedule($paperid3, $month, $barriers, $cohort_size, $notes, $sittings, $campus));
        // Test summative schedule max sittings to large - SUCCESS.
        $paperid4 = $this->create_paper('Test schedule summative 3', 2);
        $cohort_size = '11-20';
        $sittings = 1000;
        $this->assertIsInt($assessment->schedule($paperid4, $month, $barriers, $cohort_size, $notes, $sittings, $campus));
        // Test summative schedule max sittings to small - SUCCESS.
        $paperid5 = $this->create_paper('Test schedule summative 4', 2);
        $sittings = -1;
        $this->assertIsInt($assessment->schedule($paperid5, $month, $barriers, $cohort_size, $notes, $sittings, $campus));
        // Test schedule table is as expected.
        $queryTable = $this->query(array('table' => 'scheduling', 'columns' => array('paperID', 'period', 'barriers_needed', 'cohort_size', 'notes', 'sittings', 'campus')));
        $expectedTable = array(
            0 => array(
                'paperID' => $paperid,
                'period' => 1,
                'barriers_needed' => 0,
                'cohort_size' => '11-20',
                'notes' => 'Some interesting notes on this exam',
                'sittings' => 1,
                'campus' => 'Test campus'
            ),
            1 => array(
                'paperID' => $paperid3,
                'period' => 1,
                'barriers_needed' => 0,
                'cohort_size' => '<whole cohort>',
                'notes' => 'Some interesting notes on this exam',
                'sittings' => 1,
                'campus' => 'Test campus'
            ),
            2 => array(
                'paperID' => $paperid4,
                'period' => 1,
                'barriers_needed' => 0,
                'cohort_size' => '11-20',
                'notes' => 'Some interesting notes on this exam',
                'sittings' => 6,
                'campus' => 'Test campus'
            ),
            3 => array(
                'paperID' => $paperid5,
                'period' => 1,
                'barriers_needed' => 0,
                'cohort_size' => '11-20',
                'notes' => 'Some interesting notes on this exam',
                'sittings' => 1,
                'campus' => 'Test campus'
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test assessemnt date setup
     * @group assessment
     */
    public function test_setup_dates()
    {
        $assessment = new assessment($this->db, $this->config);
        // Test London.
        $datesarray = $assessment->setup_start_end_dates(0, '2016-01-25 09:00:00', '2016-01-25 12:00:00', 'Europe/London');
        $this->assertEquals('2016-01-25 09:00:00', $datesarray[0]);
        $this->assertEquals('2016-01-25 12:00:00', $datesarray[1]);
        // Test Kuwait.
        $datesarray = $assessment->setup_start_end_dates(0, '2016-01-25 09:00:00', '2016-01-25 12:00:00', 'Asia/Kuwait');
        $this->assertEquals('2016-01-25 06:00:00', $datesarray[0]);
        $this->assertEquals('2016-01-25 09:00:00', $datesarray[1]);
        // Test Honolulu.
        $datesarray = $assessment->setup_start_end_dates(0, '2016-01-25 09:00:00', '2016-01-25 12:00:00', 'Pacific/Honolulu');
        $this->assertEquals('2016-01-25 19:00:00', $datesarray[0]);
        $this->assertEquals('2016-01-25 22:00:00', $datesarray[1]);
        // Test London non leap year feb 29th.
        $datesarray = $assessment->setup_start_end_dates(0, '2017-02-29 09:00:00', '2017-02-29 12:00:00', 'Europe/London');
        $this->assertEquals('2017-03-01 09:00:00', $datesarray[0]);
        $this->assertEquals('2017-03-01 12:00:00', $datesarray[1]);
    }
}
