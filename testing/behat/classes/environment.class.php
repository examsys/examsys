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
use Symfony\Component\Yaml\Yaml,
    Config;

/**
 * This class is used to install and update behat in Rogo.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @category behat
 */
class environment {
  /** The default values for the website that behat will use for testing. */
  const DEFAULT_WEBSITE = 'http://localhost:8000';

  /**
   * Builds the behat.yml file in testing/behat/config
   *
   * This function should be modified if we wish to change the config that is used.
   *
   * @throws Exception
   */
  public static function build_config() {
    $basedir = self::get_basedir();

    $config = array(
      'default' => array(
        'autoload' => array(
          $basedir . DIRECTORY_SEPARATOR . 'contexts',
        ),
        'suites' => array(
          'frontend' => array(
            'contexts' => array(
              'RogoBehatFrontend',
            ),
            'paths' => array(
              $basedir . DIRECTORY_SEPARATOR . 'features',
            ),
            'settings' => array(
              'filters' => array(
                'tags' => '~@backend'
              ),
            ),
          ),
          'backend' => array(
            'contexts' => array(
              'RogoBehatBackend',
            ),
            'paths' => array(
              $basedir . DIRECTORY_SEPARATOR . 'features',
            ),
            'settings' => array(
              'filters' => array(
                'tags' => '@backend'
              ),
            ),
          ),
        ),
        'formatters' => array(
          'progress' => null,
        ),
        'extensions' => array(
          'Behat\MinkExtension' => array(
            'base_url' => self::get_behat_website(),
            'goutte' => null,
            'selenium2' => array(
              'browser' => 'chrome',
            ),
          ),
        ),
      ),
    );

    if (!file_put_contents(self::get_yml_location(), Yaml::dump($config, 10, 2))) {
      throw new Exception('Could not write the behat.yml page.');
    }
  }

  /**
   * Gets the website defined for the behat site in the config file or uses the default.
   *
   * @return string
   */
  public static function get_behat_website() {
    $behatwebsite = Config::get_instance()->get('cfg_behat_website');
    return $behatwebsite;
  }

  /**
   * Get the fully qualified path of the testing/behat directory.
   *
   * @return string
   */
  protected static function get_basedir() {
    return self::get_rogo_basedir() . DIRECTORY_SEPARATOR . 'testing'. DIRECTORY_SEPARATOR . 'behat';
  }
  
  /**
   * Get the full path to the behat.yml file.
   * 
   * @return string 
   */
  public static function get_yml_location() {
    return self::get_basedir() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'behat.yml';
  }

  /**
   * Check if the behat web server instance is running.
   *
   * @return boolean
   */
  public static function is_server_running() {
    $return = false;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, self::get_behat_website());
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_exec($curl);
    if (curl_errno($curl) === 0) {
      $return = true;
    }
    curl_close($curl);
    return $return;
  }

  /**
   * Check if the behat database needs refreshing.
   *
   * @return boolean
   */
  public static function upgrade_needed() {
    $config = Config::get_instance();
    if (self::rogo_behat_version() != $config->getxml('version')) {
      return true;
    }
    return false;
  }

  /**
   * Gets the location of the file that stores the version of code that behat is setup to run.
   *
   * @return string
   */
  public static function get_version_location() {
    return self::get_basedir() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'version.php';
  }

  /**
   * Writes a file that contains the version number of the Rogo code.
   *
   * @return void
   */
  public static function save_version() {
    $codeversion = Config::get_instance()->getxml('version');
    $file = self::get_version_location();
    if (!file_put_contents($file, $codeversion)) {
      throw new Exception('Could not write version file.');
    }
  }

  /**
   * Get the version of Rogo that behat is initialised for.
   *
   * @return string
   */
  public static function rogo_behat_version() { 
    $file = self::get_version_location();
    if (file_exists($file)) {
      $version = file_get_contents($file);
    } else {
      $version = '';
    }
    return $version;
  }

  /**
   * Returns the directory that Rogo is installed in.
   *
   * @return string
   */
  protected static function get_rogo_basedir() {
    return dirname(dirname(dirname(__DIR__)));
  }
}
