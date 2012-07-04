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
* NLE API, all NLE related functions go in here
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require 'VLEAPI.if.php';
require '../webServices/RestRequest.class';

class VLE_UoNCM implements iVLEAPI {
  //GET objs from the NLE
  public function getObjectives($moduleID, $session) {
    // TODO: need to use the find interface to get the Module ID for code and session
    $req = new RestRequest("http://cm.rji.ac.uk/2011/index.php/api/json/125/module_session_obs");
    $split_username = explode('_', $_SERVER['PHP_AUTH_USER']);
    $req->setUsername($split_username[0]);
    $req->setPassword($_SERVER['PHP_AUTH_PW']);
    $req->execute();

    $x = 3;
    exit;
    return $req->getResponseBody();
  }

  public function getFriendlyName($a = false) {
    return ($a) ? 'a Curriculum Map' : 'Curriculum Map';
  }
}
?>