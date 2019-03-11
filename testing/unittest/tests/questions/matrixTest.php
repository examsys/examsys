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
 * Test matrix question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class matrixtest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('matrix');
    $data->set_question_head();
    $this->assertTrue($data->displaydefault);
    $this->assertFalse($data->displaynotes);
    $this->assertTrue($data->displayleadin);
    $this->assertFalse($data->displaymedia);
    $data->notes = 'test';
    $data->set_question_head();
    $this->assertTrue($data->displaynotes);
  }

  /**
    * Test question question setter
    * @group question
    */
  public function test_set_question() {
    $data = questiondata::get_datastore('matrix');
    $data->scenario = "Word|Excel|PowerPoint|Access5|Publisher|Data File||||";
    $useranswer = '3|4|2|5|1|6';
    $scenarios = array('Word', 'Excel', 'PowerPoint', 'Access5', 'Publisher', 'Data File', '', '', '', '');
    $data->set_question(1, $useranswer, '');
    $this->assertEquals($scenarios, $data->scenarios);
    $this->assertEquals(array('3', '4', '2', '5', '1', '6'), $data->usersanswers);
    $useranswer = null;
    $data->set_question(0, $useranswer, '');
    $this->assertEquals(array(), $data->usersanswers);
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option_answer() {
    $data = questiondata::get_datastore('matrix');
    $data->matchoptions = array(0 => array('option' => '.PUB'));
    $data->set_opt(1, array('optiontext' => '.PUB'));
    $data->set_opt(2, array('optiontext' => '.PPT'));
    $data->set_opt(3, array('optiontext' => '.DOC'));
    $data->set_opt(4, array('optiontext' => '.XLS'));
    $data->set_opt(5, array('optiontext' => '.MDB'));
    $data->set_opt(6, array('optiontext' => '.DAT'));
    $data->set_option_answer(2, '3|4|2|5|1|6', '', 1);
    $this->assertEquals(array(0 => array('option' => '.PUB'), 1 => array('option' => '.PPT')), $data->matchoptions);
  }

  /**
    * Test question additional option setter
    * @group question
    */
  public function test_process_options() {
    $data = questiondata::get_datastore('matrix');
    $data->matchoptions = array(0 => array('option' => '.PUB'),
        1 => array('option' => '.PPT'),
        2 => array('option' => '.DOC'),
        3 => array('option' => '.XLS'),
        4 => array('option' => '.MDB'),
        5 => array('option' => '.DAT'));
    $data->usersanswers = array('3', '4', '2', '5', '1', '6');
    $data->optionorder = '5,2,4,1,0,3';
    $data->scenarios = array('Word', 'Excel', 'PowerPoint', 'Access5', 'Publisher', 'Data File', '', '', '', '');
    $useranswer = '3|4|2|5|1|6';
    $option['markscorrect'] = 1;
    $data->set_opt(1, $option);
    $data->set_opt(2, $option);
    $data->set_opt(3, $option);
    $data->set_opt(4, $option);
    $data->set_opt(5, $option);
    $data->set_opt(6, $option);
    $data->process_options(1, $useranswer, '', 1);
    $matchscenarios = array(
      array('unanswered' => false, 'id' => 'A', 'value' => 'Word'),
      array('unanswered' => false, 'id' => 'B', 'value' => 'Excel'),
      array('unanswered' => false, 'id' => 'C', 'value' => 'PowerPoint'),
      array('unanswered' => false, 'id' => 'D', 'value' => 'Access5'),
      array('unanswered' => false, 'id' => 'E', 'value' => 'Publisher'),
      array('unanswered' => false, 'id' => 'F', 'value' => 'Data File'),
    );
    $matchoptions = array(
      array('option' => '.PUB', 'value' => 6, 'selected' => array(1 => false,
          2 => false,
          3 => false,
          4 => false,
          5 => false,
          6 => true)),
      array('option' => '.PPT', 'value' => 3, 'selected' => array(1 => true,
          2 => false,
          3 => false,
          4 => false,
          5 => false,
          6 => false)),
      array('option' => '.DOC', 'value' => 5, 'selected' => array(1 => false,
          2 => false,
          3 => false,
          4 => true,
          5 => false,
          6 => false)),
      array('option' => '.XLS', 'value' => 2, 'selected' => array(1 => false,
          2 => false,
          3 => true,
          4 => false,
          5 => false,
          6 => false)),
      array('option' => '.MDB', 'value' => 1, 'selected' => array(1 => false,
          2 => false,
          3 => false,
          4 => false,
          5 => true,
          6 => false)),
      array('option' => '.DAT', 'value' => 4, 'selected' => array(1 => false,
          2 => true,
          3 => false,
          4 => false,
          5 => false,
          6 => false))
    );
    $this->assertEquals($matchoptions, $data->matchoptions);
    $this->assertEquals($matchscenarios, $data->matchscenarios);
  }
}