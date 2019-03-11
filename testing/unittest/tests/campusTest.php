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
use PHPUnit\DbUnit\DataSet\YamlDataSet;

/**
 * Test campus class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class campustest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new YamlDataSet($this->get_base_fixture_directory() . "campusTest" . DIRECTORY_SEPARATOR . "campus.yml");
    }
    /**
     * Test get all campus details function
     * @group campus
     */
    public function test_get_all_campus_details() {
        $campus = new campus($this->db);
        // Test details found success.
        $campusarray = array();
        $campusarray[10] = array('campusname' => 'Test Campus', 'isdefault' => 1);
        $campusarray[11] = array('campusname' => 'Test Campus 2', 'isdefault' => 0);
        $this->assertEquals($campusarray, $campus->get_all_campus_details());
        // Test details not found error.
        $this->delete_dataset($this->getDataSet());
        $this->assertFalse($campus->get_all_campus_details());
    }
    /**
     * Test get campus details function
     * @group campus
     */
    public function test_get_campus_details() {
        $campus = new campus($this->db);
        // Test details found success.
        $expected = array('campusid' => 10, 'campusname' => 'Test Campus', 'isdefault' => 1);
        $this->assertEquals($expected, $campus->get_campus_details(10));
        // Test details not found error.
        $expected = false;
        $this->assertEquals($expected, $campus->get_campus_details(12));
    }
    /**
     * Test check campus name in use function
     * @group campus
     */
    public function test_check_campus_name_inuse() {
        $campus = new campus($this->db);
        // Test campus name in use - true.
        $this->assertTrue($campus->check_campus_name_inuse('Test Campus'));
        // Test campus name in use - false.
        $this->assertFalse($campus->check_campus_name_inuse('Another campus'));
    }
    /**
     * Test check campus id in use function
     * @group campus
     */
    public function test_check_campus_in_use() {
        $campus = new campus($this->db);
        // Test campus in use - true.
        $this->assertTrue($campus->check_campus_in_use(10));
        // Test campus in use - false.
        $this->assertFalse($campus->check_campus_in_use(11));
    }
}
