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
 * Test coursemanagement api class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class coursemanagementtest extends unittestdatabase {
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
            "name" => 'CREATE',
            "description" => 'Create test',
            "school" => 'School test',
            "faculty" => 'Faculty test');
    }
    /**
     * Create a parameter array for updates
     * @return array the param array  
     */
    private function update_param_array() {
        return array(
            "nodeid" => 1,
            "id" => 1,
            "description" => 'Test course update');
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
            "externalid" => 123456,
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
            "externalid" => 123456,
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
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR . "coursemanagementTest" . DIRECTORY_SEPARATOR . "coursemanagement.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR .  "coursemanagementTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test successful course create
     * @group api
     */
    public function test_create_success() {
        // Test course creation - SUCCESS
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $course->create($params, $userid));
    }
    /**
     * Test successful course create with external id
     * @group api
     */
    public function test_create_success_external() {
        $responsearray = $this->create_response_array();
        $responsearray['externalid'] = 123457;
        $params =  array(
            "nodeid" => 1,
            "name" => 'CREATE',
            "description" => 'Create test',
            "schoolextid" => 'qwerty',
            "facultyextid" => 'abcdefghi',
            "externalid" => 123457);
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $course->create($params, $userid));
    }
    /**
     * Test successful course create - no faculty supplied
     * @group api
     */
    public function test_create_nofaculty() {
        // Test course creation - SUCCESS (not supplying faculty)
        $responsearray = $this->create_response_array();
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        $params = array(
            "nodeid" => 1,
            "name" => 'CREATE',
            "description" => 'Create test',
            "school" => 'Test school');
        $this->assertEquals($responsearray, $course->create($params, $userid));
    }
    /**
     * Test course create exception course exists
     * @group api
     */
    public function test_create_exception_course() {
        // Test course creation - ERROR course already exists
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 306;
        $responsearray['status'] = 'Course already exists';
        $responsearray['id'] = 1;
        $responsearray['externalid'] = 123456;
        $params['name'] = "TEST";
        $this->assertEquals($responsearray, $course->create($params, $userid));
    }
    /**
     * Test course create exception invalid faculty
     * @group api
     */
    public function test_create_exception_faculty() {
        // Test course creation - ERROR invalid faculty
        $responsearray = $this->create_response_array();
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 303;
        $responsearray['status'] = 'Faculty not supplied';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        $params = array(
            "nodeid" => 1,
            "name" => 'CREATE',
            "description" => 'Create test',
            "school" => 'School test invalid');
        $this->assertEquals($responsearray, $course->create($params, $userid));
    }
    /**
     * Test successful course update
     * @group api
     */
    public function test_update_success() {
        // Test course update - SUCCESS description
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        // Test course update - SUCCESS name
        $params = array(
            "nodeid" => 1,
            "id" => 1,
            "name" => 'TESTUPDATE');
        $this->assertEquals($responsearray, $course->update($params, $userid));
    }
    /**
     * Test successful course update using external id
     * @group api
     */
    public function test_update_success_external() {
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        // Test course update - SUCCESS name
        $params = array(
            "nodeid" => 1,
            "externalid" => 123456,
            "name" => 'TESTUPDATE2');
        $this->assertEquals($responsearray, $course->update($params, $userid));
    }
    /**
     * Test course update exception invalid course
     * @group api
     */
    public function test_update_exception_course() {
        // Test course uddate - ERROR course does not exist
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 301;
        $responsearray['status'] = 'Course does not exist';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        $params['id'] = 100;
        $this->assertEquals($responsearray, $course->update($params, $userid));
    }
    /**
     * Test course update exception school not supplied on faculty update
     * @group api
     */
    public function test_update_exception_school() {
        // Test course update - ERROR schhol not supplied.
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 307;
        $responsearray['status'] = 'School not supplied';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        $params['faculty'] = 'Test faculty 2';
        $this->assertEquals($responsearray, $course->update($params, $userid));
        // Check courses table.
        $querytable = $this->getConnection()->createQueryTable('courses', 'SELECT id, name, description, schoolid, externalid FROM courses WHERE id = 1');
        $expectedtable = $this->get_expected_data_set('updatecourse')->getTable("courses");  
        $this->assertTablesEqual($expectedtable, $querytable);
        // Check faculty table.
        $querytable = $this->getConnection()->createQueryTable('faculty', 'SELECT id, name FROM faculty');
        $expectedtable = $this->get_expected_data_set('updatecourse')->getTable("faculty");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test course update exception nothing to update
     * @group api
     */
    public function test_update_exception_noupdate() {
        $responsearray = $this->update_response_array();
        $params = array(
            "nodeid" => 1,
            "id" => 1,
            "name" => 'TEST',
            "description" => 'Test course',
            "school" => 'Test school');
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 308;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        $this->assertEquals($responsearray, $course->update($params, $userid));
        // Check courses table.
        $querytable = $this->getConnection()->createQueryTable('courses', 'SELECT id, name, description, schoolid, externalid FROM courses WHERE id = 1');
        $expectedtable = $this->get_expected_data_set('updatecourse')->getTable("courses");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test successful course deletion
     * @group api
     */
    public function test_delete_success() {
        // Test course deletion - SUCCESS.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $course->delete($params, $userid));
        // Check that the remaining courses are correct, when we delete a course we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->getConnection()->createQueryTable('courses', 'SELECT id, name, description, schoolid, externalid FROM courses WHERE deleted is NULL');
        $expectedtable = $this->get_expected_data_set('deletecourse')->getTable("courses");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test successful course deletion with external id
     * @group api
     */
    public function test_delete_success_external() {
        $responsearray = $this->delete_response_array();
        $params = array(
            "nodeid" => 1,
            "externalid" => 123456);
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $course->delete($params, $userid));
        // Check that the remaining courses are correct, when we delete a course we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->getConnection()->createQueryTable('courses', 'SELECT id, name, description, schoolid, externalid FROM courses WHERE deleted is NULL');
        $expectedtable = $this->get_expected_data_set('deletecourse')->getTable("courses");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test course deletion exception course does not exist
     * @group api
     */
    public function test_delete_exception_course() {
        // Test deleting a non existance course.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 301;
        $responsearray['status'] = 'Course does not exist';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        $params['id'] = 99;
        $this->assertEquals($responsearray, $course->delete($params, $userid));
        // Test course deletion- ERROR no id provided.
        $params = array(
            "nodeid" => 1);
        $this->assertEquals($responsearray, $course->delete($params, $userid));
    }
    /**
     * Test course deletion exception course in use
     * @group api
     */
    public function test_delete_exception_courseinuse() {
        // Test deleting a course in use.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $course = new \api\coursemanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 302;
        $responsearray['status'] = 'Course not deleted, as users enrolled';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        $params['id'] = 2;
        $this->assertEquals($responsearray, $course->delete($params, $userid)); 
    }
}
