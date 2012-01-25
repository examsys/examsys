<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Sidebar menu of the Rogō homepage
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/
?><div id="left-sidebar" class="sidebar">
<br />
<table id="general" cellpadding="4" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:90%">
<tr><td width="16"><a href="#" onclick="launchHelp(1); return false;"><img src="<?php echo $cfg_root_path ?>/artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td><td class="menuitem"><a href="#" onclick="launchHelp(1); return false;"><?php echo $string['helpsupport']; ?></a></td></tr>
<?php
  if (strpos($userroles,'SysAdmin') !== false) {
    echo '<tr><td width="16"><a href="' . $cfg_root_path . '/admin/index.php"><img src="' . $cfg_root_path . '/artwork/admin_icon_16.gif" width="16" height="16" alt="Admin" border="0" /></a></td><td class="menuitem"><a href="' . $cfg_root_path . '/admin/index.php">' . $string['admintools'] . '</a></td></tr>';
  } elseif (strpos($userroles,'Admin') !== false) {
    echo '<tr><td width="16"><a href="' . $cfg_root_path . '/admin/calendar.php?calyear=' . date("Y") . '#' . date("n") . '"><img src="' . $cfg_root_path . '/artwork/shortcut_calendar_icon.png" width="16" height="16" alt="Admin" border="0" /></a></td><td class="menuitem"><a href="' . $cfg_root_path . '/admin/calendar.php?calyear=' . date("Y") . '#' . date("n") . '">' . $string['calendar'] . '</a></td></tr>';
  }
?>
<tr><td width="16"><a href="<?php echo $cfg_root_path ?>/users/search.php"><img src="<?php echo $cfg_root_path ?>/artwork/small_user_icon.gif" width="16" height="16" alt="Users" border="0" /></a></td><td class="menuitem"><a href="<?php echo $cfg_root_path ?>/users/search.php"><?php echo $string['usermanagement']; ?></a></td></tr>
<tr><td width="16"><a href="<?php echo $cfg_root_path ?>/staff/index.php?newfolder=y"><img src="<?php echo $cfg_root_path ?>/artwork/folder_16.png" width="16" height="16" alt="New Folder" border="0" /></a></td><td class="menuitem"><a href="<?php echo $cfg_root_path ?>/staff/index.php?newfolder=y"><?php echo $string['makeafolder']; ?></a></td></tr>
<tr><td width="16"><a href="<?php echo $cfg_root_path ?>/folder/list_keywords.php"><img src="<?php echo $cfg_root_path ?>/artwork/key.png" width="16" height="16" alt="key" border=\"0\" /></a></td><td class="menuitem"><a href="<?php echo $cfg_root_path ?>/folder/list_keywords.php"><?php echo $string['mypersonalkeywords']; ?></a></td></tr>
</table>
<br />

<br />
<table id="papertasks" cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold"><?php echo $string['papertasks']; ?></td></tr>
<tr><td>
<?php
  if (isset($_COOKIE['caamainmenu'][4])) {
    echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" id=\"menu0\" style=\"font-size:90%; margin-left:6px; display:" . $_COOKIE['caamainmenu'][0] . "\">\n";
  } else {
    echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" id=\"menu0\" style=\"font-size:90%; margin-left:6px; display:block\">\n";
  }
  if (count($teams) > 0 or strpos($userroles,'Admin') !== false) {    // Allow SysAdmin and Admin users not on any team to still create papers.
    echo "<tr><td style=\"text-align:left\"><a href=\"#\" onclick=\"newPaper(); return false;\"><img style=\"text-align:center\" src=\"{$cfg_root_path}/artwork/new_paper_icon_16.gif\" width=\"16\" height=\"16\" alt=\"New Paper\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"#\" onclick=\"newPaper(); return false;\">" . $string['createnewpaper'] . "</a></td></tr>\n";
  } else {
    echo "<tr><td style=\"text-align:left; color:#808080\"><img style=\"text-align:center\" src=\"{$cfg_root_path}/artwork/new_paper_icon_16.gif\" width=\"16\" height=\"16\" alt=\"New Paper\" border=\"0\" /></td><td class=\"menuitem\" style=\"color:#808080\">" . $string['createnewpaper'] . "</td></tr>\n";
  }
?>
<tr><td width="16"><img src="<?php echo $cfg_root_path ?>/artwork/list_papers_icon_16.gif" width="16" height="16" alt="List Papers" border="0" /></td><td class="menuitem"><span id="listpapers"><a href="#" onclick="showMenu('popup0','papertasks','listpapers',myOptions0,myURLs0,event); return false;"><?php echo $string['listpapers']; ?></a></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>
</table>

</td></tr>
</table>
<br />

<br />
<table id="banktasks" cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold"><?php echo $string['questionbanktasks']; ?></td></tr>
<tr><td>
<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%; margin-left:6px">
<tr><td width="16"><img src="<?php echo $cfg_root_path ?>/artwork/question_list_icon.gif" width="16" height="16" alt="List Papers" border="0" /></td><td class="menuitem"><span id="type"><a href="#" onclick="showMenu('popup1','banktasks','type',myOptions1,myURLs1,event); return false;"><?php echo $string['questionsbytype']; ?></span></a><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>
<tr><td width="16"><img src="<?php echo $cfg_root_path ?>/artwork/question_list_icon.gif" width="16" height="16" alt="List Teams" border="0" /></td><td class="menuitem"><span id="team"><a href="#" onclick="showMenu('popup2','banktasks','team',myOptions2,myURLs2,event); return false;"><?php echo $string['questionsbyteam']; ?></a></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>
<tr><td width="16"><img src="<?php echo $cfg_root_path ?>/artwork/question_list_icon.gif" width="16" height="16" alt="List Keywords" border="0" /></td><td class="menuitem"><span id="keyword"><a href="#" onclick="showMenu('popup3','banktasks','keyword',myOptions3,myURLs3,event); return false;"><?php echo $string['questionsbykeyword']; ?></a></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>
<tr><td width="16"><img src="<?php echo $cfg_root_path ?>/artwork/xmag.png" width="16" height="16" alt="Search" border="0" /></td><td class="menuitem"><span id="search"><a href="#" onclick="showMenu('popup4','banktasks','search',myOptions4,myURLs4,event); return false;"><?php echo $string['search']; ?></a></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>
<tr><td width="16"><img src="<?php echo $cfg_root_path ?>/artwork/new_question_menu_icon.gif" width="16" height="16" alt="newquestion" border="0" /></td><td class="menuitem"><span id="newquestion"><a href="#" onclick="showMenu('popup5','banktasks','newquestion',myOptions5,myURLs5,event); return false;"><?php echo $string['createnewquestion']; ?></a></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>
</table>

</td></tr>
</table>

<div style="position:absolute; bottom:15px; text-align:center; width:90%; cursor:pointer; font-size:90%"><a style="color:blue" href="#" onclick="displayCredits(); return false;">Rogō <?php echo $ts_version; ?> - &copy; 2012</a></div>
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
    $url = '/folder/details.php?folder=' . $folder_row['id'];
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
      $url = '/folder/details.php?module=' . $module_row['moduleid'];
      $modules_array[$title] = $url;
    }
    $module_details->close();
  }
  
  // Teams
  $teams_array = array();
  foreach($teams as $individual_team) {
    $teams_array[$individual_team] = $cfg_root_path . '/question/list.php?type=%&team=' . $individual_team;
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
    $keywords_array[htmlspecialchars($tmp_keyword)] = $cfg_root_path . '/question/list.php?type=%&keyword=' . $keywordID . ';' . urlencode($tmp_keyword);
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
    $keywords_array[htmlspecialchars($tmp_keyword)] = $cfg_root_path . '/question/list.php?type=%&keyword=' . $keywordID . ';' . urlencode($tmp_keyword);
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
  makeMenu(array($string['info']=>$cfg_root_path . '/question/list.php?type=info',$string['keyword_based']=>$cfg_root_path . '/question/list.php?type=keyword',$string['random']=>$cfg_root_path . '/question/list.php?type=random','-'=>'-',$string['calculation']=>$cfg_root_path . '/question/list.php?type=calculation',$string['dichotomous']=>$cfg_root_path . '/question/list.php?type=dichotomous',$string['extmatch']=>$cfg_root_path . '/question/list.php?type=extmatch',$string['blank']=>$cfg_root_path . '/question/list.php?type=blank',$string['flash']=>$cfg_root_path . '/question/list.php?type=flash',$string['hotspot']=>$cfg_root_path . '/question/list.php?type=hotspot',$string['labelling']=>$cfg_root_path . '/question/list.php?type=labelling',$string['likert']=>$cfg_root_path . '/question/list.php?type=likert',$string['matrix']=>$cfg_root_path . '/question/list.php?type=matrix',$string['mcq']=>$cfg_root_path . '/question/list.php?type=mcq',$string['mrq']=>$cfg_root_path . '/question/list.php?type=mrq',$string['rank']=>$cfg_root_path . '/question/list.php?type=rank',$string['sct']=>$cfg_root_path . '/question/list.php?type=sct',$string['textbox']=>$cfg_root_path . '/question/list.php?type=textbox'));
  makeMenu($teams_array);
  makeMenu($keywords_array);
  makeMenu(array($string['questions']=>$cfg_root_path . '/question/search.php',$string['papers']=>$cfg_root_path . '/paper/search.php',$string['people']=>$cfg_root_path . '/users/search.php'));
  makeMenu(array($string['info']=>"$cfg_root_path/question/edit/index.php?type=info",$string['keyword_based']=>"$cfg_root_path/question/edit/index.php?type=keyword_based",$string['random']=>"$cfg_root_path/question/edit/index.php?type=random","-"=>"-",$string['calculation']=>"$cfg_root_path/question/edit/index.php?type=calculation",$string['dichotomous']=>"$cfg_root_path/question/edit/index.php?type=dichotomous",$string['extmatch']=>"$cfg_root_path/question/edit/index.php?type=extmatch",$string['blank']=>"$cfg_root_path/question/edit/index.php?type=blank",$string['flash']=>"$cfg_root_path/question/edit/index.php?type=flash",$string['hotspot']=>"$cfg_root_path/question/edit/index.php?type=hotspot",$string['labelling']=>"$cfg_root_path/question/edit/index.php?type=labelling",$string['likert']=>"$cfg_root_path/question/edit/index.php?type=likert",$string['matrix']=>"$cfg_root_path/question/edit/index.php?type=matrix",$string['mcq']=>"$cfg_root_path/question/edit/index.php?type=mcq",$string['mrq']=>"$cfg_root_path/question/edit/index.php?type=mrq",$string['rank']=>"$cfg_root_path/question/edit/index.php?type=rank",$string['sct']=>"$cfg_root_path/question/edit/index.php?type=sct",$string['textbox']=>"$cfg_root_path/question/edit/index.php?type=textbox"));

  hideMenuScript($menuNo);
?>