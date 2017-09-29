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
  <style>
    .clearall{float: right;margin-top: -0.4em;color: #000;margin-right: 7%;font-size: 50%;text-decoration: none;padding: 0.3em;}
    a:hover.clearall, a:link.clearall, a:visited.clearall{text-decoration: none;}
  </style>

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

    function clearAll() {
      return confirm("<?php echo $string['confirm_clear_all_logs'];?>");
    }

    function clear_a_log() {
      return confirm("<?php echo $string['confirm_clear_a_log'];?>");
    }
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
  <div class="page_title"><?php echo $string['savefailattempts'] ?><a href="?clear=all" onclick="return clearAll()"><button class="clearall" ><?php echo $string['clear_all_button_text']; ?></button></a></div>
</div>

<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="2" border="0" style="width:100%">
<thead>
<tr>
  <th class="col"></td>
  <th class="col"><?php echo $string['student'] ?></th>
  <th class="col"><?php echo $string['paper'] ?></th>
  <th class="col"><?php echo $string['screen'] ?></th>
  <th class="col"><?php echo $string['client'] ?></th>
  <th class="col"><?php echo $string['datetime'] ?></th>
  <th class="col"><?php echo $string['status'] ?></th>
  <th class="col"><?php echo $string['request'] ?></th>
  <th class="col"><?php echo $string['response'] ?></th>
</tr>
</thead>
<tbody>
<?php

$logs = new save_fail_logs();
$log_list = $logs->get_save_fail_logs();
$clear_all = param::optional('clear', null, param::TEXT, param::FETCH_GET);
$clear_a_log = param::optional('log_id', null, param::INT, param::FETCH_GET);

if (isset($clear_all)) {
  $logs->delete_save_fail_logs();
  header( 'Location: list_save_fails.php' ) ;

} elseif (isset($clear_a_log)) {
  $logs->delete_a_save_fail_log($clear_a_log);
  header( 'Location: list_save_fails.php' ) ;

} else {
  foreach ($log_list as $log) {
    echo "<tr class=\"l\"><td><a href=\"?log_id=" . $log['id'] . "\" onclick=\"return clear_a_log()\"><img title=\"".$string['icon_msg']."\" alt=\"".$string['icon_msg']."\" src='../artwork/access_denied_16.gif'></a></td>
    <td>" . $log['title'] . " " . $log['initials'] . " " . $log['surname'] . "</td>
    <td><a href=\"../paper/details.php?paperID=" . $log['paperID'] . "\">" . $log['paper_title'] . "</a></td>
    <td>" . $log['screen'] . "</td>
    <td>" . $log['ipaddress'] . "</td>
    <td>" . $log['failed'] . "</td>
    <td>" . $log['status'] . "</td>
    <td>" . $log['request'] . "</td>
    <td>" . $log['response'] . "</td>
    </tr>\n";
  }
}
?>
</tbody>
</table>
</div>

</body>
</html>
