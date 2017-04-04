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
 * This class should define all the pre and post hooks for Rogo behat tests.
 *
 * This includes:
 * - cleaning up the database
 * - cleaning up the user data directories
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
trait frontend_hooks {
  use config;

  /** Stores if the setup function for the first scenario to be run has been completed. */
  private static $firstscenariosetup = false;

  /** Stores the dataloader used to initilise the data the  */
  private static $dataloader;

  /**
   * Actions to perform before the suite is run.
   *
   * @BeforeSuite
   */
  public static function setup_suite(BeforeSuiteScope $event) {
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

    // Test that the website is running.
    if (!environment::is_server_running()) {
      $message = environment::get_behat_website() . ' is not available. '
          . 'Please ensure that the correct url is configured and the server is running.'
          . PHP_EOL . 'See ' . help::DOCUMENTATION . ' for mor information.';
      throw new Exception($message);
    }

    $dataloader = new Default_Loader(true);
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
    state::save_database_state(state::TRANSACTION_FEATURE);
  }

  /**
   * Actions to perform before every scenario.
   *
   * @BeforeScenario
   */
  public function setup_scenario(BeforeScenarioScope $event) {
    self::check_config();
    self::special_config();
    state::save_database_state(state::TRANSACTION_SCENARIO);

    $session = $this->getSession();

    if (self::is_first_scenario()) {
      selectors::register_rogo_selectors($session);
    }

    // Reset the session.
    $session->reset();

    if (self::is_first_scenario()) {
      // This should be the last thing done in this method.
      self::$firstscenariosetup = true;
    }
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
    state::rollback_database_state(state::TRANSACTION_SCENARIO);
    // Ensure the directories are empty.
    directory::reset_directories();
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
    state::rollback_database_state(state::TRANSACTION_FEATURE);
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

  /**
   * Returns whether the first scenario of the suite is running
   * @return bool
   */
  protected static function is_first_scenario() {
    return !(self::$firstscenariosetup);
  }

  /**
   * Sets some values that are usually installed by default.
   */
  protected static function special_config() {
    global $string;
    $configObject = \Config::get_instance();
    $cfg_web_root = $configObject->get('cfg_web_root');
    $language = \LangUtils::getLang($cfg_web_root);
    require __DIR__ . '/../../../../include/timezones.php';
    $encoded_timezones = json_encode($timezone_array);
    $encoded_cohorts = json_encode(array('<whole cohort>', '0-10', '11-20', '21-30', '31-40', '41-50', '51-75', '76-100', '101-150', '151-200', '201-300',
        '301-400', '401-500'));
    $configObject->set_db_object(state::get_db());
    $configObject->set_setting('timezones', $encoded_timezones, \Config::JSON);
    $configObject->set_setting('cohort_sizes', $encoded_cohorts, \Config::JSON);
    $configObject->set_setting('max_duration', 779, \Config::INTEGER);
    $configObject->set_setting('max_sittings', 6, \Config::INTEGER);
  }
}
