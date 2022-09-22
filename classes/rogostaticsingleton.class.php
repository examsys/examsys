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
 * A Class to be used as a base class for ExamSys Singleton utility classes.
 * Also acts as a static wrapper to dynamic classes to enable unit testing
 * of statistic called code
 *
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class RogoStaticSingleton
{
    /**
     * Create and return the Global instance of parent::$class_name for use in
     * the Local scope.
     */

    public static function get_instance()
    {
        //some objects are global and need parameters these are constructed using
        //a stranded constructor and need parameters passing. if they have not been
        //built and get_instance is call it should return null
        if (isset(static::$dont_construct) and static::$dont_construct == true) {
            if (is_object(static::$inst)) {
                return static::$inst;
            } else {
                return null;
            }
        }

        //normal behaviour create on demand
        if (!is_object(static::$inst)) {
            static::$inst = new static::$class_name();
        }
        return static::$inst;
    }

    /**
     * Sets the Mock instance to return. ONLY used for behat and unit testing.
     *
     * @param object
     */
    public static function set_mock_instance($obj)
    {
        if (!($obj instanceof static::$class_name)) {
            throw new \testing\invalid_rogosingleton_object();
        }
        static::$inst = $obj;
    }

    /**
     *  Dynamicly map static function calls to dynamic methods the
     *  class defined in parent::$class_name
     */
    public static function __callStatic($name, $args)
    {
        if (!is_object(static::$inst)) {
            $inst = static::$inst = static::get_instance();
        } else {
            $inst = static::$inst;
        }

        if (is_callable(array($inst,$name))) {
            return call_user_func_array(array($inst,$name), $args);
        } else {
            throw new Exception($name . ' not implemented by ' . static::$class_name);
        }
    }

    public function error_handling($context = null)
    {
        return error_handling($this);
    }
}
