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
   * @param bool $a
   * @return string
   */
  public function getFriendlyName($a = false) {
    return ($a) ? 'an NLE' : 'NLE';
  }
}
?>