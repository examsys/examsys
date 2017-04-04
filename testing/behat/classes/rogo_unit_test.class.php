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

namespace testing\behat;
use Behat\Behat\Context\Context,
    Behat\Behat\Exception\PendingException;
use PHPUnit_Framework_Assert;
use testing\datagenerator\loader;
use coding_exception,
    Exception;

/**
 * All Rogo behat test definitions should extend this class if they wish to do PHP Unit based tests.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
class rogo_unit_test implements Context {
  /** 
   * Stores any custom variables assigned to the class.
   * 
   * This is to allow steps within a test using the context to store information easily.
   * Forcing the data to use this array will allow us to easily reset this data beween tests.
   *
   * @var array 
   */
  private $data = array();
  
  /**
   * Get a data generator for adding information into the Rogo database.
   *
   * @param string $name The name of the generator.
   * @param string $component The component the generator is from (optional).
   * @return \testing\datagenerator\datagenerator
   * @throws \testing\datagenerator\not_found
   */
  protected function get_datagenerator($name, $component = 'core') {
    return loader::get($name, $component);
  }

  /**
   * Magically call PHPUnit functions as though they were part of the rogo_unit_test class.
   * 
   * @param string $name the name of the function
   * @param array $arguments the arguments passed to the function
   */
  public function __call($name, $arguments) {
    if (method_exists('PHPUnit_Framework_Assert', $name)) {
      return call_user_func_array("PHPUnit_Framework_Assert::$name", $arguments);
    }
    throw new coding_exception("The method $name does not exist");
  }
  
  /**
   * Set some custom data.
   * 
   * @param string $name the name of the variable
   * @param mixed $value the value for the variable
   */
  public function __set($name, $value) {
    $this->data[$name] = $value;
  }
  
  /**
   * Get custom data values stored in the class..
   * 
   * @param string $name
   * @return mixed
   */
  public function __get($name) {
    if (!isset($this->data[$name])) {
      return null;
    }
    return $this->data[$name];
  }
  
  /**
   * Checks if a custom data variable is set.
   * 
   * @param string $name
   * @return boolean
   */
  public function __isset($name) {
    return isset($this->data[$name]);
  }
  
  /**
   * Unsets a custom data function.
   * 
   * @param string $name
   */
  public function __unset($name) {
    if (isset($this->data[$name])) {
      unset($this->data[$name]);
    }
  }
  
  /**
   * Resets the custom data array back to it's default state.
   */
  final public function reset() {
    $this->data = array();
  }
}
