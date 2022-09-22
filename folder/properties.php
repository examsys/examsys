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
require_once '../include/errors.php';

$folderID = check_var('folder', 'GET', true, false, true);

$ownerID = 0;

$result = $mysqli->prepare('SELECT ownerID FROM folders WHERE id = ?');
$result->bind_param('i', $folderID);
$result->execute();
$result->bind_result($ownerID);
$result->fetch();
$result->close();

if ($ownerID != $userObject->get_user_ID()) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['folderproperties']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
    <link rel="stylesheet" type="text/css" href="../css/folderproperties.css" />

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/folderpropertiesinit.min.js"></script>

</head>
<?php

$result = $mysqli->prepare("SELECT name, color, DATE_FORMAT(created, '{$configObject->get('cfg_long_date_time')}'), title, initials, surname FROM folders, users WHERE folders.ownerID = users.id AND folders.id = ?");
$result->bind_param('i', $folderID);
$result->execute();
$result->bind_result($full_path, $color, $created, $title, $initials, $surname);
$result->fetch();
$result->close();

$owner = $title . ' ' . $initials . ', ' . $surname;

$folder_staff_modules = array();
$result = $mysqli->prepare('SELECT idMod, moduleID FROM folders, folders_modules_staff, modules WHERE folders.id = folders_modules_staff.folders_id AND modules.id = folders_modules_staff.idMod AND folders.id = ?');
$result->bind_param('i', $folderID);
$result->execute();
$result->bind_result($idMod, $moduleID);
while ($result->fetch()) {
    $folder_staff_modules[$idMod] = $moduleID;
}
$result->close();

?>
<body>
<form id="theform" name="edit_form" method="post" action="" autocomplete="off">
<table border="0" cellpadding="4" cellspacing="0" width="100%">
<tr>
<td style="width:48px; background-color:white; text-align:left"><img src="../artwork/properties.png" width="48" height="48" alt="Properties" /></td><td style="background-color:white; text-align:left">&nbsp;&nbsp;<span class="midblue_header" style="font-size:160%; font-weight:bold"><?php echo $string['folderproperties']; ?></span></td>
</tr>
<tr>
<td style="text-align:left" colspan="2">
    <br />
    <?php

      $folder_array = explode(';', $full_path);
      $sections = mb_substr_count($full_path, ';');
      $current_folder = $folder_array[$sections];
      $prefix = mb_substr($full_path, 0, strrpos($full_path, ';'));
      echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" style=\"width:100%\" >\n";
      echo '<tr><td style="text-align:right"><nobr>' . $string['foldername'] . '&nbsp;</nobr></td><td colspan="3"><input';
      echo " type=\"text\" size=\"50\" maxlength=\"255\" value=\"$current_folder\" id=\"folder\" name=\"folder\" required />";
      echo '<span id="duplicateerror">' . $string['nameinuse'] . '</span>';
      echo "<input type=\"hidden\" name=\"old_folder\" value=\"$full_path\"><input type=\"hidden\" name=\"old_prefix\" value=\"$prefix\"></td></tr>\n";
      echo '<input type="hidden" name="folderID" value="' . $folderID . '" />';
      echo '<tr><td align="right" valign="middle">' . $string['colour'] . '&nbsp;</td><td>';
      echo '<input type="radio" name="color" value="yellow"';
    if ($color == 'yellow') {
        echo ' checked';
    }
      echo ' /><img src="../artwork/yellow_folder.png" width="48" height="48" alt="Yellow" />';
      echo '<input type="radio" name="color" value="red"';
    if ($color == 'red') {
        echo ' checked';
    }
      echo ' /><img src="../artwork/red_folder.png" width="48" height="48" alt="Red" />';
      echo '<input type="radio" name="color" value="green"';
    if ($color == 'green') {
        echo ' checked';
    }
      echo ' /><img src="../artwork/green_folder.png" width="48" height="48" alt="Green" />';
      echo '<input type="radio" name="color" value="blue"';
    if ($color == 'blue') {
        echo ' checked';
    }
      echo ' /><img src="../artwork/blue_folder.png" width="48" height="48" alt="Blue" />';
      echo '<input type="radio" name="color" value="grey"';
    if ($color == 'grey') {
        echo ' checked';
    }
      echo ' /><img src="../artwork/grey_folder.png" width="48" height="48" alt="Grey" />';
      echo "</td></tr>\n";
      echo '<tr><td align="right" valign="top">' . $string['owner'] . "&nbsp;</td><td>$owner</td></tr>\n";
      echo '<tr><td align="right" valign="top">' . $string['created'] . "&nbsp;</td><td>$created</td></tr>\n";

      echo '<tr><td align="right">' . $string['teams'] . '&nbsp;</td><td><div style="background-color:white; display:block; height:320px; width:100%; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%">';

      $module_no = 0;
      $old_school = '';
      $old_schoolcode = '';

    foreach ($userObject->get_staff_accessable_modules() as $IdMod => $module) {
        if (is_null($module['schoolcode'])) {
            if ($module['school'] != $old_school or !is_null($old_schoolcode)) {
                echo '<table border="0" class="school"><tr><td><nobr>' . $module['school'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
            }
        } else {
            if ($module['schoolcode'] != $old_schoolcode) {
                echo '<table border="0" class="school"><tr><td><nobr>' . $module['schoolcode'] . ' ' . $module['school'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
            }
        }
        if (isset($folder_staff_modules[$IdMod])) {
            if ($userObject->is_staff_user_on_module($IdMod) or $userObject->has_role('SysAdmin')) {
                echo "<div class=\"r2\" id=\"divmodule$module_no\"><input type=\"checkbox\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module['idMod'] . "\" checked>&nbsp;<label for=\"module$module_no\">" . $module['id'] . ': ' . mb_substr($module['fullname'], 0, 60) . "</label></div>\n";
            } else {
                echo "<div class=\"r2\" id=\"divmodule$module_no\"><input type=\"checkbox\" name=\"dummymodule$module_no\" value=\"" . $module['id'] . "\" checked disabled><input type=\"checkbox\" name=\"module$module_no\" id=\"module$module_no\" style=\"display:none\" value=\"" . $module['idMod'] . "\" checked>&nbsp;<label for=\"module$module_no\">" . $module['id'] . ': ' . mb_substr($module['fullname'], 0, 60) . "</label></div>\n";
            }
        } else {
            echo "<div class=\"r1\" id=\"divmodule$module_no\"><input type=\"checkbox\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module['idMod'] . "\">&nbsp;<label for=\"module$module_no\">" . $module['id'] . ': ' . mb_substr($module['fullname'], 0, 60) . "</label></div>\n";
        }
        $module_no++;
        $old_school = $module['school'];
        $old_schoolcode = $module['schoolcode'];
    }

      echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"$module_no\" /></div>\n</td></tr>";
    ?>
    </table>
  <div style="text-align:center; padding-top:10px"><input type="submit" class="ok" name="Submit" value="<?php echo $string['save']; ?>"><input type="button" name="home" class="cancel" value="<?php echo $string['cancel']; ?>" /></div>
</td>
</tr>
</table>

<?php
  echo '<input type="hidden" name="created" value="' . $created . '" />';
  echo '<input type="hidden" name="owner" value="' . $owner . '" />';
?>
</form>
<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
$mysqli->close();
?>

</body>
</html>
