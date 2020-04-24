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
 * Test dataUtils class
 *
 * @author Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 * @package tests
 */
class DateUtilsTest extends unittest
{
    /**
     * Test getting timestamp from time
     * @group date
     */
    public function testGetTimestampFromTime(): void
    {
        $timezone = new DateTimeZone('Europe/London');
        $min = 30;
        $hr = 1;
        $tmp_datetime = new DateTime(date('Y-m-d') . $hr . ':' . $min . ':00', $timezone);
        $expected = $tmp_datetime->getTimestamp();
        $this->assertEquals($expected, date_utils::getTimestampFromTime($hr, $min, $timezone));
    }
}
