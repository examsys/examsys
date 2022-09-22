<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

?>
<div id="left-sidebar" class="sidebar">
<?php
  $folder_ownerID = folder_utils::get_ownerID($folder, $mysqli);
  
if ($folder_ownerID == $userObject->get_user_ID()) {
    echo '<div class="menuitem folderprop"><a href="#"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/properties_icon.gif" alt="Properties" />' . $string['folderproperties'] . '</a></div>';
    echo '<div class="menuitem"><a href="' . $configObject->get('cfg_root_path') . '/folder/index.php?module=' . $module . '&folder=' . $folder . '&newfolder=y"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/folder_16.png" alt="New Folder" />' . $string['makesubfolder'] . '</a></div>';
    echo '<div class="menuitem deletefolder"><a href="#"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/red_cross.png" alt="Delete Folder" />' . $string['deletefolder'] . '</a></div>';
} else {
    echo '<div class="greymenuitem"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/properties_icon_grey.gif" alt="Properties" />' . $string['folderproperties'] . '</div>';
    echo '<div class="greymenuitem"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/folder_16_grey.png" alt="New Folder" />' . $string['makesubfolder'] . '</div>';
    echo '<div class="greymenuitem"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/red_cross_grey.png" alt="Delete Folder" />' . $string['deletefolder'] . '</div>';
}
?>
  <div class="menuitem cascade showmenu" data-popupid="0" data-popuptype="general" data-popupname="search"><a href="#" id="search"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/xmag.png" alt="Search" /><?php echo $string['search'] ?></a></div>
</div>
<?php
  makeMenu(array($string['questions'] => "{$configObject->get('cfg_root_path')}/question/search.php",$string['papers'] => "{$configObject->get('cfg_root_path')}/paper/search.php",$string['people'] => "{$configObject->get('cfg_root_path')}/users/search.php"));
