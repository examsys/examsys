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

namespace testing\unittest;

/**
 * Test mathutils class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2019 onwards The University of Nottingham
 * @package tests
 */
class mathutilstest extends UnitTest {
    /**
     * Test percentile function
     * @group math
     */
    public function test_percentile() {
        $data = array(100,50,25,0);
        $test = \MathsUtils::percentile($data, 0.50);
        $this->assertEquals(37.5, $test);
        $test = \MathsUtils::percentile($data, 0.55);
        $this->assertEquals(33.75, $test);
    }

    /**
     * Test percentile function non numeric data
     * @group math
     */
    public function test_percentile_nonnumeric() {
        $data = array(100,50,null,0);
        $test = \MathsUtils::percentile($data, 0.25);
        $this->assertEquals(0.0, $test);
    }

    /**
     * Test percentile function out of range
     * @group math
     */
    public function test_percentile_outofrange() {
        $data = array(100,50,25,0);
        $test = \MathsUtils::percentile($data, 101);
        $this->assertEquals(0.0, $test);
    }

    /**
     * Test percentile function non float percentile
     * @group math
     */
    public function test_percentile_nonfloat() {
        $data = array(100,50,25,0);
        $test = \MathsUtils::percentile($data, 25);
        $this->assertEquals(62.5, $test);
    }
}