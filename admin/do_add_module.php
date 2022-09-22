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
 * Add module
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require_once '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$unique_moduleid = true;
$vle_api = '';
$map_level = 0;

// Check for unique moduleID
$modulecode = param::required('modulecode', param::TEXT, param::FETCH_POST);
$active = param::optional('active', false, param::BOOLEAN, param::FETCH_POST);
$selfenroll = param::optional('selfenroll', false, param::BOOLEAN, param::FETCH_POST);
$neg_marking = param::optional('neg_marking', false, param::BOOLEAN, param::FETCH_POST);
$fullname = param::optional('fullname', '', param::TEXT, param::FETCH_POST);
$peer = param::optional('peer', false, param::BOOLEAN, param::FETCH_POST);
$stdset = param::optional('stdset', false, param::BOOLEAN, param::FETCH_POST);
$external = param::optional('external', false, param::BOOLEAN, param::FETCH_POST);
$mapping = param::optional('mapping', false, param::BOOLEAN, param::FETCH_POST);
$schoolid = param::optional('schoolid', '', param::INT, param::FETCH_POST);
$postvleapi = param::optional('vle_api', '', param::TEXT, param::FETCH_POST);
$sms_api = param::optional('sms_api', '', param::TEXT, param::FETCH_POST);
$timed_exams = param::optional('timed_exams', false, param::BOOLEAN, param::FETCH_POST);
$exam_q_feedback = param::optional('exam_q_feedback', false, param::BOOLEAN, param::FETCH_POST);
$add_team_members = param::optional('add_team_members', false, param::BOOLEAN, param::FETCH_POST);
$academic_year_start = param::optional('academic_year_start', '07/01', param::TEXT, param::FETCH_POST);
$ebel_grid_template = param::optional('ebel_grid_template', null, param::TEXT, param::FETCH_POST);
$syncpreviousyear = param::optional('syncpreviousyear', false, param::BOOLEAN, param::FETCH_POST);

if (module_utils::module_exists($modulecode, $mysqli)) {
    $unique_moduleid = false;
}

if ($unique_moduleid == true) {
    $externalid = check_var('externalid', 'POST', false, false, true);
    if ($postvleapi == '') {
        $map_level = 0;
        $vle_api = '';
    } else {
        $vle_parts = explode('~', $postvleapi);
        $vle_api = $vle_parts[0];
        $map_level = $vle_parts[1];
    }

    $sms_import = 1;

    if (!module_utils::add_modules($modulecode, $fullname, $active, $schoolid, $vle_api, $sms_api, $selfenroll, $peer, $external, $stdset, $mapping, $neg_marking, $ebel_grid_template, $mysqli, $sms_import, $timed_exams, $exam_q_feedback, $add_team_members, $map_level, $academic_year_start, $externalid, $syncpreviousyear)) {
        echo json_encode('ERROR');
        exit();
    }

    // New sytle SMS enrolments.
    if (!is_null($externalid)) {
        $yearutils = new yearutils($mysqli);
        $session = $yearutils->get_current_session();
        $userObj = userObject::get_instance();
        $smsplugin_name = plugin_manager::get_plugin_type_enabled('plugin_sms');
        foreach ($smsplugin_name as $name) {
            $smspluginns = 'plugins\SMS\\' . $name . '\\' . $name;
            $smsplugin = new $smspluginns($userObj->get_user_ID());
            if ($sms_api === $smsplugin->get_name()) {
                if ($smsplugin->supports_module_import() !== false) {
                    $smsplugin->update_module_enrolments($externalid, $session);
                }
                break;
            }
        }
    }

    echo json_encode('SUCCESS');
    exit();
}
echo json_encode($modulecode);
