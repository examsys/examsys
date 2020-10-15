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
 * Allows the properties of a paper to be edited.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once '../include/add_edit.inc';  // to clear MS Office tags
require_once '../include/load_config.php';
require_once '../include/timezones.php';

// Marking options
define('MARK_NO_ADJUSTMENT', '0');
define('MARK_RANDOM', '1');
define('MARK_STD_SET', '2');

/**
 * Check if year provided is a leap year.
 * @param integer $year the year
 * @return bool
 */
function is_leap($year)
{
    if ((modulo($year, 4) == 0 and modulo($year, 100) != 0) or modulo($year, 400) == 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * Leap year check helper function
 * @param integer $n the year
 * @param float|int $b divisor
 * @return float|int
 */
function modulo($n, $b)
{
    return $n - $b * floor($n / $b);
}

/**
 * Fix the date of the day if a leap year.
 * @param string $leap the year
 * @param string $month the month
 * @param string $day the day
 * @return string
 */
function fix_leapyear_day($year, $month, $day)
{
    if (!empty($year)) {
        $leap = is_leap($year);
        if ($leap == true and $month == '02' and ($day == '30' or $day == '31')) {
            $day = '29';
        }
        if ($leap == false and $month == '02' and ($day == '29' or $day == '30' or $day == '31')) {
            $day = '28';
        }
    }
    if (($month == '04' or $month == '06' or $month == '09' or $month == '11') and $day == '31') {
        $day = '30';
    }
    return $day;
}

$paperID = check_var('paperID', 'POST', true, false, true);

$exam_duration_hours = param::optional('exam_duration_hours', 0, param::INT, param::FETCH_POST);
$exam_duration_mins = param::optional('exam_duration_mins', 0, param::INT, param::FETCH_POST);
$ext_tyear = param::optional('ext_tyear', null, param::INT, param::FETCH_POST);
$int_tyear = param::optional('int_tyear', null, param::INT, param::FETCH_POST);
$texteditorplugin = \plugins\plugins_texteditor::get_editor();

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$papertype = $properties->get_paper_type();
$old_marking = $properties->get_marking();
$old_paper_title = $properties->get_paper_title();
$old_externals = $properties->get_externals();
$old_internals = $properties->get_internal_reviewers();
$papersettings = new PaperSettings($paperID, $properties->get_paper_type());
$logger = new Logger($mysqli);

if ($properties->get_summative_lock() and !$userObject->has_role('SysAdmin')) {
    $locked = true;
} else {
    $locked = false;
}

$modules_array = $properties->get_modules();

$q_feedback_enabled = Paper_utils::q_feedback_enabled(array_keys($modules_array), $mysqli);  // See if question-based feedback is enabled on all modules.

if (isset($_POST['paper_title'])) {
    if ($old_paper_title == $_POST['paper_title']) {
        $title_unique = true;
    } else {
        $title_unique = Paper_utils::is_paper_title_unique($_POST['paper_title'], $mysqli);
    }
}

if (!$title_unique) {
    echo json_encode('DUPLICATE_TITLE');
    exit();
} else {
    if (isset($_POST['paper_title'])) {  // Check is set, could be disabled.
        $properties->set_paper_title($_POST['paper_title']);
    }
    if (isset($_POST['paper_type']) and ($papertype == '0' or $papertype == '1')) {
        $properties->set_paper_type($_POST['paper_type']);
    }

    if (isset($_POST['bidirectional'])) {
        $properties->set_bidirectional($_POST['bidirectional']);
    }

    // External system details;
    $extid = check_var('externalid', 'POST', false, false, true);
    $extsys = check_var('externalsys', 'POST', false, false, true);
    if (!is_null($extid)) {
        $properties->set_externalid($extid);
    }
    if (!is_null($extsys)) {
        $properties->set_externalsys($extsys);
    }

    if ($papertype == '6') {
        if (isset($_POST['display_photos'])) {
            $properties->set_display_correct_answer(1);
        } else {
            $properties->set_display_correct_answer(0);
        }
    } else {
        if (isset($_POST['display_correct_answer'])) {
            $properties->set_display_correct_answer(1);
        } else {
            $properties->set_display_correct_answer(0);
        }
    }
    if (isset($_POST['display_students_response'])) {
        $properties->set_display_students_response(1);
    } else {
        $properties->set_display_students_response(0);
    }
    if ($papertype == '6') {
        $properties->set_display_question_mark($_POST['review']);
    } else {
        if (isset($_POST['display_question_mark'])) {
            $properties->set_display_question_mark(1);
        } else {
            $properties->set_display_question_mark(0);
        }
    }
    if (isset($_POST['display_feedback'])) {
        $properties->set_display_feedback(1);
    } else {
        $properties->set_display_feedback(0);
    }

    if (isset($_POST['hide_if_unanswered'])) {
        $properties->set_hide_if_unanswered('1');
    } else {
        $properties->set_hide_if_unanswered('0');
    }

    if (!isset($_POST['timezone'])) {
        $_POST['timezone'] = $properties->get_timezone();
    }

    if (($configObject->get_setting('core', 'cfg_summative_mgmt') and $papertype == '2' and $userObject->has_role(array('SysAdmin', 'Admin'))) or !$configObject->get_setting('core', 'cfg_summative_mgmt') or $papertype != '2') {
        $local_time = new DateTimeZone($configObject->get('cfg_timezone'));
        $target_timezone = new DateTimeZone($_POST['timezone']);

        if (isset($_POST['fyear']) and isset($_POST['fmonth']) and isset($_POST['fday']) and isset($_POST['fhour']) and isset($_POST['fminute'])) {
            $null_start_date = false;
            if ($_POST['fyear'] == '' and $_POST['fmonth'] == '' and $_POST['fday'] == '' and $_POST['fhour'] == '' and $_POST['fminute'] == '') {
                $null_start_date = true;
                $tmp_start_date = null;
            } else {
                $_POST['fday'] = fix_leapyear_day($_POST['fyear'], $_POST['fmonth'], $_POST['fday']);

                $start_date = new dateTime($_POST['fyear'] . $_POST['fmonth'] . $_POST['fday'] . $_POST['fhour'] . $_POST['fminute'], $target_timezone);
                $start_date->setTimezone($local_time);

                if ($_POST['timezone'] < 0) {
                    $start_date->modify('+' . abs($_POST['timezone']) . ' hour');
                } elseif ($_POST['timezone'] > 0) {
                    $start_date->modify('-' . $_POST['timezone'] . ' hour');
                }

                $properties->set_start_date($start_date->format('U'));
                $properties->set_raw_start_date($start_date->format('YmdHis'));
            }
        }

        if (isset($_POST['tyear']) and isset($_POST['tmonth']) and isset($_POST['tday']) and isset($_POST['thour']) and isset($_POST['tminute'])) {
            $null_end_date = false;
            if ($_POST['tyear'] == '' and $_POST['tmonth'] == '' and $_POST['tday'] == '' and $_POST['thour'] == '' and $_POST['tminute'] == '') {
                $null_end_date = true;
                $tmp_end_date = null;
            } else {
                $_POST['tday'] = fix_leapyear_day($_POST['tyear'], $_POST['tmonth'], $_POST['tday']);

                $end_date = new dateTime($_POST['tyear'] . $_POST['tmonth'] . $_POST['tday'] . $_POST['thour'] . $_POST['tminute'], $target_timezone);
                $end_date->setTimezone($local_time);

                if ($_POST['timezone'] < 0) {
                    $end_date->modify('+' . abs($_POST['timezone']) . ' hour');
                } elseif ($_POST['timezone'] > 0) {
                    $end_date->modify('-' . $_POST['timezone'] . ' hour');
                }
                $properties->set_end_date($end_date->format('U'));
                $properties->set_raw_end_date($end_date->format('YmdHis'));
            }
        }
        $properties->set_timezone($_POST['timezone']);

        if (isset($_POST['calendar_year'])) {
            $calendar_year = ($_POST['calendar_year'] == '') ? null : $_POST['calendar_year'];
            $properties->set_calendar_year($calendar_year);
        }

        // Set exam duration (in minutes).
        $exam_duration = $exam_duration_hours * 60;
        $exam_duration += $exam_duration_mins;

        if (!$locked) {
            $properties->set_exam_duration($exam_duration);
        }

        $lab_string = '';
        for ($i = 0; $i < $_POST['lab_no']; $i++) {
            if (isset($_POST["lab$i"])) {
                if ($lab_string == '') {
                    $lab_string = $_POST["lab$i"];
                } else {
                    $lab_string .= ',' . $_POST["lab$i"];
                }
            }
        }
        $properties->set_labs($lab_string);

        if ($papertype == '2') {
            $remote = check_var('remote_summative', 'POST', false, false, true);
            if (is_null($remote)) {
                $remote = 0;
            }
            $properties->updateSetting('remote_summative', $remote, $paperID);
        }
    }

    $_POST['ext_tday'] = fix_leapyear_day($ext_tyear, $_POST['ext_tmonth'], $_POST['ext_tday']);

    if (empty($ext_tyear) or $_POST['ext_tmonth'] == '' or $_POST['ext_tday'] == '') {
        $properties->set_external_review_deadline(null);
    } else {
        $tmp_date = new DateTime($ext_tyear . '-' . $_POST['ext_tmonth'] . '-' . $_POST['ext_tday']);
        $properties->set_external_review_deadline($tmp_date->format('Y-m-d'));
        unset($tmp_date);
    }

    $_POST['int_tday'] = fix_leapyear_day($int_tyear, $_POST['int_tmonth'], $_POST['int_tday']);

    if (empty($int_tyear) or $_POST['int_tmonth'] == '' or $_POST['int_tday'] == '') {
        $properties->set_internal_review_deadline(null);
    } else {
        $tmp_date = new DateTime($int_tyear . '-' . $_POST['int_tmonth'] . '-' . $_POST['int_tday']);
        $properties->set_internal_review_deadline($tmp_date->format('Y-m-d'));
    }

    $paper_modules = array();
    $first_module_id = '';

    for ($i = 0; $i < $_POST['module_no']; $i++) {
        if (isset($_POST['mod' . $i])) {
            if (count($paper_modules) == 0) {
                $paper_modules[$_POST['mod' . $i]] = $_POST['mod' . $i];
                $first_module_idMod = $_POST['mod' . $i];
                $first_module_id = $_POST['mod' . $i];
            } else {
                $paper_modules[$_POST['mod' . $i]] = $_POST['mod' . $i];
            }
        }
    }

    $new_externals = array();
    for ($i = 0; $i < $_POST['examiner_no']; $i++) {
        if (isset($_POST["examiner$i"])) {
            $new_externals[] = intval($_POST["examiner$i"]);
        }
    }

    $new_internals = array();
    for ($i = 0; $i < $_POST['internal_no']; $i++) {
        if (isset($_POST["internal$i"])) {
            $new_internals[] = intval($_POST["internal$i"]);
        }
    }

    $properties->set_paper_prologue(clearMSOtags($texteditorplugin->prepare_text_for_save($_POST['paper_prologue'])));

    if (isset($_POST['osce_marking_guidance'])) {
        $properties->set_paper_postscript(clearMSOtags($texteditorplugin->prepare_text_for_save($_POST['osce_marking_guidance'])));
    } else {
        $properties->set_paper_postscript(clearMSOtags($texteditorplugin->prepare_text_for_save($_POST['paper_postscript'])));
    }

    if ($papertype == '6') {
        $properties->set_rubric($_POST['type']);      // Reuse the 'rubric' field to store which field in the metadata to use for groups.
    } else {
        $properties->set_rubric(clearMSOtags($texteditorplugin->prepare_text_for_save($_POST['rubric_text'])));
    }

    if (!isset($_POST['marking']) and $papertype == 4) {
        // Do nothing, the marking method is locked.
    } elseif (!isset($_POST['marking']) or $_POST['marking'] == '') {
        $properties->set_marking(MARK_NO_ADJUSTMENT);
    } elseif ($_POST['marking'] == MARK_STD_SET) {
        $properties->set_marking($_POST['std_set']);
    } else {
        $properties->set_marking($_POST['marking']);
    }

    $tmp_pass_mark = (isset($_POST['pass_mark'])) ? $_POST['pass_mark'] : 0;
    if ($tmp_pass_mark == '') {
        $tmp_pass_mark = 40;
    }
    $properties->set_pass_mark($tmp_pass_mark);

    $tmp_distinction_mark = (isset($_POST['distinction_mark']) and $_POST['distinction_mark'] != '') ? $_POST['distinction_mark'] : 70;
    $properties->set_distinction_mark($tmp_distinction_mark);

    if ($properties->get_summative_lock() === false or $userObject->has_role('SysAdmin')) {
        $tmp_calculator = (isset($_POST['calculator'])) ? $_POST['calculator'] : 0;
        $properties->set_calculator($tmp_calculator);
    }

    if (isset($_POST['sound_demo'])) {
        $properties->set_sound_demo(1);
    } else {
        $properties->set_sound_demo(0);
    }

    $password = trim($_POST['password']);
    if (!$locked) {
        if ($password != $properties->get_decrypted_password()) {
            $properties->set_password($password);
        }
        $properties->set_fullscreen($_POST['fullscreen']);
    }

    $properties->set_bgcolor($_POST['bgcolor']);
    $properties->set_fgcolor($_POST['fgcolor']);
    $properties->set_themecolor($_POST['themecolor']);
    $properties->set_labelcolor($_POST['labelcolor']);
    $properties->set_folder($_POST['folderID']);

    if ($papertype == '2' and $old_marking != $properties->get_marking()) {
        $properties->set_recache_marks(1);
    }

    // Save any adjusted properties to the database.
    $properties->save();

    if (!$locked or $userObject->has_role(array('SysAdmin', 'Admin'))) {
        $old_modules = $properties->get_modules(true);

        if (!$locked or $userObject->has_role(array('SysAdmin'))) {
            Paper_utils::update_modules($paper_modules, $paperID, $mysqli, $userObject);
        }

        $paper_modules = $properties->get_modules(true);

        $utils = new GeneralUtils();
        if (!$utils->arrays_are_equal($old_modules, $paper_modules)) {
            $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), implode(',', $old_modules), implode(',', $paper_modules), 'modules');
        }

        if (Paper_utils::update_reviewers($old_externals, $new_externals, 'external', $paperID, $mysqli)) {
            $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), implode(',', array_keys($old_externals)), implode(',', $new_externals), 'externals');
        }
        if (Paper_utils::update_reviewers($old_internals, $new_internals, 'internal', $paperID, $mysqli)) {
            $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), implode(',', array_keys($old_internals)), implode(',', $new_internals), 'internals');
        }
    }

    // Update Safe Exam Browser settings if enabled.
    if ($configObject->get_setting('core', 'paper_seb_enabled') and $papersettings->settingsCategoryEnabled('seb')) {
        $seb = check_var('seb_enabled', 'POST', false, false, true);
        if (is_null($seb)) {
            $seb = 0;
        }
        $properties->updateSetting('seb_enabled', $seb, $paperID);
        if ($papersettings->verifyValue(\Config::BOOLEAN, $seb)) {
            $seb_keys = param::optional('seb_keys_text', '', param::RAW, param::FETCH_POST);

            // Get existing keys to check for changes
            $seb_metadata = Paper_utils::get_metadata($mysqli, $paperID, 'seb_hash');
            $old_seb_key_array = $seb_metadata['seb_hash'] ?? [];

            if (empty(trim($seb_keys))) {
                if (!empty($old_seb_key_array)) {
                    Paper_utils::delete_metadata(
                        $mysqli,
                        $paperID,
                        'seb_hash'
                    ); // Should this be PaperUtils::delete_metadata and declared static?
                    $logger->track_change(
                        'Paper',
                        $paperID,
                        $userObject->get_user_ID(),
                        'Safe Exam Browser keys removed',
                        '',
                        'SEB'
                    );
                }
            } else {
                $seb_key_array = explode("\n", $seb_keys);
                $seb_key_array = array_map('trim', $seb_key_array);

                // Sort and compare key arrays
                sort($old_seb_key_array);
                sort($seb_key_array);

                if ($old_seb_key_array !== $seb_key_array) {
                    Paper_utils::set_metadata(
                        $mysqli,
                        $paperID,
                        array('seb_hash' => $seb_key_array),
                        true
                    ); // Delete old entries, replace with new
                    $logger->track_change(
                        'Paper',
                        $paperID,
                        $userObject->get_user_ID(),
                        'Safe Exam Browser keys added/updated',
                        '',
                        'SEB'
                    );
                }
            }
        }
    }
    // Release objectives-based feedback
    if (isset($_POST['old_objectives_report']) and $_POST['old_objectives_report'] != '' and isset($_POST['objectives_report']) and $_POST['objectives_report'] == '0') {
        $editProperties = $mysqli->prepare("DELETE FROM feedback_release WHERE paper_id = ? AND type = 'objectives'");
        $editProperties->bind_param('i', $paperID);
        $editProperties->execute();
        $editProperties->close();

        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), 'Objectives-based Feedback', '', 'feedback');
    } elseif (isset($_POST['old_objectives_report']) and $_POST['old_objectives_report'] == '' and isset($_POST['objectives_report']) and $_POST['objectives_report'] == '1') {
        $editProperties = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL, ?, NOW(), 'objectives')");
        $editProperties->bind_param('i', $paperID);
        $editProperties->execute();
        $editProperties->close();

        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), '', 'Objectives-based Feedback', 'feedback');
    }

    // Release question-based feedback
    if (isset($_POST['old_questions_report']) and $_POST['old_questions_report'] != '' and isset($_POST['questions_report']) and $_POST['questions_report'] == '0') {
        $editProperties = $mysqli->prepare("DELETE FROM feedback_release WHERE paper_id = ? AND type = 'questions'");
        $editProperties->bind_param('i', $paperID);
        $editProperties->execute();
        $editProperties->close();

        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), 'Question-based Feedback', '', 'feedback');

        // Include check to $q_feedback_enabled to see if question-based feedback
        // is switched on at the module level.
    } elseif ($q_feedback_enabled and isset($_POST['old_questions_report']) and $_POST['old_questions_report'] == '' and isset($_POST['questions_report']) and $_POST['questions_report'] == '1') {
        $editProperties = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL, ?, NOW(), 'questions')");
        $editProperties->bind_param('i', $paperID);
        $editProperties->execute();
        $editProperties->close();

        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), '', 'Question-based Feedback', 'feedback');
    }

    // Release cohort performance feedback
    if (isset($_POST['old_cohort_performance']) and $_POST['old_cohort_performance'] != '' and isset($_POST['cohort_performance']) and $_POST['cohort_performance'] == '0') {
        $editProperties = $mysqli->prepare("DELETE FROM feedback_release WHERE paper_id = ? AND type = 'cohort_performance'");
        $editProperties->bind_param('i', $paperID);
        $editProperties->execute();
        $editProperties->close();

        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), 'Cohort Performance Feedback', '', 'feedback');
    } elseif (isset($_POST['old_cohort_performance']) and $_POST['old_cohort_performance'] == '' and isset($_POST['cohort_performance']) and $_POST['cohort_performance'] == '1') {
        $editProperties = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL, ?, NOW(), 'cohort_performance')");
        $editProperties->bind_param('i', $paperID);
        $editProperties->execute();
        $editProperties->close();

        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), '', 'Cohort Performance Feedback', 'feedback');
    }

    // Release external examiner feedback
    if (isset($_POST['old_external_examiner']) and $_POST['old_external_examiner'] != '' and isset($_POST['external_examiner']) and $_POST['external_examiner'] == '0') {
        $editProperties = $mysqli->prepare("DELETE FROM feedback_release WHERE paper_id = ? AND type = 'external_examiner'");
        $editProperties->bind_param('i', $paperID);
        $editProperties->execute();
        $editProperties->close();

        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), 'External Examiner Feedback', '', 'feedback');
    } elseif (isset($_POST['old_external_examiner']) and $_POST['old_external_examiner'] == '' and isset($_POST['external_examiner']) and $_POST['external_examiner'] == '1') {
        $editProperties = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL, ?, NOW(), 'external_examiner')");
        $editProperties->bind_param('i', $paperID);
        $editProperties->execute();
        $editProperties->close();

        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), '', 'External Examiner Feedback', 'feedback');
    }

    if ($papertype != '2' and $papertype != '4') {    // Update textual feedback if not a summative paper or OSCE station.
        // Get old settings
        $old_textual_feedback = Paper_utils::get_textual_feedback($paperID, $mysqli);
        for ($i = 1; $i < 10; $i++) {
            if (!isset($old_textual_feedback[$i]['msg'])) {
                $old_textual_feedback[$i]['msg'] = '';
                $old_textual_feedback[$i]['boundary'] = '';
            }
        }

        // Get new settings
        $textual_feedback = array();
        for ($i = 1; $i < 10; $i++) {
            if (isset($_POST["feedback_msg$i"]) and trim($_POST["feedback_msg$i"]) != '') {
                $textual_feedback[$i]['msg'] = $_POST["feedback_msg$i"];
                $textual_feedback[$i]['boundary'] = $_POST["feedback_value$i"];
            } else {
                $textual_feedback[$i]['msg'] = '';
                $textual_feedback[$i]['boundary'] = '';
            }
        }

        $editProperties = $mysqli->prepare('DELETE FROM paper_feedback WHERE paperID = ?');
        $editProperties->bind_param('i', $paperID);
        $editProperties->execute();
        $editProperties->close();

        for ($i = 1; $i < 10; $i++) {
            $editProperties = $mysqli->prepare('INSERT INTO paper_feedback VALUES (NULL, ?, ?, ?)');
            if (isset($_POST["feedback_msg$i"]) and trim($_POST["feedback_msg$i"]) != '') {
                $editProperties->bind_param('iis', $paperID, $_POST["feedback_value$i"], $_POST["feedback_msg$i"]);
                $editProperties->execute();
            }
            $editProperties->close();

            if ($old_textual_feedback[$i]['msg'] != $_POST["feedback_msg$i"] or $old_textual_feedback[$i]['boundary'] != $_POST["feedback_value$i"]) {
                // log a change
                $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_textual_feedback[$i]['boundary'] . '%&nbsp;' . $old_textual_feedback[$i]['msg'], $textual_feedback[$i]['boundary'] . '%&nbsp;' . $textual_feedback[$i]['msg'], 'textualfeedback');
            }
        }
    }

    // Get the current (old) metadata security settings from the database.
    $old_meta = '';
    $result = $mysqli->prepare('SELECT name, value FROM paper_metadata_security WHERE paperID = ? ORDER BY name');
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($name, $value);
    while ($result->fetch()) {
        if ($old_meta == '') {
            $old_meta = $name . ':' . $value;
        } else {
            $old_meta .= ', ' . $name . ':' . $value;
        }
    }
    $result->close();

    // Loop around the POST fields to get the new metadata security settings.
    $new_meta = '';
    for ($i = 0; $i < $_POST['meta_dropdown_no']; $i++) {
        $meta_type = $_POST['meta_type' . $i];
        $meta_value = $_POST['meta_value' . $i];

        if ($meta_value != '') {
            if ($new_meta == '') {
                $new_meta = $meta_type . ':' . $meta_value;
            } else {
                $new_meta .= ', ' . $meta_type . ':' . $meta_value;
            }
        }
    }

    if ($old_meta != $new_meta) {
        // The metadata security settings have changed - update the database.
        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_meta, $new_meta, 'restricttometadata');

        $editProperties = $mysqli->prepare('DELETE FROM paper_metadata_security WHERE paperID = ?');
        $editProperties->bind_param('i', $paperID);
        $editProperties->execute();
        $editProperties->close();

        for ($i = 0; $i < $_POST['meta_dropdown_no']; $i++) {
            $meta_type = $_POST['meta_type' . $i];
            $meta_value = $_POST['meta_value' . $i];

            if ($meta_value != '') {
                $editProperties = $mysqli->prepare('INSERT INTO paper_metadata_security VALUES (NULL, ?, ?, ?)');
                $editProperties->bind_param('iss', $paperID, $meta_type, $meta_value);
                $editProperties->execute();
                $editProperties->close();
            }
        }
    }

    // Get existing Reference Materials
    $existing_refs = array();
    $result = $mysqli->prepare('SELECT refID FROM reference_papers WHERE paperID = ?');
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($refID);
    while ($result->fetch()) {
        $existing_refs[$refID] = $refID;
    }
    $result->close();

    $new_refs = array();
    for ($i = 0; $i < $_POST['reference_no']; $i++) {
        if (isset($_POST["ref$i"])) {
            $new_refs[$_POST["ref$i"]] = $_POST["ref$i"];
        }
    }

    foreach ($new_refs as $new_ref) {
        if (isset($existing_refs[$new_ref])) {
            unset($existing_refs[$new_ref]);
        } else {
            $editProperties = $mysqli->prepare('INSERT INTO reference_papers VALUES (NULL, ?, ?)');
            $editProperties->bind_param('ii', $paperID, $new_ref);
            $editProperties->execute();
            $editProperties->close();

            $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), '', $new_ref, 'referencematerial');
        }
    }
    foreach ($existing_refs as $existing_ref) {
        $editProperties = $mysqli->prepare('DELETE FROM reference_papers WHERE paperID = ? AND refID = ?');
        $editProperties->bind_param('ii', $paperID, $existing_ref);
        $editProperties->execute();
        $editProperties->close();

        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $existing_ref, '', 'referencematerial');
    }
}
echo json_encode('SUCCESS');
