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
* Displays a list of papers.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/sidebar_menu.inc';

$folder_name = '';
$folder_type = '';
$file_no = 0;
$add_member = false;
if (isset($_GET['module'])) {
  $module = $_GET['module'];
}

function displayIcon($paper_type,$title,$initials,$surname,$shared,$locked) {
  switch ($paper_type) {
    case 0:
      $html = "<img src=\"../artwork/formative" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"Type: Formative Self-Assessment&#013;Author: $title $initials $surname\" border=\"0\" />";
      break;
    case 1:
      $html = "<img src=\"../artwork/progress" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"Type: Progress Test&#013;Author: $title $initials $surname\" border=\"0\" />";
      break;
    case 2:
      $html = "<img src=\"../artwork/summative" . $shared . $locked . ".png\" width=\"48\" height=\"48\" alt=\"Type: Summative Exam&#013;Author: $title $initials $surname\" border=\"0\" />";
      break;
    case 3:
      $html = "<img src=\"../artwork/survey" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"Type: Survey&#013;Author: $title $initials $surname\" border=\"0\" />";
      break;
    case 4:
      $html = "<img src=\"../artwork/osce" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"Type: OSCE Station&#013;Author: $title $initials $surname\" border=\"0\" />";
      break;
    case 5:
      $html = "<img src=\"../artwork/offline" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"Type: Offline Paper&#013;Author: $title $initials $surname\" border=\"0\" />";
      break;
  }
  return $html;
}

function displayPaperIcon($row) {
  global $userroles,$type,$folder,$module,$mysqli,$userID,$teams;
  echo '<div class="file">';
  echo '<table cellpadding="0" cellspacing="0" border="0"><tr><td style="width:60px" align="center">';
  $icon_type = $row['paper_type'];
  if (date("YmdHis", time()) >= $row['start_date']) {
    $locked = '_locked';
  } else {
    $locked = '';
  }
  if ($row['paper_ownerID'] == $userID or strpos($userroles,'Admin') !== false or strpos($userroles,'SysAdmin') !== false or ($icon_type != '2' and $icon_type != '4')) {
    echo "<a class=\"blacklink\" href=\"../paper/details.php?paperID=" . $row['property_id'] . "&folder=$folder&module=$module\">" . displayIcon($icon_type,$row['title'],$row['initials'],$row['surname'],'',$locked) . "</a></td>\n";
    echo "</td><td><a href=\"../paper/details.php?paperID=" . $row['property_id'] . "&folder=$folder&module=$module\" class=\"blacklink\">" . $row['paper_title'] . '</a><br />';
  } else {
    $access = false;
    $paper_modules = explode(',',$row['moduleID']);
    foreach ($paper_modules as $individual_module) {
      if (in_array($individual_module,$teams)) $access = true;
    }
  
    if ($access == true) {
      echo "<a class=\"blacklink\" href=\"../paper/details.php?paperID=" . $row['property_id'] . "&folder=$folder&module=$module\">" . displayIcon(2,$row['title'],$row['initials'],$row['surname'],'',$locked) . "</a></td>\n";
      echo "</td><td><a href=\"../paper/details.php?paperID=" . $row['property_id'] . "&folder=$folder&module=$module\" class=\"blacklink\">" . $row['paper_title'] . '</a><br />';
    } else {
      if ($icon_type == '2') {
        echo "<img src=\"../artwork/noentry_question_icon_48.png\" width=\"48\" height=\"48\" alt=\"Type: Summative Exam (Restricted Access)&#013;Author: " . $row['title'] . ' ' . $row['initials'] . ' ' . $row['surname'] . "\" border=\"0\" /></td>\n";
      } else {
        echo "<img src=\"../artwork/noentry_osce.png\" width=\"48\" height=\"48\" alt=\"Type: OSCE Station (Restricted Access)&#013;Author: " . $row['title'] . ' ' . $row['initials'] . ' ' . $row['surname'] . "\" border=\"0\" /></td>\n";
      }
      echo '</td><td>' . $row['paper_title'] . '<br />';
    }
  }
  echo '  <span style="color:#808080">';
  if ($row['screens'] == NULL) {
    echo '0 Screens, ';
  } elseif ($row['screens'] == 1) {
    echo $row['screens'] . ' Screen';
  } else {
    echo $row['screens'] . ' Screens';
  }
  if ($row['moduleID'] == '') {
    echo ', <span style="color:red">No modules set</span>';
  } else {
    if (isset($_GET['module']) AND $_GET['module'] == '') echo ', ' . str_replace(',',', ',$row['moduleID']);
  }
  echo '<br />';
  echo '  ' . $row['display_start_date'];
  if ($icon_type == 2) {
    if ($row['exam_duration'] != '') echo ', ' . $row['exam_duration'] . 'mins';
  } else {
    echo ' to ' . $row['display_end_date'];
  }
  echo "</td></tr></table></div>\n";
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

    if (isset($folder_teams) AND $folder_teams != '' and $module == '') $module = $folder_teams;

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

  if (isset($_POST['submit']) AND $_POST['submit'] == 'Create') {
    $folder_results = $mysqli->query("SELECT name FROM folders WHERE id=$folder LIMIT 1");
    $folder_row = $folder_results->fetch_assoc();
    $folder_parent = $folder_row['name'];
    $new_folder_name = $folder_parent . ';' . $_POST['folder_name'];
    $duplicate_name = 0;
    $folder_details = $mysqli->query("SELECT name FROM folders WHERE ownerID=$userID");
    while ($folder_row = $folder_details->fetch_assoc()) {
      if ($folder_row['name'] == $new_folder_name) $duplicate_name = 1;
    }
    $folder_details->close();
    $folder_results->close();

    if ($duplicate_name == 0) {
      if ($folder_query = $mysqli->prepare("INSERT INTO folders VALUES (NULL,$userID, ?,?,NOW(),'yellow',NULL)")) {
        $folder_query->bind_param('ss', $new_folder_name, $_GET['newteam']);
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
  } elseif ($module != '') {
    $module_data = $mysqli->prepare("SELECT fullname, checklist FROM modules WHERE moduleid=?");
    $module_data->bind_param('s', $module);
    $module_data->execute();
    $module_data->store_result();
    $module_data->bind_result($module_fullname, $checklist);
    $module_data->fetch();
    $module_data->close();
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html onclick="hideMenus()">
<head>
<title>TouchStone<?php echo " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />

<script src="../javascript/sidebar.js" type="text/javascript"></script>
<script src="../javascript/staff_help.js" type="text/javascript"></script>
<script language="JavaScript">
  function addQuestion(qType) {
    top.location.href='../question/add/' + qType + '.php?folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>&module=<?php if (isset($_GET['module'])) echo $_GET['module']; ?>';
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
    notice = window.open("../paper/new_paper1.php?module=<?php if (isset($_GET['module'])) echo $_GET['module']; ?>&folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>","properties","width=700,height=500,left="+(screen.width/2-325)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }
  
  function addTeamMember() {
    notice = window.open("edit_team_popup.php?teamID=<?php if (isset($_GET['module'])) echo $_GET['module']; ?>&calling=paper_list&folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>","properties","width=450,height="+(screen.height-200)+",left="+(screen.width/2-325)+",top=10,scrollbars=no,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }
</script>
</head>

<body onclick="hideMenus()">
<?php
  include '../include/folder_options.inc';
?>

<div id="content" class="content" style="font-size:80%">
<form name="myform" action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']; ?>" method="post">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<?php
echo '<tr><td style="background-color:#F1F5FB">';
if (isset($parent_id)) {
  echo '<div class="breadcrumb"><a href="../index.php">Home</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="details.php?folder=' . $parent_id . '">' . $parent_name . '</a></div>';
} else {
  echo '<div class="breadcrumb"><a href="../index.php">Home</a></div>';
}
echo '<div style="margin-left:10px; font-size:200%; font-weight:bold">';

if ($folder != '') {
  echo $folders_array[$parts];
} elseif ($_GET['module'] != '') {
  echo $_GET['module'] . ': <span style="font-weight:normal">' . $module_fullname . '</span>';
}
echo "</div></td><td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(1); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n<tr><td colspan=\"2\" style=\"height:3px\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n</table>\n<br />\n";

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
  while ($row = $member_details->fetch()) {
    if (strpos($userroles,'Admin') !== false) {
      $tmp_html .= "<li><a style=\"color:#254280\" href=\"../users/details.php?userID=$tmp_userID&module=" . $_GET['module'] . "\" target=\"_top\">$surname, $initials. " . str_replace('Professor','Prof',$title) . "</a></li>\n";
    } else {
      $tmp_html .= "<li><span style=\"color:#254280\">$surname, $initials. " . str_replace('Professor','Prof',$title) . "</span></li>\n";
    }
    if ($tmp_userID == $userID) $add_member = true;
  }
  if ($member_details->num_rows > 0) $tmp_html .= '</ul>';
  echo '<div style="float:right; width:165px; margin-right:10px; border:1px solid #8492A6; background-color:#FCFCFC; filter:progid:DXImageTransform.Microsoft.Shadow(direction=120,color=gray,strength=2)">';
  if ($add_member == true or strpos($userroles,'Admin') !== false) {
    echo '<div style="padding:4px; background-color:#F1F5FB; border-bottom:1px solid #CFDBEB"><a href="" style="color:#254280" onclick="addTeamMember(); return false;" class="recent">Team Members</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="" onclick="addTeamMember(); return false;">Edit</a></div>';
  } else {
    echo '<div style="padding:4px; background-color:#F1F5FB; border-bottom:1px solid #CFDBEB">Team Members</div>';
  }
  echo "$tmp_html</div>\n";
  $member_details->close();
}

// Get any sub-folders first.
if ($folder != '') {
  if (count($teams) > 0) {
    $tmp_string = " OR team_name IN ('" . implode("','",$teams) . "')";
  }

  $folder_details = $mysqli->query("SELECT id, name, team_name, color FROM folders WHERE (ownerID=$userID $tmp_string) AND name LIKE \"" .  mysql_real_escape_string($folder_name) . ";%\" AND deleted IS NULL ORDER BY name, id");
  while ($row = $folder_details->fetch_assoc()) {
    $display_name = str_replace("$folder_name;","",$row['name']);
    if (substr_count($display_name,';') == 0) {
      if ($row['team_name'] == '') {
        echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"details.php?folder=" . $row['id'] . "\"><img src=\"../artwork/" . $row['color'] . "_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" /></a>&nbsp;</td><td><a href=\"details.php?folder=" . $row['id'] . "\" class=\"blacklink\">$display_name</a></td></tr></table></div>\n";
      } else {
        echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"details.php?folder=" . $row['id'] . "\"><img src=\"../artwork/shared_" . $row['color'] . "_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" /></a>&nbsp;</td><td><a href=\"details.php?folder=" . $row['id'] . "\" class=\"blacklink\">$display_name</a></td></tr></table></div>\n";
      }
    }
  }
  $folder_details->close();
}

// New folder.
if (isset($_GET['newfolder']) AND $_GET['newfolder'] == 'y' AND !isset($_POST['submit'])) {
  echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><img src=\"../artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" /></td><td><input type=\"text\" size=\"30\" name=\"folder_name\" value=\"New Folder\" onkeypress=\"if (event.keyCode == 38 || event.keyCode == 59 || event.keyCode == 63 || event.keyCode == 64 || event.keyCode == 94 || event.keyCode == 126) illegalChar(event.keyCode);\" /><input type=\"hidden\" name=\"newteam\" value=\"" . $_GET['newteam'] . "\" /><input type=\"submit\" name=\"submit\" value=\"Create\" /></td></tr></table></div>\n";
}

// Get current owner papers.
if ($folder != '') {
  $query_string = "SELECT DISTINCT property_id, title, initials, surname, moduleID, paper_ownerID, paper_type, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'%d/%m/%y %H:%i') AS display_start_date, DATE_FORMAT(end_date,'%d/%m/%y %H:%i') AS display_end_date, exam_duration, moduleID FROM (properties, users) LEFT JOIN papers ON properties.property_id=papers.paper WHERE properties.paper_ownerID=users.id AND folder=\"$folder\" AND deleted IS NULL GROUP BY paper_title ORDER BY paper_type, paper_title";
} elseif ($_GET['module'] != '') {
  $paper_types = array();
  $results = $mysqli->query("SELECT DISTINCT paper_type, COUNT(paper_type) AS no_papers FROM properties WHERE moduleID LIKE '%" . $_GET['module'] . "%' AND deleted IS NULL GROUP BY paper_type");
  while ($row = $results->fetch_assoc()) {
    $paper_types[$row['paper_type']] = $row['no_papers'];
  }
  $results->close();
  
  $query_string = "SELECT DISTINCT property_id, title, initials, surname, moduleID, paper_ownerID, paper_type, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'%d/%m/%y %H:%i') AS display_start_date, DATE_FORMAT(end_date,'%d/%m/%y %H:%i') AS display_end_date, exam_duration, moduleID FROM (properties, users) LEFT JOIN papers ON properties.property_id=papers.paper WHERE properties.paper_ownerID=users.id AND moduleID LIKE '%" . $_GET['module'] . "%' AND deleted IS NULL GROUP BY paper_title ORDER BY paper_type, paper_title";
}
$results = $mysqli->query($query_string);
$old_p_type = '';
$sent_clear_all = false;
if ($display_papers) {
  if ($results->num_rows > 0) {
    while ($row = $results->fetch_assoc()) {
      if ($old_p_type != $row['paper_type'] and (isset($_GET['module']) AND $_GET['module'] != '') ) {
        if ($sent_clear_all == true) {
          echo "<br clear=\"all\" />";
        }
        $sent_clear_all = true;
        echo "<table border=\"0\" style=\"margin-left:10px; padding-right:2px; padding-bottom:5px; color:#1E3287\"><tr><td><nobr>" . $types_array[$row['paper_type']] . " (" . $paper_types[$row['paper_type']] . ")";
        if ($row['paper_type'] == 2) {
          echo "&nbsp;&nbsp;&nbsp;<span style=\"font-weight:normal\"><a href=\"../admin/calendar.php?module=" . $_GET['module'] . "#" . date("n") . "\"><img src=\"../artwork/shortcut_calendar_icon.png\" width=\"16\" height=\"14\" alt=\"Calendar\" border=\"0\" /></a>&nbsp;<a href=\"../admin/calendar.php?module=" . $_GET['module'] . "#" . date("n") . "\">Calendar</a></span>\n";
        }
        echo "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
        echo "<br />\n";
      }
      displayPaperIcon($row);
      $old_p_type = $row['paper_type'];
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