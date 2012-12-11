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

include_once '../classes/dateutils.class.php';
include_once '../classes/moduleutils.class.php';
require_once '../include/sidebar_functions.inc';

$modules_array = array();
//$staff_modules is populated in staff_auth.inc
$my_folders_array = get_folders($staff_modules, $userObject->get_user_ID(), $mysqli);
$full_modules_array = get_modules($staff_modules, $my_folders_array, $modules_array, $mysqli);
$staff_modules_array = get_staff_module_links($staff_modules, $mysqli);
$keywords_array = get_keywords($staff_modules, $userObject->get_user_ID(), $mysqli);

?>
<div id="left-sidebar" class="sidebar">
<?php
  if ($folder != '') {       // Currently in a folder
?>
<div class="submenuheading"><?php echo $string['foldertasks']; ?></div>
<table class="sidebar_tbl">
<?php

  if ($folder_ownerID == $userObject->get_user_ID() or $userObject->has_role('SysAdmin')) {
    echo '<tr><td class="ico"><a href="#" onclick="folderProperties(); return false;"><img src="' . $configObject->get('cfg_root_path') . '/artwork/properties_icon.gif" width="16" height="16" alt="Properties" /></a></td><td class="menuitem"><a href="#" onclick="folderProperties(); return false;">' . $string['folderproperties'] . '</a></td></tr>';
    echo '<tr><td class="ico"><a href="' . $configObject->get('cfg_root_path') . '/folder/details.php?module=' . $module . '&folder=' . $folder . '&newfolder=y"><img src="' . $configObject->get('cfg_root_path') . '/artwork/folder_16.png" width="16" height="16" alt="New Folder" /></a></td><td class="menuitem"><a href="' . $configObject->get('cfg_root_path') . '/folder/details.php?module=' . $module . '&folder=' . $_GET['folder'] . '&newfolder=y">' . $string['makesubfolder'] . '</a></td></tr>';
    echo '<tr><td class="ico"><a href="#" onclick="deleteFolder(); return false;"><img src="' . $configObject->get('cfg_root_path') . '/artwork/red_cross.png" width="16" height="16" alt="Delete Folder" /></a></td><td class="menuitem"><a href="#" onclick="deleteFolder(); return false;">' . $string['deletefolder'] . '</a></td></tr>';
  } else {
    echo '<tr><td class="ico"><img src="' . $configObject->get('cfg_root_path') . '/artwork/properties_icon_grey.gif" width="16" height="16" alt="Properties" /></td><td class="greymenuitem">' . $string['folderproperties'] . '</td></tr>';
    echo '<tr><td class="ico"><img src="' . $configObject->get('cfg_root_path') . '/artwork/red_cross_grey.png" width="16" height="16" alt="Delete Folder" /></td><td class="greymenuitem">' . $string['deletefolder'] . '</td></tr>';
  }
?>
</table>

<br />

<?php
  } else {
  
    $module_details = module_utils::get_full_details_by_ID($_GET['module'], $mysqli);
  
    if ($module != '') {   
    ?>
      <div class="submenuheading"><?php echo $string['module']; ?></div>
      <table class="sidebar_tbl">
    <?php

    if ($userObject->has_role('SysAdmin')) {
      echo "<tr><td class=\"ico\"><a href=\"../admin/edit_module.php?moduleid=$module\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/admin_icon_16.gif\" width=\"16\" height=\"16\" alt=\"cog\" /></a></td><td class=\"menuitem\"><a href=\"../admin/edit_module.php?moduleid=$module\">" . $string['editpropertiessysadmin'] . "</a></td></tr>";
    }
    if (strpos($module_details['checklist'],'mapping') !== false ) {     // Curriculum mapping switched on for the module
      echo "<tr><td class=\"ico\"><a href=\"../mapping/sessions_list.php?module=" . $module . "\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/module_icon_16.png\" width=\"16\" height=\"16\" alt=\"modules\" /></a></td><td class=\"menuitem\"><a href=\"../mapping/sessions_list.php?module=" . $_GET['module'] . "\">" . $string['manageobjectives'] . "</a></td></tr>\n";
    }
    echo "<tr><td class=\"ico\"><a href=\"../folder/list_keywords.php?module=$module\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/key.png\" width=\"16\" height=\"16\" alt=\"key\" /></a></td><td class=\"menuitem\"><a href=\"../folder/list_keywords.php?module=$module\">" . $string['managekeywords'] . "</a></td></tr>";
    echo "<tr><td class=\"ico\"><a href=\"../folder/list_ref_material.php?module=$module\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/ref_16.png\" width=\"16\" height=\"16\" alt=\"key\" /></a></td><td class=\"menuitem\"><a href=\"../folder/list_ref_material.php?module=$module\">" . $string['referencematerial'] . "</a></td></tr>";
    echo '<tr><td class="ico"><a href="../users/load_users_metadata.php?module=' . $_GET['module'] . '"><img src="' . $configObject->get('cfg_root_path') . '/artwork/import_16.gif" width="16" height="16" alt="Import Metadata" /></a></td><td class="menuitem"><a href="../users/import_users_metadata.php?module=' . $_GET['module'] . '">' . $string['importstudentmetadata'] . '</a></td></tr>';
  ?>
  </table>
  
  <br />

<?php
    }
  }
?>

<div class="submenuheading" id="papertasks"><?php echo $string['papertasks']; ?></div>
<table class="sidebar_tbl">
<?php
  if (count($staff_modules) > 0) {
    echo "<tr><td class=\"ico\"><a href=\"#\" onclick=\"newPaper(); return false;\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/new_paper_icon_16.gif\" width=\"16\" height=\"16\" alt=\"New Paper\" /></a></td><td class=\"menuitem\"><a href=\"#\" onclick=\"newPaper(); return false;\">" . $string['createnewpaper'] . "</a></td></tr>\n";
  } else {
    echo "<tr><td class=\"ico\" style=\"color:#808080\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/new_paper_icon_16.gif\" width=\"16\" height=\"16\" alt=\"New Paper\" /></td><td class=\"menuitem\">" . $string['createnewpaper'] . "</td></tr>\n";
  }

?>
<tr><td width="16"><a href="#" onclick="showMenu('popup0','papertasks','listpapers',myOptions0,myURLs0,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/list_papers_icon_16.gif" width="16" height="16" alt="List Papers" /></a></td><td class="menuitem sub"><a href="#" onclick="showMenu('popup0','papertasks','listpapers',myOptions0,myURLs0,event); return false;"><span id="listpapers"><?php echo $string['listpapers']; ?></span></a></td></tr>
</table>

<br />

<div class="submenuheading" id="banktasks"><?php echo $string['questionbank']; ?></div>
<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%; margin-left:9px">
<tr><td class="ico"><a href="#" onclick="showMenu('popup1','banktasks','type',myOptions1,myURLs1,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/question_list_icon.gif" width="16" height="16" alt="List Papers" /></a></td><td class="menuitem sub"><a href="#" onclick="showMenu('popup1','banktasks','type',myOptions1,myURLs1,event); return false;"><span id="type"><?php echo $string['questionsbytype']; ?></span></a></td></tr>
<tr><td class="ico"><a href="#" onclick="showMenu('popup2','banktasks','team',myOptions2,myURLs2,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/question_list_icon.gif" width="16" height="16" alt="List Teams" /></a></td><td class="menuitem sub"><a href="#" onclick="showMenu('popup2','banktasks','team',myOptions2,myURLs2,event); return false;"><span id="team"><?php echo $string['questionsbyteam']; ?></span></a></td></tr>
<tr><td class="ico"><a href="#" onclick="showMenu('popup3','banktasks','keyword',myOptions3,myURLs3,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/question_list_icon.gif" width="16" height="16" alt="List Keywords" /></a></td><td class="menuitem sub"><a href="#" onclick="showMenu('popup3','banktasks','keyword',myOptions3,myURLs3,event); return false;"><span id="keyword"><?php echo $string['questionsbykeyword']; ?></span></a></td></tr>
<tr><td class="ico"><a href="#" onclick="showMenu('popup4','banktasks','search',myOptions4,myURLs4,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/xmag.png" width="16" height="16" alt="Search" /></a></td><td class="menuitem sub"><a href="#" onclick="showMenu('popup4','banktasks','search',myOptions4,myURLs4,event); return false;"><span id="search"><?php echo $string['search']; ?></span></a></td></tr>
<tr><td class="ico"><a href="#" onclick="showMenu('popup5','banktasks','newquestion',myOptions5,myURLs5,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/new_question_menu_icon.gif" width="16" height="16" alt="newquestion" /></a></td><td class="menuitem sub"><a href="#" onclick="showMenu('popup5','banktasks','newquestion',myOptions5,myURLs5,event); return false;"><span id="newquestion"><?php echo $string['createnewquestion']; ?></span></a></td></tr>
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
  makeMenu($full_modules_array);
  makeMenu(array($string['info']=>$configObject->get('cfg_root_path') . '/question/list.php?type=info',$string['keyword_based']=>$configObject->get('cfg_root_path') . '/question/list.php?type=keyword',$string['random']=>$configObject->get('cfg_root_path') . '/question/list.php?type=random','-'=>'-',$string['calculation']=>$configObject->get('cfg_root_path') . '/question/list.php?type=calculation',$string['dichotomous']=>$configObject->get('cfg_root_path') . '/question/list.php?type=dichotomous',$string['extmatch']=>$configObject->get('cfg_root_path') . '/question/list.php?type=extmatch',$string['blank']=>$configObject->get('cfg_root_path') . '/question/list.php?type=blank',$string['flash']=>$configObject->get('cfg_root_path') . '/question/list.php?type=flash',$string['hotspot']=>$configObject->get('cfg_root_path') . '/question/list.php?type=hotspot',$string['labelling']=>$configObject->get('cfg_root_path') . '/question/list.php?type=labelling',$string['likert']=>$configObject->get('cfg_root_path') . '/question/list.php?type=likert',$string['matrix']=>$configObject->get('cfg_root_path') . '/question/list.php?type=matrix',$string['mcq']=>$configObject->get('cfg_root_path') . '/question/list.php?type=mcq',$string['mrq']=>$configObject->get('cfg_root_path') . '/question/list.php?type=mrq',$string['rank']=>$configObject->get('cfg_root_path') . '/question/list.php?type=rank',$string['sct']=>$configObject->get('cfg_root_path') . '/question/list.php?type=sct',$string['textbox']=>$configObject->get('cfg_root_path') . '/question/list.php?type=textbox', $string['true_false']=>$configObject->get('cfg_root_path') . '/question/list.php?type=true_false'));
  makeMenu($staff_modules_array);
  makeMenu($keywords_array);
  if (isset($_GET['module'])) {
    makeMenu(array($string['questions']=>$configObject->get('cfg_root_path') . '/question/search.php', $string['papers']=>$configObject->get('cfg_root_path') . '/paper/search.php', $string['people']=>$configObject->get('cfg_root_path') . '/users/search.php', '-'=>'-', 'Students on ' . $_GET['module'] . ' (' . date_utils::get_current_academic_year() . ')'=>$configObject->get('cfg_root_path') . '/users/search.php?submit=Search&team=' . $_GET['module'] . '&calendar_year=' . date_utils::get_current_academic_year() . '&students=on&search_username=&student_id='));
  } else {
    makeMenu(array($string['questions']=>$configObject->get('cfg_root_path') . '/question/search.php', $string['papers']=>$configObject->get('cfg_root_path') . '/paper/search.php', $string['people']=>$configObject->get('cfg_root_path') . '/users/search.php'));
  }
  makeMenu(array($string['info']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=info",$string['keyword_based']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=keyword_based",$string['random']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=random","-"=>"-",$string['calculation']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=calculation",$string['dichotomous']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=dichotomous",$string['extmatch']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=extmatch",$string['blank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=blank",$string['flash']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=flash",$string['hotspot']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=hotspot",$string['labelling']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=labelling",$string['likert']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert",$string['matrix']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=matrix",$string['mcq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq",$string['mrq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mrq",$string['rank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=rank",$string['sct']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=sct",$string['textbox']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=textbox",$string['true_false']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=true_false"));

  hideMenuScript($menuNo);
?>