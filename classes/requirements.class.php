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

/**
 * Requirements package
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 */

/**
 * Requirements helper class.
 */
class requirements
{
    /**
     * Check php version meets minimum requirements.
     * @return boolean
     */
    public static function check_php_version()
    {
        $configObject = Config::get_instance();
        $php_min_ver = $configObject->getxml('php', 'min_version');
        $phpversion = phpversion();
        if (version_compare($phpversion, $php_min_ver, '<')) {
            return false;
        }
        return true;
    }

    /**
     * Check required php extensions are enabled.
     * @return array
     */
    public static function check_php_extensions()
    {
        $ext = array();
        $configObject = Config::get_instance();
        $phpModules = get_loaded_extensions();
        $extensions = $configObject->getxml('php', 'extensions');
        foreach ($extensions->extension as $extension) {
            if (!in_array($extension, $phpModules)) {
                 $ext[$extension] = false;
            } else {
                $ext[$extension] = true;
            }
        }
        // Check optional extenstions.
        $optional_extenstions = $configObject->getxml('php', 'optional_extenstions');
        foreach ($optional_extenstions->extension as $opt) {
            if (!in_array($opt, $phpModules)) {
                $ext[$opt] = 'warn';
            } else {
                $ext[$opt] = true;
            }
        }
        return $ext;
    }

    /**
     * Install composer and update libraries to required versions.
     * @return mixed
     */
    public static function composer()
    {
        try {
            ob_start();
            composer_utils::setup(composer_utils::INSTALL_NODEV);
            ob_end_clean();
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return true;
    }

    /**
     * Update NPM libraries to required versions.
     * @return mixed
     */
    public static function npm()
    {
        try {
            ob_start();
            npm_utils::setup(npm_utils::INSTALL_NODEV);
            ob_end_clean();
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return true;
    }

    /**
     * Check db version meets minimum requirements.
     * @param string $host db host
     * @param string $user db user
     * @param string $pass db password
     * @param integer $port db port
     * @param string $database db database
     * @return boolean
     * @throws Exception When the database cannot be connected to.
     */
    public static function check_db($host, $user, $pass, $port = 3306, $database = null)
    {
        $phpModules = get_loaded_extensions();
        if (in_array('mysqli', $phpModules)) {
            @$check = new mysqli($host, $user, $pass, $database, $port);
            if ($check->connect_error != '') {
                throw new Exception($check->connect_error);
            }
            $configObject = Config::get_instance();
            $mysql_min_ver = $configObject->getxml('database', 'mysql', 'min_version');
            $mysql_version = $check->server_version;
            if ($mysql_version < $mysql_min_ver) {
                return false;
            }
            $check->close();
        } else {
            return false;
        }
        return true;
    }

    /**
     * Check for required components - used by cli installers
     * @throws Exception
     */
    public static function check()
    {
        $configObject = Config::get_instance();
        // php.
        if (!self::check_php_version()) {
            $php_min_ver = $configObject->getxml('php', 'min_version');
            throw new Exception('PHP version does not meet minimum requirement - ' . $php_min_ver);
        }
        $phpext = self::check_php_extensions();
        foreach ($phpext as $idx => $val) {
            if (!$val) {
                throw new Exception('PHP extension ' . mb_strtoupper($idx) . ' missing.');
            }
        }
    }
}
