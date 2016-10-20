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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'staff_student_auth.inc';
$userObject = UserObject::get_instance();

if (!$userObject->has_role(array('Staff', 'External Examiner', 'SysCron', 'Invigilator', 'Internal Reviewer'))) {
  // Current user is not Staff, External Examiner or the system cron so display warning and exit.
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '/artwork/exclamation_48.png', '#C00000', true, true);
}

// Redirect External Examiners if they are straying
// $external_allowed_pages is an array that contains the set of scripts that External Examiners 
// CAN access all other pages should redirect to the /reviews/ section
$cfg_root_path = $configObject->get('cfg_root_path');
$external_allowed_pages = array(   
                              "$cfg_root_path/help/staff/index.php"=>1,
                              "$cfg_root_path/help/staff/search.php"=>1,
                              "$cfg_root_path/help/staff/toolbar.php"=>1,
                              "$cfg_root_path/help/staff/toc_title.php"=>1,
                              "$cfg_root_path/help/staff/toc.php"=>1,
                              "$cfg_root_path/help/staff/display_page.php"=>1,
                              "$cfg_root_path/reviews/index.php"=>1,
                              "$cfg_root_path/reviews/start.php"=>1,
                              "$cfg_root_path/reviews/finish.php"=>1,
                              "$cfg_root_path/reviews/class_totals.php"=>1,
                              "$cfg_root_path/reports/draw_distribution_chart.php"=>1,
                              "$cfg_root_path/reports/draw_scatter_plot.php"=>1,
                              "$cfg_root_path/ajax/reports/getNote.php"=>1,
                              "$cfg_root_path/ajax/reports/getAccessibility.php"=>1,
                              "$cfg_root_path/osce/form.php"=>1
                            );

$internal_allowed_pages = array(   
                              "$cfg_root_path/help/staff/index.php"=>1,
                              "$cfg_root_path/help/staff/search.php"=>1,
                              "$cfg_root_path/help/staff/toolbar.php"=>1,
                              "$cfg_root_path/help/staff/toc_title.php"=>1,
                              "$cfg_root_path/help/staff/toc.php"=>1,
                              "$cfg_root_path/help/staff/display_page.php"=>1,
                              "$cfg_root_path/reviews/index.php"=>1,
                              "$cfg_root_path/reviews/start.php"=>1,
                              "$cfg_root_path/reviews/finish.php"=>1
                            );

$invigilator_allowed_pages = array(   
                              "$cfg_root_path/ajax/invigilator/refresh_cohort_list.php"=>1,
                              "$cfg_root_path/ajax/invigilator/toilet_break.php"=>1,
                              "$cfg_root_path/invigilator/check_exam_announcements.php"=>1,
                              "$cfg_root_path/invigilator/check_unfinish_exam.php"=>1,
                              "$cfg_root_path/invigilator/do_unfinish_exam.php"=>1,
                              "$cfg_root_path/invigilator/extend_time.php"=>1,
                              "$cfg_root_path/invigilator/index.php"=>1,
                              "$cfg_root_path/invigilator/new_paper_note.php"=>1,
                              "$cfg_root_path/invigilator/new_student_note.php"=>1,
                              "$cfg_root_path/invigilator/test.php"=>1
                            );

$standards_setter_allowed_pages = array(
                                      "$cfg_root_path/index.php"=>1,
                                      "$cfg_root_path/module/index.php"=>1,
                                      "$cfg_root_path/paper/type.php"=>1,
                                      "$cfg_root_path/std_setting/index.php"=>1,
                                      "$cfg_root_path/std_setting/individual_review.php"=>1,
                                      "$cfg_root_path/std_setting/hofstee.php"=>1,
                                      "$cfg_root_path/std_setting/get_group.php"=>1,
                                      "$cfg_root_path/std_setting/group_set_angoff.php"=>1,
                                      "$cfg_root_path/delete/check_delete_review.php"=>1,
                                      "$cfg_root_path/delete/do_delete_review.php"=>1
                            );

$standards_setter_allowed_pages = array_merge($external_allowed_pages, $standards_setter_allowed_pages);                           

if ($userObject->has_role('External Examiner')) {
  if (!isset($external_allowed_pages[$_SERVER['PHP_SELF']])) {
    header("location: " . $configObject->get('cfg_root_path') . "/reviews/");
  }
} elseif ($userObject->has_role('Internal Reviewer')) {
  if (!isset($internal_allowed_pages[$_SERVER['PHP_SELF']])) {
    header("location: " . $configObject->get('cfg_root_path') . "/reviews/");
  }
} elseif ($userObject->has_role('Invigilator')) {
  if (!isset($invigilator_allowed_pages[$_SERVER['PHP_SELF']])) {
    header("location: " . $configObject->get('cfg_root_path') . "/invigilator/");
  }
} elseif($userObject->has_role('Standards Setter')) {
  if (!isset($standards_setter_allowed_pages[$_SERVER['PHP_SELF']])) {
    header("location: " . $configObject->get('cfg_root_path') . "/");
  }
}

// Redirect the system cron if it is straying
// $cron_allowed_pages is an array that contains the set of scripts that the system cron
// CAN access all other pages should exit the cron
$cron_allowed_pages = array("$cfg_root_path/testing/testing/class_totals_with_script_cli.php"=>1,
                            "$cfg_root_path/reports/class_totals.php"=>1,
                            "$cfg_root_path/reports/draw_distribution_chart.php"=>1,
                            "$cfg_root_path/reports/draw_scatter_plot.php"=>1);

if ($userObject->has_role('SysCron')) {
  if (!isset($cron_allowed_pages[$_SERVER['PHP_SELF']])) {
    $logger = new Logger($mysqli);
    $logger->record_access_denied($userObject->get_user_ID(), $_SERVER['PHP_SELF'], $string['crondisallowed']);
    exit;
  }
}

// Get teams if the user is not an External Examiner, internal reviewer, sys cron or and invigilator.
if ($userObject->has_role('External Examiner') or $userObject->has_role('Internal Reviewer') or $userObject->has_role('SysCron') or $userObject->has_role('Invigilator')) {
  $staff_modules = array();
} else {
  $staff_modules = $userObject->get_staff_modules();
}

$types_array = array('Formative Self-Assessments', 'Progress Tests', 'Summative Exams', 'Surveys', 'OSCE Stations', 'Offline Papers', 'Peer Review');

// The SYSTEM module is special, get its ID
$system_id = module_utils::get_idMod('SYSTEM', $mysqli);

//
// Default security checks for all pages.
//

if ($userObject->has_role('SysAdmin')) {
  // SysAdmin can access anything!
} elseif ($userObject->has_role('External Examiner') or $userObject->has_role('Internal Reviewer') or $userObject->has_role('SysCron') or $userObject->has_role('Invigilator')) {
} else {
  // Normal users can access things they own or are on the staff_module of only.
  $on_staff_module = false;

  // Can the user acsess the module?
  if (isset($_GET['module']) and $_GET['module'] != '' and $_GET['module'] != '0' and !isset($_GET['paperID'])) {
    $staff_module_match = false;
    $module_list = explode(',', $_GET['module']);
    foreach ($module_list as $module) {
      if ($userObject->is_staff_user_on_module((int)$module)) {
        $staff_module_match = true;
      }
    }
    if ($staff_module_match == false) {
      $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
      $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['accessdenied'], '/artwork/page_not_found.png', '#C00000', true, true);
    }
  }

  // Can the user acsess the paper?
  if ((isset($_REQUEST['paperID']) and !($_REQUEST['paperID'] == '' or  $_REQUEST['paperID'] == '-1') )) {
    $tmp_paperID = $_REQUEST['paperID'];
    if (substr($tmp_paperID, 0, 5) != 'find:' and substr($tmp_paperID, 0, 5) != 'list:') {
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
        $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
        $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['accessdenied'], '/artwork/page_not_found.png', '#C00000', true, true);
      }
    }
  }

  // Can the user access the question?
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

        // Allow acsess to the TRAIN module for training!
        if ($access == false and !in_array('SYSTEM', array_values($question_modules))) {
          $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
          $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['accessdenied'], '/artwork/page_not_found.png', '#C00000', true, true);
        }
      }
    }
  }
  
  // Can the user access reference material?
  if (isset($_REQUEST['refID'])) {
    if (!refmaterials_utils::check_access($userObject, $_REQUEST['refID'], $mysqli)) {
      $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
      $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['accessdenied'], '/artwork/page_not_found.png', '#C00000', true, true);
    }
  }
}
?>