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

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$paper_id = check_var('paperID', 'GET', true, false, true, param::INT);
$startdate = check_var('startdate', 'GET', true, false, true, param::SQLDATETIME);
$enddate = check_var('enddate', 'GET', true, false, true, param::SQLDATETIME);
$get_repyear = param::optional('repyear',null, param::INT, param::FETCH_GET);
$get_repcourse = param::optional('repcourse','%', param::TEXT, param::FETCH_GET);
$complete = param::optional('completerpt',null, param::INT, param::FETCH_GET);
$bind_types = array();
$queryParams[] = $paper_id;
$bind_types[] = "i";
if (!empty($get_repyear)) {
  $repyear_sql= "AND lm.year = ?";
  $queryParams[] = $repyear;
  $bind_types[] = "s";
} else {
  $repyear_sql = "";
}
if ($get_repcourse !== "%") {
  $repcourse_sql = "AND u.grade = ?";
  $queryParams[] = $get_repcourse;
  $bind_types[] = "s";
} else {
  $repcourse_sql = "";
}
$queryParams[] = $startdate;
$bind_types[] = "i";
$queryParams[] = $enddate;
$bind_types[] = "i";

// Capture the paper makeup.
$paper_buffer = array();
$question_no = 0;

$result = $mysqli->prepare("SELECT paper_title, q_id, q_type, paper_type FROM (papers, questions, properties) WHERE papers.paper=properties.property_id AND papers.question=questions.q_id AND papers.paper=? AND q_type!='info' ORDER BY screen, display_pos");
$result->bind_param('i', $paper_id);
$result->execute();
$result->bind_result($paper, $q_id, $q_type, $paper_type);
while ($result->fetch()) {
    $paper_buffer[$question_no]['ID'] = $q_id;
    $paper_buffer[$question_no]['type'] = $q_type;
    $paper_type = $paper_type;
    $question_no++;
}
$result->close();

header('Pragma: public');
header('Content-type: text/xml');
header("Content-Disposition: attachment; filename=" . str_replace(' ', '_', $paper) . ".xml");

$log_array = array();
$hits = 0;
$exclude = '';
if ($complete == 1) {
    $result = $mysqli->prepare("SELECT COUNT(question) AS question_no FROM papers WHERE paper=?");
    $result->bind_param('i', $paper_id);
    $result->execute();
    $result->bind_result($number_of_questions);
    $result->fetch();
    $result->close();
    $result = $mysqli->prepare("SELECT lm.userID, COUNT(l.id) AS answer_no 
        FROM log$paper_type l 
        INNER JOIN log_metadata lm 
        ON l.metadataID = lm.id 
        WHERE lm.paperID=? AND lm.started>=? AND lm.started<=? GROUP BY lm.userID");
    $result->bind_param('iii', $paper_id, $startdate, $enddate);
    $result->execute();
    $result->bind_result($tmp_username, $answer_no);
    while ($result->fetch()) {
        if ($answer_no < $number_of_questions or $answer_no > $number_of_questions) {
            $exclude .= ' AND lm.userID != "' . $tmp_username . '"';
        }
    }
    $result->close();
}


// Capture the log data first.
$user_no = 0;
$sql = <<< SQL
SELECT l.q_id, u.grade, DATE_FORMAT(lm.started,"%d/%m/%Y %T") AS started, lm.year, u.surname,
u.initials, u.title, REPLACE(l.user_answer,'"',"'") AS user_answer, lm.userID
FROM log3 l INNER JOIN log_metadata lm ON l.metadataID = lm.id
INNER JOIN users u ON lm.userID = u.id
WHERE lm.paperID = ? $repyear_sql $repcourse_sql AND (u.roles='Student' OR u.roles='graduate')$exclude
AND lm.started>= ? AND lm.started<= ? ORDER BY u.surname, u.initials
SQL;

$bind_types_str = implode('', $bind_types);
$result = $mysqli->prepare($sql);
$bind_arr = array_merge(array($bind_types_str), $queryParams);
$bind_values_ref = array();
foreach ($bind_arr as $key => $value) {
    $bind_values_ref[$key] = &$bind_arr[$key];
}
call_user_func_array(array($result, "bind_param"), $bind_values_ref);

$result->execute();
$result->bind_result($question_ID, $grade, $started, $year, $surname, $initials, $title, $user_answer, $user_ID);
while ($result->fetch()) {
    $log_array[$user_ID][$question_ID] = $user_answer;
    $log_array[$user_ID]['username'] = $user_ID;
    $log_array[$user_ID]['course'] = $grade;
    $log_array[$user_ID]['year'] = $year;
    $log_array[$user_ID]['started'] = $started;
    $log_array[$user_ID]['title'] = $title;
    $log_array[$user_ID]['surname'] = $surname;
    $log_array[$user_ID]['initials'] = $initials;
    $user_no++;
}
$result->close();
$mysqli->close();

$row_written = 0;
echo "<?xml version=\"1.0\"?>\n<document>\n";
foreach ($log_array as $individual) {
    $tmp_user_ID = $individual['username'];
    // Write out the raw data.
    echo "<user>\n";
    if ($paper_type < 3) {
        echo "<title>" . $individual['title'] . "</title>\n";
        echo "<lastname>" . $individual['surname'] . "</lastname>\n";
        echo "<initials>" . $individual['initials'] . "</initials>\n";
        echo "<username>" . $individual['username'] . "</username>\n";
    }
    echo "<course>" . $individual['course'] . "</course>\n<year>" . $individual['year'] . "</year>\n<submitted>" . $individual['started'] . "</submitted>\n";
    $Qno = 1;
    for ($i = 0; $i < $question_no; $i++) {
        $tmp_question_ID = $paper_buffer[$i]['ID'];
        switch ($paper_buffer[$i]['type']) {
            case 'blank':
            case 'matching':
                $sub_part = 1;
                $log_array[$tmp_user_ID][$tmp_question_ID] = $log_array[$tmp_user_ID][$tmp_question_ID];
                $tmp_answers = explode('$', $log_array[$tmp_user_ID][$tmp_question_ID]);
                foreach ($tmp_answers as $individual_answer) {
                    echo "<question no=\"$Qno.$sub_part\">$individual_answer</question>\n";
                    $sub_part++;
                }
                break;
            case 'matrix':
                $sub_part = 1;
                if (isset($log_array[$tmp_user_ID][$tmp_question_ID])) {
                    $tmp_answers = explode('|', $log_array[$tmp_user_ID][$tmp_question_ID]);
                    foreach ($tmp_answers as $individual_answer) {
                        echo "<question no=\"$Qno.$sub_part\">$individual_answer</question>\n";
                        $sub_part++;
                    }
                }
                break;
            case 'rank':
                $sub_part = 1;
                if (isset($log_array[$tmp_user_ID][$tmp_question_ID])) {
                    $tmp_answers = explode(',', $log_array[$tmp_user_ID][$tmp_question_ID]);
                    foreach ($tmp_answers as $individual_answer) {
                        if ($individual_answer != '') {
                            if ($individual_answer == '9990') {
                                echo "<question no=\"$Qno.$sub_part\">n/a</question>\n";
                            } else {
                                echo "<question no=\"$Qno.$sub_part\">$individual_answer</question>\n";
                            }
                            $sub_part++;
                        }
                    }
                }
                break;
            case 'hotspot':
                echo "<question no=\"$Qno\">";
                echo $log_array[$tmp_user_ID][$tmp_question_ID][0];
                echo "</question>\n";
                break;
            case 'dichotomous':
            case 'mrq':
                $sub_part = 1;
                $chars = strlen($log_array[$tmp_user_ID][$tmp_question_ID]);
                for ($char_pos = 0; $char_pos < $chars; $char_pos++) {
                    echo "<question no=\"$Qno.$sub_part\">";
                    echo substr($log_array[$tmp_user_ID][$tmp_question_ID], $char_pos, 1);
                    echo "</question>\n";
                    $sub_part++;
                }
                break;
            case 'textbox':
                echo "<question no=\"$Qno\">";
                if (isset($log_array[$tmp_user_ID][$tmp_question_ID])) {
                    $tmp_data = $log_array[$tmp_user_ID][$tmp_question_ID];
                    $buffer = '';
                    for ($character = 0; $character < strlen($tmp_data); $character++) {
                        if (ord($tmp_data{$character}) > 31 and ord($tmp_data{$character}) < 127) {
                            $buffer .= $tmp_data{$character};
                        }
                    }
                    echo htmlspecialchars($buffer);
                }
                echo "</question>\n";
                break;
            case 'mcq':
            case 'likert':
                echo "<question no=\"$Qno\">";
                if (isset($log_array[$tmp_user_ID][$tmp_question_ID])) {
                    echo $log_array[$tmp_user_ID][$tmp_question_ID];
                }
                echo "</question>\n";
                break;
        }
        $Qno++;
    }
    echo "</user>\n";
    $row_written++;
}
echo "</document>\n";
?>