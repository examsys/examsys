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
 * Test recyclebin class
 *
 * @author Mr Naseem Sarwar <naseem.sarwar@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class recyclebinTest extends unittestdatabase {

  /**
   * Get init data set from yml
   * @return dataset
   */
  public function getDataSet() {
    return new YamlDataSet($this->get_base_fixture_directory() . "classes" . DIRECTORY_SEPARATOR . "recyclebin.yml");
  }

  /**
   * Test count deleted from questions, papers, folders, schools, courses, modules and faculties
   * @group recyclebin
   */
  public function test_count_get_recyclebin_contents() {
    $this->userobject->load(1);
    $recyclebin = new recyclebin();
    $this->assertEquals(7, count($recyclebin->get_recyclebin_contents()));
  }

  /**
   * Test count deleted papers
   * @group recyclebin
   */
  public function test_count_get_papers_recyclebin_contents() {
    $this->userobject->load(1);
    $recyclebin = new recyclebin();
    $this->assertEquals(1, count($recyclebin->get_papers_recyclebin_contents()));
  }

  /**
   * Test count deleted questions
   * @group recyclebin
   */
  public function test_count_get_questions_recyclebin_contents() {
    $this->userobject->load(1);
    $recyclebin = new recyclebin();
    $this->assertEquals(1, count($recyclebin->get_questions_recyclebin_contents()));
  }

  /**
   * Test count deleted folders
   * @group recyclebin
   */
  public function test_count_get_folders_recyclebin_contents() {
    $this->userobject->load(1);
    $recyclebin = new recyclebin();
    $this->assertEquals(1, count($recyclebin->get_folders_recyclebin_contents()));
  }

  /**
   * Test count deleted schools
   * @group recyclebin
   */
  public function test_count_get_schools_recyclebin_contents() {
    $recyclebin = new recyclebin();
    $this->assertEquals(1, count($recyclebin->get_schools_recyclebin_contents()));
  }

  /**
   * Test count deleted
   * @group recyclebin
   */
  public function test_count_get_courses_recyclebin_contents() {
    $recyclebin = new recyclebin();
    $this->assertEquals(1, count($recyclebin->get_courses_recyclebin_contents()));
  }

  /**
   * Test count deleted modules
   * @group recyclebin
   */
  public function test_count_get_moduels_recyclebin_contents() {
    $recyclebin = new recyclebin();
    $this->assertEquals(1, count($recyclebin->get_modules_recyclebin_contents()));
  }

  /**
   * Test count deleted faculties in faculty table
   * @group recyclebin
   */
  public function test_count_get_faculties_recyclebin_contents() {
    $recyclebin = new recyclebin();
    $this->assertEquals(1, count($recyclebin->get_faculties_recyclebin_contents()));
  }
}
