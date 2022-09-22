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
 * @author Simon Wilkinson, Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

/**
 * Get quantitative data for survey reports
 * @param  integer $paper_id   ID of paper we're reporting on
 * @param  string  $course     Name of course
 * @param  string  $start_date Start date for report
 * @param  string  $end_date   End date for report
 * @param  boolean  $exclude   Exclude non completed user instances
 * @param  array   $log_array  Reference to array that will be populatesd with the data
 * @param  mysqli  $db         Database connection
 * @param integer $number_of_questions number of questions in paper
 * @return integer             Number of records we're returning
 */
function get_quantitative_log_data($paper_id, $course, $start_date, $end_date, $exclude, &$log_array, $db, $number_of_questions)
{
    $excluded = '';
    if ($exclude) {
        $result = $db->prepare("SELECT 
      lm.userID,
      COUNT(l.id) AS answer_no
    FROM
      log3 l,
      log_metadata lm
    WHERE
      l.metadataID = lm.id
      AND lm.paperID = ?
      AND lm.started >= ?
      AND lm.started <= ?
      AND l.user_answer NOT IN ('u', '')
    GROUP BY lm.userID
    HAVING answer_no <> ?");
        $result->bind_param('issi', $paper_id, $paper_id, $end_date, $number_of_questions);
        $result->execute();
        $result->bind_result($tmp_userid, $answer_no);
        $excludedusers = array();
        while ($result->fetch()) {
            $excludedusers[] = $tmp_userid;
        }
        $result->close();
        if (count($excludedusers) > 0) {
            $excluded = 'AND lm.userID NOT IN (' . implode(',', $excludedusers) . ') ';
        }
    }
    $hits = 0;
    // Capture the log data first.
    $sql = <<< SQL
SELECT DISTINCT lm.userID, l.q_id, l.user_answer, q.q_type, l.screen, q.score_method
FROM log3 l INNER JOIN log_metadata lm ON l.metadataID = lm.id
INNER JOIN questions q ON l.q_id = q.q_id
INNER JOIN users u on lm.userID = u.id,
user_roles ur JOIN roles r ON ur.roleid = r.id
WHERE lm.paperID = ?
AND u.id = ur.userid
AND r.name IN ('Student', 'graduate')
$excluded
AND u.grade LIKE ?
AND lm.started >= ? AND lm.started <= ?
SQL;
    $result = $db->prepare($sql);
    $result->bind_param('isss', $paper_id, $course, $start_date, $end_date);
    $result->execute();
    $result->bind_result($tmp_userID, $question_ID, $tmp_answer, $q_type, $screen, $score_method);
    $result->store_result();
    $hits = $result->num_rows;
    while ($result->fetch()) {
        $tmp_answer = str_replace('&', '&amp;', $tmp_answer);
        switch ($q_type) {
            case 'blank':
                $tmp_answer_parts = array();
                $tmp_answer_parts = json_decode($tmp_answer);
                $i = 0;
                foreach ($tmp_answer_parts as $tmp_individual_answer) {
                    $i++;
                    if ($tmp_individual_answer == 'u') {
                        if (isset($log_array[$screen][$question_ID][$i]['u'])) {
                            $log_array[$screen][$question_ID][$i]['u']++;
                        } else {
                            $log_array[$screen][$question_ID][$i]['u'] = 1;
                        }
                    } else {
                        if (isset($log_array[$screen][$question_ID][$i][$tmp_individual_answer])) {
                              $log_array[$screen][$question_ID][$i][$tmp_individual_answer]++;
                        } else {
                              $log_array[$screen][$question_ID][$i][$tmp_individual_answer] = 1;
                        }
                    }
                }
                break;
            case 'enhancedcalc':
                $tmp_score_method = array();
                $tmp_score_method = explode(',', $score_method);
                $tolerance = $tmp_score_method[1];
                $tmp_first_split = explode('|', $tmp_answer);
                if ($tmp_first_split[0] == $tmp_first_split[1]) {
                    if (isset($log_array[$screen][$question_ID][1]['correct'])) {
                        $log_array[$screen][$question_ID][1]['correct']++;
                    } else {
                        $log_array[$screen][$question_ID][1]['correct'] = 1;
                    }
                } else {
                    if ($tmp_first_split[0] == '') {
                        if (isset($log_array[$screen][$question_ID][1]['u'])) {
                            $log_array[$screen][$question_ID][1]['u']++;
                        } else {
                            $log_array[$screen][$question_ID][1]['u'] = 1;
                        }
                    } elseif (abs($tmp_first_split[0] - $tmp_first_split[1]) <= $tolerance) {
                        if (isset($log_array[$screen][$question_ID][1]['tolerance'])) {
                            $log_array[$screen][$question_ID][1]['tolerance']++;
                        } else {
                            $log_array[$screen][$question_ID][1]['tolerance'] = 1;
                        }
                    } else {
                        if (isset($log_array[$screen][$question_ID][1]['incorrect'])) {
                            $log_array[$screen][$question_ID][1]['incorrect']++;
                        } else {
                            $log_array[$screen][$question_ID][1]['incorrect'] = 1;
                        }
                    }
                }
                break;
            case 'dichotomous':
                for ($i = 0; $i < mb_strlen($tmp_answer); $i++) {
                    $tmp_individual_answer = mb_substr($tmp_answer, $i, 1);
                    if (isset($log_array[$screen][$question_ID][$i + 1][$tmp_individual_answer])) {
                        $log_array[$screen][$question_ID][$i + 1][$tmp_individual_answer]++;
                    } else {
                        $log_array[$screen][$question_ID][$i + 1][$tmp_individual_answer] = 1;
                    }
                }
                break;
            case 'labelling':
                $tmp_first_split = explode(';', $tmp_answer);
                $tmp_second_split = explode('|', $tmp_first_split[1]);
                $sections = count($tmp_second_split);
                for ($i = 2; $i <= count($tmp_second_split); $i += 4) {
                    $x_coord = $tmp_second_split[$i - 2];
                    $y_coord = $tmp_second_split[$i - 1];
                    $tmp_individual_answer = trim($tmp_second_split[$i]);
                    $element = $x_coord . 'x' . $y_coord;
                    if (isset($log_array[$screen][$question_ID][$element][$tmp_individual_answer])) {
                        $log_array[$screen][$question_ID][$element][$tmp_individual_answer]++;
                    } else {
                        $log_array[$screen][$question_ID][$element][$tmp_individual_answer] = 1;
                    }
                }
                break;
            case 'likert':
                if (isset($log_array[$screen][$question_ID][1][$tmp_answer])) {
                    $log_array[$screen][$question_ID][1][$tmp_answer]++;
                } else {
                    $log_array[$screen][$question_ID][1][$tmp_answer] = 1;
                }
                break;
            case 'hotspot':
                if (mb_substr($tmp_answer, 0, 1) == '1') {
                    if (isset($log_array[$screen][$question_ID][1]['1'])) {
                        $log_array[$screen][$question_ID][1]['1']++;
                    } else {
                        $log_array[$screen][$question_ID][1]['1'] = 1;
                    }
                } elseif (mb_substr($tmp_answer, 0, 1) == '0') {
                    if (isset($log_array[$screen][$question_ID][1]['0'])) {
                        $log_array[$screen][$question_ID][1]['0']++;
                    } else {
                        $log_array[$screen][$question_ID][1]['0'] = 1;
                    }
                } else {
                    if (isset($log_array[$screen][$question_ID][1]['u'])) {
                        $log_array[$screen][$question_ID][1]['u']++;
                    } else {
                        $log_array[$screen][$question_ID][1]['u'] = 1;
                    }
                }
                if ($log_array[$screen][$question_ID][1]['coords'] == '') {
                    $log_array[$screen][$question_ID][1]['coords'] = mb_substr($tmp_answer, 2);
                } else {
                    $log_array[$screen][$question_ID][1]['coords'] .= ';' . mb_substr($tmp_answer, 2);
                }
                break;
            case 'mcq':
                if (mb_substr($tmp_answer, 0, 5) == 'other') {
                    $log_array[$screen][$question_ID][1]['other'][] = mb_substr($tmp_answer, 6);
                } elseif ($tmp_answer == 0) {
                    if (isset($log_array[$screen][$question_ID][1]['u'])) {
                        $log_array[$screen][$question_ID][1]['u']++;
                    } else {
                        $log_array[$screen][$question_ID][1]['u'] = 1;
                    }
                } else {
                    if (isset($log_array[$screen][$question_ID][1][$tmp_answer])) {
                        $log_array[$screen][$question_ID][1][$tmp_answer]++;
                    } else {
                        $log_array[$screen][$question_ID][1][$tmp_answer] = 1;
                    }
                }
                break;
            case 'mrq':
                $result2 = $db->prepare('SELECT COUNT(o_id) AS tmp_option_no FROM options WHERE o_id=?');
                $result2->bind_param('i', $question_ID);
                $result2->execute();
                $result2->bind_result($tmp_option_no);
                $result2->store_result();
                $result2->fetch();
                $result2->close();

                for ($i = 0; $i < $tmp_option_no; $i++) {
                    $tmp_individual_answer = mb_substr($tmp_answer, $i, 1);
                    if (isset($log_array[$screen][$question_ID][$i + 1][$tmp_individual_answer])) {
                        $log_array[$screen][$question_ID][$i + 1][$tmp_individual_answer]++;
                    } else {
                        $log_array[$screen][$question_ID][$i + 1][$tmp_individual_answer] = 1;
                    }
                }

                if (mb_strlen($tmp_answer) > $tmp_option_no) {
                    $other = mb_substr($tmp_answer, $tmp_option_no + 1);

                    if (isset($log_array[$screen][$question_ID][$i + 1][$other])) {
                        $log_array[$screen][$question_ID][$i + 1][$other]++;
                    } else {
                        $log_array[$screen][$question_ID][$i + 1][$other] = 1;
                    }
                }

                break;
            case 'extmatch':
                $tmp_answer_parts = array();
                $tmp_answer_parts = explode('|', $tmp_answer);
                $i = 0;
                foreach ($tmp_answer_parts as $tmp_individual_answer) {
                    $i++;
                    $tmp_sub_parts = array();
                    $tmp_sub_parts = explode('|', $tmp_individual_answer);
                    foreach ($tmp_sub_parts as $tmp_individual_part) {
                        if ($tmp_individual_answer == 'u') {
                            if (isset($log_array[$screen][$question_ID][$i]['u'])) {
                                $log_array[$screen][$question_ID][$i]['u']++;
                            } else {
                                $log_array[$screen][$question_ID][$i]['u'] = 1;
                            }
                        } else {
                            if (isset($log_array[$screen][$question_ID][$i][$tmp_individual_part])) {
                                $log_array[$screen][$question_ID][$i][$tmp_individual_part]++;
                            } else {
                                $log_array[$screen][$question_ID][$i][$tmp_individual_part] = 1;
                            }
                        }
                    }
                }
                break;
            case 'matrix':
                $tmp_answer_parts = array();
                $tmp_answer_parts = explode('|', $tmp_answer);
                $i = 0;
                foreach ($tmp_answer_parts as $tmp_individual_answer) {
                    $i++;
                    if ($tmp_individual_answer == 'u' or $tmp_individual_answer == '') {
                        if (isset($log_array[$screen][$question_ID][$i]['u'])) {
                            $log_array[$screen][$question_ID][$i]['u']++;
                        } else {
                            $log_array[$screen][$question_ID][$i]['u'] = 1;
                        }
                    } else {
                        if (isset($log_array[$screen][$question_ID][$i][$tmp_individual_answer])) {
                            $log_array[$screen][$question_ID][$i][$tmp_individual_answer]++;
                        } else {
                            $log_array[$screen][$question_ID][$i][$tmp_individual_answer] = 1;
                        }
                    }
                }
                break;
            case 'rank':
                $tmp_answer_parts = array();
                $tmp_answer_parts = explode(',', $tmp_answer);
                $i = 0;
                foreach ($tmp_answer_parts as $tmp_individual_answer) {
                    if ($tmp_individual_answer == '9999') {
                        if (isset($log_array[$screen][$question_ID][$i]['u'])) {
                            $log_array[$screen][$question_ID][$i]['u']++;
                        } else {
                            $log_array[$screen][$question_ID][$i]['u']++;
                        }
                    } else {
                        if (isset($log_array[$screen][$question_ID][$i][$tmp_individual_answer])) {
                            $log_array[$screen][$question_ID][$i][$tmp_individual_answer]++;
                        } else {
                            $log_array[$screen][$question_ID][$i][$tmp_individual_answer] = 1;
                        }
                    }
                    $i++;
                }
                break;
            case 'textbox':
                $log_array[$screen][$question_ID][1]['other'][] = $tmp_answer;
                break;
            case 'timedate':
                $log_array[$screen][$question_ID][1]['other'][] = $tmp_answer;
                break;
        }
    }
    $result->close();

    return $hits;
}
