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
 * Test abstract api functions
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class abstractmanagementtest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR . "abstractmanagementTest" . DIRECTORY_SEPARATOR . "abstractmanagement.yml");
    }
    /**
     * Test get external system api
     * @group api
     */
    public function test_get_external_system_api() {
        $faculty = new \api\facultymanagement($this->db, 'test1');
        $response = 'test rogo api';
        $this->assertEquals($response, $faculty->get_external_system('test rogo api'));
        $this->assertEquals($response, $faculty->get_external_system(null));
    }
    /**
     * Test get external system plugin
     * @group api
     */
    public function test_get_external_system_plugin() {
        $faculty = new \api\facultymanagement($this->db);
        $response = 'test rogo plugin';
        $this->assertEquals($response, $faculty->get_external_system('test rogo plugin'));
    }
    /**
     * Test get external system api super user
     * @group api
     */
    public function test_get_external_system_api_super() {
        $faculty = new \api\facultymanagement($this->db, 'test2');
        $response = 'test rogo api';
        $this->config->set_setting('api_allow_superuser', 1, 'boolean');
        $this->assertEquals($response, $faculty->get_external_system('test rogo api'));
        $this->config->set_setting('api_allow_superuser', 0, 'boolean');
        $response = null;
        $this->assertEquals($response, $faculty->get_external_system('test rogo api'));
    }
}