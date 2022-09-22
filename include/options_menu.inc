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
 * Sidebar menu of the ExamSys homepage
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

?><div id="left-sidebar" class="sidebar" style="overflow:hidden">
<?php
if (!$userObject->has_role('Standards Setter')) {
    ?>
<div id="general">
    <?php
    if ($userObject->has_role('SysAdmin')) {
        echo '<div class="menuitem"><a href="' . $configObject->get('cfg_root_path') . '/admin/index.php"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/admin_icon_16.gif" alt="Admin" />' . $string['admintools'] . '</a></div>';
    } elseif ($userObject->has_role('Admin')) {
        echo '<div class="menuitem"><a href="' . $configObject->get('cfg_root_path') . '/admin/calendar.php?calyear=' . date('Y') . '#' . date('n') . '"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/shortcut_calendar_icon.png" alt="Admin" />' . $string['calendar'] . '</a></div>';
    }
    ?>
    <div class="menuitem"><a href="<?php echo $configObject->get('cfg_root_path') ?>/index.php?newfolder=y"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/folder_16.png" alt="New Folder" /><?php echo $string['createfolder'] ?></a></div>
    <div class="menuitem"><a href="<?php echo $configObject->get('cfg_root_path') ?>/folder/list_keywords.php"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/key.png" alt="key" /><?php echo $string['mypersonalkeywords'] ?></a></div>
  <div class="menuitem cascade showmenu" data-popupid="0" data-popuptype="general" data-popupname="search"><a href="#" id="search"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/xmag.png" alt="Search" /><?php echo $string['search'] ?></a></div>
</div>
    <?php
    // -- Display top 10 recent papers ----------------------------------
    $recent_papers = Paper_utils::get_recent($userObject->get_user_ID(), $mysqli);
    if (count($recent_papers) > 0) {
        echo '<div style="font-size:180%; padding-top:40px" id="papertasks">' . $string['recent'] . '</div>';
    }

    echo '<ul type="square">';
    foreach ($recent_papers as $paperID => $paper_title) {
        echo '<li class="recent"><a ';
        if (mb_strpos($paper_title, '[deleted') !== false) {
            echo ' style="color:#808080"';
        }
        echo 'href="' . $configObject->get('cfg_root_path') . "/paper/details.php?paperID=$paperID\">$paper_title</a></li>\n";
    }
    echo '</ul>';
}
?>
<div id="displaycredits">ExamSys <?php echo $configObject->get_setting('core', 'rogo_version') ?> - &copy; 2018</div>

</div>

<?php
if (!$userObject->has_role('Standards Setter')) {
    makeMenu(array($string['questions'] => "{$configObject->get('cfg_root_path')}/question/search.php",$string['papers'] => "{$configObject->get('cfg_root_path')}/paper/search.php",$string['people'] => "{$configObject->get('cfg_root_path')}/users/search.php"));
}
