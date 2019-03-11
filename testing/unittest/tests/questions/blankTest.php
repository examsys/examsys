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
 * Test fill in the blank question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class blanktest extends unittest{

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('blank');
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
    * Test question option setter - textbox responses
    * @group question
    */
  public function test_set_option_answer_textbox() {
    $data = questiondata::get_datastore('blank');
    $option['optiontext'] = '<div>London is the capital of [blank]England,Scotland,Wales,Northern Ireland[/blank] and is in the [blank]United Kingdom,United States of America[/blank]</div>';
    $option['markscorrect'] = 1;
    $data->set_opt(0, $option);
    $data->displaymethod = 'textboxes';
    $data->scoremethod = 'Mark per Option';
    $data->marks = 0;
    $useranswer = '["Wales","u"]';
    $data->set_option_answer(0, $useranswer, '', 1);
    $this->assertTrue($data->unanswered);
    $blankoptions[1] = array('itemtype' => 'blurb', 'itemvalue' => '<div>London is the capital of ');
    $blankoptions[2] = array('itemtype' => 'blank', 'itemcount' => 1, 'size' => 15, 'unans' => false, 'encoded_ans' => 'Wales');
    $blankoptions[3] = array('itemtype' => 'blurb', 'itemvalue' => ' and is in the ');
    $blankoptions[4] = array('itemtype' => 'blank', 'itemcount' => 2, 'size' => 15, 'unans' => true);
    $blankoptions[5] = array('itemtype' => 'blurb', 'itemvalue' => '</div>');
    $this->assertEquals($blankoptions, $data->blankoptions);
    $this->assertEquals(2, $data->marks);
    $data->scoremethod = 'Mark per Question';
    $data->set_option_answer(0, $useranswer, '', 1);
    $this->assertEquals(1, $data->marks);
  }

  /**
    * Test question option setter - with size element
    * @group question
    */
  public function test_set_option_answer_textbox_size() {
    $data = questiondata::get_datastore('blank');
    $option['optiontext'] = '<div>London is the capital of [blank|size="100"|]England,Scotland,Wales,Northern Ireland[/blank] and is in the [blank]United Kingdom,United States of America[/blank]</div>';
    $option['markscorrect'] = 1;
    $data->set_opt(0, $option);
    $data->displaymethod = 'textboxes';
    $data->scoremethod = 'Mark per Option';
    $data->marks = 0;
    $useranswer = '["Wales","u"]';
    $data->set_option_answer(0, $useranswer, '', 1);
    $blankoptions[1] = array('itemtype' => 'blurb', 'itemvalue' => '<div>London is the capital of ');
    $blankoptions[2] = array('itemtype' => 'blank', 'itemcount' => 1, 'size' => 100, 'unans' => false, 'encoded_ans' => 'Wales');
    $blankoptions[3] = array('itemtype' => 'blurb', 'itemvalue' => ' and is in the ');
    $blankoptions[4] = array('itemtype' => 'blank', 'itemcount' => 2, 'size' => 15, 'unans' => true);
    $blankoptions[5] = array('itemtype' => 'blurb', 'itemvalue' => '</div>');
    $this->assertEquals($blankoptions, $data->blankoptions);
  }

  /**
    * Test question option setter - with mark element
    * @group question
    */
  public function test_set_option_answer_textbox_marks() {
    $data = questiondata::get_datastore('blank');
    $option['optiontext'] = '<div>London is the capital of [blank|mark="3"|]England,Scotland,Wales,Northern Ireland[/blank] and is in the [blank]United Kingdom,United States of America[/blank]</div>';
    $option['markscorrect'] = 1;
    $data->set_opt(0, $option);
    $data->displaymethod = 'textboxes';
    $data->scoremethod = 'Mark per Option';
    $data->marks = 0;
    $useranswer = '["Wales","u"]';
    $data->set_option_answer(0, $useranswer, '', 1);
    $this->assertEquals(4, $data->marks);
  }

  /**
    * Test question option setter - with mark and size elements
    * @group question
    */
  public function test_set_option_answer_textbox_marks_size() {
    $data = questiondata::get_datastore('blank');
    $option['optiontext'] = '<div>London is the capital of [blank|size="100"|mark="3"|]England,Scotland,Wales,Northern Ireland[/blank] and is in the [blank]United Kingdom,United States of America[/blank]</div>';
    $option['markscorrect'] = 1;
    $data->set_opt(0, $option);
    $data->displaymethod = 'textboxes';
    $data->scoremethod = 'Mark per Option';
    $data->marks = 0;
    $useranswer = '["Wales","u"]';
    $data->set_option_answer(0, $useranswer, '', 1);
    $this->assertEquals(4, $data->marks);
    $blankoptions[1] = array('itemtype' => 'blurb', 'itemvalue' => '<div>London is the capital of ');
    $blankoptions[2] = array('itemtype' => 'blank', 'itemcount' => 1, 'size' => 100, 'unans' => false, 'encoded_ans' => 'Wales');
    $blankoptions[3] = array('itemtype' => 'blurb', 'itemvalue' => ' and is in the ');
    $blankoptions[4] = array('itemtype' => 'blank', 'itemcount' => 2, 'size' => 15, 'unans' => true);
    $blankoptions[5] = array('itemtype' => 'blurb', 'itemvalue' => '</div>');
    $this->assertEquals($blankoptions, $data->blankoptions);
  }

  /**
    * Test question option setter - dropdown responses
    * @group question
    */
  public function test_set_option_answer_dropdown() {
    $data = questiondata::get_datastore('blank');
    $option['optiontext'] = '<div>London is the capital of [blank]England,Scotland,Wales,Northern Ireland[/blank] and is in the [blank]United Kingdom,United States of America[/blank]</div>';
    $option['markscorrect'] = 1;
    $data->set_opt(0, $option);
    $data->displaymethod = 'dropdown';
    $data->scoremethod = 'Mark per Option';
    $data->marks = 0;
    $useranswer = '["Wales","u"]';
    $data->set_option_answer(0, $useranswer, '', 1);
    $this->assertTrue($data->unanswered);
    $blankoptions[1] = array('itemtype' => 'blurb', 'itemvalue' => '<div>London is the capital of ');
    $blankoptions[2] = array('itemtype' => 'blank', 'itemcount' => 1, 'unans' => false, 'itemvalue' => array (
        0 => array('answer' => 'England', 'selected' => false),
        1 => array('answer' => 'Northern Ireland', 'selected' => false),
        2 => array('answer' => 'Scotland', 'selected' => false),
        3 => array('answer' => 'Wales', 'selected' => true)));
    $blankoptions[3] = array('itemtype' => 'blurb', 'itemvalue' => ' and is in the ');
    $blankoptions[4] = array('itemtype' => 'blank', 'itemcount' => 2, 'unans' => true, 'itemvalue' => array (
         0 => array('answer' => 'United Kingdom', 'selected' => false),
         1 => array('answer' => 'United States of America', 'selected' => false)));
    $blankoptions[5] = array('itemtype' => 'blurb', 'itemvalue' => '</div>');
    // Need to split test as itemvalue randomly shuffled so need to sort before test.
    $options = $data->blankoptions;
    $this->assertEquals($blankoptions[1], $options[1]);
    sort($options[2]['itemvalue']);
    $this->assertEquals($blankoptions[2], $options[2]);
    $this->assertEquals($blankoptions[3], $options[3]);
    sort($options[4]['itemvalue']);
    $this->assertEquals($blankoptions[4], $options[4]);
    $this->assertEquals($blankoptions[5], $options[5]);
    $this->assertEquals(2, $data->marks);
    $data->scoremethod = 'Mark per Question';
    $data->set_option_answer(0, $useranswer, '', 1);
    $this->assertEquals(1, $data->marks);
  }

}