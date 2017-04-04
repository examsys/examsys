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
 * Test assessment class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class assessmenttest extends unittestdatabase {
    /**
     * Create a paper
     * @parmam string $papertitle paper title
     * @param integer $papertype paper type
     * @return paperid 
     */
    private function create_paper($papertitle, $papertype) {
        $assessment = new assessment($this->db, $this->config);
        $paperowner = 1;
        $startdate = "2016-01-25 09:00:00";
        $enddate = "2016-01-25 10:00:00";
        $labs = "1";
        $duration = 60;
        $session = 2016;
        $modules = array(1);
        $timezone = "Europe/London";
        return $assessment->create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone);
    }
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "assessmentTest" . DIRECTORY_SEPARATOR . "assessment.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "assessmentTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test assessemnt type values
     * @group assessment
     */
    public function test_get_type_value() {
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
    public function test_create() {
        // Test summative paper creation- SUCCESS.
        $this->assertEquals(1, $this->create_paper("Test create formative", 0));
        // Test properties table is as expected.
        $queryTable = $this->getConnection()->createQueryTable('properties', 'SELECT property_id, paper_title, start_date, end_date, exam_duration,
            calendar_year, timezone, paper_ownerID, labs, paper_type FROM properties');
        $expectedTable = $this->get_expected_data_set('createproperties')->getTable("properties");  
        $this->assertTablesEqual($expectedTable, $queryTable); 
        // Test properties_modules table is as expected.
        $queryTable = $this->getConnection()->createQueryTable('properties_modules', 'SELECT * FROM properties_modules');
        $expectedTable = $this->get_expected_data_set('createproperties')->getTable("properties_modules");
        $this->assertTablesEqual($expectedTable, $queryTable); 
    }
    /**
     * Test unique paper title on paper creation
     * @group assessment
     */
    public function test_create_unique_paper_title() {
        $this->create_paper("Test schedule summative", 2);
        try {
            $this->create_paper("Test schedule summative", 2);
        } catch (Exception $e) {
            if ($e->getMessage() == 'NON_UNIQUE_TITLE') {
                return;
            }
            $this->fail('Exception NON_UNIQUE_TITLE expected but ' . $e->getMessage() . ' thrown instead.');
        }
        $this->fail('Exception NON_UNIQUE_TITLE not thrown.');
    }
    /**
     * Test unique paper title on paper creation - external system
     * @group assessment
     */
    public function test_create_unique_paper_title_ext() {
        $this->create_paper("Test schedule summative", 2);
        $assessment = new assessment($this->db, $this->config);
        $paperowner = 1;
        $startdate = "2016-01-25 09:00:00";
        $enddate = "2016-01-25 10:00:00";
        $labs = "1";
        $duration = 60;
        $session = 2016;
        $modules = array(1);
        $timezone = "Europe/London";
        $externalid = "A-000000001";
        $papertitle = "Test schedule summative";
        $papertype = 2;
        $this->assertEquals(2, $assessment->create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $externalid));
    }
    /**
     * Test valid paper type on paper creation
     * @group assessment
     */
    public function test_create_valid_paper_type() {
        try {
            $this->create_paper("Test schedule summative", 1000);
        } catch (Exception $e) {
            if ($e->getMessage() == 'INVALID_PAPER_TYPE') {
                return;
            }
            $this->fail('Exception INVALID_PAPER_TYPE expected but ' . $e->getMessage() . ' thrown instead.');
        }
        $this->fail('Exception INVALID_PAPER_TYPE not thrown.');
    }
    /**
     * Test valid paper owner on paper creation
     * @group assessment
     */
    public function test_create_valid_owner() {
        $assessment = new assessment($this->db, $this->config);
        $papertitle = "Test schedule summative";
        $papertype = 2;
        $paperowner = 999;
        $startdate = "2016-01-25 09:00:00";
        $enddate = "2016-01-25 10:00:00";
        $labs = "1";
        $duration = 60;
        $session = 2016;
        $modules = array(1);
        $timezone = "Europe/London";
        try {
            $assessment->create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone);
        } catch (Exception $e) {
            if ($e->getMessage() == 'INVALID_USER') {
                return;
            }
            $this->fail('Exception INVALID_USER expected but ' . $e->getMessage() . ' thrown instead.');
        }
        $this->fail('Exception INVALID_USER not thrown.');
    }
    /**
     * Test valid paper owner type on paper creation
     * @group assessment
     */
    public function test_create_valid_owner_role() {
        $assessment = new assessment($this->db, $this->config);
        $papertitle = "Test schedule summative";
        $paperowner = 1000;
        $papertype = 2;
        $startdate = "2016-01-25 09:00:00";
        $enddate = "2016-01-25 10:00:00";
        $labs = "1";
        $duration = 60;
        $session = 2016;
        $modules = array(1);
        $timezone = "Europe/London";
        try {
            $assessment->create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone);
        } catch (Exception $e) {
            if ($e->getMessage() == 'INVALID_ROLE') {
                return;
            }
            $this->fail('Exception INVALID_ROLE expected but ' . $e->getMessage() . ' thrown instead.');
        }
        $this->fail('Exception INVALID_ROLE not thrown.');
    }
    /**
     * Test valid session on paper creation
     * @group assessment
     */
    public function test_create_valid_session() {
        $assessment = new assessment($this->db, $this->config);
        $papertitle = "Test schedule summative";
        $paperowner = 1;
        $papertype = 2;
        $startdate = "2016-01-25 09:00:00";
        $enddate = "2016-01-25 10:00:00";
        $labs = "1";
        $duration = 60;
        $session = 0000;
        $modules = array(1);
        $timezone = "Europe/London";
        try {
            $assessment->create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone);
        } catch (Exception $e) {
            if ($e->getMessage() == 'INVALID_SESSION') {
                return;
            }
            $this->fail('Exception INVALID_SESSION expected but ' . $e->getMessage() . ' thrown instead.');
        }
        $this->fail('Exception INVALID_SESSION not thrown.');
    }
    /**
     * Test valid dates on paper creation
     * @group assessment
     */
    public function test_create_valid_dates() {
        $assessment = new assessment($this->db, $this->config);
        $papertitle = "Test schedule formative";
        $paperowner = 1;
        $papertype = 0;
        $enddate = "2016-01-25 09:00:00";
        $startdate = "2016-01-25 10:00:00";
        $labs = "1";
        $duration = 60;
        $session = 2016;
        $modules = array(1);
        $timezone = "Europe/London";
        try {
            $assessment->create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone);
        } catch (Exception $e) {
            if ($e->getMessage() == 'INVALID_DATES') {
                return;
            }
            $this->fail('Exception INVALID_DATES expected but ' . $e->getMessage() . ' thrown instead.');
        }
        $this->fail('Exception INVALID_DATES not thrown.');
    }
    /**
     * Test no modules on paper creation
     * @group assessment
     */
    public function test_create_no_modules() {
        $assessment = new assessment($this->db, $this->config);
        $papertitle = "Test schedule formative";
        $paperowner = 1;
        $papertype = 0;
        $enddate = "2016-01-25 10:00:00";
        $startdate = "2016-01-25 09:00:00";
        $labs = "1";
        $duration = 60;
        $session = 2016;
        $modules = array();
        $timezone = "Europe/London";
        try {
            $assessment->create($papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone);
        } catch (Exception $e) {
            if ($e->getMessage() == 'INVALID_NO_MODULES') {
                return;
            }
            $this->fail('Exception INVALID_NO_MODULES expected but ' . $e->getMessage() . ' thrown instead.');
        }
        $this->fail('Exception INVALID_NO_MODULES not thrown.');
    }
    /**
     * Test assessemnt update
     * @group assessment
     */
    public function test_update() {
        // Test update paper - SUCCESS
        $this->config->set('cfg_summative_mgmt', false);
        $papertitle = "Test update summative";
        $papertype = 2;
        $id = $this->create_paper($papertitle, $papertype);
        $assessment = new assessment($this->db, $this->config);
        $paperowner = 1;
        $startdate = "2016-01-25 09:00:00";
        $enddate = "2016-01-25 10:30:00";
        $labs = "1";
        $duration = 90;
        $session = 2016;
        $modules = array(2);
        $timezone = "Europe/London";
        $userid = 1;
        $this->assertTrue($assessment->update($id, $papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid));
        // Test update summative max duration too large - SUCCESS
        $duration = 1000;
        $this->assertTrue($assessment->update($id, $papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid));
        // Test schedule table is as expected.
        $queryTable = $this->getConnection()->createQueryTable('properties', 'SELECT property_id, start_date, end_date, exam_duration FROM properties');
        $expectedTable = $this->get_expected_data_set('updatedproperties')->getTable("properties");
        $this->assertTablesEqual($expectedTable, $queryTable);
        // Test update summative max duration too small - SUCCESS
        $duration = -1;
        $this->assertTrue($assessment->update($id, $papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid));
        // Test properties table is as expected.
        $queryTable = $this->getConnection()->createQueryTable('properties', 'SELECT property_id, start_date, end_date, exam_duration FROM properties');
        $expectedTable = $this->get_expected_data_set('updatedproperties2')->getTable("properties");
        $this->assertTablesEqual($expectedTable, $queryTable);
        // Test properties_modules table is as expected.
        $queryTable = $this->getConnection()->createQueryTable('properties_modules', 'SELECT property_id, idMod FROM properties_modules');
        $expectedTable = $this->get_expected_data_set('updatedproperties2')->getTable("properties_modules");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    /**
     * Test unique paper title on paper update
     * @group assessment
     */
    public function test_update_unique_paper_title() {
        $papertitle = "Test update summative";
        $papertype = 2;
        $id = $this->create_paper($papertitle, $papertype);
        $this->create_paper("Test schedule summative 2", $papertype);
        $assessment = new assessment($this->db, $this->config);
        $newtitle = "Test schedule summative 2";
        $paperowner = 1;
        $startdate = "2016-01-25 09:00:00";
        $enddate = "2016-01-25 10:30:00";
        $labs = "1";
        $duration = 90;
        $session = 2016;
        $modules = array(1);
        $timezone = "Europe/London";
        $userid = 1;
        try {
            $assessment->update($id, $newtitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid);
        } catch (Exception $e) {
            if ($e->getMessage() == 'NON_UNIQUE_TITLE') {
                return;
            }
            $this->fail('Exception NON_UNIQUE_TITLE expected but ' . $e->getMessage() . ' thrown instead.');
        }
        $this->fail('Exception NON_UNIQUE_TITLE not thrown.');
    }
    /**
     * Test unique paper title on paper update - external system
     * @group assessment
     */
    public function test_update_unique_paper_title_ext() {
        $papertitle = "Test update summative";
        $papertype = 2;
        $id = $this->create_paper($papertitle, $papertype);
        $this->create_paper("Test schedule summative 2", $papertype);
        $assessment = new assessment($this->db, $this->config);
        $newtitle = "Test schedule summative 2";
        $paperowner = 1;
        $startdate = "2016-01-25 09:00:00";
        $enddate = "2016-01-25 10:30:00";
        $labs = "1";
        $duration = 90;
        $session = 2016;
        $modules = array(1);
        $timezone = "Europe/London";
        $userid = 1;
        $externalid = "A-000000001";
        $this->assertTrue($assessment->update($id, $newtitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid, $externalid));
    }
    /**
     * Test valid paper owner on paper update
     * @group assessment
     */
    public function test_update_valid_owner() {
        $papertitle = "Test update summative";
        $papertype = 2;
        $id = $this->create_paper($papertitle, $papertype);
        $assessment = new assessment($this->db, $this->config);
        $paperowner = 999;
        $startdate = "2016-01-25 09:00:00";
        $enddate = "2016-01-25 10:30:00";
        $labs = "1";
        $duration = 90;
        $session = 2016;
        $modules = array(1);
        $timezone = "Europe/London";
        $userid = 1;
        try {
            $assessment->update($id, $papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid);
        } catch (Exception $e) {
            if ($e->getMessage() == 'INVALID_USER') {
                return;
            }
            $this->fail('Exception INVALID_USER expected but ' . $e->getMessage() . ' thrown instead.');
        }
        $this->fail('Exception INVALID_USER not thrown.');
    }
    /**
     * Test valid paper owner type on paper update
     * @group assessment
     */
    public function test_update_valid_owner_role() {
        $papertitle = "Test update summative";
        $papertype = 2;
        $id = $this->create_paper($papertitle, $papertype);
        $assessment = new assessment($this->db, $this->config);
        $paperowner = 1000;
        $startdate = "2016-01-25 09:00:00";
        $enddate = "2016-01-25 10:30:00";
        $labs = "1";
        $duration = 90;
        $session = 2016;
        $modules = array(1);
        $timezone = "Europe/London";
        $userid = 1;
        try {
            $assessment->update($id, $papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid);
        } catch (Exception $e) {
            if ($e->getMessage() == 'INVALID_ROLE') {
                return;
            }
            $this->fail('Exception INVALID_ROLE expected but ' . $e->getMessage() . ' thrown instead.');
        }
        $this->fail('Exception INVALID_ROLE not thrown.');
    }
    /**
     * Test valid session on paper update
     * @group assessment
     */
    public function test_update_valid_session() {
        $papertitle = "Test update summative";
        $papertype = 2;
        $id = $this->create_paper($papertitle, $papertype);
        $assessment = new assessment($this->db, $this->config);
        $paperowner = 1;
        $startdate = "2016-01-25 09:00:00";
        $enddate = "2016-01-25 10:30:00";
        $labs = "1";
        $duration = 90;
        $session = 0000;
        $modules = array(1);
        $timezone = "Europe/London";
        $userid = 1;
        try {
            $assessment->update($id, $papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid);
        } catch (Exception $e) {
            if ($e->getMessage() == 'INVALID_SESSION') {
                return;
            }
            $this->fail('Exception INVALID_SESSION expected but ' . $e->getMessage() . ' thrown instead.');
        }
        $this->fail('Exception INVALID_SESSION not thrown.');
    }
     /**
     * Test valid dates on paper update
     * @group assessment
     */
    public function test_update_valid_dates() {
        $this->config->set('cfg_summative_mgmt', false);
        $papertitle = "Test update summative";
        $papertype = 2;
        $id = $this->create_paper($papertitle, $papertype);
        $assessment = new assessment($this->db, $this->config);
        $paperowner = 1;
        $enddate = "2016-01-25 09:00:00";
        $startdate = "2016-01-25 10:30:00";
        $labs = "1";
        $duration = 90;
        $session = 2016;
        $modules = array(1);
        $timezone = "Europe/London";
        $userid = 1;
        try {
            $assessment->update($id, $papertitle, $papertype, $paperowner, $startdate, $enddate, $labs, $duration, $session, $modules, $timezone, $userid);
        } catch (Exception $e) {
            if ($e->getMessage() == 'INVALID_DATES') {
                return;
            }
            $this->fail('Exception INVALID_DATES expected but ' . $e->getMessage() . ' thrown instead.');
        }
        $this->fail('Exception INVALID_DATES not thrown.');
    }
    /**
     * Test assessemnt scheduling
     * @group assessment
     */
    public function test_schedule() {
        $paperid = $this->create_paper("Test schedule summative", 2);
        $month = 1;
        $barriers = 0;
        $cohort_size = "11-20";
        $notes = "Some interesting notes on this exam";
        $sittings = 1;
        $campus = "Test campus";
        $assessment = new assessment($this->db, $this->config);
        // Test summative scheduke - SUCCESS.
        $this->assertEquals(1, $assessment->schedule($paperid, $month, $barriers, $cohort_size, $notes, $sittings, $campus));
        // Test formative schedule - FAIL.
        $title = "Test schedule formative";
        $paperid = $this->create_paper("Test schedule formative", 0);
        $this->assertFalse($assessment->schedule($paperid, $month, $barriers, $cohort_size, $notes, $sittings, $campus));
        // Test summative schedule unkown cohorts size - SUCCESS.
        $paperid = $this->create_paper("Test schedule summative 2", 2);
        $cohort_size = "unknown";
        $this->assertEquals(2, $assessment->schedule($paperid, $month, $barriers, $cohort_size, $notes, $sittings, $campus));
        // Test summative schedule max sittings to large - SUCCESS.
        $paperid = $this->create_paper("Test schedule summative 3", 2);
        $cohort_size = "11-20";
        $sittings = 1000;
        $this->assertEquals(3, $assessment->schedule($paperid, $month, $barriers, $cohort_size, $notes, $sittings, $campus));
        // Test summative schedule max sittings to small - SUCCESS.
        $paperid = $this->create_paper("Test schedule summative 4", 2);
        $sittings = -1;
        $this->assertEquals(4, $assessment->schedule($paperid, $month, $barriers, $cohort_size, $notes, $sittings, $campus));
        // Test schedule table is as expected.
        $queryTable = $this->getConnection()->createQueryTable('scheduling', 'SELECT * FROM scheduling');
        $expectedTable = $this->get_expected_data_set('expectedschedule')->getTable("scheduling");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    /**
     * Test assessemnt date setup
     * @group assessment
     */
    public function test_setup_dates() {
        $assessment = new assessment($this->db, $this->config);
        // Test London.
        $datesarray = $assessment->setup_start_end_dates(0, "2016-01-25 09:00:00", "2016-01-25 12:00:00", "Europe/London");
        $this->assertEquals("2016-01-25 09:00:00", $datesarray[0]);
        $this->assertEquals("2016-01-25 12:00:00", $datesarray[1]);
        // Test Kuwait.
        $datesarray = $assessment->setup_start_end_dates(0, "2016-01-25 09:00:00", "2016-01-25 12:00:00", "Asia/Kuwait");
        $this->assertEquals("2016-01-25 06:00:00", $datesarray[0]);
        $this->assertEquals("2016-01-25 09:00:00", $datesarray[1]);
        // Test Honolulu. 
        $datesarray = $assessment->setup_start_end_dates(0, "2016-01-25 09:00:00", "2016-01-25 12:00:00", "Pacific/Honolulu");
        $this->assertEquals("2016-01-25 19:00:00", $datesarray[0]);
        $this->assertEquals("2016-01-25 22:00:00", $datesarray[1]);
         // Test London non leap year feb 29th.
        $datesarray = $assessment->setup_start_end_dates(0, "2017-02-29 09:00:00", "2017-02-29 12:00:00", "Europe/London");
        $this->assertEquals("2017-03-01 09:00:00", $datesarray[0]);
        $this->assertEquals("2017-03-01 12:00:00", $datesarray[1]);
        
    }
}
