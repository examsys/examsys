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
 * Add lab
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/sysadmin_auth.inc';
require '../include/errors.php';

$bad_addresses = 0;

// Sanitize inputs
$lab_name = param::optional('lab_name', null, param::TEXT, param::FETCH_POST);
$campus = param::optional('campus', null, param::INT, param::FETCH_POST);
$building = param::optional('building', null, param::TEXT, param::FETCH_POST);
$room_no = param::optional('room_no', null, param::TEXT, param::FETCH_POST);
$low_bandwidth = param::optional('low_bandwidth', 0, param::INT, param::FETCH_POST);
$timetabling = param::optional('timetabling', null, param::TEXT, param::FETCH_POST);
$it_support = param::optional('it_support', null, param::TEXT, param::FETCH_POST);
$plagarism = param::optional('plagarism', null, param::TEXT, param::FETCH_POST);

// We need to process the text list of addresses into an array.
$raw_addresses = param::optional('addresses', null, param::RAW, param::FETCH_POST);
// Split up the addresses based on any of the major OS line ending types.
$split_addresses = preg_split('#\r\n|\r|\n#', trim($raw_addresses));
// Make sure we only have unique addresses.
$addresses = array_unique($split_addresses);

$labFactory = new LabFactory($mysqli);
$hostname_lookup = $configObject->get_setting('core', 'system_hostname_lookup');
if ($hostname_lookup) {
    $test_re = '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/';
} else {
    $test_re = '/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/';
}

$ipInvalid = array();
$ipInUse = array();

foreach ($addresses as $address) {
    $address = trim($address);
    if (0 === preg_match($test_re, $address)) {
        $bad_addresses++;
        $ipInvalid[] = $address;
    } elseif ($labFactory->get_lab_from_address($address)) {
        $bad_addresses++;
        $ipInUse[] = $address;
    }
}

if ($bad_addresses > 0) {
    echo json_encode(array('INVALID', $ipInvalid, $ipInUse));
    exit();
} else {
    // Insert into Lab table.
    $result = $mysqli->prepare('INSERT INTO labs (name, campus, building, room_no, timetabling, it_support, plagarism) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $result->bind_param('sisssss', $lab_name, $campus, $building, $room_no, $timetabling, $it_support, $plagarism);
    $result->execute();
    $labID = $mysqli->insert_id;
    $result->close();

    if ($mysqli->errno != 0) {
        echo json_encode(array('ERROR', $mysqli->errno));
        exit();
    }

    foreach ($addresses as $address) { // Insert the new IP addresses.
        $address = trim($address);
        if ($hostname_lookup) {
            $hostname = $address;
        } else {
            $hostname = gethostbyaddr($address);
        }

        $result = $mysqli->prepare('INSERT INTO client_identifiers (lab, address, hostname, low_bandwidth) VALUES (?, ?, ?, ?)');
        $result->bind_param('issi', $labID, $address, $hostname, $low_bandwidth);
        $result->execute();
        $result->close();
    }
    echo json_encode(array('OK', $labID));
}
