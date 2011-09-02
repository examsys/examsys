<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* NLE API, all NLE related functions go in here
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../webServices/RestRequest.class';

  //GET objs from the NLE
  function getObjectives($moduleID, $session) {
    $req = new RestRequest("http://www.nle.nottingham.ac.uk/webServices/getObjectives/$moduleID/$session");
    $split_username = explode('_', $_SERVER['PHP_AUTH_USER']);
    $req->setUsername($split_username[0]);
    $req->setPassword($_SERVER['PHP_AUTH_PW']);
    $req->execute();
    return $req->getResponseBody();
  }
?>