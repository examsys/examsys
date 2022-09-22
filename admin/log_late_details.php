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
  require '../include/sidebar_menu.inc';
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>ExamSys: <?php echo $string['loglatedetails'] ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <style type="text/css">
    .icon {padding-left:10px}
  </style>

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/loglateinit.min.js"></script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';

    echo draw_toprightmenu();
?>

<div id="content">

<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a></div>
  <div class="page_title"><?php echo $string['loglatedetails'] ?></div>
</div>

<table class="header" id="maindata">
<thead>
<tr>
<th></th>
<th class="col"><?php echo $string['papertitle'] ?></th>
<th class="col" style="width:50%"><?php echo $string['studentslate'] ?></th>
</tr>
</thead>
<tbody>
<?php
    $icons = array('formative_16.gif', 'progress_16.gif', 'summative_16.gif');
    $data = array();

    $sql = "
    SELECT DISTINCT 
        paper_type, paper_title, paperID, lm.userID 
    FROM 
        log_metadata lm
        JOIN log_late ll ON ll.metadataID = lm.id
        JOIN properties p ON lm.paperID = p.property_id
        JOIN users u ON lm.userID = u.id
        JOIN user_roles ur ON u.id = ur.userid
        JOIN roles r ON r.id = ur.roleid
    WHERE
        r.name IN ('Student', 'graduate')
    ";
    $result = $mysqli->prepare($sql);
    $result->execute();
    $result->bind_result($paper_type, $paper_title, $paperID, $uID);
while ($result->fetch()) {
    $data[$paperID]['paper_title'] = $paper_title;
    $data[$paperID]['paper_type'] = $paper_type;
    $data[$paperID]['students'][] = $uID;
}
  $result->close();

foreach ($data as $paperID => $row) {
    echo "<tr><td class=\"icon\"><a href=\"../paper/details.php?paperID=$paperID\"><img src=\"../artwork/" . $icons[$row['paper_type']] . "\" width=\"16\" height=\"16\" alt=\"\" /></a></td><td><a href=\"../paper/details.php?paperID=$paperID\">" . $row['paper_title'] . '</a></td><td>' . count($row['students']) . '</td></tr>';
}
?>
</tbody>
</table>

</div>
</body>
</html>
