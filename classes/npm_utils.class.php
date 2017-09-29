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
 * This class is used to install and update npm.
 */
class npm_utils {
  /** Npm should install dependancies respecting the package.json file. */
  const INSTALL = 1;

  /** Npm should get the laest versions of the depedencies. */
  const UPDATE = 2;

  /** Npm should install dependancies respecting the package.json, skipping dev packages. */
  const INSTALL_NODEV = 3;

  /**
   * Language pack component.
   */
  const langcomponent = 'classes/npmutils';
  
  /**
   * Ensures that npm is installed, uptodate and has installed all the projects dependancies.
   *
   * @return void
   */
  public static function setup($method = self::INSTALL) {
    // We are going to chage the working directory and want to reset it later.
    $workingdir = getcwd();
    // Change to the root Rogo directory.
    chdir(__DIR__ . '/..');
    self::install_update();
    if ($method === self::UPDATE) {
      self::update_dependancies();
    } else {
      self::fetch_dependancies($method);
    }
    chdir($workingdir);
  }

  /**
   * Ensures npm is installed and upto date.
   *
   * @return void
   */
  protected static function install_update() {
    $langpack = new langpack();
    // Update if installed.
    exec("npm install npm@latest -g", $output, $statuscode);
    if ($statuscode != 0) {
      throw new Exception($langpack->get_string(self::langcomponent, 'cannotupdate'));
    }
  }

  /**
   * Downloads and installs all the files required by the package.json file for the project.
   * @param integer $method install method
   * @return void
   */
  protected static function fetch_dependancies($method) {
    $langpack = new langpack();
    $devflag = '';
    if ($method === self::INSTALL_NODEV) {
      $devflag = '--production';
    }
    passthru("npm install $devflag", $statuscode);
    if ($statuscode != 0) {
      throw new Exception($langpack->get_string(self::langcomponent, 'couldnotinstallnpm'));
    }
  }

  /**
   * Downloads and installs all the files required by the package.json file for the project.
   * @param integer $method update method
   * @return void
   */
  protected static function update_dependancies() {
    $langpack = new langpack();
    passthru("npm update", $statuscode);
    if ($statuscode != 0) {
      throw new Exception($langpack->get_string(self::langcomponent, 'couldnotupdatenpm'));
    }
  }
}
