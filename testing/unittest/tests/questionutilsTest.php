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
 * Tests for the QuestionUtils class
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class QuestionUtilsTest extends unittestdatabase {
  /**
   * Get expected data set from yml
   * @param string $name fixture file name
   * @return dataset
   */
  public function getDataSet() {
    return new YamlDataSet($this->get_base_fixture_directory() . "questionutilsTest" . DIRECTORY_SEPARATOR . "questions.yml");
  }

  /**
   * Get expected data set from yml
   * @param string $name fixture file name
   * @return dataset
   */
  public function get_expected_data_set($name) {
    return new YamlDataSet($this->get_base_fixture_directory() . "questionutilsTest" . DIRECTORY_SEPARATOR . $name . ".yml");
  }

  /**
   * Test that we can detect if a summative question has been answered by a student.
   *
   * @group questions
   */
  public function test_question_answered_in_summative() {
    // Answered by student.
    $this->assertTrue(QuestionUtils::question_answered_in_summative(33, $this->db));
    // Answered by non student.
    $this->assertFalse(QuestionUtils::question_answered_in_summative(88, $this->db));
    // Not answered.
    $this->assertFalse(QuestionUtils::question_answered_in_summative(69, $this->db));
  }

  /**
   * Test get question details
   * @group questions
   */
  public function test_get_correct_answer() {
    $question = array();
    $expected['ID'] = 1;
    $expected['type'] = 'mcq';
    $expected['score_method'] = 'Mark per Option';
    $expected['correct'] = ',1';
    $expected['option_text'] = "maybe";
    $expected['correct_text'] = "\ttrue\tfalse\tmaybe";
    $this->assertEquals($expected, QuestionUtils::get_correct_answer($question, 1, $this->db));
  }

  /**
   * Test fix correct (fill in the blank)
   * @group questions
   */
  public function test_fix_correct() {
    $expected = ',a';
    $q_type = 'blank';
    $correct = '';
    $old_correct = '';
    $option_text = '<div>test [blank]a,b,c[/blank]</div> ';
    $this->assertEquals($expected, QuestionUtils::fix_correct($q_type, $correct, $old_correct, $option_text));
  }
}
