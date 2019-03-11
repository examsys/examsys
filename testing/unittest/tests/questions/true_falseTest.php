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
 * Test true/false question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class true_falsetest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('true_false');
    $data->set_question_head();
    $this->assertTrue($data->displaydefault);
    $this->assertTrue($data->displayleadin);
    $this->assertFalse($data->displaynotes);
    $this->assertFalse($data->displayscenario);
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
    * Test question option setter - tinymce
    * @group question
    */
  public function test_set_option_answer() {
    $data = questiondata::get_datastore('true_false');
    $option['markscorrect'] = 1;
    $data->set_opt(0, $option);
    $data->marks = 1;
    $data->displaymethod = 'dropdown';
    $data->set_option_answer(0, 'u', '', 1);
    $this->assertEquals(1, $data->marks);
    $this->assertTrue($data->unanswered);
    $data->set_option_answer(0, 't', '', 1);
    $this->assertFalse($data->unanswered);
    $this->assertTrue($data->trueselected);
    $this->assertFalse($data->falseselected);
    $data->displaymethod = 'vertical';
    $data->set_option_answer(0, 'f', '', 1);
    $this->assertFalse($data->trueselected);
    $this->assertTrue($data->falseselected);
    $data->negativemarking = true;
    $data->set_option_answer(0, 'a', '', 1);
    $this->assertTrue($data->abstainselected);
    $data->set_option_answer(0, 't', '', 1);
    $this->assertfalse($data->abstainselected);
    $this->assertTrue($data->trueselected);
  }

}