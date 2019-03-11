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
 * Test mrq question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class mrqtest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('mrq');
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
    $data = questiondata::get_datastore('mrq');
    $useranswer = 'nnnnn';
    $data->scoremethod = 'Mark per Question';
    $data->optionnumber = 2;
    $data->set_question(1, $useranswer, '');
    $this->assertTrue($data->unanswered);
    $this->assertEquals(2, $data->allowedresponses);
    $useranswer = 'nnnyn';
    $data->set_question(1, $useranswer, '');
    $this->assertFalse($data->unanswered);
    $useranswer = 'nynyn';
    $data->set_question(1, $useranswer, '');
    $this->assertFalse($data->unanswered);
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option_answer() {
    $data = questiondata::get_datastore('mrq');
    $option['position'] = 1;
    $option['optiontext'] = '';
    $option['omedia'] = '';
    $option['correct'] = 'n';
    $option['markscorrect'] = 1;
    $data->set_opt(1, $option);
    $data->marks = 0;
    $data->scoremethod = 'Mark per Option';
    $useranswer = 'nnny';
    $data->set_option_answer(1, $useranswer, '1000', 1);
    $option = $data->get_opt(1);
    $this->assertFalse($option['selected']);
    $this->assertTrue($option['inact']);
    $this->assertFalse($option['optiontextdisplay']);
    $this->assertFalse($option['displayoptionmedia']);
    $this->assertFalse($data->negativemarking);
    $this->assertFalse($data->abstainselected);
    $this->assertEquals(0, $data->marks);
  }

  /**
    * Test question additional option setter
    * @group question
    */
  public function test_process_options() {
    $data = questiondata::get_datastore('mrq');
    // Test other.
    $useranswer = 'nnnyn';
    $data->optionnumber = 5;
    $option['marksincorrect'] = 0;
    $option['position'] = 4;
    $option['correct'] = 'y';
    $data->set_opt(4, $option);
    $data->displaymethod = 'other';
    $data->partid = 4;
    $useranswer = 'nnnnytest';
    $data->process_options(4, $useranswer, '00000', 1);
    $this->assertEquals(5, $data->partid);
    $this->assertTrue($data->otherselected);
    $this->assertEquals('test', $data->other);
    // Test dismiss.
    $option['marksincorrect'] = -1;
    $data->set_opt(1, $option);
    $data->process_options(1, $useranswer, '01000', 1);
    $this->assertEquals('01000', $data->dismiss);
    $data->process_options(1, $useranswer, '', 1);
    $this->assertEquals('00000', $data->dismiss);
    // Test abstain.
    $useranswer = 'a';
    $option['markscorrect'] = -1;
    $data->process_options(1, $useranswer, '1000', 1);
    $option = $data->get_opt(1);
    $this->assertTrue($data->negativemarking);
    $this->assertTrue($data->abstainselected);
  }
}