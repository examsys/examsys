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
require '../include/errors.php';

define('IP_INVALID', 1);
define('IP_IN_USE', 2);

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

if ($submit) { // Validate addresses
    $labFactory = new LabFactory($mysqli);
    $test_re = $configObject->get('cfg_client_lookup') == 'name' ?
            '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/' :
            '/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/';
    foreach ($addresses as $address) {
        $address = trim($address);
        if (0 === preg_match($test_re, $address)) {
            $bad_addresses[$address] = IP_INVALID;
        } elseif ($labFactory->get_lab_from_address($address)) {
            $bad_addresses[$address] = IP_IN_USE;
        }
    }

    if (count($bad_addresses) === 0) { // Insert into Lab table.
        $result = $mysqli->prepare("INSERT INTO labs (name, campus, building, room_no, timetabling, it_support, plagarism) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $result->bind_param('sisssss', $lab_name, $campus, $building, $room_no, $timetabling, $it_support, $plagarism);
        $result->execute();
        $labID = $mysqli->insert_id;
        $result->close();

        foreach ($addresses as $address) { // Insert the new IP addresses.
            $address = trim($address);
            $hostname = $configObject->get('cfg_client_lookup') == 'name' ? $address : gethostbyaddr($address);

            $result = $mysqli->prepare("INSERT INTO client_identifiers (lab, address, hostname, low_bandwidth) VALUES (?, ?, ?, ?)");
            $result->bind_param('issi', $labID, $address, $hostname, $low_bandwidth);
            $result->execute();
            $result->close();
        }

        header("location: lab_details.php?labID={$labID}"); // Jump into new Lab page
        exit;
    }
}

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

        <?php echo $configObject->get('cfg_js_root') ?>
        <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
        <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
        <script type="text/javascript" src="../js/toprightmenu.js"></script>
        <script>
            $(function () {
                $('#theform').validate({
                    errorClass: 'errfield',
                    errorPlacement: function (error, element) {
                        return true;
                    }
                });
                $('form').removeAttr('novalidate');
            });
        </script>
    </head>

    <body>
        <?php
        require '../include/lab_options.inc';
        require '../include/toprightmenu.inc';

        echo draw_toprightmenu(233);
        ?>
        <div id="content">
            <form id="theform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
                <div class="head_title">
                    <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" />
                    <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./list_labs.php"><?php echo $string['computerlabs'] ?></a></div>
                    <div class="page_title"><?php echo $string['createnewlab'] ?></div>
                </div>

                <?php if (count($bad_addresses) > 0) : // Show error messages ?>
                    <?php
                    $ipInvalid = array_filter($bad_addresses, function($value) {
                        return $value === IP_INVALID;
                    });
                    $ipInUse = array_filter($bad_addresses, function($value) {
                        return $value === IP_IN_USE;
                    });
                    ?>
                    <div style="color: #f00; font-weight: bold; margin-left: 10px;">
                        <?php if (count($ipInvalid) > 0) : ?>
                            <p><?= sprintf($string['badaddressesinvalid'], implode(', ', array_keys($ipInvalid))); ?></p>
                        <?php endif; ?>
                        <?php if (count($ipInUse) > 0) : ?>
                            <p><?= sprintf($string['badaddressesinuse'], implode(', ', array_keys($ipInUse))); ?></p>
                        <?php endif; ?>
                    </div>
                    <br />
                <?php endif; ?>

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
                                        <option value="<?= $key; ?>"<?php if ($campus == $key) : ?> selected<?php endif; ?>><?= $campusarray['campusname']; ?></option>
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
                                <input type="radio" name="low_bandwidth" value="1"<?php if ($low_bandwidth) : ?> checked<?php endif; ?> /><?php echo $string['low'] ?>
                                &nbsp;&nbsp;&nbsp;
                                <input type="radio" name="low_bandwidth" value="0"<?php if (!$low_bandwidth) : ?> checked<?php endif; ?> /><?php echo $string['high'] ?>
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
    </body>
</html>
