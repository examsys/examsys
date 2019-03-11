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
 * Test fill in the extmatch question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class extmatchtest extends unittestdatabase{

  /**
   * Get init data set from yml
   * @return dataset
   */
  public function getDataSet() {
    return new YamlDataSet($this->get_base_fixture_directory()
      . DIRECTORY_SEPARATOR. "questions"
      . DIRECTORY_SEPARATOR . "questiondataTest"
      . DIRECTORY_SEPARATOR . "questiondata.yml");
  }

  /**
    * Test question header setter
    * @group question
    */
  public function test_set_question_head() {
    $data = questiondata::get_datastore('extmatch');
    $data->set_question_head();
    $this->assertTrue($data->displaydefault);
    $this->assertFalse($data->displaynotes);
    $this->assertTrue($data->displayleadin);
    $data->notes = 'test';
    $data->set_question_head();
    $this->assertTrue($data->displaynotes);
  }

  /**
    * Test question question setter
    * @group question
    */
  public function test_set_question() {
    $data = questiondata::get_datastore('extmatch');
    $data->scenario =  "<div>It is now known as;</div>|<div>It's football team is called</div>|<div>It's most famous landmark is the</div>|||||||";
    $data->qmedia = "1516973089.jpg|1516973089.png|1516975621.jpg||||||||";
    $data->qmediawidth = "1480|480|951||||||||";
    $data->qmediaheight = "776|105|121||||||||";
    $scenarios = array("<div>It is now known as;</div>",
        "<div>It's football team is called</div>",
        "<div>It's most famous landmark is the</div>", "", "", "", "", "", "", "");
    $media = array("1516973089.jpg",
        "1516973089.png",
        "1516975621.jpg", "", "", "", "", "", "", "", "");
    $width = array(1480, 480, 951, "", "", "", "", "", "", "", "");
    $height= array(776, 105, 121, "", "", "", "", "", "", "", "");
    $useranswer = '1|3|2';
    $data->set_question(1, $useranswer, '');
    $this->assertEquals($scenarios, $data->scenarios);
    $this->assertEquals($media, $data->media);
    $this->assertEquals($width, $data->mediawidth);
    $this->assertEquals($height, $data->mediaheight);
    $this->assertEquals(array('1', '3', '2'), $data->usersanswers);
    $useranswer = null;
    $data->set_question(0, $useranswer, '');
    $this->assertEquals(array(), $data->usersanswers);
  }

  /**
    * Test question option setter
    * @group question
    */
  public function test_set_option_answer() {
    $data = questiondata::get_datastore('extmatch');
    $data->matchoptions = array('Paris');
    $data->set_opt(1, array('optiontext' => 'Paris'));
    $data->set_opt(2, array('optiontext' => 'Eifel Tower'));
    $data->set_opt(3, array('optiontext' => 'Paris Saint-Germain F.C.'));
    $data->set_opt(4, array('optiontext' => 'Derby'));
    $data->set_opt(5, array('optiontext' => 'Intu Centre'));
    $data->set_opt(6, array('optiontext' => 'Derby County F.C.'));
    $data->set_option_answer(2, '1|3|2', '', 1);
    $this->assertEquals(array('Paris', 'Eifel Tower'), $data->matchoptions);
  }

  /**
    * Test question additional option setter
    * @group question
    */
  public function test_process_options() {
    $data = questiondata::get_datastore('extmatch');
    $data->set_opt(1, array('optiontext' => 'Paris', 'correct' => '1$4|3|2|||||||', 'markscorrect' => 1));
    $data->set_opt(2, array('optiontext' => 'Eifel Tower', 'correct' => '1$4|3|2|||||||', 'markscorrect' => 1));
    $data->set_opt(3, array('optiontext' => 'Paris Saint-Germain F.C.', 'correct' => '1$4|3|2|||||||', 'markscorrect' => 1));
    $data->set_opt(4, array('optiontext' => 'Derby', 'correct' => '1$4|3|2|||||||', 'markscorrect' => 1));
    $data->set_opt(5, array('optiontext' => 'Intu Centre', 'correct' => '1$4|3|2|||||||', 'markscorrect' => 1));
    $data->set_opt(6, array('optiontext' => 'Derby County F.C.', 'correct' => '1$4|3|2|||||||', 'markscorrect' => 1));
    $data->matchoptions = array('Paris', 'Eifel Tower', 'Paris Saint-Germain F.C.', 'Derby', 'Intu Centre', 'Derby County F.C.');
    $data->optionorder = "0,1,2,3,4,5";
    $data->scenarios = array("<div>It is now known as;</div>",
        "<div>It's football team is called</div>",
        "<div>It's most famous landmark is the</div>", "", "", "", "", "", "", "");
    $data->media = array("1516973089.jpg",
        "1516973089.png",
        "1516975621.jpg", "", "", "", "", "", "", "", "");
    $data->mediawidth = array(1480, 480, 951, "", "", "", "", "", "", "", "");
    $data->mediaheight =  array(776, 105, 121, "", "", "", "", "", "", "", "");
    $data->usersanswers =  array('1', '3', '2');
    $data->marks = 0;
    $cfg_root_path = $this->config->get('cfg_root_path');
    $matchstem = array(
      array('answerno' => 2,
          'scenario' => "<div>It is now known as;</div>",
          'display' => true,
          'media' => array('mediaid' => -1,
            'mediafile' => '1516973089.png',
            'mediawidth' => 480,
            'mediaheight' => 105,
            'mediaurl' => $cfg_root_path . '/getfile.php?type=media&filename=1516973089.png',
            'mediadelete' => false,
            'mediaedit' => false,
            'mediatype' => 2,
            'mediaborder' => false,
            'mediabordercolour' => '',
            'mediaext' => 'png',
            'mediadelay' => false,
            'mediaextra' => array()),
          'unanswered' => false,
          'listsize' => 6,
          'subanswers' => 2,
          'matchingoptions' => 6,
          'matchingoption' => array(
            array (
              'selected' => true,
              'value' => 1,
              'option' => 'A. Paris'
            ),
            array (
              'selected' => false,
              'value' => 2,
              'option' => 'B. Eifel Tower'
            ),
            array (
              'selected' => false,
              'value' => 3,
              'option' => 'C. Paris Saint-Germain F.C.'
            ),
            array (
              'selected' => false,
              'value' => 4,
              'option' => 'D. Derby'
            ),
            array (
              'selected' => false,
              'value' => 5,
              'option' => 'E. Intu Centre'
            ),
            array (
              'selected' => false,
              'value' => 6,
              'option' => 'F. Derby County F.C.'
            ),
          )
      ),
      array('answerno' => 1,
          'scenario' => "<div>It's football team is called</div>",
          'display' => true,
          'media' => array('mediaid' => -1,
            'mediafile' => '1516975621.jpg',
            'mediawidth' => 951,
            'mediaheight' => 121,
            'mediaurl' => $cfg_root_path . '/getfile.php?type=media&filename=1516975621.jpg',
            'mediadelete' => false,
            'mediaedit' => false,
            'mediatype' => 2,
            'mediaborder' => false,
            'mediabordercolour' => '',
            'mediaext' => 'jpg',
            'mediadelay' => false,
            'mediaextra' => array()),
          'unanswered' => false,
          'matchingoption' => array(
            array (
              'selected' => false,
              'value' => 1,
              'option' => 'A. Paris'
            ),
            array (
              'selected' => false,
              'value' => 2,
              'option' => 'B. Eifel Tower'
            ),
            array (
              'selected' => true,
              'value' => 3,
              'option' => 'C. Paris Saint-Germain F.C.'
            ),
            array (
              'selected' => false,
              'value' => 4,
              'option' => 'D. Derby'
            ),
            array (
              'selected' => false,
              'value' => 5,
              'option' => 'E. Intu Centre'
            ),
            array (
              'selected' => false,
              'value' => 6,
              'option' => 'F. Derby County F.C.'
            ),
          )
      ),
      array('answerno' => 1,
        'scenario' => "<div>It's most famous landmark is the</div>",
        'display' => false,
        'unanswered' => false,
        'matchingoption' => array(
          array (
            'selected' => false,
            'value' => 1,
            'option' => 'A. Paris'
          ),
          array (
            'selected' => true,
            'value' => 2,
            'option' => 'B. Eifel Tower'
          ),
          array (
            'selected' => false,
            'value' => 3,
            'option' => 'C. Paris Saint-Germain F.C.'
          ),
          array (
            'selected' => false,
            'value' => 4,
            'option' => 'D. Derby'
          ),
          array (
            'selected' => false,
            'value' => 5,
            'option' => 'E. Intu Centre'
          ),
          array (
            'selected' => false,
            'value' => 6,
            'option' => 'F. Derby County F.C.'
          ),
        )
      )
    );
    $data->process_options(6, '1|3|2', '', 1);
    $this->assertTrue($data->extmatchdisplaymedia);
    $this->assertEquals(2, $data->split);
    $this->assertEquals(6, $data->matchoptionsno);
    $this->assertEquals(4, $data->marks);
    $this->assertFalse($data->unanswered);
    $this->assertEquals($matchstem, $data->matchstem);
  }
}