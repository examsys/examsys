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
require '../include/toprightmenu.inc';

$current_year = check_var('calyear', 'GET', true, false, true);
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
);
$lang['title'] .= ': ' . $current_year . '/' . (mb_substr($current_year, 2, 2) + 1);
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
$render->render_admin_options('', '', $string, $toprightmenu, 'admin/no_sidebar.html');
$render->render_admin_content($breadcrumb, $lang);
$total_paper_no = 0;
$total_paper_unused = 0;
$total_student_no = 0;
$month_paper_no = 0;
$month_student_no = 0;
$month_min = 99999;
$month_max = 0;
$old_month = '';
$month_papers_unused = 0;
$distinct_users = array();
$stats = new Statistics();

// Get summative papers running in academic year.
$papers = $stats->getSummativePapers($current_year);
$renderdata = array();
$labdata = array();
foreach ($papers as $pid => $data) {
    $labdata[] = $data['labs'];
    $paper_count = 0;
    // Get distinct user count for a paper.
    $users = $stats->getStudentCount($pid, $data['start'], $data['end']);
    $user_no = count($users);
    if ($user_no == 0) {
        $month_papers_unused++;
        $total_paper_unused++;
    } else {
        $distinct_users += $users;
        $paper_count += $user_no;
    }

    if ($old_month != $data['month']) {
        if ($old_month != '') {
            $renderdata['months'][] = $stats->generateMonth(
                $old_month,
                $month_paper_no,
                $month_papers_unused,
                $month_student_no,
                $month_min,
                $month_max,
                $current_year
            );
        }
        $month_paper_no = 0;
        $month_student_no = 0;
        $month_min = 99999;
        $month_max = 0;
        $month_papers_unused = 0;
    }

    if ($paper_count > 0) {
        $total_paper_no++;
        $total_student_no += $paper_count;
        $month_paper_no++;
        $month_student_no += $paper_count;
        if ($paper_count < $month_min) {
            $month_min = $paper_count;
        }
        if ($paper_count > $month_max) {
            $month_max = $paper_count;
        }
    }
    $old_month = $data['month'];
}

$renderdata['months'][] = $stats->generateMonth($old_month, $month_paper_no, $month_papers_unused, $month_student_no, $month_min, $month_max, $current_year);
$renderdata['totalpaperno'] = number_format($total_paper_no);
$renderdata['totalpaperunused'] = number_format($total_paper_unused);
$renderdata['totalstudentno'] = number_format($total_student_no);
$renderdata['uniquestudents'] = sprintf($string['uniquestudents'], number_format(count($distinct_users)));
$renderdata['labs'] = $stats->generateLabStats($labdata);
$stats->renderStatsHeader($current_year);
$stats->renderSummativeSummary($renderdata);
$render->render_admin_footer();
