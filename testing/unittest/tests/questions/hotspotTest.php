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
 * Test hotspot question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class hotspottest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('hotspot');
    $data->set_question_head();
    $this->assertTrue($data->displaydefault);
    $this->assertFalse($data->displaynotes);
    $this->assertFalse($data->displayscenario);
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
    $data = questiondata::get_datastore('hotspot');
    $option['correct'] = 'Chocolate calculator~16711680~polygon~16a,399,152,3c7,1a9,3ed,106,407,f9,3a6~0~|Dictionary~16776960~ellipse~392,382,2d1,418~0~';
    $option['markscorrect'] = 1;
    $data->set_opt(0, $option);
    $useranswer = 'u';
    $data->set_option_answer(0, $useranswer, '', 1);
    $this->assertTrue($data->unanswered);
    $useranswer = '1,325,995|1,825,965';
    $data->mediaheight = 1600;
    $data->scoremethod = 'Mark per Question';
    $data->set_option_answer(0, $useranswer, '', 1);
    $this->assertFalse($data->unanswered);
    $this->assertEquals($option['correct'], $data->tmpcorrect);
    $this->assertEquals(1601, $data->mediaheight);
    $this->assertEquals($useranswer, $data->useranswer);
    $this->assertEquals(1, $data->screensubmitted);
    $this->assertEquals(1, $data->marks);
    $data->scoremethod =  'Mark per Option';
    $data->set_option_answer(0, $useranswer, '', 1);
    $this->assertEquals(2, $data->marks);
  }

}