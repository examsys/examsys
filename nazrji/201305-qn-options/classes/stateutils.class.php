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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

Class StateUtils {

  function getState($userID, $db, $page = '') {
    $state_array = array();
    if ($page == '') {
      $page = $_SERVER['PHP_SELF'];
    }
    
    $result = $db->prepare("SELECT state_name, content FROM state WHERE page = ? AND userID = ?");
    $result->bind_param('si', $page, $userID);
    $result->execute();
    $result->store_result();
    $result->bind_result($state_name, $content);
    while ($result->fetch()) {
      $state_array[$state_name] = $content;
    }
    $result->close();
    
    return $state_array;
  }

  function setState($userID, $state_name, $content, $page, $db) {
    $result = $db->prepare("REPLACE INTO state (userID, state_name, content, page) VALUES (?, ?, ?, ?)");
    $result->bind_param('isss', $userID, $state_name, $content, $page);
    $result->execute();
  }

}

$stateutil = new StateUtils();
?>