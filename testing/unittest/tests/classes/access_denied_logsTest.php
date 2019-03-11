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
 * Test access denied logs class
 *
 * @author Naseem Sarwar <naseem.sarwar@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class access_denied_logsTest extends unittestdatabase {
  /**
   * Get init data set from yml
   * @return dataset
   */
  public function getDataSet() {
    return new YamlDataSet($this->get_base_fixture_directory() . "classes" . DIRECTORY_SEPARATOR . "access_denied_logs.yml");
  }
  /**
   * Test get all the logs from access denied record
   * @group log
   */
  public function test_get_access_denied_logs() {
    $log_obj = new access_denied_logs($this->db);
    $this->assertEquals(2,count($log_obj->get_access_denied_logs()));
  }

  /**
   * Test deleting a access denied log record
   * @group log
   */
  public function test_delete_a_access_denied_log() {
    $log_obj = new access_denied_logs($this->db);
    $this->assertTrue($log_obj->delete_a_access_denied_log(1));
  }

  /**
   * Test deleting all the access denied logs records
   * @group log
   */
  public function test_delete_access_denied_logs() {
    $log_obj = new access_denied_logs($this->db);
    $this->assertTrue($log_obj->delete_access_denied_logs());
  }


}
