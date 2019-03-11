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
 * Test rank question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class ranktest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('rank');
    $data->set_question_head();
    $this->assertTrue($data->displaydefault);
    $this->assertFalse($data->displaynotes);
    $this->assertFalse($data->displayscenario);
    $this->assertTrue($data->displayleadin);
    $this->assertFalse($data->displaymedia);
    $data->notes = 'test';
    $data->scenario = 'test';
    $data->qmedia = 'test';
    $data->set_question_head();
    $this->assertTrue($data->displaynotes);
    $this->assertTrue($data->displayscenario);
    $this->assertTrue($data->displaymedia);
  }

  /**
    * Test question option setter - not answered
    * @group question
    */
  public function test_set_option_answer() {
    $data = questiondata::get_datastore('rank');
    $data->papertype = '0'; 
    $data->scoremethod = 'Mark per Option';
    $data->optionnumber = 3;
    $data->marks = 0;
    $question['options'][0]['correct'] = 1;
    $question['options'][1]['correct'] = 2;
    $question['options'][2]['correct'] = 0;
    $data->question = $question;
    $option['position'] = 1;
    $option['correct'] = 1;
    $option['markscorrect'] = 1;
    $data->set_opt(1, $option);
    $useranswer = 'u,u,u';
    $data->set_option_answer(1, $useranswer, '100', 1);
    $option = $data->get_opt(1);
    $this->assertTrue($data->unanswered);
    $this->assertTrue($option['unans']);
    $this->assertFalse($option['selected'][0]['value']);
    $this->assertFalse($option['selected'][1]['value']);
    $this->assertFalse($option['selected'][2]['value']);
    $this->assertTrue($option['inact']);
    $this->assertEquals(1, $data->marks);
  }

  /**
    * Test question option setter - partially answered
    * @group question
    */
  public function test_set_option_answer_partial() {
    $data = questiondata::get_datastore('rank');
    $data->papertype = '0'; 
    $data->scoremethod = 'Mark per Option';
    $data->optionnumber = 3;
    $data->marks = 0;
    $question['options'][0]['correct'] = 1;
    $question['options'][1]['correct'] = 2;
    $question['options'][2]['correct'] = 0;
    $data->question = $question;
    $option['position'] = 1;
    $option['correct'] = 1;
    $option['markscorrect'] = 1;
    $option['position'] = 1;
    $data->set_opt(1, $option);
    $useranswer = '1,u,u';
    $data->set_option_answer(1, $useranswer, '000', 1);
    $this->assertFalse($data->unanswered);
    $option = $data->get_opt(1);
    $this->assertFalse($option['unans']);
    $this->assertFalse($option['selected'][0]['value']);
    $this->assertTrue($option['selected'][1]['value']);
    $this->assertFalse($option['selected'][2]['value']);
    $this->assertFalse($option['inact']);
  }

  /**
    * Test question additional option setter
    * @group question
    */
  public function test_process_options() {
    $data = questiondata::get_datastore('rank');
    // Test dismiss and negative marking.
    $useranswer = '1,u,u';
    $data->optionnumber = 3;
    $option['marksincorrect'] = -1;
    $data->set_opt(1, $option);
    $data->process_options(1, $useranswer, '010', 1);
    $this->assertEquals('010', $data->dismiss);
    $this->assertTrue($data->negativemarking);
    $option['marksincorrect'] = 0;
    $data->set_opt(1, $option);
    $data->process_options(1, $useranswer, '', 1);
    $this->assertEquals('000', $data->dismiss);
    $this->assertFalse($data->negativemarking);
  }
}