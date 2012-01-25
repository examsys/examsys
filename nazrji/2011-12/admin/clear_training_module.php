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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/sysadmin_auth.inc';

  function stripTrainModule($module_string) {
    $new_modules = array();
    $old_modules = explode(',',$module_string);
    foreach($old_modules as $old_module) {
      if ($old_module != 'TRAIN') $new_modules[] = $old_module;
    }
    return implode(',',$new_modules);
  }

  // Clear the TRAIN team
  $update = $mysqli->prepare("DELETE FROM teams WHERE name='TRAIN'");
  $update->execute();
  $update->close();
  
  // Get all the papers on the TRAIN team
  $result = $mysqli->prepare("SELECT property_id, moduleID FROM properties WHERE moduleID like '%TRAIN%'");
  $result->execute();
  $result->store_result();
  $result->bind_result($paperID, $moduleID);
  while ($result->fetch()) {
    if ($moduleID == 'TRAIN') {
      // Paper only on the TRAIN module
      $q_result = $mysqli->prepare("SELECT question FROM papers WHERE paper=?");
      $q_result->bind_param('i', $paperID);
      $q_result->execute();
      $q_result->store_result();
      $q_result->bind_result($questionID);
      while ($q_result->fetch()) {
        // Check the question isn't used on any other papers
        $check = $mysqli->prepare("SELECT question FROM papers WHERE question=?");
        $check->bind_param('i', $questionID);
        $check->execute();
        $check->store_result();
        $check->bind_result($questionID);
        $check->fetch();
        if ($check->num_rows > 0) {
          $update = $mysqli->prepare("UPDATE questions SET deleted=NOW(), q_group='', ownerID=-1 WHERE q_id=$questionID");
          $update->execute();
          $update->close();
        }
        $check->close();
      }
      $q_result->close();
      $update = $mysqli->prepare("UPDATE properties SET deleted=NOW(), moduleID='', paper_ownerID=-1 WHERE property_id=$paperID");
      $update->execute();
      $update->close();
    } else {
      // Paper on other modules as well
      $update = $mysqli->prepare("UPDATE properties SET moduleID='" . stripTrainModule($moduleID) . "' WHERE property_id=$paperID");
      $update->execute();
      $update->close();
      
      $q_result = $mysqli->prepare("SELECT question FROM papers WHERE paper=?");
      $q_result->bind_param('i', $paperID);
      $q_result->execute();
      $q_result->store_result();
      $q_result->bind_result($questionID);
      
      while ($q_result->fetch()) {
        $check = $mysqli->prepare("SELECT q_group FROM questions WHERE q_id=? LIMIT 1");
        $check->bind_param('i', $questionID);
        $check->execute();
        $check->store_result();
        $check->bind_result($q_group);
        $check->fetch();
        
        $update = $mysqli->prepare("UPDATE questions SET q_group='" . stripTrainModule($q_group) . "' WHERE q_id=$questionID");
        $update->execute();
        $update->close();
        $check->close();
      }
    }
  }
  $result->close();
  
  $mysqli->close();
  header("location: index.php");
?>