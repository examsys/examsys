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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

  require '../include/sysadmin_auth.inc';
  require '../include/sidebar_menu.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['smsimportsummary'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  
  <script src="../js/jquery-1.6.1.min.js" type="text/javascript"></script>
  <script src="../js/staff_help.js" type="text/javascript"></script>
</head>
<body>
<?php
  require '../include/admin_options.inc';
?>

<div id="content" class="content" style="font-size:80%">
<table class="header">
<tr>
<th colspan="4"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['smsimportsummary']; ?></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
</tr>
<tr><th><div style="padding-left:10px"><?php echo $string['date']; ?></div></th><th class="vert_div">&nbsp;<?php echo $string['modules']; ?></th><th class="vert_div">&nbsp;<?php echo $string['enroled']; ?></th><th class="vert_div">&nbsp;<?php echo $string['deleted']; ?></th><th style="width:50%">&nbsp;</th></tr>
<tr><th colspan="5" class="bevel"></th></tr>

<?php
  $id = 1;
  $result = $mysqli->prepare("SELECT DATE_FORMAT(updated,'%Y%m%d'), DATE_FORMAT(updated,'%d/%m/%Y'), COUNT(id), SUM(enrolements), SUM(deletions) FROM sms_imports GROUP BY updated ORDER BY updated DESC");
  $result->execute();
  $result->store_result();
  $result->bind_result($updated, $display_updated, $module_no, $enrolement_no, $deletion_no);
  while ($result->fetch()) {
    echo "<tr id=\"$id\" onclick=\"window.location='sms_import_detail.php?day=$updated';\" class=\"l\"><td style=\"padding-left:10px\">$display_updated</td><td class=\"no\">$module_no</td><td class=\"no\">$enrolement_no</td><td class=\"no\">$deletion_no</td><td>&nbsp;</td></tr>\n";
    $id++;
  }
?>
</table>
</div>

</body>
</html>
