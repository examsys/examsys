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
 * Test question data storage class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class questiondatatest extends unittestdatabase{

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
    * Test question data setup
    * @group question
    */
  public function test_setup_question_data() {
    // General test here. Any question type specific tests should go in the relevant question type test class.
    $data = questiondata::get_datastore('mcq');
    $screen_pre_submitted = 1;
    $q_displayed = 0;
    $string = array();
    $question = array(
        'theme' => 'question test theme',
        'scenario' => 'question test scenario',
        'notes' => 'question test notes',
        'leadin' => 'question test leadin',
        'assigned_number' => 1,
        'q_type' => 'mcq',
        'q_id' => 4,
        'settings' => array(),
        'q_media' => '1517406311.png',
        'q_media_width' => '480',
        'q_media_height' => '105',
        'score_method' => 'Mark per Option',
        'display_method' => 'vertical',
        'options' => array(
            0 => array(
              'correct' => '1',
              'option_text' => '<div>True</div>',
              'o_media' => '',
              'o_media_width' => '0',
              'o_media_height' => '0',
              'marks_correct' => 2,
              'marks_incorrect' => -1,
              'marks_partial' =>  0
            ),
            1 => array(
              'correct' => '1',
              'option_text' => '<div>False</div>',
              'o_media' => '1517409282.jpg',
              'o_media_width' => '951',
              'o_media_height' => '121',
              'marks_correct' => 2,
              'marks_incorrect' => -1,
              'marks_partial' => 0,
              'mediaext' => 'jpg',
              'mediadelay' => false,
              'mediaextra' => array(),
            ),
            2 => array(
              'correct' => '1',
              'option_text' => '<div>Maybe</div>',
              'o_media' => '1517411342.png',
              'o_media_width' => '693',
              'o_media_height' => '149',
              'marks_correct' => 2,
              'marks_incorrect' => -1,
              'marks_partial' => 0,
              'mediaext' => 'png',
              'mediadelay' => false,
              'mediaextra' => array(),
            ),
            3 => array(
              'correct' => '1',
              'option_text' => '<div>Depends on which country you\'re in</div>',
              'o_media' => '',
              'o_media_width' => '0',
              'o_media_height' => '0',
              'marks_correct' => 2,
              'marks_incorrect' => -1,
              'marks_partial' => 0
            ),
        ),
        'option_order' => array(
            0 => 0,
            1 => 1,
            2 => 2,
            3 => 3
        ),
    );
    $pid = 1;
    $current_screen = 1;
    $question_no = 0;
    $user_answers[1][4] = 1;
    $data->setup_question_data($screen_pre_submitted, $q_displayed, $string, $question, $pid, $current_screen, $question_no, $user_answers);
    $this->assertEquals('paper test prologue', $data->prologue);
    $this->assertTrue($data->displayprologue);
    $this->assertEquals(4, $data->optionnumber);
    $this->assertTrue($data->negativemarking);
    $this->assertEquals('question test theme', $data->theme);
    $this->assertTrue($data->displaytheme);
    $this->assertEquals('0', $data->papertype);
    $this->assertEquals(1, $data->assignednumber);
    $this->assertEquals('question test scenario', $data->scenario);
    $this->assertEquals('question test notes', $data->notes);
    $this->assertEquals('1517406311.png', $data->qmedia);
    $this->assertEquals('480', $data->qmediawidth);
    $this->assertEquals('105', $data->qmediaheight);
    $this->assertEquals('question test leadin', $data->leadin);
    $this->assertEquals($question, $data->question);
    $this->assertEquals($user_answers, $data->useranswers);
    $this->assertEquals(array(), $data->settings);
    $this->assertEquals($question_no, $data->questionno);
    $this->assertEquals('vertical', $data->displaymethod);
    $this->assertEquals('Mark per Option', $data->scoremethod);
    $this->assertEquals(2, $data->marks);
    $cfg_root_path = $this->config->get('cfg_root_path');
    $optionsdata = array(
      1 => array(
        'correct' => '1',
        'optiontext' => '<div>True</div>',
        'omedia' => '',
        'markscorrect' => 2,
        'marksincorrect' => -1,
        'optionno' => 'q1_1',
        'position' => 1,
        'optionmedia' => array(
            'mediaid' => -1,
            'mediafile' => '',
            'mediawidth' => '0',
            'mediaheight' => '0',
            'mediaurl' => $cfg_root_path . '/getfile.php?type=media&filename=',
            'mediadelete' => false,
            'mediaedit' => false,
            'mediatype' => 1,
            'mediaborder' => true,
            'mediabordercolour' => '',
            'mediaext' => '',
            'mediadelay' => false,
            'mediaextra' => array(),
        ),
        'selected' => true,
        'optiontextdisplay' => true,
        'displayoptionmedia' => false,
        'inact' => false,
      ),
      2 => array(
        'correct' => '1',
        'optiontext' => '<div>False</div>',
        'omedia' => '1517409282.jpg',
        'markscorrect' => 2,
        'marksincorrect' => -1,
        'optionno' => 'q1_2',
        'position' => 2,
        'optionmedia' => array(
            'mediaid' => -1,
            'mediafile' => '1517409282.jpg',
            'mediawidth' => '951',
            'mediaheight' => '121',
            'mediaurl' => $cfg_root_path . '/getfile.php?type=media&filename=1517409282.jpg',
            'mediadelete' => false,
            'mediaedit' => false,
            'mediatype' => 2,
            'mediaborder' => false,
            'mediabordercolour' => '',
            'mediaext' => 'jpg',
            'mediadelay' => false,
            'mediaextra' => array(),
        ),
        'selected' => false,
        'optiontextdisplay' => true,
        'displayoptionmedia' => true,
        'inact' => false,
      ),
      3 => array(
        'correct' => '1',
        'optiontext' => '<div>Maybe</div>',
        'omedia' => '1517411342.png',
        'markscorrect' => 2,
        'marksincorrect' => -1,
        'optionno' => 'q1_3',
        'position' => 3,
        'optionmedia' => array(
            'mediaid' => -1,
            'mediafile' => '1517411342.png',
            'mediawidth' => '693',
            'mediaheight' => '149',
            'mediaurl' => $cfg_root_path . '/getfile.php?type=media&filename=1517411342.png',
            'mediadelete' => false,
            'mediaedit' => false,
            'mediatype' => 2,
            'mediaborder' => false,
            'mediabordercolour' => '',
            'mediaext' => 'png',
            'mediadelay' => false,
            'mediaextra' => array(),
        ),
        'selected' => false,
        'optiontextdisplay' => true,
        'displayoptionmedia' => true,
        'inact' => false,
      ),
      4 => array(
        'correct' => '1',
        'optiontext' => '<div>Depends on which country you\'re in</div>',
        'omedia' => '',
        'markscorrect' => 2,
        'marksincorrect' => -1,
        'optionno' => 'q1_4',
        'position' => 4,
        'optionmedia' => array(
            'mediaid' => -1,
            'mediafile' => '',
            'mediawidth' => '0',
            'mediaheight' => '0',
            'mediaurl' => $cfg_root_path . '/getfile.php?type=media&filename=',
            'mediadelete' => false,
            'mediaedit' => false,
            'mediatype' => 1,
            'mediaborder' => true,
            'mediabordercolour' => '',
            'mediaext' => '',
            'mediadelay' => false,
            'mediaextra' => array(),
        ),
        'selected' => false,
        'optiontextdisplay' => true,
        'displayoptionmedia' => false,
        'inact' => false,
      ),
    );
    $this->assertEquals($optionsdata, $data->options);
    $this->assertEquals(4, $data->partid);
    $this->assertEquals('0,1,2,3', $data->optionorder);
    $this->assertEquals('', $data->bonus);
    $this->assertEquals(2, $data->finalmarks);
  }

  /**
    * Test question attribute getter
    * @group question
    */
  public function test_get() {
    $data = questiondata::get_datastore('area');
    $this->assertNull($data->notes);
    $data->notes = 'notes';
    $this->assertEquals('notes', $data->notes);
  }

  /**
    * Test question option setter/getter
    * @group question
    */
  public function test_get_set_opt() {
    $data = questiondata::get_datastore('area');
    $option2 = array('test' => 2, 'test2' => array(2,1));
    $data->set_opt(2, $option2);
    $option1 = array('test' => 1, 'test2' => array(1,2));
    $data->set_opt(1, $option1);
    $this->assertEquals($option2, $data->get_opt(2));
  }

  /**
    * Test question media data setup
    * @group question
    */
  public function test_set_media() {
    $data = questiondata::get_datastore('area');
    // Test question media.
    $filename = 'test.png';
    $width = 100;
    $height = 200;
    $border_color = '#000000';
    $imageid = 1;
    $locked = true;
    $mediadirectory = rogo_directory::get_directory('media');
    $url = $mediadirectory->url($filename);
    $data->set_media($filename, $width, $height, $border_color, false, $imageid, $locked);
    $this->assertEquals($imageid ,$data->mediaid);
    $this->assertEquals($filename, $data->mediafile);
    $this->assertEquals($width, $data->mediawidth);
    $this->assertEquals($height, $data->mediaheight) ;
    $this->assertEquals($url, $data->mediaurl);
    $this->assertFalse($data->mediadelete);
    $this->assertFalse($data->mediaedit);
    $this->assertEquals(2, $data->mediatype);
    $this->assertTrue($data->mediaborder);
    $this->assertEquals($border_color, $data->mediabordercolour);
    // Test option media.
    $part_id = 2;
    $data->set_media($filename, $width, $height, $border_color, false, $imageid, $locked, $part_id);
    $option['optionmedia'] = array(
          'mediaid' => $imageid,
          'mediafile' => $filename,
          'mediawidth'=> $width,
          'mediaheight'=> $height,
          'mediaurl' => $url,
          'mediadelete' => false,
          'mediaedit' => false,
          'mediatype' => 2,
          'mediaborder' => true,
          'mediabordercolour' => $border_color,
          'mediaext' => 'png',
          'mediadelay' => false,
          'mediaextra' => array(),
        );
    $this->assertEquals($option, $data->get_opt($part_id));
  }

  /**
   * Test retrieval of invalid datastore
   * @group question
   */
  public function test_get_datastore() {
    $data = questiondata::get_datastore('invalid');
    $this->assertEquals('undefined', $data->questiontype);
  }
}