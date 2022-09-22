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

require '../include/sysadmin_auth.inc';

$clear_all = param::optional('clear', null, param::TEXT, param::FETCH_GET);
$clear_a_log = param::optional('log_id', null, param::INT, param::FETCH_GET);
$logs = new access_denied_logs();
if (isset($clear_all)) {
    $logs->delete_access_denied_logs();
    header('Location: view_access_denied.php', true, 303);
    exit();
} elseif (isset($clear_a_log)) {
    $logs->delete_a_access_denied_log($clear_a_log);
    header('Location: view_access_denied.php', true, 303);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['deniedlogwarnings']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <style>
    .d {background-image: url('../artwork/access_denied_16.gif'); background-repeat:no-repeat; background-position: left center; padding-left:20px}
    .clearall{float: right;margin-top: -0.4em;color: #000;margin-right: 8%;font-size: 50%;text-decoration: none;padding: 0.5em;}
    a:hover.clearall, a:link.clearall, a:visited.clearall{text-decoration: none;}
  </style>

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/accessdeniedinit.min.js"></script>
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
  <div class="page_title"><?php echo $string['deniedlogwarnings'] ?><a href="?clear=all" id="clearall"><button class="clearall" ><?php echo $string['clear_all_button_text']; ?></button></a></div>
</div>

<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="2" border="0" style="width:100%">
<thead>
<tr>
  <th class="col" style="width:15%"><?php echo $string['date'] ?></th>
  <th class="col" style="width:15%"><?php echo $string['user'] ?></th>
  <th class="col" style="width:45%"><?php echo $string['url'] ?></th>
  <th class="col" style="width:15%"><?php echo $string['message'] ?></th>
</tr>
</thead>
<tbody>
<?php
$log_list = $logs->get_access_denied_logs();

foreach ($log_list as $log) {
    $tried_date = new DateTime();
    $tried_date->setTimestamp($log['tried']);

    echo '<tr class="l">
  <td class="d"><a href="?log_id=' . $log['id'] . '">' . $tried_date->format($configObject->get('cfg_long_datetime_php')) . '</a></td>
  <td><a href="../users/details.php?submit=Search&userID=' . $log['userID'] . '">' . $log['title'] . ' ' . $log['initials'] . ' ' . $log['surname'] . '</a></td>
  <td>' . $log['page'] . '</td>
  <td>' . $log['msg'] . "</td>
  </tr>\n";
}

?>
</tbody>
</table>
</div>
<?php
$render = new render($configObject);
$dataset['name'] = 'dataset';
$dataset['attributes']['datetime'] = $configObject->get('cfg_tablesorter_date_time');
$render->render($dataset, array(), 'dataset.html');
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
?>
</body>
</html>
