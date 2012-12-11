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
   * Get an array with staff modules information the current user has access to.
   * @return array of staff module information
   */
  static function get_staff_modules($staff_modules, $userroles, $userID, $db, $userObject) {
    trigger_error("get_staff_modules:: Called and it has been removed", E_USER_WARNING);
    return array();
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
   * @param object the current user object
   * @param object $db database connection
   * @return string HTML of the dropdown menu
   */
  static function display_staff_modules_dropdown($userObj, $db) {
    global $string;
    
    $staff_modules = $userObj->get_staff_accessable_modules();
    
    echo "<select style=\"width:175px\" onchange=\"updateDropdownState(this,'module')\" name=\"module\">\n";
    echo "<option value=\"\">" . $string['anymodule'] . "</option>\n";
    
    $old_school = '';
    foreach ($staff_modules as $module) {
      if ($module['school'] != $old_school) {
        if ($old_school != '') echo "</optgroup>\n";
        echo "<optgroup label=\"" . $module['school'] . "\">\n";
      }
      if ((isset($_POST['moduleid']) and $module['idMod'] == $_POST['moduleid']) or (isset($_GET['moduleID']) and $module['idMod'] == $_GET['moduleID']) or (isset($_POST['module']) and $module['idMod'] == $_POST['module']) or (isset($_GET['module']) and $module['idMod'] == $_GET['module']) or (isset($_GET['module']) and $module['id'] == $_GET['module'])) {
        echo "<option value=\"" . $module['idMod'] . "\" selected>" . $module['id'] . ": " . $module['fullname'] . "</option>\n";
      } else {
        echo "<option value=\"" . $module['idMod'] . "\">" . $module['id'] . ": " . $module['fullname'] . "</option>\n";
      }
      $old_school = $module['school'];
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
  static function get_owners($userObj, $db) {
    if ($userObj->has_role(array('SysAdmin','Admin'))) {
      $stmt = $db->prepare("SELECT DISTINCT id, REPLACE(title,'Professor','Prof') AS title, initials, surname FROM users WHERE roles LIKE 'Staff%' ORDER BY surname, initials");
    } else {
      $team_sql = implode(',',array_keys($userObj->get_staff_modules()));
      if($team_sql != '') $team_sql = " AND idMod IN ($team_sql) ";
      $stmt = $db->prepare("SELECT DISTINCT id, REPLACE(title,'Professor','Prof') AS title, initials, surname FROM users, modules_staff WHERE users.id=modules_staff.memberID $team_sql AND (roles LIKE 'Staff%' OR roles LIKE '%SysAdmin%') ORDER BY surname, initials");
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
   * @param object the current user object
   * @param object $db database connection
   * @param string $type used to control wording - whether dealing with papers or questions
   * @return string HTML of the dropdown menu
   */
  static function display_owners_dropdown($userObj, $db, $type, $font_size = 90) {
    global $string, $state;
    $owners = self::get_owners($userObj, $db);
    
    echo "<select style=\"width:175px; font-size:$font_size%\" onchange=\"updateDropdownState(this,'owner')\" name=\"owner\">\n";
    echo "<option value=\"\">" . $string['anyowner']. "</option>\n";
    if ($type == 'questions') {
      echo "<option value=\"{$userObj->get_user_ID()}\">" . $string['myquestionsonly']. "</option>\n";
    } else {
      echo "<option value=\"{$userObj->get_user_ID()}\">" . $string['mypaperssonly']. "</option>\n";
    }
    //echo "<option value=\"%\" style=\"background-color:#ECE9D8\"></option>\n";
    
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