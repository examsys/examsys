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
 * Academic session administration.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 */

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

// Get all sessions.
$result = $mysqli->prepare('SELECT calendar_year, academic_year, cal_status, stat_status FROM academic_year WHERE deleted is NULL');
$result->execute();
$result->store_result();
$result->bind_result($calendar_year, $academic_year, $cal_status, $stat_status);
// Put sessions into array so we can close hte db connection.
$sessions = array();
while ($result->fetch()) {
    $sessions[$calendar_year] = array($academic_year, $cal_status, $stat_status);
}
$num_sessions = count($sessions);
$result->close();
?>

<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['academicsessions']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/sessionsinit.min.js"></script>
</head>

<body>
<?php
    require '../include/academic_session_options.inc';
    require '../include/toprightmenu.inc';

    echo draw_toprightmenu(740);

?>
<div id="content">

<div class="head_title">
  <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" />
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a></div>
  <div class="page_title"><?php echo $string['academicsessions'] ?> (<?php echo $num_sessions ?>)</div>
</div>

<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="2" border="0" style="width:100%">
<thead>
<tr>
  <th class="col10"><?php echo $string['calendaryear'] ?></th>
  <th class="col"><?php echo $string['academicyear'] ?></th>
  <th class="col"><?php echo $string['calendarenabled'] ?></th>
  <th class="col"><?php echo $string['statisticsenabled'] ?></th>
</tr>
</thead>

<tbody>
<?php

if ($num_sessions > 0) {
    $yes = '<img src="../artwork/tick.gif" id="yes" />';
    $no = '<img src="../artwork/cross.gif" id="no" />';
    foreach ($sessions as $year => $info) {
        echo "<tr id=\"$year\" class=\"l\"><td>$year</td><td>$info[0]</td>"
        . '<td class="no" style="text-align:left">' . (($info[1] == 0) ? $no : $yes) . '</td>'
        . '<td class="no" style="text-align:left">' . (($info[2] == 0) ? $no : $yes) . '</td>'
        . "</tr>\n";
    }
} else {
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    echo '<tr><td colspan="4">' . $string['musthavesession'] . "</td></tr>\n";
}

?>
</tbody>
</table>
</div>

</body>
</html>

