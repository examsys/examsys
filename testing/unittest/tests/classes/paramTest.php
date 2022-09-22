<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

namespace testing\unittest;

/**
 * Test param class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class paramtest extends UnitTest
{
    /**
     * Test clean INT with range
     * @group param
     */
    public function test_clean_int_with_range()
    {
        $ok = \param::clean(1, \param::INT, array('default' => null, 'min_range' => 0, 'max_range' => 8));
        $bad = \param::clean(9, \param::INT, array('default' => null, 'min_range' => 0, 'max_range' => 8));
        $this->assertEquals(1, $ok);
        $this->assertEquals(null, $bad);
    }

    /**
     * Test clean INT no options
     * @group param
     */
    public function test_clean_int_not_options()
    {
        $bad = \param::clean(9, \param::INT);
        $this->assertEquals(9, $bad);
    }

    /**
     * Test clean REGEXP
     * @group param
     */
    public function test_clean_regexp()
    {
        $regexp = '#^[A-Z][0-9]$#';
        $ok = \param::clean('A1', \param::REGEXP, array('default' => null, 'regexp' => $regexp));
        $bad = \param::clean('A', \param::REGEXP, array('default' => null, 'regexp' => $regexp));
        $this->assertEquals('A1', $ok);
        $this->assertEquals(null, $bad);
    }

    /**
     * Test unicode/non-breaking spaces correctly switched to regular spaces
     * @group param
     */
    public function test_clean_bad_chars()
    {
        $in = "1\u{A0}" # non-breaking space
        . "2\u{2007}" # figure space
        . "3\u{202F}" # narrow non-breaking space
        . "4\u{2060}" # word joiner
        . "5";
        $cleaned = \param::cleanBadChars($in);
        $this->assertEquals("1 2 3 4 5", $cleaned);
    }
}
