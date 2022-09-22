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
 * Test facultyemanagement api class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class facultymanagementtest extends unittestdatabase
{
    /** @var integer Storage for faculty id in tests. */
    private $faculty2;

    /** @var integer Storage for faculty id in tests. */
    private $faculty3;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $this->faculty2 = $this->get_faculty_id('Administrative and Support Units');
        $datagenerator = $this->get_datagenerator('faculty', 'core');
        $faculty = $datagenerator->create_faculty(array('externalid' => 'abcdef', 'externalsys' => 'ExamSys test api', 'code' => 'TEST'));
        $this->faculty3 = $faculty['id'];
    }

    /**
     * Create a response array for creation
     * @return array the resposne array
     */
    private function create_response_array()
    {
        return array(
            'statuscode' => 100,
            'status' => 'OK',
            'id' => $this->faculty3 + 1,
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
            'name' => 'TEST3',
            'code' => 'T3',
            'externalid' => 'xyz');
    }

    /**
     * Create a parameter array for updates
     * @return array the param array
     */
    private function update_param_array()
    {
        return array(
            'nodeid' => 1,
            'id' => $this->faculty,
            'name' => 'TESTUPDATE');
    }

    /**
     * Create a response array for updates
     * @return array the resposne array
     */
    private function update_response_array()
    {
        return array(
            'statuscode' => 100,
            'status' => 'OK',
            'id' => $this->faculty,
            'externalid' => null,
            'error' => null,
            'node' => 'update',
            'nodeid' => 1);
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
            'id' => $this->faculty3,
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
            'id' => $this->faculty3);
    }

    /**
     * Test successful faculty creation - external system faculty
     * @group api
     */
    public function test_create()
    {
        // Test faculty creation - SUCCESS
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $faculty = new \api\facultymanagement($this->db, 'test1');
        $responsearray['externalid'] = 'xyz';
        $this->assertEquals($responsearray, $faculty->create($params, $this->admin['id']));
    }

    /**
     * Test successful faculty creation  - non external system faculty
     * @group api
     */
    public function test_create2()
    {
        $responsearray = $this->create_response_array();
        $params = array(
            'nodeid' => 1,
            'name' => 'TEST3',
            'code' => 'T3');
        $faculty = new \api\facultymanagement($this->db, 'test1');
        $this->assertEquals($responsearray, $faculty->create($params, $this->admin['id']));
    }

    /**
     * Test successful faculty creation  - name exits but new code
     * @group api
     */
    public function test_create3()
    {
        $responsearray = $this->create_response_array();
        $params = array(
            'nodeid' => 1,
            'name' => 'Test name',
            'code' => 'T3');
        $faculty = new \api\facultymanagement($this->db, 'test1');
        $this->assertEquals($responsearray, $faculty->create($params, $this->admin['id']));
    }

    /**
     * Test faculty creation exception faculty exists
     * @group api
     */
    public function test_create_exception_faculty()
    {
        // Test faculty creation - ERROR faculty already exists
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $faculty = new \api\facultymanagement($this->db, 'test1');
        $responsearray['statuscode'] = 405;
        $responsearray['status'] = 'Faculty already exists';
        $responsearray['id'] = $this->faculty;
        $responsearray['externalid'] = 'abcdefghi';
        $params = array(
            'nodeid' => 1,
            'name' => 'UNKNOWN Faculty');
        $this->assertEquals($responsearray, $faculty->create($params, $this->admin['id']));
    }

    /**
     * Test faculty creation exception faculty exists (code exits)
     * @group api
     */
    public function test_create_exception_faculty2()
    {
        $responsearray = $this->create_response_array();
        $params = $this->create_param_array();
        $faculty = new \api\facultymanagement($this->db, 'test1');
        $responsearray['statuscode'] = 405;
        $responsearray['status'] = 'Faculty already exists';
        $responsearray['id'] = $this->faculty3;
        $responsearray['externalid'] = 'abcdef';
        $params = array(
            'nodeid' => 1,
            'name' => 'UNKNOWN Faculty',
            'code' => 'TEST');
        $this->assertEquals($responsearray, $faculty->create($params, $this->admin['id']));
    }

    /**
     * Test faculty create exception faculty not supplied
     * @group api
     */
    public function test_create_exception_nofaculty()
    {
        // Test faculty update - ERROR faculty does not exist
        $responsearray = $this->create_response_array();
        $faculty = new \api\facultymanagement($this->db, 'test1');
        $responsearray['statuscode'] = 406;
        $responsearray['status'] = 'Faculty name not supplied';
        $responsearray['id'] = null;
        $params = array(
            'nodeid' => 1);
        $this->assertEquals($responsearray, $faculty->create($params, $this->admin['id']));
    }

    /**
     * Test successful faculty update
     * @group api
     */
    public function test_update()
    {
        // Test faculty update - SUCCESS
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $faculty = new \api\facultymanagement($this->db, 'test1');
        $responsearray['externalid'] = 'abcdefghi';
        $this->assertEquals($responsearray, $faculty->update($params, $this->admin['id']));
    }

    /**
     * Test faculty update exception nothing to update
     * @group api
     */
    public function test_update_exception_noupdate()
    {
        $responsearray = $this->update_response_array();
        $params = array(
            'nodeid' => 1,
            'id' => $this->faculty,
            'name' => 'UNKNOWN Faculty');
        $faculty = new \api\facultymanagement($this->db, 'test1');
        $responsearray['statuscode'] = 407;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $this->assertEquals($responsearray, $faculty->update($params, $this->admin['id']));
    }

    /**
     * Test faculty update exception faculty does not exist
     * @group api
     */
    public function test_update_exception_faculty()
    {
        // Test faculty update - ERROR faculty does not exist
        $responsearray = $this->update_response_array();
        $params = $this->update_param_array();
        $faculty = new \api\facultymanagement($this->db, 'test1');
        $responsearray['statuscode'] = 401;
        $responsearray['status'] = 'Faculty does not exist';
        $responsearray['id'] = null;
        $params['id'] = 0;
        $this->assertEquals($responsearray, $faculty->update($params, $this->admin['id']));
    }

    /**
     * Test successful faculty deletion
     * @group api
     */
    public function test_delete()
    {
        // Test faculty deletion - SUCCESS.
        $responsearray = $this->delete_response_array();
        $responsearray['externalid'] = 'abcdef';
        $params = $this->delete_param_array();
        $faculty = new \api\facultymanagement($this->db, 'test1');
        $this->assertEquals($responsearray, $faculty->delete($params, $this->admin['id']));
        // Check that the remaining faculty are correct, when we delete a faculty we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->query(array('columns' => array('name'), 'table' => 'faculty', 'where' => array(array('column' => 'deleted', 'value' => null, 'operator' => 'IS'))));
        $expectedtable = array (
            0 => array(
                'name' => 'UNKNOWN Faculty'
            ),
            1 => array(
                'name' => 'Administrative and Support Units'
            ),
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test faculty deletion exception does not exist
     * @group api
     */
    public function test_delete_faculty()
    {
        // Test deleting a non existance faculty.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $faculty = new \api\facultymanagement($this->db, 'test1');
        $responsearray['statuscode'] = 401;
        $responsearray['status'] = 'Faculty does not exist';
        $responsearray['id'] = null;
        $params['id'] = 0;
        $this->assertEquals($responsearray, $faculty->delete($params, $this->admin['id']));
        // No id supplied.
        $params = array(
            'nodeid' => 1);
        $this->assertEquals($responsearray, $faculty->delete($params, $this->admin['id']));
    }

    /**
     * Test faculty deletion exception faculty in use
     * @group api
     */
    public function test_delete_inuse()
    {
        // Test deleting a faculty in use.
        $responsearray = $this->delete_response_array();
        $params = $this->delete_param_array();
        $faculty = new \api\facultymanagement($this->db, 'test1');
        $responsearray['statuscode'] = 404;
        $responsearray['status'] = 'Faculty not deleted, as contains schools';
        $responsearray['id'] = null;
        $params['id'] = $this->faculty2;
        $this->assertEquals($responsearray, $faculty->delete($params, $this->admin['id']));
    }
}
