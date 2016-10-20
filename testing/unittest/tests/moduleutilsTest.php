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
}
