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

require_once 'sidebar_functions.inc';

$modules_array = array();
//$staff_modules is populated in staff_auth.inc
$my_folders_array = get_folders($staff_modules, $userObject->get_user_ID(), $mysqli);
$full_modules_array = get_modules($staff_modules, $my_folders_array, $modules_array, $mysqli);
$staff_modules_array = get_staff_module_links($staff_modules, $mysqli);
$keywords_array = get_keywords($staff_modules, $userObject->get_user_ID(), $mysqli);

?><div id="left-sidebar" class="sidebar">
<br />
<table id="general" class="sidebar_tbl" style="margin-left:2px">
<tr><td class="ico"><a href="#" onclick="launchHelp(1); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_help_icon.gif" width="16" height="16" alt="Help" /></a></td><td class="menuitem"><a href="#" onclick="launchHelp(1); return false;"><?php echo $string['helpsupport']; ?></a></td></tr>
<?php
  if ($userObject->has_role('SysAdmin')) {
    echo '<tr><td class="ico"><a href="' . $configObject->get('cfg_root_path') . '/admin/index.php"><img src="' . $configObject->get('cfg_root_path') . '/artwork/admin_icon_16.gif" width="16" height="16" alt="Admin" /></a></td><td class="menuitem"><a href="' . $configObject->get('cfg_root_path') . '/admin/index.php">' . $string['admintools'] . '</a></td></tr>';
  } elseif ($userObject->has_role('Admin')) {
    echo '<tr><td class="ico"><a href="' . $configObject->get('cfg_root_path') . '/admin/calendar.php?calyear=' . date("Y") . '#' . date("n") . '"><img src="' . $configObject->get('cfg_root_path') . '/artwork/shortcut_calendar_icon.png" width="16" height="16" alt="Admin" /></a></td><td class="menuitem"><a href="' . $configObject->get('cfg_root_path') . '/admin/calendar.php?calyear=' . date("Y") . '#' . date("n") . '">' . $string['calendar'] . '</a></td></tr>';
  }
?>
<tr><td class="ico"><a href="<?php echo $configObject->get('cfg_root_path') ?>/users/search.php"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_user_icon.gif" width="16" height="16" alt="Users" /></a></td><td class="menuitem"><a href="<?php echo $configObject->get('cfg_root_path') ?>/users/search.php"><?php echo $string['usermanagement']; ?></a></td></tr>
<tr><td class="ico"><a href="<?php echo $configObject->get('cfg_root_path') ?>/staff/index.php?newfolder=y"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/folder_16.png" width="16" height="16" alt="New Folder" /></a></td><td class="menuitem"><a href="<?php echo $configObject->get('cfg_root_path') ?>/staff/index.php?newfolder=y"><?php echo $string['makeafolder']; ?></a></td></tr>
<tr><td class="ico"><a href="<?php echo $configObject->get('cfg_root_path') ?>/folder/list_keywords.php"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/key.png" width="16" height="16" alt="key" /></a></td><td class="menuitem"><a href="<?php echo $configObject->get('cfg_root_path') ?>/folder/list_keywords.php"><?php echo $string['mypersonalkeywords']; ?></a></td></tr>
</table>
<br />

<div class="submenuheading" id="papertasks"><?php echo $string['papertasks']; ?></div>
<table class="sidebar_tbl">
<?php
  if (count($staff_modules) > 0 or $userObject->has_role(array('Admin','SysAdmin'))) {    // Allow SysAdmin and Admin users not on any team to still create papers.
    echo "<tr><td style=\"text-align:left\"><a href=\"#\" onclick=\"newPaper(); return false;\"><img style=\"text-align:center\" src=\"{$configObject->get('cfg_root_path')}/artwork/new_paper_icon_16.gif\" width=\"16\" height=\"16\" alt=\"New Paper\" /></a></td><td class=\"menuitem\"><a href=\"#\" onclick=\"newPaper(); return false;\">" . $string['createnewpaper'] . "</a></td></tr>\n";
  } else {
    echo "<tr><td style=\"text-align:left; color:#808080\"><img style=\"text-align:center\" src=\"{$configObject->get('cfg_root_path')}/artwork/new_paper_icon_16.gif\" width=\"16\" height=\"16\" alt=\"New Paper\" /></td><td class=\"menuitem\" style=\"color:#808080\">" . $string['createnewpaper'] . "</td></tr>\n";
  }
?>
<tr><td width="16"><a href="#" onclick="showMenu('popup0','papertasks','listpapers',myOptions0,myURLs0,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/list_papers_icon_16.gif" width="16" height="16" alt="List Papers" /></a></td><td class="menuitem sub"><span id="listpapers"><a href="#" onclick="showMenu('popup0','papertasks','listpapers',myOptions0,myURLs0,event); return false;"><?php echo $string['listpapers']; ?></a></span></td></tr>
</table>

<br />

<div class="submenuheading" id="banktasks"><?php echo $string['questionbanktasks']; ?></div>
<table class="sidebar_tbl">
<tr><td class="ico"><a href="#" onclick="showMenu('popup1','banktasks','type',myOptions1,myURLs1,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/question_list_icon.gif" width="16" height="16" alt="List Papers" /></a></td><td class="menuitem sub"><span id="type"><a href="#" onclick="showMenu('popup1','banktasks','type',myOptions1,myURLs1,event); return false;"><?php echo $string['questionsbytype']; ?></span></a></td></tr>
<?php
  if (count($staff_modules) == 0) {
    echo "<tr><td class=\"ico\"><img src=\"" . $configObject->get('cfg_root_path') . "/artwork/question_list_icon_grey.gif\" width=\"16\" height=\"16\" alt=\"List Teams\" /></td><td class=\"greymenuitem sub\"><span id=\"team\">" . $string['questionsbyteam'] . "</span></td></tr>\n";
  } else {
    echo "<tr><td class=\"ico\"><a href=\"#\" onclick=\"showMenu('popup2','banktasks','team',myOptions2,myURLs2,event); return false;\"><img src=\"" . $configObject->get('cfg_root_path') . "/artwork/question_list_icon.gif\" width=\"16\" height=\"16\" alt=\"List Teams\" /></a></td><td class=\"menuitem sub\"><span id=\"team\"><a href=\"#\" onclick=\"showMenu('popup2','banktasks','team',myOptions2,myURLs2,event); return false;\">" . $string['questionsbyteam'] . "</a></span></td></tr>\n";
  }
  if (count($keywords_array) == 0) {
    echo "<tr><td class=\"ico\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/question_list_icon_grey.gif\" width=\"16\" height=\"16\" alt=\"List Keywords\" /></td><td class=\"greymenuitem sub\"><span id=\"keyword\">" . $string['questionsbykeyword'] . "</span></td></tr>\n";
  } else {
    echo "<tr><td class=\"ico\"><a href=\"#\" onclick=\"showMenu('popup3','banktasks','keyword',myOptions3,myURLs3,event); return false;\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/question_list_icon.gif\" width=\"16\" height=\"16\" alt=\"List Keywords\" /></a></td><td class=\"menuitem sub\"><span id=\"keyword\"><a href=\"#\" onclick=\"showMenu('popup3','banktasks','keyword',myOptions3,myURLs3,event); return false;\">" . $string['questionsbykeyword'] . "</a></span></td></tr>\n";
  }
?>
<tr><td class="ico"><a href="#" onclick="showMenu('popup4','banktasks','search',myOptions4,myURLs4,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/xmag.png" width="16" height="16" alt="Search" /></a></td><td class="menuitem sub"><span id="search"><a href="#" onclick="showMenu('popup4','banktasks','search',myOptions4,myURLs4,event); return false;"><?php echo $string['search']; ?></a></span></td></tr>
<tr><td class="ico"><a href="#" onclick="showMenu('popup5','banktasks','newquestion',myOptions5,myURLs5,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/new_question_menu_icon.gif" width="16" height="16" alt="newquestion" /></a></td><td class="menuitem sub"><span id="newquestion"><a href="#" onclick="showMenu('popup5','banktasks','newquestion',myOptions5,myURLs5,event); return false;"><?php echo $string['createnewquestion']; ?></a></span></td></tr>
</table>

<div style="position:absolute; bottom:15px; text-align:center; width:90%; cursor:pointer; font-size:90%"><a style="color:blue" href="#" onclick="displayCredits(); return false;">Rogō <?php echo $configObject->get('rogo_version'); ?> - &copy; 2012</a></div>
</div>

<?php
  // Create the sub-menus.
  makeMenu($full_modules_array);
  makeMenu(array($string['info']=>$configObject->get('cfg_root_path') . '/question/list.php?type=info',$string['keyword_based']=>$configObject->get('cfg_root_path') . '/question/list.php?type=keyword',$string['random']=>$configObject->get('cfg_root_path') . '/question/list.php?type=random','-'=>'-',$string['area']=>$configObject->get('cfg_root_path') . '/question/list.php?type=area',$string['calculation']=>$configObject->get('cfg_root_path') . '/question/list.php?type=calculation',$string['dichotomous']=>$configObject->get('cfg_root_path') . '/question/list.php?type=dichotomous',$string['extmatch']=>$configObject->get('cfg_root_path') . '/question/list.php?type=extmatch',$string['blank']=>$configObject->get('cfg_root_path') . '/question/list.php?type=blank',$string['flash']=>$configObject->get('cfg_root_path') . '/question/list.php?type=flash',$string['hotspot']=>$configObject->get('cfg_root_path') . '/question/list.php?type=hotspot',$string['labelling']=>$configObject->get('cfg_root_path') . '/question/list.php?type=labelling',$string['likert']=>$configObject->get('cfg_root_path') . '/question/list.php?type=likert',$string['matrix']=>$configObject->get('cfg_root_path') . '/question/list.php?type=matrix',$string['mcq']=>$configObject->get('cfg_root_path') . '/question/list.php?type=mcq',$string['mrq']=>$configObject->get('cfg_root_path') . '/question/list.php?type=mrq',$string['rank']=>$configObject->get('cfg_root_path') . '/question/list.php?type=rank',$string['sct']=>$configObject->get('cfg_root_path') . '/question/list.php?type=sct',$string['textbox']=>$configObject->get('cfg_root_path') . '/question/list.php?type=textbox', $string['true_false']=>$configObject->get('cfg_root_path') . '/question/list.php?type=true_false'));
  makeMenu($staff_modules_array);
  makeMenu($keywords_array);
  makeMenu(array($string['questions']=>$configObject->get('cfg_root_path') . '/question/search.php',$string['papers']=>$configObject->get('cfg_root_path') . '/paper/search.php',$string['people']=>$configObject->get('cfg_root_path') . '/users/search.php'));
  makeMenu(array($string['info']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=info",$string['keyword_based']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=keyword_based",$string['random']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=random","-"=>"-",$string['area']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=area",$string['calculation']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=calculation",$string['dichotomous']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=dichotomous",$string['extmatch']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=extmatch",$string['blank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=blank",$string['flash']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=flash",$string['hotspot']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=hotspot",$string['labelling']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=labelling",$string['likert']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert",$string['matrix']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=matrix",$string['mcq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq",$string['mrq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mrq",$string['rank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=rank",$string['sct']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=sct",$string['textbox']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=textbox",$string['true_false']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=true_false"));

  hideMenuScript($menuNo);
?>