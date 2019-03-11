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
Class PaperUtils {
  
  /**
  * Records an access to a paper in recent_papers table.
  *
  * @param int $userID  - ID of the user accessing the paper.
  * @param int $paperID - ID of the paper.
  * @param object $db   - Database object.
  */
  public function log_hit($userID, $paperID, $db) {
    // Log the hit in recent_papers.
    $result = $db->prepare("INSERT INTO recent_papers (userID, paperID, accessed) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE accessed = NOW()");
    $result->bind_param('ii', $userID, $paperID);
    $result->execute();
    $result->close();    
  }

  /**
  * Parses a paper title and returns the academic year if it exists within the title
  *
  * @param string $paper_title - The name of the paper.
  * @return mixed - False = no academic year found in title, string = the academic year that was found.
  */
  public function academic_year_from_title($paper_title) {
		if (preg_match('/\d\d\d\d\D\d\d\d\d/', $paper_title, $matches) == 1) {
			$tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0], -2);
		} elseif (preg_match('/\d\d\d\d\s\D\s\d\d\d\d/', $paper_title, $matches) == 1) {
			$tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0], -2);
		} elseif (preg_match('/\d\d\d\d\D\d\d/', $paper_title, $matches) == 1) {
			$tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0], -2);
		} elseif (preg_match('/\d\d\D\d\d/', $paper_title, $matches) == 1) {
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

   /**
   * Function to count the number of un-assigned papers for a user
   *
   * @param int $user_id User ID
   * @param mysqli $db Database link object
   * @return int $count the number of unassigned papers
   */
  public function count_unassigned_papers($user_id, $db) {
    $query = $db->prepare("SELECT count(properties.property_id)"
      . " FROM properties"
      . " LEFT JOIN properties_modules ON properties.property_id=properties_modules.property_id"
      . " WHERE paper_ownerID = ?"
      . " AND idMod is NULL"
      . " AND deleted IS NULL");
    $query->bind_param('i', $user_id);
    $query->execute();
    $query->bind_result($count);
    $query->fetch();
    $query->close();

    return $count;
  }

  /**
   * Function to count the number of un-assigned questions for a user
   *
   * @param int $user_id User ID
   * @param mysqli $db Database link object
   * @return int $count the number of unassigned questions
   */
  public function count_unassigned_questions($user_id, $db) {
    $query = $db->prepare("SELECT count(questions.q_id)"
      . " FROM questions"
      . " LEFT JOIN questions_modules ON questions.q_id=questions_modules.q_id"
      . " WHERE questions.ownerID = ?"
      . " AND questions_modules.idMod is NULL"
      . " AND questions.deleted IS NULL");
    $query->bind_param('i', $user_id);
    $query->execute();
    $query->bind_result($count);
    $query->fetch();
    $query->close();

    return $count;
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
    Paper_utils::remove_modules($paper_modules, $paperID, $db, $userObject, "all");
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

    $new_list = array_flip($new_list);

    foreach ($old_list as $oldID => $value) {
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
  * @param int $paperID - The id of the paper or property_id
  * @param object $db - Database connection
  * @param object $userObject - user object
  * @param strting $modulefilter - 'all' or a specfic module
  * @return void
  */
  public function remove_modules($paper_modules, $paperID, $db, $userObject, $modulefilter = "") {
      
    // Non sysadmin users can only remove modules if they are on the team. Sysadmins have no restrictions.
    if (!$userObject->has_role('SysAdmin')) {
      $staff_modules = $userObject->get_staff_modules();
      if (count($staff_modules) > 0) {
        $permission = "AND idMod IN (" . implode(',', array_keys($staff_modules)) . ")"; 
      }
    } else {
        $permission = "";
    }
    
    // Are we removing all of the associated modules or a specifc one.
    if ($modulefilter == "all") {
        $modules = "";
    } else {
        $modules = "AND idMod = ?";
    }
    
    $remove = $db->prepare("DELETE FROM properties_modules WHERE property_id = ? $modules $permission");
    
    if ($modulefilter == "all") {
      $remove->bind_param('i', $paperID);
    } else {
      foreach ($paper_modules as $idMod => $ModuleID) {
        $remove->bind_param('ii', $paperID, $idMod);
      }
    }
    $remove->execute();
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
  * @param $owner the owner we want to set the deleted paper to
  * @param $db Database connection
  * @return void
  */
  public function delete_paper($paperID, $owner, $db) {
    $configObject = \Config::get_instance();
    $assessment = new assessment($db, $configObject);
    $now = date("Y-m-d H:i:s");
    $details = Paper_utils::get_paper_properties($paperID, $db);
    $papertitle = $details['title'] . ' [deleted ' .  date($configObject->get('cfg_short_date_php')) . ']';
    $update_params = array(
      'paper_title' => array('s',$papertitle),
      'deleted' => array('s', $now),
      'paper_ownerID' => array('i', $owner)
    );
    return $assessment->db_update_assessment($paperID, $update_params);
  }

  public function type_to_name($type, $string) {
      switch ($type) {
        case '0':
          $name = $string['formative self-assessments'];
          break;
        case '1':
          $name = $string['progress tests'];
          break;
        case '2':
          $name = $string['summative exams'];
          break;
        case '3':
          $name = $string['surveys'];
          break;
        case '4':
          $name = $string['osce stations'];
          break;
        case '5':
          $name = $string['offline papers'];
          break;
        case '6':
          $name = $string['peer review'];
          break;
      }
      
      return $name;
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
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/formative" . $retired . ".png\" alt=\"$alt\" />";
        break;
      case '1':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/progress" . $retired . ".png\" alt=\"$alt\" />";
        break;
      case '2':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/summative" . $retired . $locked . ".png\" alt=\"$alt\" />";
        break;
      case '3':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/survey" . $retired . ".png\" alt=\"$alt\" />";
        break;
      case '4':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/osce" . $retired . ".png\" alt=\"$alt\" />";
        break;
      case '5':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/offline" . $retired . ".png\" alt=\"$alt\" />";
        break;
      case '6':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/peer_review" . $retired . ".png\" alt=\"$alt\" />";
        break;
      case 'objectives':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/feedback_release_icon.png\" alt=\"Objectives Feedback\" />";
        break;
      case 'questions':
        $html = "<img src=\"" . $configObj->get('cfg_root_path') . "/artwork/question_release_icon.png\" alt=\"Questions Feedback\" />";
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
    $paper_query = $db->prepare("SELECT property_id, paper_type, crypt_name, paper_title, bidirectional, MAX(screen) AS max_screen, labs, calendar_year, password, completed
      FROM (papers, properties) LEFT JOIN log_metadata ON properties.property_id = log_metadata.paperID AND userID = ?
      WHERE papers.paper = properties.property_id AND (labs != '' OR password != '') AND ({$type_sql}) AND deleted IS NULL AND start_date < DATE_ADD(NOW(),interval 15 minute)
      AND end_date > NOW() $exclude_sql GROUP BY calendar_year, paper_type, paper_title, property_id, completed, crypt_name, paper_type, labs, bidirectional, password");
    $paper_query->bind_param('i', $userObj->get_user_ID());
    $paper_query->execute();
    $paper_query->store_result();
    $paper_query->bind_result($property_id, $paper_type, $crypt_name, $paper_title, $bidirectional, $max_screen, $labs, $calendar_year, $password, $completed);
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
        $paper_display[$paper_no]['completed'] = $completed;
        $paper_no++;
      }
    }
    $paper_query->close();

    return $paper_no;
  }
  
  /**
   * Determins if there is an interactive question (e.g. image hotspot, labelling,
   * area) on a particular screen of a paper. Speeds system up if not loading
   * unnecessary HTML5/Flash include files.
   * @param  array      $screen_data Array of screen/question information
   * @param  array      $screen      The screen number to check
   * @return bool       True = HTML5 or Flash neeed, False=no interactive questions found.
   */
  function need_interactiveQ($screen_data, $screen, $db) {
    $interactive = false;
    $checktypes = array('hotspot', 'labelling', 'area');
    if (isset($screen_data[$screen])) {
      foreach ($screen_data[$screen] as $question_part) {
        if (in_array($question_part[0], $checktypes)) {
          $interactive = true;
        } else if ($question_part[0] == 'random') {
          $options = random_utils::get_random_qids_for_question($question_part[1], $db);
          $types = array();
          foreach ($options as $opt) {
              $qtype = QuestionUtils::get_question_type($opt, $db);
              $types[] = $qtype;
          }
          foreach ($types as $t) {
            if (in_array($t, $checktypes)) {
              $interactive = true;
              break;
            }
          }
        } else if ($question_part[0] == 'keyword_based') {
          $options = QuestionUtils::get_options_text($question_part[1], $db);
          foreach ($options as $opt) {
            $keywords = keyword_utils::get_keyword_questions($opt, $db);
            $types = array();
            foreach ($keywords as $key) {
              $qtype = QuestionUtils::get_question_type($key, $db);
              $types[] = $qtype;
            }
          }
          foreach ($types as $t) {
            if (in_array($t, $checktypes)) {
              $interactive = true;
              break;
            }
          }
        }
      }
    }

    return $interactive;
  }

  /**
   * Creates a list of the last 10 papers accessed by a member of staff.
   * @param int $userID - ID of the user we want last 10 papers for.
   * @param object $db  - Database connection.
   * @return array      - List of 10 last papers keyed by paperID.
   */
  public function get_recent($userID, $db) {
    $recent = array();

    $result = $db->prepare("SELECT paperID, paper_title FROM (recent_papers, properties) WHERE userID = ? AND recent_papers.paperID = properties.property_id ORDER BY accessed DESC LIMIT 10");
    $result->bind_param('i', $userID);
    $result->execute();
    $result->bind_result($paperID, $paper_title);
    $result->store_result();
    while ($result->fetch()) {
      $recent[$paperID] = $paper_title;
    }
    $result->close();    
    
    return $recent;
  }

  /**
   * Returns the number of screens in a paper.
   *
   * @param int $paperID - id of paper.
   * @param object $db  - Database connection.
   * @return int - number of screens in paper.
   */
  public function get_num_screens($paperID, $db) {

    $result = $db->prepare("SELECT MAX(screen) from papers where paper = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($maxscreen);
    $result->fetch();
    $result->close();

    return $maxscreen;
  }
  
  /**
  * Check if the paper has been taken
  * @param integer $id - paper id
  * @param mysqli $db 
  * @return bool
  */
  static function paper_taken($id, $db) {
    $result = $db->prepare("SELECT NULL FROM log_metadata WHERE paperID = ?
        UNION SELECT NULL FROM log4_overall WHERE q_paper = ?");
    $result->bind_param('ii', $id, $id);
    $result->execute();
    $result->store_result();
    if ($result->num_rows > 0) {
        $result->close();
        return true;
    }
    $result->close();
    return false;
  }
  
  /**
   * Checks is a paper is available in the specidiced lab
   * Only Summative and progressive papers checked.
   * @param integer $lab - lab id
   * @param mysqli $db
   * @return bool true if paper available 
   */
  static function paper_available_in_lab_now($lab, $db) {
    $results = $db->prepare("SELECT NULL 
      FROM properties 
      WHERE start_date < DATE_ADD(NOW(), interval 15 minute)
      AND end_date > NOW()
      AND paper_type IN ('1','2')
      AND labs REGEXP ?");
    $lab_regexp = "(^|,)(" . $lab . ")(,|$)";
    $results->bind_param('s', $lab_regexp);
    $results->execute();
    $results->store_result();
    if ($results->num_rows() > 0) {
      $results->close();
      return true;
    }
    $results->close();
    return false;
  }

  /**
  * Get the paper type of the paper.
  * @param integer $id - paper id
  * @param mysqli $db 
  * @return integer|bool paper type or false on error
  */
  static function get_paper_type($id, $db) {
    $result = $db->prepare("SELECT paper_type FROM properties WHERE property_id = ?");
    $result->bind_param('i', $id);
    $result->execute();
    $result->bind_result($paper_type);
    $result->fetch();
    if ($db->errno != 0) {
        $result->close();
        return false;
    }
    $result->close();
    return $paper_type;
  }
  
  /**
   * Delete a paper from the database
   * @param integer $id paper id
   * @param mysqli $db 
   * @return bool true on success, false otherwise
   */
  static function complete_delete_paper($id, $db) {
    $result = $db->prepare("DELETE FROM properties WHERE property_id = ?");
    $result->bind_param('i', $id);
    $result->execute();
    if ($db->errno != 0) {
        return false;
    }
    $result->close();
    // We should also delete any entries in properties_modules otherwise they will be orphaned.
    $result = $db->prepare("DELETE FROM properties_modules WHERE property_id = ?");
    $result->bind_param('i', $id);
    $result->execute();
    if ($db->errno != 0) {
        return false;
    }
    $result->close();
    return true;
  }
  
  /**
   * Get paper properties
   * @param integer $id paper id
   * @param mysqli $db 
   * @return array|bool array of paper details or false on error
   */
  static function get_paper_properties($id, $db) {
    $result = $db->prepare("SELECT 
      paper_title,
      paper_type,
      paper_ownerID,
      calendar_year,
      start_date,
      end_date,
      labs,
      exam_duration,
      timezone,
      externalid,
      externalsys,
      paper_prologue,
      paper_postscript,
      bgcolor,
      fgcolor,
      themecolor,
      labelcolor,
      fullscreen,
      marking,
      bidirectional,
      pass_mark,
      distinction_mark,
      folder,
      rubric,
      calculator,
      random_mark, 
      total_mark,
      display_correct_answer,
      display_question_mark,
      display_students_response,
      display_feedback,
      hide_if_unanswered,
      external_review_deadline,
      internal_review_deadline,
      sound_demo,
      password
    FROM properties WHERE property_id = ?");
    $result->bind_param('i', $id);
    $result->execute();
    $result->bind_result($title,
      $type,
      $owner,
      $session,
      $startdatetime,
      $enddatetime,
      $labs,
      $duration, 
      $timezone, 
      $externalid, 
      $externalsys, 
      $paper_prologue,
      $paper_postscript,
      $bgcolor,
      $fgcolor,
      $themecolor,
      $labelcolor,
      $fullscreen,
      $marking,
      $bidirectional,
      $pass_mark,
      $distinction_mark,
      $folder,
      $rubric,
      $calculator,
      $random_mark, 
      $total_mark,
      $display_correct_answer,
      $display_question_mark,
      $display_students_response,
      $display_feedback,
      $hide_if_unanswered,
      $external_review_deadline,
      $internal_review_deadline,
      $sound_demo,
      $password);
    $result->fetch();
    if ($db->errno != 0) {
        $result->close();
        return false;
    }
    $result->close();
    $details = array('title' => $title,
                    'type' => $type,
                    'owner' => $owner,
                    'session' => $session,
                    'startdatetime' => $startdatetime,
                    'enddatetime' => $enddatetime,
                    'labs' => $labs,
                    'duration' => $duration,
                    'timezone' => $timezone,
                    'externalid' => $externalid,
                    'externalsys' => $externalsys,
                    'paper_prologue' => $paper_prologue,
                    'paper_postscript' => $paper_postscript,
                    'bgcolor' => $bgcolor,
                    'fgcolor' => $fgcolor,
                    'themecolor' => $themecolor,
                    'labelcolor' => $labelcolor,
                    'fullscreen' => $fullscreen,
                    'marking' => $marking,
                    'bidirectional' => $bidirectional,
                    'pass_mark' => $pass_mark,
                    'distinction_mark' => $distinction_mark,
                    'folder' => $folder,
                    'rubric' => $rubric,
                    'calculator' => $calculator,
                    'random_mark' => $random_mark, 
                    'total_mark' => $total_mark,
                    'display_correct_answer' => $display_correct_answer,
                    'display_question_mark' => $display_question_mark,
                    'display_students_response' => $display_students_response,
                    'display_feedback' => $display_feedback,
                    'hide_if_unanswered' => $hide_if_unanswered,
                    'external_review_deadline' => $external_review_deadline,
                    'internal_review_deadline' => $internal_review_deadline,
                    'sound_demo' => $sound_demo,
                    'password' => $password
                    );
    return $details;
  }

  /**
   * Get internal rogo properties id from external id
   * @param string $externalid external system id
   * @param string $externalsys external system source
   * @param mysqli $db db connection
   * @return integer|bool rogo id or false on error
   */
  static public function get_id_from_externalid($externalid, $externalsys, $db) {
    $result = $db->prepare("SELECT property_id FROM properties WHERE externalid = ? AND externalsys = ? AND deleted IS NULL");
    $result->bind_param('ss', $externalid, $externalsys);
    $result->execute();
    $result->store_result();
    $result->bind_result($id);
    $result->fetch();
    if ($result->num_rows == 0) {
      $paperid = false;
    } else {
      $paperid = $id;
    }
    $result->close();
    return $paperid;
  }
  
  /**
   * Get papers running in academic session
   * @param integer $session academic session
   * @param string $type paper type
   * @param mysqli $db db connection
   * @return array rogo ids
   */
  static public function get_papers_by_session($session, $type, $db) {
    $paperids = array();
    $result = $db->prepare("SELECT property_id FROM properties WHERE calendar_year = ? AND paper_type = ? AND deleted IS NULL");
    $result->bind_param('is', $session, $type);
    $result->execute();
    $result->store_result();
    $result->bind_result($id);
    while ($result->fetch()) {
      $paperids[] = $id;
    }
    $result->close();
    return $paperids;
  }
  
  /**
   * Get papers finalised in specific year
   * @param integer $year year
   * @param string $papertype type of paper
   * @param mysqli $db db connection
   * @return array list of ids of papers finialised in supplied year
   */
  static public function get_finalised_papers($year, $papertype, $db) {
    $papers = array();
    $result = $db->prepare("SELECT paperid
      FROM gradebook_paper, properties
      WHERE gradebook_paper.paperid = properties.property_id
      AND properties.paper_type = ? AND DATE_FORMAT(timestamp, '%Y') = ?");
    $result->bind_param('si', $papertype, $year);
    $result->execute();
    $result->bind_result($paperid);
    while ($result->fetch()) {
      $papers[] = $paperid;
    }
    $result->close();
    return $papers;
  }
  
  /**
   * Get a list of papers available to the logged in user
   *
   * @param object $userObject logged in user object
   * @param string $order query order string
   * @param string $direction query order direction string
   * @param integer $type paper type
   * @param integer $teamid logged in users team
   * @return array list of papers available to logged in user
   */
  static public function get_available_papers($userObject, $order, $direction, $type = null, $teamid = null) {
    // Return empty list if type and team not provided.
    if (is_null($type) and is_null($teamid)) {
        return array();
    }
    $configObject = \Config::get_instance();
    $mysqli = $configObject->db;
    $paper_details = array();
    if (!is_null($type)) {
      $user_teams = $userObject->get_staff_modules();
      $module_id_list = implode(',', array_keys($user_teams));
      $my_teams = '';
      if (count($user_teams) > 0) {
        $my_teams = " OR idMod IN ($module_id_list)";
      }  
      $sql = "SELECT properties.property_id, paper_title, paper_type, DATE_FORMAT(created,' {$configObject->get('cfg_long_date')}') AS created, title, initials, surname, modules.moduleid FROM (properties, properties_modules, modules, users) WHERE properties.property_id=properties_modules.property_id AND properties_modules.idMod=modules.id AND paper_type='" . $type . "' AND deleted IS NULL AND paper_ownerID=users.id AND (paper_ownerID=" . $userObject->get_user_ID() . " $my_teams)";
    } else {
      $sql = "SELECT properties.property_id, paper_title, paper_type, DATE_FORMAT(created,' {$configObject->get('cfg_long_date')}') AS created, title, initials, surname, modules.moduleid FROM (properties, properties_modules, modules, users) WHERE properties.property_id=properties_modules.property_id AND properties_modules.idMod=modules.id AND idMod = " . $teamid . " AND deleted IS NULL AND paper_ownerID=users.id";
    }
    if ($order == 'created') {
      $order = 'CAST(created AS DATE)';
    }
    $sql .= " ORDER BY {$order} " . strtoupper($direction);
    if (strpos($order, 'surname') === false) {
      $sql .= ', users.surname ASC';
    }
    if (strpos($order, 'moduleid') === false) {
      $sql .= ', modules.moduleid ASC';
    }
    $result = $mysqli->prepare($sql);
    $result->execute();
    $result->bind_result($property_id, $paper_title, $paper_type, $created, $title, $initials, $surname, $moduleid);
    while ($result->fetch()) {
      if (!isset($paper_details[$property_id])) {
        $paper_details[$property_id] = array('paper_title'=>$paper_title, 'paper_type'=>$paper_type, 'created'=>$created, 'title'=>$title, 'initials'=>$initials, 'surname'=>$surname);
      }
      $paper_details[$property_id]['moduleid'][] = $moduleid;
    }
    $result->close();
    return $paper_details;
  }
  
  /**
   * This function compares the old and the new courses session objectives to see which can be copied.
   * 
   * @param array $old_course - old course objective information
   * @param array $new_course - new course objective information
   * @return array $mappings_copy_objID - objectives to map
   */
  static public function copy_between_sessions ($old_course, $new_course) {
    $mappings_copy_objID = array();
    foreach ($old_course as $module => $sessions) {
      foreach ($sessions as $identifier => $session) {
        if (!empty($session['objectives'])) {
          foreach ($session['objectives'] as $obj) {
            if (isset( $obj['id'])) {
              $old_objID = $obj['id'];
            } else {
              $old_objID = NULL;
            }
            if (isset($obj['guid'])) {
              $old_objGUID = $obj['guid'];
            } else {
              $old_objGUID = NULL;
            }
            $skip = 0;
            foreach ($new_course as $newmodule => $newsessions) {
              foreach ($newsessions as $newidentifier => $newsession) {
                if (!empty($newsession['VLE']) and !empty($session['VLE']) and $newsession['VLE'] === $session['VLE']) {
                  // Matching External VLEs.
                  if (isset($newsession['objectives'])){
                    foreach ($newsession['objectives'] as $new_obj) {
                      if (((array_key_exists('id', $new_obj) and $new_obj['id'] == $old_objID)
                        or (array_key_exists('guid', $new_obj) and $new_obj['guid'] == $old_objGUID))
                        and (array_key_exists('content', $new_obj) and array_key_exists('content', $obj)
                        and $new_obj['content'] == $obj['content'])) {
                          // Build a list of objectives that are still in both sessions
                          $mappings_copy_objID[$old_objID] = $new_obj['id'];
                          break;
                      }
                    }
                  }
                } elseif (empty($newsession['VLE']) and empty($session['VLE'])) {
                  // External VLEs not set, try internal mappings.
                  if (isset($newsession['objectives'])){
                    foreach ($newsession['objectives'] as $new_obj) {
                      if (array_key_exists('content', $new_obj) and array_key_exists('content', $obj)) {
                        // Brefore comparing the contents strip out all no alpha numeric characters and convert to lowecase.
                        $new_content_check = strtolower($new_obj['content']);
                        $new_content_check = preg_replace("/[^a-z0-9]/", '', $new_content_check);
                        $old_content_check = strtolower($obj['content']);
                        $old_content_check = preg_replace("/[^a-z0-9]/", '', $old_content_check);
                        if ($new_content_check == $old_content_check) {
                          // Build a list of objectives that are still in both sessions
                          $mappings_copy_objID[$old_objID] = $new_obj['id'];
                          break;
                        }
                      }
                    }
                  }
                } else {
                  // VLEs do not match between sessions, cannot map.
                  $skip = 1;
                  break;
                }
              }
              if ($skip !== 0) {
                break;
              }
            }
          }
        }
      }
    }
    return $mappings_copy_objID;
  }
  /**
   * Copies the paper properties record.
   *
   * @param string $calendar_year - Looks up and updates the academic session - used with learning objectives
   * @param string $new_calendar_year  - Looks up and updates the academic session - used with learning objectives
   * @param string $moduleIDs - Looks up and updates the modules the paper is on - used with learning objectives
   * @param array $postparams - posted parameters to copy
   * @return array - calendar year of copied paper, calendar year of new paper, modules new paper associated with and the id of the new paper.
   */
  static public function copyProperties($calendar_year, $new_calendar_year, $moduleIDs, $postparams) {
    $configObject = \Config::get_instance();
    $db = $configObject->db;
    $userObj = \UserObject::get_instance();
    $pid = $postparams['paperID'];
    $userID = $userObj->get_user_ID();
    $moduleIDs = Paper_utils::get_modules($postparams['paperID'], $db);

    $properties = Paper_utils::get_paper_properties($postparams['paperID'], $db);
    $calendar_year = $properties['session'];
    if ($postparams['paper_type'] == 2 and $configObject->get_setting('core', 'cfg_summative_mgmt')) {
      $duration = ($postparams['duration_hours'] * 60) + $postparams['duration_mins'] ;
      $tmp_exam_duration = $duration;
    } else {
      $tmp_exam_duration = $properties['duration'];
    }
    $labs = $properties['labs'];
    if ($postparams['paper_type'] == 2) {
      if ($configObject->get_setting('core', 'cfg_summative_mgmt')) {
        $tmp_start_date = NULL;
        $tmp_end_date = NULL;
        $labs = NULL;
      } else {
        $tmp_start_date = '20200505090000';
        $tmp_end_date = '20200505100000';
      }
    } else {
      $tmp_start_date = $properties['startdatetime'];
      $tmp_end_date = $properties['enddatetime'];;
    }
    $tmp_random_mark = $properties['random_mark'];
    if ($tmp_random_mark == '') $tmp_random_mark = NULL;
    $tmp_total_mark = $properties['total_mark'];
    if ($tmp_total_mark == '') $tmp_total_mark = NULL;

    $tmp_external_review_deadline = $properties['external_review_deadline'];
    if ($tmp_external_review_deadline == '') $tmp_external_review_deadline = NULL;

    $tmp_internal_review_deadline = $properties['internal_review_deadline'];
    if ($tmp_internal_review_deadline == '') $tmp_internal_review_deadline = NULL;

    if (!is_null($postparams['session'])) {
      $new_calendar_year = $postparams['session'];
      if ($new_calendar_year == '') {
        $new_calendar_year = NULL;
      }
    } else {
      $academic_year_title = Paper_utils::academic_year_from_title($postparams['new_paper']);
      if ($academic_year_title !== false) {
        $new_calendar_year = $academic_year_title;
      } else {
        $new_calendar_year = $calendar_year;
      }
    }

    $assessment = new assessment($db, $configObject);
    $unixtime = time();
    $created = date("Y-m-d H:i:s", $unixtime);
    $params = array(
      'paper_title' => array('s', $postparams['new_paper']),
      'start_date' => array('s', $tmp_start_date),
      'end_date' => array('s', $tmp_end_date),
      'timezone' => array('s', $properties['timezone']),
      'paper_type' => array('s', $postparams['paper_type']),
      'paper_prologue' => array('s', $properties['paper_prologue']),
      'paper_postscript' => array('s', $properties['paper_postscript']),
      'bgcolor' => array('s', $properties['bgcolor']),
      'fgcolor' => array('s', $properties['fgcolor']),
      'themecolor' => array('s', $properties['themecolor']),
      'labelcolor' => array('s', $properties['labelcolor']),
      'fullscreen' => array('s', $properties['fullscreen']),
      'marking' => array('s', $properties['marking']),
      'bidirectional' => array('s', $properties['bidirectional']),
      'pass_mark' => array('i', $properties['pass_mark']),
      'distinction_mark' => array('i', $properties['distinction_mark']),
      'paper_ownerID' => array('i', $userID),
      'folder' => array('s', $properties['folder']),
      'labs' => array('s', $labs),
      'rubric' => array('s', $properties['rubric']),
      'calculator' => array('i', $properties['calculator']),
      'exam_duration' => array('i', $tmp_exam_duration),
      'created' => array('s', $created),
      'random_mark' => array('d', $tmp_random_mark),
      'total_mark' => array('i', $tmp_total_mark),
      'display_correct_answer' => array('s', $properties['display_correct_answer']),
      'display_question_mark' => array('s', $properties['display_question_mark']),
      'display_students_response' => array('s', $properties['display_students_response']),
      'display_feedback' => array('s', $properties['display_feedback']),
      'hide_if_unanswered' => array('s', $properties['hide_if_unanswered']),
      'calendar_year' => array('i', $new_calendar_year),
      'external_review_deadline' => array('s', $tmp_external_review_deadline),
      'internal_review_deadline' => array('s', $tmp_internal_review_deadline),
      'sound_demo' => array('s', $properties['sound_demo']),
      'password' => array('s', $properties['password'])
    );
    $new_paper_id = $assessment->db_insert_assessment($params);
    $update_params = array('crypt_name' => array('s', $new_paper_id . $unixtime . $userID));
    $assessment->db_update_assessment($new_paper_id, $update_params);

    // Get the old reviewers and populate the new paper with.
    $result2 = $db->prepare("SELECT reviewerID, type FROM properties_reviewers WHERE paperID = ?");
    $result2->bind_param('i', $postparams['paperID']);
    $result2->execute();
    $result2->store_result();
    $result2->bind_result($reviewerID, $type);
    while ($result2->fetch()) {
      $stmt = $db->prepare("INSERT INTO properties_reviewers VALUES (NULL, ?, ?, ?)");
      $stmt->bind_param('iis', $new_paper_id, $reviewerID, $type);
      $stmt->execute();
      $stmt->close();
    }
    $result2->close();

    // Set the modules on the new paper
    Paper_utils::update_modules($moduleIDs, $new_paper_id, $db, $userObj);

    if ($postparams['paper_type'] == $assessment::TYPE_SUMMATIVE and $configObject->get_setting('core', 'cfg_summative_mgmt')) {
      $assessment->schedule($new_paper_id, $postparams['period'], $postparams['barriers_needed'], $postparams['cohort_size'], $postparams['notes'] , $postparams['sittings'] , $postparams['campus']);
    }
    return array('calendar_year' => $calendar_year, 'new_calendar_year' => $new_calendar_year, 'moduleIDs' => $moduleIDs, 'new_paper_id' => $new_paper_id);
  }

  /**
   * Get marking overrides for a user on a paper
   * @param enum $log_type type of paper
   * @param integer $temp_userID user id
   * @param integer $paperID paper id
   * @return array
   */
  public function get_marking_overrides($log_type, $temp_userID, $paperID) {
      $overrides = array();
      $configObject = \Config::get_instance();
      $db = $configObject->db;
      $sql = "SELECT m.q_id, title, surname, date_marked, new_mark_type, adjmark
          FROM marking_override m INNER JOIN users u ON m.marker_id = u.id
          INNER JOIN log{$log_type} l ON m.log_id = l.id
          WHERE user_id = ? AND paper_id = ?";
      $result = $db->prepare($sql);
      $result->bind_param('ii', $temp_userID, $paperID);
      $result->execute();
      $result->store_result();
      $result->bind_result($o_q_id, $o_title, $o_surname, $o_date_marked, $o_new_mark_type, $o_adjmark);
      while($result->fetch()) {
        $overrides[$o_q_id] = array('q_id' => $o_q_id, 'title' => $o_title, 'surname' => $o_surname, 'date_marked' => $o_date_marked, 'new_mark_type' => $o_new_mark_type, 'adjmark' => $o_adjmark);
      }
      $result->close();
      return $overrides;
  }

  /**
   * Get list of question ids for parents of linked questions.
   * @param $questions questions array from paper
   * @return array
   */
  public static function get_linked_question_parents($questions) {
    $linked = array();
    foreach ($questions as &$question) {
      if ($question['q_type'] === 'enhancedcalc') {
        $settings = json_decode($question['settings'], true);
        foreach ($settings['vars'] as $var_name => $var_data) {
          if ($question['object']->is_linked_ans($var_data['min'])) {
            $linked[] = $question['object']->parse_linked_ans($var_data['min']);
          }
          if ($question['object']->is_linked_ans($var_data['max'])) {
            $linked[] = $question['object']->parse_linked_ans($var_data['max']);
          }
          if ($question['object']->is_linked_question_var($var_data['min'])) {
            list($var, $qid)= $question['object']->parse_linked_question_var($var_data['min']);
            $linked[] = $qid;
          }
          if ($question['object']->is_linked_question_var($var_data['max'])) {
            list($var, $qid)= $question['object']->parse_linked_question_var($var_data['max']);
            $linked[] = $qid;
          }
        }
      }
    }
    return array_unique($linked);
  }
}