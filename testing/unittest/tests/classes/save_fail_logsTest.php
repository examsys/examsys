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
 * Test save fail logs class
 *
 * @author Naseem Sarwar <naseem.sarwar@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class save_fail_logsTest extends unittestdatabase {
  /**
   * Get init data set from yml
   * @return dataset
   */
  public function getDataSet() {
    return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "classes" . DIRECTORY_SEPARATOR . "save_fail_logs.yml");
  }
  /**
   * Test get all the logs from fail logs record
   * @group log
   */
  public function test_get_save_fail_logs() {
    $log_obj = new save_fail_logs($this->db);
    $this->assertEquals(2,count($log_obj->get_save_fail_logs()));
  }

  /**
   * Test deleting a save fail log record
   * @group log
   */
  public function test_delete_a_save_fail_log() {
    $log_obj = new save_fail_logs($this->db);
    $log_obj->delete_a_save_fail_log('1');
    $this->assertTrue($log_obj->delete_a_save_fail_log(1));
  }

  /**
   * Test deleting all the save fail logs records
   * @group log
   */
  public function test_delete_save_fail_logs() {
    $log_obj = new save_fail_logs($this->db);
    $this->assertTrue($log_obj->delete_save_fail_logs());
  }


}
