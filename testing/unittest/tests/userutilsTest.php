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
 * Test userutils class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class userutilstest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new YamlDataSet($this->get_base_fixture_directory() . "userutilsTest" . DIRECTORY_SEPARATOR . "userutils.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
      return new YamlDataSet($this->get_base_fixture_directory() . "userutilsTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test gettign enrolment id
     * @group user
     */
    public function test_get_enrolement_id() {
        // Enrolment exists.
        $this->assertEquals(1, UserUtils::get_enrolement_id(1000, 3, 2016, $this->db));
        // Enrolment does not exist.
        $this->assertEquals(false, UserUtils::get_enrolement_id(1, 3, 2016, $this->db));
    }
    /**
     * Test updating user
     * @group user
     */
    public function test_update_user() {
      // Student ID exists.
      $this->assertEquals(true, UserUtils::update_user(1, 'student1', '12345678', 'Mr', 'Joe', 'Baxter', 'joseph.baxter@example.com', 'TEST', 'Male', 1, 'Student', '12345678', $this->db));
      // Student ID does not exist.
      $this->assertEquals(true, UserUtils::update_user(2, 'student2', '87654321', 'Dr', 'Joseph', 'Baxter', 'joe.baxter@example.com', 'TEST', 'Male', 1, 'Student', '87654321', $this->db, "J"));
      // Check tables update as expected.
      $querytable = $this->getConnection()->createQueryTable('users', 'SELECT id, username, roles, grade, title, initials, surname, first_names, email, gender FROM users');
      $expectedtable = $this->get_expected_data_set('updated')->getTable("users");
      $this->assertTablesEqual($expectedtable, $querytable);
      $querytable = $this->getConnection()->createQueryTable('sid', 'SELECT userID, student_id FROM sid');
      $expectedtable = $this->get_expected_data_set('updated')->getTable("sid");
      $this->assertTablesEqual($expectedtable, $querytable);
    }
}
