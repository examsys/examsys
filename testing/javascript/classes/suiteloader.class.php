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

namespace testing\javascript;

/**
 * Class to find all test Suites in Rogō.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @subpackage javascript
 */
class SuiteLoader {
  /** @var array An array of all the Javascript unit tests. */
  public $suites = array();

  /**
   * Find all tests and adds their location to the $tests property.
   */
  public function locate_all() {
    // Get the root location of Rogo.
    $config = \Config::get_instance();
    $rootpath = $config->get('cfg_web_root');
    // Get the test files.
    $test_location = self::get_base_directory() . '*';
    $suites = glob($test_location, GLOB_ONLYDIR);
    // Add the name of the suite to the array.
    foreach ($suites as $suite) {
      $this->suites[] = basename($suite);
    }
  }

  /**
   * Get the base directory all Javascript based tests are located in.
   *
   * @return string
   */
  public static function get_base_directory() {
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR;
  }
}
