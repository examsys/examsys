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
 * Test fill in the enhancedcalc question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class enhancedcalctest extends unittestdatabase{
  /**
    * Get init data set from yml
    * @return dataset
    */
  public function getDataSet() {
    return new YamlDataSet($this->get_base_fixture_directory()
      . DIRECTORY_SEPARATOR. "questions"
      . DIRECTORY_SEPARATOR . "enchancedcalcTest"
      . DIRECTORY_SEPARATOR . "enhancedcalc.yml");
  }

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('enhancedcalc');
    $data->set_question_head();
    $this->assertTrue($data->displaydefault);
    $this->assertFalse($data->displaynotes);
    $data->notes = 'test';
    $data->set_question_head();
    $this->assertTrue($data->displaynotes);
  }

  /**
    * Test question question setter
    * @group question
    */
  public function test_set_question() {
    $data = questiondata::get_datastore('enhancedcalc');
    $cfg_web_root = $this->config->get('cfg_web_root');
    require_once $cfg_web_root . 'plugins/questions/enhancedcalc/enhancedcalc.class.php';
    $question['object'] = new \EnhancedCalc($this->config);
    $data->question = $question;
    $useranswer = '{"vars":{"$A":2,"$B":8},"uans":""}';
    $data->set_question(1, $useranswer, '');
    $this->assertEquals($data->useranswers, $question['object']->alluseranswers);
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option_answer() {
    ob_start(); // Start output buffering
    $data = questiondata::get_datastore('enhancedcalc');
    $data->marks = 0;
    $data->questionno = 1;
    $cfg_web_root = $this->config->get('cfg_web_root');
    require_once $cfg_web_root . 'plugins/questions/enhancedcalc/enhancedcalc.class.php';
    $propertyObj = \PaperProperties::get_paper_properties_by_id(1, $this->db, array(), true);
    $questions =  $propertyObj->build_paper(true, 1, 1);
    $questions[1]['object'] = new \EnhancedCalc($this->config);
    $questions[1]['object']->load($questions[1]);
    $data->question = $questions[1];
    $useranswer = '{"vars":{"$A":2,"$B":8},"uans":""}';
    $data->set_option_answer(1, $useranswer, '', 1);
    $this->assertEquals(3, $data->marks);
    $output = ob_get_contents(); // Store buffer in variable
    ob_end_clean(); // End buffering and clean up
  }
}