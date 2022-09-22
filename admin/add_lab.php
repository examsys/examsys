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
require '../include/errors.php';

$bad_addresses = array();
$submit = param::optional('submit', null, param::TEXT, param::FETCH_POST);

// Sanitize inputs
$lab_name = param::optional('lab_name', null, param::TEXT, param::FETCH_POST);
$campus = param::optional('campus', null, param::INT, param::FETCH_POST);
$building = param::optional('building', null, param::TEXT, param::FETCH_POST);
$room_no = param::optional('room_no', null, param::TEXT, param::FETCH_POST);
$low_bandwidth = param::optional('low_bandwidth', 0, param::INT, param::FETCH_POST);
$timetabling = param::optional('timetabling', null, param::TEXT, param::FETCH_POST);
$it_support = param::optional('it_support', null, param::TEXT, param::FETCH_POST);
$plagarism = param::optional('plagarism', null, param::TEXT, param::FETCH_POST);
$addresses = explode(PHP_EOL, trim(param::optional('addresses', null, param::TEXT, param::FETCH_POST)));

$campusobj = new campus($mysqli);
$campuses = $campusobj->get_all_campus_details();

if (null === $campus) {
    foreach ($campuses as $key => $campusarray) {
        if ($campusarray['isdefault']) {
            $campus = $key;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
        <title><?php echo $string['createnewlab']; ?></title>
        <link rel="stylesheet" type="text/css" href="../css/body.css" />
        <link rel="stylesheet" type="text/css" href="../css/header.css" />
        <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
        <link rel="stylesheet" type="text/css" href="../css/lab.css" />

        <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
        <script src='../js/require.js'></script>
        <script src='../js/main.min.js'></script>
        <script src="../js/labinit.min.js"></script>

    </head>

    <body>
        <?php
        require '../include/lab_options.inc';
        require '../include/toprightmenu.inc';

        echo draw_toprightmenu(233);
        ?>
        <div id="content">
            <form id="theform" action="" method="post" autocomplete="off">
                <div class="head_title">
                    <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" />
                    <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./list_labs.php"><?php echo $string['computerlabs'] ?></a></div>
                    <div class="page_title"><?php echo $string['createnewlab'] ?></div>
                </div>

                <div class="invalidlab"></div>
                <div class="inuselab"></div>
                <br />

                <table cellpadding="2" cellspacing="0" border="0" style="font-size:100%; margin-left:10px; margin-right:10px">
                    <tr>
                        <td style="vertical-align:top; width:200px">
                            <div><?php echo $string['ipaddresses'] ?></div>
                            <textarea cols="20" rows="28" style="width:200px; height:590px" name="addresses" id="addresses" required><?= implode(PHP_EOL, $addresses); ?></textarea>
                        </td>
                        <td style="width:50px"></td>
                        <td style="vertical-align:top">
                            <div><?php echo $string['name'] ?></div>
                            <div><input type="text" size="40" maxlength="255" name="lab_name" id="lab_name" value="<?= $lab_name; ?>" required /></div>
                            <br />

                            <div><?= $string['campus'] ?></div>
                            <div>
                                <select name="campus">
                                    <?php foreach ($campuses as $key => $campusarray) : ?>
                                        <option value="<?= $key; ?>"<?php if ($campus == $key) :
                                            ?> selected<?php
                                                       endif; ?>><?= $campusarray['campusname']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <br />

                            <div><?php echo $string['building'] ?></div>
                            <div><input type="text" size="40" maxlength="255" name="building" value="<?= $building; ?>" required /></div>
                            <br />

                            <div><?php echo $string['roomnumber'] ?></div>
                            <div><input type="text" size="10" maxlength="255" name="room_no" value="<?= $room_no; ?>" required /></div>
                            <br />

                            <div><?php echo $string['bandwidth'] ?></div>
                            <div>
                                <input type="radio" name="low_bandwidth" value="1"<?php if ($low_bandwidth) :
                                    ?> checked<?php
                                                                                  endif; ?> /><?php echo $string['low'] ?>
                                &nbsp;&nbsp;&nbsp;
                                <input type="radio" name="low_bandwidth" value="0"<?php if (!$low_bandwidth) :
                                    ?> checked<?php
                                                                                  endif; ?> /><?php echo $string['high'] ?>
                            </div>
                            <br />

                            <div><?php echo $string['timetabling'] ?></div>
                            <div>
                                <textarea name="timetabling" rows="3" cols="100"><?= $timetabling; ?></textarea>
                            </div>
                            <br />

                            <div><?php echo $string['itsupport'] ?></div>
                            <div>
                                <textarea name="it_support" rows="3" cols="100"><?= $it_support; ?></textarea>
                            </div>
                            <br />

                            <div><?php echo $string['plagarism'] ?></div>
                            <div>
                                <textarea name="plagarism" rows="3" cols="100"><?= $plagarism; ?></textarea>
                            </div>
                            <br />
                            <br />

                            <input type="submit" name="submit" value="<?php echo $string['save'] ?>" class="ok" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
        // JS utils dataset.
        $render = new render($configObject);
        $miscdataset['name'] = 'dataset';
        $miscdataset['attributes']['posturl'] = 'do_add_lab.php';
        $render->render($miscdataset, array(), 'dataset.html');// JS utils dataset.
        $jsdataset['name'] = 'jsutils';
        $jsdataset['attributes']['xls'] = json_encode($string);
        $render->render($jsdataset, array(), 'dataset.html');
        ?>
    </body>
</html>
