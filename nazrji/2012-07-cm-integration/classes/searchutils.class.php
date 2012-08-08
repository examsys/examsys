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
* Utility class for search related functionality
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/


Class search_utils {
 
  /**
   * Get an array with team information the current user has access to.
   * @param array $teams teams the current user is on
   * @param string $userroles the role(s) of the current user
   * @param integer $userID ID of the current user
   * @param object $db database connection
   * @return array of team information
   */
  static function get_teams($teams, $userroles, $userID, $db) {
    $teams_list = array();

    $team_sql = implode("','", $teams);
    if ($team_sql != '') $team_sql = "'$team_sql'";
    
    if ($team_sql != '' or strpos($userroles,'Admin') !== false) {
      if (strpos($userroles,'SysAdmin') !== false) {
        $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id ORDER BY school, moduleID";
      } elseif (strpos($userroles,'Admin') !== false) {
        $schoolIDs = implode(',', SchoolUtils::get_admin_schools($userID, $db));
        if ($schoolIDs != '') {
          $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id AND schoolid IN ($schoolIDs) ORDER BY school, moduleID";
        } elseif ($team_sql != '') {
          $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id AND moduleid IN ($team_sql) ORDER BY school, moduleID";
        }
      } else {
        $sql = "SELECT DISTINCT modules.id, moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id AND moduleid IN ($team_sql) ORDER BY school, moduleID";
      }

      if (isset($sql)) {
        $team_no = 0;

        $result = $db->prepare($sql);
        $result->execute();
        $result->bind_result($recordid, $moduleid, $fullname, $school);
        while ($result->fetch()) {
          $teams_list[$team_no]['school'] = $school;
          $teams_list[$team_no]['id'] = $moduleid;
          $teams_list[$team_no]['recordid'] = $recordid;
          $teams_list[$team_no]['fullname'] = $fullname;
          $team_no++;
        }
        $result->close();
      }
    }
    
    return $teams_list;
  }
  
  /**
   * Get a list of personal and group keywords for the current user.
   * @param object $db database connection
   * @param array $teams teams the current user is on
   * @param integer $user_id ID of the current user
   * @return array of keywords
   */
  static function get_keywords($db, $teams, $user_id) {
    $keywords = array('team' => array(), 'personal' => array());
    
    $teams = (is_array($teams)) ? implode("','", $teams) : $teams;
    $result = $db->prepare("SELECT m.moduleid, k.keyword, k.id FROM keywords_user k INNER JOIN modules m ON k.userID = m.id WHERE k.keyword_type = 'team' AND m.moduleid IN ('$teams') ORDER BY m.moduleid, k.keyword");
    $result->execute();
    $result->bind_result($moduleID, $keyword, $keywordID);
    while ($result->fetch()) {
      $keywords['team'][] = array('module_id' => $moduleID, 'keyword_id' => $keywordID, 'keyword' => $keyword);
    }
    $result->close();
    
    $result = $db->prepare("SELECT DISTINCT keyword, id FROM keywords_user WHERE userID = ? AND keyword_type = 'personal' ORDER BY keyword");
    $result->bind_param('i', $user_id);
    $result->execute();
    $result->bind_result($keyword, $keywordID);
    while ($result->fetch()) {
      $keywords['personal'][] = array('keyword_id' => $keywordID, 'keyword' => $keyword);
    }
    $result->close();
    
    return $keywords;
  }

  /**
   * Display a dropdown list of available teams for the current user.
   * @param array $teams teams the current user is on
   * @param string $userroles the role(s) of the current user
   * @param integer $userID ID of the current user
   * @param object $db database connection
   * @return string HTML of the dropdown menu
   */
  static function display_team_dropdown($teams, $userroles, $userID, $db) {
    global $string;
    
    $teams = self::get_teams($teams, $userroles, $userID, $db);
    
    echo "<select style=\"width:175px\" onchange=\"updateDropdownState(this,'team')\" name=\"team\">\n";
    echo "<option value=\"\">" . $string['anymodule'] . "</option>\n";
    
    $old_school = '';
    foreach ($teams as $team) {
      if ($team['school'] != $old_school) {
        if ($old_school != '') echo "</optgroup>\n";
        echo "<optgroup label=\"" . $team['school'] . "\">\n";
      }
      if ((isset($_POST['moduleid']) and $team['id'] == $_POST['moduleid']) or (isset($_GET['moduleID']) and $team['id'] == $_GET['moduleID']) or (isset($_POST['team']) and $team['id'] == $_POST['team']) or (isset($_GET['team']) and $team['id'] == $_GET['team'])) {
        echo "<option value=\"" . $team['id'] . "\" selected>" . $team['id'] . ": " . $team['fullname'] . "</option>\n";
      } else {
        echo "<option value=\"" . $team['id'] . "\">" . $team['id'] . ": " . $team['fullname'] . "</option>\n";
      }
      $old_school = $team['school'];
    }
    echo "</optgroup>\n</select>\n";
  }
  
  /**
   * Get a list of names for people in the current user teams.
   * @param array $teams teams the current user is on
   * @param string $userroles the role(s) of the current user
   * @param object $db database connection
   * @return array of name data
   */
  static function get_owners($teams, $userroles, $db) {
    if (strpos($userroles,'Admin') !== false) {
      $stmt = $db->prepare("SELECT DISTINCT id, REPLACE(title,'Professor','Prof') AS title, initials, surname FROM users WHERE roles LIKE 'Staff%' ORDER BY surname, initials");
    } else {
      $stmt = $db->prepare("SELECT DISTINCT id, REPLACE(title,'Professor','Prof') AS title, initials, surname FROM users, teams WHERE users.id=teams.memberID AND name IN (\"" . implode('","', $teams) . "\") AND (roles LIKE 'Staff%' OR roles LIKE '%SysAdmin%') ORDER BY surname, initials");
    }
    $stmt->execute();
    $stmt->bind_result($id, $title, $initials, $surname);
    $owners = array();
    while ($stmt->fetch()) {
      $owners[$id]['title'] = $title;
      $owners[$id]['initials'] = $initials;
      $owners[$id]['surname'] = $surname;
    }
    $stmt->close();
    
    return $owners;
  }

  /**
   * Display a dropdown list of owners in teams available for the current user.
   * @param array $teams teams the current user is on
   * @param string $userroles the role(s) of the current user
   * @param integer $userID ID of the current user
   * @param object $db database connection
   * @param string $type used to control wording - whether dealing with papers or questions
   * @return string HTML of the dropdown menu
   */
  static function display_owners_dropdown($teams, $userroles, $userID, $db, $type) {
    global $string, $state;
    $owners = self::get_owners($teams, $userroles, $db);
    
    echo "<select style=\"width:175px\" onchange=\"updateDropdownState(this,'owner')\" name=\"owner\">\n";
    echo "<option value=\"\">" . $string['anyowner']. "</option>\n";
    if ($type == 'questions') {
      echo "<option value=\"$userID\">" . $string['myquestionsonly']. "</option>\n";
    } else {
      echo "<option value=\"$userID\">" . $string['mypaperssonly']. "</option>\n";
    }
    echo "<option value=\"%\" style=\"background-color:#ECE9D8\"></option>\n";
    
    $old_letter = '';
    foreach ($owners as $ownerID=>$details) {
      if ($old_letter != strtoupper(substr($details['surname'],0,1))) {
        if ($old_letter != '') echo "</optgroup>\n";
        echo "<optgroup label=\"" . strtoupper(substr($details['surname'],0,1)) . "\">\n";
      }
      if ((isset($state['owner']) and $state['owner'] == $ownerID) or (isset($_REQUEST['owner']) and $_REQUEST['owner'] == $ownerID)) {
        echo "<option value=\"$ownerID\" selected>" . $details['surname'] . ", " . $details['initials'] . ". " . $details['title'] . "</option>\n";
      } else {
        echo "<option value=\"$ownerID\">" . $details['surname'] . ", " . $details['initials'] . ". " . $details['title'] . "</option>\n";
      }
      $old_letter = strtoupper(substr($details['surname'],0,1));
    }
    echo "</optgroup>\n</select>\n";
  }
  
  /**
   * Display a dropdown menu of status options for a question.
   * @return string HTML of the status dropdown menu
   */
  static function display_status_dropdown() {
    global $string, $state;
    
    echo "<select style=\"width:175px\" onchange=\"updateDropdownState(this,'status')\" name=\"status\">\n";
    echo "<option value=\"nonretired\">" . $string['anynonretiredstatus'] . "</option>\n";
    if (isset($state['status']) and $state['status'] == '%') {
      echo "<option value=\"%\" selected>" . $string['anystatus'] . "</option>\n";
    } else {
      echo "<option value=\"%\">" . $string['anystatus'] . "</option>\n";
    }
    $status_array = array('Normal', 'Retired', 'Incomplete', 'Experimental', 'Beta');
    foreach ($status_array as $individual_status) {
      if (isset($state['status']) and $state['status'] == $individual_status) {
        echo "<option value=\"$individual_status\" selected>" . $string[strtolower($individual_status)] . "</option>"; 
      } else {
        echo "<option value=\"$individual_status\">" . $string[strtolower($individual_status)] . "</option>"; 
      }
    }
    echo "</select>\n";    
    
  }

  /**
   * Display a dropdown menu of Bloom's Taxonomy options for a question.
   * @return string HTML of the Bloom's Taxonomy dropdown menu
   */
  static function display_blooms_dropdown() {
    global $string, $state;
    
    echo "<select style=\"width:175px\" onchange=\"updateDropdownState(this,'bloom')\" name=\"bloom\">\n";
    echo "<option value=\"%\">" . $string['alllevels'] . "</option>\n";

    $blooms_array = array('Knowledge','Comprehension','Application','Analysis','Synthesis','Evaluation');
    foreach ($blooms_array as $individual_bloom) {
      if (isset($state['bloom']) and $state['bloom'] == $individual_bloom) {
        echo "<option value=\"$individual_bloom\" selected>" . $string[strtolower($individual_bloom)] . "</option>"; 
      } else {
        echo "<option value=\"$individual_bloom\">" . $string[strtolower($individual_bloom)] . "</option>"; 
      }
    }
    echo "</select>\n";
  }

}