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
 * Utility class for paper related functionality
 *
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */


Class Paper_utils {

  /**
  * Return a array of modules assigned to a paper
  *
  * @param $paperID the id of the paper or property_id
  * @return array 
  */
  static function get_modules($paperID,$db) {
    $modules = array();
    $result = $db->prepare("SELECT idMod,moduleid FROM modules,properties_modules WHERE idMod = id AND  property_id = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($idMod, $moduleid);
    while($result->fetch()) {
      $modules[$idMod] = $moduleid;
    }
    $result->close();
    return $modules;
  } 

  /**
  * updates the modules on a paper removes modules if the user has permission to do so and then adds in the new modules
  * @param $paper_modules an array of modules keyed on idMod
  * @param $paperID the id of the paper or property_id
  * @return void 
  */
  static function update_modules($paper_modules, $paperID, $db) {
    global $userID, $userroles, $staff_modules; //these will come form the users object later

    if(count($staff_modules) < 0) {
      $user_modules = get_staff_modules($userID, $db);
    }

    if(count($staff_modules) > 0) {
      if(strpos($userroles,'SysAdmin')) {
        //sysadmin 
        $user_can_delete = ''; //no restrictions
      } else {
        $user_can_delete = "AND idMod IN (" . implode(',',array_keys($staff_modules)) . ")"; //users can only remove modules if they are on the team
      }

      $editProperties = $db->prepare("DELETE FROM properties_modules WHERE property_id = ? $user_can_delete");
      $editProperties->bind_param('i', $paperID);
      $editProperties->execute();
      $editProperties->close();
    }
    Paper_utils::add_modules($paper_modules, $paperID, $db);
  }

  /**
  * Add modules to a paper ignoring duplicates
  * @param $paper_modules an array of modules keyed on idMod
  * @param $paperID the id of the paper or property_id
  * @return void 
  */
  static function add_modules($paper_modules, $paperID, $db) {
    $editProperties = $db->prepare("INSERT INTO properties_modules VALUES(?,?) ON DUPLICATE KEY UPDATE idMod=idMod");
    foreach ($paper_modules as $idMod => $ModuleID) {
      $editProperties->bind_param('ii', $paperID, $idMod);
      $editProperties->execute();
    }
    $editProperties->close();
  }

  /**
  * remove modules from a paper 
  * @param $paper_modules an array of modules keyed on idMod
  * @param $paperID the id of the paper or property_id
  * @return void 
  */
  static function remove_modules($paper_modules, $paperID, $db) {
    $remove = $db->prepare("DELETE FROM properties_modules WHERE property_id = ? and idMod=?");
    foreach ($paper_modules as $idMod => $ModuleID) {
      $remove->bind_param('ii', $paperID, $idMod);
      $remove->execute();
    }
    $remove->close();
  }

  static function is_paper_title_unique($title, $db) {
    $unique = true;
    $result = $db->prepare("SELECT property_id FROM properties WHERE paper_title=? LIMIT 1");
    $result->bind_param('s', $title);
    $result->execute();  
    $result->store_result();
    $result->bind_result($tmp_id);
    $rows_found = $result->num_rows;
    $result->free_result();
    $result->close();
    if($rows_found > 0) {
      $unique = false;
    }
    return $unique;
  }

  /**
  * Delete a paper from rogo (N.B sets the deleted field we don't actuality delete the row form the papers table)
  * @param $paperID the id of the paper or property_id
  * @return void 
  */
  static function delete_paper($paperID, $db) {
    //delete the paper
    $update = $db->prepare("UPDATE properties SET deleted=NOW(), paper_ownerID=-1 WHERE property_id=?");
    $update->bind_param('i', $paperID);
    $update->execute();
    $update->close();
  }

}