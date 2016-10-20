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
 * Test apixml api class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class apixmltest extends unittestdatabase {
    /**
     * Faculty xml
     */
    private $faculty = '<?xml version="1.0" encoding="utf-8"?>
        <facultyManagementRequest>
            <create id="str1234">
            <name>abc</name>
        </create>
    </facultyManagementRequest>';
    /**
     * Assessment xml
     */
     private $assessment = '<?xml version="1.0" encoding="utf-8"?>
        <assessmentManagementRequest xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://localhost/rogo/api/schema/assessmentmanagement/managementrequest.xsd">
            <create id="str1234">
                <title>Test</title>
                <type>formative</type>
                <owner>1</owner>
                <session>2016</session>
                <startdatetime>2016-05-30T09:00:00</startdatetime>
                <enddatetime>2016-05-30T10:00:00</enddatetime> 
                <modules>
                    <moduleid id="dfdsf">1</moduleid>
                </modules>
            </create>
        </assessmentManagementRequest>';
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR . "apixmlTest" . DIRECTORY_SEPARATOR . "apixml.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR .  "apixmlTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test validate faculty - success
     * @group apixml
     */
    public function test_validate_faculty_success() {
        $api = new \api\apixml($this->faculty);
        $this->assertEquals(array(), $api->validate('facultymanagement', 'managementrequest'));
    }
    /**
     * Test parse faculty - success
     * @group apixml
     */
    public function test_parse_faculty_success() {
        $api = new \api\apixml($this->faculty);
        $api->validate('facultymanagement', 'managementrequest');
        $requestobject = new \api\facultymanagement($this->db);
        $fields = array('id', 'name', 'code', 'externalid');
        $actions = array('create');
        $perm['create'] = true;
        $userid = 1;
        $responsearray = array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 3,
            "externalid" => null,
            "error" => null,
            "node" => 'create',
            "nodeid" => 'str1234');
        $this->assertEquals(array($responsearray), $api->parse($requestobject, $fields, $actions, $perm, $userid));
        $querytable = $this->getConnection()->createQueryTable('faculty', 'SELECT id, name FROM faculty');
        $expectedtable = $this->get_expected_data_set('create')->getTable("faculty");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test validate assessment - success
     * @group apixml
     */
    public function test_validate_assessment_success() {
        $api = new \api\apixml($this->assessment);
        $this->assertEquals(array(), $api->validate('assessmentmanagement', 'managementrequest'));
    }
    /**
     * Test parse assessment - success
     * @group apixml
     */
    public function test_parse_assessment_success() {
        $api = new \api\apixml($this->assessment);
        $api->validate('assessmentmanagement', 'managementrequest');
        $requestobject = new \api\assessmentmanagement($this->db);
        $fields = array('id', 'owner', 'type', 'title', 'startdatetime', 'enddatetime', 'modules', 'session', 'labs', 'month',
            'cohort_size', 'sittings', 'barriers', 'campus', 'notes', 'timezone', 'duration');
        $actions = array('create');
        $perm['create'] = true;
        $userid = 1;
        $responsearray = array(
            "statuscode" => 100,
            "status" => 'OK',
            "id" => 2,
            "externalid" => null,
            "error" => array(),
            "node" => 'create',
            "nodeid" => 'str1234');
        $this->assertEquals(array($responsearray), $api->parse($requestobject, $fields, $actions, $perm, $userid));
        $querytable = $this->getConnection()->createQueryTable('properties', 'SELECT property_id, paper_title, start_date, end_date, exam_duration,
            calendar_year, timezone, paper_ownerID, labs, paper_type FROM properties');
        $expectedtable = $this->get_expected_data_set('create')->getTable("properties");  
        $this->assertTablesEqual($expectedtable, $querytable);
        $querytable = $this->getConnection()->createQueryTable('properties_modules', 'SELECT property_id, idMod FROM properties_modules');
        $expectedtable = $this->get_expected_data_set('create')->getTable("properties_modules");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
}
