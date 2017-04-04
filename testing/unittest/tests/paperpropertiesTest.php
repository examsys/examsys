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
 * Test paperproperties class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class paperpropertiestest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "paperpropertiesTest" . DIRECTORY_SEPARATOR . "paperproperties.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "paperpropertiesTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    
    /**
     * Test setting paper password
     * @group paper
     */
    public function test_set_password() {
        // Load user id 1.
        $this->userobject->load(1);
        // Set new password.
        $newpassword = 'newpassword';
        $properties = PaperProperties::get_paper_properties_by_id(45, $this->db, '');
        $properties->set_password($newpassword);
        $properties->save();
        // Check password set.
        $querypropertiestable = $this->getConnection()->createQueryTable('properties', 'SELECT property_id, calendar_year, paper_type, password FROM properties');
        $expectedpropertiestable = $this->get_expected_data_set('paperproperties_updated')->getTable("properties");
        $this->assertTablesEqual($expectedpropertiestable, $querypropertiestable);
        // Check track changes recorded.
        $querychangestable = $this->getConnection()->createQueryTable('track_changes', 'SELECT id, type, typeID, editor, old, new, part FROM track_changes');
        $expectedchangestable = $this->get_expected_data_set('paperproperties_updated')->getTable("track_changes");
        $this->assertTablesEqual($expectedchangestable, $querychangestable);
    }
}