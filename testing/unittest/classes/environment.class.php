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

use Config;

/**
 * This class is used to install and update phpunit in ExamSys.
 *
 * Based on /testing/behat/casses/environment.php by Neill Magill <neill.magill@nottingham.ac.uk>
 *
 * @author Dr Joseph baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @category unittest
 */
class environment
{

    /**
     * Get the fully qualified path of the testing/unittest directory.
     *
     * @return string
     */
    protected static function get_basedir()
    {
        return self::get_rogo_basedir() . DIRECTORY_SEPARATOR . 'testing' . DIRECTORY_SEPARATOR . 'unittest';
    }

    /**
     * Get the full path to the phpunit.xml file.
     *
     * @return string
     */
    public static function get_xml_location()
    {
        return self::get_basedir() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'phpunit.xml';
    }

    /**
     * Check if the phpunit database needs refreshing.
     *
     * @return boolean
     */
    public static function upgrade_needed()
    {
        $config = Config::get_instance();
        if (self::rogo_phpunit_version() != $config->getxml('version')) {
            return true;
        }
        return false;
    }

    /**
     * Gets the location of the file that stores the version of code that phpunit is setup to run.
     *
     * @return string
     */
    public static function get_version_location()
    {
        return self::get_basedir() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'version.php';
    }

    /**
     * Writes a file that contains the version number of the ExamSys code.
     *
     * @return void
     */
    public static function save_version()
    {
        $codeversion = Config::get_instance()->getxml('version');
        $file = self::get_version_location();
        if (!file_put_contents($file, $codeversion)) {
            throw new Exception('Could not write version file.');
        }
    }

    /**
     * Get the version of ExamSys that phpunit is initialised for.
     *
     * @return string
     */
    public static function rogo_phpunit_version()
    {
        $file = self::get_version_location();
        if (file_exists($file)) {
            $version = file_get_contents($file);
        } else {
            $version = '';
        }
        return $version;
    }

    /**
     * Returns the directory that ExamSys is installed in.
     *
     * @return string
     */
    protected static function get_rogo_basedir()
    {
        return dirname(dirname(dirname(__DIR__)));
    }
}
