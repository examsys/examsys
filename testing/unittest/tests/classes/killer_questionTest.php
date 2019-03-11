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
 * Test Killer Question class
 *
 * @author Naseem Sarwar <naseem.sarwar@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class Killer_Questiontest extends unittestdatabase {

  /**
   * Get init data set from yml
   * @return dataset
   */
  public function getDataSet() {
    return new YamlDataSet($this->get_base_fixture_directory() . "classes" . DIRECTORY_SEPARATOR . "killer_question.yml");
  }
  /**
   * Get expected data set from yml
   * @param string $name fixture file name
   * @return dataset
   */
  public function get_expected_data_set($name) {
    return new YamlDataSet($this->get_base_fixture_directory() . "classes" . DIRECTORY_SEPARATOR . $name . ".yml");
  }
  /**
   * Test checks a questions a killer
   * @group paper
   */
  public function test_is_a_killer_question() {
    // Checks a question is killer or not.
    $killer_question = new Killer_Question(1,$this->db);
    $this->assertFalse( $killer_question->is_killer_question(1));
  }

  /**
   * Test sets a killer question
   * @group paper
   */
  public function test_set_question(){
    $killer_question = new Killer_Question(1,$this->db);
    $this->assertEquals(0, count($killer_question->get_questions()));
  }

  /**
   * Test counts killer questions by paper
   * @group paper
   */
  public function test_get_questions(){
    $killer_question = new Killer_Question(1,$this->db);
    $killer_question->set_question(1);
    $killer_question->save();
    $this->assertEquals(1, count($killer_question->get_questions()));
  }

  /**
   * Test checks copy killer questions from one paper to the other
   * @group paper
   */
  public function test_copy_killer_questions(){
    $killer_question = new Killer_Question(1,$this->db);
    $killer_question->set_question(1);
    $killer_question->save();
    $killer_question->copy_killer_questions(2 );
    $killer_question_new = new Killer_Question(2,$this->db);
    $this->assertEquals(1, count($killer_question_new->get_questions()));
    $querytable = $this->getConnection()->createQueryTable('killer_questions', 'SELECT paperID, q_id FROM killer_questions');
    $expectedtable = $this->get_expected_data_set('copy_killer_questions')->getTable("killer_questions");
    $this->assertTablesEqual($expectedtable, $querytable);
  }

  /**
   * Test unsets the killer question
   * @group paper
   */
  public function test_unset_question(){
    $killer_question = new Killer_Question(1,$this->db);
    $killer_question->unset_question(1);
    $killer_question->save();
    $this->assertEquals(0, count($killer_question->get_questions()));
    $killer_question_new = new Killer_Question(2,$this->db);
    $killer_question_new->unset_question(1);
    $killer_question_new->save();
  }
}
