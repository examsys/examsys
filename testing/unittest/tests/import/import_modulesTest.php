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
 * Test import csv class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class import_modulesTest extends unittestdatabase {
  /**
   * Get init data set from yml
   * @return dataset
   */
  public function getDataSet() {
    return new YamlDataSet($this->get_base_fixture_directory() . "import" . DIRECTORY_SEPARATOR . "import.yml");
  }

  /**
   * Get expected data set from yml
   * @param string $name fixture file name
   * @return dataset
   */
  public function get_expected_data_set($name) {
    return new YamlDataSet($this->get_base_fixture_directory() . "import" . DIRECTORY_SEPARATOR . $name . ".yml");
  }

  /**
   * Get test file
   * @param $name name of file
   * @return csv_handler
   */
  public function get_test_csv($name) {
    return new \csv\csv_handler($name . ".csv", $this->get_base_fixture_directory() . "import" . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR );
  }

  /**
   * Test module import add
   * @group import
   */
  public function test_execute_add() {
    $import = new \import\import_modules($this->get_test_csv('modules'));
    $import->execute();
    // Test modules table is correct.
    $queryTable = $this->getConnection()->createQueryTable('modules', 'SELECT * FROM modules');
    $expectedTable = $this->get_expected_data_set('modules')->getTable("modules");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test module import update 'add team member'
   * @group import
   */
  public function test_execute_update_addteammember() {
    $import = new \import\import_modules($this->get_test_csv('modules_update_addteammember'));
    $import->execute();
    // Test modules table is correct.
    $queryTable = $this->getConnection()->createQueryTable('modules', 'SELECT * FROM modules');
    $expectedTable = $this->get_expected_data_set('modules_updated_addteammember')->getTable("modules");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test module import update 'checklist'
   * @group import
   */
  public function test_execute_update_checklist() {
    $import = new \import\import_modules($this->get_test_csv('modules_update_checklist'));
    $import->execute();
    // Test modules table is correct.
    $queryTable = $this->getConnection()->createQueryTable('modules', 'SELECT * FROM modules');
    $expectedTable = $this->get_expected_data_set('modules_updated_checklist')->getTable("modules");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test module import update 'active'
   * @group import
   */
  public function test_execute_update_active() {
    $import = new \import\import_modules($this->get_test_csv('modules_update_active'));
    $import->execute();
    // Test modules table is correct.
    $queryTable = $this->getConnection()->createQueryTable('modules', 'SELECT * FROM modules');
    $expectedTable = $this->get_expected_data_set('modules_updated_active')->getTable("modules");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test module import update 'external id'
   * @group import
   */
  public function test_execute_update_externalid() {
    $import = new \import\import_modules($this->get_test_csv('modules_update_externalid'));
    $import->execute();
    // Test modules table is correct.
    $queryTable = $this->getConnection()->createQueryTable('modules', 'SELECT * FROM modules');
    $expectedTable = $this->get_expected_data_set('modules_updated_externalid')->getTable("modules");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test module import update 'negative marking'
   * @group import
   */
  public function test_execute_update_negmarking() {
    $import = new \import\import_modules($this->get_test_csv('modules_update_negmarking'));
    $import->execute();
    // Test modules table is correct.
    $queryTable = $this->getConnection()->createQueryTable('modules', 'SELECT * FROM modules');
    $expectedTable = $this->get_expected_data_set('modules_updated_negmarking')->getTable("modules");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test module import update 'objective api'
   * @group import
   */
  public function test_execute_update_objectiveapi() {
    $import = new \import\import_modules($this->get_test_csv('modules_update_objectiveapi'));
    $import->execute();
    // Test modules table is correct.
    $queryTable = $this->getConnection()->createQueryTable('modules', 'SELECT * FROM modules');
    $expectedTable = $this->get_expected_data_set('modules_updated_objectiveapi')->getTable("modules");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test module import update 'question based feedback'
   * @group import
   */
  public function test_execute_update_questionbasedfb() {
    $import = new \import\import_modules($this->get_test_csv('modules_update_questionbasedfb'));
    $import->execute();
    // Test modules table is correct.
    $queryTable = $this->getConnection()->createQueryTable('modules', 'SELECT * FROM modules');
    $expectedTable = $this->get_expected_data_set('modules_updated_questionbasedfb')->getTable("modules");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test module import update 'self enrol'
   * @group import
   */
  public function test_execute_update_selfenrol() {
    $import = new \import\import_modules($this->get_test_csv('modules_update_selfenrol'));
    $import->execute();
    // Test modules table is correct.
    $queryTable = $this->getConnection()->createQueryTable('modules', 'SELECT * FROM modules');
    $expectedTable = $this->get_expected_data_set('modules_updated_selfenrol')->getTable("modules");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test module import update 'timed exams'
   * @group import
   */
  public function test_execute_update_timedexams() {
    $import = new \import\import_modules($this->get_test_csv('modules_update_timedexams'));
    $import->execute();
    // Test modules table is correct.
    $queryTable = $this->getConnection()->createQueryTable('modules', 'SELECT * FROM modules');
    $expectedTable = $this->get_expected_data_set('modules_updated_timedexams')->getTable("modules");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test module import update 'year start'
   * @group import
   */
  public function test_execute_update_yearstart() {
    $import = new \import\import_modules($this->get_test_csv('modules_update_yearstart'));
    $import->execute();
    // Test modules table is correct.
    $queryTable = $this->getConnection()->createQueryTable('modules', 'SELECT * FROM modules');
    $expectedTable = $this->get_expected_data_set('modules_updated_yearstart')->getTable("modules");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test module import - missing school
   * @group import
   */
  public function test_execute_missing_school() {
    $import = new \import\import_modules($this->get_test_csv('modules_missing_school'));
    $import->execute();
    // Test modules table is correct.
    $queryTable = $this->getConnection()->createQueryTable('modules', 'SELECT * FROM modules');
    $expectedTable = $this->get_expected_data_set('modules')->getTable("modules");
    $this->assertTablesEqual($expectedTable, $queryTable);
    // Check failure caught.
    $this->assertEquals(array('TST2'), $import->get_failed());
  }

  /**
   * Test module import - missing required header
   * @group import
   */
  public function test_execute_missing_req_header()
  {
    $import = new \import\import_modules($this->get_test_csv('modules_missing_req_header'));
    $this->expectExceptionMessage('modules_missing_req_header.csv has invalid headers');
    $import->execute();
  }
}