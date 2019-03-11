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
 * Test fill in the flash question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class flashtest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('flash');
    $data->set_question_head();
    $this->assertTrue($data->displaydefault);
    $this->assertFalse($data->displaynotes);
    $this->assertFalse($data->displayscenario);
    $this->assertTrue($data->displayleadin);
    $data->notes = 'test';
    $data->scenario = 'test';
    $data->set_question_head();
    $this->assertTrue($data->displaynotes);
    $this->assertTrue($data->displayscenario);
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option_answer() {
    $data = questiondata::get_datastore('flash');
    $option['markscorrect'] = 1;
    $data->set_opt(0, $option);
    $data->marks = 1;
    $data->set_option_answer(0, '', '', 0);
    $this->assertEquals(2, $data->marks);
  }

}