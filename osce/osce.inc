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

/**
 * Save OSCE form data back to the database.
 * @param int $propertyObj                  - Object of the OSCE station paper.
 * @param int $userID                               - ID of the student
 * @param mixed $postdata                       - $_POST data from the form.
 * @param object $db                                - Link to MySQL database.
 */
function save_osce_form($propertyObj, $userID, $postdata, $db)
{

    $userObject = UserObject::get_instance();

    $paperID      = $propertyObj->get_property_id();
    $marking      = $propertyObj->get_marking();
    $number_of_qs = $propertyObj->get_question_no();
    $pass_mark    = $propertyObj->get_pass_mark();
    $total_mark   = $propertyObj->get_total_mark();

    if ($postdata['userID'] != '') {
        $result = $db->prepare('SELECT id FROM log4_overall WHERE q_paper = ? AND userID = ? LIMIT 1');
        $result->bind_param('ii', $paperID, $userID);
        $result->execute();
        $result->store_result();
        $result->bind_result($insertID);
        $result->fetch();
        $have_record = $result->num_rows > 0;
        $result->close();

        if ($have_record) {
            // Delete any Log4 previous submissions for this student.
            $result = $db->prepare('DELETE FROM log4 WHERE log4_overallID = ?');
            $result->bind_param('i', $insertID);
            $result->execute();
        } else {
            // Write summary information into Log4_overall.
            $started = DATE('YmdHis');
            $result = $db->prepare("INSERT INTO log4_overall VALUES (NULL, ?, ?, ?, ?, NULL, ?, ?, ?, 'electronic', ?)");
            $result->bind_param('isisssii', $userID, $started, $paperID, $postdata['overall_val'], $postdata['fback'], $postdata['grade'], $userObject->get_user_ID(), $postdata['year']);
            $result->execute();
            $result->close();

            $insertID = $db->insert_id;
        }

        // Write individual ratings into Log4.
        $numeric_score = 0;
        for ($question = 1; $question <= $number_of_qs; $question++) {
            $tmp_val = ($postdata['q' . $question . '_val'] - 1);
            if (isset($postdata[$postdata['q' . $question . '_id'] . '_parts'])) {
                $q_parts = $postdata[$postdata['q' . $question . '_id'] . '_parts'];
            } else {
                $q_parts = '';
            }
            $result = $db->prepare('INSERT INTO log4 VALUES (NULL, ?, ?, ?, ?)');
            $result->bind_param('issi', $postdata['q' . $question . '_id'], $tmp_val, $q_parts, $insertID);
            $result->execute();
            $result->close();
            $numeric_score += ($postdata['q' . $question . '_val'] - 1);
        }

        // Update Log4_overall with the overall score.
        if ($marking == '5') {
            if ($total_mark == 0) {
                $user_percent = 0;
            } else {
                $user_percent = ($numeric_score / $total_mark) * 100;
            }
            if ($user_percent >= $pass_mark) {
                $overall_rating = 'pass';
            } else {
                $overall_rating = 'fail';
            }
        } else {
            $overall_rating = $postdata['overall_val'];
        }
        $result = $db->prepare('UPDATE log4_overall SET numeric_score = ?, overall_rating = ?, feedback = ? WHERE id = ?');
        $result->bind_param('issi', $numeric_score, $overall_rating, $postdata['fback'], $insertID);
        $result->execute();
        $result->close();
    }
}

/**
 * Strips out text that could cause Word 2003 to throw an error.
 * @param string $text  - The text string to clean up.
 * @param $parts                - Which parts have been ticked off in the database.
 * @return string - cleaned up text.
 */
function parse_leadin_word_2003($text, $parts)
{
    global $q_id;

    $count = 0;
    $calss = 'part';

    $text = str_replace("\n", '', $text);
    $text = str_replace("\r", '', $text);
    $text = str_replace("\t", '', $text);
    $text = str_replace('&nbsp;', ' ', $text);
    $text = str_replace('<strong>', '', $text);
    $text = str_replace('</strong>', '', $text);
    $text = str_replace('<ul>', '', $text);
    $text = str_replace('</ul>', '', $text);
    $text = str_replace('<div>', '', $text);
    $text = str_replace('</div>', '<w:br/>', $text);

    $starts = explode('[', $text);
    $count = count($starts);
    if ($parts == '') {
        $value = str_pad('', $count - 1, '0');
    } else {
        $value = $parts;
    }

    $tmptext = '';
    if ($count > 1) {
        for ($i = 0; $i < $count; $i++) {
            if ($i > 0) {
                if ($value[$i - 1] == 1) {
                    $tmptext .= '<w:r><w:rPr><w:color w:val="00B050"/><w:u w:val="single"/></w:rPr><w:t>';
                } else {
                    $tmptext .= '<w:r><w:rPr></w:rPr><w:t>';
                }

                $tmptext .= ltrim($starts[$i]);

                if ($i < ($count - 1)) {
                    if (mb_substr($starts[$i], -1) != ']' or mb_substr($starts[$i], -1) != 'li>') {
                        $end_tags = '</w:t></w:r>';
                        $rep_tags = '</w:t></w:r><w:r><w:t>';
                    } else {
                        $end_tags = '</w:t></w:r>';
                        $rep_tags = '';
                    }
                } else {
                    $end_tags = '</w:t></w:r>';
                    $rep_tags = '';
                }

                $tmptext .= $end_tags;
                $tmptext = preg_replace('/\]/', " $rep_tags", $tmptext);
            } else {
                if (trim($starts[$i]) != '' and trim($starts[$i]) != '<li>') {
                    $tmptext .= '<w:r><w:t>|' . trim(htmlspecialchars($starts[$i])) . '|</w:t></w:r></w:p><w:p>';
                }
            }
        }
        $x = 0;
        $tmptext = str_replace('<li>', '', $tmptext, $x);
        if ($x > 0) {
            $tmptext = str_replace('<w:r><w:rPr><w:color w:val="00B050"/><w:u w:val="single"/></w:rPr><w:t>', '<w:pPr><w:listPr><w:ilvl w:val="0"/><w:ilfo w:val="3"/><wx:t wx:val="\xE2\x80\xA2"/><wx:font wx:val="Calibri"/></w:listPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/></w:pPr><w:r><w:rPr><w:color w:val="00B050"/><w:u w:val="single"/></w:rPr><w:t>' . "\n\n", $tmptext);
            $tmptext = str_replace('<w:r><w:rPr></w:rPr><w:t>', '<w:pPr><w:listPr><w:ilvl w:val="0"/><w:ilfo w:val="3"/><wx:t wx:val="\xE2\x80\xA2"/><wx:font wx:val="Calibri"/></w:listPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/></w:pPr><w:r><w:t>' . "\n\n", $tmptext);
            $tmptext = str_replace('</li></w:t></w:r>', '</w:t></w:r></w:p><w:p>', $tmptext);
        }

        return $tmptext;
    } else {
        return  '<w:r><w:t>' . $text . '</w:t></w:r>';
    }
}

function parse_leadin($text, $parts)
{
    global $q_id;

    $count = 0;
    $calss = 'part';

    $starts = explode('[', $text);
    $count = count($starts);
    if ($parts == '') {
        $value = str_pad('', $count - 1, '0');
    } else {
        $value = $parts;
    }
    $tmptext = '';
    if ($count > 1) {
        $tmptext .= $starts[0];
        for ($i = 1; $i < $count; $i++) {
            if ($value[$i - 1] == 1) {
                 $class = 'part_ok';
            } else {
                $class = 'part';
            }
            $tmptext .= '<span id="' . $q_id . '_' . ($i) . "\" class=\"$class\" onclick=\"subpart(this);\">";
            $tmptext .= $starts[$i];
        }
        $tmptext = preg_replace('/\]/', '</span>', $tmptext);

        $tmptext .= "\n<input id=\"" . $q_id . '_parts" name="' . $q_id . "_parts\" type=\"hidden\" value=\"$value\" />\n";

        return $tmptext;
    } else {
        return $text;
    }
}

/**
 * Determines is a rating should be used to calculate the pass mark in the borderline method.
 * @param int $result                   - The student classification.
 * @param object $propertyObj   - Paper properties object.
 * @return bool - true = rating should be used to determine borderline pass mark, false = is should not.
 */
function isBorderline($result, $propertyObj)
{
    if ($propertyObj->get_marking() == 3 and $result == 2) {                          // Clear Fail | Borderline | Clear Pass
        return true;
    } elseif ($propertyObj->get_marking() == 4 and ($result == 2 or $result == 3)) {  // Fail | Borderline fail | Borderline pass | Pass | Good pass
        return true;
    } elseif ($propertyObj->get_marking() == 6 and $result == 2) {                    // Clear FAIL | BORDERLINE | Clear PASS | Honours PASS
        return true;
    } else {
        return false;
    }
}

/**
 * Calculates the pass mark when the borderline method is used.
 * @param array $user_results   - Master array of student results.
 * @param int $user_no              - How many students there are.
 * @param object $propertyObj   - Paper properties object.
 * @return int - the pass mark of the paper expressed as a percentage.
 */
function getBlinePassmk($user_results, $user_no, $propertyObj)
{
    $passmark = 0;

    $borderlines = array();

    for ($i = 0; $i < $user_no; $i++) {
        if (isBorderline($user_results[$i]['rating'], $propertyObj)) {
            $borderlines[] = $user_results[$i]['percent'];
        }
    }

    if (count($borderlines) > 0) {
        $passmark = MathsUtils::median($borderlines);
    }

    return $passmark;
}

/**
 * Compile a report on students who have taken an OSCE station.
 * @param object $propertyObj   - Paper properties object.
 * @param bool $demo                    - Is the system in demo mode.
 * @param object $configObj     - Configuration object.
 * @param object $db                    - MySQL database object.
 * @return array - details of students who have taken the OSCE station.
 */
function load_osce_results($propertyObj, $demo, $configObj, $question_no, $db)
{

    $modIDs = implode(',', array_keys(Paper_utils::get_modules($_GET['paperID'], $db)));
    $user_no = 0;
    $calendar_year = $propertyObj->get_calendar_year();

    $total_mark = $propertyObj->get_total_mark();

    if (!isset($_GET['absent']) or $_GET['absent'] == 0) {
        $result = $db->prepare("SELECT DISTINCT log4_overall.id, students.id, students.gender, log4_overall.student_grade, log4_overall.year, students.surname, students.initials, students.first_names, students.title, student_id, started, DATE_FORMAT(started,'{$configObj->get('cfg_long_date_time')}') AS display_started, overall_rating, numeric_score, feedback, examiners.title, examiners.surname, examiners.initials FROM (users AS students, users AS examiners, sid, log4_overall) WHERE students.id = log4_overall.userID AND examiners.id = log4_overall.examinerID AND started >= ? AND started <= ? AND q_paper = ? AND students.id=sid.userID ORDER BY students.surname, students.initials");
        $result->bind_param('ssi', $_GET['startdate'], $_GET['enddate'], $_GET['paperID']);
    } else {
        $result = $db->prepare("SELECT log4_overall.id, students.id, students.gender, log4_overall.student_grade, log4_overall.year, students.surname, students.initials, students.first_names, students.title, student_id, started, DATE_FORMAT(started,'{$configObj->get('cfg_long_date_time')}') AS display_started, overall_rating, numeric_score, feedback, examiners.title, examiners.surname, examiners.initials FROM (modules_student, users AS students, sid) LEFT JOIN log4_overall ON students.id = log4_overall.userID AND started >= ? AND started <= ? AND q_paper = ? LEFT JOIN users AS examiners ON log4_overall.examinerID = examiners.id WHERE modules_student.userID=students.id AND students.id = sid.userID AND modules_student.idMod IN ($modIDs) AND calendar_year = ? ORDER BY students.surname, students.initials");
        $result->bind_param('ssis', $_GET['startdate'], $_GET['enddate'], $_GET['paperID'], $calendar_year);
    }
    $result->execute();
    $result->bind_result($metadataID, $userID, $gender, $grade, $year, $surname, $initials, $first_names, $title, $student_id, $started, $display_started, $overall_rating, $numeric_score, $feedback, $examiner_title, $examiner_surname, $examiner_initials);
    $user_results = array();
    while ($result->fetch()) {
        $user_results[$user_no]['metadataID']       = $metadataID;
        $user_results[$user_no]['name']             = $surname . $initials;
        $user_results[$user_no]['display_name']     = $title . ' ' . $surname . ', <span style="color:#808080">' . $first_names . '</span>';
        $user_results[$user_no]['classification']   = '';
        $user_results[$user_no]['rating']           = $overall_rating;
        $user_results[$user_no]['numeric_score']    = $numeric_score;
        $user_results[$user_no]['mark']             = $numeric_score;
        if ($total_mark > 0) {
            $user_results[$user_no]['percent']    = ($numeric_score / $total_mark) * 100;
        } else {
            $user_results[$user_no]['percent']    = 0;
        }
        $user_results[$user_no]['grade']            = $grade;
        $user_results[$user_no]['started']          = $started;
        $user_results[$user_no]['duration']         = 0;
        $user_results[$user_no]['userID']           = $userID;
        $user_results[$user_no]['student_grade']    = $grade;
        $user_results[$user_no]['year']             = $year;
        $user_results[$user_no]['display_started']  = $display_started;
        $user_results[$user_no]['title']            = $title;
        $user_results[$user_no]['surname']          = \demo::demo_replace($surname, $demo);
        $user_results[$user_no]['initials']         = \demo::demo_replace($initials, $demo);
        $user_results[$user_no]['first_names']      = \demo::demo_replace($first_names, $demo);
        $user_results[$user_no]['student_id']       = \demo::demo_replace_number($student_id, $demo);
        $user_results[$user_no]['gender']           = $gender;
        $user_results[$user_no]['feedback']         = $feedback;
        $user_results[$user_no]['examiner']         = $examiner_title . ' ' . $examiner_initials . ' ' . $examiner_surname;
        $user_results[$user_no]['visible']          = 1;    // Default to visible unless switched off below.
        $user_results[$user_no]['questions']        = $question_no;
        $user_results[$user_no]['killer_fail']      = false;        // Set all users to false for killer questions.

        $user_no++;
    }
    $result->close();

    $killer_questions = new Killer_question($_GET['paperID'], $db);
    $killer_questions->load();

    check_killer_answers($user_results, $killer_questions, $db);

    return $user_results;
}

function find_user_result($user_results, $target_userID)
{
    $user_no = false;
    $user_count = count($user_results);

    for ($i = 0; $i < $user_count; $i++) {
        if ($user_results[$i]['userID'] == $target_userID) {
            $user_no = $i;
            break;
        }
    }

    return $user_no;
}

function check_killer_answers(&$user_results, $killer_questions, $db)
{
    $result = $db->prepare('SELECT q_id, rating, userID FROM log4, log4_overall WHERE log4.log4_overallID = log4_overall.id AND started >= ? AND started <= ? AND q_paper = ?');
    $result->bind_param('ssi', $_GET['startdate'], $_GET['enddate'], $_GET['paperID']);
    $result->execute();
    $result->bind_result($q_id, $rating, $userID);
    while ($result->fetch()) {
        if ($rating == 0 and $killer_questions->is_killer_question($q_id)) {
            $target_user = find_user_result($user_results, $userID);
            if ($target_user !== false) {
                $user_results[$target_user]['killer_fail'] = true;
            }
        }
    }
    $result->close();
}

/**
 * Load all the ratings of questions on a paper.
 * @param object $db - MySQL database object.
 * @return array - set of ratings for each question.
 */
function load_osce_medians($db)
{
    $q_medians = array();

    $result = $db->prepare('SELECT q_id, rating, userID FROM log4, log4_overall WHERE log4.log4_overallID = log4_overall.id AND started >= ? AND started <= ? AND q_paper = ?');
    $result->bind_param('ssi', $_GET['startdate'], $_GET['enddate'], $_GET['paperID']);
    $result->execute();
    $result->bind_result($q_id, $rating, $userID);
    $user_results = array();
    while ($result->fetch()) {
        $q_medians[$q_id][] = (int)$rating;
    }
    $result->close();

    return $q_medians;
}

/**
 * Set pass fail classifications for all users.
 * @param array $user_results   - Master array of student results.
 * @param int $passmark             - Pass mark of the paper expressed as a percentage.
 * @param int $user_no              - How many users there are in the master array.
 * @param array $string             - Language translations.
 */
function set_classification($marking, &$user_results, $passmark, $user_no, $string)
{
    switch ($marking) {
        case '3':
            $labels = $string['marking3'];
            break;
        case '4':
            $labels = $string['marking4'];
            break;
        case '6':
            $labels = $string['marking6'];
            break;
        case '7':
            $labels = $string['marking7'];
            break;
    }

    for ($i = 0; $i < (int)$user_no; $i++) {
        if ($user_results[$i]['killer_fail']) {
            $user_results[$i]['classification'] = $string['fail'];
        } elseif ($passmark == 'N/A') {     // Passmark is set to N/A - therefore use global rating from OSCE mark sheet
            if (isset($user_results[$i]['rating']) and $user_results[$i]['rating'] > 0 and !empty($labels)) {
                $rating_no = ($user_results[$i]['rating'] - 1);
                $user_results[$i]['classification'] = $labels[$rating_no];
            } else {
                $user_results[$i]['classification'] = '';
            }
        } elseif ($user_results[$i]['percent'] >= $passmark) {
            $user_results[$i]['classification'] = $string['pass'];
        } else {
            $user_results[$i]['classification'] = $string['fail'];
        }
    }
}

/**
 * Converts a numerical overall rating for a student into a textual one (e.g. 3 = 'Clear Pass').
 * @param array $user_results   - Master array of student results.
 * @param int $user_no              - How many users there are in the master array.
 * @param object $propertyObj   - Paper properties object.
 * @param array $string             - Language translations.
 */
function rating_num_text(&$user_results, $user_no, $propertyObj, $string)
{
    $labels = get_labels($propertyObj);

    for ($i = 0; $i < $user_no; $i++) {
        if (isset($user_results[$i]['rating']) and isset($labels[$user_results[$i]['rating']])) {
            $user_results[$i]['rating'] = $labels[$user_results[$i]['rating']];
        }
    }
}

/**
 * Creates an appropriate array for the marking method.
 * @param object $propertyObj   - Paper properties object.
 * @return array - Lookup array of numbers to textual labels.
 */
function get_labels($propertyObj)
{
    switch ($propertyObj->get_marking()) {
        case '1':
            $labels = array(1 => 'Fail', 2 => 'Pass', 'ERROR' => 'ERROR');
            break;
        case '3':
            $labels = array(1 => 'Clear Fail', 2 => 'Borderline', 3 => 'Clear Pass', 'ERROR' => 'ERROR');
            break;
        case '4':
            $labels = array(1 => 'Fail', 2 => 'Borderline fail', 3 => 'Borderline pass', 4 => 'Pass', 5 => 'Good pass', 'ERROR' => 'ERROR');
            break;
        case '5':
            $labels = array('fail' => 'Unsatisfactory', 'pass' => 'Competent', 'ERROR' => 'ERROR');
            break;
        case '6':
            $labels = array(1 => 'Clear FAIL', 2 => 'BORDERLINE', 3 => 'Clear PASS', 4 => 'Honours PASS', 'ERROR' => 'ERROR');
            break;
        case '7':
            $labels = array(1 => 'Fail', 2 => 'Pass', 'ERROR' => 'ERROR');
            break;
        default:
            $labels = array();
            break;
    }
    return $labels;
}
