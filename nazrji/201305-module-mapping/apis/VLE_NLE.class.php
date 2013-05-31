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
* Implement VLE API for NLE
*
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once 'VLEAPI.if.php';
require_once $configObject->get('cfg_web_root') . 'webServices/RestRequest.class';

class VLE_NLE implements iVLEAPI {
  private $_mapping_level = self::LEVEL_SESSION;

  /**
   * Return objectives from the University of Nottingham Medical School Networked Learning Environment
   * @param $moduleID
   * @param $session
   * @return mixed Array of session and objective data in format required by Rogō
   */
  public function getObjectives($moduleID, $session) {
    $req = new RestRequest("http://www.nle.nottingham.ac.uk/webServices/RogoRestAPI.php?url=getObjectives/$moduleID/$session");
    $req->execute();
    return $req->getResponseBody();
  }

  /**
   * Get a friendly name for the source system, with the indefinite article if required
   * @param bool $a     Include the definite article?
   * @param bool $long  Return the long form of the name?
   * @return string     The name in the required format
   */
  public function getFriendlyName($a = false, $long = false) {
    $name = ($long) ? 'Networked Learning Environment' : 'NLE';
    $name = ($a) ? 'a ' . $name : $name;
    return $name;
  }

  /**
   * Get the levels of mapping that are supported by this class
   * @return array Array of mapping levels supported
   */
  public function getMappingLevels() {
    return array(self::LEVEL_SESSION);
  }

  /**
   * Set the mapping level at which the class should work
   * @param integer $level Mapping level
   */
  public function setMappingLevel($level) {
    // Ignore anything passed in, we only support session level mapping
    $this->_mapping_level = self::LEVEL_SESSION;
  }
}
?>