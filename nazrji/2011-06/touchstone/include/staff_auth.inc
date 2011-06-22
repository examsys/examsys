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
* Authentication routine which permits staff only access.
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require_once 'staff_student_auth.inc';
  if (strpos($userroles,'Staff') === false AND strpos($userroles,'External Examiner') === false) {
    Header("WWW-authenticate: basic realm=\"TouchStone\"");
    Header("HTTP/1.0 401 Unauthorised");
    echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#BF0000;font-size:140%}\n</style>\n</head>\n<body>\n";
    echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">You are attempting to access a <strong>Staff Only</strong> page.</p>\n</body>\n</html>";
    exit;
  }
  
  // Redirect External Examiners if they are straying
  if (strpos($userroles,'External Examiner') !== false) {
    if ($_SERVER['PHP_SELF'] != '/touchstone/index.php' and $_SERVER['PHP_SELF'] != '/touchstone/reviews/index.php' and $_SERVER['PHP_SELF'] != '/touchstone/reviews/start.php' and $_SERVER['PHP_SELF'] != '/touchstone/reviews/finish.php') {
      header("location: " . $protocol. $_SERVER['HTTP_HOST'] . "/touchstone/reviews/");
    }
  }

  // Get teams.
  $teams = array();
  $team_details = $mysqli->query("SELECT name FROM teams WHERE memberID=$userID ORDER BY name");
  while ($team_row = $team_details->fetch_assoc()) {
    $teams[strtoupper($team_row['name'])] = strtoupper($team_row['name']);
  }
  $team_details->close();

  $types_array = array('Formative Self-Assessments','Progress Tests','Summative Exams','Surveys','OSCE Stations','Offline Papers');

  //
  //default security checkes
  //

  if (strpos($userroles,'SysAdmin') !== false) {
    //sysadmin can access any thing !!!

  } elseif (strpos($userroles,'Admin') !== false) {
    //admin users have rights in there facalty
    
    //Can the user access the paper?
    if (isset($_GET['paperID']) or isset($_POST['paperID'])) {
      $on_team = false;

      if (isset($_GET['paperID'])) {
        $tmp_paperID = $_GET['paperID'];
      } else {
        $tmp_paperID = $_POST['paperID'];
      }

      if (substr($tmp_paperID,0,5) != 'find:') {
        // Get the moduleID of the paper
        $result = $mysqli->prepare("SELECT moduleID, paper_ownerID FROM properties WHERE property_id=?");
        $result->bind_param('i',$tmp_paperID);
        $result->execute();  
        $result->bind_result($moduleID, $paper_ownerID);
        $result->fetch();
        $result->close();
      
        // Check which Faculty the moduleID(s) are in.
        $result = $mysqli->prepare("SELECT faculty FROM schools, modules WHERE schools.school=modules.school AND moduleid IN ('" . str_replace(",","','",$moduleID). "')");
        $result->execute();  
        $result->bind_result($paper_faculty);
        while ($result->fetch()) {
          if ($faculty == $paper_faculty) $on_team = true;
        }
        $result->close();
      
        if (!$on_team) {
          // Check if 'Admin' is on the team outside of the module.
          $paper_modules = explode(',',$moduleID);
          foreach ($paper_modules as $individual_module) {
            if (isset($teams[$individual_module])) $on_team = true;
          }
        }
    
        // Override access for the system training module.
        if ($on_team == false and strpos($moduleID,'SYSTEM') === false) {
          echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#BF0000;font-size:140%}\n</style>\n</head>\n<body>\n";
          echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
          echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
          echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">You are attempting to access a <strong>paper you do not have rights to</strong>!</p>\n</body>\n</html>"; 
          $mysqli->close();
          exit;
        }
      }
    }
    
    // Can the user acsess the question?
    if (isset($_GET['q_id']) or isset($_POST['q_id'])) {
      if (isset($_GET['q_id'])) {
        $tmp_q_id = $_GET['q_id'];
      } else {
        $tmp_q_id = $_POST['q_id'];
      }
      $result = $mysqli->prepare("SELECT q_group, ownerID, faculty FROM questions LEFT JOIN modules ON q_group=modules.moduleid LEFT JOIN schools ON modules.school=schools.school WHERE q_id=?");
      $result->bind_param('i',$tmp_q_id);
      $result->execute();  
      $result->bind_result($q_group, $ownerID, $question_faculty);
      while ($result->fetch()) {
        if ($ownerID != $userID AND !in_array($q_group,$teams) AND $faculty != $question_faculty AND !in_array('SYSTEM', $paper_modules)) {   //allow acsess to the SYSTEM module for training!
          echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%;font-family:Arial,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#BF0000;font-size:140%}\n</style>\n</head>\n<body>\n";
          echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
          echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
          echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px;color:#C0C0C0;background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">You are attempting to access a <strong>question you do not have rights to</strong>!</p>\n</body>\n</html>";       
          $result->close();
          $mysqli->close();
          exit;
        } 
      }
      $result->close();
    }
  } else {
    //normal users can acces things they own or are on the team of only !!

    //can the user acsess the module 
    if (isset($_GET['module']) and $_GET['module'] != '' and !isset($_GET['paperID'])) {
      $team_match = false;
      $paper_modules = explode(',',$_GET['module']);
      foreach ($paper_modules as $paper_module) {
        if (in_array(strtoupper($paper_module), $teams)) $team_match = true;
      }
      if ($team_match == false) {
        echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%;font-family:Arial,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#BF0000;font-size:140%}\n</style>\n</head>\n<body>\n";
        echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
        echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
        echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">You are not a member of the <strong>" . $_GET['module'] . "</strong> module team.</p>\n</body>\n</html>";
        exit;
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
          if($paper_ownerID != $userID) {
            $moduleIDs = explode(',',$moduleID);
            $on_team = false;
            foreach ($moduleIDs as $g) {
              if(isset($teams[$g])) { 
                $on_team = true;
                break;
              }
            }            //allow acsess to the SYSTEM module for training!
            if ($on_team == false and strpos($moduleID,'SYSTEM') === false) {
              echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
              echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
              echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
              echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">You are attempting to access a <strong>paper you do not have rights to</strong>!</p>\n</body>\n</html>";       
              $result->close();
              $mysqli->close();
              exit;
            }
          }
        }
        $result->close();
      }
    } 

    //Can the user acsess the question?
    if (isset($_GET['q_id']) OR isset($_POST['q_id'])) {
      $result = $mysqli->prepare("SELECT q_group, ownerID FROM questions WHERE q_id=?");
      if (isset($_GET['q_id'])) {
        $result->bind_param('i',$_GET['q_id']);
      } else {
        $result->bind_param('i', $_POST['q_id']);
      }
      $result->execute();  
      $result->bind_result($q_group, $ownerID);
      $result->fetch();
      $result->close();
      
      $access = false;
	  $moduleIDs = explode(';',$q_group);
      //$question_teams = explode(';',$q_group);
      foreach ($moduleIDs as $question_team) {
        if (isset($teams[$question_team])) $access = true;
      }
      
      //allow acsess to the TRAIN module for training!
      if ($ownerID != $userID AND $access == false AND !in_array('SYSTEM', $moduleIDs)) {
        echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
        echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
        echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
        echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">You are attempting to access a <strong>question you do not have right to</strong>!</p>\n</body>\n</html>";       
        $mysqli->close();
        exit;
      } 
    } 
  }
?>