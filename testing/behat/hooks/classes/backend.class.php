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

namespace testing\behat\hooks;

use Behat\Testwork\Hook\Scope\AfterSuiteScope,
    Behat\Testwork\Hook\Scope\BeforeSuiteScope,
    Behat\Behat\Hook\Scope\AfterFeatureScope,
    Behat\Behat\Hook\Scope\BeforeFeatureScope,
    Behat\Behat\Hook\Scope\AfterScenarioScope,
    Behat\Behat\Hook\Scope\BeforeScenarioScope;
use testing\behat\environment,
    testing\behat\help,
    testing\behat\selectors;
use testing\datagenerator\loader;
use testing\behat\helpers\rogo\directory,
    testing\behat\helpers\database\state,
    testing\behat\helpers\database\Default_Loader;
use Config as RogoConfig,
    Exception;

/**
 * This class should define all the pre and post hooks for Rogo backend behat tests.
 *
 * This includes:
 * - cleaning up the database
 * - cleaning up the user data directories
 *
 * These hooks assume that all tested database calls during tests will be within the scope of the
 * transactions these hooks set and that the tested code will not use transactions.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
trait backend {
  use config;

  /** Stores the dataloader used to initilise the data the  */
  private static $dataloader;

  /** Stores a copy of $_GET before a scenario. */
  private $get;

  /** Stores a copy of $_POST before a scenario. */
  private $post;

  /** Stores a copy of $_REQUEST before a scenario. */
  private $request;

  /**
   * Actions to perform before the suite is run.
   *
   * @BeforeSuite
   */
  public static function setup(BeforeSuiteScope $event) {
    self::check_config();
    // Setup the config for behat and store a cloned instance of it.
    $config = RogoConfig::get_instance();
    // Check Rogo is installed correctly.
    if ($config->get('rogo_version') != $config->getxml('version')) {
      $message = 'The version of Rogo in the config file (' . $config->get('rogo_version') . ')'
          . ' does not match the version of the Rogo code (' . $config->getxml('version') . ')';
      throw new Exception($message);
    }
    self::$default_config = clone($config);
    $config->use_behat_site();
    self::$rogo_config = clone($config);

    state::connect($config);
    state::sanatise_tables();
    // Let the data generators have the database connection.
    loader::set_database(state::get_db());

    // Ensure the directories are empty.
    directory::reset_directories();

    $dataloader = new Default_Loader();
    $dataloader->load();
    self::$dataloader = $dataloader;
  }

  /**
   * Actions to perform before every Feature.
   *
   * @BeforeFeature
   */
  public static function setup_feature(BeforeFeatureScope $event) {
    self::check_config();
    state::start_transaction(state::TRANSACTION_FEATURE);
  }

  /**
   * Actions to perform before every scenario.
   *
   * @BeforeScenario
   */
  public function setup_scenario(BeforeScenarioScope $event) {
    self::check_config();
    state::start_transaction(state::TRANSACTION_SCENARIO);
    $this->get = $_GET;
    $this->post = $_POST;
    $this->request = $_REQUEST;
  }

  /**
   * Cleanup up Rogo after a scenario has been run.
   *
   * @AfterScenario
   */
  public function teardown_scenario(AfterScenarioScope $event) {
    // Reset the config object.
    RogoConfig::set_mock_instance(clone(self::$rogo_config));
    // Rollback any database changes.
    state::rollback_transaction(state::TRANSACTION_SCENARIO);
    // Ensure the directories are empty.
    directory::reset_directories();
    // Reset any locally stored data.
    $this->reset();
    $_GET = $this->get;
    $_POST = $this->post;
    $_REQUEST = $this->request;
  }

  /**
   * Clean up Rogo after a feature file has been run.
   *
   * @AfterFeature
   */
  public static function teardown_feature(AfterFeatureScope $event) {
    // Reset the config object.
    RogoConfig::set_mock_instance(clone(self::$rogo_config));
    // Rollback any database changes.
    state::rollback_transaction(state::TRANSACTION_FEATURE);
    // Ensure the directories are empty.
    directory::reset_directories();
  }

  /**
   * Clean up Rogo after the suite has finished running.
   *
   * @AfterSuite
   */
  public static function teardown(AfterSuiteScope $event) {
    // Ensure the directories are empty.
    directory::reset_directories();
    // Perform the dataloaders teardown.
    $dataloader = self::$dataloader;
    $dataloader->clean();
    // Close the database connection.
    state::close_db();
    // Reset the config object.
    RogoConfig::set_mock_instance(clone(self::$default_config));
  }
}
