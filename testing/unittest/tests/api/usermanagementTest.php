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
class usermanagementtest extends unittestdatabase
{
    /** @var integer Storage for user id in tests. */
    private $user;

    /** @var integer Storage for user id in tests. */
    private $user2;

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
        $this->user = $this->get_user_id('test2');
        $this->module2 = $this->get_module_id('SYSTEM');
        $datagenerator = $this->get_datagenerator('users', 'core');
        $user = $datagenerator->create_user(array('surname' => 'test3', 'username' => 'unit3', 'grade' => 'TEST2', 'initials' => null,
            'title' => null, 'email' => null, 'gender' => null, 'first_names' => null, 'yearofstudy' => null, 'sid' => '141516171819'));
        $this->user2 = $user['id'];
        $datagenerator = $this->get_datagenerator('course', 'core');
        $datagenerator->create_course(array('name' => 'TEST', 'description' => 'Test course', 'schoolid' => $this->school));
        $datagenerator->create_course(array('name' => 'TEST2', 'description' => 'Test course 2', 'schoolid' => $this->school));
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $pid = $datagenerator->create_paper(array('papertitle' => 'Test create formative',
            'startdate' => '2016-01-25 09:00:00',
            'enddate' => '2016-01-25 10:00:00',
            'duration' => 60,
            'timezone' => 'Europe/London',
            'paperowner' => 'admin',
            'papertype' => '0',
            'externalid' => '123abc456',
            'externalsys' => 'test rogo api',
            'modulename' => 'Training Module'));
        $pid2 = $datagenerator->create_paper(array('papertitle' => 'Test create osce',
            'startdate' => '2016-01-25 09:00:00',
            'enddate' => '2016-01-25 10:00:00',
            'duration' => 60,
            'timezone' => 'Europe/London',
            'paperowner' => 'admin',
            'papertype' => '4',
            'modulename' => 'Training Module'));
        $datagenerator = $this->get_datagenerator('log', 'core');
        $datagenerator->create_metadata(array('userID' => $this->student['id'], 'paperID' => $pid2['id']));
        $datagenerator->create_osceoverall(array('userID' => $this->user2, 'q_paper' => $pid['id']));
    }

    /**
     * Create a student response array for creation
     * @return array the response array
     */
    private function create_response_array()
    {
        return array(
            'statuscode' => 100,
            'status' => 'OK',
            'id' => $this->user2 + 1,
            'externalid' => null,
            'error' => array(),
            'node' => 'create',
            'nodeid' => 1);
    }

    /**
     * Create a student parameter array for creation
     * @return array the param array
     */
    private function create_param_array()
    {
        return array(
            'nodeid' => 1,
            'username' => 'testy',
            'surname' => 'tester',
            'role' => 'Student',
            'course' => 'TEST2',
            'modules' => array(array('name' => 'moduleid', 'id' => 0, 'value' => $this->module)));
    }

    /**
     * Create a staff parameter array for creation
     * @return array the param array
     */
    private function create_staff_param_array()
    {
        return array(
            'nodeid' => 1,
            'username' => 'staff',
            'surname' => 'staffy',
            'role' => 'Staff',
            'course' => 'University Lecturer',
            'modules' => array(array('name' => 'moduleid', 'id' => 0, 'value' => $this->module)));
    }

    /**
     * Create a response array for updates
     * @return array the response array
     */
    private function update_response_array()
    {
        return array(
            'statuscode' => 100,
            'status' => 'OK',
            'id' => $this->user,
            'externalid' => '00000001',
            'error' => array(),
            'node' => 'update',
            'nodeid' => 1);
    }

    /**
     * Create a parameter array for updates
     * @return array the param array
     */
    private function update_param_array()
    {
        return array(
            'nodeid' => 1,
            'id' => $this->user,
            'externalid' => null,
            'surname' => '',
            'forename' => 'test',
            'modules' => array(array('name' => 'moduleid', 'id' => 0, 'value' => $this->module2)));
    }

    /**
     * Create a response array for deletion
     * @return array the response array
     */
    private function delete_response_array()
    {
        return array(
            'statuscode' => 100,
            'status' => 'OK',
            'id' => $this->user,
            'externalid' => '00000001',
            'error' => null,
            'node' => 'delete',
            'nodeid' => 1);
    }

    /**
     * Create a parameter array for deletion
     * @return array the param array
     */
    private function delete_param_array()
    {
        return array(
            'nodeid' => 1,
            'id' => $this->user);
    }

    /**
     * Test successful student creation
     * @group api
     */
    public function test_create_student_success()
    {
        // Test s create - SUCCESS.
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $create = $user->create($params, $this->admin['id']);
        $this->assertEquals($responsearray, $create);
        // Check user is enrolled on expected moulde.
        $querytable = $this->query(array('columns' => array('userID', 'idMod'), 'table' => 'modules_student'));
        $expectedtable = array (
            0 => array(
                'userID' => $create['id'],
                'idMod' => $this->module
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test successful staff creation
     * @group api
     */
    public function test_create_staff_success()
    {
        // Test s create - SUCCESS.
        $responsearray = $this->create_response_array();
        $params = $this->create_staff_param_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $create = $user->create($params, $this->admin['id']);
        $this->assertEquals($responsearray, $create);
        // Check user is enrolled on expected moulde.
        $querytable = $this->query(array('columns' => array('memberID', 'idMod'), 'table' => 'modules_staff'));
        $expectedtable = array(
            0 => array(
                'memberID' => $create['id'],
                'idMod' => $this->module
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user creation exception user exists
     * @group api
     */
    public function test_create_exception_user()
    {
        // Test user create - ERROR already exists
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 706;
        $responsearray['status'] = 'User already exists';
        $responsearray['id'] = $this->student['id'];
        $responsearray['externalid'] = '1234567890';
        $params['username'] = 'test1';
        $this->assertEquals($responsearray, $user->create($params, $this->admin['id']));
    }

    /**
     * Test user creation exception invalid user role
     * @group api
     */
    public function test_create_exception_role()
    {
        // Test user create - ERROR invalid role
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 707;
        $responsearray['status'] = 'User has invalid role';
        $responsearray['id'] = null;
        $params['username'] = 'unknowntest';
        $params['surname'] = 'unknown';
        $params['role'] = 'Left';
        $this->assertEquals($responsearray, $user->create($params, $this->admin['id']));
    }

    /**
     * Test user creation exception invalid course
     * @group api
     */
    public function test_create_exception_course()
    {
        // Test user create - ERROR invalid course
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 705;
        $responsearray['status'] = 'Course does not exist';
        $responsearray['id'] = null;
        $params['username'] = 'unknowntest';
        $params['surname'] = 'unknown';
        $params['role'] = 'Student';
        $params['course'] = 'TEST22';
        $this->assertEquals($responsearray, $user->create($params, $this->admin['id']));
    }

    /**
     * Test staff creation exception invalid course
     * @group api
     */
    public function test_create_staff_exception_course()
    {
        // Test s create - SUCCESS.
        $responsearray = $this->create_response_array();
        $params = $this->create_staff_param_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 705;
        $responsearray['status'] = 'Course does not exist';
        $responsearray['id'] = null;
        $params['username'] = 'unknowntest';
        $params['surname'] = 'unknown';
        $params['role'] = 'Staff';
        $params['course'] = 'Invalid';
        $this->assertEquals($responsearray, $user->create($params, $this->admin['id']));
    }

    /**
     * Test successful user update
     * @group api
     */
    public function test_update_success()
    {
        // Test user update - SUCCESS.
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $update = $user->update($params, $this->admin['id']);
        $this->assertEquals($responsearray, $update);
        // Check user is enrolled on expected module.
        $querytable = $this->query(array('columns' => array('userID', 'idMod'), 'table' => 'modules_student'));
        $expectedtable = array (
            0 => array(
                'userID' => $update['id'],
                'idMod' => $this->module2
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception nothing to update - blank username
     * @group api
     */
    public function test_update_exception_noupdate()
    {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        // Username.
        $params = array(
            'nodeid' => 1,
            'id' => $this->user2,
            'username' => '');
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
        $querytable = $this->query(array('table' => 'users', 'columns' => array('username'), 'where' => array(array('column' => 'id', 'value' => $this->user2))));
        $expectedtable = array(
            0 => array (
                'username' => 'unit3'
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception nothing to update  - blank password
     * @group api
     */
    public function test_update_exception_noupdate2()
    {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        // Password.
        $params = array(
            'nodeid' => 1,
            'id' => $this->user2,
            'password' => '');
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
        $querytable = $this->query(array('table' => 'users', 'columns' => array('password'), 'where' => array(array('column' => 'id', 'value' => $this->user2))));
        $expectedtable = array(
            0 => array (
                'password' => '$6$033jGxQoVIVcYEgX5/wSBQrGEpoYvSvi4cPyjX8C7NLIgcXI4c5WfYKtmYuE.AEaYVUitfeIVxNkhjEkRGZsb1'
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception nothing to update  - blank title
     * @group api
     */
    public function test_update_exception_noupdate3()
    {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        // Title.
        $params = array(
            'nodeid' => 1,
            'id' => $this->user2,
            'title' => '');
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
        $querytable = $this->query(array('table' => 'users', 'columns' => array('title'), 'where' => array(array('column' => 'id', 'value' => $this->user2))));
        $expectedtable = array(
            0 => array (
                'title' => null
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception nothing to update  - blank forename
     * @group api
     */
    public function test_update_exception_noupdate4()
    {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        // Forename.
        $params = array(
            'nodeid' => 1,
            'id' => $this->user2,
            'forename' => '');
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
        $querytable = $this->query(array('table' => 'users', 'columns' => array('first_names'), 'where' => array(array('column' => 'id', 'value' => $this->user2))));
        $expectedtable = array(
            0 => array (
                'first_names' => null
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception nothing to update  - blank surname
     * @group api
     */
    public function test_update_exception_noupdate5()
    {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        // Surname.
        $params = array(
            'nodeid' => 1,
            'id' => $this->user2,
            'surname' => '');
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
        $querytable = $this->query(array('table' => 'users', 'columns' => array('surname'), 'where' => array(array('column' => 'id', 'value' => $this->user2))));
        $expectedtable = array(
            0 => array (
                'surname' => 'test3'
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception nothing to update  - blank email
     * @group api
     */
    public function test_update_exception_noupdate6()
    {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        // Email.
        $params = array(
            'nodeid' => 1,
            'id' => $this->user2,
            'email' => '');
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
        $querytable = $this->query(array('table' => 'users', 'columns' => array('email'), 'where' => array(array('column' => 'id', 'value' => $this->user2))));
        $expectedtable = array(
            0 => array (
                'email' => null
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception nothing to update  - blank course
     * @group api
     */
    public function test_update_exception_noupdate7()
    {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        // Course.
        $params = array(
            'nodeid' => 1,
            'id' => $this->user2,
            'course' => '');
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
        $querytable = $this->query(array('table' => 'users', 'columns' => array('grade'), 'where' => array(array('column' => 'id', 'value' => $this->user2))));
        $expectedtable = array(
            0 => array (
                'grade' => 'TEST2'
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception nothing to update  - blank gender
     * @group api
     */
    public function test_update_exception_noupdate8()
    {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db, 'test1');
        ;
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        // Gender.
        $params = array(
            'nodeid' => 1,
            'id' => $this->user2,
            'gender' => '');
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
        $querytable = $this->query(array('table' => 'users', 'columns' => array('gender'), 'where' => array(array('column' => 'id', 'value' => $this->user2))));
        $expectedtable = array(
            0 => array (
                'gender' => null
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception nothing to update  - blank year
     * @group api
     */
    public function test_update_exception_noupdate9()
    {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        // Year.
        $params = array(
            'nodeid' => 1,
            'id' => $this->user2,
            'year' => '');
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
        $querytable = $this->query(array('table' => 'users', 'columns' => array('yearofstudy'), 'where' => array(array('column' => 'id', 'value' => $this->user2))));
        $expectedtable = array(
            0 => array (
                'yearofstudy' => null
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception nothing to update  - blank role
     * @group api
     */
    public function test_update_exception_noupdate10()
    {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        // Role.
        $params = array(
            'nodeid' => 1,
            'id' => $this->user2,
            'role' => '');
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
        $querytable = $this->query(array('table' => 'user_roles', 'columns' => array('roleid'), 'where' => array(array('column' => 'userid', 'value' => $this->user2))));
        $expectedtable = array(
            0 => array (
                'roleid' => 4,
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception nothing to update  - blank school
     * @group api
     */
    public function test_update_exception_noupdate11()
    {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        // Student id.
        $params = array(
            'nodeid' => 1,
            'id' => $this->user2,
            'studentid' => '');
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
        $querytable = $this->query(array('table' => 'users', 'columns' => array('yearofstudy', 'gender', 'grade', 'email',
            'surname', 'first_names', 'title', 'password', 'username', 'initials'), 'where' => array(array('column' => 'id', 'value' => $this->user2))));
        $expectedtable = array(
            0 => array (
                'yearofstudy' => null,
                'gender' => null,
                'grade' => 'TEST2',
                'email' => null,
                'surname' => 'test3',
                'first_names' => null,
                'title' => null,
                'password' => '$6$033jGxQoVIVcYEgX5/wSBQrGEpoYvSvi4cPyjX8C7NLIgcXI4c5WfYKtmYuE.AEaYVUitfeIVxNkhjEkRGZsb1',
                'username' => 'unit3',
                'initials' => null
            )
        );
        $this->assertEquals($expectedtable, $querytable);
        $querytable = $this->query(array('table' => 'user_roles', 'columns' => array('roleid'), 'where' => array(array('column' => 'userid', 'value' => $this->user2))));
        $expectedtable = array(
                0 => array (
                        'roleid' => 4,
                )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception nothing to update  - blank initials
     * @group api
     */
    public function test_update_exception_noupdate12()
    {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        // Initials.
        $params = array(
            'nodeid' => 1,
            'id' => $this->user2,
            'initials' => '');
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
        $querytable = $this->query(array('table' => 'users', 'columns' => array('initials'), 'where' => array(array('column' => 'id', 'value' => $this->user2))));
        $expectedtable = array(
            0 => array (
                'initials' => null
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception nothing to update - blank modules
     * @group api
     */
    public function test_update_exception_noupdate13()
    {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        // Modules.
        $params = array(
            'nodeid' => 1,
            'id' => $this->user2,
            'modules' => array());
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
        $querytable = $this->query(array('table' => 'users', 'columns' => array('yearofstudy', 'gender', 'grade', 'email',
            'surname', 'first_names', 'title', 'password', 'username', 'initials'), 'where' => array(array('column' => 'id', 'value' => $this->user2))));
        $expectedtable = array(
            0 => array (
                'yearofstudy' => null,
                'gender' => null,
                'grade' => 'TEST2',
                'email' => null,
                'surname' => 'test3',
                'first_names' => null,
                'title' => null,
                'password' => '$6$033jGxQoVIVcYEgX5/wSBQrGEpoYvSvi4cPyjX8C7NLIgcXI4c5WfYKtmYuE.AEaYVUitfeIVxNkhjEkRGZsb1',
                'username' => 'unit3',
                'initials' => null
            )
        );
        $this->assertEquals($expectedtable, $querytable);
        $querytable = $this->query(array('table' => 'user_roles', 'columns' => array('roleid'), 'where' => array(array('column' => 'userid', 'value' => $this->user2))));
        $expectedtable = array(
                0 => array (
                        'roleid' => 4,
                )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception nothing to update - no changes
     * @group api
     */
    public function test_update_exception_noupdate14()
    {
        $responsearray = $this->update_response_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 708;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        // Modules.
        $params = array(
            'nodeid' => 1,
            'id' => $this->user2,
            'password' => '',
            'surname' => 'test3',
            'username' => 'unit3',
            'roles' => 'Student',
            'course' => 'TEST2');
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
        $querytable = $this->query(array('table' => 'users', 'columns' => array('yearofstudy', 'gender', 'grade', 'email',
            'surname', 'first_names', 'title', 'password', 'username', 'initials'), 'where' => array(array('column' => 'id', 'value' => $this->user2))));
        $expectedtable = array(
            0 => array (
                'yearofstudy' => null,
                'gender' => null,
                'grade' => 'TEST2',
                'email' => null,
                'surname' => 'test3',
                'first_names' => null,
                'title' => null,
                'password' => '$6$033jGxQoVIVcYEgX5/wSBQrGEpoYvSvi4cPyjX8C7NLIgcXI4c5WfYKtmYuE.AEaYVUitfeIVxNkhjEkRGZsb1',
                'username' => 'unit3',
                'initials' => null
            )
        );
        $this->assertEquals($expectedtable, $querytable);
        $querytable = $this->query(array('table' => 'user_roles', 'columns' => array('roleid'), 'where' => array(array('column' => 'userid', 'value' => $this->user2))));
        $expectedtable = array(
                0 => array (
                        'roleid' => 4,
                )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user update exception user does not exist
     * @group api
     */
    public function test_update_exception_user()
    {
        // Test user update - ERROR user does not exist
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 701;
        $responsearray['status'] = 'User does not exist';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        $params['id'] = 0;
        $params['surname'] = 'unknown';
        $this->assertEquals($responsearray, $user->update($params, $this->admin['id']));
    }

    /**
     * Test successful user deletion
     * @group api
     */
    public function test_delete_success()
    {
        // Test user deletion - SUCCESS.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $this->assertEquals($responsearray, $user->delete($params, $this->admin['id']));
        // Check that the remaining user are correct, when we delete a user we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->query(array('columns' => array('username'), 'table' => 'users', 'where' => array(array('column' => 'user_deleted', 'value' => null, 'operator' => 'IS'))));
        $expectedtable = array(
            0 => array (
                'username' => 'admin'
            ),
            1 => array (
                'username' => 'test1'
            ),
            2 => array (
                'username' => 'unit3'
            ),
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test user deletion exception user does not exist
     * @group api
     */
    public function test_delete_exception_user()
    {
        // Test deleting a non existance user.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 701;
        $responsearray['status'] = 'User does not exist';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        $params['id'] = 0;
        $this->assertEquals($responsearray, $user->delete($params, $this->admin['id']));
        // Test id not supplied.
        $params = array(
            'nodeid' => 1);
        $this->assertEquals($responsearray, $user->delete($params, $this->admin['id']));
    }

    /**
     * Test user deletion exception user does not exist
     * @group api
     */
    public function test_delete_exception_inuse()
    {
        // Test deleting a user in use. case 1 - in log_metadata
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $user = new \api\usermanagement($this->db, 'test1');
        $responsearray['statuscode'] = 704;
        $responsearray['status'] = 'User not deleted, as they have taken a paper';
        $responsearray['id'] = null;
        $responsearray['externalid'] = null;
        $params['id'] = $this->student['id'];
        $this->assertEquals($responsearray, $user->delete($params, $this->admin['id']));
        // Test deleting a user in use. case 2 - in log4_overall
        $responsearray['statuscode'] = 704;
        $responsearray['status'] = 'User not deleted, as they have taken a paper';
        $params['id'] = $this->user2;
        $this->assertEquals($responsearray, $user->delete($params, $this->admin['id']));
    }
}
