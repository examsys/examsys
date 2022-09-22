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
 * Test apixml api class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class apixmltest extends unittestdatabase
{

    /**
     * @var array Storage for paper data in tests
     */
    private $pid1;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2016, 'academic_year' => '2016/17'));
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $this->pid1 = $datagenerator->create_paper(array('papertitle' => 'Test create formative',
            'calendaryear' => 2016,
            'modulename' => 'Training Module',
            'paperowner' => 'admin',
            'papertype' => '0',
            'duration' => 60,
            'labs' => '1'));
    }

    /**
     * Faculty xml
     */
    private $facultyxml = '<?xml version="1.0" encoding="utf-8"?>
        <facultyManagementRequest>
            <create id="str1234">
            <name>abc</name>
        </create>
    </facultyManagementRequest>';
    /**
     * Assessment xml
     */
    private $assessmentxml = '<?xml version="1.0" encoding="utf-8"?>
        <assessmentManagementRequest xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://localhost/rogo/api/schema/assessmentmanagement/managementrequest.xsd">
            <create id="str1234">
                <title>Test</title>
                <type>formative</type>
                <owner>%s</owner>
                <session>2016</session>
                <startdatetime>2016-05-30T09:00:00</startdatetime>
                <enddatetime>2016-05-30T10:00:00</enddatetime> 
                <modules>
                    <moduleid id="dfdsf">1</moduleid>
                </modules>
            </create>
        </assessmentManagementRequest>';

    /**
     * Test validate faculty - success
     * @group apixml
     */
    public function test_validate_faculty_success()
    {
        $api = new \api\apixml($this->facultyxml);
        $this->assertEquals(array(), $api->validate('facultymanagement', 'managementrequest'));
    }

    /**
     * Test parse faculty - success
     * @group apixml
     */
    public function test_parse_faculty_success()
    {
        $api = new \api\apixml($this->facultyxml);
        $api->validate('facultymanagement', 'managementrequest');
        $requestobject = new \api\facultymanagement($this->db);
        $fields = array('id', 'name', 'code', 'externalid');
        $actions = array('create');
        $perm['create'] = true;
        $create = $api->parse($requestobject, $fields, $actions, $perm, $this->admin['id']);
        $responsearray = array(
            'statuscode' => 100,
            'status' => 'OK',
            'id' => $create[0]['id'],
            'externalid' => null,
            'error' => null,
            'node' => 'create',
            'nodeid' => 'str1234');
        $this->assertEquals(array($responsearray), $create);
        $querytable = $this->query(array('columns' => array('name'), 'table' => 'faculty'));
        $expectedtable = array(
            0 => array(
                'name' => 'UNKNOWN Faculty'
            ),
            1 => array(
                'name' => 'Administrative and Support Units'
            ),
            2 => array(
                'name' => 'abc'
            ),
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test validate assessment - success
     * @group apixml
     */
    public function test_validate_assessment_success()
    {
        $api = new \api\apixml(sprintf($this->assessmentxml, $this->admin['id']));
        $this->assertEquals(array(), $api->validate('assessmentmanagement', 'managementrequest'));
    }

    /**
     * Test parse assessment - success
     * @group apixml
     */
    public function test_parse_assessment_success()
    {
        $api = new \api\apixml(sprintf($this->assessmentxml, $this->admin['id']));
        $api->validate('assessmentmanagement', 'managementrequest');
        $requestobject = new \api\assessmentmanagement($this->db);
        $fields = array('id', 'owner', 'type', 'title', 'startdatetime', 'enddatetime', 'modules', 'session', 'labs', 'month',
            'cohort_size', 'sittings', 'barriers', 'campus', 'notes', 'timezone', 'duration');
        $actions = array('create');
        $perm['create'] = true;
        $create = $api->parse($requestobject, $fields, $actions, $perm, $this->admin['id']);
        $responsearray = array(
            'statuscode' => 100,
            'status' => 'OK',
            'id' => $create[0]['id'],
            'externalid' => null,
            'error' => array(),
            'node' => 'create',
            'nodeid' => 'str1234');
        $this->assertEquals(array($responsearray), $create);
        $querytable = $this->query(array('columns' => array('paper_title', 'exam_duration',
            'calendar_year', 'timezone', 'paper_ownerID', 'labs', 'paper_type'), 'table' => 'properties'));
        $expectedtable = array(
            0 => array(
                'paper_title' => 'Test create formative',
                'exam_duration' => 60,
                'calendar_year' => 2016,
                'timezone' => 'Europe/London',
                'paper_ownerID' => $this->admin['id'],
                'labs' => '1',
                'paper_type' => '0',
            ),
            1 => array(
                'paper_title' => 'Test',
                'exam_duration' => null,
                'calendar_year' => 2016,
                'timezone' => 'Europe/London',
                'paper_ownerID' => $this->admin['id'],
                'labs' => '',
                'paper_type' => '0',
            ),
        );
        $this->assertEquals($expectedtable, $querytable);
        $querytable = $this->query(array('columns' => array('property_id', 'idMod'), 'table' => 'properties_modules'));
        $expectedtable = array(
            0 => array(
               'property_id' => $this->pid1['id'],
               'idMod' => $this->module
            ),
            1 => array(
                'property_id' => $create[0]['id'],
                'idMod' =>  $this->module
            ),
        );
        $this->assertEquals($expectedtable, $querytable);
    }
}
