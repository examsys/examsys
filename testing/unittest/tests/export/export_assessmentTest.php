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
 * Test export csv assessment class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class export_assessmentTestTest extends unittestdatabase {
  /**
   * Get init data set from yml
   * @return dataset
   */
  public function getDataSet() {
    return new YamlDataSet($this->get_base_fixture_directory() . "export" . DIRECTORY_SEPARATOR . "export.yml");
  }

  /**
   * Get expected data set from yml
   * @param string $name fixture file name
   * @return dataset
   */
  public function get_expected_data_set($name) {
    return new YamlDataSet($this->get_base_fixture_directory() . "export" . DIRECTORY_SEPARATOR . $name . ".yml");
  }

  /**
   * Test assessment dynamic header
   * @group export
   */
  public function test_create_dymanic_header() {
    $properties = PaperProperties::get_paper_properties_by_id(1, $this->db, '');
    $paper_buffer = $properties->get_paper_questions();
    $exclusions = new Exclusion(1, $this->db);
    $exclusions->load();
    $file = "test.csv";
    $csvhandler = new \csv\csv_handler($file);
    $export = new \export\export_assessment($csvhandler);
    $export->create_dynamic_header($paper_buffer, $exclusions);
    $expected = array('Q1i', 'Q1ii', 'Q1iii',
      'Q2A', 'Q2B', 'Q2C',
      'Q3A', 'Q3B', 'Q3C', 'Q3D', 'Q3E', 'Q3F', 'Q3G',
      'Q4',
      'Q5A',
      'Q6:user', 'Q6:correct', 'Q6:variables',
      'Q7:user', 'Q7:correct', 'Q7:variables',
      'Q8',
      'Q9A',
      'Q10A', 'Q10B', 'Q10C', 'Q10D',
      'Q11',
      'Q12',
      'Q13A', 'Q13B', 'Q13C', 'Q13D',
      'Q14A',
      'Q15A', 'Q15B', 'Q15C', 'Q15D', 'Q15E', 'Q15F',
      'Q16');
    $this->assertEquals($expected, $export->dynamic_headers);
  }

  /**
   * Test assessment correct answer
   * @group export
   */
  public function test_create_correct_answer() {
    $properties = PaperProperties::get_paper_properties_by_id(1, $this->db, '');
    $paper_buffer = $properties->get_paper_questions();
    $exclusions = new Exclusion(1, $this->db);
    $exclusions->load();
    $file = "test.csv";
    $csvhandler = new \csv\csv_handler($file);
    $export = new \export\export_assessment($csvhandler);
    $language = "en";
    $string['correctanswers'] = 'Correct Answers: ';
    // Text export.
    $mode = "text";
    $expected = array(0 => array($string['correctanswers'],
      '',
      '',
      '',
      '',
      '',
      '',
      '',
      'Paris',
      'Paris Saint-Germain F.C.',
      'Eifel Tower',
      't',
      't',
      'f',
      'N/A',
      '2nd',
      '1st',
      'N/A',
      'N/A',
      'N/A',
      'N/A',
      '',
      '1',
      '',
      '$A*$B',
      '',
      '',
      '$A*$B',
      '',
      '413,60,268,65,207,106,333,107,395,104,413,60',
      '',
      'beetle3.png',
      'earwig3.png',
      'spider',
      'plants',
      'very likely',
      '',
      'Steering wheel',
      'Spare wheel',
      '',
      '',
      'England',
      '.DOC',
      '.XLS',
      '.PPT',
      '.MDB',
      '.PUB',
      '.DAT',
      ''
      ));
    $this->assertEquals($expected, $export->create_correct_answer($paper_buffer, $exclusions, $mode, $string, $language));
    // Numeric export.
    $mode = "numeric";
    $expected = array(0 => array($string['correctanswers'],
      '',
      '',
      '',
      '',
      '',
      '',
      '',
      '1',
      '3',
      '2',
      't',
      't',
      'f',
      ',N/A,2,1,N/A,N/A,N/A,N/A',
      'f',
      '1',
      '',
      '$A*$B',
      '',
      '',
      '$A*$B',
      '',
      '413,60,268,65,207,106,333,107,395,104,413,60',
      '',
      '1',
      '2',
      '4',
      '',
      5,
      '',
      ',y,y,n,n',
      'England',
      '3',
      '4',
      '2',
      '5',
      '1',
      '6',
      'NULL'
    ));
    $this->assertEquals($expected, $export->create_correct_answer($paper_buffer, $exclusions, $mode, $string, $language));
  }

  /**
   * Test assessment create data
   * @group export
   */
  public function test_create_data() {
    $properties = PaperProperties::get_paper_properties_by_id(1, $this->db, '');
    $paper_buffer = $properties->get_paper_questions();
    $course = "TEST";
    $startdate = "2018-01-01 00:00:00";
    $enddate = "2018-02-01 11:00:00";
    $studentonly = true;
    $demo = false;
    $modules = '';
    $percentile = 100;
    $students = $properties->get_user_list($startdate, $enddate, $percentile, $studentonly, $modules);
    $student_list = implode(',', $students);
    $log_array = $properties->get_paper_assessment_data($course, $startdate, $enddate, $student_list, $studentonly, $demo);
    $exclusions = new Exclusion(1, $this->db);
    $exclusions->load();
    $file = "test.csv";
    $csvhandler = new \csv\csv_handler($file);
    $export = new \export\export_assessment($csvhandler);
    $language = "en";
    $string['error_random'] = 'error';
    // Text export.
    $mode = "text";
    $expected = array(1 => array("Other", "Mx", "student", "test", "1234567890", "TEST", 1, "2018-01-01 09:00:00",
      'Paris',
      'Paris Saint-Germain F.C.',
      'Eifel Tower',
      't',
      't',
      'f',
      'N/A',
      '2nd',
      '1st',
      'N/A',
      'N/A',
      'N/A',
      'N/A',
      'True',
      'f',
      '36',
      36,
      '6,6',
      '360',
      360,
      '36,10',
      '202,114,219,107,233,97,249,81,254,71,264,68,299,66,420,75,412,96,404,108,386,113,222,112,202,114,0',
      '330x995',
      'beetle3.png',
      'earwig3.png',
      'spider',
      'plants',
      'very unlikely',
      'test mathjax $$\theta$$',
      'Steering wheel',
      'Spare wheel',
      '',
      '',
      'England',
      '.DOC',
      '.XLS',
      '.PPT',
      '.MDB',
      '.PUB',
      '.DAT',
      ''));
    $this->assertEquals($expected, $export->create_data($log_array, $paper_buffer, $exclusions, $mode, $string, $language));
    // Numeric export.
    $mode = "numeric";
    $expected = array(1 => array("Other", "Mx", "student", "test", "1234567890", "TEST", 1, "2018-01-01 09:00:00",
      '1',
      '3',
      '2',
      't',
      't',
      'f',
      'N/A',
      '2',
      '1',
      'N/A',
      'N/A',
      'N/A',
      'N/A',
      '1',
      'f',
      '36',
      36,
      '6,6',
      '360',
      360,
      '36,10',
      '202,114,219,107,233,97,249,81,254,71,264,68,299,66,420,75,412,96,404,108,386,113,222,112,202,114,0',
      '330x995',
      '1',
      '2',
      '4',
      '',
      '1',
      'test mathjax $$\theta$$',
      'y',
      'y',
      'n',
      'n',
      'England',
      '3',
      '4',
      '2',
      '5',
      '1',
      '6',
      '3'));
    $this->assertEquals($expected, $export->create_data($log_array, $paper_buffer, $exclusions, $mode, $string, $language));
  }
}