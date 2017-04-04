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

/**
 * Steps for testing the param class
 *
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait param {
  /**
   * Pass a value to the param::clean() method using the specified type of filter and store the result.
   *
   * @Given /^I clean "([^"]*)" as "([^"]*)"$/
   * @param mixed $value A value that should be cleaned.
   * @param string $type The type the clean function expects.
   */
  public function i_clean_value_as_type($value, $type) {
    if (!defined("\param::$type")) {
      throw new \coding_exception('You must provicde the name of a constant of the param class');
    }
    $this->original = $value;
    $this->type = $type;
    $this->clean = \param::clean($value, constant("\param::$type"));
  }

  /**
   * Tests that the result of the last cleaning step gave the expected result.
   * The 'I clean "([^"]*)" as "([^"]*)' step must have been run within the scenario already.
   *
   * Note that if null, true or false are passed as a result they will be treated as the PHP keywords.
   *
   * @Given /^the clean result should be "([^"]*)"$/
   * @param mixed $result
   */
  public function the_clean_result_should_be($result) {
    $failmessage = "'$this->original' was cleaned as a '$this->type'";
    if ($result === 'null') {
      $this->assertNull($this->clean, $failmessage);
    } else if ($result === 'true') {
      $this->assertTrue($this->clean, $failmessage);
    } else if ($result === 'false') {
      $this->assertFalse($this->clean, $failmessage);
    } else {
      $this->assertEquals($result, $this->clean, $failmessage);
    }
  }

  /**
   * Fake values being sent via get or post requests by directly adding them to the relevant global arrays.
   * Note this will not actually honour any PHP settings for the $REQUEST array so if you pass the a variable
   * of the same name via post and get the REQUEST global will have the last value sent.
   *
   * The table should contain the following columns:
   * # name - the name of the parameter
   * # value - the value of the parameter
   * # method - either GET or POST
   *
   * @Given the following parameters have been passed:
   * @param TableNode $paramters A table of values
   */
  public function the_following_parameters_have_been_passed(TableNode $paramters) {
    foreach ($paramters->getHash() as $paramter) {
      $name = $paramter['name'];
      $value = $paramter['value'];
      if (preg_match('/^array:\s?((?:[\w\d-]+(?:,\s?)?)+);$/', $value, $matches)) {
        // Pass the matching group to the method. This is the value it expects.
        $value = $this->cast_to_array($matches[1]);
      }
      switch ($paramter['method']) {
        case 'GET':
          $_GET[$name] = $value;
          $_REQUEST[$name] = $value;
          break;
        case 'POST':
          $_POST[$name] = $value;
          $_REQUEST[$name] = $value;
          break;
        default:
          throw new \coding_exception('Only GET and POST are valid methods');
      }
    }
  }

  /**
   * Use the param::optional() method to retrive a parameter. It is hard coded in the step to return null as a default.
   *
   * @When /^I look for the optional "([^"]*)" parameter "([^"]*)" as a "([^"]*)"$/
   * @param string $from The global array to get the value from (must be GET, POST or REQUEST)
   * @param string $name The name of the paramter to get a value for
   * @param string $type The type the paramter should be filtered as
   * @throws \coding_exception
   */
  public function i_look_for_the_optional_parameter_as_a($from, $name, $type) {
    if (!defined("\param::$type")) {
      throw new \coding_exception('You must provicde the name of a constant of the param class');
    }
    $this->original = $name;
    $this->type = $type;
    if ($from == 'GET') {
      $from = \param::FETCH_GET;
    } else if ($from == 'POST') {
      $from = \param::FETCH_POST;
    } else if ($from == 'REQUEST') {
      $from = \param::FETCH_REQUEST;
    } else {
      throw new \coding_exception('$from must be one of: GET, POST, REQUEST');
    }
    $this->clean = \param::optional($name, null, constant("\param::$type"), $from);
  }

  /**
   * Use the param::require() method to retrive a parameter.
   *
   * @When /^I look for the required "([^"]*)" parameter "([^"]*)" as a "([^"]*)"$/
   * @param string $from The global array to get the value from (must be GET, POST or REQUEST)
   * @param string $name The name of the paramter to get a value for
   * @param string $type The type the paramter should be filtered as
   * @throws \coding_exception
   */
  public function i_look_for_the_required_parameter_as_a($from, $name, $type) {
    if (!defined("\param::$type")) {
      throw new \coding_exception('You must provicde the name of a constant of the param class');
    }
    $this->original = $name;
    $this->type = $type;
    if ($from == 'GET') {
      $from = \param::FETCH_GET;
    } else if ($from == 'POST') {
      $from = \param::FETCH_POST;
    } else if ($from == 'REQUEST') {
      $from = \param::FETCH_REQUEST;
    } else {
      throw new \coding_exception('$from must be one of: GET, POST, REQUEST');
    }
    $this->clean = \param::required($name, constant("\param::$type"), $from);
  }

  /**
   * Use the param::require() method to retrive a parameter.
   *
   * @When /^I look for the required "([^"]*)" parameter "([^"]*)" as a "([^"]*)" there should be an exception$/
   * @param string $from The global array to get the value from (must be GET, POST or REQUEST)
   * @param string $name The name of the paramter to get a value for
   * @param string $type The type the paramter should be filtered as
   * @throws \coding_exception
   */
  public function i_look_for_the_required_parameter_as_a_type_there_should_be_an_exception($from, $name, $type) {
    if (!defined("\param::$type")) {
      throw new \coding_exception('You must provicde the name of a constant of the param class');
    }
    $this->original = $name;
    $this->type = $type;
    if ($from == 'GET') {
      $from = \param::FETCH_GET;
    } else if ($from == 'POST') {
      $from = \param::FETCH_POST;
    } else if ($from == 'REQUEST') {
      $from = \param::FETCH_REQUEST;
    } else {
      throw new \coding_exception('$from must be one of: GET, POST, REQUEST');
    }
    try {
      \param::required($name, constant("\param::$type"), $from);
    } catch (\MissingParameter $e) {
      // Validate the type of exception.
      return;
    }
    throw new \Exception('No exception was thrown by \param::required()');
  }
}
