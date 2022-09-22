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
 * Test schoolmanagement api class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class schoolmanagementtest extends unittestdatabase
{
    /**
     * @var integer Storage for faculty id in tests
     */
    private $faculty2;

    /**
     * @var integer Storage for school id in tests
     */
    private $school2;

    /**
     * @var array Storage for school data in tests
     */
    private $school3;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $this->faculty2 = $this->get_faculty_id('Administrative and Support Units');
        $this->school2 = $this->get_school_id('Training');
        $datagenerator = $this->get_datagenerator('school', 'core');
        $this->school3 = $datagenerator->create_school(array('school' => 'Test school 3', 'facultyID' => $this->faculty));
        $datagenerator = $this->get_datagenerator('modules', 'core');
        $datagenerator->create_module(array('moduleid' => 'TEST', 'fullname' => 'Another test module', 'schoolID' => $this->school3['id']));
        $datagenerator = $this->get_datagenerator('course', 'core');
        $datagenerator->create_course(array('name' => 'TEST', 'description' => 'Test course', 'schoolid' => $this->school2));
    }

    /**
     * Create a response array for creation
     * @return array the response array
     */
    private function create_response_array()
    {
        return array(
            'statuscode' => 100,
            'status' => 'OK',
            'id' => $this->school3['id'] + 1,
            'externalid' => null,
            'error' => null,
            'node' => 'create',
            'nodeid' => 1);
    }

    /**
     * Create a parameter array for creation
     * @return array the param array
     */
    private function create_param_array()
    {
        return array(
            'nodeid' => 1,
            'name' => 'CREATE',
            'faculty' => 'Test faculty',
            'code' => 'TST',
            'externalid' => 'xyz');
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
            'id' => $this->school,
            'externalid' => null,
            'error' => null,
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
            'id' => $this->school,
            'name' => 'Test school update',
            'faculty' => 'Test faculty');
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
            'id' => $this->school,
            'externalid' => null,
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
            'id' => $this->school);
    }

    /**
     * Test successful school create
     * @group api
     */
    public function test_create_success()
    {
        // Test school creation - SUCCESS
        $responsearray = $this->create_response_array();
        $responsearray['externalid'] = 'xyz';
        $params = $this->create_param_array();
        $school = new \api\schoolmanagement($this->db, 'test1');
        $this->assertEquals($responsearray, $school->create($params, $this->admin['id']));
    }

    /**
     * Test school create exception invalid school (external system)
     * @group api
     */
    public function test_create_exception_school()
    {
        // Test school creation - ERROR school already exists
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $school = new \api\schoolmanagement($this->db, 'test1');
        $responsearray['statuscode'] = 606;
        $responsearray['status'] = 'School already exists';
        $responsearray['id'] = $this->school;
        $responsearray['externalid'] = 'ABC';
        $params = array(
            'nodeid' => 1,
            'name' => 'UNKNOWN School',
            'faculty' => 'UNKNOWN Faculty');
        $this->assertEquals($responsearray, $school->create($params, $this->admin['id']));
    }

    /**
     * Test school create exception invalid school (non external system)
     * @group api
     */
    public function test_create_exception_school2()
    {
        $responsearray = $this->create_response_array();
        $school = new \api\schoolmanagement($this->db, 'test1');
        $responsearray['statuscode'] = 606;
        $responsearray['status'] = 'School already exists';
        $responsearray['id'] = $this->school3['id'];
        $params = array(
            'nodeid' => 1,
            'name' => 'Test school 3',
            'faculty' => 'UNKNOWN Faculty');
        $this->assertEquals($responsearray, $school->create($params, $this->admin['id']));
    }

    /**
     * Test school create exception invalid faculty
     * @group api
     */
    public function test_create_exception_faculty()
    {
        // Test school creation - ERROR faculty not supplied
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $school = new \api\schoolmanagement($this->db, 'test1');
        $responsearray['statuscode'] = 605;
        $responsearray['status'] = 'Faculty not supplied';
        $responsearray['id'] = null;
        $params = array(
            'nodeid' => 1,
            'name' => 'CREATE 2',
            'faculty' => '');
        $this->assertEquals($responsearray, $school->create($params, $this->admin['id']));
    }

    /**
     * Test successful school update
     * @group api
     */
    public function test_update_success()
    {
        // Test school update - SUCCESS
        $responsearray = $this->update_response_array();
        $responsearray['externalid'] = 'ABC';
        $params = $this->update_param_array();
        $school = new \api\schoolmanagement($this->db, 'test1');
        $this->assertEquals($responsearray, $school->update($params, $this->admin['id']));
        // Test with no faculty provided i.e. school name update.
        $responsearray['id'] = $this->school2;
        $responsearray['externalid'] = null;
        $params = array(
            'nodeid' => 1,
            'id' => $this->school3['id'],
            'name' => 'Test school 3 update');
        $this->assertEquals($responsearray, $school->update($params, $this->admin['id']));
        // Test with no name provided i.e. faculty update.
        $params = array(
            'nodeid' => 1,
            'id' => $this->school3['id'],
            'faculty' => 'Administrative and Support Units');
        $this->assertEquals($responsearray, $school->update($params, $this->admin['id']));
    }

    /**
     * Test school update exception nothing to update
     * @group api
     */
    public function test_update_exception_noupdate()
    {
        $responsearray = $this->update_response_array();
        $params = array(
            'nodeid' => 1,
            'id' => $this->school,
            'name' => 'UNKNOWN School',
            'faculty' => 'UNKNOWN Faculty');
        $school = new \api\schoolmanagement($this->db, 'test1');
        $responsearray['statuscode'] = 607;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $this->assertEquals($responsearray, $school->update($params, $this->admin['id']));
    }

    /**
     * Test school update exception invalid school
     * @group api
     */
    public function test_update_exception_school()
    {
        // Test school update - ERROR school does not exist
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $school = new \api\schoolmanagement($this->db, 'test1');
        $responsearray['statuscode'] = 601;
        $responsearray['status'] = 'School does not exist';
        $responsearray['id'] = null;
        $params['id'] = 100;
        $this->assertEquals($responsearray, $school->update($params, $this->admin['id']));
    }

    /**
     * Test school update exception no school supplied
     * @group api
     */
    public function test_update_exception_school2()
    {
        // Test school update - ERROR school does not exist
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $school = new \api\schoolmanagement($this->db, 'test1');
        $responsearray['statuscode'] = 602;
        $responsearray['status'] = 'School not updated';
        $responsearray['id'] = null;
        $params['name'] = '';
        $params['faculty'] = 'Test faculty 2';
        $this->assertEquals($responsearray, $school->update($params, $this->admin['id']));
    }

    /**
     * Test school update exception invalid faculty
     * @group api
     */
    public function test_update_exception_faculty()
    {
        // Test school update - ERROR faculty invalid
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $school = new \api\schoolmanagement($this->db, 'test1');
        $responsearray['statuscode'] = 605;
        $responsearray['status'] = 'Faculty not supplied';
        $responsearray['id'] = null;
        $params['faculty'] = '';
        $this->assertEquals($responsearray, $school->update($params, $this->admin['id']));
    }

    /**
     * Test successful school deletion
     * @group api
     */
    public function test_delete_success()
    {
        // Test school deletion - SUCCESS.
        $responsearray = $this->delete_response_array();
        $responsearray['externalid'] = 'ABC';
        $params = $this->delete_param_array();
        $school = new \api\schoolmanagement($this->db, 'test1');
        $this->assertEquals($responsearray, $school->delete($params, $this->admin['id']));
        // Check that the remaining schools are correct, when we delete a school we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->query(array('columns' => array('school', 'facultyID'), 'table' => 'schools', 'where' => array(array('column' => 'deleted', 'value' => null, 'operator' => 'IS'))));
        $expectedtable = array(
            0 => array(
                'school' => 'Training',
                'facultyID' => $this->faculty2
            ),
            1 => array(
                'school' => 'Test school 3',
                'facultyID' => $this->faculty
            ),
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test school deletion exception invalid school
     * @group api
     */
    public function test_delete_exception_school()
    {
        // Test deleting a non existance school.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $school = new \api\schoolmanagement($this->db, 'test1');
        $responsearray['statuscode'] = 601;
        $responsearray['status'] = 'School does not exist';
        $responsearray['id'] = null;
        $params['id'] = 0;
        $this->assertEquals($responsearray, $school->delete($params, $this->admin['id']));
        // Test no school id supplued.
        $params = array(
            'nodeid' => 1);
        $this->assertEquals($responsearray, $school->delete($params, $this->admin['id']));
    }

    /**
     * Test school deletion exception in use
     * @group api
     */
    public function test_delete_exception_inuse()
    {
        // Test deleting a school in use - in a course.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $school = new \api\schoolmanagement($this->db, 'test1');
        $responsearray['statuscode'] = 604;
        $responsearray['status'] = 'School not deleted, as in use by a course or module';
        $responsearray['id'] = null;
        $params['id'] = 2;
        $this->assertEquals($responsearray, $school->delete($params, $this->admin['id']));
        // Test deleting a school in use - in a module.
        $responsearray['statuscode'] = 604;
        $responsearray['status'] = 'School not deleted, as in use by a course or module';
        $params['id'] = $this->school3['id'];
        $this->assertEquals($responsearray, $school->delete($params, $this->admin['id']));
    }
}
