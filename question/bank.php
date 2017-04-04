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
* Displays a list of papers.
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
require_once '../include/mapping.inc';

require_once '../classes/questionbank.class.php';

$stateutil = new StateUtils($userObject->get_user_ID(), $mysqli);
$state = $stateutil->getState();

$type = check_var('type', 'GET', true, false, true);
$module = check_var('module', 'GET', true, false, true);

if ($module == 0) {
  $module_details['moduleid'] = 'Unassigned';
  $module_details['active'] = 1;
} else {
  $module_details = module_utils::get_full_details_by_ID($module, $mysqli);
}

if ($module != 0 and strpos($module_details['checklist'], 'mapping') === false and $_GET['type'] == 'objective') {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (!$module_details) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
} elseif ($module_details['active'] == 0) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);	
}

$qbank = new QuestionBank($module, $module_details['moduleid'], $string, $notice, $mysqli);

$_SESSION['nav_page'] = $_SERVER['SCRIPT_NAME'];
$_SESSION['nav_query'] = $_SERVER['QUERY_STRING'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['questionbank'] . ' ' . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style>
    p { margin: 0; padding: 0}
    .subsect_table {margin-left: 12px; margin-bottom: 8px}
  </style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/sidebar.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    $(function() {
      $(document).click(function() {
        hideMenus();
      });
    });
  </script>
</head>

<body>
<?php
  require '../include/module_options.inc';
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
  
  if ($type == 'type') {
    $display_type = $string['bytype']; 
    $zero_warning = $string['noquestions'];
  } elseif ($type == 'bloom') {
    $display_type = $string['byblooms']; 
    $zero_warning = $string['noquestionsbloom'];
  } elseif ($type == 'keyword') {
    $display_type = $string['bykeyword'];
    $zero_warning = $string['noquestionskeyword'];
  } elseif ($type == 'status') {
    $display_type = $string['bystatus'];
    $zero_warning = $string['noquestionsstatus'];
  } elseif ($type == 'performance') {
    $display_type = $string['byperformance'];
    $zero_warning = $string['noquestionsperformance'];
  } elseif ($type == 'objective') {
    $display_type = $string['byobjective'];
    $zero_warning = $string['noquestionsobjective'];
  }
?>
<div id="content">
  
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=<?php echo $module ?>"><?php echo $module_details['moduleid'] ?></a></div>
  <div class="page_title"><?php echo $string['questionbank'] ?>: <span style="font-weight:normal"><?php echo $display_type ?></span></div>
</div>
<?php
$bank_types = $qbank->get_categories($type);
$stats      = $qbank->get_stats($type);

if (count($stats) == 0) {
	echo $notice->info_strip($zero_warning, 100) . "\n</div>\n</body>\n</html>";
  exit;
}

if ($type != 'keyword') {
  echo "<br />\n";
}
if ($type == 'performance') {
  echo "<div class=\"subsect_table\"><div class=\"subsect_title\"><nobr>" . $string['bydifficulty'] . "</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
}

$old_section = '';

foreach ($bank_types as $id=>$type_name) {
  $grey_text = '';
  $url = 'list.php?type=' . $type . '&subtype=' . $id . '&module=' . $module;
  
  if ($type == 'keyword') {
    if ($old_section != $type_name{0}) {
      echo "<br clear=\"all\" />\n";
      echo "<div class=\"subsect_table\"><div class=\"subsect_title\"><nobr>" . $type_name{0} . "</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
    }
    $old_section = $type_name{0};
  } elseif ($type == 'performance' and $id == 'highest') {
    echo "<br clear=\"left\" />\n";
    echo "<div class=\"subsect_table\"><div class=\"subsect_title\"><nobr>" . $string['bydiscrimination'] . "</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
  } elseif ($type == 'objective') {
    $ids = $type_name['ids'];
    $type_name = $type_name['label'];

    $q_count = 0;
    foreach ($ids as $o_id) {
      if (isset($stats[$o_id])) {
        $q_count += $stats[$o_id];
      }
    }
    $stats[$id] = $q_count;
  }

  if ($_GET['type'] == 'objective') {
    $class = 'f100';
  } else {
    $class = 'f2';
  }
  
  if (isset($stats[$id])) {
    if (($type != 'objective' and $type != 'performance') or $stats[$id] > 0) {
      $grey_text = number_of_questions($stats[$id], $string);
      echo display_folder($url, $type_name, $grey_text, $class);
    }
  } elseif (isset($stats[$type_name])) {
    $grey_text = number_of_questions($stats[$type_name], $string);
    echo display_folder($url, $type_name, $grey_text, $class);
  }
}

function number_of_questions($question_no, $string) {
  $html = '<br /><span class="grey">' . number_format($question_no) . ' ';
  if ($question_no == 1) {
    $html .= $string['question'];
  } else {
    $html .= $string['questions'];
  }
  $html .= '</span>';

  return $html;
}

function display_folder($url, $type_name, $grey_text, $class) {
  $type_name = strip_tags($type_name);
  return "<div class=\"$class\"><div class=\"f_icon\"><a href=\"$url\"><img src=\"../artwork/yellow_folder.png\" alt=\"Folder\" /></a></div><div class=\"f_details\"><a href=\"$url\" class=\"blacklink\">" . $type_name . "</a>$grey_text</div></div>\n";
}
$mysqli->close();
?>
</div>

</body>
</html>