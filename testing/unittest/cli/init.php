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

/**
 * This file is used to install and upgrade phpunit for Rogo.
 * 
 * Based on /testing/behat/cli/init.php by Neill Magill <neill.magill@nottingham.ac.uk>
 * 
 * @author Dr Joseph baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @category unittest
 */
ini_set('display_errors', 1);
require_once dirname(dirname(dirname(__DIR__))) . '/include/autoload.inc.php';
autoloader::init();

use testing\unittest\help,
    testing\unittest\environment,
    testing\unittest\database;

// Lets look to see what arguments have been passed.
$options = 'h';
$longoptions = array(
  'clean',
  'help',
  'update',
);

$optionslist = getopt($options, $longoptions);

if (isset($optionslist['h']) or isset($optionslist['help'])) {
  // Display some help information.
  cli_utils::prompt(help::init_help());
  exit(0);
}

// Work out what type of composer dependancy installation method we should use.
if (isset($optionslist['update'])) {
  $composer_method = composer_utils::UPDATE;
} else {
  $composer_method = composer_utils::INSTALL;
}

// Load the phpunit config file.
try {
  $config = Config::get_instance();
  if (!$config->is_phpunit_configured()) {
    // Stop if phpunit is not configured correctly.
    throw new Exception('Phpunit not configured correctly.');
  }
  $config->use_phpunit_site();
} catch (Exception $e) {
  cli_utils::prompt($e->getMessage());
  cli_utils::prompt(help::error());
  exit(0);
}

// Setup some variables that are needed by scripts that are included further down.
$cfg_web_root = get_root_path() . '/';
$cfg_root_path = ltrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', $cfg_web_root), '/');

// Ensure any caches are cleared.
if (function_exists('opcache_reset')) {
    opcache_reset();
}

chdir(__DIR__);

try {
  // Ensure composer and it's dependancies are installed and upto date.
  composer_utils::setup($composer_method);
  // The composer autoloader may not have been generated before this point so we should ensure it is.
  autoloader::init();
  // Create the database.
  if (isset($optionslist['clean']) or environment::upgrade_needed()) {
    database::install_database();
    // Store the version of Rogo that phpunit is initialised for.
    environment::save_version();
  } else {
    cli_utils::prompt('Database does not need updating.');
  }
  // Display the command to run tests.
  cli_utils::prompt(help::run_help());
} catch (Exception $e) {
  cli_utils::prompt($e->getMessage());
  cli_utils::prompt(help::error());
}

exit(0);
