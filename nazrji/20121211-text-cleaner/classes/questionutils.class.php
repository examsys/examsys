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
* Utility class for question related functions
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/


Class QuestionUtils {

  /**
   * Get the leading for a give question ID
   * @param integer $q_id
   * @param resource $db
   * @return string The leadin
   */
  static function get_ownerID($q_id, $db) {
    $stmt = $db->prepare("SELECT ownerID FROM questions WHERE q_id=? LIMIT 1");
    $stmt->bind_param('i', $q_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($ownerID);
    $stmt->fetch();
    $stmt->close();
    
    return $ownerID;
  }

  /**
   * Get the leading for a give question ID
   * @param integer $q_id
   * @param resource $db
   * @return string The leadin
   */
  static function get_leadin($q_id, $db) {
    $stmt = $db->prepare("SELECT leadin FROM questions WHERE q_id=? LIMIT 1");
    $stmt->bind_param('i', $q_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($leadin);
    $stmt->fetch();
    $leadin = ($stmt->num_rows == 0) ? '' : $leadin;
    $stmt->close();
    
    return $leadin;
  }

  /**
   * Strip tags from the leading string (if it doesn't contain equations) and trim length
   * @param $leadin
   * @return string
   */
  static function clean_leadin($leadin) {
    if (strpos($leadin, 'class="mee"') === false AND strpos($leadin, 'class=mee') === false) {
      $leadin = strip_tags($leadin);                                     // No equation, strip all tags
      if (strlen($leadin) > 160) {
        $leadin = substr($leadin, 0, 160) . '...';
      }
    } else {
      $leadin = trim(str_replace('&nbsp;',' ', $leadin));
    }

    return $leadin;
  }

  /**
   * returns an array of id/keywords that the question is on 
   * @param intager $q_id the id of the questions
   * @param resource $db
   * @return array of keywords
   */
  static function get_keywords($q_id, $db) {
    $keywords = array();

    $stmt = $db->prepare("SELECT keywordID, keyword FROM keywords_question, keywords_user WHERE q_id=? and keywords_question.keywordID = keywords_user.id");
    $stmt->bind_param('i', $q_id);
    $stmt->execute();
    $stmt->bind_result($keywordID, $keyword);
    while($res = $stmt->fetch()) {
      $keywords[$keywordID] = $keyword;
    }
    $stmt->close();
    
    return $keywords;
  }

  /**
   * returns an array of modules/teams that the question is on 
   * @param intager $q_id the id of the questions
   * @param resource $db
   * @return array of modules keyed on idMod
   */
  static function get_modules($q_id, $db) {
    $modules = array();
    
    $stmt = $db->prepare("SELECT idMod, moduleID FROM questions_modules, modules WHERE q_id=? and questions_modules.idMod = modules.id");
    $stmt->bind_param('i', $q_id);
    $stmt->execute();
    $stmt->bind_result($idMod, $moduleID);
    while($res = $stmt->fetch()) {
      $modules[$idMod] = $moduleID;
    }
    $stmt->close();
    
    return $modules;
  }

  /**
  * Update the modules for a question bast on the modules that the papers it is part of are on 
  * @param $modules an array of modules keyed on idMod
  * @param $q_id the id of the questions
  * @return void 
  */
  static function update_modules_from_papers($q_id, $db) {

    $sql = <<<SQL
      SELECT DISTINCT idMod 
      FROM papers, properties, properties_modules 
      WHERE properties.property_id = properties_modules.property_id 
      AND properties.property_id=paper 
      AND question = ? 
      AND deleted is NULL 
SQL;
    $update = $db->prepare($sql);
    $update->bind_param('i', $q_id);
    $update->execute();
    $update->bind_result($tmp_idMod);
    $on_idMod = array();
    while($update->fetch()) {
      $on_idMod[$tmp_idMod] = $tmp_idMod;
    }
    $update->close();

    //questions may be on modules the current users is not in - should we exclude these from the delete
    $update = $db->prepare("DELETE FROM questions_modules WHERE q_id = ?");
    $update->bind_param('i', $q_id);
    $update->execute();
    $update->close();
    
    QuestionUtils::add_modules($on_idMod, $q_id, $db);

  }

  /**
  * updates the modules on a question removes modules if the user has permission to do so and then adds in the new modules
  * @param $modules an array of modules keyed on idMod
  * @param $q_id the id of the question
  * @return void 
  */
  static function update_modules($modules, $q_id, $db, $userObj) {
    global $REPLACEMEuserIDold, $DISABLEDuserroles, $staff_modules; //these will come form the users object later

    if($userObj->has_role('SysAdmin')) {
      //sysadmin 
      $user_can_delete = ''; //no restrictions
    } else {
      $user_can_delete = "AND idMod IN (" . implode(',',array_keys($userObj->get_staff_modules())) . ")"; //users can only remove modules if they are on the team
    }

    $editProperties = $db->prepare("DELETE FROM questions_modules WHERE q_id = ? $user_can_delete");
    $editProperties->bind_param('i', $q_id);
    $editProperties->execute();
    $editProperties->close();
    
    QuestionUtils::add_modules($modules, $q_id, $db);
  }

  /**
  * add modules to a question ignoring any duplicates  
  * @param $modules an array of modules keyed on idMod
  * @param $q_id the id of the question
  * @return void 
  */
  static function add_modules($modules, $q_id, $db) {
  
    $update = $db->prepare("INSERT INTO questions_modules VALUES(?, ?) ON DUPLICATE KEY UPDATE idMod = idMod");
    foreach ($modules as $idMod => $ModuleID) {
      $update->bind_param('ii', $q_id, $idMod);
      $update->execute();
    }
    $update->close();
  
  }

  /**
  * add keywords to a question  
  * @param $keywords an array of keywords keyed on IDs
  * @param $q_id the id of the question
  * @return void 
  */
  static function add_keywords($keywords, $q_id, $db) {
  
    $update = $db->prepare("INSERT INTO keywords_question VALUES (?, ?)");
    foreach ($keywords as $keywordID => $keyword) {
      $update->bind_param('ii', $q_id, $keywordID);
      $update->execute();
    }
    $update->close();
  
  }

  /**
  * remove a module from a question  
  * @param $idMod an array of modules to remove keyed on idMod
  * @param $q_id the id of the question or property_id
  * @return void 
  */
  static function remove_modules($modules, $q_id, $db) {
    $update = $db->prepare("DELETE FROM questions_modules WHERE q_id = ? AND idMod = ?");
    foreach ($modules as $idMod => $ModuleID) {
      $update->bind_param('ii', $q_id, $idMod);
      $update->execute();
    }
    $update->close();
  }

/**
  * remove a question from rogo (N.B sets the deleted field we don't actuality delete the row form the questions table)  
  * @param $idMod an array of modules to remove keyed on idMod
  * @param $q_id the id of the question or property_id
  * @return void 
  */
  static function delete_question($q_id, $db) {
    $delete = $db->prepare("UPDATE questions SET deleted=NOW() WHERE q_id=?");
    $delete->bind_param('i', $q_id);
    $delete->execute();
    $delete->close();
    //TODO:: If we delete a question should we remove it from any staff_modules?
  }

  static function lock_question($q_id, $db) {
    $lock = $db->prepare("UPDATE questions SET locked=NOW() WHERE q_id=? AND locked IS NULL");
    $lock->bind_param('i', $q_id);
    $lock->execute();
    $lock->close();
  }
}
?>