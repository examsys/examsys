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
 * Utility class for folder related functionality
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */


Class folder_utils {

  /**
  * Returns the name of a folder from an ID
  *
  * @param $folderID ID of the folder to be used
  * @param $db database connection
  * @return string the name of the folder.
  */
  static function get_folder_name($folderID, $db) {
    $folder = $_GET['folder'];
    $result = $db->prepare("SELECT name FROM folders WHERE id = ? LIMIT 1");
    $result->bind_param('i', $folderID);
    $result->execute();
    $result->bind_result($name);
    $result->fetch();
    $result->close();
    
    return $name;
  }
  
  /**
  * Creates a new personal folder for a user.
  *
  * @param $folder_name The name of the folder
  * @param $userObj The userObject of the currently logged in user
  * @param $db 
  * @return string the name of the folder.
  */
  static function create_folder($folder_name, $userObj, $db) {
    if ($folder_query = $db->prepare("INSERT INTO folders VALUES (NULL, ?, ?, NOW(), 'yellow', NULL)")) {
      $folder_query->bind_param('is', $userObj->get_user_ID(), $folder_name);
      $folder_query->execute();
      $folder_query->close();
    } else {
      display_error("New Folder Error", $db->error);
    }
  }
  
  static function folder_exists($folder_name, $userObj, $db) {
    $result = $db->prepare("SELECT name FROM folders WHERE ownerID = ? AND name = ?");
    $result->bind_param('is', $userObj->get_user_ID(), $folder_name);
    $result->execute();
    $result->store_result();
    if ($result->num_rows() == 0) {
      $duplicate = false;
    } else {
      $duplicate = true;
    }
    $result->close();
    
    return $duplicate;
  }
  
  static function get_all_folders($db) {
    $folders = array();
  
    $result = $db->prepare("SELECT id, name FROM folders");
    $result->execute();
    $result->bind_result($id, $name);
    while ($result->fetch()) {
      $folders[$id] = $name;
    }
    $result->close();
    
    return $folders;
  }
  
  static function get_ownerID($folderID, $db) {
    $result = $db->prepare("SELECT ownerID FROM folders WHERE id = ? LIMIT 1");
    $result->bind_param('i', $folderID);
    $result->execute();
    $result->bind_result($ownerID);
    $result->store_result();
    $result->fetch();
    if ($result->num_rows == 0) {
      $ownerID = false;
    }    
    $result->close();
    
    return $ownerID;
  }
  
  

}