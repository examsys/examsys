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
 * Test csv class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class csv_handlerTest extends unittestdatabase {
  /**
   * Get init data set from yml
   * @return dataset
   */
  public function getDataSet() {
    return new YamlDataSet($this->get_base_fixture_directory() . "csv" . DIRECTORY_SEPARATOR . "csv.yml");
  }

  /**
   * Get test dir
   * @return csv_handler
   */
  public function get_test_dir() {
    return $this->get_base_fixture_directory() . "csv" . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR;
  }

  /**
   * Test invalid file type upload
   * @group csv
   */
  public function test_move_upload_to_temp_invalid() {
    $this->expectExceptionMessage('File has an invalid file extension. Only .csv is supported.');
    $file['name'] = 'modules.xslx';
    $file['tmp_name'] = 'modules.csv';
    $file['error'] = 0;
    \csv\csv_handler::move_upload_to_temp($file, $this->config->get('cfg_tmpdir'));
  }

  /**
   * Test invalid file type upload
   * @group csv
   */
  public function test_move_upload_to_temp_noname() {
    $this->expectExceptionMessage('No filename supplied.');
    $file['name'] = '';
    $file['tmp_name'] = '';
    $file['error'] = 0;
    \csv\csv_handler::move_upload_to_temp($file, $this->config->get('cfg_tmpdir'));
  }

  /**
   * Test get line
   * @group csv
   */
  public function test_get_line() {
    $csv = new \csv\csv_handler("test.csv", $this->get_test_dir());
    $line = array('a' => '1', 'b' => '2', 'c' => '3');
    $this->assertEquals($line, $csv->get_line());
  }

}