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

use testing\unittest\unittest;

/**
 * Unit tests the date_utils methods.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 * @package tests
 * @group core
 */
class DateUtilsTest extends unittest
{
    /**
     * Tests that dates are converted to the correct localised format.
     *
     * @dataProvider dataRogoToDisplay
     */
    public function testRogoToDisplay(string $format, string $input, string $expected)
    {
        Config::get_instance()->set('cfg_short_datetime_php', $format);
        $this->assertEquals($expected, date_utils::rogoToDisplay($input));
    }

    /**
     * Data used to test the date formatting.
     *
     * @return array
     */
    public function dataRogoToDisplay(): array
    {
        return [
            'default' => ['d/m/Y H:i', '202002191854', '19/02/2020 18:54'],
            'localisation 1' => ['m/d/Y H:i', '202002191854', '02/19/2020 18:54'],
        ];
    }

    /**
     * Test getting timestamp from time
     *
     * @param string $timezone
     * @param int $hour
     * @param int $minute
     * @group date
     * @dataProvider dataGetTimestampFromTime
     */
    public function testGetTimestampFromTime(string $timezone, int $hour, int $minute): void
    {
        $tz = new DateTimeZone($timezone);
        // The date right now for the timezone.
        $now = new DateTime('now', $tz);

        $result = date_utils::getTimestampFromTime($hour, $minute, $tz);
        // Convert the timestamp to a DateTime using the server timezone.
        $resultdate = new DateTime(date('Y-m-d H:i:s', $result));
        $resultdate->setTimezone($tz);

        // Assert that the local day is the same and that the hour and minutes are correct.
        $this->assertEquals($now->format('Y-m-d'), $resultdate->format('Y-m-d'));
        $this->assertEquals($hour, $resultdate->format('G'));
        $this->assertEquals($minute, $resultdate->format('i'));
    }

    /**
     * Data used to test that timestamps are calculated correctly.
     *
     * @return array
     */
    public function dataGetTimestampFromTime(): array
    {
        return [
            'london-early' => ['Europe/London', 1, 30],
            'london-noon' => ['Europe/London', 12, 0],
            'london-late' => ['Europe/London', 23, 5],
            'china-early' => ['Asia/Shanghai', 0, 1],
            'china-noon' => ['Asia/Shanghai', 12, 0],
            'china-late' => ['Asia/Shanghai', 23, 29],
            'alaska-early' => ['America/Anchorage', 1, 15],
            'alaska-noon' => ['America/Anchorage', 12, 0],
            'alaska-late' => ['America/Anchorage', 23, 59],
        ];
    }
}
