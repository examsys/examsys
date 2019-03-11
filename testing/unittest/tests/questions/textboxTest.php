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
 * Test textbox question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class textboxtest extends unittestdatabase{

  /**
   * Get init data set from yml
   * @return dataset
   */
  public function getDataSet() {
    return new YamlDataSet($this->get_base_fixture_directory()
      . DIRECTORY_SEPARATOR. "questions"
      . DIRECTORY_SEPARATOR . "textboxTest"
      . DIRECTORY_SEPARATOR . "textbox.yml");
  }

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('textbox');
    $data->set_question_head();
    $this->assertTrue($data->displaydefault);
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
    * Test question option setter - mathjax
    * @group question
    */
  public function test_set_option_answer_mathjax() {
    $data = questiondata::get_datastore('textbox');
    $data->settings = json_encode(array('columns' => 40, 'rows' => 10, 'editor' => 'mathjax'));
    $data->questionno = 2;
    $data->textboxesseen = array(1);
    $option['markscorrect'] = 1;
    $data->set_opt(0, $option);
    $data->marks = 1;
    $data->set_option_answer(0, '', '', 1);
    $this->assertEquals(40, $data->editorcolumns);
    $this->assertEquals(10, $data->editorrows);
    $this->assertEquals('plain', $data->editor);
    $this->assertEquals('', $data->useranswer);
    $this->assertTrue($data->unanswered);
    $this->assertTrue($data->editormathjax);
    $this->assertEquals(array(1, 2), $data->textboxesseen);
    $this->assertEquals(2, $data->marks);
    $data->questionno = 3;
    $data->set_option_answer(0, 'test', '', 1);
    $this->assertEquals('test', $data->useranswer);
    $this->assertFalse($data->unanswered);
    
  }

  /**
    * Test question option setter - tinymce
    * @group question
    */
  public function test_set_option_answer_tinymce() {
    ob_start(); // Start output buffering
    $data = questiondata::get_datastore('textbox');
    $data->settings = json_encode(array('columns' => 40, 'rows' => 10, 'editor' => 'WYSIWYG'));
    $data->questionno = 2;
    $data->textboxesseen = array(1);
    $option['markscorrect'] = 1;
    $data->set_opt(0, $option);
    $data->marks = 1;
    $data->set_option_answer(0, '', '', 1);
    $this->assertEquals(40, $data->editorcolumns);
    $this->assertEquals(10, $data->editorrows);
    $this->assertEquals('tinymce3', $data->editor);
    $this->assertTrue($data->unanswered);
    $this->assertFalse($data->editormathjax);
    $this->assertEquals(array(1, 2), $data->textboxesseen);
    $this->assertEquals(2, $data->marks);
    $data->questionno = 3;
    $data->set_option_answer(0, 'test', '', 1);
    $this->assertFalse($data->unanswered);
    $output = ob_get_contents(); // Store buffer in variable
    ob_end_clean(); // End buffering and clean up
  }

}