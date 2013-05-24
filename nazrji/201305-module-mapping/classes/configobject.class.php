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

require_once __DIR__ . '/../classes/rogostaticsingleton.class.php';

/**
 *
 * config file
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 *
 * Designed to hold the config options in a class for easier access.
 */
class Config extends RogoStaticSingleton {
  /**
   * @var array
   */
  public $data;
  protected static $inst;
  protected static $class_name='Config';

  /**
   *
   */
/*
  public static function get_instance()
  {
    if (!is_object(self::$inst)) {
      self::$inst = new Config();
    }
    return self::$inst;
  }
*/
function __toString() {
  return "ConfigObject!";
}
  protected function __construct() {
    $conf_file = __DIR__ . '/../config/config.inc.php';
    if (file_exists($conf_file)) {
      include $conf_file;
    }
    $this->data = get_defined_vars();
  }

  function export_all() {
    return $this->data;
  }

  function set($var, $value) {
    $this->data[$var]=$value;
  }

  function append($var, $value) {
    $this->data[$var]=$this->data[$var] . $value;
  }

  function get($var) {
    if (is_string($var)) {
      if (isset($this->data[$var])) {
        return $this->data[$var];
      }
    } elseif (is_array($var)) {
      $dat = array();
      foreach ($var as $key) {
        if (isset($this->data[$key])) {
          $dat[$key]=$this->data[$key];
        }
      }
      return $dat;
    }
    return null;
  }

  function &getbyref($var) {
    if (is_string($var)) {
      if (isset($this->data[$var])) {
        return $this->data[$var];
      }
    }

    return null;
  }
}
