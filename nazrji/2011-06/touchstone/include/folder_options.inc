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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

// Get folder information
$modules_array = array();
if (count($teams) > 0) {
$modules_array = array();
$team_sql = implode("','", $teams);
if ($team_sql != '') $team_sql = "'$team_sql'";
$module_details = $mysqli->query("SELECT DISTINCT moduleid, fullname, checklist FROM modules WHERE moduleid IN($team_sql) ORDER BY moduleID");
while ($module_row = $module_details->fetch_assoc()) {
  $title = $module_row['moduleid'] . ' - ' . str_replace("'","\'",$module_row['fullname']);
  $url = '/touchstone/folder/details.php?module=' . $module_row['moduleid'];
  $modules_array[$title] = $url;
}
$module_details->close();
}
  
?>
<div id="left-sidebar" class="sidebar">
<br />
<?php
  if (isset($_GET['folder'])) {
    $folder = $_GET['folder'];
  } else {
    $folder = '';
  }
  if (isset($_GET['module'])) {
    $module = $_GET['module'];
    
    $module_data = $mysqli->prepare("SELECT fullname, checklist, selfenroll FROM modules WHERE moduleid=?");
    $module_data->bind_param('s', $module);
    $module_data->execute();
    $module_data->store_result();
    $module_data->bind_result($module_fullname, $checklist, $selfenrol);
    $module_data->fetch();
    $module_data->close();
  } else {
    $module = '';
  }
  if ($folder != '') {       // Currently in a folder
?>
<table cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold">Folder Tasks</td></tr>
<tr><td style="margin:4px">
<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%; margin-left:7px">
<?php

  if ($folder_ownerID == $userID or (in_array($team_name,$teams)) == true or strpos($userroles,'SysAdmin') !== false) {
    echo '<tr><td width="16"><a href="#" onclick="folderProperties(); return false;"><img src="../artwork/properties_icon.gif" width="16" height="16" alt="Properties" border="0" /></a></td><td class="menuitem"><a href="#" onclick="folderProperties(); return false;">Folder Properties</a></td></tr>';
    echo '<tr><td width="16"><a href="/touchstone/folder/details.php?module=' . $module . '&folder=' . $folder . '&newfolder=y"><img src="../artwork/folder_16.png" width="16" height="16" alt="New Folder" border="0" /></a></td><td class="menuitem"><a href="/touchstone/folder/details.php?module=' . $module . '&folder=' . $_GET['folder'] . '&newfolder=y&newteam=' . $team_name . '">Make a new sub-folder</a></td></tr>';
    echo '<tr><td width="16"><a href="#" onclick="deleteFolder(); return false;"><img src="../artwork/red_cross.png" width="16" height="16" alt="Delete Folder" border="0" /></a></td><td class="menuitem"><a href="#" onclick="deleteFolder(); return false;">Delete this folder</a></td></tr>';
  } else {
    echo '<tr><td width="16"><img src="../artwork/properties_icon_grey.gif" width="16" height="16" alt="Properties" border="0" /></td><td class="greymenuitem">Folder Properties</td></tr>';
    echo '<tr><td width="16"><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="Delete Folder" border="0" /></td><td class="greymenuitem">Delete this folder</td></tr>';
  }
?>
</table>
</td></tr>
</table>

<br />
<br />

<?php
  } else {
  if ($module != '' and (strpos($checklist,'mapping') !== false or strpos($userroles,'SysAdmin') !== false)) {   // Curriculum mapping switched on for the module
?>
<table cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold">Module</td></tr>
<tr><td style="margin:4px">
<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%; margin-left:7px">
<?php
  if (strpos($userroles,'SysAdmin') !== false) {
    echo "<tr><td width=\"16\"><a href=\"../admin/edit_module.php?moduleid=$module\"><img src=\"../artwork/admin_icon_16.gif\" width=\"16\" height=\"16\" alt=\"cog\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"../admin/edit_module.php?moduleid=$module\">Edit Properties</a> (SysAdmin)</td></tr>";
  }
  if (strpos($checklist,'mapping') !== false and (in_array($module, $teams) == true or strpos($userroles,'SysAdmin') !== false)) {
    echo "<tr><td width=\"16\"><a href=\"../mapping/sessions_list.php?module=" . $module . "\"><img src=\"../artwork/module_icon_16.png\" width=\"16\" height=\"16\" alt=\"modules\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"../mapping/sessions_list.php?module=" . $_GET['module'] . "\">Manage Objectives</a></td></tr>\n";
  } else {
    echo "<tr><td width=\"16\"><img src=\"../artwork/module_icon_grey_16.png\" width=\"16\" height=\"16\" alt=\"modules\" border=\"0\" /></td><td class=\"menuitem\" style=\"color:#808080\">Manage Objectives</td></tr>\n";
  }
  echo "<tr><td width=\"16\"><a href=\"list_keywords.php?module=$module\"><img src=\"../artwork/key.png\" width=\"16\" height=\"16\" alt=\"key\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"list_keywords.php?module=$module\">Manage Keywords</a></td></tr>";
  echo '<tr><td><a href="../users/load_users_metadata.php?module=' . $_GET['module'] . '"><img src="../artwork/import_16.gif" width="16" height="16" alt="Import Metadata" border="0" /></a></td><td class="menuitem"><a href="../users/import_users_metadata.php?module=' . $_GET['module'] . '">Import Student metadata</a></td></tr>';
?>
</table>
</td></tr>
</table>

<br />
<br />

<?php
    }
  }
?>

<table id="papertasks" cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold">Paper Tasks</td></tr>
<tr><td style="margin:4px">
<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%; margin-left:7px">
<?php
  if (count($teams) > 0) {
    echo "<tr><td><a href=\"#\" onclick=\"newPaper(); return false;\"><img src=\"../artwork/new_paper_icon_16.gif\" width=\"16\" height=\"16\" alt=\"New Paper\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"#\" onclick=\"newPaper(); return false;\">Create new Paper</a></td></tr>\n";
  } else {
    echo "<tr><td style=\"color:#808080\"><img src=\"../artwork/new_paper_icon_16.gif\" width=\"16\" height=\"16\" alt=\"New Paper\" border=\"0\" /></td><td class=\"menuitem\">Create new Paper</td></tr>\n";
  }

?>
<tr><td width="16"><img src="../artwork/list_papers_icon_16.gif" width="16" height="16" alt="List Papers" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup0','papertasks','listpapers',myOptions0,myURLs0,event); return false;"><span id="listpapers">List Papers</span><img src="../artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>

</table>
</td></tr>
</table>

<br />
<br />

<table id="banktasks" cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold">Question Bank</td></tr>
<tr><td style="margin:4px">
<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%; margin-left:7px">
<tr><td width="16"><img src="../artwork/question_list_icon.gif" width="16" height="16" alt="List Papers" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup1','banktasks','type',myOptions1,myURLs1,event); return false;"><span id="type">Questions by Type</span><img src="../artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="../artwork/question_list_icon.gif" width="16" height="16" alt="List Teams" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup2','banktasks','team',myOptions2,myURLs2,event); return false;"><span id="team">Questions by Team</span><img src="../artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="../artwork/question_list_icon.gif" width="16" height="16" alt="List Keywords" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup3','banktasks','keyword',myOptions3,myURLs3,event); return false;"><span id="keyword">Questions by Keyword</span><img src="../artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="../artwork/xmag.png" width="16" height="16" alt="Search" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup4','banktasks','search',myOptions4,myURLs4,event); return false;"><span id="search">Search</span><img src="../artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="../artwork/new_question_menu_icon.gif" width="16" height="16" alt="newquestion" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup5','banktasks','newquestion',myOptions5,myURLs5,event); return false;"><span id="newquestion">Create new Question</span><img src="../artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
</table>

</td></tr>
</table>

<?php
if ($folder != '') {
?>
<br />
<br />
<?php
}
?>

</div>
<?php
  // My Folders.
  $team_string = '';
  if (count($teams) > 0) {
    $team_string = " OR team_name IN ('" . implode("','",$teams) . "')";
  }
  $my_folders_array = array();
  $folder_details = $mysqli->query("SELECT id, name FROM folders WHERE (ownerID=$userID $team_string) AND deleted IS NULL ORDER BY name");
  while ($folder_row = $folder_details->fetch_assoc()) {
    $path_parts = substr_count($folder_row['name'],';');
    $folder_array = explode(';',$folder_row['name']);
    $display_name = str_repeat('&nbsp;',$path_parts * 4) . $folder_array[$path_parts];
    $display_name = str_replace("'","\'",$display_name);
    $url = '/touchstone/folder/details.php?folder=' . $folder_row['id'];
    $my_folders_array[$display_name] = $url;
  }
  $folder_details->close();

  // Teams
  $teams_array = array();
  foreach($teams as $individual_team) {
    $teams_array[$individual_team] = '../question/list.php?type=%&team=' . $individual_team;
  }

  // Keywords
  $keywords_array = array();
  $keyword_no = 0;
  $old_moduleID = '';
  $stmt = $mysqli->prepare("SELECT moduleid, keyword, keywords_user.id FROM keywords_user, modules WHERE keywords_user.userID=modules.id AND moduleid IN ('" . implode("','",$teams) . "') ORDER BY moduleid, keyword");
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($moduleID, $tmp_keyword, $keywordID);
  while ($stmt->fetch()) {
    if ($old_moduleID != $moduleID) $keywords_array[$keyword_no] = "#$moduleID";
    $keywords_array[htmlspecialchars($tmp_keyword)] = '../question/list.php?type=%&keyword=' . $keywordID . ';' . urlencode($tmp_keyword);
    $old_moduleID = $moduleID;
    $keyword_no++;
  }
  if ($stmt->num_rows > 0) $keywords_array[$keyword_no] = '#Personal Keywords';
  $stmt->close();

  $result = $mysqli->prepare("SELECT DISTINCT id, keyword FROM keywords_user WHERE userID=? ORDER BY keyword");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->bind_result($keywordID, $tmp_keyword);
  while ($result->fetch()) {
    $keywords_array[htmlspecialchars($tmp_keyword)] = '../question/list.php?type=%&keyword=' . $keywordID . ';' . urlencode($tmp_keyword);
  }
  $result->close();
  
  if (count($my_folders_array) > 0) {
    $divider = array('-'=>'-');
    $full_modules_array = array();
    $full_modules_array += $my_folders_array;
    if (count($modules_array) > 0) {
      $full_modules_array += $divider + $modules_array;
    } else {
      $full_modules_array = $modules_array;
    }
  } else {
    $full_modules_array = $modules_array;
  }
  
  makeMenu($full_modules_array);
  makeMenu(array('Information Block'=>'/touchstone/question/list.php?type=info','Random Question Block'=>'/touchstone/question/list.php?type=random','-'=>'-','Calculation'=>'/touchstone/question/list.php?type=calculation','Dichotomous (Yes/No and True/False)'=>'/touchstone/question/list.php?type=dichotomous','Extended Matching'=>'/touchstone/question/list.php?type=extmatch','Fill-in-the-Blank'=>'/touchstone/question/list.php?type=blank','Flash Interface'=>'/touchstone/question/list.php?type=flash','Image Hotspot'=>'/touchstone/question/list.php?type=hotspot','Labelling'=>'/touchstone/question/list.php?type=labelling','Likert Scale'=>'/touchstone/question/list.php?type=likert','Matrix'=>'/touchstone/question/list.php?type=matrix','Multiple Choice (MCQ)'=>'/touchstone/question/list.php?type=mcq','Multiple Response (MRQ)'=>'/touchstone/question/list.php?type=mrq','Ranking'=>'/touchstone/question/list.php?type=rank','Script Concordance Test (SCT)'=>'/touchstone/question/list.php?type=sct','Text Box'=>'/touchstone/question/list.php?type=textbox','Time/Date'=>'/touchstone/question/list.php?type=timedate'));
  makeMenu($teams_array);
  makeMenu($keywords_array);
  makeMenu(array('Questions'=>'/touchstone/question/search.php','Papers'=>'/touchstone/paper/search.php','People'=>'/touchstone/users/search.php'));
  makeMenu(array('Information Block'=>'/touchstone/question/add/info.php','Random Question Block'=>'/touchstone/question/add/random.php','-'=>'-','Calculation'=>'/touchstone/question/add/calculation.php','Dichotomous (Yes/No and True/False)'=>'/touchstone/question/add_dichotomous.php','Extended Matching'=>'/touchstone/question/add/extmatch.php','Fill-in-the-Blank'=>'/touchstone/question/add/blank.php','Flash Interface'=>'/touchstone/question/add/flash.php','Image Hotspot'=>'/touchstone/question/add/hotspot1.php','Labelling'=>'/touchstone/question/add/labelling1.php','Likert Scale'=>'/touchstone/question/add/likert.php','Matrix'=>'/touchstone/question/add/matrix.php','Multiple Choice (MCQ)'=>'/touchstone/question/add/mcq.php','Multiple Response (MRQ)'=>'/touchstone/question/add/mrq.php','Ranking'=>'./add/add_rank.php','Script Concordance Test (SCT)'=>'/touchstone/question/add/sct.php','Text Box'=>'/touchstone/question/add/textbox.php','Time/Date'=>'/touchstone/question/add/timedate.php'));

  hideMenuScript($menuNo);
?>