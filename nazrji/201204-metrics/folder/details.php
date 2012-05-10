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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/icon_display.inc';
require '../include/sidebar_menu.inc';
require '../include/errors.inc';
require_once '../classes/stateutils.class.php';

$stateutil = new StateUtils();
$state = $stateutil->getState($userID, $mysqli);

$folder_name = '';
$folder_type = '';
$file_no = 0;
$add_member = false;
if (isset($_GET['module'])) {
  $module = $_GET['module'];
}

// Folder security checks
if (isset($_GET['folder'])) {
  $folder = $_GET['folder'];
} else {
  $folder = '';
}
if ($folder != '') {
  $tmp_folder = $_GET['folder'];
  $result = $mysqli->prepare("SELECT ownerID, name, team_name FROM folders WHERE id=?");
  $result->bind_param('i', $tmp_folder);
  $result->execute();
  $result->store_result();
  $result->bind_result($folder_ownerID, $folder_name, $team_name);
  $result->fetch();
  $result->close();

  if (isset($folder_teams) and $folder_teams != '' and $module == '') $module = $folder_teams;

  if (substr_count($folder_name,';') > 0) {
    $last_semicolon = strrpos($folder_name,';');
    $path = substr($folder_name,0,$last_semicolon);
    $parent_results = $mysqli->prepare("SELECT id, name FROM folders WHERE name=? AND ownerID=? LIMIT 1");
    $parent_results->bind_param('si', $path, $userID);
    $parent_results->execute();
    $parent_results->bind_result($parent_id, $parent_name);
    $parent_results->fetch();
    $parent_results->close();
  }
}
if (isset($_GET['module']) and $_GET['module'] != '') {
  $module = $_GET['module'];
  
  $module_data = $mysqli->prepare("SELECT fullname, checklist, selfenroll FROM modules WHERE moduleid=?");
  $module_data->bind_param('s', $module);
  $module_data->execute();
  $module_data->store_result();
  $module_data->bind_result($module_fullname, $checklist, $selfenrol);
  $module_data->fetch();
  $module_count = $module_data->num_rows();
  $module_data->close();
  
  if ($module_count == 0) {
    display_error($string['modulenotfound'], sprintf($string['modulenotfoundmsg'], $module), false, true);
  }
} else {
  $module = '';
}


if (isset($_POST['submit']) and $_POST['submit'] == 'Create') {
  $folder_results = $mysqli->prepare("SELECT name FROM folders WHERE id=? LIMIT 1");
  $folder_results->bind_param('i', $folder);
  $folder_results->execute();
  $folder_results->bind_result($folder_parent);
  $folder_results->fetch();
  $folder_results->close();

  $new_folder_name = $folder_parent . ';' . $_POST['folder_name'];
  $duplicate_name = 0;
  
  $folder_details = $mysqli->prepare("SELECT name FROM folders WHERE ownerID=? AND name=?");
  $folder_details->bind_param('is', $userID, $new_folder_name);
  $folder_details->execute();
  $folder_details->store_result();
  $folder_details->bind_result($name);
  if ($folder_details->num_rows() > 0) {
    $duplicate_name = 1;
  }
  $folder_details->close();
  
  if ($duplicate_name == 0) {
    if ($folder_query = $mysqli->prepare("INSERT INTO folders VALUES (NULL, ?, ?, ?, NOW(), 'yellow', NULL)")) {
      $folder_query->bind_param('iss', $userID, $new_folder_name, $_GET['newteam']);
      $folder_query->execute();
      $folder_query->close();
    } else {
      display_error("New Folder Error",$mysqli->error);
    }
  }
}

if ($folder != '') {
  $folders_array = explode(';',$folder_name);
  $parts = count($folders_array) - 1;
  $selfenrol = 0;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html onclick="hideMenus()">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title>Rogō<?php echo ' ' . $cfg_install_type; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style>
<?php
if (isset($state['showretired']) and $state['showretired'] == 'true') {
  echo ".retired {display:block}\n";
} else {
  echo ".retired {display:none}\n";
}
?>
</style>
<?php echo $cfg_js_root ?>
<script type="text/javascript" src="../js/sidebar.js"></script>
<script type="text/javascript" src="../js/staff_help.js"></script>
<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="../js/state.js"></script>
<script language="JavaScript">
  function addQuestion(qType) {
    top.location.href='../question/edit/?type=' + qType + 'folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>&module=<?php if (isset($_GET['module'])) echo $_GET['module']; ?>';
  }

  function deleteFolder() {
    notice=window.open("../delete/check_delete_folder.php?folderID=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

  function folderProperties() {
    notice=window.open("properties.php?folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>","properties","width=600,height=500,left="+(screen.width/2-300)+",top="+(screen.height/2-250)+",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }

  function newPaper(paperID) {
    notice = window.open("../paper/new_paper1.php?module=<?php if (isset($_GET['module'])) echo $_GET['module']; ?>&folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>","properties","width=700,height=500,left="+(screen.width/2-325)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }
  
  function addTeamMember() {
    notice = window.open("edit_team_popup.php?teamID=<?php if (isset($_GET['module'])) echo $_GET['module']; ?>&calling=paper_list&folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>","properties","width=450,height="+(screen.height-200)+",left="+(screen.width/2-325)+",top=10,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }
  
  function refreshPage() {
    $('.retired').toggle();
  }
</script>
</head>

<body onclick="hideMenus()">
<?php
  require '../include/folder_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<form name="myform" action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']; ?>" method="post">
<table class="header">
<?php
echo '<tr><th>';
if (isset($parent_id)) {
  echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="details.php?folder=' . $parent_id . '">' . $parent_name . '</a></div>';
} else {
  echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a></div>';
}
echo "</th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(1); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></th></tr>\n";

echo '<tr><th><div style="margin-left:10px; font-size:200%; font-weight:bold">';
if ($folder != '') {
  echo $folders_array[$parts];
} elseif ($_GET['module'] != '') {
  echo $_GET['module'] . ': <span style="font-weight:normal">' . $module_fullname . '</span>';
}
echo '</div></th>';
echo "<th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><input class=\"chk\" type=\"checkbox\" name=\"showretired\" id=\"showretired\" value=\"on\" onclick=\"refreshPage();\"";
if (isset($state['showretired']) and $state['showretired'] == 'true') echo ' checked';
echo " /> " . $string['showretired'] . "</th></tr>\n";

echo "<tr><th colspan=\"2\" class=\"bevel\"></th></tr>\n</table>\n<br />\n";

$display_papers = true;
// Get members of current folder.
if (isset($_GET['module']) and $_GET['module'] != '') {

  $member_details = $mysqli->prepare("SELECT DISTINCT surname, initials, title, users.id FROM (teams, users) WHERE teams.memberID=users.id AND name=? ORDER BY surname, initials");
  $member_details->bind_param('s', $module);
  $member_details->execute();
  $member_details->store_result();
  $member_details->bind_result($surname, $initials, $title, $tmp_userID);

  $tmp_html = '';
  if ($member_details->num_rows > 0) $tmp_html = '<ul type="square" style="line-height:155%; font-size:90%; color:#8492A6; margin-top:4px; margin-bottom:4px; margin-left:20px; padding-left:0px">';
  while ($member_details->fetch()) {
    if (strpos($userroles,'Admin') !== false) {
      $tmp_html .= "<li><a style=\"color:#254280\" href=\"../users/details.php?userID=$tmp_userID&module=" . $_GET['module'] . "\" target=\"_top\">$surname, $initials. " . str_replace('Professor','Prof',$title) . "</a></li>\n";
    } else {
      $tmp_html .= "<li><span style=\"color:#254280\">$surname, $initials. " . str_replace('Professor','Prof',$title) . "</span></li>\n";
    }
    if ($tmp_userID == $userID) $add_member = true;
  }
  if ($member_details->num_rows > 0) $tmp_html .= '</ul>';
  echo '<div style="box-shadow: 2px 2px 2px #C0C0C0; float:right; width:165px; margin-right:10px; border:1px solid #8492A6; background-color:#FCFCFC">';
  if ($add_member == true or strpos($userroles,'Admin') !== false) {
    echo '<div style="float:left; width:95%; padding:4px; background-color:#F1F5FB; border-bottom:1px solid #CFDBEB"><div style="float:left"><a href="" style="color:#254280" onclick="addTeamMember(); return false;" class="recent">' . $string['teammembers'] . '</a></div><div style="float:right"><a href="" onclick="addTeamMember(); return false;">' . $string['edit'] . '</a></div></div>';
  } else {
    echo '<div style="padding:4px; background-color:#F1F5FB; border-bottom:1px solid #CFDBEB">' . $string['teammembers'] . '</div>';
  }
  echo "<br clear=\"all\" />$tmp_html</div>\n";
  $member_details->close();
}

// Is it a self-enrol module.
if ($selfenrol == 1) {
  $selfenrol_url = $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . '/self_enrol.php?moduleid=' . $_GET['module'];
  echo "<div style=\"padding-left:10px\"><img src=\"../artwork/module_icon_16.png\" width=\"16\" height=\"16\" alt=\"modules\" /> <span style=\"color:#C00000\">Self-enrol URL:</span> <a href=\"$selfenrol_url\">$selfenrol_url</a></div>\n<br />";
}

// Get any sub-folders first.
if ($folder != '') {
  $tmp_string = '';
  if (count($teams) > 0) {
    $tmp_string = " OR team_name IN ('" . implode("','",$teams) . "')";
  }

  $tmp_folder_name = $folder_name . ';%';
  $folder_details = $mysqli->prepare("SELECT id, name, team_name, color FROM folders WHERE (ownerID=? $tmp_string) AND name LIKE ? AND deleted IS NULL ORDER BY name, id");
  $folder_details->bind_param('is', $userID, $tmp_folder_name);
  $folder_details->execute();
  $folder_details->bind_result($id, $name, $team_name, $color);
  while ($folder_details->fetch()) {
    $display_name = str_replace("$folder_name;","",$name);
    if (substr_count($display_name,';') == 0) {
      if ($team_name == '') {
        echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"details.php?folder=$id\"><img src=\"../artwork/" . $color . "_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" /></a>&nbsp;</td><td><a href=\"details.php?folder=$id\" class=\"blacklink\">$display_name</a></td></tr></table></div>\n";
      } else {
        echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"details.php?folder=$id\"><img src=\"../artwork/shared_" . $color . "_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" /></a>&nbsp;</td><td><a href=\"details.php?folder=$id\" class=\"blacklink\">$display_name</a></td></tr></table></div>\n";
      }
    }
  }
  $folder_details->close();
}

// New folder.
if (isset($_GET['newfolder']) AND $_GET['newfolder'] == 'y' AND !isset($_POST['submit'])) {
  echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><img src=\"../artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" /></td><td><input type=\"text\" size=\"30\" name=\"folder_name\" value=\"" . $string['newfolder'] . "\" onkeypress=\"if (event.keyCode == 38 || event.keyCode == 59 || event.keyCode == 63 || event.keyCode == 64 || event.keyCode == 94 || event.keyCode == 126) illegalChar(event.keyCode);\" /><input type=\"hidden\" name=\"newteam\" value=\"" . $_GET['newteam'] . "\" /><input type=\"submit\" name=\"submit\" value=\"" . $string['create'] . "\" /></td></tr></table></div>\n";
}

// Get current owner papers.
if ($folder != '') {
  $query_string = "SELECT DISTINCT paper_ownerID, property_id, paper_type, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'$cfg_long_date_time') AS display_start_date, DATE_FORMAT(end_date,'$cfg_long_date_time') AS display_end_date, exam_duration, title, initials, surname, retired, moduleID FROM (properties, users) LEFT JOIN papers ON properties.property_id=papers.paper WHERE properties.paper_ownerID=users.id AND folder=\"$folder\" AND deleted IS NULL GROUP BY paper_title ORDER BY paper_type, paper_title";
} elseif ($_GET['module'] != '') {
  $paper_types = array();
  
  $results = $mysqli->prepare("SELECT DISTINCT paper_type, COUNT(paper_type) AS no_papers FROM properties WHERE (moduleID = '" . $_GET['module'] . "' OR moduleID LIKE '%," . $_GET['module'] . ",%' OR moduleID LIKE '" . $_GET['module'] . ",%' OR moduleID LIKE '%," . $_GET['module'] . "') AND deleted IS NULL GROUP BY paper_type");
  $results->execute();
  $results->bind_result($paper_type, $no_papers);
  while ($results->fetch()) {
    $paper_types[$paper_type] = $no_papers;
  }
  $results->close();
  
  $query_string = "SELECT DISTINCT paper_ownerID, property_id, paper_type, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'$cfg_long_date_time') AS display_start_date, DATE_FORMAT(end_date,'$cfg_long_date_time') AS display_end_date, exam_duration, title, initials, surname, retired, moduleID FROM (properties, users) LEFT JOIN papers ON properties.property_id=papers.paper WHERE properties.paper_ownerID=users.id AND (moduleID = '" . $_GET['module'] . "' OR moduleID LIKE '%," . $_GET['module'] . ",%' OR moduleID LIKE '" . $_GET['module'] . ",%' OR moduleID LIKE '%," . $_GET['module'] . "') AND deleted IS NULL GROUP BY paper_title ORDER BY paper_type, paper_title";
}

$results = $mysqli->prepare($query_string);
$results->execute();
$results->bind_result($paper_ownerID, $property_id, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $moduleID);
$results->store_result();

$old_p_type = '';
$sent_clear_all = false;
if ($display_papers) {
  if ($results->num_rows > 0) {
    while ($results->fetch()) {
      if ($old_p_type != $paper_type and (isset($_GET['module']) AND $_GET['module'] != '') ) {
        if ($sent_clear_all == true) {
          echo "<br clear=\"all\" />";
        }
        $sent_clear_all = true;
        
        echo "<table border=\"0\" style=\"margin-left:10px; padding-right:2px; padding-bottom:5px; color:#1E3287\"><tr><td><nobr>" . $string[strtolower($types_array[$paper_type])] . " (" . $paper_types[$paper_type] . ")";
        if ($paper_type == 2) {
          echo "&nbsp;&nbsp;&nbsp;<span style=\"font-weight:normal\"><a href=\"../admin/calendar.php?module=" . $_GET['module'] . "#" . date("n") . "\"><img src=\"../artwork/shortcut_calendar_icon.png\" width=\"16\" height=\"14\" alt=\"Calendar\" border=\"0\" /></a>&nbsp;<a href=\"../admin/calendar.php?module=" . $_GET['module'] . "#" . date("n") . "\">" . $string['calendar'] . "</a></span>\n";
        }
        echo "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
        echo "<br />\n";
      }
      displayPaperIcon($paper_ownerID, $property_id, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $moduleID);
      $old_p_type = $paper_type;
      $file_no++;
    }
    $results->close();
  }
}
$mysqli->close();
?>
</form>

</div>

</body>
</html>