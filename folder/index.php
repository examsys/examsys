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
require_once '../include/demo_replace.inc';

$folder = check_var('folder', 'GET', true, false, true);

function getLastFolder($path) {
  $parts = explode(';' , $path);
  $part_no = count($parts);

  if ($part_no > 0) {
    return $parts[$part_no-1];
  } else {
    return $parts[0];
  }
}

$stateutil = new StateUtils($userObject->get_user_ID(), $mysqli);
$state = $stateutil->getState();

$folder_name = '';
$folder_type = '';
$file_no = 0;

// Folder security checks
$orig_folder_name = folder_utils::get_folder_name($folder, $mysqli);

if ($orig_folder_name == '') {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (!folder_utils::has_permission($folder, $userObject, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$parent_list = folder_utils::get_parent_list($orig_folder_name, $userObject, $mysqli);

$module = '';

if (isset($_POST['submit'])) {
  $folder_parent = folder_utils::get_folder_name($folder, $mysqli);
  
  $new_folder_name = $folder_parent . ';' . $_POST['folder_name'];

  $duplicate_folder = folder_utils::folder_exists($new_folder_name, $userObject, $mysqli);
  if ($duplicate_folder == false) {
    folder_utils::create_folder($new_folder_name, $userObject, $mysqli);
  }
}

$folders_array = explode(';', $orig_folder_name);
$parts = count($folders_array) - 1;
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
	</style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/sidebar.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    function deleteFolder() {
      notice=window.open("../delete/check_delete_folder.php?folderID=<?php echo $folder; ?>","notice","width=500,height=210,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      notice.moveTo(screen.width/2-210,screen.height/2-105);
      if (window.focus) {
        notice.focus();
      }
    }

    function folderProperties() {
      notice=window.open("properties.php?folder=<?php echo $folder; ?>","properties","width=600,height=600,left="+(screen.width/2-300)+",top="+(screen.height/2-300)+",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        notice.focus();
      }
    }

    $(function () {
      $('#showretired').click(function() {
        $('.retired').toggle();
      });
      
      $(document).click(function() {
        hideMenus();
      });
      
    });
  </script>
</head>

<body>
<?php
  require '../include/folder_options.inc';
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
?>
<div id="content">
<form name="myform" action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']; ?>" method="post" autocomplete="off">
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
<?php
  echo "<div style=\"position:absolute; right: 6px; top: 24px\"><input class=\"chk\" type=\"checkbox\" name=\"showretired\" id=\"showretired\" value=\"on\"\"";
  if (isset($state['showretired']) and $state['showretired'] == 'true') echo ' checked="checked"';
  echo " /> " . $string['showretired'] . "</div>\n";
?>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a>
<?php
if (count($parent_list) > 0) {
  foreach ($parent_list as $parent_id=>$parent_name) {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="index.php?folder=' . $parent_id . '">' . getLastFolder($parent_name) . '</a>';
  }
}
echo "</div>\n";

echo '<div class="page_title">';
if ($folder != '') {
  echo $folders_array[$parts];
} elseif ($_GET['module'] != '') {
  echo $module_details['moduleid'] . ': <span style="font-weight:normal">' . $module_details['fullname'] . '</span>';
}
echo '</div>';


echo "</div>\n<br />\n";

// Get any sub-folders first.
$tmp_string = '';
if (count($staff_modules) > 0) {
  $tmp_string = " OR idMod IN ('" . implode("','",array_keys($staff_modules)) . "')";
}

$tmp_folder_name = $orig_folder_name . ';%';
$folder_details = $mysqli->prepare("SELECT folders.id, name, color FROM folders WHERE (ownerID = ?) AND name LIKE ? AND deleted IS NULL ORDER BY name, folders.id");
$folder_details->bind_param('is', $userObject->get_user_ID(), $tmp_folder_name);
$folder_details->execute();
$folder_details->bind_result($id, $name, $color);
while ($folder_details->fetch()) {
  $display_name = str_replace("$orig_folder_name;","",$name);
  if (substr_count($display_name,';') == 0) {
    echo "<div class=\"f\" ><div class=\"f_icon\"><a href=\"../folder/index.php?folder=$id\"><img class=\"f_icon\" src=\"../artwork/" . $color . "_folder.png\" alt=\"Folder\" /></a></div><div class=\"f_details\"><a href=\"../folder/index.php?folder=$id\" class=\"blacklink\">$display_name</a></div></div>\n";
  }
}
$folder_details->close();

// New folder.
if (isset($_GET['newfolder']) and $_GET['newfolder'] == 'y' and !isset($_POST['submit'])) {
  echo "<div class=\"f\"><div class=\"f_icon\"><img src=\"../artwork/yellow_folder.png\" alt=\"Folder\" /></div><div class=\"f_details\"><input type=\"text\" size=\"30\" name=\"folder_name\" value=\"\" placeholder=\"" . $string['foldername'] . "\" required onkeypress=\"if (event.keyCode == 59) illegalChar(event.keyCode);\" /><br /><input type=\"submit\" name=\"submit\" class=\"ok\" style=\"width:90px; margin:1px; padding:3px\" value=\"" . $string['create'] . "\" /></div></div>\n";
}

// Get current owner papers.
$query_string = "SELECT DISTINCT paper_ownerID, property_id, paper_type, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'{$configObject->get('cfg_long_date_time')}') AS display_start_date, DATE_FORMAT(end_date,'{$configObject->get('cfg_long_date_time')}') AS display_end_date, exam_duration, title, initials, surname, retired, properties.password FROM (properties, users) LEFT JOIN papers ON properties.property_id=papers.paper WHERE properties.paper_ownerID=users.id AND folder=\"$folder\" AND deleted IS NULL GROUP BY paper_title ORDER BY paper_type, paper_title";
$results = $mysqli->prepare($query_string);
$results->execute();
$results->bind_result($paper_ownerID, $property_id, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $password);
$results->store_result();
$sent_clear_all = false;
if ($results->num_rows > 0) {
  while ($results->fetch()) {
    display_paper_icon($paper_ownerID, $property_id, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $password, $userObject);
    $file_no++;
  }
  $results->close();
}

$mysqli->close();
?>
</form>

</div>

</body>
</html>