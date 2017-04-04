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
 * Test paperutils class count methods.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class paperutils_counttest extends unittestdatabase {
  public function getDataSet() {
    return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "paperutilsTest" . DIRECTORY_SEPARATOR . "paperutils_count.yml");
  }

  /**
   * Test that the count unassigned papers method counts the number of papers
   * owned by a user that are not assigned to a module.
   *
   * @group PaperUtils
   */
  public function test_count_unassigned_papers() {
    // Get the Rogo database connection.
    $db = $this->config->db;
    $paperutils = new PaperUtils();
    // Test a user who owns papers, where all are assigned or deleted.
    $this->assertEquals(0, $paperutils->count_unassigned_papers(1, $db));
    // Test a user who owns papers, and has some, but not all assigned.
    $this->assertEquals(2, $paperutils->count_unassigned_papers(2, $db));
    // Test a user who owns no papers.
    $this->assertEquals(0, $paperutils->count_unassigned_papers(3, $db));
  }

  /**
   * Test that the count unnasigned questions method counts the number of
   * questions owned by a user that are not assigned to a module.
   *
   * @group PaperUtils
   */
  public function test_count_unassigned_questions() {
    // Get the Rogo database connection.
    $db = $this->config->db;
    $paperutils = new PaperUtils();
    // Test a user who owns questions, where all are assigned or deleted.
    $this->assertEquals(0, $paperutils->count_unassigned_questions(1, $db));
    // Test a user who owns questions, and has some, but not all assigned.
    $this->assertEquals(1, $paperutils->count_unassigned_questions(2, $db));
    // Test a user who owns no questions.
    $this->assertEquals(0, $paperutils->count_unassigned_questions(3, $db));
  }
}
