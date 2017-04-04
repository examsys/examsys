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
 * Class to find Javascript unit tests.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @subpackage javascript
 */
class TestLoader {
  /** @var array An array of all the Javascript unit tests. */
  public $tests = array();
  
  /** @var \stdClass The configuration for the suite. */
  public $config;

  /**
   * Find all tests and adds their location to the $tests property.
   *
   * @param string $suite The name of a suite to get the javascript tests for.
   * @return bool Flags if the suite was loaded correctly.
   */
  public function locate($suite) {
    $test_location = SuiteLoader::get_base_directory() . $suite . DIRECTORY_SEPARATOR;
    // Get and load the configuration file for the suite.
    $setup = new \stdClass();
    $configfile = $test_location . 'config.php';
    if (file_exists($configfile)) {
      include $configfile;
    }
    if (!isset($setup->test) || $setup->test !== $suite) {
      return false;
    }
    $this->config = $setup;
    // Get the root location of Rogo.
    $config = \Config::get_instance();
    $rootpath = $config->get('cfg_web_root');
    // Get the test files.
    $files = glob($test_location . '*.js');
    // Add the relative location of the files to the tests array.
    foreach ($files as $file) {
      $path_parts = pathinfo($file);
      $directory = normalise_path($path_parts['dirname']);
      $relativepath = str_replace($rootpath, '', $directory);
      $this->tests[] = $relativepath . '/' . $path_parts['basename'];
    }
    return true;
  }
}
