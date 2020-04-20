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

define('AJAX_REQUEST', true);

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$assessment = new assessment($mysqli, $configObject);
$central_mgmt = $configObject->get_setting('core', 'cfg_summative_mgmt');
$paper_name = check_var('paper_name', 'POST', true, false, true);
$paper_type = check_var('paper_type', 'POST', true, false, true);
$paper_owner = check_var('paper_owner', 'POST', true, false, true);
$session = $_POST['session'];
if (empty($session)) {
    $yearutils = new yearutils($mysqli);
    $session = $yearutils->get_current_session();
}

$papertype = $assessment->get_type_value($paper_type);
if ($papertype === false) {
    $errorline = __LINE__ - 2;
    $msg = __FILE__ . ' Line: ' . $errorline . ' Error:' . $string['papertypenotfound'];
    echo json_encode(array('ERROR',$notice->ajax_notice("$paper_type" . $string['papertypenotfound'], $msg)));
    exit();
}
// Process the posted modules
$modules = array();
$first = true;
for ($i = 0; $i < $_POST['module_no']; $i++) {
    if (isset($_POST['mod' . $i])) {
        if ($first == true) {
            $first_module = $_POST['mod' . $i];
            $first = false;
        }
        $modules[] = $_POST['mod' . $i];
    }
}

if (isset($_POST['timezone'])) {
    $timezone = $_POST['timezone'];
} else {
    $timezone = $configObject->get('cfg_timezone');
}
if ($central_mgmt and $papertype == $assessment::TYPE_SUMMATIVE) {
    $duration = 0;
    if (isset($_POST['duration_hours'])) {
        $duration += ($_POST['duration_hours'] * 60);
    }
    if (isset($_POST['duration_mins'])) {
        $duration += $_POST['duration_mins'];
    }

    $start_date = null;
    $end_date = null;
} else {
    $duration = null;
    if ((bool) date('L', strtotime($_POST['fyear'] . '-01-01'))) {
        $leap = true;
    } else {
        $leap = false;
    }

    if ($leap == true and $_POST['fmonth'] == '02' and ($_POST['fday'] == '30' or $_POST['fday'] == '31')) {
        $_POST['fday'] = '29';
    }
    if ($leap == false and $_POST['fmonth'] == '02' and ($_POST['fday'] == '29' or $_POST['fday'] == '30' or $_POST['fday'] == '31')) {
        $_POST['fday'] = '28';
    }
    if (($_POST['fmonth'] == '04' or $_POST['fmonth'] == '06' or $_POST['fmonth'] == '09' or $_POST['fmonth'] == '11') and $_POST['fday'] == '31') {
        $_POST['fday'] = '30';
    }

    $start_date = $_POST['fyear'] . $_POST['fmonth'] . $_POST['fday'] . $_POST['ftime'];


    if ($leap == true and $_POST['tmonth'] == '02' and ($_POST['tday'] == '30' or $_POST['tday'] == '31')) {
        $_POST['tday'] = '29';
    }
    if ($leap == false and $_POST['tmonth'] == '02' and ($_POST['tday'] == '29' or $_POST['tday'] == '30' or $_POST['tday'] == '31')) {
        $_POST['tday'] = '28';
    }
    if (($_POST['tmonth'] == '04' or $_POST['tmonth'] == '06' or $_POST['tmonth'] == '09' or $_POST['tmonth'] == '11') and $_POST['tday'] == '31') {
        $_POST['tday'] = '30';
    }

    $end_date = $_POST['tyear'] . $_POST['tmonth'] . $_POST['tday'] . $_POST['ttime'];
}

try {
    $remote = 0;
    if ($papertype == $assessment::TYPE_SUMMATIVE) {
        $remote = check_var('remote_summative', 'POST', false, false, true);
        if (is_null($remote)) {
            $remote = 0;
        } else {
            $remote = 1;
        }
    }

    $property_id = $assessment->create($paper_name, $papertype, $paper_owner, $start_date, $end_date, '', $duration, $session, $modules, $timezone, null, null, $remote);

    if ($central_mgmt and $papertype == $assessment::TYPE_SUMMATIVE) {
        if (isset($_POST['barriers_needed'])) {
            $barriers_needed = 1;
        } else {
            $barriers_needed = 0;
        }
        $assessment->schedule($property_id, $_POST['period'], $barriers_needed, $_POST['cohort_size'], $_POST['notes'], $_POST['sittings'], $_POST['campus']);
    }
} catch (Exception $e) {
    $log = new logger($mysqli);
    // Log warning to system.
    $errorstring = $e->getMessage();
    $errorline = __LINE__ - 14;
    $log->record_application_warning($paper_owner, 'Paper Creation', $errorstring, $_SERVER['PHP_SELF'], $errorline);
    $msg = $errorline . ' Error code: ' . $e->getCode() . ' - ' . $errorstring;
    echo json_encode(array(
        'ERROR',
        $notice->ajax_notice($string['errorcreatingpaper'], $msg)
    ));
    exit();
}

if ($property_id !== false) {
    echo json_encode(array(
        'SUCCESS',
        $property_id,
        $first_module,
        $_POST['folder']
    ));
} else {
    $log = new logger($mysqli);
    // Log warning to system.
    $errorline = __LINE__ - 33;
    $log->record_application_warning($paper_owner, 'Paper Creation', $string['dbinsertfailed'], $_SERVER['PHP_SELF'], $errorline);
    echo json_encode(array(
        'ERROR',
        $notice->ajax_notice($string['errorcreatingpaper'], $string['dbinsertfailed'])
    ));
}
