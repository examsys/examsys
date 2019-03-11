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
 * Test mcq question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class mcqtest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('mcq');
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
    * Test question question setter
    * @group question
    */
  public function test_set_question() {
    $data = questiondata::get_datastore('mcq');
    $data->displaymethod = 'vertical_other';
    $data->set_question(1, '0', '');
    $this->assertTrue($data->unanswered);
    $data->displaymethod = 'dropdown';
    $data->set_question(1, '4', '');
    $this->assertFalse($data->unanswered);
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option_answer() {
    $data = questiondata::get_datastore('mcq');
    $option['position'] = 4;
    $option['optiontext'] = '';
    $option['omedia'] = '';
    $option['correct'] = '1';
    $option['markscorrect'] = 2;
    $data->set_opt(1, $option);
    $data->marks = 0;
    $data->displaymethod = 'vertical';
    $data->set_option_answer(1, '4', '0000', 1);
    $option = $data->get_opt(1);
    $this->assertFalse($option['optiontextdisplay']);
    $this->assertFalse($option['displayoptionmedia']);
    $this->assertFalse($option['inact']);
    $this->assertTrue($option['selected']);
    $this->assertEquals(2, $data->marks);
    $option['position'] = 1;
    $data->set_opt(1, $option);
    $data->set_option_answer(1, '2', '1000', 1);
    $option = $data->get_opt(1);
    $this->assertTrue($option['inact']);
    $this->assertFalse($option['selected']);
  }

  /**
    * Test question additional option setter
    * @group question
    */
  public function test_process_options() {
    $data = questiondata::get_datastore('mcq');
    // Test dismiss.
    $useranswer = 'u';
    $data->optionnumber = 4;
    $option['marksincorrect'] = -1;
    $data->set_opt(1, $option);
    $data->process_options(1, $useranswer, '0100', 1);
    $this->assertTrue($data->negativemarking);
    $this->assertFalse($data->abstainselected);
    $this->assertEquals('0100', $data->dismiss);
    $option['marksincorrect'] = 0;
    $data->set_opt(1, $option);
    $data->process_options(1, $useranswer, '', 1);
    $this->assertFalse($data->negativemarking);
    $this->assertFalse($data->abstainselected);
    $this->assertEquals('0000', $data->dismiss);
    // Test abstain.
    $useranswer = 'a';
    $option['marksincorrect'] = -1;
    $data->set_opt(1, $option);
    $data->process_options(1, $useranswer, '', 1);
    $this->assertTrue($data->negativemarking);
    $this->assertTrue($data->abstainselected);
    $this->assertEquals('0000', $data->dismiss);
    // Test other.
    $data->displaymethod =  'vertical_other';
    $data->papertype = '3';
    $useranswer = 'other:test';
    $data->process_options(1, $useranswer, '', 1);
    $this->assertTrue($data->otherselected);
    $this->assertEquals('test', $data->other);
  }
}