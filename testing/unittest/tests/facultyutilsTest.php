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
 * Test facultyutils class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class facultyutilstest extends unittestdatabase {
    
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "facultyutilsTest" . DIRECTORY_SEPARATOR . "facultyutils.yml");
    }
    /**
     * Test count schools in faculties
     * @group faculty
     */
    public function test_count_schools_in_faculty() {
        // Check count does not include deleted schools.
        $this->assertEquals(1, FacultyUtils::count_schools_in_faculty(1, $this->db));
    }
    /**
     * Test getting faculty name from external id
     * @group faculty
     */
    public function test_get_facultyid_from_externalid() {
        $this->assertEquals(1, FacultyUtils::get_facultyid_from_externalid("ABC", $this->db));
    }
    /**
     * Test comparing  faculties with external list
     * @group faculty
     */
    public function test_diff_external_faculties_to_internal_faculties() {
        $external = array("ABC", "JKL");
        $this->assertEquals(array("DEF"), FacultyUtils::diff_external_faculties_to_internal_faculties($external, 'external', $this->db));
    }
}
