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
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package ExamSys
 */

require_once '../include/sysadmin_auth.inc';
require_once '../include/sidebar_menu.inc';

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['detailed_authentication_information']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    .sechead {background-color:#295AAD; color:white; text-align:left; font-weight:normal}
  </style>

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
</head>

<body>
<?php
    require '../include/toprightmenu.inc';
echo draw_toprightmenu();
?>
<div id="content">
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./system_info.php"><?php echo $string['System Information'] ?></a></div>
  <div class="page_title"><nobr><?php echo $string['detailed_authentication_information'] ?></nobr></div>
</div>
<?php
$authinfo = $authentication->version_info();
$plugin_no = count($authinfo->plugins);
echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" style=\"margin:10px\">\n";
echo '<tr><td class="sechead">' . $string['No'] . '</td><td class="sechead">' . $string['Name'] . '</td><td class="sechead">' . $string['Class'] . '</td><td class="sechead">' . $string['Version'] . '</td><td class="sechead" style="text-align:center">' . $string['Settings'] . '</td></tr>';
for (
    $i = 1; $i < $plugin_no; $i++
) {
    $settinginfo = '';
    foreach ($authinfo->plugins[$i]->settings as $setting => $value) {
        if ($settinginfo != '') {
            $settinginfo .= ', &nbsp; ';
        }
        if (is_array($value)) {
            $sub_info = '';
            foreach ($value as $sub_key => $sub_value) {
                if ($sub_info == '') {
                    $sub_info = "$sub_key = > $sub_value";
                } else {
                    $sub_info .= ", $sub_key = > $sub_value";
                }
            }
            $settinginfo .= $setting . "=array($sub_info)";
        } else {
            $settinginfo .= $setting . '=' . $value;
        }
    }
  
    echo '<tr><td>' . $authinfo->plugins[$i]->number . '.</td><td><nobr>' . $authinfo->plugins[$i]->name . '</nobr></td><td>' . $authinfo->plugins[$i]->classname . '</td><td>' . $authinfo->plugins[$i]->version . "</td><td>$settinginfo</td></tr>\n";
}
echo "</table>\n";
echo "<br />\n";
echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" style=\"margin:10px\">\n";
echo '<tr><td class="sechead">' . $string['Name'] . '</td><td class="sechead">' . $string['Function'] . '</td><td class="sechead">' . $string['Description'] . '</td><td class="sechead">' . $string['ID'] . '</td></tr>';
$old_function = '';
foreach ($authinfo->callbacks as $callback_name => $callback_details) {
    foreach ($callback_details as $callback) {
        if ($callback->functionname != $old_function) {
            echo '<tr><td colspan=4><hr></td></tr>';
        }
        echo '<tr><td>' . $callback_name . '&nbsp;</td><td>' . $callback->functionname . '&nbsp;</td><td>' . $callback->plugindescname . '&nbsp;</td><td>' . $callback->pluginconfigid . "&nbsp;</td></tr>\n";
        $old_function = $callback->functionname;
    }
}
echo '<tr><td colspan=4><hr></td></tr>';
echo "</table>\n";

?>
</table>
</div>

</body>
</html>
