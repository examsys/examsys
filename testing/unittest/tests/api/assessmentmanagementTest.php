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
 * Test assessmentmanagement api class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class assessmentmanagementtest extends unittestdatabase {
    /**
     * Create a response array for creation
     * @return array the resposne array  
     */
    private function create_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 5,
            "externalid" => null,
            "error" => array(),
            "node" => 'create',
            "nodeid" => 1);
    }
    /**
     * Create a parameter array for creation
     * @return array the param array  
     */
    private function create_param_array() {
        return array(
            "nodeid" => 1,
            "title" => "Test Formative",
            "type" => 'formative',
            "owner" => 1,
            "startdatetime" => "2016-05-30T09:00:00",
            "enddatetime" => "2016-05-30T10:00:00",
            "session" => 2016,
            "modules" => array(array('id' => 0, 'value' => 1)),
            "labs" => array(array('id' => 0, 'value' => 'Test lab')),
            "timezone" => "Europe/London");
    }
    /**
     * Create a parameter array for updates
     * @return array the param array  
     */
    private function update_param_array() {
        return array(
            "nodeid" => 1,
            "id" => 2,
            "title" => "Test Formative 2 update",
            "modules" => array(array('id' => 0, 'value' => 2)),
            "labs" => array(array('id' => 0, 'value' => 'Test lab')));
    }
    /**
     * Create a parameter array for updates for external ids
     * @return array the param array  
     */
    private function update_ext_param_array() {
        return array(
            "nodeid" => 1,
            "externalid" => "123abc456",
            "title" => "Test Formative 2 update",
            "extmodules" => array(array('id' => 0, 'value' => "abc123def")),
            "labs" => array(array('id' => 0, 'value' => 'Test lab')));
    }
    /**
     * Create a response array for updates
     * @return array the resposne array  
     */
    private function update_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 2,
            "externalid" => null,
            "error" => array(),
            "node" => 'update',
            "nodeid" => 1);
    }
    /**
     * Create a response array for updates for external ids
     * @return array the resposne array  
     */
    private function update_ext_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 2,
            "externalid" => "123abc456",
            "error" => array(),
            "node" => 'update',
            "nodeid" => 1);
    }
    /**
     * Create a response array for scheduling
     * @return array the response array  
     */
    private function schedule_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 5,
            "externalid" => null,
            "error" => array(),
            "node" => 'schedule',
            "nodeid" => 1);
    }
    /**
     * Create a parameter array for scheduling
     * @return array the param array  
     */
    private function schedule_param_array() {
        return array(
            "nodeid" => 1,
            "title" => "Test Summative",
            "owner" => 1,
            "session" => 2016,
            "duration" => 60,
            "month" => 0,
            "cohort_size" => "76-100",
            "sittings" => 1,
            "barriers" => 1,
            "campus" => "Free text campus",
            "notes" => "Free text notes",
            "modules" => array(array('id' => 0, 'value' => 1)));
    }
    /**
     * Create a response array for deletion
     * @return array the response array  
     */
    private function delete_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1,
            "externalid" => null,
            "error" => null,
            "node" => 'delete',
            "nodeid" => 1);
    }
    /**
     * Create a parameter array for deletion
     * @return array the param array  
     */
    private function delete_param_array() {
        return array(
            "nodeid" => 1,
            "id" => 1);
    }
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR . "assessmentmanagementTest" . DIRECTORY_SEPARATOR . "assessmentmanagement.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR .  "assessmentmanagementTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test successful assessment creation
     * @group api
     */
    public function test_create_success() {
        // Test paper create- SUCCESS.
        $params = $this->create_param_array();
        $responsearray = $this->create_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
    }
    /**
     * Test successful assessment creation using external id
     * @group api
     */
    public function test_ext_create_success() {
        // Test paper create- SUCCESS.
        $params = $this->create_param_array();
        $params['externalid'] = "qwertyberty";
        $params['modules'] = array(array('id' => 0, 'value' => "abc123def"));
        $responsearray = $this->create_response_array();
        $responsearray['externalid'] = "qwertyberty";
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
    }
    /**
     * Test exception on assessment creation - non unique title
     * @group api
     */
    public function test_create_exception_title() {
        // Test paper create - EXCEPTION title in use.
        $params = $this->create_param_array();
        $responsearray = $this->create_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $params['title'] = "Test create formative"; 
        $responsearray['statuscode'] = 206;
        $responsearray['status'] = 'Assessment title is already in use';
        $responsearray['id'] = null;
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
    }
    /**
     * Test exception on assessment creation - unknown paper type
     * @group api
     */
    public function test_create_exception_type() {
        // Test paper create- EXCEPTION invalid paper type.
        $params = $this->create_param_array();
        $responsearray = $this->create_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 215;
        $responsearray['status'] = 'Paper type unknown';
        $responsearray['id'] = null;
        $params['title'] = "Test Formative 2"; 
        $params['type'] = 0;
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
    }
    /**
     * Test exception on assessment creation - invalid user
     * @group api
     */
    public function test_create_exception_user() {
        // Test paper create - EXCEPTION invalid user.
        $params = $this->create_param_array();
        $responsearray = $this->create_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 207;
        $responsearray['status'] = 'Assessment owner is invalid';
        $responsearray['id'] = null;
        $params['title'] = "Test Formative 2"; 
        $params['type'] = "formative";
        $params['owner'] = 999;
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
    }
    /**
     * Test exception on assessment creation - invalid user role
     * @group api
     */
    public function test_create_exception_role() {
        // Test paper create - EXCEPTION invalid user role.
        $params = $this->create_param_array();
        $responsearray = $this->create_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 208;
        $responsearray['status'] = 'Assessment owner role is invalid';
        $responsearray['id'] = null;
        $params['owner'] = 1000;
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
    }
    /**
     * Test exception on assessment creation - invalid session
     * @group api
     */
    public function test_create_exception_session() {
        // Test paper create - EXCEPTION invalid session.
        $params = $this->create_param_array();
        $responsearray = $this->create_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 209;
        $responsearray['status'] = 'Calendar year invalid';
        $responsearray['id'] = null;
        $params['owner'] = 1;
        $params['session'] = 1970;
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
    }
    /**
     * Test exception on assessment creation - invalid dates
     * @group api
     */
    public function test_create_exception_dates() {
        // Test paper create - EXCEPTION invalid dates.
        $params = $this->create_param_array();
        $responsearray = $this->create_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 212;
        $responsearray['id'] = null;
        $responsearray['status'] = 'End date must be after start date';
        $params['session'] = 2016;
        $params['startdatetime'] = "2016-05-30T10:00:00";
        $params['enddatetime'] = "2016-05-30T09:00:00";
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
    }
    /**
     * Test exception on assessment creation - invalid modules
     * @group api
     */
    public function test_create_exception_modules() {
        // Test paper create - ERROR invalid modules.
        $params = $this->create_param_array();
        $responsearray = $this->create_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 211;
        $responsearray['status'] = 'Module error';
        $responsearray['id'] = null;
        $error = array();
        $error[0] = 'Invalid module 1000';
        $responsearray['error'] = $error;
        $params['startdatetime'] = "2016-05-30T09:00:00";
        $params['enddatetime'] = "2016-05-30T10:00:00";
        $params['modules'] = array(array('id' => 0, 'value' => 1000));
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
    }
    /**
     * Test exception on assessment creation - no modules
     * @group api
     */
    public function test_create_exception_nomodules() {
        // Test paper create - ERROR invalid modules.
        $params = $this->create_param_array();
        $responsearray = $this->create_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 218;
        $responsearray['status'] = 'Paper was not assigned any modules';
        $responsearray['id'] = null;
        $params['startdatetime'] = "2016-05-30T09:00:00";
        $params['enddatetime'] = "2016-05-30T10:00:00";
        $params['modules'] = array();
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
    }
    /**
     * Test exception on assessment creation - invalid labs
     * @group api
     */
    public function test_create_exception_labs() {
        // Test paper create - ERROR invalid labs.
        $params = $this->create_param_array();
        $responsearray = $this->create_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 100;
        $responsearray['status'] = 'OK';
        $error[0] = 'Invalid lab Test lab 3';
        $responsearray['error'] = $error;
        $params['labs'] = array(array('id' => 0, 'value' => 'Test lab 3'));
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
    }
    /**
     * Test summative central control on assessment creation
     * @group api
     */
    public function test_create_exception_summative() {
        // Test create summative - ERROR centrally managed
        $params = $this->create_param_array();
        $responsearray = $this->create_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $this->config->set('cfg_summative_mgmt', true);
        $responsearray['statuscode'] = 214;
        $responsearray['status'] = 'This system is set-up to only allow the scheduling of summative exams';
        $responsearray['error'] = array();
        $responsearray['id'] = null;
        $params['labs'] = array();
        $params['type'] = 'summative';
        $params['title'] = "Test summative"; 
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
        // Test create summative - success not centrally managed
        $this->config->set('cfg_summative_mgmt', false);
        $responsearray['statuscode'] = 100;
        $responsearray['status'] = 'OK';
        $responsearray['id'] = 5;
        $this->assertEquals($responsearray, $assessment->create($params, $userid));
    }
    /**
     * Test successful assessment update
     * @group api
     */
    public function test_update_success() {
        // Test paper update - SUCCESS update title.
        $params = $this->update_param_array();
        $responsearray = $this->update_response_array();
        $responsearray['externalid'] = "123abc456";
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $this->assertEquals($responsearray, $assessment->update($params, $userid));
        // Check properties_modules.
        $querytable = $this->getConnection()->createQueryTable('properties_modules', 'SELECT property_id, idMod FROM properties_modules');
        $expectedtable = $this->get_expected_data_set('updateassessment')->getTable("properties_modules");  
        $this->assertTablesEqual($expectedtable, $querytable); 
    }
    /**
     * Test successful assessment update using external ids
     * @group api
     */
    public function test_ext_update_success() {
        // Test paper update - SUCCESS update title.
        $params = $this->update_ext_param_array();
        $responsearray = $this->update_ext_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $this->assertEquals($responsearray, $assessment->update($params, $userid));
        // Check properties_modules.
        $querytable = $this->getConnection()->createQueryTable('properties_modules', 'SELECT property_id, idMod FROM properties_modules');
        $expectedtable = $this->get_expected_data_set('updateassessment')->getTable("properties_modules");  
        $this->assertTablesEqual($expectedtable, $querytable); 
    }
    /**
     * Test assessment update startdate
     * @group api
     */
    public function test_update_startdate() {
        $params = $this->update_param_array();
        $responsearray = $this->update_response_array();
        $responsearray['externalid'] = "123abc456";
        $params['startdatetime'] = "2016-01-25T08:00:00";
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $this->assertEquals($responsearray, $assessment->update($params, $userid));
    }
    /**
     * Test assessment update exception - nothing to update
     * @group api
     */
    public function test_update_exception_noupdate() {
        // Test paper update - ERROR invalid paper id.
        $params = array(
            "id" => 1,
            "nodeid" => 1,
            "title" => "Test create formative",
            "owner" => 1,
            "startdatetime" => "2016-01-25T09:00:00",
            "enddatetime" => "2016-01-25T10:00:00",
            "duration" => 60,
            "session" => 2016,
            "modules" => array(array('id' => 0, 'value' => 1)),
            "labs" => array(array('id' => 0, 'value' => 'Test lab')),
            "timezone" => "Europe/London");
        $responsearray = $this->update_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 216;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $this->assertEquals($responsearray, $assessment->update($params, $userid));
    }
    /**
     * Test assessment update exception - nothing to update, no modules supplied
     * @group api
     */
    public function test_update_exception_noupdate2() {
        // Test paper update - ERROR invalid paper id.
        $params = array(
            "id" => 1,
            "nodeid" => 1,
            "title" => "Test create formative",
            "owner" => 1,
            "startdatetime" => "2016-01-25T09:00:00",
            "enddatetime" => "2016-01-25T10:00:00",
            "duration" => 60,
            "session" => 2016,
            "labs" => array(array('id' => 0, 'value' => 'Test lab')),
            "timezone" => "Europe/London");
        $responsearray = $this->update_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 216;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $this->assertEquals($responsearray, $assessment->update($params, $userid));
    }
    /**
     * Test assessment update exception - invalid paper id
     * @group api
     */
    public function test_update_exception_paper() {
        // Test paper update - ERROR invalid paper id.
        $params = $this->update_param_array();
        $responsearray = $this->update_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 210;
        $responsearray['status'] = 'Paper does not exist';
        $responsearray['id'] = null;
        $params['id'] = 1000;
        $this->assertEquals($responsearray, $assessment->update($params, $userid));
    }
    /**
     * Test assessment update invalid and empty labs.
     * @group api
     */
    public function test_update_exception_labs() {
        // Test paper update - SUCCESS do not pass labs or title.
        $responsearray = $this->update_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $params = array(
            "id" => 2,
            "nodeid" => 1,
            "duration" => 90,
            "modules" => array(array('id' => 0, 'value' => 1)));
        $responsearray['externalid'] = "123abc456";
        $this->assertEquals($responsearray, $assessment->update($params, $userid));
        // Test paper update - SUCCESS empty labs non fatal error.
        $params = $this->update_param_array();
        $assessment = new \api\assessmentmanagement($this->db);
        $params['title'] = "Test Formative 3 update";
        $params['id'] = 3;
        $params['labs'] = array(array('id' => 0, 'value' => ''));
        $responsearray['id'] = 3;
        $responsearray['externalid'] = null;
        $this->assertEquals($responsearray, $assessment->update($params, $userid));
        // We have done two updates that we want to check against the db now.
        // Assesment 2 - Check title / labs have not been changed in the db.
        // Assessment 3 - Check labs are null in the db.
        $querytable = $this->getConnection()->createQueryTable('properties', 'SELECT property_id, paper_title, start_date, end_date, exam_duration,
            calendar_year, timezone, paper_ownerID, labs, paper_type, externalid FROM properties');
        $expectedtable = $this->get_expected_data_set('updateassessment')->getTable("properties");  
        $this->assertTablesEqual($expectedtable, $querytable); 
    }
    /**
     * Test assessment update exception - invalid user
     * @group api
     */
    public function test_update_exception_user() {
        // Test paper update - EXCEPTION invalid user.
        $params = $this->update_param_array();
        $responsearray = $this->update_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 207;
        $responsearray['status'] = 'Assessment owner is invalid';
        $responsearray['id'] = null;
        $params['title'] = "Test Formative 2 update"; 
        $params['owner'] = 999;
        $this->assertEquals($responsearray, $assessment->update($params, $userid));
    }
    /**
     * Test assessment update exception - invalid user role
     * @group api
     */
    public function test_update_exception_role() {
        // Test paper update - EXCEPTION invalid user role.
        $params = $this->update_param_array();
        $responsearray = $this->update_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 208;
        $responsearray['status'] = 'Assessment owner role is invalid';
        $responsearray['id'] = null;
        $params['owner'] = 1000;
        $this->assertEquals($responsearray, $assessment->update($params, $userid));
    }
    /**
     * Test assessment update exception - invalid session
     * @group api
     */
    public function test_update_exception_session() {
        // Test paper update - EXCEPTION invalid session.
        $params = $this->update_param_array();
        $responsearray = $this->update_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 209;
        $responsearray['status'] = 'Calendar year invalid';
        $responsearray['id'] = null;
        $params['owner'] = 1;
        $params['session'] = 1970;
        $this->assertEquals($responsearray, $assessment->update($params, $userid));
    }
    /**
     * Test assessment update exception - invalid dates
     * @group api
     */
    public function test_update_exception_dates() {
        // Test paper update - EXCEPTION invalid dates.
        $params = $this->update_param_array();
        $responsearray = $this->update_response_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 212;
        $responsearray['status'] = 'End date must be after start date';
        $responsearray['id'] = null;
        $params['session'] = 2016;
        $params['startdatetime'] = "2016-05-30T10:00:00";
        $params['enddatetime'] = "2016-05-30T09:00:00";
        $this->assertEquals($responsearray, $assessment->update($params, $userid));
    }
    /**
     * Test assessment update central summative control
     * @group api
     */
    public function test_update_exception_summative() {
        // Test update summative - ERROR centrally managed
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $this->config->set('cfg_summative_mgmt', true);
        $summativeparams = array(
            "id" => 4,
            "type" => 'summative',
            "nodeid" => 9,
            "title" => "Test summative 666",
            "modules" => array(array('id' => 0, 'value' => 1)),
            "labs" => array(array('id' => 0, 'value' => 'Test lab')));
        $summativeresponsearray = array(
            "statuscode" => 214,
            "status" => 'This system is set-up to only allow the scheduling of summative exams',
            "id" => null,
            "externalid" => null,
            "error" => array(),
            "node" => 'update',
            "nodeid" => 9);
        $assessment->create($summativeparams, $userid);
        $this->assertEquals($summativeresponsearray, $assessment->update($summativeparams, $userid));
        // Test create summative - success not centrally managed
        $this->config->set('cfg_summative_mgmt', false);
        $summativeresponsearray['statuscode'] = 100;
        $summativeresponsearray['status'] = 'OK';
        $summativeresponsearray['nodeid'] = 10;
        $summativeresponsearray['id'] = 4;
        $summativeparams['nodeid'] = 10;
        $this->assertEquals($summativeresponsearray, $assessment->update($summativeparams, $userid));
    }
    /**
     * Test assessemnt scheduling success
     * @group api
     */
    public function test_schedule_success() {
        // Test paper schedule- SUCCESS.
        $this->config->set('cfg_summative_mgmt', true);
        $responsearray = $this->schedule_response_array();
        $params = $this->schedule_param_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $this->assertEquals($responsearray, $assessment->schedule($params, $userid));
    }
    /**
     * Test assessemnt scheduling success - only required paramaters
     * @group api
     */
    public function test_schedule_success_req() {
        // Test paper schedule- SUCCESS.
        $this->config->set('cfg_summative_mgmt', true);
        $responsearray = $this->schedule_response_array();
        $params = array(
            "nodeid" => 1,
            "title" => "Test Summative",
            "owner" => 1,
            "session" => 2016,
            "duration" => 60,
            "month" => 0,
            "modules" => array(array('id' => 0, 'value' => 1)));
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $this->assertEquals($responsearray, $assessment->schedule($params, $userid));
        // Check db.
        $querytable = $this->getConnection()->createQueryTable('properties', 'SELECT property_id, paper_title, start_date, end_date, exam_duration,
            calendar_year, timezone, paper_ownerID, labs, paper_type, externalid FROM properties');
        $expectedtable = $this->get_expected_data_set('scheduleassessment')->getTable("properties");  
        $this->assertTablesEqual($expectedtable, $querytable);
        $querytable = $this->getConnection()->createQueryTable('properties_modules', 'SELECT property_id, idMod FROM properties_modules');
        $expectedtable = $this->get_expected_data_set('scheduleassessment')->getTable("properties_modules");  
        $this->assertTablesEqual($expectedtable, $querytable);
        $querytable = $this->getConnection()->createQueryTable('scheduling', 'SELECT * FROM scheduling');
        $expectedtable = $this->get_expected_data_set('scheduleassessment')->getTable("scheduling");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test assessemnt scheduling success - non fatal incorrect modules
     * @group api
     */
    public function test_schedule_exception_modules() {
        // Test scheduling with invalid modules - non fatal error.
        $this->config->set('cfg_summative_mgmt', true);
        $responsearray = $this->schedule_response_array();
        $params = $this->schedule_param_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 100;
        $responsearray['status'] = 'OK';
        $responsearray['id'] = 5;
        $error = array();
        $error[0] = 'Invalid module 99';
        $responsearray['error'] = $error;
        $params['title'] = "Test Summative 99";
        $params['modules'] = array(array('id' => 0, 'value' => 99), array('id' => 1, 'value' => 2));
        $this->assertEquals($responsearray, $assessment->schedule($params, $userid));
    }
    /**
     * Test assessemnt scheduling success - no modules
     * @group api
     */
    public function test_schedule_exception_nomodules() {
        // Test scheduling with invalid modules - non fatal error.
        $this->config->set('cfg_summative_mgmt', true);
        $responsearray = $this->schedule_response_array();
        $params = $this->schedule_param_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 218;
        $responsearray['status'] = 'Paper was not assigned any modules';
        $responsearray['id'] = null;
        $params['title'] = "Test Summative 99";
        $params['modules'] = array();
        $this->assertEquals($responsearray, $assessment->schedule($params, $userid));
    }
    /**
     * Test assessemnt scheduling exception invalid title
     * @group api
     */
    public function test_schedule_exception_title() {
        // Test scheduling with duplciate title - fatal error.
        $responsearray = $this->schedule_response_array();
        $params = $this->schedule_param_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 206;
        $responsearray['status'] = 'Assessment title is already in use';
        $responsearray['id'] = null;
        $responsearray['error'] = array();
        $params['title'] = "Test create summative";
        $params['modules'] = array(array('id' => 0, 'value' => 1));
        $this->assertEquals($responsearray, $assessment->schedule($params, $userid));
    }
    /**
     * Test successful assessement deletion
     * @group api
     */
    public function test_delete_success() {
        // Test paper deletion- SUCCESS.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $this->assertEquals($responsearray, $assessment->delete($params, $userid));
        // Check that the remaining properties are correct, when we delete a paper we actually jsut add a timestamp to the table
        // which makes creating a ficute to check against difficult so doing this instead
        $querytable = $this->getConnection()->createQueryTable('properties', 'SELECT property_id, paper_title, start_date, end_date, exam_duration,
            calendar_year, timezone, paper_ownerID, labs, paper_type, externalid FROM properties WHERE deleted is NULL');
        $expectedtable = $this->get_expected_data_set('deleteassessment')->getTable("properties");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test assessement deletion exception invalid paper id
     * @group api
     */
    public function test_delete_exception_paper() {
        // Test deleting a non existance paper.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 202;
        $responsearray['status'] = 'Paper does not exist';
        $responsearray['id'] = null;
        $params['id'] = 99;
        $this->assertEquals($responsearray, $assessment->delete($params, $userid));
        // Test paper deletion- ERROR no id provided.
        $params = array(
            "nodeid" => 1);
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $this->assertEquals($responsearray, $assessment->delete($params, $userid));
    }
    /**
     * Test assessement deletion exception paper in use
     * @group api
     */
    public function test_delete_exception_paperinuse() {
        // Test deleting a paper in use - first add an entry in log_metadata.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $userid = 1;
        $assessment = new \api\assessmentmanagement($this->db);
        $responsearray['statuscode'] = 203;
        $responsearray['id'] = null;
        $responsearray['status'] = 'Assessment not deleted, as has been taken by a user';
        $params['id'] = 2;
        $this->assertEquals($responsearray, $assessment->delete($params, $userid));
        // Test deleting a paper in use - second add an entry in log4_overall.
        $responsearray['statuscode'] = 203;
        $responsearray['status'] = 'Assessment not deleted, as has been taken by a user';
        $params['id'] = 3;
        $this->assertEquals($responsearray, $assessment->delete($params, $userid));
    }
}
