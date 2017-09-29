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
 * Test external_systems class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class externalsystemstest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "external_systemsTest" . DIRECTORY_SEPARATOR . "external.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "external_systemsTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test external system info for user.
     * @group extsys
     */
    public function test_get_mapped_externalsystem_info() {
        $external = new external_systems();
        $info = $external->get_mapped_externalsystem_info('test2');
        $this->assertEquals('external api 2', $info['name']);
        $this->assertEquals(2, $info['id']);
    }
    /**
     * Test list all external systems.
     * @group extsys
     */
    public function test_get_all_externalsystems() {
        $external = new external_systems();
        $extsys = array(1 => 'external api', 2 => 'external api 2', 3 => 'Campus Solutions', 5 => 'external api 3');
        $this->assertEquals($extsys, $external->get_all_externalsystems());
    }
    /**
     * Test list all external systems details included.
     * @group extsys
     */
    public function test_get_all_externalsystems_details() {
        $external = new external_systems();
        $extsys = array(1 => array('name' => 'external api', 'type' => \external_systems::API),
            2 => array('name' => 'external api 2', 'type' => \external_systems::API),
            3 => array('name' => 'Campus Solutions', 'type' => \external_systems::PLUGIN),
            5 => array('name' => 'external api 3', 'type' => \external_systems::API));
        $this->assertEquals($extsys, $external->get_all_externalsystems_details());
    }
    /**
     * Test list all API external systems.
     * @group extsys
     */
    public function test_get_all_api_externalsystems() {
        $external = new external_systems();
        $extsys = array(1 => 'external api', 2 => 'external api 2', 5 => 'external api 3');
        $this->assertEquals($extsys, $external->get_all_api_externalsystems());
    }
    /**
     * Test inserting new external system mapping
     * @group extsys
     */
    public function test_insert_external_system_mapping() {
        $external = new external_systems();
        $external->insert_external_system_mapping('test3', 2);
        $queryTable = $this->getConnection()->createQueryTable('external_systems_mapping', 'SELECT * FROM external_systems_mapping');
        $expectedTable = $this->get_expected_data_set('external_inserted')->getTable("external_systems_mapping");  
        $this->assertTablesEqual($expectedTable, $queryTable); 
    }
    /**
     * Test updating new external system mapping
     * @group extsys
     */
    public function test_update_external_system_mapping() {
        $external = new external_systems();
        $external->update_external_system_mapping('test1', 2);
        $queryTable = $this->getConnection()->createQueryTable('external_systems_mapping', 'SELECT * FROM external_systems_mapping');
        $expectedTable = $this->get_expected_data_set('external_updated')->getTable("external_systems_mapping");  
        $this->assertTablesEqual($expectedTable, $queryTable); 
    }
    /**
     * Test deleting external system mapping
     * @group extsys
     */
    public function test_delete_external_system_mapping() {
        $external = new external_systems();
        $external->insert_external_system_mapping('test3', 2);
        $external->delete_external_system_mapping('test3');
        $queryTable = $this->getConnection()->createQueryTable('external_systems_mapping', 'SELECT * FROM external_systems_mapping');
        $expectedTable = $this->get_expected_data_set('external')->getTable("external_systems_mapping");  
        $this->assertTablesEqual($expectedTable, $queryTable); 
    }
    /**
     * Test inserting new external system
     * @group extsys
     */
    public function test_insert_external_system() {
        $external = new external_systems();
        $external->insert_external_system('test', \external_systems::PLUGIN);
        $queryTable = $this->getConnection()->createQueryTable('external_systems', 'SELECT * FROM external_systems');
        $expectedTable = $this->get_expected_data_set('external_inserted')->getTable("external_systems");  
        $this->assertTablesEqual($expectedTable, $queryTable); 
    }
    /**
     * Test inserting new external system invalid name
     * @group extsys
     */
    public function test_insert_external_system_invlaid() {
        $external = new external_systems();
        $this->assertFalse($external->insert_external_system('', \external_systems::PLUGIN));
        $this->assertFalse($external->insert_external_system('0', \external_systems::PLUGIN));
    }
    /**
     * Test deleting external system
     * @group extsys
     */
    public function test_delete_external_system() {
        $external = new external_systems();
        $external->insert_external_system('test', \external_systems::PLUGIN);
        $external->delete_external_system(6);
        $queryTable = $this->getConnection()->createQueryTable('external_systems', 'SELECT * FROM external_systems');
        $expectedTable = $this->get_expected_data_set('external')->getTable("external_systems");  
        $this->assertTablesEqual($expectedTable, $queryTable); 
    }
    /**
     * Test checking external system exists
     * @group extsys
     */
    public function test_external_system_exists() {
        $external = new external_systems();
        $this->assertTrue($external->external_system_exists(1));
        $this->assertTrue($external->external_system_exists(2));
        $this->assertTrue($external->external_system_exists(3));
        $this->assertFalse($external->external_system_exists(4));
    }
    /**
     * Test checking external system in use
     * @group extsys
     */
    public function test_external_system_inuse() {
        $external = new external_systems();
        $this->assertTrue($external->external_system_inuse(1));
        $this->assertTrue($external->external_system_inuse(2));
        $this->assertTrue($external->external_system_inuse(3));
        $this->assertFalse($external->external_system_inuse(4));
    }
    /**
     * Test checking external system not in use
     * @group extsys
     */
    public function test_external_system_not_inuse() {
        $external = new external_systems();
        $this->assertFalse($external->external_system_inuse(5));
    }
}