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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
require '../include/sidebar_menu.inc';
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['savefailattempts'] . ' ' . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    $(function () {
      if ($("#maindata").find("tr").size() > 1) {
        $("#maindata").tablesorter({ 
          dateFormat: '<?php echo $configObject->get('cfg_tablesorter_date_time') ?>',
          sortList: [[4,1]] 
        });
      }

    });
  </script>
</head>
<body>
<?php
require '../include/toprightmenu.inc';

echo draw_toprightmenu();
?>

<div id="content">
  
<div class="head_title">
  <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" />
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a></div>
  <div class="page_title"><?php echo $string['savefailattempts'] ?></div>
</div>
  
<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="2" border="0" style="width:100%">
<thead>
<tr>
  <th class="col"><?php echo $string['student'] ?></th>
  <th class="col"><?php echo $string['paper'] ?></th>
  <th class="col"><?php echo $string['screen'] ?></th>
  <th class="col"><?php echo $string['client'] ?></th>
  <th class="col"><?php echo $string['datetime'] ?></th>
  <th class="col"><?php echo $string['status'] ?></th>
</tr>
</thead>
<tbody>
<?php
$result = $mysqli->prepare("SELECT surname, title, initials, userID, paperID, screen, ipaddress, FROM_UNIXTIME(failed, '%d/%m/%Y %H:%i:%s'), paper_title, status FROM save_fail_log, users, properties WHERE save_fail_log.userID = users.id AND save_fail_log.paperID = properties.property_id ORDER BY failed");
$result->execute();
$result->store_result();
$result->bind_result($surname, $title, $initials, $userID, $paperID, $screen, $ipaddress, $failed, $paper_title, $status);
while ($result->fetch()) {
  echo "<tr class=\"l\"><td>$title $initials $surname</td><td><a href=\"../paper/details.php?paperID=$paperID\">$paper_title</a></td><td>$screen</td><td>$ipaddress</td><td>$failed</td><td>$status</td></tr>\n";
}
?>
</tbody>
</table>
</div>

</body>
</html>
