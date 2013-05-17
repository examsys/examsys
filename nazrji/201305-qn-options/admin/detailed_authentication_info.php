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
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package Rogō
 */

require_once '../include/sysadmin_auth.inc';
require_once '../include/sidebar_menu.inc';
require_once '../classes/networkutils.class.php';
require_once '../classes/dateutils.class.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['detailed_authentication_information']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .sechead {background-color:#EBF2F7; color:#00156E; border-bottom: 1px solid #CFDBEB}
  </style>
  
  <script type="text/javascript" src="../js/staff_help.js"></script>
</head>

<body>
<?php
include '../include/admin_options.inc';
?>
<div id="content" class="content">
<table class="header">
<tr>
<th colspan="4"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./system_info.php"><?php echo $string['System Information']; ?></a></div><div style="font-size:200%; margin-left:10px; font-weight:bold"><nobr><?php echo $string['detailed_authentication_information']; ?></nobr></div></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(240); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
</tr>
<tr><th colspan="5" class="bevel"></th></tr>
</table>
<?php
$authinfo = $authentication->version_info();

$plugin_no = count($authinfo->plugins);

echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" style=\"margin:10px\">\n";
echo "<tr><td class=\"sechead\">" . $string['No'] . "</td><td class=\"sechead\">" . $string['Name'] . "</td><td class=\"sechead\">" . $string['Class'] . "</td><td class=\"sechead\">" . $string['Version'] . "</td><td class=\"sechead\" style=\"text-align:center\">" . $string['Settings'] . "</td></tr>";
for ($i=1; $i<$plugin_no; $i++) {
  
  $settinginfo = '';
  foreach ($authinfo->plugins[$i]->settings as $setting => $value) {
    if ($settinginfo != '') $settinginfo .= ', &nbsp; ';
    $settinginfo .= $setting . '=' . $value ;
  }
  
  echo "<tr><td>" . $authinfo->plugins[$i]->number . ".</td><td><nobr>" . $authinfo->plugins[$i]->name . "</nobr></td><td>" . $authinfo->plugins[$i]->classname . "</td><td>" . $authinfo->plugins[$i]->version . "</td><td>$settinginfo</td></tr>\n";
}
echo "</table>\n";

echo "<br />\n";

echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" style=\"margin:10px\">\n";
echo "<tr><td class=\"sechead\">" . $string['Name'] . "</td><td class=\"sechead\">" . $string['Function'] . "</td><td class=\"sechead\">" . $string['Description'] . "</td><td class=\"sechead\">" . $string['ID'] . "</td></tr>";
$old_function='';
foreach ($authinfo->callbacks as $callback_name => $callback_details) {
  foreach ($callback_details as $callback) {
    if($callback->functionname!=$old_function) {
      echo "<tr><td colspan=4><hr></td></tr>";
    }
    echo "<tr><td>" . $callback_name . "&nbsp;</td><td>" . $callback->functionname . "&nbsp;</td><td>" . $callback->plugindescname . "&nbsp;</td><td>" . $callback->pluginconfigid . "&nbsp;</td></tr>\n";
    $old_function=$callback->functionname;
  }
}
echo "<tr><td colspan=4><hr></td></tr>";
echo "</table>\n";

?>
</table>
</div>

</body>
</html>
