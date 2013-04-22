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

require_once $cfg_web_root . 'classes/rogostaticsingleton.class.php';

/**
 *
 * Wrapper class for old static style calls to module_utils::[Function]
 *
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */
Class module_utils extends RogoStaticSingleton {
  public static $inst = NULL;
  public static $class_name = 'module'; //name of the new dynamic class

  /**
  * constructor
  */
  private function __construct() {}

}

/**
 *
 * Utility class for module related functionality
 *
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */
Class module {

  /**
  * constructor
  */
  public function __construct() {}

  public function add_modules($moduleid, $fullname, $active, $schoolID, $vle_api, $sms_api, $selfEnroll, $peer, $external, $stdset, $mapping, $neg_marking, $ebel_grid_template, $db, $sms_import = 0, $timed_exams = 0, $exam_q_feedback = 1, $add_team_members = 1) {

    if ($moduleid == '' or $fullname == '' or $schoolID == '' or module_utils::module_exists($moduleid, $db) !== false) {
      return false;
    }

    //dont let modules with no id to be created ;-)
    if ($moduleid == '') {
      return false;
    }

    $checklist = '';
    if ($peer == true) $checklist .= ',peer';
    if ($external == true) $checklist .= ',external';
    if ($stdset == true) $checklist .= ',stdset';
    if ($mapping == true) $checklist .= ',mapping';
    $tmp_checklist = substr($checklist, 1);
    
    $result = $db->prepare("INSERT INTO modules VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?)");
    $result->bind_param('ssisssiiiiiii', $moduleid, $fullname, $active, $vle_api, $tmp_checklist, $sms_api, $selfEnroll, $schoolID, $neg_marking, $ebel_grid_template, $timed_exams, $exam_q_feedback, $add_team_members);
    $result->execute();
    $result->close();
    if ($db->errno != 0) {
      return false;
    }
    
    $idMod = $db->insert_id;

    if ($sms_import == 1 and $sms_api != '') {
      $SMS = SmsUtils::GetSmsUtils();
      $SMS->update_module_enrolement($moduleid, $idMod, $sms_api, $db);
    }

    return $idMod;
  }

  public function module_exists($moduleid, $db) {
    // Check for unique moduleID
    $exists = true;
    
    $result = $db->prepare("SELECT moduleid FROM modules WHERE moduleid = ? AND mod_deleted IS NULL");
    $result->bind_param('s', $moduleid);
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_moduleid);
    $result->fetch();
    if ($result->num_rows == 0) {
      $exists = false;
    }
    $result->free_result();
    $result->close();

    return $exists;
  }

  public function get_full_details_by_name($modID, $db) {
    $moduleid = self::get_idMod($modID, $db);
    if ($moduleid === false) {
      return false;
    }

    return self::get_full_details_by_ID($moduleid, $db);
  }

  public function get_full_details_by_ID($modID, $db) {
    // returns false if not self enrol else returns needed data;
    $result = $db->prepare("SELECT moduleid, fullname, school, active, selfenroll, checklist, timed_exams, exam_q_feedback, add_team_members FROM modules, schools WHERE modules.schoolid = schools.id AND modules.id = ? AND mod_deleted IS NULL");
    if ($db->error) {
      try {
        throw new Exception("MySQL error $db->error <br /> Query:<br /> $query", $db->errno);
      }
      catch (Exception $e) {
        echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
        echo nl2br($e->getTraceAsString());
      }
    }
    $result->bind_param('i', $modID);
    $result->execute();
    $result->store_result();
    $result->bind_result($moduleid, $fullname, $school, $active, $selfenroll, $checklist, $timed_exams, $exam_q_feedback, $add_team_members);
    $result->fetch();
    if ($result->num_rows == 0) {
      $result->close();
      return false;
    }
    $result->close();

    return array('moduleid'=>$moduleid, 'fullname'=>$fullname, 'school'=>$school, 'active'=>$active, 'selfenroll'=>$selfenroll, 'checklist'=>$checklist, 'timed_exams'=>$timed_exams, 'exam_q_feedback'=>$exam_q_feedback, 'add_team_members'=>$add_team_members);
  }

  public function is_allowed_add_team_members_by_name($modID, $db) {
    $moduleid = self::get_idMod($modID, $db);
    if ($moduleid === false) {
      return false;
    }

    return self::is_allowed_add_team_members_by_id($moduleid, $db);
  }

  public function is_allowed_add_team_members_by_id($modID, $db) {
    $data = self::get_full_details_by_ID($modID, $db);
    if ($data === false) {
      return false;
    }
    if ($data['add_team_members'] == 0) {
      return false;
    }

    return true;
  }

  public function get_moduleid_from_id($modID, $db) {
    $modID = intval($modID);

    $result = $db->prepare("SELECT moduleid FROM modules WHERE id = ? AND mod_deleted IS NULL");
    $result->bind_param('i', $modID);
    $result->execute();
    $result->store_result();
    $result->bind_result($moduleid);
    $result->fetch();
    if ($result->num_rows == 0) {
      $result->close();
      return false;
    }
    $result->close();

    return $moduleid;
  }
  
  public function get_moduleID($idMod, $db) {
    $result = $db->prepare("SELECT moduleID FROM modules WHERE id = ? AND mod_deleted IS NULL");
    $result->bind_param('i', $idMod);
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

  public function get_idMod($module_id, $db) {
    if (is_array($module_id)) {
      $ids = array();

      $sql = implode("','", $module_id);
      $sql = str_replace("',' ", "','", $sql);

      $result = $db->prepare("SELECT id FROM modules WHERE moduleid IN ('$sql') AND mod_deleted IS NULL");
      $result->execute();
      $result->store_result();
      $result->bind_result($id);
      while ($result->fetch()) {
        $ids[] = $id;
      }
      $result->close();
      
      if (count($ids) == 0) {
        return false;
      }      
      return $ids;
    } else {
      $result = $db->prepare("SELECT id FROM modules WHERE moduleid = ? AND mod_deleted IS NULL");
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
  }

  public function get_module_list_by_id($db) {
    $modules = array();
    
    $result = $db->prepare("SELECT id, moduleid, fullname FROM modules WHERE mod_deleted IS NULL");
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $moduleid, $fullname);
    while ($result->fetch()) {
      $modules[$id]['code'] = $moduleid;      
      $modules[$id]['name'] = $fullname;      
    }
    $result->close();
    
    return $modules;
  }
  
  public function delete_module($idMod, $db) {
    $result = $db->prepare("UPDATE modules SET mod_deleted = NOW() WHERE id = ?");
    $result->bind_param('i', $idMod);
    $result->execute();  
    $result->close();
  }

  public function modules_allow_timing($module_ids, $db) {
    // Only allow timing if ALL the modules of the paper allow
    $mod_id_list = implode(',', $module_ids);
    $stmt = $db->prepare("SELECT id FROM modules WHERE id IN ($mod_id_list) AND timed_exams = 0");
    $stmt->execute();
    $stmt->store_result();
    $allow_timing = ($stmt->num_rows === 0);
    $stmt->close();

    return $allow_timing;
  }
}

?>