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

namespace testing\behat\helpers\database;
use PHPUnit\DbUnit\TestCaseTrait;
use PHPUnit\DbUnit\DataSet\YamlDataSet;
use PDO;

/**
 * Implements the PHP Unit database extension for Rogo Behat tests.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
class Default_Loader extends Data_Loader {
  use TestCaseTrait {
    setUp as public load;
    tearDown as public clean;
  }

  public function __construct($load_help = false) {
    parent::__construct($load_help);
    $this->fixture_base = __DIR__ . '/../../../fixtures/base/';
  }
  
  /**
   * Create and return the connection to the rogo behat database that PHP unit database extension will use.
   * @return type
   */
  protected function getConnection() {
    $config = \Config::get_instance();
    if (!isset($this->phpunit_db)) {
      $database = $config->get('cfg_behat_db_database');
      $host = $config->get('cfg_db_host');
      $user = $config->get('cfg_behat_db_user');
      $password = $config->get('cfg_behat_db_password');
      $pdo_connection = new PDO("mysql:dbname=" . $database . ";" . "host=" . $host, $user, $password);
      $this->phpunit_db = $this->createDefaultDBConnection($pdo_connection, $database);
    }
    return $this->phpunit_db;
  }

  /**
   * Gets the base data that should always be present in Rogo.
   *
   * There should be a yml file for every database table in Rogo.
   */
  protected function getDataSet() {
    $base_location = $this->fixture_base;
    // Get a list of all files to be loaded.
    $fixtures = glob("$base_location*.yml");
    if (count($fixtures) < 1) {
      // We should find some yml files!
      throw new \Exception('Could not find base fixture files');
    }
    // Create the dataset.
    $firstfixture = array_shift($fixtures);
    $dataset = new YamlDataSet($firstfixture);
    // Build up the dataset.
    foreach ($fixtures as $fixture) {
      $dataset->addYamlFile($fixture);
    }
    return $dataset;
  }
}
