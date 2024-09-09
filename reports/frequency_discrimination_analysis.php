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
 * The Frequency & Discrimination Analysis is used to look at the number of students
 * that have selected each option in a question and how well it discriminates between
 * the upper and lower 27% of students.  These values help to identify how well the
 * question is working.
 *
 * @author    Simon Wilkinson
 * @author    Richard Aspden
 * @version   1.1
 * @copyright Copyright (c) 2014-2020 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require_once '../include/errors.php';

require_once '../plugins/questions/enhancedcalc/enhancedcalc.class.php';

//HTML5 part
require_once '../lang/' . $language . '/question/edit/hotspot_correct.php';
require_once '../lang/' . $language . '/question/edit/area.php';
require_once '../lang/' . $language . '/paper/hotspot_answer.php';
require_once '../lang/' . $language . '/paper/hotspot_question.php';
require_once '../lang/' . $language . '/paper/label_answer.php';

require_once '../include/frequency_discrimination_report_data.inc';
require_once '../include/frequency_discrimination_report_display.inc';

$paperID = check_var('paperID', 'GET', true, false, true);

set_time_limit(0);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

// Process form if submitted
if (isset($_POST['submit'])) {
    $old_exclusions = new Exclusion($paperID, $mysqli);
    $old_exclusions->load();

    // Clear the database of any past exclusions from the current paper.
    $old_exclusions->clear_all_exclusions();

    $old_q_id = 0;
    $old_status = '';
    $excluded = false;

    $new_exclusions = new Exclusion($paperID, $mysqli);
    for ($i = 1; $i <= $_POST['question_no']; $i++) {
        $current_id = $_POST['id_' . $i];
        if ($current_id != $old_q_id) {
            if (mb_strpos($old_status, '1') !== false) {
                $new_exclusions->add_exclusion($old_q_id, $old_status);
            }
            $old_status = '';
        }
        $old_status .= $_POST['status_' . $i];
        $old_q_id = $_POST['id_' . $i];
    }
    if (mb_strpos($old_status, '1') !== false) {
        $new_exclusions->add_exclusion($old_q_id, $old_status);
    }

    $new_exclusions->load();

    if ($old_exclusions->excluded !== $new_exclusions->excluded) {
        $propertyObj->set_recache_marks(1);
        $propertyObj->save();
    }

    header('location: ../paper/details.php?paperID=' . $paperID . '&module=' . $_GET['module'] . '&folder=' . $_GET['folder']);
    exit();
}

// Begin main code
$startdate = check_var('startdate', 'GET', true, false, true);
$enddate = check_var('enddate', 'GET', true, false, true);

$gradebook = new gradebook($mysqli);
$graded = $gradebook->paper_graded($paperID);

$pstats_array = array();
$dstats_array = array();
$ex_no = 0;

$cohort_percent = param::optional('percent', 100, param::INT, param::FETCH_GET);
if ($cohort_percent == 100) {
    $cohort_percent = 27;
}
$pstats = array('ve' => 0, 'e' => 0, 'm' => 0, 'h' => 0, 'vh' => 0);
$dstats = array('highest' => 0, 'high' => 0, 'intermediate' => 0, 'low' => 0);

$d_no = 0;
$d_total = 0;

$texteditorplugin = \plugins\plugins_texteditor::get_editor();
$renderpath = $texteditorplugin->get_render_paths();
$renderpath[] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates';
$render = new render($configObject, $renderpath);

render_head($render, $string);

// Initialise paper property variables
$paper_title = $propertyObj->get_paper_title();
$paper_type = $propertyObj->get_paper_type();
$labelcolor = $propertyObj->get_labelcolor();
$themecolor = $propertyObj->get_themecolor();
$marking = $propertyObj->get_marking();
$pass_mark = $propertyObj->get_pass_mark();

$moduleIDs = Paper_utils::get_modules($paperID, $mysqli);

$exclusions = new Exclusion($paperID, $mysqli);
$exclusions->load();

// Get the standards setting
if ($marking[0] == '2') {
    $tmp_parts = explode(',', $marking);

    $standard_setting = new StandardSetting($mysqli);
    $std_set_array = $standard_setting->get_ratings_by_question($tmp_parts[1]);
}

$students_only = param::optional('studentsonly', false, param::INT, param::FETCH_GET);
$repmodule = param::optional('repmodule', '', param::INT, param::FETCH_GET);
$repcourse = param::required('repcourse', param::TEXT, param::FETCH_GET);

// Capture the log data first.
$freq_array = array();
$bottom_log_array = array();
$top_log_array = array();
$bottom_cohort = array();
$top_cohort = array();
$user_no = 0;
$user_total = 0;

// Using references for returns for now, not ideal but better than it was!
capture_log_data(
    $cohort_percent,
    $students_only,
    $moduleIDs,
    $paper_type,
    $repcourse,
    $repmodule,
    $paperID,
    $startdate,
    $enddate,
    $bottom_cohort,
    $top_cohort,
    $freq_array,
    $bottom_log_array,
    $top_log_array,
    $user_no,
    $user_total,
    $mysqli
);

if ($user_total == 0) {
    // No one has taken the paper yet.
    render_notattempted($paper_title, $mysqli, $string);
    $mysqli->close();
    render_footer($paperID, $startdate, $enddate, $language, $cfg_root_path, $string, $render, $students_only);
    exit;
} elseif ($user_total == 1) {
    $cohort_percent = 100; // Moved here for header display
}

// Capture the paper makeup.
$question_no = 0;
$old_screen = 1;

$qids = param::optional('q_ids', false, param::TEXT, param::FETCH_GET);
if ($qids) {
    if (preg_match('/^[0-9,]+$/', $qids) !== false) { // question ID list validation
        $qids = explode(',', $qids);
        $qids = array_map('intval', $qids);
    } else {
        throw new \Exception('Unrecognised value for question IDs submitted - ' . $qids);
    }
}

$question_data = get_exam_questions($mysqli, $paperID, $qids);
$option_data = get_exam_options($mysqli, $paperID, $qids);

foreach ($question_data as $q_id => $question) {
    if ($question['q_type'] !== 'info') {
        $question_no++;
        $tmp_q_no = $question_no;
    } else {
        $tmp_q_no = null; // Set question number in compiled question data just to avoid ambiguity later
    }

    if (isset($std_set_array[$q_id])) {
        $std = $std_set_array[$q_id];
    } else {
        $std = '';
    }

    $question_data_array[] = compile_question_data(
        $question['screen_no'],
        $exclusions,
        $tmp_q_no,
        $q_id,
        $question['theme'],
        $question['scenario'],
        $question['leadin'],
        $question['q_type'],
        $question['q_media'],
        $question['q_media_width'],
        $question['q_media_height'],
        $question['q_media_alt'],
        $question['q_media_num'],
        $option_data[$q_id],
        $bottom_log_array,
        $top_log_array,
        $freq_array,
        $user_no,
        $question['score_method'],
        $question['display_method'],
        $themecolor,
        $std,
        $ex_no,
        $d_no,
        $d_total,
        $pstats,
        $pstats_array,
        $dstats,
        $dstats_array,
        $user_total,
        $language,
        $string
    );
}

render_display_header($paper_title, $graded, $user_total, $cohort_percent, $user_no, $mysqli, $string);
$old_screen = $question_data_array[0]['screen_no'];
foreach ($question_data_array as $question_data) {
    if ($old_screen != $question_data['screen_no']) {
        // Could should put this somewhere in a function/template/etc?
        echo '<tr><td colspan="2">';
        echo '<div class="screenbrk"><span class="scr_no">' . $string['screen'] . '&nbsp;' . $question_data['screen_no'] . '</span></div>';
        echo '</td></tr>';
    }
    display_question($render, $question_data, $string, $language);
    $old_screen = $question_data['screen_no'];
}

render_summary($render, $string, $pstats, $dstats);

refresh_performance_stats($mysqli, $paperID, $startdate, $cohort_percent, $pstats, $dstats, $pstats_array, $dstats_array, $user_total, $string);
// $startdate is likely to cause issues if the exam was spanned over two dates, it's using substrings

render_prefooter($ex_no, $string, $graded);
render_footer($paperID, $startdate, $enddate, $language, $configObject->get('cfg_root_path'), $string, $render, $students_only);
