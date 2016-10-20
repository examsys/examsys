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
 * Test schoolutils class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class schoolutilstest extends unittestdatabase {
    
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "schoolutilsTest" . DIRECTORY_SEPARATOR . "schoolutils.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "schoolutilsTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    
    /**
     * Test updating a school
     * @group school
     */
    public function test_update_school() {
        // Check successful update.
        $this->assertTrue(SchoolUtils::update_school(1, 1, 'test update school', null, '123456', 'external', $this->db));
        // Check unsuccessful update - duplicate name.
        $this->assertFalse(SchoolUtils::update_school(2, 1, 'test update school', null, '123456', null, $this->db));
        // Check unsuccessful update - no school supplied.
        $this->assertFalse(SchoolUtils::update_school(2, 1, '', 'TST2', '678912', null, $this->db));
        // Check unsuccessful update - no faculty supplied.
        $this->assertFalse(SchoolUtils::update_school(2, '', 'test update school 2', 'TST2', '678912', null, $this->db));
        // Check schools table update as expected.
        $querytable = $this->getConnection()->createQueryTable('schools', 'SELECT id, school, facultyID, code, externalid, externalsys FROM schools');
        $expectedtable = $this->get_expected_data_set('updatedschools')->getTable("schools");
        $this->assertTablesEqual($expectedtable, $querytable); 
    }
    /**
     * Test checking is school in use
     * @group school
     */
    public function test_school_in_use() {
        // Check in use.
        $this->assertTrue(SchoolUtils::school_in_use(1, $this->db));
        // Check not in use.
        $this->assertFalse(SchoolUtils::school_in_use(2, $this->db));
    }    
    /**
     * Test getting school id from external id
     * @group school
     */
    public function test_get_schoolid_from_externalid() {
        $this->assertEquals(1, SchoolUtils::get_schoolid_from_externalid("ABC", $this->db));
    }
    /**
     * Test comparing  faculties with external list
     * @group achool
     */
    public function test_diff_external_schools_to_internal_schools() {
        $external = array("ABCD", "EFGH", "IJKL");
        $this->assertEquals(array("ABC"), SchoolUtils::diff_external_schools_to_internal_schools($external, 'external', $this->db));
    }
}
