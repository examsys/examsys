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
 * Stores information about the commands needed to run phpunit.
 *
 * Based on /testing/behat/classes/help.php by Neill Magill <neill.magill@nottingham.ac.uk>
 *
 * @author Dr Joseph baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @category unittest
 */
class help
{
    /** The URL to documentation for phpunit in ExamSys. */
    public const DOCUMENTATION = 'https://rogo-eassessment-docs.atlassian.net/wiki/display/ROGO/Unit+testing';

    /**
     * The location of the phpunit execution script.
     *
     * @return string
     */
    public static function get_phpunit_location()
    {
        return 'vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'phpunit';
    }

    /**
     * The command a user needs to type to run phpunit tests from the root of ExamSys.
     *
     * @return string
     */
    public static function get_test_run_command()
    {
        return self::get_phpunit_location() . ' -c ' . environment::get_xml_location();
    }

    /**
     * Returns a help message describing how to run the phpunit test suit.
     *
     * @return string
     */
    public static function run_help()
    {
        $message = PHP_EOL . 'Phpunit is now installed and can be run from the root ExamSys directory using:'
            . PHP_EOL . self::get_test_run_command();
        return $message;
    }

    /**
     * Get a generic error message that states where to get help.
     *
     * @return string
     */
    public static function error()
    {
        $message = PHP_EOL . 'For details about Phpunit testing in ExamSys visit: ' . PHP_EOL . self::DOCUMENTATION;
        return $message;
    }

    /**
     * Help for the phpunit init script.
     *
     * @return string
     */
    public static function init_help()
    {
        $message = 'ExamSys Phpunit initialisation script options'
            . PHP_EOL . PHP_EOL . "-h, --help \tDisplay help"
            . PHP_EOL . "--clean \tForce a database install"
            . PHP_EOL . "--update \tUpdate the composer dependancies.";
        return $message;
    }
}
