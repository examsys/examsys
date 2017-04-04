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

namespace testing\behat\steps\backend;

use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode,
    Behat\Behat\Tester\Exception\PendingException;

use testing\behat\helpers\database\state;

/**
 * Database related step definitions.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait database {
  /** @var string The name of the database state used by database steps. */
  private $db_state_name = 'databaseteststate';

  /**
   * Checks that there are an expected number of rows in a database table.
   * 
   * @Given /^there are "([^"]*)" records in the "([^"]*)" table$/
   * @param int $count The expected number of records
   * @param string $table The name odf the table to check
   * @throws Exception
   */
  public function there_are_records_in_the_table($count, $table) {
    $sql = "SELECT COUNT(*) AS count FROM $table";
    $db = state::get_db();
    $result = $db->query($sql);
    if ($result === false) {
      throw new \Exception("$sql failed in testing\behat\steps\backend\database\there_are_records_in_the_table");
    }
    $row = $result->fetch_object();
    $this->assertEquals($count, $row->count);
  }

  /**
   * Saves the database state using the testing\behat\helpers\database\state class.
   * This step cannot be called twice without "I reset the database state" being
   * used between the calls.
   *
   * @given I store the database state
   */
  public function i_store_the_database_state() {
    state::save_database_state($this->db_state_name);
  }

  /**
   * Resets the database state using the testing\behat\helpers\database\state class
   * The "I store the database state" step must previously have been called.
   *
   * @given I reset the database state
   */
  public function i_reset_the_database_state() {
    state::rollback_database_state($this->db_state_name);
  }
}
