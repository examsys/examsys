<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Utility class for search related functionality
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/


Class SearchUtils {
 
  static function getTeams($teams, $userroles, $userID, $db) {
    $teams_list = array();

    $team_sql = implode("','", $teams);
    if ($team_sql != '') $team_sql = "'$team_sql'";
    
    if ($team_sql != '' or strpos($userroles,'Admin') !== false) {
      if (strpos($userroles,'SysAdmin') !== false) {
        $search_results = $db->query("SELECT DISTINCT moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id ORDER BY school, moduleID");
      } elseif (strpos($userroles,'Admin') !== false) {
        $schoolIDs = implode(',', SchoolUtils::getAdminSchools($userID, $db));
        if ($schoolIDs != '') {
          $search_results = $db->query("SELECT DISTINCT moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id AND schoolid IN ($schoolIDs) ORDER BY school, moduleID");
        } elseif ($team_sql != '') {
          $search_results = $db->query("SELECT DISTINCT moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id AND moduleid IN ($team_sql) ORDER BY school, moduleID");
        }
      } else {
        $search_results = $db->query("SELECT DISTINCT moduleid, fullname, school FROM modules, schools WHERE modules.schoolid=schools.id AND moduleid IN ($team_sql) ORDER BY school, moduleID");
      }
      
      $team_no = 0;
      while ($row = $search_results->fetch_assoc()) {
        $teams_list[$team_no]['school'] = $row['school'];
        $teams_list[$team_no]['id'] = $row['moduleid'];
        $teams_list[$team_no]['fullname'] = $row['fullname'];
        $team_no++;
      }
      $search_results->close();
    }
    
    return $teams_list;
  }

  static function displayTeamDropdown($teams, $userroles, $userID, $db) {
    global $string;
    
    $teams = self::getTeams($teams, $userroles, $userID, $db);
    
    echo "<select style=\"width:175px\" onchange=\"updateCookieOwner(this,'team')\" name=\"team\">\n";
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
  
  static function getOwners($teams, $userroles, $db) {
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

  static function displayOwnersDropdown($teams, $userroles, $userID, $db, $type) {
    global $string;
    $owners = self::getOwners($teams, $userroles, $db);
    
    echo "<select style=\"width:175px\" onchange=\"updateCookieOwner(this,'owner')\" name=\"owner\">\n";
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
      if ((isset($_COOKIE['owner']) and $_COOKIE['owner'] == $ownerID) or (isset($_POST['owner']) and $_POST['owner'] == $ownerID)) {
        echo "<option value=\"$ownerID\" selected>" . $details['surname'] . ", " . $details['initials'] . ". " . $details['title'] . "</option>\n";
      } else {
        echo "<option value=\"$ownerID\">" . $details['surname'] . ", " . $details['initials'] . ". " . $details['title'] . "</option>\n";
      }
      $old_letter = strtoupper(substr($details['surname'],0,1));
    }
    echo "</optgroup>\n</select>\n";
  }
  
  static function displayStatusDropdown() {
    global $string;
    
    echo "<select style=\"width:175px\" onchange=\"updateCookieOwner(this,'status')\" name=\"status\">\n";
    echo "<option value=\"%\">" . $string['anystatus'] . "</option>\n";

    $status_array = array('Normal','Retired','Incomplete','Experimental','Beta');
    foreach ($status_array as $individual_status) {
      if (isset($_COOKIE['status']) and $_COOKIE['status'] == $individual_status) {
        echo "<option value=\"$individual_status\" selected>" . $string[strtolower($individual_status)] . "</option>"; 
      } else {
        echo "<option value=\"$individual_status\">" . $string[strtolower($individual_status)] . "</option>"; 
      }
    }
    echo "</select>\n";    
    
  }

  static function displayBloomsDropdown() {
    global $string;
    
    echo "<select style=\"width:175px\" onchange=\"updateCookieOwner(this,'bloom')\" name=\"bloom\">\n";
    echo "<option value=\"%\">" . $string['alllevels'] . "</option>\n";

    $blooms_array = array('Knowledge','Comprehension','Application','Analysis','Synthesis','Evaluation');
    foreach ($blooms_array as $individual_bloom) {
      if (isset($_COOKIE['bloom']) and $_COOKIE['bloom'] == $individual_bloom) {
        echo "<option value=\"$individual_bloom\" selected>" . $string[strtolower($individual_bloom)] . "</option>"; 
      } else {
        echo "<option value=\"$individual_bloom\">" . $string[strtolower($individual_bloom)] . "</option>"; 
      }
    }
    echo "</select>\n";
  }

}