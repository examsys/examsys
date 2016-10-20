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
 * Test usermanagement api class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class usermanagementtest extends unittestdatabase {
    /**
     * Create a student response array for creation
     * @return array the response array  
     */
    private function create_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1003,
            "externalid" => null,
            "error" => array(),
            "node" => 'create',
            "nodeid" => 1);
    }
    /**
     * Create a student parameter array for creation
     * @return array the param array  
     */
    private function create_param_array() {
        return array(
            "nodeid" => 1,
            "username" => "testy",
            "surname" => "tester",
            "role" => "Student",
            "course" => "TEST2",
            "modules" => array(array('name' => 'moduleid', 'id' => 0, 'value' => 1)));
    }
    /**
     * Create a staff parameter array for creation
     * @return array the param array  
     */
    private function create_staff_param_array() {
        return array(
            "nodeid" => 1,
            "username" => "staff",
            "surname" => "staffy",
            "role" => "Staff",
            "course" => "University Lecturer",
            "modules" => array(array('name' => 'moduleid', 'id' => 0, 'value' => 1)));
    }
    /**
     * Create a response array for updates
     * @return array the response array  
     */
    private function update_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1001,
            "externalid" => null,
            "error" => array(),
            "node" => 'update',
            "nodeid" => 1);
    }
    /**
     * Create a parameter array for updates
     * @return array the param array  
     */
    private function update_param_array() {
        return array(
            "nodeid" => 1,
            "id" => 1001,
            "externalid" => null,
            "surname" => "",
            "forename" => "test",
            "modules" => array(array('name' => 'moduleid', 'id' => 0, 'value' => 2)));
    }
    /**
     * Create a response array for deletion
     * @return array the response array  
     */
    private function delete_response_array() {
        return array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 1001,
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
            "id" => 1001);
    }
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR . "usermanagementTest" . DIRECTORY_SEPARATOR . "usermanagement.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR .  "usermanagementTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test successful student creation
     * @group api
     */
    public function test_create_student_success() {
        // Test s create - SUCCESS.
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $user->create($params, $userid));
        // Check user is enrolled on expected moulde.
        $querytable = $this->getConnection()->createQueryTable('modules_student', 'SELECT id, userID, idMod FROM modules_student');
        $expectedtable = $this->get_expected_data_set('createuser')->getTable("modules_student");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test successful staff creation
     * @group api
     */
    public function test_create_staff_success() {
        // Test s create - SUCCESS.
        $responsearray = $this->create_response_array();
        $params = $this->create_staff_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $user->create($params, $userid));
        // Check user is enrolled on expected moulde.
        $querytable = $this->getConnection()->createQueryTable('modules_staff', 'SELECT memberID, idMod FROM modules_staff');
        $expectedtable = $this->get_expected_data_set('createuser')->getTable("modules_staff");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user creation exception user exists
     * @group api
     */
    public function test_create_exception_user() {
        // Test user create - ERROR already exists
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 706;
        $responsearray['status'] = 'User already exists';
        $responsearray['id'] = 1000;
        $params['username'] = 'unit';
        $this->assertEquals($responsearray, $user->create($params, $userid));
    }
    /**
     * Test user creation exception invalid user role
     * @group api
     */
    public function test_create_exception_role() {
        // Test user create - ERROR invalid role
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 707;
        $responsearray['status'] = 'User has invalid role';
        $responsearray['id'] = null;
        $params['username'] = 'unknowntest';
        $params['surname'] = 'unknown';
        $params['role'] = 'unknownrole';
        $this->assertEquals($responsearray, $user->create($params, $userid));
    }
    /**
     * Test user creation exception invalid course
     * @group api
     */
    public function test_create_exception_course() {
        // Test user create - ERROR invalid course
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 705;
        $responsearray['status'] = 'Course does not exist';
        $responsearray['id'] = null;
        $params['username'] = 'unknowntest';
        $params['surname'] = 'unknown';
        $params['role'] = 'Student';
        $params['course'] = 'TEST22';
        $this->assertEquals($responsearray, $user->create($params, $userid));
    }
    /**
     * Test staff creation exception invalid course
     * @group api
     */
    public function test_create_staff_exception_course() {
        // Test s create - SUCCESS.
        $responsearray = $this->create_response_array();
        $params = $this->create_staff_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 705;
        $responsearray['status'] = 'Course does not exist';
        $responsearray['id'] = null;
        $params['username'] = 'unknowntest';
        $params['surname'] = 'unknown';
        $params['role'] = 'Staff';
        $params['course'] = 'Invalid';
        $this->assertEquals($responsearray, $user->create($params, $userid));
    }
    /**
     * Test successful user update
     * @group api
     */
    public function test_update_success() {
        // Test user update - SUCCESS.
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $user->update($params, $userid));
        // Check user is enrolled on expected moulde.
        $querytable = $this->getConnection()->createQueryTable('modules_student', 'SELECT id, userID, idMod FROM modules_student');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("modules_student");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception nothing to update - blank username
     * @group api
     */
    public function test_update_exception_noupdate() {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        // Username.
        $params = array(
            "nodeid" => 1,
            "id" => 1002,
            "username" => "");
        $this->assertEquals($responsearray, $user->update($params, $userid));
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT * FROM users WHERE id = 1002');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception nothing to update  - blank password
     * @group api
     */
    public function test_update_exception_noupdate2() {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        // Password.
        $params = array(
            "nodeid" => 1,
            "id" => 1002,
            "password" => "");
        $this->assertEquals($responsearray, $user->update($params, $userid));
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT * FROM users WHERE id = 1002');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception nothing to update  - blank title
     * @group api
     */
    public function test_update_exception_noupdate3() {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        // Title.
        $params = array(
            "nodeid" => 1,
            "id" => 1002,
            "title" => "");
        $this->assertEquals($responsearray, $user->update($params, $userid));
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT * FROM users WHERE id = 1002');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception nothing to update  - blank forename
     * @group api
     */
    public function test_update_exception_noupdate4() {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        // Forename.
        $params = array(
            "nodeid" => 1,
            "id" => 1002,
            "forename" => "");
        $this->assertEquals($responsearray, $user->update($params, $userid));
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT * FROM users WHERE id = 1002');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception nothing to update  - blank surname
     * @group api
     */
    public function test_update_exception_noupdate5() {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        // Surname.
        $params = array(
            "nodeid" => 1,
            "id" => 1002,
            "surname" => "");
        $this->assertEquals($responsearray, $user->update($params, $userid));
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT * FROM users WHERE id = 1002');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception nothing to update  - blank email
     * @group api
     */
    public function test_update_exception_noupdate6() {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        // Email.
        $params = array(
            "nodeid" => 1,
            "id" => 1002,
            "email" => "");
        $this->assertEquals($responsearray, $user->update($params, $userid));
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT * FROM users WHERE id = 1002');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception nothing to update  - blank course
     * @group api
     */
    public function test_update_exception_noupdate7() {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        // Course.
        $params = array(
            "nodeid" => 1,
            "id" => 1002,
            "course" => "");
        $this->assertEquals($responsearray, $user->update($params, $userid));
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT * FROM users WHERE id = 1002');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception nothing to update  - blank gender
     * @group api
     */
    public function test_update_exception_noupdate8() {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        // Gender.
        $params = array(
            "nodeid" => 1,
            "id" => 1002,
            "gender" => "");
        $this->assertEquals($responsearray, $user->update($params, $userid));
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT * FROM users WHERE id = 1002');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception nothing to update  - blank year
     * @group api
     */
    public function test_update_exception_noupdate9() {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        // Year.
        $params = array(
            "nodeid" => 1,
            "id" => 1002,
            "year" => "");
        $this->assertEquals($responsearray, $user->update($params, $userid));
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT * FROM users WHERE id = 1002');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception nothing to update  - blank role
     * @group api
     */
    public function test_update_exception_noupdate10() {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        // Role.
        $params = array(
            "nodeid" => 1,
            "id" => 1002,
            "role" => "");
        $this->assertEquals($responsearray, $user->update($params, $userid));
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT * FROM users WHERE id = 1002');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception nothing to update  - blank sid
     * @group api
     */
    public function test_update_exception_noupdate11() {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        // Student id.
        $params = array(
            "nodeid" => 1,
            "id" => 1002,
            "studentid" => "");
        $this->assertEquals($responsearray, $user->update($params, $userid));
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT * FROM users WHERE id = 1002');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception nothing to update  - blank initials
     * @group api
     */
    public function test_update_exception_noupdate12() {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        // Initials.
        $params = array(
            "nodeid" => 1,
            "id" => 1002,
            "initials" => "");
        $this->assertEquals($responsearray, $user->update($params, $userid));
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT * FROM users WHERE id = 1002');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception nothing to update - blank modules
     * @group api
     */
    public function test_update_exception_noupdate13() {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        // Modules.
        $params = array(
            "nodeid" => 1,
            "id" => 1002,
            "modules" => array());
        $this->assertEquals($responsearray, $user->update($params, $userid));
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT * FROM users WHERE id = 1002');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception nothing to update - no changes
     * @group api
     */
    public function test_update_exception_noupdate14() {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        // Modules.
        $params = array(
            "nodeid" => 1,
            "id" => 1002,
            "password" => "12345678",
            "surname" => "test3",
            "username" => "unit3",
            "roles" => "Student",
            "course" => "TEST2");
        $this->assertEquals($responsearray, $user->update($params, $userid));
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT * FROM users WHERE id = 1002');
        $expectedtable = $this->get_expected_data_set('updateuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user update exception user does not exist
     * @group api
     */
    public function test_update_exception_user() {
        // Test user update - ERROR user does not exist
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 701;
        $responsearray['status'] = 'User does not exist';
        $responsearray['id'] = null;
        $params['id'] = '99';
        $params['surname'] = 'unknown';
        $this->assertEquals($responsearray, $user->update($params, $userid));
    }
    /**
     * Test user update exception user id = 0
     * @group api
     */
    public function test_update_exception_user2() {
        // Test user update - ERROR user does not exist
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 701;
        $responsearray['status'] = 'User does not exist';
        $responsearray['id'] = null;
        $params['id'] = '0';
        $params['surname'] = 'unknown';
        $this->assertEquals($responsearray, $user->update($params, $userid));
    }
    /**
     * Test successful user deletion
     * @group api
     */
    public function test_delete_success() {
        // Test user deletion - SUCCESS.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $this->assertEquals($responsearray, $user->delete($params, $userid));
        // Check that the remaining user are correct, when we delete a user we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->getConnection()->createQueryTable('users', 'SELECT id, password, surname, username, roles, grade FROM users WHERE user_deleted is NULL');
        $expectedtable = $this->get_expected_data_set('deleteuser')->getTable("users");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test user deletion exception user does not exist
     * @group api
     */
    public function test_delete_exception_user() {
        // Test deleting a non existance user.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 701;
        $responsearray['status'] = 'User does not exist';
        $responsearray['id'] = null;
        $params['id'] = 99;
        $this->assertEquals($responsearray, $user->delete($params, $userid));
        // Test id not supplied.
        $params = array(
            "nodeid" => 1);
        $this->assertEquals($responsearray, $user->delete($params, $userid));
    }
    /**
     * Test user deletion exception user does not exist
     * @group api
     */
    public function test_delete_exception_inuse() {
        // Test deleting a user in use. case 1 - in log_metadata
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $user = new \api\usermanagement($this->db);
        $userid = 1;
        $responsearray['statuscode'] = 704;
        $responsearray['status'] = 'User not deleted, as they have taken a paper';
        $responsearray['id'] = null;
        $params['id'] = 1000;
        $this->assertEquals($responsearray, $user->delete($params, $userid));
        // Test deleting a user in use. case 2 - in log4_overall
        $responsearray['statuscode'] = 704;
        $responsearray['status'] = 'User not deleted, as they have taken a paper';
        $params['id'] = 1002;
        $this->assertEquals($responsearray, $user->delete($params, $userid));
    }
}