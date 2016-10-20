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
 * Test facultyemanagement api class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class facultymanagementtest extends unittestdatabase {
    /**
     * Create a response array for creation
     * @return array the resposne array  
     */
    private function create_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 3,
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
            "name" => 'TEST3',
            "code" => 'T3',
            "externalid" => "xyz");
    }
    /**
     * Create a parameter array for updates
     * @return array the param array  
     */
    private function update_param_array() {
        return array(
            "nodeid" => 1,
            "id" => 1,
            "name" => 'TESTUPDATE');
    }
    /**
     * Create a response array for updates
     * @return array the resposne array  
     */
    private function update_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1,
            "externalid" => null,
            "error" => null,
            "node" => 'update',
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
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR . "facultymanagementTest" . DIRECTORY_SEPARATOR . "facultymanagement.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR .  "facultymanagementTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test successful faculty creation - external system faculty
     * @group api
     */
    public function test_create() {
        // Test faculty creation - SUCCESS
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        $responsearray['externalid'] = "xyz";
        $this->assertEquals($responsearray, $faculty->create($params, $userid));
    }
    /**
     * Test successful faculty creation  - non external system faculty
     * @group api
     */
    public function test_create2() {
        $responsearray = $this->create_response_array();
        $params = array(
            "nodeid" => 1,
            "name" => 'TEST3',
            "code" => 'T3');
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $faculty->create($params, $userid));
    }
    /**
     * Test successful faculty creation  - name exits but new code
     * @group api
     */
    public function test_create3() {
        $responsearray = $this->create_response_array();
        $params = array(
            "nodeid" => 1,
            "name" => 'Test name',
            "code" => 'T3');
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $faculty->create($params, $userid));
    }
    /**
     * Test faculty creation exception faculty exists
     * @group api
     */
    public function test_create_exception_faculty() {
        // Test faculty creation - ERROR faculty already exists
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 405;
        $responsearray['status'] = 'Faculty already exists';
        $responsearray['id'] = 1;
        $responsearray['externalid'] = "abcdef";
        $params = array(
            "nodeid" => 1,
            "name" => 'Test name');
        $this->assertEquals($responsearray, $faculty->create($params, $userid));
    }
    /**
     * Test faculty creation exception faculty exists (code exits)
     * @group api
     */
    public function test_create_exception_faculty2() {
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 405;
        $responsearray['status'] = 'Faculty already exists';
        $responsearray['id'] = 1;
        $responsearray['externalid'] = "abcdef";
        $params = array(
            "nodeid" => 1,
            "name" => 'Test name',
            "code" => 'TEST');
        $this->assertEquals($responsearray, $faculty->create($params, $userid));
    }
    /**
     * Test faculty create exception faculty not supplied
     * @group api
     */
    public function test_create_exception_nofaculty() {
        // Test faculty update - ERROR faculty does not exist
        $responsearray = $this->create_response_array();
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 406;
        $responsearray['status'] = 'Faculty name not supplied';
        $responsearray['id'] = null;
        $params = array(
            "nodeid" => 1);
        $this->assertEquals($responsearray, $faculty->create($params, $userid));
    }
    /**
     * Test successful faculty update
     * @group api
     */
    public function test_update() {
        // Test faculty update - SUCCESS
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        $responsearray['externalid'] = "abcdef";
        $this->assertEquals($responsearray, $faculty->update($params, $userid));
    }
    /**
     * Test faculty update exception nothing to update
     * @group api
     */
    public function test_update_exception_noupdate() {
        $responsearray = $this->update_response_array();
        $params = array(
            "nodeid" => 1,
            "id" => 1,
            "name" => 'Test name');
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 407;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $this->assertEquals($responsearray, $faculty->update($params, $userid));
    }
    /**
     * Test faculty update exception faculty does not exist
     * @group api
     */
    public function test_update_exception_faculty() {
        // Test faculty update - ERROR faculty does not exist
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 401;
        $responsearray['status'] = 'Faculty does not exist';
        $responsearray['id'] = null;
        $params['id'] = 100;
        $this->assertEquals($responsearray, $faculty->update($params, $userid));
    }
    /**
     * Test successful faculty deletion
     * @group api
     */
    public function test_delete() {
        // Test faculty deletion - SUCCESS.
        $responsearray = $this->delete_response_array();
        $responsearray['externalid'] = "abcdef";
        $params = $this->delete_param_array();
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $faculty->delete($params, $userid));
        // Check that the remaining faculty are correct, when we delete a faculty we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->getConnection()->createQueryTable('faculty', 'SELECT id, name FROM faculty WHERE deleted is NULL');
        $expectedtable = $this->get_expected_data_set('deletefaculty')->getTable("faculty");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test faculty deletion exception does not exist
     * @group api
     */
    public function test_delete_faculty() {
        // Test deleting a non existance faculty.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 401;
        $responsearray['status'] = 'Faculty does not exist';
        $responsearray['id'] = null;
        $params['id'] = 99;
        $this->assertEquals($responsearray, $faculty->delete($params, $userid));
        // No id supplied.
        $params = array(
            "nodeid" => 1);
        $this->assertEquals($responsearray, $faculty->delete($params, $userid));
    }
    /**
     * Test faculty deletion exception faculty in use
     * @group api
     */
    public function test_delete_inuse() {
        // Test deleting a faculty in use.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $faculty = new \api\facultymanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 404;
        $responsearray['status'] = 'Faculty not deleted, as contains schools';
        $responsearray['id'] = null;
        $params['id'] = 2;
        $this->assertEquals($responsearray, $faculty->delete($params, $userid)); 
    }
}