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
* Displays three main parts of a module: 1) Papers, 2) Question Bank and 3) Users.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/icon_display.inc';
require_once '../include/sidebar_menu.inc';
require_once '../include/errors.php';
require_once '../include/demo_replace.inc';

$module = check_var('module', 'GET', true, false, true);

if ((int)$module != $module) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
$add_member = false;

if (!isset($module_details) and $_GET['module'] != '0') {
  $module_details = module_utils::get_full_details_by_ID($module, $mysqli);

  if (!$module_details) {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  } elseif ($module_details['active'] == 0) {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);	
  }
} else {
  $module_details['moduleid'] = 'Unassigned';
  $module_details['fullname'] = 'Questions/papers not on any module'; 
  $module_details['checklist'] = '';
}

$_SESSION['nav_page'] = $_SERVER['SCRIPT_NAME'];
$_SESSION['nav_query'] = $_SERVER['QUERY_STRING'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;<?php echo ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style>
    a:link {color:black}
    .red {background-color:#C00000; color:white; padding-left:2px; padding-right:2px}
    .subsect_table {margin-top:22px; margin-left:10px; margin-bottom:12px}
    #addteammember {cursor:pointer}
  </style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/sidebar.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <?php 
    if ($module != '0') {
      echo "<script type=\"text/javascript\" src=\"../js/module.js\"></script>";
    }
    ?>

  <script>
    $(function () {
   
      $('#addteammember').click(function() {
        notice = window.open("edit_team_popup.php?module=<?php echo $module ?>&calling=paper_list&folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>","properties","width=450,height="+(screen.height-200)+",left="+(screen.width/2-325)+",top=10,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
        if (window.focus) {
            notice.focus();
         }
      });

    });
  </script>
</head>

<body>
<?php
  require '../include/module_options.inc';
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
?>
<div id="content">
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a></div>
  <div class="page_title">Module: <span style="font-weight:normal"><?php echo $module_details['moduleid'] ?></span></div>
</div>
<?php

  // Is it a self-enrol module.
  if (isset($module_details['selfenroll']) and $module_details['selfenroll'] == 1) {
    $selfenrol_url = NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/self_enrol.php?moduleid=' . $module_details['moduleid'];
    echo "<br /><div style=\"margin-left:auto; margin-right:auto; width:500px\"><img src=\"../artwork/self_enrol.png\" width=\"48\" height=\"48\" alt=\"modules\" style=\"float:left; margin-right:10px\" /> <div style=\"color:#F18103; font-weight:bold; line-height:200%\">" . $string['selfenrolmodule'] . "</div>" . $string['studenturl'] . ": <a href=\"$selfenrol_url\" style=\"color:#316ac5\">$selfenrol_url</a></div>\n";
  }


// Paper type folders
echo "<div class=\"subsect_table\" style=\"clear:both\"><div class=\"subsect_title\">" . $string['papers'] . "</div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";

$stateutil = new StateUtils($userObject->get_user_ID(), $mysqli);
$state = $stateutil->getState($configObject->get('cfg_root_path') . '/paper/type.php');

if (isset($state['showretired']) and $state['showretired'] == 'true') {
  $types_used = module_utils::paper_types($module, true, $mysqli);
} else {
  $types_used = module_utils::paper_types($module, false, $mysqli);
}
foreach ($types_used as $type=>$no_papers) {
  if(!($userObject->has_role('Standards Setter') && $type >= 3)) {
    $url = '../paper/type.php?module=' . $module . '&type=' . $type;
    echo "<div class=\"f2\"><div class=\"f_icon\"><a href=\"$url\"><img src=\"../artwork/yellow_folder.png\" alt=\"Folder\" /></a></div><div class=\"f_details\"><a href=\"$url\">" . Paper_utils::type_to_name($type, $string) . "</a><br /><span class=\"grey\">" . number_format($no_papers) . " " . strtolower($string['papers']) . "</span></div></div>\n";
  }
}

if(!$userObject->has_role('Standards Setter')) {
  echo "<br clear=\"left\">\n";
  echo "<div class=\"f2\"><div class=\"f_icon\"><a href=\"../paper/search.php?module=$module\"><img src=\"../artwork/search_48.png\" alt=\"Folder\" /></a></div><div class=\"f_details\"><a href=\"../paper/search.php?module=$module\">" . $string['search'] . "</a><br /><span class=\"grey\">" . $string['forpapers'] . "</span></div></div>\n";
  if ($module != 0) {
    // Don't want new papers created from the Unassigned folder.
    echo "<div class=\"f2\"><div class=\"f_icon\"><a href=\"\" onclick=\"newPaper($module); return false;\"><img src=\"../artwork/new_paper_48.png\" alt=\"" . $string['newpaper'] . "\" /></a></div><div class=\"f_details\"><a href=\"\" onclick=\"newPaper($module); return false;\">" . $string['newpaper'] . "</a></div></div>\n";
  }
  // Question bank section
  echo "<br clear=\"left\">\n";
  echo "<div class=\"subsect_table\" style=\"clear:both\"><div class=\"subsect_title\"><nobr>" . $string['questionbank'] . "</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";


  $qbank = new QuestionBank($module, $module_details['moduleid'], $string, $notice, $mysqli);
  $qbank->get_categories('all');
  $stats = $qbank->get_stats('all');
  $question_no = 0;
  foreach ($stats as $stat_name=>$stat_no) {
    $question_no += $stat_no;
  }

  echo "<div class=\"f2\"><div class=\"f_icon\"><a href=\"../question/list.php?type=all&module=$module\"><img src=\"../artwork/yellow_folder.png\" alt=\"Folder\" /></a></div><div class=\"f_details\"><a href=\"../question/list.php?type=all&module=$module\">" . $string['allquestions'] . "</a><br /><span class=\"grey\">" . number_format($question_no) . " " . strtolower($string['questions']) . "</span></div></div>\n";

  $bank_types = array($string['bykeyword']=>'../question/bank.php?type=keyword&module=' . $module, $string['byquestiontype']=>'../question/bank.php?type=type&module=' . $module, $string['bystatus']=>'../question/bank.php?type=status&module=' . $module, $string['bybloom']=>'../question/bank.php?type=bloom&module=' . $module, $string['byperformance']=>'../question/bank.php?type=performance&module=' . $module);
  if (strpos($module_details['checklist'], 'mapping') !== false) {
    $bank_types[$string['byobjective']] = '../question/bank.php?type=objective&module=' . $module;
  }
  foreach ($bank_types as $type_name=>$url) {
    echo "<div class=\"f2\"><div class=\"f_icon\"><a href=\"$url\"><img src=\"../artwork/yellow_folder.png\" alt=\"Folder\" /></a></div><div class=\"f_details\"><a href=\"$url\">" . $type_name . "</a></div></div>\n";
  }
  echo "<br clear=\"left\">\n";
  echo "<div class=\"f2\"><div class=\"f_icon\"><a href=\"../question/search.php?module=$module\"><img src=\"../artwork/search_48.png\" alt=\"Folder\" /></a></div><div class=\"f_details\"><a href=\"../question/search.php?module=$module\">" . $string['search'] . "</a><br /><span class=\"grey\">" . $string['forquestions'] . "</span></div></div>\n";
  if ($module != 0) {   // Don't want new questions created from the Unassigned folder.
    echo "<div class=\"f2\"><div class=\"f_icon\"><a href=\"#\" onclick=\"newQuestion($module); return false;\"><img src=\"../artwork/new_question.png\" alt=\"" . $string['newquestion'] . "\" /></a></div><div class=\"f_details\"><a href=\"\" onclick=\"newQuestion($module); return false;\">" . $string['newquestion'] . "</a></div></div>\n";
  }

  // User section
  echo "<br clear=\"left\">\n";
  echo "<div class=\"subsect_table\" style=\"clear:both\"><div class=\"subsect_title\">" . $string['users'] . "</div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";

  echo "<div class=\"f2\"><div class=\"f_icon\"><a href=\"../users/search.php?module=$module\"><img src=\"../artwork/search_48.png\" alt=\"Folder\" /></a></div><div class=\"f_details\"><a href=\"../users/search.php?module=$module\">" . $string['search'] . "</a><br /><span class=\"grey\">" . $string['forusers'] . "</span></div></div>\n";

  if ($_GET['module'] != '0') {
    $yearutils = new yearutils($mysqli);
    $current_year = $yearutils->get_current_session($module_details['academic_year_start']);
    $academic_year = $yearutils->get_academic_session($current_year);
    $student_cohort = module_utils::get_student_members($current_year, $module, $mysqli);

    $url = '../users/search.php?submit=Search&module=' . $module . '&calendar_year=' . $current_year . '&students=on&search_username=&student_id=';
    $student_no = count($student_cohort);
    if ($student_no == 0) {
      $student_class = 'red';
    } else {
      $student_class = 'grey';
    }
    echo "<div class=\"f2\"><div class=\"f_icon\"><a href=\"$url\"><img src=\"../artwork/user_accounts_icon.png\" alt=\"Folder\" /></a></div><div class=\"f_details\"><a href=\"$url\">" . sprintf($string['studentlist'], $academic_year) . "</a><br /><span class=\"$student_class\">" . number_format($student_no) . " " . $string['students'] . "</span></div></div>\n";

    $url = '../users/import_users_metadata.php?module=' . $module;
    echo "<div class=\"f2\"><div class=\"f_icon\"><a href=\"$url\"><img src=\"../artwork/user_metadata_48.png\" alt=\"Folder\" /></a></div><div class=\"f_details\"><a href=\"$url\">" . $string['addmetadata'] . "</a><br /><span class=\"grey\">" . sprintf($string['extradataaboutstudents'], $module_details['moduleid']) . "</span></div></div>\n";
  }
}

$mysqli->close();
?>
</div>

</body>
</html>