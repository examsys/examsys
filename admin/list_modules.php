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

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>
    <title><?php echo page::title('Rog&#333;: ' . $string['modules']); ?></title>

    <link rel="stylesheet" type="text/css" href="../css/body.css"/>
    <link rel="stylesheet" type="text/css" href="../css/header.css"/>
    <link rel="stylesheet" type="text/css" href="../css/submenu.css"/>
    <link rel="stylesheet" type="text/css" href="../css/list.css"/>
    <style>
        th a {
            color: black !important
        }
    </style>

    <script id="rogoconfig" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
    <script src='../js/require.js'></script>
    <script src='../js/main.min.js'></script>
</head>

<body>
<?php
require '../include/admin_module_options.inc';
require '../include/toprightmenu.inc';

echo draw_toprightmenu(233);

$result = $mysqli->prepare('SELECT modules.id, moduleid, fullname, schools.code, school, active, modules.externalid FROM modules LEFT JOIN schools ON modules.schoolid = schools.id WHERE mod_deleted IS NULL');
$result->execute();
$result->bind_result($id, $moduleid, $fullname, $schoolcode, $school, $active, $externalid);
$result->store_result();
?>
<div id="content">

    <div class="head_title">
        <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon"/>
        <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img
                    src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-"/><a
                    href="./index.php"><?php echo $string['administrativetools'] ?></a></div>
        <div class="page_title"><?php echo $string['modules'] ?> (<?php echo $result->num_rows; ?>)</div>
    </div>

    <table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="2" border="0" style="width:100%">
        <thead>
        <tr>
            <th class="col" style="width:15%"><?php echo $string['moduleid'] ?></th>
            <th class="col" style="width:30%"><?php echo $string['name'] ?></th>
            <th class="col" style="width:30%"><?php echo $string['school'] ?></th>
            <th class="col" style="width:15%"><?php echo $string['active'] ?></th>
        </tr>
        </thead>

        <tbody>
        <?php
        while ($result->fetch()) {
            if ($school == '') {
                $school = '<span style="color:#808080">unknown</span>';
            }
            if ($active == 1) {
                $tmp_active = $string['yes'];
                $class = 'l';
            } else {
                $tmp_active = $string['no'];
                $class = 'l grey';
            }
            $syncprev = module_utils::check_sync_previous_year($id);
            $yearutils = new \yearutils($configObject->db);
            $session = $yearutils->get_current_session(module_utils::getAcademicYearStart($id));
            echo "<tr data-session=\"$session\" data-syncprevious=\"$syncprev\" data-externalid=\"$externalid\" class=\"$class\" id=\"$id\"><td>$moduleid</td><td>$fullname</td><td>$schoolcode $school</td><td>$tmp_active</td></tr>\n";
        }
        $result->close();
        $mysqli->close();
        ?>
        </tbody>
    </table>
</div>
<?php
// JS utils dataset.
$render = new render($configObject);
$miscdataset['name'] = 'jsutils';
$miscdataset['attributes']['xls'] = json_encode($string);
$render->render($miscdataset, array(), 'dataset.html');
?>
<script src="../js/moduleadmininit.min.js"></script>
</body>
</html>
