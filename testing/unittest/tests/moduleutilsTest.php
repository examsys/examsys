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
 * Test moduleytils class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class moduleutilstest extends unittestdatabase {
    
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "moduleutilsTest" . DIRECTORY_SEPARATOR . "moduleutils.yml");
    }
    
    /**
     * Test get modules paper assoicated with
     * @group gradebook
     */
    public function test_get_modules_for_paper() {
        $modules = array(array("moduleid" => "ABC100", "fullname" => "Test Module", "externalid" => "123456789"), array("moduleid" => "ABC200", "fullname" => "Test Module 2", "externalid" => "987654321"));
        $this->assertEquals($modules, module_utils::get_modules_for_paper(2, 1, $this->db));
        $modules = array(array("moduleid" => "ABC100", "fullname" => "Test Module", "externalid" => "123456789"));
        $this->assertEquals($modules, module_utils::get_modules_for_paper(1, 1, $this->db));
        $modules = array();
        $this->assertEquals($modules, module_utils::get_modules_for_paper(1, 2, $this->db));
    }
    /**
     * Test get full details using internal id
     * @group modules
     */
    public function test_get_full_details_internalid() {
        $detailsarray = array( 'idMod' => 2,
                  'moduleid' => 'ABC200',
                  'fullname' => 'Test Module 2',
                  'school' => 'test school',
                  'active' => null,
                  'vle_api' => null,
                  'checklist' => null,
                  'sms' => 'external',
                  'selfenroll' => null,
                  'schoolid' => 1,
                  'neg_marking' => null,
                  'ebel_grid_template' => null,
                  'timed_exams' => null,
                  'exam_q_feedback' => null,
                  'add_team_members' => null,
                  'map_level' => 0,
                  'academic_year_start' => '01/07',
                  'externalid' => '987654321');
        $details = module_utils::get_full_details('internal', 2, $this->db);
        $this->assertEquals($detailsarray, $details);
    }
    /**
     * Test get full details using wrapper function get_full_details_by_ID
     * @group modules
     */
    public function test_get_full_details_by_ID() {
        $detailsarray = array( 'idMod' => 2,
                  'moduleid' => 'ABC200',
                  'fullname' => 'Test Module 2',
                  'school' => 'test school',
                  'active' => null,
                  'vle_api' => null,
                  'checklist' => null,
                  'sms' => 'external',
                  'selfenroll' => null,
                  'schoolid' => 1,
                  'neg_marking' => null,
                  'ebel_grid_template' => null,
                  'timed_exams' => null,
                  'exam_q_feedback' => null,
                  'add_team_members' => null,
                  'map_level' => 0,
                  'academic_year_start' => '01/07',
                  'externalid' => '987654321');
        $details = module_utils::get_full_details_by_ID(2, $this->db);
        $this->assertEquals($detailsarray, $details);
    }
    /**
     * Test get full details using external id
     * @group modules
     */
    public function test_get_full_details_externalid() {
        $detailsarray = array( 'idMod' => 2,
                  'moduleid' => 'ABC200',
                  'fullname' => 'Test Module 2',
                  'school' => 'test school',
                  'active' => null,
                  'vle_api' => null,
                  'checklist' => null,
                  'sms' => 'external',
                  'selfenroll' => null,
                  'schoolid' => 1,
                  'neg_marking' => null,
                  'ebel_grid_template' => null,
                  'timed_exams' => null,
                  'exam_q_feedback' => null,
                  'add_team_members' => null,
                  'map_level' => 0,
                  'academic_year_start' => '01/07',
                  'externalid' => '987654321');
        $details = module_utils::get_full_details('external', '987654321', $this->db, 'external');
        $this->assertEquals($detailsarray, $details);
    }
    /**
     * Test get full details using invalid id type
     * @group modules
     */
    public function test_get_full_details_invalid() {
        $details = module_utils::get_full_details('placeholder', 2, $this->db);
        $this->assertFalse($details);
    }
}
