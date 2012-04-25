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
* Authentication routine which permits staff only access.
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require_once 'staff_student_auth.inc';
  
  if (strpos($userroles,'Staff') === false AND strpos($userroles,'External Examiner') === false) {
    access_denied('You are attempting to access a <strong>Staff Only</strong> page.',true);
  }
  
  // Redirect External Examiners if they are straying
  if (strpos($userroles,'External Examiner') !== false) {
    if ($_SERVER['PHP_SELF'] != '/staff/index.php' and $_SERVER['PHP_SELF'] != '/reviews/index.php' and $_SERVER['PHP_SELF'] != '/reviews/start.php' and $_SERVER['PHP_SELF'] != '/reviews/finish.php') {
      header("location: " . $protocol. $_SERVER['HTTP_HOST'] . $cfg_root_path . "/reviews/");
    }
  }

  // Get teams.
  $teams = getUserTeams($userID, $mysqli);
  $types_array = array('Formative Self-Assessments', 'Progress Tests', 'Summative Exams', 'Surveys', 'OSCE Stations', 'Offline Papers', 'Peer Review');

  //
  //default security checkes
  //

  if (strpos($userroles,'SysAdmin') !== false) {
    //sysadmin can access any thing !!!

  } elseif (strpos($userroles,'Admin') !== false) {
    //For 'Admin' users look up permissions from 'admin_access' table.
    
    $on_team = false;
    $failed_paper = false;

    //Can the user access the paper?
    if ((isset($_GET['paperID']) and $_GET['paperID'] != '') or (isset($_POST['paperID']) and $_POST['paperID'] != '')) {
      if (isset($_GET['paperID'])) {
        $tmp_paperID = $_GET['paperID'];
      } else {
        $tmp_paperID = $_POST['paperID'];
      }
      if (substr($tmp_paperID,0,5) != 'find:') {

        // Get the moduleID of the paper
        $result = $mysqli->prepare("SELECT moduleID, paper_ownerID FROM properties WHERE property_id=?");
        $result->bind_param('i', $tmp_paperID);
        $result->execute();  
        $result->bind_result($moduleID, $paper_ownerID);
        $result->fetch();
        $result->close();
      
        // Check which Schools the moduleID(s) are in.
        $result = $mysqli->prepare("SELECT school FROM schools, modules, admin_access WHERE admin_access.userID=$userID AND admin_access.schools_id=schools.id AND schools.id=modules.schoolid AND moduleid IN ('" . str_replace(",","','",$moduleID). "')");
        $result->execute();
        $result->store_result();
        $result->bind_result($paper_school);
        if ($result->num_rows() > 0) {
          $on_team = true;
        } else {
          $on_team = false;
        }
        $result->close();
        
        // Override access for the system training module.
        if ($on_team == false and strpos($moduleID, 'SYSTEM') === false) {
          $failed_paper = true;
        }
      }
    }
   
    if ($on_team == false and isset($_GET['module']) and $_GET['module'] != '') {
      // Is 'Admin' on the module as normal
      $result = $mysqli->prepare("SELECT groupID FROM teams WHERE memberID=? AND name=?");
      $result->bind_param('is', $userID, $_GET['module']);
      $result->execute();
      $result->store_result();
      $result->bind_result($groupID);
      if ($result->num_rows() > 0) {
        $on_team = true;
      } else {
        $on_team = false;
      }
      $result->close();
      
      if ($on_team == false) { // Check which Schools the moduleID(s) are in and match school to 'Admin' person.
        $result = $mysqli->prepare("SELECT school FROM schools, modules, admin_access WHERE admin_access.userID=? AND admin_access.schools_id=schools.id AND schools.id=modules.schoolid AND moduleid=?");
        $result->bind_param('is', $userID, $_GET['module']);
        $result->execute();
        $result->store_result();
        $result->bind_result($paper_school);
        if ($result->num_rows() > 0) {
          $on_team = true;
        } else {
          $on_team = false;
        }
        $result->close();
      }
      
      // Override access for the system training module.
      if ($on_team == false and strpos($_GET['module'], 'SYSTEM') === false) {
        $message = ($failed_paper) ? $string['denied_paper'] : $string['denied_folder'];
        access_denied($message, false);
      }
    }
    
    // Check if user has access to the questions
    if (isset($_REQUEST['q_id'])) {
      $tmp_q_id = $_REQUEST['q_id'];
      $result = $mysqli->prepare("SELECT q_group, ownerID FROM questions WHERE q_id=?");
      $result->bind_param('i', $tmp_q_id);
      $result->execute();
      $result->store_result();
      $result->bind_result($q_group, $ownerID);
      $result->fetch();
      $group_array = explode(';', $q_group);
      if ($ownerID != $userID and !in_array('SYSTEM', $group_array) and count(array_intersect($group_array, $teams)) == 0) {
        // Not owner or on team so check schools
        $is_admin = false;
        if (count($group_array) > 0) {
          $module_list = "'" . str_replace(';', ',', $q_group) . "'";
          $result2 = $mysqli->prepare("SELECT school FROM schools, modules, admin_access WHERE admin_access.userID=? AND admin_access.schools_id=schools.id AND schools.id=modules.schoolid AND moduleid IN ({$module_list})");
          $result2->bind_param('i', $userID);
          $result2->execute();
          $result2->store_result();
          if($result2->num_rows > 0) {
            $is_admin = true;
          }
          $result2->close();
        }

        if (!$is_admin) {
          $result->close();
          access_denied($string['denied_question'], false);
        }
      }
      $result->close();
    }

  } else {
    //normal users can acces things they own or are on the team of only !!
    $on_team = false;

    //can the user acsess the module 
    if (isset($_GET['module']) and $_GET['module'] != '' and !isset($_GET['paperID'])) {
      $team_match = false;
      $paper_modules = explode(',',$_GET['module']);
      foreach ($paper_modules as $paper_module) {
        if (in_array(strtoupper($paper_module), $teams)) $team_match = true;
      }
      if ($team_match == false) {
        access_denied(str_replace('%s',$_GET['module'],$string['denied_team']), false);
      }    
    }
    
    //Can the user acsess the paper?
    if ((isset($_GET['paperID']) and $_GET['paperID'] != '') OR (isset($_POST['paperID']) and $_POST['paperID'] != '')) {
      if (isset($_GET['paperID'])) {
        $tmp_paperID = $_GET['paperID'];
      } else {
        $tmp_paperID = $_POST['paperID'];
      }
      if (substr($tmp_paperID,0,5) != 'find:') {
        $result = $mysqli->prepare("SELECT moduleID, paper_ownerID FROM properties WHERE property_id=?");
        $result->bind_param('i',$tmp_paperID);
        $result->execute();  
        $result->bind_result($moduleID, $paper_ownerID);
        while ($result->fetch()) {
          if ($paper_ownerID != $userID) {
            $moduleIDs = explode(',',$moduleID);
            foreach ($moduleIDs as $g) {
              if (isset($teams[$g])) { 
                $on_team = true;
                break;
              }
            }            //allow acsess to the SYSTEM module for training!
            if ($on_team == false and strpos($moduleID,'SYSTEM') === false) {
              access_denied($string['denied_paper'], false);
            }
          }
        }
        $result->close();
      }
    } 

    //Can the user access the question?
    if (isset($_REQUEST['q_id']) and $_REQUEST['q_id'] != -1) {
      $result = $mysqli->prepare("SELECT q_group, ownerID FROM questions WHERE q_id=?");
      $result->bind_param('i', $_REQUEST['q_id']);
      $result->execute();  
      $result->bind_result($q_group, $ownerID);
      $result->fetch();
      $result->close();
      
      $access = false;
      $moduleIDs = explode(';',$q_group);
      //$question_teams = explode(';', $q_group);
      foreach ($moduleIDs as $question_team) {
        if (isset($teams[$question_team])) $access = true;
      }
      
      //allow acsess to the TRAIN module for training!
      if ($ownerID != $userID AND $access == false AND !in_array('SYSTEM', $moduleIDs)) {
        access_denied($string['denied_question'], false);
      } 
    } 
  }
?>