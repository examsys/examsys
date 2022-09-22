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

require '../include/staff_auth.inc';
require '../include/errors.php';

$refID = check_var('refID', 'GET', true, false, true);
$texteditorplugin = \plugins\plugins_texteditor::get_editor();
if (!refmaterials_utils::refmaterials_exist($refID, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (isset($_POST['submit'])) {
    $content = $texteditorplugin->prepare_text_for_save($_POST['ref_content']);
    // Write the reference material
    $result = $mysqli->prepare('UPDATE reference_material SET title = ?, content = ?, width = ? WHERE id = ?');
    $result->bind_param('sssi', $_POST['title'], $content, $_POST['width'], $_GET['refID']);
    $result->execute();

    // Add it to the modules
    $result = $mysqli->prepare('DELETE FROM reference_modules WHERE refID = ?');
    $result->bind_param('i', $_GET['refID']);
    $result->execute();

    for ($i = 0; $i < $_POST['module_no']; $i++) {
        if (isset($_POST['mod' . $i])) {
            $result = $mysqli->prepare('INSERT INTO reference_modules VALUES (NULL, ?, ?)');
            $result->bind_param('ii', $_GET['refID'], $_POST['mod' . $i]);
            $result->execute();
        }
    }

    header('location: list_ref_material.php?module=' . $_POST['module']);
    exit();
}

$result = $mysqli->prepare('SELECT title, content, width FROM reference_material WHERE id = ?');
$result->bind_param('i', $_GET['refID']);
$result->execute();
$result->bind_result($title, $content, $width);
$result->fetch();
$result->close();

$ref_modules = array();

$result = $mysqli->prepare('SELECT moduleID FROM reference_modules, modules WHERE reference_modules.idMod = modules.id AND refID = ?');
$result->bind_param('i', $_GET['refID']);
$result->execute();
$result->bind_result($moduleID);
while ($result->fetch()) {
    $ref_modules[] = $moduleID;
}
$result->close();

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>ExamSys: New Reference Material</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/refmaterial.css" />
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/refmaterialforminit.min.js"></script>
<?php
$texteditorplugin->display_header();
$texteditorplugin->get_javascript_config(\plugins\plugins_texteditor::CONFIG);
?>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';

    echo draw_toprightmenu();
?>
<div class="head_title" style="font-size:90%">
    <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="details.php?module=<?php echo $_GET['module'] ?>"><?php echo module_utils::get_moduleid_from_id($_GET['module'], $mysqli); ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="list_ref_material.php?module=<?php echo $_GET['module']; ?>"><?php echo $string['referencematerial']; ?></a></div>
  <div class="page_title">Reference Material</div>
</div>

<form id="theform" action="<?php echo $_SERVER['PHP_SELF'] . '?refID=' . $_GET['refID']; ?>" method="post" charset="UTF-8" autocomplete="off">
<br />
<table border="0" style="text-align:left; margin-left:auto; margin-right:auto; font-size:80%">
<tr><td><?php echo $string['name']; ?> <input type="text" name="title" size="40" value="<?php echo $title; ?>" required autofocus />&nbsp;&nbsp;&nbsp;<?php echo $string['width']; ?> <select name="width"><?php
for ($size = 200; $size < 850; $size += 50) {
    if ($width == $size) {
        echo "<option value=\"$size\" selected>" . $size . "px</option>\n";
    } else {
        echo "<option value=\"$size\">" . $size . "px</option>\n";
    }
}
?></select></td><td><?php echo $string['modules']; ?></td></tr>
<tr><td><?php $texteditorplugin->get_textarea('ref_content', 'ref_content', $texteditorplugin->get_text_for_display($content), plugins\plugins_texteditor::TYPE_STANDARD); ?></td><td style="vertical-align:top">
<?php
  echo '<div style="margin-top:1px; display:block; width:420px; height:604px; overflow-y:scroll; border:1px solid #909090; font-size:90%">';

  $extra_modules = array();
  $result = $mysqli->prepare('SELECT idMod FROM reference_modules WHERE refID = ?');
  $result->bind_param('i', $_GET['refID']);
  $result->execute();
  $result->bind_result($idMod);
while ($result->fetch()) {
    $extra_modules[] = $idMod;
}
  $result->close();

  $module_array = $userObject->get_staff_accessable_modules($extra_modules);

  $module_no = 0;
  $old_school = '';
  $old_schoolcode = '';
foreach ($module_array as $modID => $module) {
    if (is_null($module['schoolcode'])) {
        if ($module['school'] != $old_school or !is_null($old_schoolcode)) {
            echo '<table border="0" class="school"><tr><td><nobr>' . $module['school'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
        }
    } else {
        if ($module['schoolcode'] != $old_schoolcode) {
            echo '<table border="0" class="school"><tr><td><nobr>' . $module['schoolcode']  . ' ' . $module['school'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
        }
    }
    $match = false;
    foreach ($ref_modules as $separate_module) {
        if ($separate_module == $module['id']) {
            $match = true;
        }
    }
    if ($match == true) {
        if ($userObject->is_staff_user_on_module($modID) or $userObject->has_role('SysAdmin')) {
            echo "<div class=\"r2\" id=\"divmod$module_no\"><input type=\"checkbox\" name=\"mod$module_no\" id=\"mod$module_no\" value=\"$modID\" checked><label for=\"mod$module_no\">" . $module['id'] . ': ' . mb_substr($module['fullname'], 0, 60) . "</label></div>\n";
        } else {
            echo "<div class=\"r2\" id=\"divmod$module_no\"><input type=\"checkbox\" name=\"dummymod$module_no\" value=\"" . $module['id'] . "\" checked disabled><input type=\"checkbox\" name=\"mod$module_no\" id=\"mod$module_no\" style=\"display:none\" value=\"$modID\" checked>" . $module['id'] . ': ' . mb_substr($module['fullname'], 0, 60) . "</div>\n";
        }
    } else {
        echo "<div class=\"r1\" id=\"divmod$module_no\"><input type=\"checkbox\" name=\"mod$module_no\" id=\"mod$module_no\" value=\"$modID\"><label for=\"mod$module_no\">" . $module['id'] . ': ' . mb_substr($module['fullname'], 0, 60) . "</label></div>\n";
    }
    $module_no++;
    $old_school = $module['school'];
    $old_schoolcode = $module['schoolcode'];
}
  echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"$module_no\" /></div>\n";
?>
</td>
</tr>
<tr><td colspan="2" style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['ok']; ?>" class="ok" style="font-size:90%" /><input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" class="cancel" style="font-size:90%" /></td></tr>
</table>
<input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" />
</form>

</body>
</html>
