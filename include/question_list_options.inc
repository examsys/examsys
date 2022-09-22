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
 * Task menu for question list.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once $cfg_web_root . 'include/sidebar_menu.inc';
require_once $cfg_web_root . 'include/sidebar_functions.inc';
require_once $cfg_web_root . 'include/errors.php';
require_once $cfg_web_root . 'include/mapping.inc';

$module = check_var('module', 'GET', true, false, true);

$add_member = false;

if (!isset($module_details) && $module != '0') {
    $module_details = module_utils::get_full_details_by_ID($module, $mysqli);
}
?>

<div id="left-sidebar" class="sidebar">
<form name="PapersMenu" action="" autocomplete="off">

<div id="menu2a">
    <div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_play_grey.png" alt="<?php echo $string['quickview'] ?>" /><?php echo $string['quickview'] ?></div>
    <div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit_grey.png" alt="<?php echo $string['editquestion'] ?>" /><?php echo $string['editquestion'] ?></div>
    <div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon_grey.gif" alt="<?php echo $string['information'] ?>" /><?php echo $string['information'] ?></div>
    <div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon_grey.gif" alt="<?php echo $string['copyontopaperx'] ?>" /><?php echo $string['copyontopaperx'] ?></div>
    <div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link_grey.png" alt="<?php echo $string['linktopaperx'] ?>" /><?php echo $string['linktopaperx'] ?></div>
    <div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross_grey.png" alt="<?php echo $string['deletequestion'] ?>" /><?php echo $string['deletequestion'] ?></div>
</div>

<div id="menu2b">
    <div class="menuitem"><a id="preview" href="#"><img class="sidebar_icon" src="../artwork/small_play.png" alt="<?php echo $string['quickview'] ?>" /><?php echo $string['quickview'] ?></a></div>
    <div class="menuitem"><a id="edit" href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit.png" alt="<?php echo $string['editquestion'] ?>" /><?php echo $string['editquestion'] ?></a></div>
    <div class="menuitem"><a id="information" href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon.gif" alt="<?php echo $string['information'] ?>" /><?php echo $string['information'] ?></a></div>
    <div class="menuitem"><a id="copy" href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" alt="<?php echo $string['copyontopaperx'] ?>" /><?php echo $string['copyontopaperx'] ?></a></div>
    <div class="menuitem"><a id="link" href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link.png" alt="<?php echo $string['linktopaperx'] ?>" /><?php echo $string['linktopaperx'] ?></a></div>
    <div id="deleteitem" class="menuitem"><a id="delete" href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross.png" alt="<?php echo $string['deletequestion'] ?>" /><?php echo $string['deletequestion'] ?></a></div>
</div>

<div>
<div class="menuitem cascade showmenu" id="newquestion" data-popupid="0" data-popuptype="banktasks" data-popupname="newquestion"><a href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/new_question_menu_icon.gif" alt="newquestion" /><?php echo $string['createnewquestion'] ?></a></div>
</div>

<?php

if ($module != 0 and mb_strpos($module_details['checklist'], 'mapping') === false and $_GET['type'] == 'objective') {
    // Trying to access learning objectives when not selected at the module level.
} else {
    switch ($_GET['type']) {
        case 'all':
        case 'type':
            $display_type = $string['type'];
            break;
        case 'status':
            $display_type = $string['status'];
            break;
        case 'bloom':
            $display_type = $string['bloomstaxonomy'];
            break;
        case 'keyword':
            $display_type = $string['keyword'];
            break;
        case 'performance':
            $display_type = $string['performance'];
            break;
        case 'objective':
            $display_type = $string['learningoutcome'];
            break;
    }
    echo "<div style=\"padding-top:30px; font-size:170%\">$display_type</div>\n";
    ?>
  <div style="height:78%; overflow:auto" id="list">
    <?php
    echo '<div style="margin-bottom:15px"><input type="checkbox" id="check_locked" value="locked" checked="checked" /><label style="background-color:#F0C01E">' . $string['showlockedquestions'] . "&nbsp;</label></div>\n";
    if (isset($_GET['type'])) {
        if ($_GET['type'] == 'objective') {
            echo '<div style="margin-bottom:15px"><input type="text" name="filter" id="filter" style="font-size:110%" placeholder="' . $string['search'] . '" /> <button type="button" style="padding:2px 6px 2px 6px" id="filter_clear">' . $string['clear'] . '</button></div>';
        }

        $subtypes = $qbank->get_categories($type);
        if ($type != 'bloom') {    // Do not sort Bloom's Taxonomy categories.
            asort($subtypes);     // Sort the options alphabetically. Different languages need different orders.
        }

        foreach ($subtypes as $id => $subtype) {
            $data = '';

            if ($_GET['type'] == 'all') {
                $checked = ' checked';
            } elseif (isset($_GET['subtype']) and $_GET['subtype'] == $id) {
                $checked = ' checked';
            } else {
                $checked = '';
            }

            $hidden = '';
            if ($_GET['type'] == 'objective') {
                $ids = $subtype['ids'];
                $data = ' data-ids="' . implode(',', $ids) . '"';

                $subtype = $subtype['label'];
                $hidden = ' class="hidden"';
            }

            echo "<div{$hidden}><input type=\"checkbox\" class=\"check_type\" id=\"check_$id\" value=\"$id\" $checked $data/><label for=\"check_$id\" class=\"check_label\">$subtype</label></div>\n";
        }
    }
}
?>
</div>

<input type="hidden" name="questionID" id="questionID" value="" />
<input type="hidden" name="qType" id="qType" value="" />
<input type="hidden" name="oldQuestionID" id="oldQuestionID" value="" />
<input type="hidden" name="team" id="team" value="<?php if (isset($_GET['team'])) {
    echo $_GET['team'];
                                                  } ?>" />
<input type="hidden" name="keyword" id="keyword" value="<?php if (isset($_GET['keyword'])) {
    echo $_GET['keyword'];
                                                        } ?>" />
<input type="hidden" name="module" id="module" value="<?php echo $module ?>" />
<input type="hidden" name="language" id="language" value="<?php echo $language ?>" />
<input type="hidden" name="type" id="type" value="<?php echo $_GET['type'] ?>" />

</form>
</div>
<?php

  makeMenu(array(
    $string['info'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=info&module=$module",
    $string['keyword_based'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=keyword_based&module=$module",
    $string['random'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=random&module=$module",
    '-' => '-',
    $string['area'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=area&module=$module",
    $string['calculation'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=enhancedcalc&module=$module",
    $string['dichotomous'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=dichotomous&module=$module",
    $string['extmatch'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=extmatch&module=$module",
    $string['blank'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=blank&module=$module",
    $string['hotspot'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=hotspot&module=$module",
    $string['labelling'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=labelling&module=$module",
    $string['likert'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert&module=$module",
    $string['matrix'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=matrix&module=$module",
    $string['mcq'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq&module=$module",
    $string['mrq'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mrq&module=$module",
    $string['rank'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=rank&module=$module",
    $string['sct'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=sct&module=$module",
    $string['textbox'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=textbox&module=$module",
    $string['true_false'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=true_false&module=$module"));
