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
 * Test info question class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class infotest extends unittest{

  /**
    * Test question setter
    * @group question
    */
  public function test_set_question() {
    $data = questiondata::get_datastore('info');
    $data->set_question(1, '', '');
    $this->assertFalse($data->displaymedia);
    $this->assertTrue($data->displayleadin);
    $data->qmedia = 'test';
    $data->questionno =  2;
    $data->set_question(1, '', '');
    $this->assertTrue($data->displaymedia);
    // Question number should not be affected by a info block.
    $this->assertEquals(2, $data->questionno);
  }

}