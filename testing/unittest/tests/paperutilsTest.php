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
 * Test paperutils class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class paperutilstest extends unittestdatabase {
    
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "paperutilsTest" . DIRECTORY_SEPARATOR . "paperutils.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "paperutilsTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    
    /**
     * Test complete paper deletion
     * @group assessment
     */
    public function test_complete_delete_paper() {
        // Check successful deletion.
        $this->assertTrue(Paper_utils::complete_delete_paper(2, $this->db));
        $querypropertiestable = $this->getConnection()->createQueryTable('properties', 'SELECT property_id FROM properties');
        $querypropertiesmodulestable = $this->getConnection()->createQueryTable('properties_modules', 'SELECT * FROM properties_modules');
        $expectedpropertiestable = $this->get_expected_data_set('deleteproperties')->getTable("properties");
        $expectedpropertiesmodulestable = $this->get_expected_data_set('deleteproperties')->getTable("properties_modules");
        // Check properties table deletion.
        $this->assertTablesEqual($expectedpropertiestable, $querypropertiestable);
        // Check properties_modules table deletion.
        $this->assertTablesEqual($expectedpropertiesmodulestable, $querypropertiesmodulestable);  
    }
    
    /**
     * Test get papers by session
     * @group gradebook
     */
    public function test_get_papers_by_session() {
        $papers = array(2);
        $this->assertEquals($papers, Paper_utils::get_papers_by_session('2016', 2, $this->db));
        $papers = array();
        $this->assertEquals($papers, Paper_utils::get_papers_by_session('2016', 1, $this->db));
    }
    
    /**
     * Test get finalised papers
     * @group gradebook
     */
    public function test_get_finalised_papers() {
        $papers = array(2);
        $this->assertEquals($papers, Paper_utils::get_finalised_papers('2016', 2, $this->db));
        $papers = array();
        $this->assertEquals($papers, Paper_utils::get_finalised_papers('2016', 1, $this->db));
    }
}
