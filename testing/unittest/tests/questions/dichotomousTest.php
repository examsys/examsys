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
 * Test fill in the dichotomous question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class dichotomoustest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('dichotomous');
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
    * Test question option setter
    * @group question
    */
  public function test_set_option_answer() {
    $data = questiondata::get_datastore('dichotomous');
    $option['markscorrect'] = 1;
    $option['position'] = 1;
    $option['omedia'] = '';
    $data->set_opt(0, $option);
    $data->displaymethod = 'TF_Positive';
    $data->marks = 0;
    $useranswer = 'uuu';
    $data->set_option_answer(0, $useranswer, '', 1);
    $option = $data->get_opt(0);
    $this->assertTrue($data->unanswered);
    $this->assertFalse($option['displayoptionmedia']);
    $this->assertFalse($option['abstain']);
    $this->assertEquals(1, $data->marks);
    $option['omedia'] = 'test';
    $data->set_opt(0, $option);
    $data->displaymethod = 'TF_NegativeAbstain';
    $data->set_option_answer(0, $useranswer, '', 1);
    $option = $data->get_opt(0);
    $this->assertTrue($option['abstain']);
    $this->assertTrue($option['displayoptionmedia']);
  }

  /**
    * Test question additional option setter
    * @group question
    */
  public function test_process_options() {
    $data = questiondata::get_datastore('dichotomous');
    $useranswer = 'uuu';
    $option['marksincorrect'] = -1;
    $data->set_opt(0, $option);
    $data->process_options(0, $useranswer, '', 1);
    $this->assertTrue($data->negativemarking);
    $option['marksincorrect'] = 0;
    $data->set_opt(0, $option);
    $data->process_options(0, $useranswer, '', 1);
    $this->assertFalse($data->negativemarking);
  }
  
}