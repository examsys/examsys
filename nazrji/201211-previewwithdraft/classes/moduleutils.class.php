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


Class module_utils {

  static function add_modules($moduleid, $fullname, $active, $schoolID, $vle_api, $sms_api, $selfEnroll, $peer, $external, $stdset, $mapping, $neg_marking, $ebel_grid_template, $db,$sms_import = 0) {

    if (module_utils::module_exists($moduleid, $db) !== false) {
      return false;
    }

    //dont let modules with no id to be created ;-)
    if($moduleid == '') {
      return false;
    }

    $checklist = '';
    if ($peer == true) $checklist .= ',peer';
    if ($external == true) $checklist .= ',external';
    if ($stdset == true) $checklist .= ',stdset';
    if ($mapping == true) $checklist .= ',mapping';
    $tmp_checklist = substr($checklist, 1);
    $result = $db->prepare("INSERT INTO modules VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    echo $db->error;
    $result->bind_param('ssisssiiii', $moduleid, $fullname, $active, $vle_api, $tmp_checklist, $sms_api, $selfEnroll, $schoolID, $neg_marking, $ebel_grid_template);
    $result->execute();
    $result->close();
    if ($db->errno != 0) {
      return false;
    }

    if ($sms_import == 1 and $sms_api != '') {
      $SMS = SmsUtils::GetSmsUtils();
      $SMS->update_module_enrolement($moduleid, $sms_api, $db);
    }

    return true;
  }

  static function module_exists($moduleid, $db) {
    // Check for unique moduleID
    $unique_moduleid = true;
    $result = $db->prepare("SELECT moduleid FROM modules WHERE moduleid=?");
    if ($db->error) {
      try {
        throw new Exception("MySQL error $db->error <br> Query:<br> ", $db->errno);
      } catch (Exception $e) {
        echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br >";
        echo nl2br($e->getTraceAsString());
      }
    }
    $result->bind_param('s', $moduleid);
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_moduleid);
    $result->fetch();
    if ($result->num_rows == 0) {
      $unique_moduleid = false;
    }
    $result->free_result();
    $result->close();

    return $unique_moduleid;
  }

  static function module_check_self_enrol($module_id, $db) {
    // returns false if not self enrol else returns needed data;
    $result = $db->prepare("SELECT fullname, school, active, selfenroll FROM modules, schools WHERE modules.schoolid=schools.id AND moduleid=?");
    $result->bind_param('s', $module_id);
    $result->execute();
    $result->store_result();
    $result->bind_result($fullname, $school, $active, $selfenroll);
    $result->fetch();
    if ($result->num_rows == 0) {
      $result->close();
      return false;
    }
    $result->close();

    return array($fullname, $school, $active, $selfenroll);
  }

  static function get_full_details($module_id, $db) {
    // returns false if not self enrol else returns needed data;
    $result = $db->prepare("SELECT fullname, school, active, selfenroll, checklist FROM modules, schools WHERE modules.schoolid=schools.id AND moduleid=?");
    $result->bind_param('s', $module_id);
    $result->execute();
    $result->store_result();
    $result->bind_result($fullname, $school, $active, $selfenroll, $checklist);
    $result->fetch();
    if ($result->num_rows == 0) {
      $result->close();
      return false;
    }
    $result->close();

    return array('fullname'=>$fullname, 'school'=>$school, 'active'=>$active, 'selfenroll'=>$selfenroll, 'checklist'=>$checklist);
  }
  
  static function get_idMod($module_id, $db) {
    $result = $db->prepare("SELECT id FROM modules WHERE moduleid=?");
    $result->bind_param('s', $module_id);
    $result->execute();
    $result->store_result();
    $result->bind_result($id);
    $result->fetch();
    if ($result->num_rows == 0) {
      $result->close();
      return false;
    }
    $result->close();
    return $id;
  }

  static function get_moduleID($idMod, $db) {
    $result = $db->prepare("SELECT moduleID FROM modules WHERE id=?");
    $result->bind_param('s', $idMod);
    $result->execute();
    $result->store_result();
    $result->bind_result($module_id);
    $result->fetch();
    if ($result->num_rows == 0) {
      $result->close();
      return false;
    }
    $result->close();
    return $module_id;
  }

}

?>