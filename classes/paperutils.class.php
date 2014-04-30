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
 * @copyright Copyright (c) 2014 The University of Nottingham
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

  /**
  * Parses a paper title and returns the academic year if it exists within the title
  *
  * @param string $paper_title - The name of the paper.
  * @return mixed - False = no academic year found in title, string = the academic year that was found.
  */
  public function academic_year_from_title($paper_title) {
		if (preg_match('/\d\d\d\d\/\d\d\d\d/', $paper_title, $matches) == 1) {
			$tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0], -2);
		} elseif (preg_match('/\d\d\d\d\s\/\s\d\d\d\d/', $paper_title, $matches) == 1) {
			$tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0], -2);
		} elseif (preg_match('/\d\d\d\d\/\d\d/', $paper_title, $matches) == 1) {
			$tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0], -2);
		} elseif (preg_match('/\d\d\/\d\d/', $paper_title, $matches) == 1) {
			$tmp_match = '20' . substr($matches[0],0,2) . '/' . (substr($matches[0],0,2) + 1);	
		} else {
			$tmp_match = false;
		}
		
		return $tmp_match;
	}

  /**
  * Checks to see if a non-deleted paper ID exists in the database.
  *
  * @param int $paperID 		- ID of the paper to be used
  * @param object $db				-	Database connection
	* @return bool - True = the paperID exists, False = the paper does not exist.
  */
  public function paper_exists($paperid, $db) {
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
  * @param int $paperID 		- ID of the paper to be used
  * @param int $questionID 	- ID of the question to be added
  * @param int $screen_no 	- Number of the screen to add to
  * @param int $display_pos	- The display position of the new question
  * @param object $db				-	Database connection
  */
  public function add_question($paperID, $questionID, $screen_no, $display_pos, $db) {
    $display_pos_free = false;

    $result = $db->prepare("SELECT p_id FROM papers WHERE paper = ? AND display_pos = ?");
    while (!$display_pos_free) {
      // Look up the maximum display_pos here for safety.
      $result->bind_param('ii', $property_id, $display_pos);
      $result->execute();
      $result->bind_result($p_id);
      $result->store_result();
      $result->fetch();
      if ($result->num_rows > 0) {
        $display_pos++;
      } else {
        $display_pos_free = true;
      }
    }
    $result->close();

    $result = $db->prepare("INSERT INTO papers VALUES (NULL, ?, ?, ?, ?)");
    $result->bind_param('iiii', $paperID, $questionID, $screen_no, $display_pos);
    $result->execute();
    $result->close();
  }

  /**
  * Return the user ID of the paper owner
  *
  * @param int $paperID - The id of the paper or property_id
  * @param object $db 	- Database connection
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
  * @param int $paperID - The id of the paper or property_id
  * @param object $db		- Database connection
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
    if (count($moduleIDs) == 0) {
      return false;
    }

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
  * Updates the modules on a paper. Removes modules if the user has permission to do so and then adds in the new modules.
  * @param array $paper_modules - An array of modules keyed on idMod
  * @param int $paperID 				- The id of the paper or property_id
  * @param object $db 					- Database connection
  * @param object $userObject 	- Currently authenticated user
  * @return void
  */
  public function update_modules($paper_modules, $paperID, $db, $userObject) {
    $staff_modules = $userObject->get_staff_modules();
    if (count($staff_modules) < 0) {
      $user_modules = get_staff_modules($userObject->get_user_ID(), $db, $userObject->get_user_ID());
    }

    if (count($staff_modules) > 0) {
      if ($userObject->has_role('SysAdmin')) {
        $user_can_delete = ''; // No restrictions
      } else {
        $user_can_delete = "AND idMod IN (" . implode(',', array_keys($staff_modules)) . ")"; // Users can only remove modules if they are on the team.
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
	*
  * @param array $old_list	- Array of the old reviewers
  * @param array $new_list	- Array of the new reviewers
  * @param string $type			- 'internal' or 'external' review type
  * @param integer $paperID	- ID of the paper or property_id
  * @param object $db				-  Database connection
	*
  * @return bool - True if the list of reviewers has changed
  */
  public function update_reviewers($old_list, $new_list, $type, $paperID, $db) {
    $has_changed = false;

    $old_list = array_flip($old_list);
    $new_list = array_flip($new_list);

    foreach ($old_list as $oldID=>$value) {
      if (!isset($new_list[$oldID])) {
        $editProperties = $db->prepare("DELETE FROM properties_reviewers WHERE paperID = ? AND reviewerID = ? AND type = ?");
        $editProperties->bind_param('iis', $paperID, $oldID, $type);
        $editProperties->execute();
        $editProperties->close();

        $has_changed = true;
      }
    }

    foreach ($new_list as $newID => $value) {
      if (!isset($old_list[$newID])) {
        $editProperties = $db->prepare("INSERT INTO properties_reviewers VALUES(NULL, ?, ?, ?)");
        $editProperties->bind_param('iis', $paperID, $newID, $type);
        $editProperties->execute();
        $editProperties->close();

        $has_changed = true;
      }
    }

    return $has_changed;
  }

  /**
  * Add modules to a paper ignoring duplicates
	*
  * @param array $paper_modules	- An array of modules keyed on idMod
  * @param int $paperID 				- The id of the paper or property_id
  * @param object $db						- Database connection
	*
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
  * Remove modules from a paper
	*
  * @param array $paper_modules - An array of modules keyed on idMod
  * @param int $paperID					- The id of the paper or property_id
  * @param object $db						- Database connection
	*
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
  * Delete a paper (Note: sets the deleted field we don't actuality delete the row form the papers table)
  * @param $paperID the id of the paper or property_id
  * @param $db Database connection
  * @return void
  */
  public function delete_paper($paperID, $db) {
    $update = $db->prepare("UPDATE properties SET deleted = NOW(), paper_ownerID = -1 WHERE property_id = ?");
    $update->bind_param('i', $paperID);
    $update->execute();
    $update->close();
  }

  public function displayIcon($paper_type, $title, $initials, $surname, $locked,  $retired) {
	  $configObj = Config::get_instance();

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
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/formative" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '1':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/progress" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '2':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/summative" . $retired . $locked . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '3':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/survey" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '4':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/osce" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '5':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/offline" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case '6':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/peer_review" . $retired . ".png\" width=\"48\" height=\"48\" alt=\"$alt\" />";
        break;
      case 'objectives':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/feedback_release_icon.png\" width=\"48\" height=\"48\" alt=\"Objectives Feedback\" />";
        break;
      case 'questions':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/question_release_icon.png\" width=\"48\" height=\"48\" alt=\"Questions Feedback\" />";
        break;
       default:
         var_dump($paper_type);
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
    $paper_query = $db->prepare("SELECT property_id, paper_type, crypt_name, paper_title, bidirectional, fullscreen, MAX(screen) AS max_screen, labs, calendar_year, password FROM (papers, properties) WHERE papers.paper = properties.property_id AND (labs != '' OR password != '') AND ({$type_sql}) AND deleted IS NULL AND start_date < DATE_ADD(NOW(),interval 15 minute) AND end_date > NOW() $exclude_sql GROUP BY paper");
    if ($db->error) {
      try {
        throw new Exception("MySQL error $db->error <br /> ", $db->errno);
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
        $lab_info = $db->query("SELECT address FROM client_identifiers WHERE address = '" . NetworkUtils::get_client_address() . "' AND (lab = $labs)");
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
          $moduleInfo = $db->prepare("SELECT userID FROM modules_student WHERE userID = ? $cal_sql AND idMod IN ($module_in)");
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