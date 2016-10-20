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
 *
 * Interface to be implemented by all VLE API classes
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

class UnsupportedMappingLevelException extends Exception { }

interface iCMAPI
{
  const LEVEL_SESSION = 0;
  const LEVEL_MODULE = 1;

  /**
   * Return objectives from the remote system
   * @param string $moduleID module code
   * @param integer $session academic session
   * @param mysqli $db database connection
   * @return mixed Array of session and objective data in format required by Rogō
   */
  public function getObjectives($moduleID, $session, $db);

  /**
   * Get a friendly name for the source system, with the indefinite article if required
   * @param bool $a     Include the definite article?
   * @param bool $long  Return the long form of the name?
   * @return string     The name in the required format
   */
  public function getFriendlyName($a = false, $long = false);

  /**
   * Get the levels of mapping that are supported by this class
   * @return array Array of mapping levels supported
   */
  public function getMappingLevels();

  /**
   * Set the mapping level at which the class should work
   * @param integer $level Mapping level
   */
  public function setMappingLevel($level);
}
