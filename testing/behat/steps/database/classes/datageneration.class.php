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

namespace testing\behat\steps\database;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

/**
 * Core steps that add data into the Rogo database.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait datageneration {
  /**
   * Maps the types that can be passed to exist to a data generator.
   *
   * The values should be an array where:
   * the first value is the name of the data generator,
   * the second value should be the data generator's component,
   * the third value is the function that should be called to create the data.
   *
   * @var array
   */
  protected $datagenerator_map = array(
    'users' => array('users', 'core', 'create_user'),
    'papers' => array('papers', 'core', 'create_paper'),
    'questions' => array('questions', 'core', 'create_question'),
    'modules' => array('modules', 'core', 'create_module'),  
    'academic_year' => array('academic_year', 'core', 'create_academic_year'),    
    'module_teams' => array('modules', 'core', 'create_module_team'),    
  );

  /**
   * Adds records to the database using an appropriate data generator.
   * 
   * @Given /^the following "([^"]*)" exist:$/
   * @param string $type The type of data to be generated. It must appear in self::$datagenerator_map
   * @param TableNode $data The data to be loaded
   * @throws PendingException if the type is not mapped, or the generator function does not exist
   */
  public function the_following_exist($type, TableNode $data) {
    if (!isset($this->datagenerator_map[$type])) {
      // The type has not yet been mapped.
      // We should let the user know that it needs to be implemented.
      throw new PendingException("Implement a data generator for: $type");
    }
    $generatorname = $this->datagenerator_map[$type][0];
    $generatorcomponent = $this->datagenerator_map[$type][1];
    $createmethod = $this->datagenerator_map[$type][2];
    $datagenerator = $this->get_datagenerator($generatorname, $generatorcomponent);
    // Check the creation method exists.
    if (!method_exists($datagenerator, $createmethod)) {
      $message = "Implement the {$createmethod} method in the "
      . "{$generatorcomponent}_{$generatorname} data generator";
      throw new PendingException($message);
    }
    // Convert the data into a form that the data generator can use.
    foreach ($data->getHash() as $row) {
      // Pass each row into the generator.
      $datagenerator->$createmethod($row);
    }
  }
}
