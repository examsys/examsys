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
require_once '../include/errors.php';
require_once '../include/demo_replace.inc';
require_once '../include/sidebar_menu.inc';

$module = check_var('module', 'GET', true, false, true);
$type = check_var('type', 'GET', true, false, true);

$stateutil = new StateUtils($userObject->get_user_ID(), $mysqli);
$state = $stateutil->getState();

if ($_GET['module'] != '0') {
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
  <style type="text/css">
  <?php
    if (isset($state['showretired']) and $state['showretired'] == 'true') {
      echo ".retired {display:block}\n";
    } else {
      echo ".retired {display:none}\n";
    }
    ?>
    a {color: black !important}
    .sum_cal {margin-left: 28px; margin-top: 10px; margin-bottom: 12px}
    .subsect_table {margin-left: 10px; margin-bottom: 8px}
	</style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/sidebar.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    function newPaper() {
      notice = window.open("../paper/new_paper1.php?module=<?php echo $module ?>&type=<?php echo $type ?>","paper","width=700,height=500,left="+(screen.width/2-325)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        notice.focus();
      }
    }
    
    function updatePaperCount() {
      var n = $(".file:visible").length;
      var papercount = n.toLocaleString("<?php echo $language ?>");
      
      var decimals = papercount.indexOf('.');
        if (decimals !== -1) {
          papercount = papercount.slice(0, decimals);
        }
      $("#paper_count").text(papercount);
    }
    
    $(function () {
      $('#showretired').click(function() {
        $('.retired').toggle();
        updatePaperCount();
      });
      
      updatePaperCount();
    });
  </script>
</head>

<body>
<?php
  require '../include/module_options.inc';
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
  
  if (isset($state['showretired']) and $state['showretired'] == 'true') {
    $types_used = module_utils::paper_types($module, true, $mysqli);
  } else {
    $types_used = module_utils::paper_types($module, false, $mysqli);    
  }
?>
<div id="content">
        
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
<?php
  echo "<div style=\"position:absolute; right: 6px; top: 24px\"><label><input class=\"chk\" type=\"checkbox\" name=\"showretired\" id=\"showretired\" value=\"on\"\"";
  if (isset($state['showretired']) and $state['showretired'] == 'true') echo ' checked="checked"';
  echo " />" . $string['showretired'] . "</label></div>\n";
?>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=<?php echo $module ?>"><?php echo $module_details['moduleid'] ?></a></div>
  <div class="page_title"><?php echo $string['papers'] ?>: <span style="font-weight:normal"><?php echo $string[strtolower($types_array[$type])] ?> (<span id="paper_count"><?php echo $types_used[$type] ?></span>)</div>
</div>

<?php
if (!$userObject->has_role('Standards Setter') && $module != 0) {
  // Don't want new papers created from the Unassigned folder.
  echo "<br /><div class=\"f\"><div class=\"f_icon\"><a href=\"\" onclick=\"newPaper(); return false;\"><img src=\"../artwork/new_paper_48.png\" alt=\"" . $string['newpaper'] . "\" /></a></div><div class=\"f_details\"><a href=\"\" onclick=\"newPaper(); return false;\">" . $string['newpaper'] . "</a></div></div>\n";
}

if (!$userObject->has_role('Standards Setter') && $_GET['type'] == 2) {
  echo "<div class=\"f\"><div class=\"f_icon\"><a href=\"../admin/calendar.php#week" . date("W") . "\"><img src=\"../artwork/calendar_icon.png\" alt=\"Folder\" /></a></div><div class=\"f_details\"><a href=\"../admin/calendar.php#week" . date("W") . "\">" . $string['examcalendar'] . "<br />" . date('Y') . "</a></div></div>\n";
}

// UPDATED SQL query simplified removed the modules table as no data was coming from it. Also removed distinct as group by was doing it. The user data is returned but for some reason the icons alt tags (that contain the user data don't display
if ($_GET['module'] != '0') {
  $sql = "SELECT calendar_year, paper_ownerID, properties.property_id, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'{$configObject->get('cfg_long_date_time')}') AS display_start_date, DATE_FORMAT(end_date,'{$configObject->get('cfg_long_date_time')}') AS display_end_date, exam_duration, title, initials, surname, retired, properties.password FROM (properties, properties_modules, users) LEFT JOIN papers ON properties.property_id = papers.paper WHERE properties.property_id = properties_modules.property_id AND properties_modules.idMod = ? AND paper_type = ? AND properties.paper_ownerID = users.id  AND deleted IS NULL GROUP BY paper_title ORDER BY calendar_year DESC, paper_title";
  $results = $mysqli->prepare($sql);
  $results->bind_param('is', $module, $type);
} else {
  $sql = "SELECT calendar_year, paper_ownerID, properties.property_id, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'{$configObject->get('cfg_long_date_time')}') AS display_start_date, DATE_FORMAT(end_date,'{$configObject->get('cfg_long_date_time')}') AS display_end_date, exam_duration, title, initials, surname, retired, properties.password FROM (properties, users) LEFT JOIN properties_modules ON properties.property_id = properties_modules.property_id LEFT JOIN papers ON properties.property_id = papers.paper WHERE properties_modules.idMod IS NULL AND paper_type = ? AND properties.paper_ownerID = users.id AND paper_ownerID = ? AND deleted IS NULL GROUP BY paper_title ORDER BY calendar_year DESC, paper_title";
  $results = $mysqli->prepare($sql);
  $results->bind_param('si', $type, $userObject->get_user_ID());
}
$results->execute();
$results->bind_result($calendar_year, $paper_ownerID, $property_id, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $password);
$results->store_result();
$old_calendar_year = 'zzzz';
$sent_clear_all = false;
if ($results->num_rows > 0) {
  while ($results->fetch()) {
    if ($old_calendar_year != $calendar_year) {
      if ($sent_clear_all) {
        echo "<br clear=\"left\" />";
      }
      $sent_clear_all = true;
      
      if ($calendar_year == '') {
        $display_calendar_year = $string['unspecifiedsession'];
      } else {
        $display_calendar_year = $calendar_year;      
      }

      echo "<div class=\"subsect_table\"><div class=\"subsect_title\"><nobr>" . $display_calendar_year . "</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
    }
    display_paper_icon($paper_ownerID, $property_id, $type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $password, $userObject); 
    $old_calendar_year = $calendar_year;
  }
  $results->close();
}
$mysqli->close();
?>
</div>

</body>
</html>