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

require '../include/sysadmin_auth.inc';
require '../include/sidebar_menu.inc';

function get_list($list, $db) {
  $html = '';
  if ($list != '') {
    $result = $db->prepare("SELECT id, title, surname, first_names FROM users WHERE username IN ('" . str_replace(",","','",$list) . "') ORDER BY surname, initials");
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $title, $surname, $first_names);
    while ($result->fetch()) {
      $html .= '<a href="../users/details.php?userID=' . $id . '">' . $title . ' ' . $surname . ', ' . $first_names . '</a><br />';
    }
  }
  
  return $html;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>SMS Update Summary<?php echo " $cfg_install_type"; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
  tr {vertical-align:top}
  .no {text-align:right}
  </style>
  <script type="text/javascript" src="../js/staff_help.js"></script>
</head>
<body>
<?php
  require '../include/admin_options.inc';
?>

<div id="content" class="content" style="font-size:80%">
<table class="header">
<tr>
<th colspan="6"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="sms_import_summary.php"><?php echo $string['smsimportsummary']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['smsimportson']; ?> <?php echo substr($_GET['day'],6,2) . '/' . substr($_GET['day'],4,2) . '/' . substr($_GET['day'],0,4); ?></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
</tr>
<tr><th><div style="padding-left:10px"><?php echo $string['moduleid']; ?></div></th><th><?php echo $string['enrolements']; ?></th><th><?php echo $string['enrolementdetails']; ?></th><th><?php echo $string['deletions']; ?></th><th><?php echo $string['deletiondetails']; ?></th><th><?php echo $string['importtype']; ?></th><th style="width:20%">&nbsp;</th></tr>
<tr><th colspan="7" class="bevel"></th></tr>

<?php
  $result = $mysqli->prepare("SELECT moduleid, enrolements, enrolement_details, deletions, deletion_details, import_type FROM sms_imports WHERE updated=? ORDER BY moduleid");
  $db = $mysqli;
  if ($db->error) {
    try {
      throw new Exception("0MySQL error $db->error <br> Query:<br> $query", $db->errno);
    } catch (Exception $e) {
      echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
      echo nl2br($e->getTraceAsString());
    }
  }

  $result->bind_param('s',$_GET['day']);
  $result->execute();
  $result->store_result();
  $result->bind_result($moduleid, $enrolements, $enrolement_details, $deletions, $deletion_details, $import_type);
  while ($result->fetch()) {
    echo "<tr><td style=\"padding-left:10px\"><a href=\"../folder/details.php?module=$moduleid\">$moduleid</a></td><td class=\"no\">$enrolements</td><td>" . get_list($enrolement_details, $mysqli) . "</td><td class=\"no\">$deletions</td><td>" . get_list($deletion_details, $mysqli) . "</td><td>" . $import_type . "</td><td></td></tr>\n";
  }

?>
</table>

</div>

</body>
</html>
