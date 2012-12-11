<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 21/11/12
 * Time: 15:56
 * To change this template use File | Settings | File Templates.
 */
/**
 *
 * config file
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 *
 * Designed to hold the config options in a class for easier access.
 */
class Config {
  /**
   * @var array
   */
  public $data;
  private static $inst;

  /**
   *
   */

  public static function Instance()
  {
    if (!is_object(self::$inst)) {
      self::$inst = new Config();
    }
    return self::$inst;
  }

  private function __construct() {
    include __DIR__ . '/../config/config.inc.php';
    $this->data = get_defined_vars();
  }

  function export_all() {
    return $this->data;
  }

  function get($var) {
    if (is_string($var)) {
      if (isset($this->data[$var])) {
        return $this->data[$var];
      }
    } elseif(is_array($var)) {
      $dat=array();
      foreach($var as $key) {
        if(isset($this->data[$key])) {
          $dat[$key]=$this->data[$key];
        }
      }
      return $dat;
    }

    return null;
  }
}
