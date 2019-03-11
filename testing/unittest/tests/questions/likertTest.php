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

use testing\unittest\unittest;

/**
 * Test likert question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class likerttest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('likert');
    $data->set_question_head();
    $this->assertFalse($data->displaydefault);
    $this->assertFalse($data->displaymedia);
    $data->qmedia = 'test';
    $data->set_question_head();
    $this->assertTrue($data->displaymedia);
  }

  /**
    * Test question setter
    * @group question
    */
  public function test_set_question_na() {
    $data = questiondata::get_datastore('likert');
    $data->displaymethod =  '0|1|2|3|4|true';
    $data->set_question(0, '', '');
    $this->assertFalse($data->displaylikertnotes);
    $this->assertFalse($data->displaylikertscenario);
    $this->assertTrue($data->displayna);
    $this->assertEquals(array(0, 1, 2, 3, 4), $data->scale);
  }

   /**
    * Test question setter
    * @group question
    */
  public function test_set_question_nona() {
    $data = questiondata::get_datastore('likert');
    $data->displaymethod = '0|1|2|3|4|false';
    $data->notes = 'note';
    $data->scenario = 'scenario';
    $data->set_question(0, '', '');
    $this->assertFalse($data->displayna);
    $this->assertEquals(array(0, 1, 2, 3, 4), $data->scale);
    $this->assertTrue($data->displaylikertnotes);
    $this->assertTrue($data->displaylikertscenario);
    $this->assertEquals(6, $data->likertnotescolspan);
    $this->assertEquals(7, $data->likertscenariocolspan);
  }

  /**
    * Test question option setter - na not selected
    * @group question
    */
  public function test_set_option_answer() {
    $data = questiondata::get_datastore('likert');
    $data->displaymethod =  '0|1|2|3|4|true';
    $data->scale_size = substr_count($data->displaymethod,'|');
    $data->questionno =  '1';
    $data->displayna = true;
    $data->set_option_answer(0, '4', '', 1);
    $this->assertFalse($data->unanswered);
    $this->assertEquals('1_0', $data->id);
    $this->assertEquals(array('n/a' => false, 1 => false, 2 => false, 3 => false, 4 => true, 5 => false), $data->scaleopt);
  }

  /**
    * Test question option setter - na selected
    * @group question
    */
  public function test_set_option_answer_naselected() {
    $data = questiondata::get_datastore('likert');
    $data->displaymethod =  '0|1|2|3|4|true';
    $data->scale_size = substr_count($data->displaymethod,'|');
    $data->questionno =  '1';
    $data->displayna = true;
    $data->set_option_answer(0, 'n/a', '', 1);
    $this->assertEquals(array('n/a' => true, 1 => false, 2 => false, 3 => false, 4 => false, 5 => false), $data->scaleopt);
  }

  /**
    * Test question option setter - unanswered
    * @group question
    */
  public function test_set_option_answer_unanswered() {
    $data = questiondata::get_datastore('likert');
    $data->questionno =  '1';
    $data->displayna = false;
    $data->displaymethod = '0|1|2|3|4|false';
    $data->scale_size = substr_count($data->displaymethod,'|');
    $data->set_option_answer(0, 'u', '', 1);
    $this->assertTrue($data->unanswered);
    $this->assertEquals(array(1 => false, 2 => false, 3 => false, 4 => false, 5 => false), $data->scaleopt);
  }

}