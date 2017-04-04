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

namespace testing\behat\steps\common;

use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode,
    Behat\Behat\Tester\Exception\PendingException;

/**
 * Transform arguments into specific types.
 *
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait transforms {
  /**
   * Converts a string that starts with array: and then a comma seperated list of
   * letters, numbers, underscores and hyphens, closed by a semi-colon into an array.
   *
   * For example:
   *
   * from:
   * array:value1,boo;
   * into:
   * array('value1', 'boo');
   *
   * from:
   * array: value1, boo;
   * into:
   * array('value1', 'boo');
   *
   * @Transform /^array:\s?((?:[\w\d-]+(?:,\s?)?)+);$/
   * @param string $string The value of the capture group from the regular expression.
   * @return array
   */
  public function cast_to_array($string) {
    // Get rid of array from the start and ; from the end of the string.
    $temporary_array = explode(',', $string);
    $return = array();
    foreach ($temporary_array as $value) {
      // Remove any white space from the front and end.
      $trimmed = trim($value);
      if ($trimmed === 'null') {
        // A string of null should be set to be a value of null.
        $trimmed = null;
      }
      $return[] = $trimmed;
    }
    return $return;
  }
}
