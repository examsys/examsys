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
* Utility class for date related functionality
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

Class RecycleBin {
	/**
	 * Determine what icon to show for the recycle bin
   * @param integer $userID ID of the current user
   * @param array $teams array of teams the current user is on
   * @param resource $db database connection
	 * @return string the relevant icon empty/fill
	 */
	static function get_recyclebin_icon($userID, $teams, $db)	{
    $recycle_bin_no = 0;
    
    if (count($teams) == 0) {
      $team_sql = '';
    } else {
      $team_sql = " OR moduleID REGEXP ('" . implode('|', $teams) . "')";
    }
    $stmt = $db->prepare("SELECT property_id FROM properties WHERE (paper_ownerID=?$team_sql) AND deleted IS NOT NULL LIMIT 1");
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $stmt->bind_result($no_deleted);
    $stmt->fetch();
    $stmt->close();
    $recycle_bin_no += $no_deleted;
    
    if (count($teams) == 0) {
      $team_sql = '';
    } else {
      $team_sql = " OR q_group REGEXP ('" . implode('|', $teams) . "')";
    }
    $stmt = $db->prepare("SELECT q_id FROM questions WHERE (ownerID=?$team_sql) AND deleted IS NOT NULL LIMIT 1");
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $stmt->bind_result($no_deleted);
    $stmt->fetch();
    $stmt->close();
    $recycle_bin_no += $no_deleted;
    
    if (count($teams) == 0) {
      $team_sql = '';
    } else {
      $team_sql = " OR team_name REGEXP ('" . implode('|', $teams) . "')";
    }
    $stmt = $db->prepare("SELECT id FROM folders WHERE (ownerID=?$team_sql) AND deleted IS NOT NULL LIMIT 1");
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $stmt->bind_result($no_deleted);
    $stmt->fetch();
    $stmt->close();
    $recycle_bin_no += $no_deleted;
		
    if ($recycle_bin_no == 0) {
      $icon = 'empty_bin.png';
    } else {
      $icon = 'full_bin.png';
    }
    
    return $icon;
	}
  
	/**
	 * Get a list of recycle bin contents for the current user
   * @param integer $userID ID of the current user
   * @param array $teams array of teams the current user is on
   * @param resource $db database connection
	 * @return array of recycle bin contents
	 */
  static function get_recyclebin_contents($userID, $teams, $db) {
    $recycle_bin = array();
  
    // Query the Papers tables.
    $i = 0;
    $stmt = $db->prepare("SELECT property_id AS id, paper_type, paper_title, DATE_FORMAT(deleted,'%Y%m%d%H%i') AS deleted FROM properties WHERE (paper_ownerID=? OR moduleID IN ('" . implode("','",$teams) . "')) AND deleted IS NOT NULL");
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $stmt->bind_result($id, $paper_type, $paper_title, $deleted);
    while ($stmt->fetch()) {
      $recycle_bin[$i]['id'] = $id;
      $recycle_bin[$i]['type'] = 'paper';
      $recycle_bin[$i]['name'] = $paper_title;
      $recycle_bin[$i]['deleted'] = $deleted;
      $recycle_bin[$i]['subtype'] = $paper_type;
      $i++;
    }
    $stmt->close();

    // Query the Questions tables.
    $stmt = $db->prepare("SELECT q_id AS id, q_type, leadin_plain, DATE_FORMAT(deleted,'%Y%m%d%H%i') AS deleted FROM questions WHERE (ownerID=? OR q_group IN ('" . implode("','",$teams) . "')) AND deleted IS NOT NULL");
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $stmt->bind_result($id, $q_type, $leadin_plain, $deleted);
    while ($stmt->fetch()) {
      $recycle_bin[$i]['id'] = $id;
      $recycle_bin[$i]['type'] = 'question';
      $recycle_bin[$i]['name'] = $leadin_plain;
      $recycle_bin[$i]['deleted'] = $deleted;
      $recycle_bin[$i]['subtype'] = $q_type;
      $i++;
    }
    $stmt->close();

    // Query the Folder tables.
    $stmt = $db->prepare("SELECT id, name, DATE_FORMAT(deleted,'%Y%m%d%H%i') AS deleted FROM folders WHERE (ownerID=? OR team_name IN ('" . implode("','",$teams) . "')) AND deleted IS NOT NULL");
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $stmt->bind_result($id, $name, $deleted);
    while ($stmt->fetch()) {
      $recycle_bin[$i]['id'] = $id;
      $recycle_bin[$i]['type'] = 'folder';
      $recycle_bin[$i]['name'] = str_replace(';','\\',$name);
      $recycle_bin[$i]['deleted'] = $deleted;
      $recycle_bin[$i]['subtype'] = '';
      $i++;
    }
    $stmt->close();
    
    return $recycle_bin;	
  }
  
}

?>