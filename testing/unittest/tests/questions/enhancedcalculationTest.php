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

/**
 * Test enhanced calculation question functionality
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 * @group questions
 */
class enhancedcalculationTest extends \testing\unittest\UnitTest {
  /**
   * Test a range of valid answers, that should not be filtered.
   */
  public function test_process_user_answer_valid() {
    require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/plugins/questions/enhancedcalc/enhancedcalc.class.php';
    $emptyarray = array();
    $post1 = array(
      'uans' => '10',
      'uansunit' => 'm/s',
    );
    $return1 = EnhancedCalc::process_user_answer($post1, $emptyarray);
    $this->assertEquals(json_encode($post1), $return1);

    $post2 = array(
      'uans' => '1.69 m/s',
    );
    $return2 = EnhancedCalc::process_user_answer($post2, $emptyarray);
    $this->assertEquals(json_encode($post2), $return2);

    $post3 = array(
      'uans' => '0.69',
      'uansunit' => 'μm',
    );
    $return3 = EnhancedCalc::process_user_answer($post3, $emptyarray);
    $this->assertEquals(json_encode($post3), $return3);

    $post4 = array(
      'uans' => '69μm',
    );
    $return4 = EnhancedCalc::process_user_answer($post4, $emptyarray);
    $this->assertEquals(json_encode($post4), $return4);

    $post5 = array(
      'uans' => '-0.69',
      'uansunit' => 'kg-m/s',
    );
    $return5 = EnhancedCalc::process_user_answer($post5, $emptyarray);
    $this->assertEquals(json_encode($post5), $return5);

    $post6 = array(
      'uans' => '-42',
      'uansunit' => 'mol',
    );
    $return6 = EnhancedCalc::process_user_answer($post6, $emptyarray);
    $this->assertEquals(json_encode($post6), $return6);

    $post7 = array(
      'uans' => '10',
      'uansunit' => 'Pa s',
    );
    $return7 = EnhancedCalc::process_user_answer($post7, $emptyarray);
    $this->assertEquals(json_encode($post7), $return7);

    $post8 = array(
      'uans' => '46658',
      'uansunit' => 'kg/m/s',
    );
    $return8 = EnhancedCalc::process_user_answer($post8, $emptyarray);
    $this->assertEquals(json_encode($post8), $return8);

    $post9 = array(
      'uans' => '-1.10',
      'uansunit' => 'Ω',
    );
    $return9 = EnhancedCalc::process_user_answer($post9, $emptyarray);
    $this->assertEquals(json_encode($post9), $return9);

    // Latex for power
    $post10 = array(
      'uans' => '2',
      'uansunit' => 'm s^2',
    );
    $return10 = EnhancedCalc::process_user_answer($post10, $emptyarray);
    $this->assertEquals(json_encode($post10), $return10);

    $post11 = array(
      'uans' => '31',
      'uansunit' => 'Å',
    );
    $return11 = EnhancedCalc::process_user_answer($post11, $emptyarray);
    $this->assertEquals(json_encode($post11), $return11);

    // Unicode superscript.
    $post11 = array(
      'uans' => '31',
      'uansunit' => 'm⁴',
    );
    $return11 = EnhancedCalc::process_user_answer($post11, $emptyarray);
    $this->assertEquals(json_encode($post11), $return11);
    
    // Latex for subscript
    $post12 = array(
      'uans' => '75231.2134554544234',
      'uansunit' => 'k_4',
    );
    $return12 = EnhancedCalc::process_user_answer($post12, $emptyarray);
    $this->assertEquals(json_encode($post12), $return12);

    // Latex for subscript
    $post13 = array(
      'uans' => '14',
      'uansunit' => '°C',
    );
    $return13 = EnhancedCalc::process_user_answer($post13, $emptyarray);
    $this->assertEquals(json_encode($post13), $return13);
  }
}
