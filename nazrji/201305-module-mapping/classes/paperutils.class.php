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
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once $cfg_web_root . 'classes/rogostaticsingleton.class.php';

Class Paper_utils extends RogoStaticSingleton {
  public static $inst = NULL;
  public static $class_name = 'PaperUtils';

  /**
  * constructor
  */
  private function __construct() {}
}

Class PaperUtils {

  public function paper_exists($paperid, $db) {
    // Check for unique moduleID
    $exist = true;
    
    $result = $db->prepare("SELECT property_id FROM properties WHERE property_id = ? AND deleted IS NULL");
    $result->bind_param('i', $paperid);
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_paperid);
    $result->fetch();
    if ($result->num_rows == 0) {
      $exist = false;
    }
    $result->free_result();
    $result->close();

    return $exist;
  }

  /**
  * Add a question onto a paper
  *
  * @param $paperID ID of the paper to be used
  * @param $questionID ID of the question to be added
  * @param $screen_no number of the screen to add to
  * @param $display_pos the display position of the new question
  * @param $db Database connection
  */
  public function add_question($paperID, $questionID, $screen_no, $display_pos, $db) {
    $result = $db->prepare("INSERT INTO papers VALUES (NULL, ?, ?, ?, ?)");
    $result->bind_param('iiii', $paperID, $questionID, $screen_no, $display_pos);
    $result->execute();
    $result->close();
  }

  /**
  * Return the user ID of the paper owner
  *
  * @param $paperID the id of the paper or property_id
  * @param $db Database connection
  * @return integer
  */
  public function get_ownerID($paperID, $db) {
    $modules = array();
    $result = $db->prepare("SELECT paper_ownerID FROM properties WHERE property_id = ? LIMIT 1");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($paper_ownerID);
    $result->fetch();
    $result->close();

    return $paper_ownerID;
  }

  public function get_textual_feedback($paperID, $db, $direction = 'ASC') {
    $textual_feedback = array();
    $i = 1;

    $result = $db->prepare("SELECT boundary, msg FROM paper_feedback WHERE paperID = ? ORDER BY boundary $direction");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($boundary, $msg);
    while ($result->fetch()) {
      $textual_feedback[$i]['msg'] = $msg;
      $textual_feedback[$i]['boundary'] = $boundary;
      $i++;
    }
    $result->close();

    return $textual_feedback;
  }

  /**
  * Return a array of modules assigned to a paper
  *
  * @param $paperID the id of the paper or property_id
  * @param $db Database connection
  * @return array
  */
  public function get_modules($paperID, $db) {
    $modules = array();
    if ($paperID == -1) {
      return $modules;
    }
    $result = $db->prepare("SELECT idMod, moduleid FROM (modules, properties_modules) WHERE idMod = id AND property_id = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($idMod, $moduleid);
    while ($result->fetch()) {
      $modules[$idMod] = $moduleid;
    }
    $result->close();

    return $modules;
  }

  public function q_feedback_enabled($moduleIDs, $db) {
    $enabled = true;
    
    $module_list = implode(',', $moduleIDs);
  
    $result = $db->prepare("SELECT exam_q_feedback FROM modules WHERE id IN ($module_list)");
    $result->execute();
    $result->bind_result($exam_q_feedback);
    while ($result->fetch()) {
      if ($exam_q_feedback == 0) {
        $enabled = false;
      }
    }
    $result->close();

    return $enabled;
  }
  
  /**
  * Return a array of metadata pairs assigned to a paper
  *
  * @param $paperID the id of the paper or property_id
  * @param $db Database connection
  * @return array
  */
  public function get_metadata($paperID, $db) {
    $metadata = array();

    $result = $db->prepare("SELECT name, value FROM paper_metadata_security WHERE paperID = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($security_type, $security_value);
    $result->store_result();
    while ($result->fetch()) {
      $metadata[$security_type] = $security_value;
    }
    $result->close();

    return $metadata;
  }

  /**
  * updates the modules on a paper removes modules if the user has permission to do so and then adds in the new modules
  * @param $paper_modules an array of modules keyed on idMod
  * @param $paperID the id of the paper or property_id
  * @param $db Database connection
  * @param $userObject currently authenticated user
  * @return void
  */
  public function update_modules($paper_modules, $paperID, $db, $userObject) {
    $staff_modules = $userObject->get_staff_modules();
    if (count($staff_modules) < 0) {
      $user_modules = get_staff_modules($userObject->get_user_ID(), $db, $userObject->get_user_ID());
    }

    if (count($staff_modules) > 0) {
      if ($userObject->has_role('SysAdmin')) {
        $user_can_delete = ''; //no restrictions
      } else {
        $user_can_delete = "AND idMod IN (" . implode(',', array_keys($staff_modules)) . ")"; //users can only remove modules if they are on the team
      }

      $editProperties = $db->prepare("DELETE FROM properties_modules WHERE property_id = ? $user_can_delete");
      $editProperties->bind_param('i', $paperID);
      $editProperties->execute();
      $editProperties->close();
    }

    Paper_utils::add_modules($paper_modules, $paperID, $db);
  }

  /**
  * Add/delete internal and external reviewers to a paper
  * @param $old_list an array of the old reviewers
  * @param $new_list an array of the new reviewers
  * @param $type 'internal' or 'external' review type
  * @param $paperID the id of the paper or property_id
  * @param $db Database connection
  * @return void
  */
  public function update_reviewers($old_list, $new_list, $type, $paperID, $db) {
    $old_list = array_flip($old_list);
    $new_list = array_flip($new_list);

    foreach ($old_list as $oldID=>$value) {
      if (!isset($new_list[$oldID])) {
        $editProperties = $db->prepare("DELETE FROM properties_reviewers WHERE paperID = ? AND reviewerID = ? AND type = ?");
        $editProperties->bind_param('iis', $paperID, $oldID, $type);
        $editProperties->execute();
        $editProperties->close();
      }
    }

    foreach ($new_list as $newID => $value) {
      if (!isset($old_list[$newID])) {
        $editProperties = $db->prepare("INSERT INTO properties_reviewers VALUES(NULL, ?, ?, ?)");
        $editProperties->bind_param('iis', $paperID, $newID, $type);
        $editProperties->execute();
        $editProperties->close();
      }
    }
  }

  /**
  * Add modules to a paper ignoring duplicates
  * @param $paper_modules an array of modules keyed on idMod
  * @param $paperID the id of the paper or property_id
  * @param $db Database connection
  * @return void
  */
  public function add_modules($paper_modules, $paperID, $db) {
    $editProperties = $db->prepare("INSERT INTO properties_modules VALUES(?, ?) ON DUPLICATE KEY UPDATE idMod = idMod");
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
  * @param $db Database connection
  * @return void
  */
  public function remove_modules($paper_modules, $paperID, $db) {
    $remove = $db->prepare("DELETE FROM properties_modules WHERE property_id = ? and idMod = ?");
    foreach ($paper_modules as $idMod => $ModuleID) {
      $remove->bind_param('ii', $paperID, $idMod);
      $remove->execute();
    }
    $remove->close();
  }

  /**
  * Return the paper title (name).
  * @param $paperID the id of the paper or property_id
  * @param $db Database connection
  * @return $paper_title the name of the paper
  */
  public function get_title($paperID, $db) {
    $result = $db->prepare("SELECT paper_title FROM properties WHERE property_id = ? LIMIT 1");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($paper_title);
    $result->fetch();
    $result->close();

    if ($paper_title == '') {
      return false;
    }

    return $paper_title;
  }

  /**
  * Determine if a paper title (name) is unique - in the database already.
  * @param $title the title to be tested
  * @param $db Database connection
  * @return $unique true if the name does not already exist
  */
  public function is_paper_title_unique($title, $db) {
    $unique = true;
    $result = $db->prepare("SELECT property_id FROM properties WHERE paper_title = ? LIMIT 1");
    $result->bind_param('s', $title);
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_id);
    $rows_found = $result->num_rows;
    $result->free_result();
    $result->close();

    if ($rows_found > 0) {
      $unique = false;
    }

    return $unique;
  }

  /**
  * Delete a paper from rogo (N.B sets the deleted field we don't actuality delete the row form the papers table)
  * @param $paperID the id of the paper or property_id
  * @param $db Database connection
  * @return void
  */
  public function delete_paper($paperID, $db) {
    //delete the paper
    $update = $db->prepare("UPDATE properties SET deleted = NOW(), paper_ownerID = -1 WHERE property_id = ?");
    $update->bind_param('i', $paperID);
    $update->execute();
    $update->close();
  }

  /**
  * caculates the number of screens on a paper
  * @param $paperID the id of the paper or property_id
  * @param $db Database connection
  * return @int max
  */
  public function get_numder_of_screens($paperID, $db) {
    $no_screens = 0;
    $result = $db->prepare("SELECT max(screen) FROM papers WHERE paper = ? group by paper LIMIT 1");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    if($result->num_rows <= 0) {
      $result->close();
      return 0;
    } else {
      $result->bind_result($no_screens);
      $result->fetch();
      $result->close();
      return $no_screens;
    }
  }

  public function displayIcon($paper_type, $title, $initials, $surname, $locked,  $retired) {
    $paper_type = strval($paper_type);

    if ($retired != '') {
      $retired = '_retired';
    }

    if (isset($surname)) {
      $alt = "&#013;Author: $title $initials $surname";
    } else {
      $alt = '';
    }

    switch ($paper_type) {
      case '0':
        $html = "<img src=\"../artwork/formative" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '1':
        $html = "<img src=\"../artwork/progress" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '2':
        $html = "<img src=\"../artwork/summative" . $retired . $locked . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '3':
        $html = "<img src=\"../artwork/survey" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '4':
        $html = "<img src=\"../artwork/osce" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '5':
        $html = "<img src=\"../artwork/offline" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '6':
        $html = "<img src=\"../artwork/peer_review" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case 'objectives':
        $html = "<img src=\"../artwork/feedback_release_icon.png\" width=\"48\" height=\"48\" alt=\"Objectives Feedback\" />";
        break;
      case 'questions':
        $html = "<img src=\"../artwork/question_release_icon.png\" width=\"48\" height=\"48\" alt=\"Questions Feedback\" />";
        break;
    }
    return $html;
  }

  /**
   * Get the details of the papers that are currently available for the current user and lab
   * @param  array      $paper_display Reference to array in which to build details of available papers
   * @param  array      $types         Array of paper types to check for
   * @param  UserObject $userObj       The current user
   * @param  mysqli     $db            Database reference
   * @param  string     $exclude       Option ID of a paper to exclude from the check
   * @return integer                   The number of currently active papers
   */
  public function get_active_papers(&$paper_display, $types, $userObj, $db, $exclude = '') {
    $type_sql = '';
    foreach ($types as $type) {
      if ($type_sql != '') {
        $type_sql .= ' OR ';
      }
      $type_sql .= "paper_type='{$type}'";
    }

    $exclude_sql = '';
    if ($exclude != '') {
      $exclude_sql = ' AND property_id != ' . $exclude;
    }

    $paper_no = 0;
    $paper_query = $db->prepare("SELECT property_id, paper_type, crypt_name, paper_title, bidirectional, fullscreen, MAX(screen) AS max_screen, labs, calendar_year, password FROM (papers, properties) WHERE papers.paper=properties.property_id AND (labs != '' OR password != '') AND ({$type_sql}) AND deleted IS NULL AND start_date < DATE_ADD(NOW(),interval 15 minute) AND end_date > NOW() $exclude_sql GROUP BY paper");
    if ($db->error) {
      try {
        throw new Exception("MySQL error $db->error <br> ", $db->errno);
      } catch (Exception $e) {
        echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
        echo nl2br($e->getTraceAsString());
        exit();
      }
    }
    $paper_query->execute();
    $paper_query->store_result();
    $paper_query->bind_result($property_id, $paper_type, $crypt_name, $paper_title, $bidirectional, $fullscreen, $max_screen, $labs, $calendar_year, $password);
    while ($paper_query->fetch()) {
      if ($labs != '') {
        $machineOK = false;
        $labs = str_replace(",", " OR lab=", $labs);
        $lab_info = $db->query("SELECT address FROM ip_addresses WHERE address='" . NetworkUtils::get_ipaddress() . "' AND (lab=$labs)");
        if ($lab_info->num_rows > 0) $machineOK = true;
        $lab_info->close();
      } else {
        $machineOK = true;
      }
      if (strpos($userObj->get_username(), 'user') !== 0) {
        $moduleIDs = Paper_utils::get_modules($property_id, $db);
        if (count($moduleIDs) > 0) {
          $moduleOK = false;
          if ($calendar_year != '') {
            $cal_sql = "AND calendar_year = '" . $calendar_year . "'";
          } else {
            $cal_sql = '';
          }
          $module_in = implode(',', array_keys($moduleIDs));
          $moduleInfo = $db->prepare("SELECT userID FROM modules_student WHERE userID=? $cal_sql AND idMod IN ($module_in)");
          $moduleInfo->bind_param('i', $userObj->get_user_ID());
          $moduleInfo->execute();
          $moduleInfo->store_result();
          $moduleInfo->bind_result($tmp_userID);
          $moduleInfo->fetch();
          if ($moduleInfo->num_rows() > 0) $moduleOK = true;
          $moduleInfo->close();
        } else {
          $moduleOK = true;
        }
      } else {
        $moduleOK = true;
      }
      if ($machineOK == true and $moduleOK == true) {
        $paper_display[$paper_no]['id'] = $property_id;
        $paper_display[$paper_no]['paper_title'] = $paper_title;
        $paper_display[$paper_no]['crypt_name'] = $crypt_name;
        $paper_display[$paper_no]['paper_type'] = $paper_type;
        $paper_display[$paper_no]['max_screen'] = $max_screen;
        $paper_display[$paper_no]['bidirectional'] = $bidirectional;
        $paper_display[$paper_no]['password'] = $password;
        $paper_no++;
      }
    }
    $paper_query->close();

    return $paper_no;
  }

}