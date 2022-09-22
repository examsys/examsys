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
 * Edit mdoule
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

check_var('moduleid', 'GET', true, false, false);
$moduleid = param::required('moduleid', param::INT, param::FETCH_GET);
$modulecode = param::required('modulecode', param::TEXT, param::FETCH_POST);
$oldmodulecode = param::required('old_modulecode', param::TEXT, param::FETCH_POST);
$module = module_utils::get_full_details_by_ID($moduleid, $mysqli);

if ($module === false) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$moduleid_in_use = false;
if ($modulecode != $oldmodulecode) {
    // Check for unique moduleid
    $moduleid_in_use = module_utils::module_exists($modulecode, $mysqli);
}
if ($moduleid_in_use == false) {
    $module['active'] = param::optional('active', false, param::BOOLEAN, param::FETCH_POST);
    $module['selfenroll'] = param::optional('selfenroll', false, param::BOOLEAN, param::FETCH_POST);
    $module['neg_marking'] = param::optional('neg_marking', false, param::BOOLEAN, param::FETCH_POST);


    $module['checklist'] = '';
    if (param::optional('peer', false, param::BOOLEAN, param::FETCH_POST)) {
        $module['checklist'] .= ',peer';
    }
    if (param::optional('external', false, param::BOOLEAN, param::FETCH_POST)) {
        $module['checklist'] .= ',external';
    }
    if (param::optional('stdset', false, param::BOOLEAN, param::FETCH_POST)) {
        $module['checklist'] .= ',stdset';
    }
    if (param::optional('mapping', false, param::BOOLEAN, param::FETCH_POST)) {
        $module['checklist'] .= ',mapping';
    }
    if ($module['checklist'] != '') {
        $module['checklist'] = mb_substr($module['checklist'], 1);
    }


    // Update the properties of the module.
    $module['moduleid'] = $modulecode;
    $module['fullname'] = param::optional('fullname', '', param::TEXT, param::FETCH_POST);
    $module['timed_exams'] = param::optional('timed_exams', false, param::BOOLEAN, param::FETCH_POST);
    $module['exam_q_feedback'] = param::optional('exam_q_feedback', false, param::BOOLEAN, param::FETCH_POST);
    $module['add_team_members'] = param::optional('add_team_members', false, param::BOOLEAN, param::FETCH_POST);
    $module['syncpreviousyear'] = param::optional('syncpreviousyear', false, param::BOOLEAN, param::FETCH_POST);

    $vle_data = param::optional('vle_api', '', param::TEXT, param::FETCH_POST);
    if ($vle_data == '') {
        $module['map_level'] = 0;
        $module['vle_api'] = '';
    } else {
        $vle_parts = explode('~', $vle_data);
        $module['vle_api'] = $vle_parts[0];
        $module['map_level'] = $vle_parts[1];
    }

    $module['sms'] = param::optional('sms_api', '', param::TEXT, param::FETCH_POST);
    $module['academic_year_start']  = param::optional('academic_year_start', '07/01', param::TEXT, param::FETCH_POST);
    $module['schoolid'] = param::optional('schoolid', '', param::INT, param::FETCH_POST);
    $module['ebel_grid_template'] = param::optional('ebel_grid_template', null, param::TEXT, param::FETCH_POST);
    $module['externalid'] = check_var('externalid', 'POST', false, false, true);
    module_utils::update_module_by_code($oldmodulecode, $module, $mysqli);

    $mysqli->close();

    echo json_encode('SUCCESS');
    exit();
}
echo json_encode($modulecode);
