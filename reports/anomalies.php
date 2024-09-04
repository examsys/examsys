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
 * Anomaly report.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @package core
 */

set_time_limit(0);

require '../include/staff_auth.inc';
require '../include/toprightmenu.inc';

$paperid = param::required('paperID', param::INT, param::FETCH_GET);
$module = param::optional('module', null, param::INT, param::FETCH_GET);
$folder = param::optional('folder', null, param::INT, param::FETCH_GET);
$startdate  = param::optional('startdate', null, param::INT, param::FETCH_GET);
$enddate    = param::optional('enddate', null, param::INT, param::FETCH_GET);
$studentsonly    = param::optional('studentsonly', 0, param::BOOLEAN, param::FETCH_GET);
$limit = param::optional('limit', Search::DEFAULT_LIMIT, param::INT, param::FETCH_GET);
$page = param::optional('page', 1, param::INT, param::FETCH_GET);

// If no start date provided default to the start of today.
if (is_null($startdate)) {
    $startdatetime = date_utils::getUTCDateTime('today', $configObject->get('cfg_timezone'));
    $startdate = $startdatetime->format('YmdHis');
}

// If no end date provided default to the start date + 1 day
if (is_null($enddate)) {
    $enddatetime = date_utils::rogoToTimestamp($startdate) + \date_utils::DAYSECS;
    $enddate = date('YmdHis', $enddatetime);
}

$render = new render($configObject);
$headerdata = array(
    'css' => array(
        '/css/body.css',
        '/css/header.css',
        '/css/warnings.css',
        '/css/list.css',
        '/css/reports.css',
    ),
    'scripts' => array(
        '/js/require.js',
        '/js/main.min.js',
    ),
);

$properties = PaperProperties::get_paper_properties_by_id($paperid, $mysqli, $string);

if (!Anomaly::anomalyDetectionEnabled($properties->get_paper_type())) {
    UserNotices::display_notice($string['anomalyaccessdenied'], $string['anomalyerrormessage'], '../artwork/access_denied.png', '#C00000');
    exit();
}

// initial link of breadcrumb
$links = array('/' => $string['home']);

if ($folder) {
    // links of parent folders
    $folderName = folder_utils::get_folder_name($folder, $mysqli);
    foreach (folder_utils::get_parent_list($folderName, $userObject, $mysqli) as $parentId => $parentName) {
        $href = '/folder/index.php?folder=' . $parentId;
        $links[$href] = $parentName;
    }

    // link of current folder
    $href = '/folder/index.php?folder=' . $folder;
    $links[$href] = false === mb_strpos($folderName, ';') ? $folderName : mb_substr($folderName, mb_strrpos($folderName, ';') + 1);
} else {
    if (is_null($module)) {
        // Get the modules from paper properties
        $modules = Paper_utils::get_modules($paperid, $mysqli);
        $module = key($modules);
    }
    // link to module
    $href = '/module/index.php?module=' . $module ;
    $links[$href] = module_utils::get_moduleid_from_id($module, $mysqli);

    // link to module
    $href = '/paper/type.php?module=' . $module . '&type=' . $properties->get_paper_type();
    $links[$href] = Paper_utils::type_to_name($properties->get_paper_type(), $string);
}

// link of current paper
$href = '/paper/details.php?paperID=' . $paperid;
$links[$href] = $properties->get_paper_title();
$href = '/reports/anomalies.php?paperID=' . $paperid . '&module=' . $module . '&folder=' . $folder;
$links[$href] = $string['title'];
$render->render($headerdata, $string, 'header.html');
$data['toprightmenu'] = draw_toprightmenu();
$data['current'] = count($links) > 0 ? array_pop($links) : '';
$data['links'] = $links;
$data['anomalies'] = Anomaly::getAnomalies(
    $paperid,
    $properties->get_paper_type(),
    date_utils::rogoToTimestamp($startdate),
    date_utils::rogoToTimestamp($enddate),
    $limit,
    $page,
    $studentsonly
);
$data['noanomalies'] = false;
$data['datetime'] = $configObject->get('cfg_tablesorter_date_time');
if (count($data['anomalies']['items']) == 0) {
    $data['noanomalies'] = true;
} else {
    // Create pages.
    if (isset($data['anomalies']['pages'])) {
        for ($i = 1; $i <= $data['anomalies']['pages']; $i++) {
            $url = Url::fromGlobals();
            $data['anomalies']['pageinfo'][$i]['url'] = $url->setQueryValue('page', $i);
            if ($i == $page) {
                $data['anomalies']['pageinfo'][$i]['selected'] = true;
            } else {
                $data['anomalies']['pageinfo'][$i]['selected'] = false;
            }
        }
    }
}
$data['userid'] = $userObject->get_user_ID();
$render->render($data, $string, '/reports/anomalies.html');
$data = [
    'scripts' => [
        '/js/anomalyreportinit.min.js',
    ],
];
$render->render($data, array(), 'footer.html');
