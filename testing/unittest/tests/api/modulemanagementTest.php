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
 * Test modulemanagement api class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 * @group api
 * @group modulemanagement
 */
class ModuleManagementTest extends unittestdatabase
{

    /**
     * @var integer Storage for user id in test
     */
    private $user;

    /**
     * @var integer Storage for module id in tests
     */
    private $module2;

    /**
     * @var integer Storage for module id in tests
     */
    private $module3;

    /**
     * @var integer Storage for school id in tests
     */
    private $school2;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $this->user = $this->get_user_id('test2');
        $this->module2 = $this->get_module_id('SYSTEM');
        $this->school2 = $this->get_school_id('Training');
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2015, 'academic_year' => '2015/16'));
        $datagenerator->create_academic_year(array('calendar_year' => 2016, 'academic_year' => '2016/17'));
        $datagenerator = $this->get_datagenerator('modules', 'core');
        $module = $datagenerator->create_module(array('fullname' => 'Test module 3', 'moduleid' => 'TEST3'));
        $this->module3 = $module['id'];
        $datagenerator->create_enrolment(
            array(
                'userid' => $this->student['id'],
                'moduleid' => $this->module3,
                'calendar_year' => 2016)
        );
        $datagenerator->create_enrolment(
            array(
                'userid' => $this->user,
                'moduleid' => $this->module3,
                'calendar_year' => 2016)
        );
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $datagenerator->create_paper(array('papertitle' => 'Test create formative',
            'calendaryear' => 2016,
            'modulename' => 'Online Help',
            'paperowner' => 'admin',
            'papertype' => '0',
            'duration' => 60,
            'labs' => '1'));
        $datagenerator = $this->get_datagenerator('api', 'core');
        $client = $datagenerator->create_client(
            array(
                'clientid' => 'test1',
                'userid' => $this->admin['id'],
                'secret' => 'test'
            )
        );
        $datagenerator->create_external(
            array(
                'clientid' => $client['clientid'],
                'name' => 'test ExamSys api',
                'type' => 'api'
            )
        );
        $datagenerator = $this->get_datagenerator('labs', 'core');
        $datagenerator->create_campus(array('name' => 'Test Campus', 'isdefault' => 1));
        $datagenerator->create_lab(
            array(
                'name' => 'Test lab',
                'building' => 'Test building',
                'room' => 1
            )
        );
    }

    /**
     * Create a response array for creation
     * @return array the response array
     */
    private function createResponseArray()
    {
        return array(
            'statuscode' => 100,
            'status' => 'OK',
            'id' => $this->module3 + 1,
            'externalid' => null,
            'error' => null,
            'node' => 'create',
            'nodeid' => 1);
    }

    /**
     * Create a parameter array for creation
     * @return array the param array
     */
    private function createParamArray()
    {
        return array(
            'nodeid' => 1,
            'modulecode' => 'TEST4',
            'name' => 'Test module 4',
            'school' => 'Test school',
            'faculty' => 'Test faculty');
    }

    /**
     * Create a parameter array for updates
     * @return array the param array
     */
    private function updateParamArray()
    {
        return array(
            'nodeid' => 1,
            'id' => $this->module2,
            'name' => 'Test module 2 update');
    }

    /**
     * Create a response array for updates
     * @return array the response array
     */
    private function updateResponseArray()
    {
        return array(
            'statuscode' => 100,
            'status' => 'OK',
            'id' => $this->module2,
            'externalid' => null,
            'error' => null,
            'node' => 'update',
            'nodeid' => 1);
    }

    /**
     * Create a parameter array for enrolment
     * @return array the param array
     */
    private function enrolParamArray()
    {
        return array(
            'nodeid' => 1,
            'userid' => $this->student['id'],
            'moduleid' => $this->module,
            'session' => 2016,
            'attempt' => 1);
    }

    /**
     * Create a response array for enrolment
     * @return array the response array
     */
    private function enrolResponseArray()
    {
        return array(
            'statuscode' => 100,
            'status' => 'OK',
            'externalid' => null,
            'error' => null,
            'node' => 'enrol',
            'nodeid' => 1);
    }

    /**
     * Create a parameter array for unenrolment
     * @return array the param array
     */
    private function unenrolParamArray()
    {
        return array(
            'nodeid' => 1,
            'userid' => $this->student['id'],
            'moduleid' => $this->module3,
            'session' => 2016);
    }

    /**
     * Create a response array for unenrolment
     * @return array the response array
     */
    private function unenrolResponseArray()
    {
        return array(
            'statuscode' => 100,
            'status' => 'OK',
            'externalid' => null,
            'error' => null,
            'node' => 'unenrol',
            'nodeid' => 1);
    }

    /**
     * Create a response array for deletion
     * @return array the response array
     */
    private function deleteResponseArray()
    {
        return array(
            'statuscode' => 100,
            'status' => 'OK',
            'id' => $this->module,
            'externalid' => null,
            'error' => null,
            'node' => 'delete',
            'nodeid' => 1);
    }

    /**
     * Create a parameter array for deletion
     * @return array the param array
     */
    private function deleteParamArray()
    {
        return array(
            'nodeid' => 1,
            'id' => $this->module);
    }

    /**
     * Test successful module creation
     */
    public function testCreateSuccess()
    {
        // Test module create - SUCCESS.
        $responsearray = $this->createResponseArray();
        $params = $this->createParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $create = $module->create($params, $this->admin['id']);
        $responsearray['id'] = $create['id'];
        $this->assertEquals($responsearray, $create);
        // Create moudle in new school
        $params = array(
            'nodeid' => 1,
            'modulecode' => 'TEST5',
            'name' => 'Test module 5',
            'school' => 'Test school 2',
            'faculty' => 'Test faculty');
        $create = $module->create($params, $this->admin['id']);
        $responsearray['id'] = $create['id'];
        $this->assertEquals($responsearray, $create);
        // Check default settings
        $querytable = $this->query(
            array(
                'columns' => array(
                    'checklist',
                    'selfenroll',
                    'neg_marking',
                    'ebel_grid_template',
                    'timed_exams',
                    'exam_q_feedback',
                    'add_team_members',
                    'map_level',
                    'academic_year_start',
                    'syncpreviousyear',
                ),
                'table' => 'modules',
                'where' => array(
                    array(
                        'column' => 'id',
                        'value' => $responsearray['id']
                    )
                ),
            )
        );
        $expectedtable = array(
            0 => array(
                'checklist' => 'peer,external',
                'selfenroll' => 0,
                'neg_marking' => 1,
                'ebel_grid_template' => 0,
                'timed_exams' => 0,
                'exam_q_feedback' => 1,
                'add_team_members' => 1,
                'map_level' => 0,
                'academic_year_start' => '07/01',
                'syncpreviousyear' => 0,
            ),
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test module creation exception module exists
     */
    public function testCreateExceptionModule()
    {
        // Test module create - ERROR module already exists.
        $responsearray = $this->createResponseArray();
        $params = $this->createParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 505;
        $responsearray['status'] = 'Module already exists';
        $responsearray['id'] = $this->module;
        $responsearray['externalid'] = 'abc123def';
        $params['modulecode'] = 'TRAIN';
        $this->assertEquals($responsearray, $module->create($params, $this->admin['id']));
    }

    /**
     * Test module creation exception module exists (duplicate externalid - empty string)
     */
    public function testCreateExceptionModule2()
    {
        $responsearray = $this->createResponseArray();
        $params = $this->createParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 505;
        $responsearray['status'] = 'Module already exists';
        $responsearray['id'] = $this->module;
        $responsearray['externalid'] = 'abc123def';
        $params['externalid'] = 'abc123def';
        $params['sms'] = 'test ExamSys api';
        $this->assertEquals($responsearray, $module->create($params, $this->admin['id']));
    }

    /**
     * Test module creation exception invalid faculty
     */
    public function testCreateExceptionFaculty()
    {
        // Test module create - ERROR invalid faculty
        $responsearray = $this->createResponseArray();
        $params = $this->createParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 506;
        $responsearray['status'] = 'Faculty not supplied';
        $responsearray['id'] = null;
        $params = array(
            'nodeid' => 1,
            'modulecode' => 'TEST5',
            'name' => 'Test module 5',
            'school' => 'Test school 2',
            'faculty' => '');
        $this->assertEquals($responsearray, $module->create($params, $this->admin['id']));
    }

    /**
     * Test successful module update
     */
    public function testUpdateSuccess()
    {
        // Test module update name.
        $responsearray = $this->updateResponseArray();
        $params = $this->updateParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $this->assertEquals($responsearray, $module->update($params, $this->admin['id']));
        // Test module update module code.
        $params = array(
            'nodeid' => 1,
            'id' => $this->module2,
            'modulecode' => 'TEST2UPDATE');
        $this->assertEquals($responsearray, $module->update($params, $this->admin['id']));
        // Check update occured.
        $querytable = $this->query(
            array(
                'columns' => array(
                    'moduleid',
                    'fullname',
                    'active',
                    'schoolid',
                    'academic_year_start',
                    'sms'
                ),
                'table' => 'modules',
                'where' => array(
                    array(
                        'column' => 'id',
                        'value' => 2
                    )
                )
            )
        );
        $expectedtable = array(
            0 => array(
                'moduleid' => 'TEST2UPDATE',
                'fullname' => 'Test module 2 update',
                'active' => 1,
                'schoolid' =>  $this->school2,
                'academic_year_start' => '07/01',
                'sms' => ''
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test module update, also supplying school and faculty that have not changed
     */
    public function testUpdateSuccess2()
    {
        $responsearray = $this->updateResponseArray();
        $params = $this->updateParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $params = array(
            'nodeid' => 1,
            'id' =>  $this->module2,
            'modulecode' => 'TEST2UPDATE',
            'school' => 'Test school',
            'faculty' => 'Test faculty');
        $this->assertEquals($responsearray, $module->update($params, $this->admin['id']));
    }

    /**
     * Test successful module update with a new external id
     */
    public function testUpdateNewExternalId()
    {
        // Test module update name.
        $responsearray = $this->updateResponseArray();
        $params = $this->updateParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $this->assertEquals($responsearray, $module->update($params, $this->admin['id']));
        // Test module update module code.
        $params = array(
            'nodeid' => 1,
            'externalid' => 'abc123def',
            'newexternalid' => '87654321');
        $responsearray['id'] = $this->module;
        $responsearray['externalid'] = '87654321';
        $this->assertEquals($responsearray, $module->update($params, $this->admin['id']));
        // Check update occured.
        $querytable = $this->query(
            array(
                'columns' => array(
                    'moduleid',
                    'fullname',
                    'active',
                    'schoolid',
                    'academic_year_start',
                    'externalid',
                    'sms'
                ),
                'table' => 'modules',
                'where' => array(
                    array(
                        'column' => 'externalid',
                        'value' => '87654321'
                    )
                )
            )
        );
        $expectedtable = array(
            0 => array(
                'moduleid' => 'TRAIN',
                'fullname' => 'Training Module',
                'active' => 1,
                'schoolid' =>  $this->school2,
                'externalid' => '87654321',
                'sms' => 'test ExamSys api',
                'academic_year_start' => '07/01'
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test module update exception nothing to update
     */
    public function testUpdateExceptionNoUpdate()
    {
        $responsearray = $this->updateResponseArray();
        $params = array(
            'nodeid' => 1,
            'id' => $this->module2,
            'modulecode' => 'SYSTEM',
            'name' => 'Online Help',
            'school' => 'Training',
            'faculty' => 'Administrative and Support Units',
            'sms' => '');
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 512;
        $responsearray['status'] = 'Request updates nothing';
        $responsearray['id'] = null;
        $this->assertEquals($responsearray, $module->update($params, $this->admin['id']));
    }

    /**
     * Test module update exception module does not exist
     */
    public function testUpdateExceptionModule()
    {
        // Test module create - ERROR module does not exist.
        $responsearray = $this->updateResponseArray();
        $params = $this->updateParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 501;
        $responsearray['status'] = 'Module does not exist';
        $responsearray['id'] = null;
        $params['id'] = 0;
        $this->assertEquals($responsearray, $module->update($params, $this->admin['id']));
    }

    /**
     * Test module update exception module already exists
     */
    public function testUpdateExceptionModuleExists()
    {
        // Test module create - ERROR module does not exist.
        $responsearray = $this->updateResponseArray();
        $params = $this->updateParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 505;
        $responsearray['status'] = 'Module already exists';
        $responsearray['id'] = $this->module3;
        $params['modulecode'] = 'TEST3';
        $this->assertEquals($responsearray, $module->update($params, $this->admin['id']));
    }

    /**
     * Test module update exception faculty not supplied on school update
     */
    public function testUpdateExceptionFaculty()
    {
        // Test module create - ERROR faculty not supplied.
        $responsearray = $this->updateResponseArray();
        $params = $this->updateParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 506;
        $responsearray['status'] = 'Faculty not supplied';
        $responsearray['id'] = null;
        $params['school'] = 'Test school 2';
        $this->assertEquals($responsearray, $module->update($params, $this->admin['id']));
    }

    /**
     * Test module update exception school not supplied on faculty update
     */
    public function testUpdateExceptionSchool()
    {
        // Test module create - ERROR faculty not supplied.
        $responsearray = $this->updateResponseArray();
        $params = $this->updateParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 511;
        $responsearray['status'] = 'School not supplied';
        $responsearray['id'] = null;
        $params['faculty'] = 'Test faculty 2';
        $this->assertEquals($responsearray, $module->update($params, $this->admin['id']));
    }

    /**
     * Test successful module enrolment
     */
    public function testEnrolSuccess()
    {
        // Test module enrolment - SUCCESS.
        $responsearray = $this->enrolResponseArray();
        $params = $this->enrolParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $enrol = $module->enrol($params, $this->admin['id']);
        $responsearray['id'] = $enrol['id'];
        $this->assertEquals($responsearray, $enrol);
        // Already enrolled, so just return id of existing enrolment.
        $responsearray['statuscode'] = 514;
        $enrol = $module->enrol($params, $this->admin['id']);
        $responsearray['id'] = $enrol['id'];
        $this->assertEquals($responsearray, $enrol);
        // No session supplied - user current.
        $responsearray['statuscode'] = 100;
        $params = array(
            'nodeid' => 1,
            'userid' => $this->student['id'],
            'moduleid' => $this->module2,
            'attempt' => 1);
        $enrol = $module->enrol($params, $this->admin['id']);
        $responsearray['id'] = $enrol['id'];
        $this->assertEquals($responsearray, $enrol);
    }

    /**
     * Test successful module enrolment using user external id
     */
    public function testEnrolSuccessExternal()
    {
        // Test module enrolment - SUCCESS.
        $responsearray = $this->enrolResponseArray();
        $params = array(
            'nodeid' => 1,
            'studentid' => '00000001',
            'moduleid' => $this->module,
            'session' => 2016,
            'attempt' => 1);
        $module = new \api\modulemanagement($this->db, 'test1');
        $enrol = $module->enrol($params, $this->admin['id']);
        $responsearray['id'] = $enrol['id'];
        $this->assertEquals($responsearray, $enrol);
    }

    /**
     * Test module enrolment exception invalid module
     */
    public function testEnrolExceptionModule()
    {
        // Test module enrolment - ERROR module does not exist.
        $responsearray = $this->enrolResponseArray();
        $params = $this->enrolParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 508;
        $responsearray['status'] = 'User not enrolled';
        $responsearray['id'] = null;
        $params['moduleid'] = 0;
        $this->assertEquals($responsearray, $module->enrol($params, $this->admin['id']));
    }

    /**
     * Test module enrolment exception invalid user
     */
    public function testEnrolExceptionUser()
    {
        // Test module enrolment - ERROR invalid user.
        $responsearray = $this->enrolResponseArray();
        $params = $this->enrolParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 507;
        $responsearray['status'] = 'User does not exist';
        $responsearray['id'] = null;
        $params['userid'] = 0;
        $this->assertEquals($responsearray, $module->enrol($params, $this->admin['id']));
    }

    /**
     * Test successful module un-enrolment
     */
    public function testUnEnrolSuccess()
    {
        // Test module enrolment - SUCCESS.
        $responsearray = $this->unenrolResponseArray();
        $params = $this->unenrolParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $enrol = $module->unenrol($params, $this->admin['id']);
        $responsearray['id'] = $enrol['id'];
        $this->assertEquals($responsearray, $enrol);
    }

    /**
     * Test successful module un-enrolment using externalid
     */
    public function testUnEnrolSuccessExternal()
    {
        // Test module enrolment - SUCCESS.
        $responsearray = $this->unenrolResponseArray();
        $params = array('nodeid' => 1,
            'studentid' => '00000001',
            'moduleid' => $this->module3,
            'session' => 2016);
        $module = new \api\modulemanagement($this->db, 'test1');
        $enrol = $module->unenrol($params, $this->admin['id']);
        $responsearray['id'] = $enrol['id'];
        $this->assertEquals($responsearray, $enrol);
    }

    /**
     * Test module un-enrolment exception incorrect session supplied
     */
    public function testUnEnrolExceptionSession()
    {
        // Test module unenrolment - wrong session
        $responsearray = $this->unenrolResponseArray();
        $params = $this->unenrolParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 509;
        $responsearray['status'] = 'User not un-enrolled';
        $responsearray['id'] = null;
        $params['session'] = 2015;
        $this->assertEquals($responsearray, $module->unenrol($params, $this->admin['id']));
        // No session supplied.
        $enrolparams = $this->enrolParamArray();
        $module->enrol($enrolparams, $this->admin['id']);
        $responsearray['statuscode'] = 510;
        $responsearray['status'] = 'Session not supplied';
        $responsearray['id'] = null;
        $params = array(
            'nodeid' => 1,
            'userid' => $this->student['id'],
            'moduleid' => $this->module);
        $this->assertEquals($responsearray, $module->unenrol($params, $this->admin['id']));
    }

    /**
     * Test module un-enrolment exception incorrect module
     */
    public function testUnEnrolModule()
    {
        // Test module enrolment - ERROR no enrolment to unenrol.
        $responsearray = $this->unenrolResponseArray();
        $params = $this->unenrolParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 509;
        $responsearray['status'] = 'User not un-enrolled';
        $responsearray['id'] = null;
        $params['moduleid'] = $this->module2;
        $this->assertEquals($responsearray, $module->unenrol($params, $this->admin['id']));
    }

    /**
     * Test module un-enrolment exception invalid user
     */
    public function testUnEnrolUser()
    {
        // Test module enrolment - ERROR invalid user.
        $responsearray = $this->unenrolResponseArray();
        $params = $this->unenrolParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 507;
        $responsearray['status'] = 'User does not exist';
        $responsearray['id'] = null;
        $params['userid'] = 0;
        $this->assertEquals($responsearray, $module->unenrol($params, $this->admin['id']));
    }

    /**
     * Test successful module deletion
     */
    public function testDeleteSuccess()
    {
        // Test module deletion - SUCCESS.
        $responsearray = $this->deleteResponseArray();
        $responsearray['externalid'] = 'abc123def';
        $params = $this->deleteParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $this->assertEquals($responsearray, $module->delete($params, $this->admin['id']));
        // Check that the remaining modules are correct
        // when we delete a module we actually just add a timestamp to the table
        // which makes creating a fixture to check against difficult so doing this instead
        $querytable = $this->query(
            array(
                'columns' => array(
                    'moduleid',
                    'fullname',
                    'active',
                    'schoolid',
                    'academic_year_start'
                ),
                'table' => 'modules',
                'where' => array(
                    array(
                        'column' => 'mod_deleted',
                        'value' => null,
                        'operator' => 'IS')
                )
            )
        );
        $expectedtable = array(
            0 => array(
                'moduleid' => 'SYSTEM',
                'fullname' => 'Online Help',
                'active' => 1,
                'schoolid' => $this->school2,
                'academic_year_start' => '07/01',
            ),
            1 => array(
                'moduleid' => 'TEST3',
                'fullname' => 'Test module 3',
                'active' => 1,
                'schoolid' => $this->school,
                'academic_year_start' => '07/01',
            ),
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test module deletion exception invalid module
     */
    public function testDeleteExceptionModule()
    {
        // Test deleting a non existance module.
        $responsearray = $this->deleteResponseArray();
        $params = $this->deleteParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 501;
        $responsearray['status'] = 'Module does not exist';
        $responsearray['id'] = null;
        $params['id'] = 0;
        $this->assertEquals($responsearray, $module->delete($params, $this->admin['id']));
        // Test no module supplied.
        $params = array(
            'nodeid' => 1);
        $this->assertEquals($responsearray, $module->delete($params, $this->admin['id']));
    }

    /**
     * Test module deletion exception module in use
     */
    public function testDeleteExceptionInuse()
    {
        // Test deleting a module in use - first check module has a paper.
        $responsearray = $this->deleteResponseArray();
        $params = $this->deleteParamArray();
        $module = new \api\modulemanagement($this->db, 'test1');
        $responsearray['statuscode'] = 502;
        $responsearray['status'] = 'Module not deleted, as linked to a paper or enrolement';
        $responsearray['id'] = null;
        $params['id'] = $this->module2;
        $this->assertEquals($responsearray, $module->delete($params, $this->admin['id']));
        // Test deleting a module in use - second check module has a user.
        $responsearray['statuscode'] = 502;
        $responsearray['status'] = 'Module not deleted, as linked to a paper or enrolement';
        $params['id'] = $this->module3;
        $this->assertEquals($responsearray, $module->delete($params, $this->admin['id']));
    }
}
