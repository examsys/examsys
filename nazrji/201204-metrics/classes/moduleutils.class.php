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
* Utility class for installer related functionality
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/


Class ModuleUtils {

  static function addModules($moduleid, $fullname, $active, $schoolID, $vle_api, $sms_api, $selfEnroll, $peer, $external, $stdset, $mapping, $neg_marking, $ebel_grid_template, $db) {
    
    if (ModuleUtils::moduleExists($moduleid,$db) === false) {
      return false;
    }
    
    $checklist = '';
    if ($peer == true)      $checklist .= ',peer';
    if ($external == true)  $checklist .= ',external';
    if ($stdset == true)    $checklist .= ',stdset';
    if ($mapping == true)   $checklist .= ',mapping';
    $tmp_checklist = substr($checklist,1);
    $result = $db->prepare( "INSERT INTO modules VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)" );
    echo $db->error;
    $result->bind_param('ssiissiiii', $moduleid, $fullname, $active, $vle_api, $tmp_checklist, $sms_api, $selfEnroll, $schoolID, $neg_marking, $ebel_grid_template);
    $result->execute();
    $result->close();
    if($db->errno != 0) {
      return false;
    }
    
    return true;
  }
  
  static function moduleExists($moduleid, $db) {
    // Check for unique moduleID
    $unique_moduleid = false;
    $result = $db->prepare("SELECT moduleid FROM modules WHERE moduleid=?");
    $result->bind_param('s', $moduleid);
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_moduleid);
    $result->fetch();
    if ($result->num_rows == 0) {
      $unique_moduleid = true;
    }
    $result->free_result();
    $result->close();
    
    return $unique_moduleid;
  }
  
}
?>