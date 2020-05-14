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
require '../include/toprightmenu.inc';

$current_year = check_var('calyear', 'GET', true, false, true);
$current_month = check_var('month', 'GET', true, false, true);

$render = new render($configObject);
$toprightmenu = draw_toprightmenu(744);
$lang['title'] = $string['summativeexamstats'];
$additionaljs = '<script type="text/javascript" src="../js/statisticsinit.min.js"></script>';
$addtionalcss = '<link rel="stylesheet" type="text/css" href="../../css/statistics.css"/>
<link rel="stylesheet" type="text/css" href="../../css/tabs.css"/>';
$breadcrumb = array(
    $string['home'] => '../index.php',
    $string['administrativetools'] => '../admin/index.php',
    $string['statistics'] => 'index.php',
    $string['summativeexamstats'] => 'summative_stats.php?calyear=' . $current_year,
);
$stats = new Statistics();
$lang['title'] .= ': ' . $stats->getDisplayMonth($current_month) . ' ' . $current_year . '/' . (mb_substr($current_year, 2, 2) + 1);
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
$render->render_admin_options('', '', $string, $toprightmenu, 'admin/no_sidebar.html');
$render->render_admin_content($breadcrumb, $lang);
$total_student_no = 0;
$distinct_users = array();
// Get paper details.
$renderdata = array();
$renderdata['papers'] = array();
$details = $stats->getSummativePapersDetails($current_month, $current_year);
foreach ($details as $pid => $data) {
    // Get distinct user count for a paper.
    $stats = new Statistics();
    $users = $stats->getStudentCount($pid, $data['start'], $data['end']);
    $user_no = count($users);
    $distinct_users += $users;

    if ($user_no == 0) {
        $class = ' grey';
    } else {
        $class = '';
    }
    $renderdata['papers'][] = array(
            'link' => '../paper/details.php?paperID=' . $pid,
            'linklabel' => $data['title'],
            'class' => $class,
            'userno' => $user_no,
    );
    $total_student_no += $user_no;
}

$renderdata['totalstudentno'] = number_format($total_student_no);
$renderdata['uniquestudents'] = sprintf($string['uniquestudents'], number_format(count($distinct_users)));
$stats->renderStatsHeader($current_year, $current_month);
$stats->renderSummativeDetail($renderdata);
$render->render_admin_footer();
