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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

// Get folder information
$modules_array = array();
if (count($teams) > 0) {
  $modules_array = array();
  $team_sql = implode("','", $teams);
  if ($team_sql != '') $team_sql = "'$team_sql'";
  
  $result = $mysqli->prepare("SELECT DISTINCT moduleid, fullname, checklist FROM modules WHERE moduleid IN ($team_sql) ORDER BY moduleID");
  $result->execute();
  $result->bind_result($moduleid, $fullname, $checklist);
  while ($result->fetch()) {
    $title = $moduleid . ' - ' . str_replace("'", "\'", $fullname);
    $url = '/folder/details.php?module=' . $moduleid;
    $modules_array[$title] = $url;
  }
  $result->close();
}
?>
<div id="left-sidebar" class="sidebar">
<br />
<?php
  if ($folder != '') {       // Currently in a folder
?>
<table cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold"><?php echo $string['foldertasks']; ?></td></tr>
<tr><td style="margin:4px">
<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%; margin-left:7px">
<?php

  if ($folder_ownerID == $userID or (in_array($team_name,$teams)) == true or strpos($userroles,'SysAdmin') !== false) {
    echo '<tr><td width="16"><a href="#" onclick="folderProperties(); return false;"><img src="' . $cfg_root_path . '/artwork/properties_icon.gif" width="16" height="16" alt="Properties" border="0" /></a></td><td class="menuitem"><a href="#" onclick="folderProperties(); return false;">' . $string['folderproperties'] . '</a></td></tr>';
    echo '<tr><td width="16"><a href="' . $cfg_root_path . '/folder/details.php?module=' . $module . '&folder=' . $folder . '&newfolder=y"><img src="' . $cfg_root_path . '/artwork/folder_16.png" width="16" height="16" alt="New Folder" border="0" /></a></td><td class="menuitem"><a href="' . $cfg_root_path . '/folder/details.php?module=' . $module . '&folder=' . $_GET['folder'] . '&newfolder=y&newteam=' . $team_name . '">' . $string['makesubfolder'] . '</a></td></tr>';
    echo '<tr><td width="16"><a href="#" onclick="deleteFolder(); return false;"><img src="' . $cfg_root_path . '/artwork/red_cross.png" width="16" height="16" alt="Delete Folder" border="0" /></a></td><td class="menuitem"><a href="#" onclick="deleteFolder(); return false;">' . $string['deletefolder'] . '</a></td></tr>';
  } else {
    echo '<tr><td width="16"><img src="' . $cfg_root_path . '/artwork/properties_icon_grey.gif" width="16" height="16" alt="Properties" border="0" /></td><td class="greymenuitem">' . $string['folderproperties'] . '</td></tr>';
    echo '<tr><td width="16"><img src="' . $cfg_root_path . '/artwork/red_cross_grey.png" width="16" height="16" alt="Delete Folder" border="0" /></td><td class="greymenuitem">' . $string['deletefolder'] . '</td></tr>';
  }
?>
</table>
</td></tr>
</table>

<br />
<br />

<?php
  } else {
  
    if ($module != '') {   
    ?>
      <table cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
      <tr><td style="font-weight:bold"><?php echo $string['module']; ?></td></tr>
      <tr><td style="margin:4px">
      <table cellpadding="4" cellspacing="0" border="0" style="font-size:90%; margin-left:7px">
    <?php

    if (strpos($userroles,'SysAdmin') !== false) {
      echo "<tr><td width=\"16\"><a href=\"../admin/edit_module.php?moduleid=$module\"><img src=\"$cfg_root_path/artwork/admin_icon_16.gif\" width=\"16\" height=\"16\" alt=\"cog\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"../admin/edit_module.php?moduleid=$module\">" . $string['editpropertiessysadmin'] . "</a></td></tr>";
    }
    if (strpos($checklist,'mapping') !== false ) {     // Curriculum mapping switched on for the module
      if (in_array($module, $teams) == true or strpos($userroles,'SysAdmin') !== false or strpos($userroles,'Admin') !== false ) {
        echo "<tr><td width=\"16\"><a href=\"../mapping/sessions_list.php?module=" . $module . "\"><img src=\"$cfg_root_path/artwork/module_icon_16.png\" width=\"16\" height=\"16\" alt=\"modules\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"../mapping/sessions_list.php?module=" . $_GET['module'] . "\">" . $string['manageobjectives'] . "</a></td></tr>\n";
      } else {
        echo "<tr><td width=\"16\"><img src=\"$cfg_root_path/artwork/module_icon_grey_16.png\" width=\"16\" height=\"16\" alt=\"modules\" border=\"0\" /></td><td class=\"menuitem\" style=\"color:#808080\">" . $string['manageobjectives'] . "</td></tr>\n";
      }
    }
    echo "<tr><td width=\"16\"><a href=\"list_keywords.php?module=$module\"><img src=\"$cfg_root_path/artwork/key.png\" width=\"16\" height=\"16\" alt=\"key\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"list_keywords.php?module=$module\">" . $string['managekeywords'] . "</a></td></tr>";
    echo '<tr><td><a href="../users/load_users_metadata.php?module=' . $_GET['module'] . '"><img src="' . $cfg_root_path . '/artwork/import_16.gif" width="16" height="16" alt="Import Metadata" border="0" /></a></td><td class="menuitem"><a href="../users/import_users_metadata.php?module=' . $_GET['module'] . '">' . $string['importstudentmetadata'] . '</a></td></tr>';
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
<tr><td style="font-weight:bold"><?php echo $string['papertasks']; ?></td></tr>
<tr><td style="margin:4px">
<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%; margin-left:7px">
<?php
  if (count($teams) > 0) {
    echo "<tr><td><a href=\"#\" onclick=\"newPaper(); return false;\"><img src=\"$cfg_root_path/artwork/new_paper_icon_16.gif\" width=\"16\" height=\"16\" alt=\"New Paper\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"#\" onclick=\"newPaper(); return false;\">" . $string['createnewpaper'] . "</a></td></tr>\n";
  } else {
    echo "<tr><td style=\"color:#808080\"><img src=\"$cfg_root_path/artwork/new_paper_icon_16.gif\" width=\"16\" height=\"16\" alt=\"New Paper\" border=\"0\" /></td><td class=\"menuitem\">" . $string['createnewpaper'] . "</td></tr>\n";
  }

?>
<tr><td width="16"><img src="<?php echo $cfg_root_path ?>/artwork/list_papers_icon_16.gif" width="16" height="16" alt="List Papers" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup0','papertasks','listpapers',myOptions0,myURLs0,event); return false;"><span id="listpapers"><?php echo $string['listpapers']; ?></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>

</table>
</td></tr>
</table>

<br />
<br />

<table id="banktasks" cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold"><?php echo $string['questionbank']; ?></td></tr>
<tr><td style="margin:4px">
<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%; margin-left:7px">
<tr><td width="16"><img src="<?php echo $cfg_root_path ?>/artwork/question_list_icon.gif" width="16" height="16" alt="List Papers" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup1','banktasks','type',myOptions1,myURLs1,event); return false;"><span id="type"><?php echo $string['questionsbytype']; ?></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="<?php echo $cfg_root_path ?>/artwork/question_list_icon.gif" width="16" height="16" alt="List Teams" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup2','banktasks','team',myOptions2,myURLs2,event); return false;"><span id="team"><?php echo $string['questionsbyteam']; ?></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="<?php echo $cfg_root_path ?>/artwork/question_list_icon.gif" width="16" height="16" alt="List Keywords" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup3','banktasks','keyword',myOptions3,myURLs3,event); return false;"><span id="keyword"><?php echo $string['questionsbykeyword']; ?></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="<?php echo $cfg_root_path ?>/artwork/xmag.png" width="16" height="16" alt="Search" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup4','banktasks','search',myOptions4,myURLs4,event); return false;"><span id="search"><?php echo $string['search']; ?></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="<?php echo $cfg_root_path ?>/artwork/new_question_menu_icon.gif" width="16" height="16" alt="newquestion" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup5','banktasks','newquestion',myOptions5,myURLs5,event); return false;"><span id="newquestion"><?php echo $string['createnewquestion']; ?></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
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
  $result = $mysqli->prepare("SELECT id, name FROM folders WHERE (ownerID=? $team_string) AND deleted IS NULL ORDER BY name");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->bind_result($folder_id, $folder_name);
  while ($result->fetch()) {
    $path_parts = substr_count($folder_name, ';');
    $folder_array = explode(';', $folder_name);
    $display_name = str_repeat('&nbsp;',$path_parts * 4) . $folder_array[$path_parts];
    $display_name = str_replace("'", "\'", $display_name);
    $url = '/folder/details.php?folder=' . $folder_id;
    $my_folders_array[$display_name] = $url;
  }
  $result->close();

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
  makeMenu(array($string['info']=>$cfg_root_path . '/question/list.php?type=info',$string['keyword_based']=>$cfg_root_path . '/question/list.php?type=keyword',$string['random']=>$cfg_root_path . '/question/list.php?type=random','-'=>'-',$string['calculation']=>$cfg_root_path . '/question/list.php?type=calculation',$string['dichotomous']=>$cfg_root_path . '/question/list.php?type=dichotomous',$string['extmatch']=>$cfg_root_path . '/question/list.php?type=extmatch',$string['blank']=>$cfg_root_path . '/question/list.php?type=blank',$string['flash']=>$cfg_root_path . '/question/list.php?type=flash',$string['hotspot']=>$cfg_root_path . '/question/list.php?type=hotspot',$string['labelling']=>$cfg_root_path . '/question/list.php?type=labelling',$string['likert']=>$cfg_root_path . '/question/list.php?type=likert',$string['matrix']=>$cfg_root_path . '/question/list.php?type=matrix',$string['mcq']=>$cfg_root_path . '/question/list.php?type=mcq',$string['mrq']=>$cfg_root_path . '/question/list.php?type=mrq',$string['rank']=>$cfg_root_path . '/question/list.php?type=rank',$string['sct']=>$cfg_root_path . '/question/list.php?type=sct',$string['textbox']=>$cfg_root_path . '/question/list.php?type=textbox', $string['true_false']=>$cfg_root_path . '/question/list.php?type=true_false'));
  makeMenu($teams_array);
  makeMenu($keywords_array);
  makeMenu(array($string['questions']=>$cfg_root_path . '/question/search.php',$string['papers']=>$cfg_root_path . '/paper/search.php',$string['people']=>$cfg_root_path . '/users/search.php'));
  makeMenu(array($string['info']=>"$cfg_root_path/question/edit/index.php?type=info",$string['keyword_based']=>"$cfg_root_path/question/edit/index.php?type=keyword_based",$string['random']=>"$cfg_root_path/question/edit/index.php?type=random","-"=>"-",$string['calculation']=>"$cfg_root_path/question/edit/index.php?type=calculation",$string['dichotomous']=>"$cfg_root_path/question/edit/index.php?type=dichotomous",$string['extmatch']=>"$cfg_root_path/question/edit/index.php?type=extmatch",$string['blank']=>"$cfg_root_path/question/edit/index.php?type=blank",$string['flash']=>"$cfg_root_path/question/edit/index.php?type=flash",$string['hotspot']=>"$cfg_root_path/question/edit/index.php?type=hotspot",$string['labelling']=>"$cfg_root_path/question/edit/index.php?type=labelling",$string['likert']=>"$cfg_root_path/question/edit/index.php?type=likert",$string['matrix']=>"$cfg_root_path/question/edit/index.php?type=matrix",$string['mcq']=>"$cfg_root_path/question/edit/index.php?type=mcq",$string['mrq']=>"$cfg_root_path/question/edit/index.php?type=mrq",$string['rank']=>"$cfg_root_path/question/edit/index.php?type=rank",$string['sct']=>"$cfg_root_path/question/edit/index.php?type=sct",$string['textbox']=>"$cfg_root_path/question/edit/index.php?type=textbox",$string['true_false']=>"$cfg_root_path/question/edit/index.php?type=true_false"));

  hideMenuScript($menuNo);
?>