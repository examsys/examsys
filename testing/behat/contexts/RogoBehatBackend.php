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

// Start Rogo autoloading.
require_once dirname(dirname(dirname(__DIR__))) . '/include/autoload.inc.php';
autoloader::init();

use testing\behat\rogo_unit_test;

/**
 * This is the backend context for Rogo.
 *
 * It is designed to be used by Behat tests that should be
 * performed as Unit tests rather than UI based tests. It is not suitiable
 * for testing code that uses database transactions.
 *
 * Please do not add setps to it directly.
 * Steps should be included in traits in the \testing\behat\steps\ namespace
 * and then set to be used by this class.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
class RogoBehatBackend extends rogo_unit_test {
  use \testing\behat\steps\backend\include_backend,
      \testing\behat\steps\database\datageneration,
      \testing\behat\steps\common\include_common,
      \testing\behat\hooks\backend;
}
