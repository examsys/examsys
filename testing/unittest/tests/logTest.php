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
 * Test assessment class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class logtest extends unittestdatabase {
  /**
   * Get init data set from yml
   * @return dataset
   */
  public function getDataSet() {
      return new YamlDataSet($this->get_base_fixture_directory() . "logTest" . DIRECTORY_SEPARATOR . "log.yml");
  }

  /**
   * Test retrieving previous answers - with restart
   * @group log
   */
  public function test_get_previous_answers() {
    $papertype = '0';
    $log = \log::get_paperlog($papertype);
    $metadataID = 2;
    $do_restart = true;
    $current_screen = 1;
    $previous = array('used_questions' => array(2 => 2),
        'user_answers' => array(2 => array(2 => '1')),
        'user_dismiss' => array(2 => array(2 => '0000')),
        'user_order' => array(2 => array(2 => '0,1,2,3')),
        'previous_duration' => 5,
        'screen_pre_submitted' => 1,
        'current_screen' => 2);
    $this->assertEquals($previous, $log->get_previous_answers($metadataID, $do_restart, $current_screen));
  }

  /**
   * Test retrieving previous answers from log late
   * @group log
   */
  public function test_get_previous_answers_late() {
    $papertype = '2';
    $log = \log::get_paperlog($papertype);
    $metadataID = 1;
    $do_restart = false;
    $current_screen = 1;
    $previous = array('used_questions' => array(2 => 2),
        'user_answers' => array(1 => array(2 => '2')),
        'user_dismiss' => array(1 => array(2 => '1000')),
        'user_order' => array(1 => array(2 => '0,1,2,3')),
        'previous_duration' => 10,
        'screen_pre_submitted' => 1,
        'current_screen' => 1);
    $this->assertEquals($previous, $log->get_previous_answers($metadataID, $do_restart, $current_screen, true));
  }

  /**
   * Test retrieving previous answers from log late - multiple questions
   * @group log
   */
  public function test_get_previous_answers_late_complex() {
    $papertype = '2';
    $log = \log::get_paperlog($papertype);
    $metadataID = 8;
    $do_restart = false;
    $current_screen = 1;
    $previous = array('used_questions' => array(3 => 3, 2 => 2),
      'user_answers' => array(1 => array(2 => '1'), 2 => array(3 => '2')),
      'user_dismiss' => array(1 => array(2 => '0000'), 2 => array(3 => '1000')),
      'user_order' => array(1 => array(2 => '0,1,3,2'), 2 => array(3 => '0,1,2,3')),
      'previous_duration' => 5,
      'screen_pre_submitted' => 1,
      'current_screen' => 1);
    $this->assertEquals($previous, $log->get_previous_answers($metadataID, $do_restart, $current_screen, true));
  }

  /**
   * Test retrieving users in log for survey (where it is not supported)
   * @group log
   */
  public function test_get_log_users_survey() {
    $papertype = '3';
    $log = \log::get_paperlog($papertype);
    $this->assertEquals(array(), $log->get_log_users(1, '', '', array(), false));
  }

  /**
   * Test retrieving users in log for summative
   * @group log
   */
  public function test_get_log_users_summative() {
    $papertype = '2';
    $log = \log::get_paperlog($papertype);
    $expected[0]['userid'] = 2;
    $expected[0]['totalmark'] = 1.0;
    $expected[1]['userid'] = 1;
    $expected[1]['totalmark'] = 3.0;
    // All users.
    $this->assertEquals($expected, $log->get_log_users(2, '2018-01-01 00:00:00', '2018-01-05 01:00:00', array(1, 2), false));
    // Students only.
    $expected = array();
    $expected[0]['userid'] = 1;
    $expected[0]['totalmark'] = 3.0;
    $this->assertEquals($expected, $log->get_log_users(2, '2018-01-01 00:00:00', '2018-01-05 01:00:00', array(1, 2), true));
    // All users in timeframe
    $this->assertEquals($expected, $log->get_log_users(2, '2018-01-01 00:00:00', '2018-01-01 01:00:00', array(1, 2), false));
  }

  /**
   * Test retrieving users in log for formative
   * @group log
   */
  public function test_get_log_users_formative() {
    $papertype = '0';
    $log = \log::get_paperlog($papertype);
    $expected[0]['userid'] = 2;
    $expected[0]['totalmark'] = 0.0;
    $expected[1]['userid'] = 1;
    $expected[1]['totalmark'] = 1.0;
    $expected[2]['userid'] = 2;
    $expected[2]['totalmark'] = 2.0;
    $expected[3]['userid'] = 1;
    $expected[3]['totalmark'] = 4.0;
    // Progressive paper migrated to formative paper.
    // All users.
    $this->assertEquals($expected, $log->get_log_users(3, '2016-01-01 00:00:00', '2018-01-05 01:00:00', array(1, 2), false));
    // Students only.
    $expected = array();
    $expected[0]['userid'] = 1;
    $expected[0]['totalmark'] = 1.0;
    $expected[1]['userid'] = 1;
    $expected[1]['totalmark'] = 4.0;
    $this->assertEquals($expected, $log->get_log_users(3, '2016-01-01 00:00:00', '2018-01-05 01:00:00', array(1, 2), true));
    // All users in timeframe
    $this->assertEquals($expected, $log->get_log_users(3, '2017-01-01 00:00:00', '2018-01-05 01:00:00', array(1, 2), false));
  }

  /**
   * Test retrieving assessment data in log for survey (where it is not supported)
   * @group log
   */
  public function test_get_assessment_data_survey() {
    $papertype = '3';
    $log = \log::get_paperlog($papertype);
    $this->assertEquals(array(), $log->get_assessment_data(1, '', '', ''));
  }

  /**
   * Test retrieving assessment data in log for summative
   * @group log
   */
  public function test_get_assessment_data_summative() {
    $papertype = '2';
    $log = \log::get_paperlog($papertype);
    // Student only.
    $expected[0]['username'] = 'student1';
    $expected[0]['uID'] = 1;
    $expected[0]['title'] = 'Mr';
    $expected[0]['surname'] = 'Baxter';
    $expected[0]['first_names'] = "Joseph";
    $expected[0]['grade'] = 'TEST';
    $expected[0]['gender'] = "Male";
    $expected[0]['year'] = 2;
    $expected[0]['started'] = "2018-01-01 00:00:00";
    $expected[0]['question_ID'] = 2;
    $expected[0]['user_answer'] = '1';
    $expected[0]['screen'] = 1;
    $expected[1]['username'] = 'student1';
    $expected[1]['uID'] = 1;
    $expected[1]['title'] = 'Mr';
    $expected[1]['surname'] = 'Baxter';
    $expected[1]['first_names'] = "Joseph";
    $expected[1]['grade'] = 'TEST';
    $expected[1]['gender'] = "Male";
    $expected[1]['year'] = 2;
    $expected[1]['started'] = "2018-01-01 00:00:00";
    $expected[1]['question_ID'] = 3;
    $expected[1]['user_answer'] = '1';
    $expected[1]['screen'] = 2;
    $this->assertEquals($expected, $log->get_assessment_data(2, '2018-01-01 00:00:00', '2018-01-05 01:00:00', "1, 2", '%', true));
    // All users in timeframe.
    $this->assertEquals($expected, $log->get_assessment_data(2, '2018-01-01 00:00:00', '2018-01-01 01:00:00', "1, 2"));
    // All users in course.
    $this->assertEquals($expected, $log->get_assessment_data(2, '2018-01-01 00:00:00', '2018-01-05 01:00:00', "1, 2", 'TEST', false));
    // All users.
    $expected[2]['username'] = 'staff';
    $expected[2]['uID'] = 2;
    $expected[2]['title'] = 'Dr';
    $expected[2]['surname'] = 'Baxter';
    $expected[2]['first_names'] = "Josephine";
    $expected[2]['grade'] = 'University Staff';
    $expected[2]['gender'] = "Female";
    $expected[2]['year'] = 1;
    $expected[2]['started'] = "2018-01-04 00:00:00";
    $expected[2]['question_ID'] = 2;
    $expected[2]['user_answer'] = '1';
    $expected[2]['screen'] = 1;
    $expected[3]['username'] = 'staff';
    $expected[3]['uID'] = 2;
    $expected[3]['title'] = 'Dr';
    $expected[3]['surname'] = 'Baxter';
    $expected[3]['first_names'] = "Josephine";
    $expected[3]['grade'] = 'University Staff';
    $expected[3]['gender'] = "Female";
    $expected[3]['year'] = 1;
    $expected[3]['started'] = "2018-01-04 00:00:00";
    $expected[3]['question_ID'] = 3;
    $expected[3]['user_answer'] = '0';
    $expected[3]['screen'] = 2;
    $this->assertEquals($expected, $log->get_assessment_data(2, '2018-01-01 00:00:00', '2018-01-05 01:00:00', "1, 2"));
  }

  /**
   * Test retrieving assessment data in log for formative
   * @group log
   */
  public function test_get_assessment_data_formative() {
    $papertype = '0';
    $log = \log::get_paperlog($papertype);
    // Student only.
    $expected[0]['username'] = 'student1';
    $expected[0]['uID'] = 1;
    $expected[0]['title'] = 'Mr';
    $expected[0]['surname'] = 'Baxter';
    $expected[0]['first_names'] = "Joseph";
    $expected[0]['grade'] = 'TEST';
    $expected[0]['gender'] = "Male";
    $expected[0]['year'] = 2;
    $expected[0]['started'] = "2017-01-01 00:00:00";
    $expected[0]['question_ID'] = 2;
    $expected[0]['user_answer'] = '1';
    $expected[0]['screen'] = 1;
    $expected[1]['username'] = 'student1';
    $expected[1]['uID'] = 1;
    $expected[1]['title'] = 'Mr';
    $expected[1]['surname'] = 'Baxter';
    $expected[1]['first_names'] = "Joseph";
    $expected[1]['grade'] = 'TEST';
    $expected[1]['gender'] = "Male";
    $expected[1]['year'] = 2;
    $expected[1]['started'] = "2018-01-01 00:00:00";
    $expected[1]['question_ID'] = 2;
    $expected[1]['user_answer'] = '1';
    $expected[1]['screen'] = 1;
    $this->assertEquals($expected, $log->get_assessment_data(3, '2016-01-01 00:00:00', '2018-01-05 01:00:00', "1, 2", '%', true));
    // All users in timeframe.
    $this->assertEquals($expected, $log->get_assessment_data(3, '2017-01-01 00:00:00', '2018-01-05 01:00:00', "1, 2"));
    // All users in course.
    $this->assertEquals($expected, $log->get_assessment_data(3, '2016-01-01 00:00:00', '2018-01-05 01:00:00', "1, 2", 'TEST', false));
    // All users.
    $expected[2]['username'] = 'staff';
    $expected[2]['uID'] = 2;
    $expected[2]['title'] = 'Dr';
    $expected[2]['surname'] = 'Baxter';
    $expected[2]['first_names'] = "Josephine";
    $expected[2]['grade'] = 'University Staff';
    $expected[2]['gender'] = "Female";
    $expected[2]['year'] = 1;
    $expected[2]['started'] = "2016-01-01 00:00:00";
    $expected[2]['question_ID'] = 2;
    $expected[2]['user_answer'] = '4';
    $expected[2]['screen'] = 1;
    $expected[3]['username'] = 'staff';
    $expected[3]['uID'] = 2;
    $expected[3]['title'] = 'Dr';
    $expected[3]['surname'] = 'Baxter';
    $expected[3]['first_names'] = "Josephine";
    $expected[3]['grade'] = 'University Staff';
    $expected[3]['gender'] = "Female";
    $expected[3]['year'] = 1;
    $expected[3]['started'] = "2016-01-01 00:00:00";
    $expected[3]['question_ID'] = 2;
    $expected[3]['user_answer'] = '3';
    $expected[3]['screen'] = 1;
    $this->assertEquals($expected, $log->get_assessment_data(3, '2016-01-01 00:00:00', '2018-01-05 01:00:00', "1, 2"));
  }
}
