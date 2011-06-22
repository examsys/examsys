<?php
/// This file is part of TouchStone
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
* Sidebar menu of the TouchStone homepage
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/
?>
<div id="left-sidebar" class="sidebar">
<br />
<table id="general" cellpadding="4" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:90%">
<tr><td width="16"><a href="#" onclick="launchHelp(1); return false;"><img src="/touchstone/artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td><td class="menuitem"><a href="#" onclick="launchHelp(1); return false;">Help and Support</a></td></tr>
<?php
  if (strpos($userroles,'SysAdmin') !== false) {
    echo '<tr><td width="16"><a href="/touchstone/admin/index.php"><img src="/touchstone/artwork/admin_icon_16.gif" width="16" height="16" alt="Admin" border="0" /></a></td><td class="menuitem"><a href="/touchstone/admin/index.php">Administrative Tools</a></td></tr>';
  } elseif (strpos($userroles,'Admin') !== false) {
    echo '<tr><td width="16"><a href="/touchstone/admin/calendar.php?calyear=' . date("Y") . '#' . date("n") . '"><img src="/touchstone/artwork/shortcut_calendar_icon.png" width="16" height="16" alt="Admin" border="0" /></a></td><td class="menuitem"><a href="/touchstone/admin/calendar.php?calyear=' . date("Y") . '#' . date("n") . '">Calendar</a></td></tr>';
  }
?>
<tr><td width="16"><a href="/touchstone/users/search.php"><img src="/touchstone/artwork/small_user_icon.gif" width="16" height="16" alt="Users" border="0" /></a></td><td class="menuitem"><a href="/touchstone/users/search.php">User Management</a></td></tr>
<tr><td width="16"><a href="/touchstone/index.php?newfolder=y"><img src="/touchstone/artwork/folder_16.png" width="16" height="16" alt="New Folder" border="0" /></a></td><td class="menuitem"><a href="/touchstone/index.php?newfolder=y">Make a new folder</a></td></tr>
<tr><td width="16"><a href="/touchstone/folder/list_keywords.php?module="><img src="/touchstone/artwork/key.png" width="16" height="16" alt="key" border=\"0\" /></a></td><td class="menuitem"><a href="/touchstone/folder/list_keywords.php?module=">My Personal Keywords</a></td></tr>
</table>
<br />

<br />
<table id="papertasks" cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold">Paper Tasks</td></tr>
<tr><td>
<?php
  if (isset($_COOKIE['caamainmenu'][4])) {
    echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" id=\"menu0\" style=\"font-size:90%; margin-left:6px; display:" . $_COOKIE['caamainmenu'][0] . "\">\n";
  } else {
    echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" id=\"menu0\" style=\"font-size:90%; margin-left:6px; display:block\">\n";
  }
  if (count($teams) > 0 or strpos($userroles,'Admin') !== false) {    // Allow SysAdmin and Admin users not on any team to still create papers.
    echo "<tr><td style=\"text-align:left\"><a href=\"#\" onclick=\"newPaper(); return false;\"><img style=\"text-align:center\" src=\"/touchstone/artwork/new_paper_icon_16.gif\" width=\"16\" height=\"16\" alt=\"New Paper\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"#\" onclick=\"newPaper(); return false;\">Create new Paper</a></td></tr>\n";
  } else {
    echo "<tr><td style=\"text-align:left; color:#808080\"><img style=\"text-align:center\" src=\"/touchstone/artwork/new_paper_icon_16.gif\" width=\"16\" height=\"16\" alt=\"New Paper\" border=\"0\" /></td><td class=\"menuitem\" style=\"color:#808080\">Create new Paper</td></tr>\n";
  }
?>
<tr><td width="16"><img src="/touchstone/artwork/list_papers_icon_16.gif" width="16" height="16" alt="List Papers" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup0','papertasks','listpapers',myOptions0,myURLs0,event); return false;"><span id="listpapers">List Papers</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
</table>

</td></tr>
</table>
<br />

<br />
<table id="banktasks" cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold">Question Bank Tasks</td></tr>
<tr><td>
<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%; margin-left:6px">
<tr><td width="16"><img src="/touchstone/artwork/question_list_icon.gif" width="16" height="16" alt="List Papers" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup1','banktasks','type',myOptions1,myURLs1,event); return false;"><span id="type">Questions by Type</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="/touchstone/artwork/question_list_icon.gif" width="16" height="16" alt="List Teams" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup2','banktasks','team',myOptions2,myURLs2,event); return false;"><span id="team">Questions by Team</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="/touchstone/artwork/question_list_icon.gif" width="16" height="16" alt="List Keywords" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup3','banktasks','keyword',myOptions3,myURLs3,event); return false;"><span id="keyword">Questions by Keyword</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="/touchstone/artwork/xmag.png" width="16" height="16" alt="Search" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup4','banktasks','search',myOptions4,myURLs4,event); return false;"><span id="search">Search</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="/touchstone/artwork/new_question_menu_icon.gif" width="16" height="16" alt="newquestion" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup5','banktasks','newquestion',myOptions5,myURLs5,event); return false;"><span id="newquestion">Create new Question</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
</table>

</td></tr>
</table>

<div style="position:absolute; bottom:15px; text-align:center; width:90%; cursor:pointer; font-size:90%"><a style="color:blue" href="#" onclick="displayCredits(); return false;">TouchStone <?php echo $ts_version; ?> - &copy; 2011</a></div>
</div>

<?php
  // My Folders.
  $team_string = '';
  if (count($teams) > 0) {
    $team_string = " OR team_name IN('" . implode("','",$teams) . "')";
  }
  $my_folders_array = array();
  $folder_details = $mysqli->query("SELECT id, name FROM folders WHERE (ownerID=$userID $team_string) AND deleted IS NULL ORDER BY name");
  while ($folder_row = $folder_details->fetch_assoc()) {
    $path_parts = substr_count($folder_row['name'],';');
    $folder_array = explode(';',$folder_row['name']);
    $display_name = str_repeat('&nbsp;',$path_parts * 4) . $folder_array[$path_parts];
    $display_name = str_replace("'","\'",$display_name);
    $url = './folder/details.php?folder=' . $folder_row['id'];
    $my_folders_array[$display_name] = $url;
  }
  $folder_details->close();

  // Module folders.
  $modules_array = array();
  if (count($teams) > 0) {
    $team_sql = implode("','", $teams);
    if ($team_sql != '') $team_sql = "'$team_sql'";
    $module_details = $mysqli->query("SELECT DISTINCT moduleid, fullname FROM modules WHERE moduleid IN($team_sql) ORDER BY moduleID");
    while ($module_row = $module_details->fetch_assoc()) {
      $title = $module_row['moduleid'] . ' - ' . str_replace("'","\'",$module_row['fullname']);
      $url = '/touchstone/folder/details.php?module=' . $module_row['moduleid'];
      $modules_array[$title] = $url;
    }
    $module_details->close();
  }
  
  // Teams
  $teams_array = array();
  foreach($teams as $individual_team) {
    $teams_array[$individual_team] = './question/list.php?type=%&team=' . $individual_team;
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
    $keywords_array[htmlspecialchars($tmp_keyword)] = './question/list.php?type=%&keyword=' . $keywordID . ';' . urlencode($tmp_keyword);
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
    $keywords_array[htmlspecialchars($tmp_keyword)] = './question/list.php?type=%&keyword=' . $keywordID . ';' . urlencode($tmp_keyword);
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
  
  //if (count($full_modules_array) > 0) makeMenu($full_modules_array);
  makeMenu($full_modules_array);
  makeMenu(array('Information Block'=>'/touchstone/question/list.php?type=info','Keyword-based Question'=>'/touchstone/question/list.php?type=keyword','Random Question Block'=>'/touchstone/question/list.php?type=random','-'=>'-','Calculation'=>'./question/list.php?type=calculation','Dichotomous (Yes/No and True/False)'=>'/touchstone/question/list.php?type=dichotomous','Extended Matching'=>'/touchstone/question/list.php?type=extmatch','Fill-in-the-Blank'=>'/touchstone/question/list.php?type=blank','Flash Interface'=>'/touchstone/question/list.php?type=flash','Image Hotspot'=>'/touchstone/question/list.php?type=hotspot','Labelling'=>'/touchstone/question/list.php?type=labelling','Likert Scale'=>'/touchstone/question/list.php?type=likert','Matrix'=>'/touchstone/question/list.php?type=matrix','Multiple Choice (MCQ)'=>'/touchstone/question/list.php?type=mcq','Multiple Response (MRQ)'=>'/touchstone/question/list.php?type=mrq','Ranking'=>'/touchstone/question/list.php?type=rank','Script Concordance Test (SCT)'=>'/touchstone/question/list.php?type=sct','Text Box'=>'/touchstone/question/list.php?type=textbox','Time/Date'=>'/touchstone/question/list.php?type=timedate'));
  makeMenu($teams_array);
  makeMenu($keywords_array);
  makeMenu(array('Questions'=>'/touchstone/question/search.php','Papers'=>'/touchstone/paper/search.php','People'=>'/touchstone/users/search.php'));
  makeMenu(array('Information Block'=>'/touchstone/question/add/info.php','Keyword-based Question'=>'/touchstone/question/add/keyword_based.php','Random Question Block'=>'/touchstone/question/add/random.php','-'=>'-','Calculation'=>'/touchstone/question/add/calculation.php','Dichotomous (Yes/No and True/False)'=>'/touchstone/question/add/dichotomous.php','Extended Matching'=>'/touchstone/question/add/extmatch.php','Fill-in-the-Blank'=>'./question/add/blank.php','Flash Interface'=>'/touchstone/question/add/flash.php','Image Hotspot'=>'/touchstone/question/add/hotspot1.php','Labelling'=>'/touchstone/question/add/labelling1.php','Likert Scale'=>'/touchstone/question/add/likert.php','Matrix'=>'/touchstone/question/add/matrix.php','Multiple Choice (MCQ)'=>'/touchstone/question/add/mcq.php','Multiple Response (MRQ)'=>'/touchstone/question/add/mrq.php','Ranking'=>'./question/add/rank.php','Script Concordance Test (SCT)'=>'/touchstone/question/add/sct.php','Text Box'=>'./question/add/textbox.php','Time/Date'=>'/touchstone/question/add/timedate.php'));

  hideMenuScript($menuNo);
