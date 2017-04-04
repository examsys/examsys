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

/* 
 * This class is used to install and update composer.
 */
class composer_utils {
  /** Composer should install dependancies respecting the lock file. */
  const INSTALL = 1;

  /** Composer should get the laest versions of the depedencies and update the lock file. */
  const UPDATE = 2;

  /** Composer should install dependancies respecting the lock file, skipping dev packages. */
  const INSTALL_NODEV = 3;

  /** Composer should get the laest versions of the depedencies and update the lock file, skipping dev packages. */
  const UPDATE_NODEV = 4;
  
  /**
   * Language pack component.
   */
  const langcomponent = 'classes/composerutils';
  
  /**
   * Ensures that composer is installed, uptodate and has installed all the projects dependancies.
   *
   * @return void
   */
  public static function setup($method = self::INSTALL) {
    // We are going to chage the working directory and want to reset it later.
    $workingdir = getcwd();
    // Change to the root Rogo directory.
    chdir(__DIR__ . '/..');
    self::install_update();
    if ($method === self::UPDATE or $method === self::UPDATE_NODEV) {
      self::update_dependancies($method);
    } else {
      self::fetch_dependancies($method);
    }
    chdir($workingdir);
  }

  /**
   * Ensures composer is installed and upto date.
   *
   * @return void
   */
  protected static function install_update() {
    $langpack = new langpack();
    if (!file_exists(__DIR__ . '/../composer.phar')) {
      // Composer needs to be installed.
      passthru("curl http://getcomposer.org/installer | php", $statuscode);
      if ($statuscode != 0) {
        throw new Exception($langpack->get_string(self::langcomponent, 'cannotinstall'));
      }
    } else {
      // Composer needs to be updated.
      passthru("php composer.phar self-update", $statuscode);
      if ($statuscode != 0) {
        throw new Exception($langpack->get_string(self::langcomponent, 'cannotupdate'));
      }
    }
  }

  /**
   * Downloads and installs all the files required by the composer.lock file for the project.
   * @param integer $method install method
   * @return void
   */
  protected static function fetch_dependancies($method) {
    $langpack = new langpack();
    $devflag = '';
    if ($method === self::INSTALL_NODEV) {
      $devflag = '--no-dev';
    }
    passthru("php composer.phar install $devflag", $statuscode);
    if ($statuscode != 0) {
      throw new Exception($langpack->get_string(self::langcomponent, 'couldnotinstallcomp'));
    }
  }

  /**
   * Downloads and installs all the files required by the composer.json file for the project.
   * @param integer $method update method
   * @return void
   */
  protected static function update_dependancies($method) {
    $langpack = new langpack();
    $devflag = '';
    if ($method === self::UPDATE_NODEV) {
      $devflag = '--no-dev';
    }
    passthru("php composer.phar update $devflag", $statuscode);
    if ($statuscode != 0) {
      throw new Exception($langpack->get_string(self::langcomponent, 'couldnotupdatecomp'));
    }
  }
}
