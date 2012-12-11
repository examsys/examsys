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
require_once $cfg_web_root . 'classes/paperutils.class.php';
require_once $cfg_web_root . 'classes/moduleutils.class.php';
require_once $cfg_web_root . 'classes/questionutils.class.php';
require_once $cfg_web_root . 'classes/moduleutils.class.php';
global $userObject;

if (!$userObject->has_role(array('Staff', 'External Examiner'))) {
  UserNotices::access_denied('You are attempting to access a <strong>Staff Only</strong> page.', true);
}

// Redirect External Examiners if they are straying
// $external_allowed_pages is an array that contains the set of scripts that External Examiners 
// CAN access all other pages should redirect to the /reviews/ section
$external_allowed_pages = array(   
                              '/help/staff/index.php'=>1,
                              '/help/staff/search.php'=>1,
                              '/help/staff/toolbar.php'=>1,
                              '/help/staff/toc_title.php'=>1,
                              '/help/staff/toc.php'=>1,
                              '/help/staff/display_page.php'=>1,
                              '/reviews/index.php'=>1,
                              '/reviews/start.php'=>1,
                              '/reviews/finish.php'=>1
                            );
if ($userObject->has_role('External Examiner')) {
  if (!isset($external_allowed_pages[$_SERVER['PHP_SELF']])) {
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/reviews/");
  }
}

// Get teams if the user is not an External Examiner.
if ($userObject->has_role('External Examiner')) {
  $staff_modules = array();
} else {
  $staff_modules = $userObject->get_staff_modules();
}

$types_array = array('Formative Self-Assessments', 'Progress Tests', 'Summative Exams', 'Surveys', 'OSCE Stations', 'Offline Papers', 'Peer Review');

// The SYSTEM module is special, get its ID
$system_id = module_utils::get_idMod('SYSTEM', $mysqli);

//
//default security checks
//

if ($userObject->has_role('SysAdmin')) {
  //sysadmin can access any thing !!!

} elseif ($userObject->has_role('Admin')) {
  //For 'Admin' users look up permissions from 'admin_access' table.

  $on_staff_module = false;
  $failed_paper = false;

  //Can the user access the paper?
  if ((isset($_GET['paperID']) and $_GET['paperID'] != '') or (isset($_POST['paperID']) and $_POST['paperID'] != '')) {
    $tmp_paperID = $_REQUEST['paperID'];

    if (substr($tmp_paperID, 0, 5) != 'find:') {

      // Get the moduleID of the paper
      $moduleIDs = Paper_utils::get_modules($tmp_paperID, $mysqli);
      $module_in = "'" . implode("','",array_keys($moduleIDs)) . "'";

      // Check which Schools the moduleID(s) are in.
      $result = $mysqli->prepare("SELECT school FROM schools, modules, admin_access WHERE admin_access.userID=? AND admin_access.schools_id=schools.id AND schools.id=modules.schoolid AND modules.id IN ($module_in)"); 
      $result->bind_param('i',$userObject->get_user_ID());
      $result->execute();
      $result->store_result();
      $result->bind_result($paper_school);
      if ($result->num_rows() > 0) {
        $on_staff_module = true;
      } else {
        $on_staff_module = false;
      }
      $result->close();

      // Override access for the system training module.
      if ($on_staff_module == false and strpos($moduleID, 'SYSTEM') === false) {
        //TODO Why is this here!!
        $failed_paper = true;
      }
    }
  }


  if ($on_staff_module == false and isset($_GET['module']) and $_GET['module'] != '') {
    // Is 'Admin' on the module as normal
    $result = $mysqli->prepare("SELECT groupID FROM modules_staff WHERE memberID=? AND name=?");
    $result->bind_param('is', $userObject->get_user_ID(), $_GET['module']);
    $result->execute();
    $result->store_result();
    $result->bind_result($groupID);
    if ($result->num_rows() > 0) {
      $on_staff_module = true;
    } else {
      $on_staff_module = false;
    }
    $result->close();

    if ($on_staff_module == false) { // Check which Schools the moduleID(s) are in and match school to 'Admin' person.
      $result = $mysqli->prepare("SELECT school FROM schools, modules, admin_access WHERE admin_access.userID=? AND admin_access.schools_id=schools.id AND schools.id=modules.schoolid AND moduleid=?");
      $result->bind_param('is', $userObject->get_user_ID(), $_GET['module']);
      $result->execute();
      $result->store_result();
      $result->bind_result($paper_school);
      if ($result->num_rows() > 0) {
        $on_staff_module = true;
      } else {
        $on_staff_module = false;
      }
      $result->close();
    }

    // Override access for the system training module.
    if ($on_staff_module == false and strpos($_GET['module'], 'SYSTEM') === false) {
      $message = ($failed_paper) ? $string['denied_paper'] : $string['denied_folder'];
      UserNotices::access_denied($message, false);
    }
  }

  // Check if user has access to the questions
  if (isset($_REQUEST['q_id'])) {
    $tmp_q_id = ltrim($_REQUEST['q_id'], ',');
    $q_id_list = explode(',', $tmp_q_id);
    foreach ($q_id_list as $tmp_q_id) {
      if ($tmp_q_id != -1) {
        $group_array = array();

        $result = $mysqli->prepare("SELECT qm.idMod, q.ownerID FROM questions q INNER JOIN questions_modules qm ON q.q_id=qm.q_id WHERE q.q_id=?");
        $result->bind_param('i', $tmp_q_id);
        $result->execute();
        $result->store_result();
        $result->bind_result($q_group, $ownerID);
        while($result->fetch()) {
          $group_array[] = $q_group;
        }

        if ($ownerID != $userObject->get_user_ID() and !in_array($system_id, $group_array) and count(array_intersect($group_array, array_keys($staff_modules))) == 0) {
          // Not owner or on staff_module so check schools
          $is_admin = false;
          if (count($group_array) > 0) {
            $module_list = "'" . str_replace(';', ',', $q_group) . "'";
            $result2 = $mysqli->prepare("SELECT school FROM schools, modules, admin_access WHERE admin_access.userID=? AND admin_access.schools_id=schools.id AND schools.id=modules.schoolid AND moduleid IN ({$module_list})");
            $result2->bind_param('i', $userObject->get_user_ID());
            $result2->execute();
            $result2->store_result();
            if ($result2->num_rows > 0) {
              $is_admin = true;
            }
            $result2->close();
          }

          if (!$is_admin) {
            $result->close();
            UserNotices::access_denied($string['denied_question'], false);
          }
        }
        $result->close();
      }
    }
  }

} else {
  //normal users can access things they own or are on the staff_module of only !!
  $on_staff_module = false;

  //can the user acsess the module
  if (isset($_GET['module']) and $_GET['module'] != '' and !isset($_GET['paperID'])) {
    $staff_module_match = false;
    $module_list = explode(',', $_GET['module']);
    foreach ($module_list as $module) {
      if ($userObject->is_staff_user_on_module((int)$module)) {
        $staff_module_match = true;
      }
    }
    if ($staff_module_match == false) {
      
      UserNotices::access_denied(str_replace('%s', module_utils::get_moduleid_from_id($_GET['module'], $mysqli), $string['denied_team']), false);
      //access_denied(str_replace('%s', $_GET['module'], $string['denied_team']), false);
    }
  }

  //Can the user acsess the paper?
  if ((isset($_REQUEST['paperID']) and $_REQUEST['paperID'] != '')) {
    $tmp_paperID = $_REQUEST['paperID'];
    if (substr($tmp_paperID, 0, 5) != 'find:') {
      $paper_ownerID = Paper_utils::get_ownerID($tmp_paperID, $mysqli);
    
      if ($paper_ownerID == $userObject->get_user_ID()) {
        $on_staff_module = true;
      } else {
        $paper_modules = Paper_utils::get_modules($tmp_paperID, $mysqli);
        foreach ($paper_modules as $paper_moduleID=>$paper_module) {
          if ($userObject->is_staff_user_on_module($paper_moduleID)) {
            $on_staff_module = true;
          }
        }
      }

      if ($on_staff_module == false and !in_array('SYSTEM', array_values($paper_modules))) {
        UserNotices::access_denied($string['denied_paper'], false);
      }
    }
  }

  //Can the user access the question?
  if (isset($_REQUEST['q_id'])) {
    $tmp_q_id = ltrim($_REQUEST['q_id'], ',');
    $q_id_list = explode(',', $tmp_q_id);
    foreach ($q_id_list as $tmp_q_id) {
      if ($tmp_q_id != -1) {
        $access = false;
        
        $ownerID = QuestionUtils::get_ownerID($tmp_q_id, $mysqli);
        
        if ($ownerID == $userObject->get_user_ID()) {
          $access = true;
        } else {
          $question_modules = QuestionUtils::get_modules($tmp_q_id, $mysqli);
          foreach ($question_modules as $question_moduleID=>$question_module) {
            if ($userObject->is_staff_user_on_module($question_moduleID)) {
              $access = true;
            }
          }
        }

        //allow acsess to the TRAIN module for training!
        if ($access == false and !in_array('SYSTEM', array_values($question_modules))) {
          UserNotices::access_denied($string['denied_question'], false);
        }
      }
    }
  }
}
?>