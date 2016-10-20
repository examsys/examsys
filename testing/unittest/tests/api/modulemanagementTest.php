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
 * Test modulemanagement api class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class modulemanagementtest extends unittestdatabase {
    /**
     * Create a response array for creation
     * @return array the response array  
     */
    private function create_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 4,
            "externalid" => null,
            "error" => null,
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
            "modulecode" => 'TEST4',
            "name" => 'Test module 4',
            "school" => 'Test school',
            "faculty" => 'Test faculty');
    }
    /**
     * Create a parameter array for updates
     * @return array the param array  
     */
    private function update_param_array() {
        return array(
            "nodeid" => 1,
            "id" => 2,
            "name" => 'Test module 2 update');
    }
    /**
     * Create a response array for updates
     * @return array the response array  
     */
    private function update_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 2,
            "externalid" => null,
            "error" => null,
            "node" => 'update',
            "nodeid" => 1);
    }
    /**
     * Create a parameter array for enrolment
     * @return array the param array  
     */
    private function enrol_param_array() {
        return array(
            "nodeid" => 1,
            "userid" => 1000,
            "moduleid" => 1,
            "session" => 2016,
            "attempt" => 1);
    }
    /**
     * Create a response array for enrolment
     * @return array the response array  
     */
    private function enrol_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 3,
            "externalid" => null,
            "error" => null,
            "node" => 'enrol',
            "nodeid" => 1);
    }
    /**
     * Create a parameter array for unenrolment
     * @return array the param array  
     */
    private function unenrol_param_array() {
        return array(
            "nodeid" => 1,
            "userid" => 1000,
            "moduleid" => 3,
            "session" => 2016);
    }
    /**
     * Create a response array for unenrolment
     * @return array the response array  
     */
    private function unenrol_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1,
            "externalid" => null,
            "error" => null,
            "node" => 'unenrol',
            "nodeid" => 1);
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
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR . "modulemanagementTest" . DIRECTORY_SEPARATOR . "modulemanagement.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR .  "modulemanagementTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test successful module creation
     * @group api
     */
    public function test_create_success() {
        // Test module create - SUCCESS.
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $module->create($params, $userid));
        // Create moudle in new school
        $responsearray['id'] = 5;
        $params = array(
            "nodeid" => 1,
            "modulecode" => 'TEST5',
            "name" => 'Test module 5',
            "school" => 'Test school 2',
            "faculty" => 'Test faculty');
        $this->assertEquals($responsearray, $module->create($params, $userid));
    }
    /**
     * Test module creation exception module exists
     * @group api
     */
    public function test_create_exception_module() {
        // Test module create - ERROR module already exists.
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 505;
        $responsearray['status'] = 'Module already exists';
        $responsearray['id'] = 1;
        $params['modulecode'] = 'TEST';
        $this->assertEquals($responsearray, $module->create($params, $userid));
    }
    /**
     * Test module creation exception module exists (duplicate externalid - empty string)
     * @group api
     */
    public function test_create_exception_module2() {
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 505;
        $responsearray['status'] = 'Module already exists';
        $responsearray['id'] = 1;
        $responsearray['externalid'] = '';
        $params['externalid'] = '';
        $this->assertEquals($responsearray, $module->create($params, $userid));
    }
    /**
     * Test module creation exception invalid faculty
     * @group api
     */
    public function test_create_exception_faculty() {
        // Test module create - ERROR invalid faculty
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 506;
        $responsearray['status'] = 'Faculty not supplied';
        $responsearray['id'] = null;
        $params = array(
            "nodeid" => 1,
            "modulecode" => 'TEST5',
            "name" => 'Test module 5',
            "school" => 'Test school 2',
            "faculty" => '');
        $this->assertEquals($responsearray, $module->create($params, $userid));
    }
    /**
     * Test successful module update
     * @group api
     */
    public function test_update_success() {
        // Test module update name.
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $module->update($params, $userid));
        // Test module update module code.
        $params = array(
            "nodeid" => 1,
            "id" => 2,
            "modulecode" => 'TEST2UPDATE');
        $this->assertEquals($responsearray, $module->update($params, $userid));
        // Check update occured.
        $querytable = $this->getConnection()->createQueryTable('modules', 'SELECT id, moduleid, fullname, active, schoolid, academic_year_start, sms FROM modules where id = 2');
        $expectedtable = $this->get_expected_data_set('updatemodule')->getTable("modules");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test module update, also supplying school and faculty that have not changed
     * @group api
     */
    public function test_update_success2() {
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $params = array(
            "nodeid" => 1,
            "id" => 2,
            "modulecode" => 'TEST2UPDATE',
            "school" => 'Test school',
            "faculty" => 'Test faculty');
        $this->assertEquals($responsearray, $module->update($params, $userid));
    }
    /**
     * Test module update exception nothing to update
     * @group api
     */
    public function test_update_exception_noupdate() {
        $responsearray = $this->update_response_array();
        $params = array(
            "nodeid" => 1,
            "id" => 2,
            "modulecode" => 'TEST2',
            "name" => 'Test module 2',
            "school" => 'Test school',
            "faculty" => 'Test faculty',
            "sms" => 'unittest');
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 512;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $this->assertEquals($responsearray, $module->update($params, $userid));
    }
    /**
     * Test module update exception module does not exist
     * @group api
     */
    public function test_update_exception_module() {
        // Test module create - ERROR module does not exist.
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 501;
        $responsearray['status'] = 'Module does not exist';
        $responsearray['id'] = null;
        $params['id'] = 99;
        $this->assertEquals($responsearray, $module->update($params, $userid));
    }
    /**
     * Test module update exception module already exists
     * @group api
     */
    public function test_update_exception_moduleexists() {
        // Test module create - ERROR module does not exist.
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 505;
        $responsearray['status'] = 'Module already exists';
        $responsearray['id'] = 3;
        $params['modulecode'] = 'TEST3';
        $this->assertEquals($responsearray, $module->update($params, $userid));
    }
    /**
     * Test module update exception faculty not supplied on school update
     * @group api
     */
    public function test_update_exception_faculty() {
        // Test module create - ERROR faculty not supplied.
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 506;
        $responsearray['status'] = 'Faculty not supplied';
        $responsearray['id'] = null;
        $params['school'] = 'Test school 2';
        $this->assertEquals($responsearray, $module->update($params, $userid));
    }
    /**
     * Test module update exception school not supplied on faculty update
     * @group api
     */
    public function test_update_exception_school() {
        // Test module create - ERROR faculty not supplied.
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 511;
        $responsearray['status'] = 'School not supplied';
        $responsearray['id'] = null;
        $params['faculty'] = 'Test faculty 2';
        $this->assertEquals($responsearray, $module->update($params, $userid));
    }
    /**
     * Test successful module enrolment
     * @group api
     */
    public function test_enrol_success() {
        // Test module enrolment - SUCCESS.
        $responsearray = $this->enrol_response_array();
        $params = $this->enrol_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $module->enrol($params, $userid));
        // Already enrolled, so just return id of existing enrolment.
        $responsearray['statuscode'] = 514;
        $this->assertEquals($responsearray, $module->enrol($params, $userid));
        // No session supplied - user current.
        $responsearray['id'] = 4;
        $responsearray['statuscode'] = 100;
        $params = array(
            "nodeid" => 1,
            "userid" => 1000,
            "moduleid" => 2,
            "attempt" => 1);
        $this->assertEquals($responsearray, $module->enrol($params, $userid));
    }
    /**
     * Test successful module enrolment using user external id
     * @group api
     */
    public function test_enrol_success_external() {
        // Test module enrolment - SUCCESS.
        $responsearray = $this->enrol_response_array();
        $params = array(
            "nodeid" => 1,
            "studentid" => "00000001",
            "moduleid" => 1,
            "session" => 2016,
            "attempt" => 1);
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $module->enrol($params, $userid));
    }
    /**
     * Test module enrolment exception invalid module
     * @group api
     */
    public function test_enrol_exception_module() {
        // Test module enrolment - ERROR module does not exist.
        $responsearray = $this->enrol_response_array();
        $params = $this->enrol_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 508;
        $responsearray['status'] = 'User not enrolled';
        $responsearray['id'] = null;
        $params['moduleid'] = 99;
        $this->assertEquals($responsearray, $module->enrol($params, $userid));
    }
    /**
     * Test module enrolment exception invalid user
     * @group api
     */
    public function test_enrol_exception_user() {
        // Test module enrolment - ERROR invalid user.
        $responsearray = $this->enrol_response_array();
        $params = $this->enrol_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 507;
        $responsearray['status'] = 'User does not exist';
        $responsearray['id'] = null;
        $params['userid'] = 999;
        $this->assertEquals($responsearray, $module->enrol($params, $userid));
    }
    /**
     * Test successful module un-enrolment
     * @group api
     */
    public function test_unenrol_success() {
        // Test module enrolment - SUCCESS.
        $responsearray = $this->unenrol_response_array();
        $params = $this->unenrol_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $module->unenrol($params, $userid));   
    }
    /**
     * Test successful module un-enrolment using externalid
     * @group api
     */
    public function test_unenrol_success_external() {
        // Test module enrolment - SUCCESS.
        $responsearray = $this->unenrol_response_array();
        $responsearray['id'] = 2;
        $params = array("nodeid" => 1,
            "studentid" => "00000001",
            "moduleid" => 3,
            "session" => 2016);
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $module->unenrol($params, $userid));   
    }
    /**
     * Test module un-enrolment exception incorrect session supplied
     * @group api
     */
    public function test_unenrol_exception_session() {
        // Test module unenrolment - wrong session
        $responsearray = $this->unenrol_response_array();
        $params = $this->unenrol_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 509;
        $responsearray['status'] = 'User not un-enrolled';
        $responsearray['id'] = null;
        $params['session'] = 2015;
        $this->assertEquals($responsearray, $module->unenrol($params, $userid));
        // No session supplied.
        $enrolparams = $this->enrol_param_array();
        $module->enrol($enrolparams, $userid);
        $responsearray['statuscode'] = 510;
        $responsearray['status'] = 'Session not supplied';
        $responsearray['id'] = null;
        $params = array(
            "nodeid" => 1,
            "userid" => 1000,
            "moduleid" => 1);
        $this->assertEquals($responsearray, $module->unenrol($params, $userid));
    }
    /**
     * Test module un-enrolment exception incorrect module
     * @group api
     */
    public function test_unenrol_module() {
        // Test module enrolment - ERROR no enrolment to unenrol.
        $responsearray = $this->unenrol_response_array();
        $params = $this->unenrol_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 509;
        $responsearray['status'] = 'User not un-enrolled';
        $responsearray['id'] = null;
        $params['moduleid'] = 2;
        $this->assertEquals($responsearray, $module->unenrol($params, $userid));
    }
    /**
     * Test module un-enrolment exception invalid user
     * @group api
     */
    public function test_unenrol_user() {
        // Test module enrolment - ERROR invalid user.
        $responsearray = $this->unenrol_response_array();
        $params = $this->unenrol_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 507;
        $responsearray['status'] = 'User does not exist';
        $responsearray['id'] = null;
        $params['userid'] = 999;
        $this->assertEquals($responsearray, $module->unenrol($params, $userid));
    }
    /**
     * Test successful module deletion
     * @group api
     */
    public function test_delete_success() {
        // Test module deletion - SUCCESS.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $module->delete($params, $userid));
        // Check that the remaining modules are correct, when we delete a module we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->getConnection()->createQueryTable('modules', 'SELECT id, moduleid, fullname, active, schoolid, academic_year_start FROM modules WHERE mod_deleted is NULL');
        $expectedtable = $this->get_expected_data_set('deletemodule')->getTable("modules");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test module deletion exception invalid module
     * @group api
     */
    public function test_delete_exception_module() {
        // Test deleting a non existance module.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 501;
        $responsearray['status'] = 'Module does not exist';
        $responsearray['id'] = null;
        $params['id'] = 99;
        $this->assertEquals($responsearray, $module->delete($params, $userid));
        // Test no module supplied.
        $params = array(
            "nodeid" => 1);
        $this->assertEquals($responsearray, $module->delete($params, $userid));
    }
     /**
     * Test module deletion exception module in use
     * @group api
     */
    public function test_delete_exception_inuse() {
        // Test deleting a module in use - first check module has a paper.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $module = new \api\modulemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 502;
        $responsearray['status'] = 'Module not deleted, as linked to a paper or enrolement';
        $responsearray['id'] = null;
        $params['id'] = 2;
        $this->assertEquals($responsearray, $module->delete($params, $userid)); 
        // Test deleting a module in use - second check module has a user.
        $responsearray['statuscode'] = 502;
        $responsearray['status'] = 'Module not deleted, as linked to a paper or enrolement';
        $params['id'] = 3;
        $this->assertEquals($responsearray, $module->delete($params, $userid)); 
    }
}
