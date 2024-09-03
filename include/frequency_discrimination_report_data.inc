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
 * Data gathering functions for Frequency and Discrimination Analysis report
 *
 * @author    Simon Wilkinson
 * @author    Richard Aspden
 * @version   1.1
 * @copyright Copyright (c) 2014-2020 The University of Nottingham
 * @package
*/

/**
 * Calculate the 'Pearson product-moment correlation coefficient'
 * https://examsys-eassessment-docs.atlassian.net/wiki/pages/viewpage.action?pageId=1049586
 * https://en.wikipedia.org/wiki/Pearson_product-moment_correlation_coefficient
 *
 * @param int   $no_students The number of users in each cohort
 * @param array $top_log_q_id The answers for a single question given by the high performing users.
 * @param array $bottom_log_q_id The answers for a single question given by the low performing users.
 * @param mixed $i The identifier for the part of the question being analysed.
 * @param mixed $keys The correct answer, or an array of correct answers for the part.
 *
 * @return string
 */
function calcDiscrimination($no_students, &$top_log_q_id, &$bottom_log_q_id, $i, $keys)
{
    $top_key_value = 0;
    $bottom_key_value = 0;

    if (!is_array($keys)) {
        $keys = array($keys);
    }

    foreach ($keys as $key) {
        if (isset($top_log_q_id[$i][$key])) {
            $top_key_value += $top_log_q_id[$i][$key];
        }
        if (isset($bottom_log_q_id[$i][$key])) {
            $bottom_key_value += $bottom_log_q_id[$i][$key];
        }
    }

    $top_ratio = $top_key_value / $no_students;
    $bottom_ratio = $bottom_key_value / $no_students;

    return number_format($top_ratio - $bottom_ratio, 2);
}

/**
 * Calculate question difficulty statistics (p)
 *
 * @param float    $value        Percentage of marks scored (marks / total marks)
 * @param int      $qid          Question ID
 * @param int      $part_no      Question sub-part
 * @param mixed[]  $pstats       Global p statistics summary array
 * @param mixed[]  $pstats_array Full p stats array
 *
 * @return string
 */
function pStats(float $value, int $qid, int $part_no, array &$pstats, array &$pstats_array)
{
    if ($value > 1) {
        // The pstats of a question that has a total number of marks that is not a whole number can be wrong
        // because the stored total truncates it to an integer, this means that sometimes we might get invalid values.
        // Making the value something that is obviously wrong, but will not overflow the database.
        $value = 1.1;
    }
    if ($value >= 0.8) {
        $pstats['ve']++;
    } elseif ($value >= 0.6 and $value < 0.8) {
        $pstats['e']++;
    } elseif ($value >= 0.4 and $value < 0.6) {
        $pstats['m']++;
    } elseif ($value >= 0.2 and $value < 0.4) {
        $pstats['h']++;
    } else {
        $pstats['vh']++;
    }
    if (isset($pstats['total'])) {
        $pstats['total'] += $value;
    } else {
        $pstats['total'] = $value;
    }
    if (isset($pstats['no'])) {
        $pstats['no']++;
    } else {
        $pstats['no'] = 1;
    }

    $pstats_array[$qid][$part_no] = round($value, 2) * 100;

    return $value;
}

/**
 * Calculate discrimination value (d)
 *
 * @param float    $value        Percentage of marks scored (marks / total marks)
 * @param int      $qid          Question ID
 * @param int      $part_no      Question sub-part
 * @param mixed[]  $dstats       Global d statistics summary array
 * @param mixed[]  $dstats_array Full d stats array
 *
 * @return string
 */
function dStats(float $value, int $qid, int $part_no, array &$dstats, array &$dstats_array)
{
    if ($value >= 0.35) {
        $dstats['highest']++;
    } elseif ($value >= 0.25 and $value < 0.35) {
        $dstats['high']++;
    } elseif ($value >= 0.15 and $value < 0.25) {
        $dstats['intermediate']++;
    } else {
        $dstats['low']++;
    }
    if (isset($dstats['total'])) {
        $dstats['total'] += $value;
    } else {
        $dstats['total'] = 1;
    }
    if (isset($dstats['no'])) {
        $dstats['no']++;
    } else {
        $dstats['no'] = 1;
    }

    $dstats_array[$qid][$part_no] = round($value, 2) * 100;

    return $value;
}

/**
 * Calculate unanswered question statistics
 *
 * @param string          $q_type       Question type
 * @param int             $q_id         Question ID
 * @param array           $freq_log     Frequency log array for processing
 * @param int             $user_total   User total
 * @param int|string|null $part_no      Question sub-part (optional)
 *
 * @return int[]|null[]
 */
function xStats(string &$q_type, int &$q_id, array &$freq_log, int &$user_total, $part_no = 1)
{
    if ($user_total == 0) {
        return array('percent' => null, 'count' => null);
    }
    switch ($q_type) {
        case 'area':
        case 'enhancedcalc':
        case 'mcq':
        case 'mrq':
        case 'textbox':
            $unanswered_count = (isset($freq_log[$q_id]['u'])) ? $freq_log[$q_id]['u'] : 0;
            $unanswered_percent = ($unanswered_count / $user_total) * 100;
            break;
        case 'dichotomous':
        case 'extmatch':
        case 'blank':
        case 'likert':
        case 'sct':
        case 'matrix':
        case 'true_false':
            $unanswered_count = (isset($freq_log[$q_id][$part_no]['u'])) ? $freq_log[$q_id][$part_no]['u'] : 0;
            $unanswered_percent = ($unanswered_count / $user_total) * 100;
            break;
        case 'labelling':
            $unanswered_count = $user_total - $freq_log[$q_id]['answercount'][$part_no];
            $unanswered_percent = ($unanswered_count / $user_total) * 100;
            break;
        case 'hotspot':
            $unanswered_count = (isset($freq_log[$q_id][$part_no]['u'])) ? $freq_log[$q_id][$part_no]['u'] : 0; // Layer unanswered
            $unanswered_count += (isset($freq_log[$q_id]['u'])) ? $freq_log[$q_id]['u'] : 0; // Question unanswered
            $unanswered_percent = ($unanswered_count / $user_total) * 100;
            break;
        case 'rank':
            $unanswered_count = (isset($freq_log[$q_id][$part_no]['u'])) ? $freq_log[$q_id][$part_no]['u'] : 0;
            $unanswered_percent = ($unanswered_count / $user_total) * 100;
            break;
        default:
            throw new \Exception('Unhandled question type in unanswered stats function ' . $q_type);
    }

    return array('percent' => $unanswered_percent, 'count' => $unanswered_count);
}


/**
 * Store data in global log array
 *
 * @param mixed[]  $log_array     Marking log array
 * @param int      $qID           Question ID
 * @param string   $answer        Submitted answer
 * @param string   $q_type        Question type
 * @param string   $display       Display method
 * @param string   $settings      JSON settings array
 * @param float    $mark          Mark awarded
 * @param int      $totalpos      Total marks available
 * @param string   $analysis_type 'all', 'top' or 'bottom'
 */
function storeData(&$log_array, $qID, $answer, $q_type, $display, $settings, $mark, $totalpos, $analysis_type)
{
    $stop_words = array('-', 'a', 'about', 'above', 'across', 'after', 'again', 'against', 'all', 'almost', 'alone', 'along', 'already', 'also', 'although', 'always', 'among', 'an', 'and', 'another', 'any', 'anybody', 'anyone', 'anything', 'anywhere', 'are', 'area', 'areas', 'around', 'as', 'ask', 'asked', 'asking', 'asks', 'at', 'away', 'b', 'back', 'backed', 'backing', 'backs', 'be', 'became', 'because', 'become', 'becomes', 'been', 'before', 'began', 'behind', 'being', 'beings', 'best', 'better', 'between', 'big', 'both', 'but', 'by', 'c', 'came', 'can', 'cannot', 'case', 'cases', 'certain', 'certainly', 'clear', 'clearly', 'come', 'could', 'd', 'did', 'differ', 'different', 'differently', 'do', 'does', 'done', 'down', 'downed', 'downing', 'downs', 'during', 'e', 'each', 'early', 'either', 'end', 'ended', 'ending', 'ends', 'enough', 'even', 'evenly', 'ever', 'every', 'everybody', 'everyone', 'everything', 'everywhere', 'f', 'face', 'faces', 'fact', 'facts', 'far', 'felt', 'few', 'find', 'finds', 'first', 'for', 'four', 'from', 'full', 'fully', 'further', 'furthered', 'furthering', 'furthers', 'g', 'gave', 'general', 'generally', 'get', 'gets', 'give', 'given', 'gives', 'go', 'going', 'good', 'goods', 'got', 'great', 'greater', 'greatest', 'group', 'grouped', 'grouping', 'groups', 'h', 'had', 'has', 'have', 'having', 'he', 'her', 'here', 'herself', 'high', 'higher', 'highest', 'him', 'himself', 'his', 'how', 'however', 'i', 'if', 'important', 'in', 'interest', 'interested', 'interesting', 'interests', 'into', 'is', 'it', 'its', 'itself', 'j', 'just', 'k', 'keep', 'keeps', 'kind', 'knew', 'know', 'known', 'knows', 'l', 'large', 'largely', 'last', 'later', 'latest', 'least', 'less', 'let', 'lets', 'like', 'likely', 'long', 'longer', 'longest', 'm', 'made', 'make', 'making', 'man', 'many', 'may', 'me', 'member', 'members', 'men', 'might', 'more', 'most', 'mostly', 'mr', 'mrs', 'much', 'must', 'my', 'myself', 'n', 'necessary', 'need', 'needed', 'needing', 'needs', 'never', 'new', 'newer', 'newest', 'next', 'no', 'nobody', 'non', 'noone', 'not', 'nothing', 'now', 'nowhere', 'number', 'numbers', 'o', 'of', 'off', 'often', 'old', 'older', 'oldest', 'on', 'once', 'one', 'only', 'open', 'opened', 'opening', 'opens', 'or', 'order', 'ordered', 'ordering', 'orders', 'other', 'others', 'our', 'out', 'over', 'p', 'part', 'parted', 'parting', 'parts', 'per', 'perhaps', 'place', 'places', 'point', 'pointed', 'pointing', 'points', 'possible', 'present', 'presented', 'presenting', 'presents', 'problem', 'problems', 'put', 'puts', 'q', 'quite', 'r', 'rather', 'really', 'right', 'room', 'rooms', 's', 'said', 'same', 'saw', 'say', 'says', 'second', 'seconds', 'see', 'seem', 'seemed', 'seeming', 'seems', 'sees', 'several', 'shall', 'she', 'should', 'show', 'showed', 'showing', 'shows', 'side', 'sides', 'since', 'small', 'smaller', 'smallest', 'so', 'some', 'somebody', 'someone', 'something', 'somewhere', 'state', 'states', 'still', 'such', 'sure', 't', 'take', 'taken', 'than', 'that', 'the', 'their', 'them', 'then', 'there', 'therefore', 'these', 'they', 'thing', 'things', 'think', 'thinks', 'this', 'those', 'though', 'thought', 'thoughts', 'three', 'through', 'thus', 'to', 'today', 'together', 'too', 'took', 'toward', 'turn', 'turned', 'turning', 'turns', 'two', 'u', 'under', 'until', 'up', 'upon', 'us', 'use', 'used', 'uses', 'v', 'very', 'w', 'want', 'wanted', 'wanting', 'wants', 'was', 'way', 'ways', 'we', 'well', 'wells', 'went', 'were', 'what', 'when', 'where', 'whether', 'which', 'while', 'who', 'whole', 'whose', 'why', 'will', 'with', 'within', 'without', 'work', 'worked', 'working', 'works', 'would', 'x', 'y', 'year', 'years', 'yet', 'you', 'young', 'younger', 'youngest', 'your', 'yours', 'z');

    if (!isset($log_array[$qID]['mark'])) {
        $log_array[$qID]['mark'] = 0;
    }
    if (!isset($log_array[$qID]['totalpos'])) {
        $log_array[$qID]['totalpos'] = 0;
    }

    switch ($q_type) {
        case 'area':
            if ($mark == $totalpos) {
                if (isset($log_array[$qID]['correct'])) {
                    $log_array[$qID][1]['correct']++;
                } else {
                    $log_array[$qID][1]['correct'] = 1;
                }
            } elseif ($mark < $totalpos and $mark > 0) {
                if (isset($log_array[$qID]['partial'])) {
                    $log_array[$qID][1]['partial']++;
                } else {
                    $log_array[$qID][1]['partial'] = 1;
                }
            } else {
                if (isset($log_array[$qID]['incorrect'])) {
                    $log_array[$qID][1]['incorrect']++;
                } else {
                    $log_array[$qID][1]['incorrect'] = 1;
                }
            }
            $log_array[$qID]['mark'] += $mark;
            $log_array[$qID]['totalpos'] += $totalpos;
            if (mb_substr($answer, -1) == ';') {
                if (isset($log_array[$qID]['u'])) {
                    $log_array[$qID]['u']++;
                } else {
                    $log_array[$qID]['u'] = 1;
                }
            }
            break;
        case 'blank':
            $tmp_answer_parts = array();
            $tmp_answer_parts = json_decode($answer);
            $i = 0;
            foreach ($tmp_answer_parts as $tmp_individual_answer) {
                $tmp_individual_answer = mb_strtolower(trim($tmp_individual_answer));
                $i++;
                if ($tmp_individual_answer == 'u') {
                    if (isset($log_array[$qID][$i]['u'])) {
                        $log_array[$qID][$i]['u']++;
                    } else {
                        $log_array[$qID][$i]['u'] = 1;
                    }
                } else {
                    if (isset($log_array[$qID][$i][$tmp_individual_answer])) {
                        $log_array[$qID][$i][$tmp_individual_answer]++;
                    } else {
                        $log_array[$qID][$i][$tmp_individual_answer] = 1;
                    }
                }
            }
            break;
        case 'enhancedcalc':
            $configObj = Config::get_instance();
            $calc = new enhancedcalc($configObj);
            $calc->set_settings($settings);
            $calc->set_useranswer($answer);

            if ($calc->is_user_ans_correct() or $calc->is_user_ans_within_fullmark_tolerance()) {
                if (isset($log_array[$qID][1]['correct'])) {
                    $log_array[$qID][1]['correct']++;
                } else {
                    $log_array[$qID][1]['correct'] = 1;
                }
            }

            if (trim($calc->get_user_answer_raw()) == '') {
                if (isset($log_array[$qID]['u'])) {
                    $log_array[$qID]['u']++;
                } else {
                    $log_array[$qID]['u'] = 1;
                }
            }

            $log_array[$qID]['mark'] += $mark;
            $log_array[$qID]['totalpos'] += $totalpos;
            break;
        case 'dichotomous':
        case 'true_false':
            $count_answer = strlen($answer);
            for ($i = 0; $i < $count_answer; $i++) {
                $tmp_individual_answer = $answer[$i];
                if (isset($log_array[$qID][$i + 1][$tmp_individual_answer])) {
                    $log_array[$qID][$i + 1][$tmp_individual_answer]++;
                } else {
                    $log_array[$qID][$i + 1][$tmp_individual_answer] = 1;
                }
            }
            break;
        case 'labelling':
            $tmp_first_split = explode(';', $answer);
            $tmp_second_split = explode('$', $tmp_first_split[1]);
            $count_tmp_second_split = count($tmp_second_split);
            for ($i = 2; $i <= $count_tmp_second_split; $i += 4) {
                $x_coord = $tmp_second_split[$i - 2];
                $y_coord = $tmp_second_split[$i - 1];
                $tmp_individual_answer = trim($tmp_second_split[$i]);
                if (!isset($log_array[$qID]['answercount'][$tmp_individual_answer])) {
                    $log_array[$qID]['answercount'][$tmp_individual_answer] = 1;
                } else {
                    $log_array[$qID]['answercount'][$tmp_individual_answer]++;
                }
                $element = $x_coord . 'x' . $y_coord;
                if (isset($log_array[$qID][$element][$tmp_individual_answer])) {
                    $log_array[$qID][$element][$tmp_individual_answer]++;
                } else {
                    $log_array[$qID][$element][$tmp_individual_answer] = 1;
                }
            }
            break;
        case 'hotspot':
            if ($answer == 'u') {
                if (isset($log_array[$qID]['u'])) {
                    $log_array[$qID]['u']++;
                } else {
                    $log_array[$qID]['u'] = 1;
                }
            } else {
                $layer_answers = explode('|', $answer);

                $layer = 1;
                foreach ($layer_answers as $layer_answer) {
                    $layer_answer_index = mb_substr($layer_answer, 0, 1);
                    if ($layer_answer_index == '0' or $layer_answer_index == '1') {
                        if ($layer_answer_index == '1') {
                            if (isset($log_array[$qID][$layer]['1'])) {
                                $log_array[$qID][$layer]['1']++;
                            } else {
                                $log_array[$qID][$layer]['1'] = 1;
                            }
                        } elseif ($layer_answer_index == '0') {
                            if (isset($log_array[$qID][$layer]['0'])) {
                                $log_array[$qID][$layer]['0']++;
                            } else {
                                $log_array[$qID][$layer]['0'] = 1;
                            }
                        }
                    } else {
                        if (isset($log_array[$qID][$layer]['u'])) {
                            $log_array[$qID][$layer]['u']++;
                        } else {
                            $log_array[$qID][$layer]['u'] = 1;
                        }
                    }
                    if (!isset($log_array[$qID][$layer]['coords'])) {
                        $log_array[$qID][$layer]['coords'] = $layer_answer;
                    } else {
                        $log_array[$qID][$layer]['coords'] .= ';' . $layer_answer;
                    }
                    $layer++;
                }
            }
            break;
        case 'mcq':
            if ($answer == '0') {
                if (isset($log_array[$qID]['u'])) {
                    $log_array[$qID]['u']++;
                } else {
                    $log_array[$qID]['u'] = 1;
                }
            } elseif (isset($log_array[$qID][1][$answer])) {
                $log_array[$qID][1][$answer]++;
            } else {
                $log_array[$qID][1][$answer] = 1;
            }
            break;
        case 'mrq':
            if ($answer == 'a') {
                if (isset($log_array[$qID]['a'])) {
                    $log_array[$qID]['a']++;
                } else {
                    $log_array[$qID]['a'] = 1;
                }
            } elseif (mb_strpos($answer, 'y') === false) {
                if (isset($log_array[$qID]['u'])) {
                    $log_array[$qID]['u']++;
                } else {
                    $log_array[$qID]['u'] = 1;
                }
            } else {
                $count_answer = strlen($answer);
                if (strpos($answer, 'y') === false) {
                    // force unanswered on all
                    $answer = str_repeat('u', $count_answer);
                }
                for ($i = 0; $i < $count_answer; $i++) {
                    $tmp_individual_answer = $answer[$i];
                    if (isset($log_array[$qID][$i + 1][$tmp_individual_answer])) {
                        $log_array[$qID][$i + 1][$tmp_individual_answer]++;
                    } else {
                        $log_array[$qID][$i + 1][$tmp_individual_answer] = 1;
                    }
                }
            }
            $log_array[$qID]['mark'] += $mark;
            $log_array[$qID]['totalpos'] += $totalpos;
            break;
        case 'extmatch':
            $tmp_answer_parts = array();
            $tmp_answer_parts = explode('|', $answer);
            $i = 0;
            foreach ($tmp_answer_parts as $tmp_individual_answer) {
                $i++;
                $tmp_sub_parts = array();
                $tmp_sub_parts = explode('$', $tmp_individual_answer);
                foreach ($tmp_sub_parts as $tmp_individual_part) {
                    if ($tmp_individual_answer == 'u' or $tmp_individual_answer == '') {
                        if (isset($log_array[$qID][$i]['u'])) {
                            $log_array[$qID][$i]['u']++;
                        } else {
                            $log_array[$qID][$i]['u'] = 1;
                        }
                    } else {
                        if (isset($log_array[$qID][$i][$tmp_individual_part])) {
                            $log_array[$qID][$i][$tmp_individual_part]++;
                        } else {
                            $log_array[$qID][$i][$tmp_individual_part] = 1;
                        }
                    }
                }
            }
            break;
        case 'matrix':
            $tmp_answer_parts = explode('|', $answer);
            $count_tmp_answer_parts = count($tmp_answer_parts);
            for ($i = 0; $i < $count_tmp_answer_parts; $i++) {
                $tmp_individual_answer = $tmp_answer_parts[$i];

                if ($tmp_individual_answer == 'u' or $tmp_individual_answer == '') {
                    if (isset($log_array[$qID][$i + 1]['u'])) {
                        $log_array[$qID][$i + 1]['u']++;
                    } else {
                        $log_array[$qID][$i + 1]['u'] = 1;
                    }
                } else {
                    if (isset($log_array[$qID][$i + 1][$tmp_individual_answer])) {
                        $log_array[$qID][$i + 1][$tmp_individual_answer]++;
                    } else {
                        $log_array[$qID][$i + 1][$tmp_individual_answer] = 1;
                    }
                }
            }
            break;
        case 'rank':
            $tmp_answer_parts = array();
            $tmp_answer_parts = explode(',', $answer);
            $i = 0;
            foreach ($tmp_answer_parts as $tmp_individual_answer) {
                if (isset($log_array[$qID][$i][$tmp_individual_answer])) {
                    $log_array[$qID][$i][$tmp_individual_answer]++;
                } else {
                    $log_array[$qID][$i][$tmp_individual_answer] = 1;
                }
                $i++;
            }
            if ($mark == $totalpos) {
                if (isset($log_array[$qID]['all_correct'])) {
                    $log_array[$qID]['all_correct']++;
                } else {
                    $log_array[$qID]['all_correct'] = 1;
                }
            }
            $log_array[$qID]['mark'] += $mark;
            $log_array[$qID]['totalpos'] += $totalpos;
            break;
        case 'sct':
            if ($answer == 0) {
                $answer = 'u';
            }
            if (isset($log_array[$qID][1][$answer])) {
                $log_array[$qID][1][$answer]++;
            } else {
                $log_array[$qID][1][$answer] = 1;
            }
            $log_array[$qID]['mark'] += $mark;
            $log_array[$qID]['totalpos'] += $totalpos;
            break;
        case 'textbox':
            if (trim($answer) == false) {
                if (isset($log_array[$qID]['u'])) {
                    $log_array[$qID]['u']++;
                } else {
                    $log_array[$qID]['u'] = 1;
                }
            }

            if ($analysis_type == 'top' or $analysis_type == 'bottom') {
                $user_words = str_word_count($answer, 1);
                foreach ($user_words as $word) {
                    $word = mb_strtolower($word);
                    if (!in_array($word, $stop_words)) {
                        if (isset($log_array[$qID]['words'][$word])) {
                            $log_array[$qID]['words'][$word]++;
                        } else {
                            $log_array[$qID]['words'][$word] = 1;
                        }
                    }
                }
            }

            // Implement t/u/l for textbox questions. Indexes cast to strings to avoid bad PHP auto-conversion for numbers to integers on arrays
            $mark_for_distrib = is_null($mark) ? 'unmarked' : number_format($mark, 1);
            if (isset($log_array[$qID][1][(string)$mark_for_distrib])) {
                $log_array[$qID][1][(string)$mark_for_distrib]++;
            } else {
                $log_array[$qID][1][(string)$mark_for_distrib] = 1;
            }

            if (isset($user_words)) {
                if (isset($log_array[$qID]['word_count'])) {
                    $log_array[$qID]['word_count'] += count($user_words);
                } else {
                    $log_array[$qID]['word_count'] = count($user_words);
                }
            }

            $log_array[$qID]['mark'] += $mark;
            if (is_null($mark)) {
                if (isset($log_array[$qID]['unmarked'])) {
                    $log_array[$qID]['unmarked']++;
                } else {
                    $log_array[$qID]['unmarked'] = 1;
                }
            }
            $log_array[$qID]['totalpos'] += $totalpos;
            break;
        case 'likert':
            if (isset($log_array[$qID][1][$answer])) {
                $log_array[$qID][1][$answer]++;
            } else {
                $log_array[$qID][1][$answer] = 1;
            }
            break;
    }
}

/**
 * Clear and recalculate performance stats
 *
 * @param \mysqli  $mysqli MySQL object
 * @param int      $paperID      Paper ID
 * @param string   $started      Started date
 * @param int      $percent      Percentage of cohort?
 * @param mixed[]  $pstats       P statistics
 * @param mixed[]  $dstats       D statistics
 * @param mixed[]  $pstats_array P statistics array (how is this different?)
 * @param mixed[]  $dstats_array D statistics array (how is this different?)
 * @param int      $user_total   Total users that took the paper
 * @param string[] $string       Language strings
 */
function refresh_performance_stats(\mysqli $mysqli, int $paperID, string $started, int $percent, array &$pstats, array &$dstats, array &$pstats_array, array &$dstats_array, int $user_total, array $string)
{
    $id_list = array();
    $result = $mysqli->prepare('SELECT id FROM performance_main WHERE paperID = ?');

    if ($mysqli->error) {
        echo $string['showerror'] . '<br >';
    }
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($id);
    while ($result->fetch()) {
        $id_list[] = $id;
    }
    $result->close();

    // Remove records from performance_main
    $date_started = mb_substr($started, 0, 8); // this is a little worrying how it refers to the dates, also will only do exams done on a single day. Ticket being created to investigate.
    $remove = $mysqli->prepare('DELETE FROM performance_main WHERE paperID = ? AND taken = ?');
    $remove->bind_param('is', $paperID, $date_started);
    $remove->execute();
    $remove->close();

    // Remove records from performance_details
    if (count($id_list) > 0) {
        $remove = $mysqli->prepare('DELETE FROM performance_details WHERE perform_id IN (' . implode(',', $id_list) . ')');
        $remove->execute();
        $remove->close();
    }

    if ($percent == 100) {
        $tmp_percent = 27; // The default for U/L analysis
    } else {
        $tmp_percent = $percent;
    }

    if (isset($pstats['no'])) {
        // Write records into performance_main
        $sql = '';
        $params = '';
        $variables = array();
        $tmp = array();

        foreach ($dstats_array as $qid => $question_data) {
            if ($sql == '') {
                $sql = 'INSERT INTO performance_main VALUES (NULL, ?, ?, ?, ?, ?)';
            } else {
                $sql .= ', (NULL, ?, ?, ?, ?, ?)';
            }
            $params .= 'iiiis';
            $variables[] = $qid;
            $variables[] = $paperID;
            $variables[] = $tmp_percent;
            $variables[] = $user_total;
            $variables[] = $date_started;
        }

        $record = $mysqli->prepare($sql);

        array_unshift($variables, $params);
        foreach ($variables as $key => $value) {
            $tmp[$key] = & $variables[$key];
        }
        call_user_func_array(array($record, 'bind_param'), $tmp);

        $record->execute();
        $record->close();

        // Write records into performance_details
        //----------------------------------------------------------------------------------------------
        $q_rec_ids = array();
        // First a quick query to get the IDs from performance_main to use in performance_details
        $result = $mysqli->prepare('SELECT id, q_id FROM performance_main WHERE paperID = ? AND taken = ?');
        $result->bind_param('is', $paperID, $date_started);
        $result->execute();
        $result->bind_result($id, $tmp_q_id);
        while ($result->fetch()) {
            $q_rec_ids[$tmp_q_id] = $id;
        }
        $result->close();

        $sql = '';
        $params = '';
        $variables = array();
        $tmp = array();
        foreach ($dstats_array as $qid => $question_data) {
            foreach ($question_data as $part_no => $d_value) {
                if ($sql == '') {
                    $sql = 'INSERT INTO performance_details VALUES (NULL, ?, ?, ?, ?)';
                } else {
                    $sql .= ', (NULL, ?, ?, ?, ?)';
                }
                $params .= 'iiii';
                $variables[] = (array_key_exists($qid, $q_rec_ids)) ? $q_rec_ids[$qid] : null;
                $variables[] = $part_no;
                $variables[] = $pstats_array[$qid][$part_no];
                $variables[] = $d_value;
            }
        }

        $record = $mysqli->prepare($sql);

        array_unshift($variables, $params);
        foreach ($variables as $key => $value) {
            $tmp[$key] = & $variables[$key];
        }
        call_user_func_array(array($record, 'bind_param'), $tmp);

        $record->execute();
        $record->close();
    }
}

/**
 * Compile question data ready for display
 *
 * @param int       $screen_no        Screen number for question
 * @param Exclusion $exclusions       Question exclusion list object
 * @param int       $q_no             Question display number on page
 * @param int       $q_id             Question ID from database
 * @param string    $theme            Question theme text
 * @param string    $scenario         Question scenario text
 * @param string    $leadin           Question lead-in text
 * @param string    $q_type           Question type
 * @param string    $q_media          Media filename
 * @param int       $q_media_width    Media width in pixels
 * @param int       $q_media_height   Media height in pixels
 * @param string    $q_media_alt      Media alt-text
 * @param int       $q_media_num      Media ID number
 * @param array[]   $options          Options array ( option_text, o_media, o_media_width, o_media_height, o_media_alt, correct )
 * @param mixed[]   $bottom_log       Lower cohort data array
 * @param mixed[]   $top_log          Upper cohort data array
 * @param mixed[]   $freq_log         Full data array
 * @param int       $user_no
 * @param string    $score_method     Question marking method - mark per option/mark per question
 * @param string    $display_method   Question display method
 * @param string    $themecolor       HTML hex theme colour
 * @param string    $std              Standard setting data
 * @param int       $ex_no            Current exclusion ID (ex-global)
 * @param int       $d_no             Discrimination total number (?) (ex-global)
 * @param int       $d_total          Discrimination total count (?) (ex-global)
 * @param int[]     $pstats           Difficulty running tally (ex-global)
 * @param array[]   $pstats_array     Full difficulty stats array (ex-global)
 * @param int[]     $dstats           Discrimination running tally (ex-global)
 * @param array[]   $dstats_array     Full discrimination stats array (ex-global)
 * @param int       $user_total       Total number of candidates that have taken the paper (ex-global)
 * @param string    $language         Current system language string for paths (ex-global)
 * @param string[]  $string           Language strings
 *
 * @return mixed[]
 */
function compile_question_data($screen_no, $exclusions, $q_no, $q_id, $theme, $scenario, $leadin, $q_type, $q_media, $q_media_width, $q_media_height, $q_media_alt, $q_media_num, $options, $bottom_log, $top_log, $freq_log, $user_no, $score_method, $display_method, $themecolor, $std, &$ex_no, &$d_no, &$d_total, &$pstats, &$pstats_array, &$dstats, &$dstats_array, &$user_total, &$language, $string)
{
    // Initialise return data array
    $question_data = array(
        'theme' => $theme,
        'theme_color' => $themecolor,
        'type' => $q_type,
        'leadin' => $leadin,
        'no' => $q_no,
        'id' => $q_id,
        'score_method' => $score_method,
        'display_method' => $display_method,
        'screen_no' => $screen_no,
        'std' => $std,
        'excluded' => $exclusions->is_question_excluded($q_id)
    );

    $tmp_std_array = (!empty($std)) ? explode(',', $std) : array();
    $parts = count($tmp_std_array);
    for ($i = 0; $i < $parts; $i++) {
        $tmp_std_array[$i] = str_replace('exclude_', '', $tmp_std_array[$i]);
    }

    if ($q_type != 'extmatch' and $q_type != 'matrix' and $q_type != 'textbox') {
        // Check if the question type is not 'info' and $scenario is not null
        if ($q_type != 'info' && $scenario !== null) {
            // Remove '&nbsp;' from $scenario, trim the result, and check if it's not an empty string
            if (trim(str_replace('&nbsp;', '', $scenario)) != '') {
                $question_data['scenario'] = $scenario;
            }
        }

        switch ($q_type) {
            case 'area':
                $question_data['media'] = array($q_media, $q_media_width, $q_media_height, $q_media_alt, '#7F9DB9', true);

                $ex_no++;
                $question_data['ex_no'] = $ex_no;
                if (!isset($freq_log[$q_id][1]['correct'])) {
                    $freq_log[$q_id][1]['correct'] = 0;
                }
                if (!isset($freq_log[$q_id][1]['partial'])) {
                    $freq_log[$q_id][1]['partial'] = 0;
                }
                if (!isset($freq_log[$q_id][1]['incorrect'])) {
                    $freq_log[$q_id][1]['incorrect'] = 0;
                }

                if (!isset($top_log[$q_id][1]['correct'])) {
                    $top_log[$q_id][1]['correct'] = 0;
                }
                if (!isset($top_log[$q_id][1]['partial'])) {
                    $top_log[$q_id][1]['partial'] = 0;
                }
                if (!isset($top_log[$q_id][1]['incorrect'])) {
                    $top_log[$q_id][1]['incorrect'] = 0;
                }

                if (!isset($bottom_log[$q_id][1]['correct'])) {
                    $bottom_log[$q_id][1]['correct'] = 0;
                }
                if (!isset($bottom_log[$q_id][1]['partial'])) {
                    $bottom_log[$q_id][1]['partial'] = 0;
                }
                if (!isset($bottom_log[$q_id][1]['incorrect'])) {
                    $bottom_log[$q_id][1]['incorrect'] = 0;
                }
                $question_data['t'] = ($user_total != 0) ? ($freq_log[$q_id][1]['correct'] / $user_total) * 100 : 0;
                $question_data['u'] = ($user_no != 0) ? ($top_log[$q_id][1]['correct'] / $user_no) * 100 : 0;
                $question_data['l'] = ($user_no != 0) ? ($bottom_log[$q_id][1]['correct'] / $user_no) * 100 : 0;

                $question_data['partial_t'] = ($user_total != 0) ? ($freq_log[$q_id][1]['partial'] / $user_total) * 100 : 0;
                $question_data['partial_u'] = ($user_no != 0) ? ($top_log[$q_id][1]['partial'] / $user_no) * 100 : 0;
                $question_data['partial_l'] = ($user_no != 0) ? ($bottom_log[$q_id][1]['partial'] / $user_no) * 100 : 0;

                $question_data['incorrect_t'] = ($user_total != 0) ? ($freq_log[$q_id][1]['incorrect'] / $user_total) * 100 : 0;
                $question_data['incorrect_u'] = ($user_no != 0) ? ($top_log[$q_id][1]['incorrect'] / $user_no) * 100 : 0;
                $question_data['incorrect_l'] = ($user_no != 0) ? ($bottom_log[$q_id][1]['incorrect'] / $user_no) * 100 : 0;

                if (!isset($freq_log[$q_id]) or !isset($freq_log[$q_id]['totalpos']) or $freq_log[$q_id]['totalpos'] == 0) {
                    $question_data['p'] = 0;
                } else {
                    $question_data['p'] = $freq_log[$q_id]['mark'] / $freq_log[$q_id]['totalpos'];
                }

                $question_data['d'] = calcDiscrimination($user_no, $top_log[$q_id], $bottom_log[$q_id], 1, 'correct');

                $question_data['p_stats'] = pStats($question_data['p'], $q_id, 1, $pstats, $pstats_array);
                $question_data['d_stats'] = dStats($question_data['d'], $q_id, 1, $dstats, $dstats_array);
                $question_data['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total);
                break;
            case 'blank':
                if (!empty($q_media)) {
                    $question_data['media'] = array($q_media, $q_media_width, $q_media_height, $q_media_alt, '', true);
                }

                // Strip mark/size strings
                $options[0]['option_text'] = preg_replace('/ (mark|size)="([0-9]{1,3})"/u', '', $options[0]['option_text']);

                preg_match_all('|\[blank\](.*?)\[/blank\]|mu', $options[0]['option_text'], $blanks);
                $blanks = $blanks[1];
                // Below is to prepare a string ready to output with sprintf. May need str_replace changing for multibyte custom function, depending on charset
                $question_data['formatted_string'] = preg_replace('|\[blank\](.*?)\[/blank\]|su', '%s', str_replace('%', '%%', $options[0]['option_text']));

                if ($score_method == 'Mark per Question') {
                    $ex_no++;
                    $question_data['ex_no'] = $ex_no;
                }

                $i = 0;
                foreach ($blanks as $blank) {
                    $i++;
                    $tmp_correct_no = 0;
                    $tmp_top_no = 0;
                    $tmp_bottom_no = 0;
                    $blank_options = explode(',', $blank);
                    // Filter the options in the same way that user's answers are.
                    $blank_options = \param::clean_array($blank_options, \param::TEXT);
                    $question_data['parts'][$i]['answers'] = $blank_options; // Could be referenced as this all the way through?

                    if ($display_method == 'dropdown') {
                        foreach ($blank_options as $individual_answer) {
                            if (!isset($log[$q_id][$i][$blank])) {
                                $log[$q_id][$i][$blank] = 0;
                            }
                        }
                        $blank_word = mb_strtolower(trim($blank_options[0]));

                        if (isset($freq_log[$q_id][$i][$blank_word])) {
                            $tmp_correct_no += $freq_log[$q_id][$i][$blank_word];
                        }
                        if (isset($top_log[$q_id][$i][$blank_word])) {
                            $tmp_top_no += $top_log[$q_id][$i][$blank_word];
                        }
                        if (isset($bottom_log[$q_id][$i][$blank_word])) {
                            $tmp_bottom_no += $bottom_log[$q_id][$i][$blank_word];
                        }

                        $question_data['parts'][$i]['d'] = calcDiscrimination($user_no, $top_log[$q_id], $bottom_log[$q_id], $i, $blank_word);
                    } else {
                        $unique_blank_options = array_intersect_key($blank_options, array_unique(array_map('mb_strtolower', $blank_options)));
                        $unique_blank_options = array_map('mb_strtolower', $unique_blank_options);

                        // Merge the same option on its own and with spaces (e.g. 'cat' and ' cat').
                        $new_blank_options = array();
                        foreach ($unique_blank_options as $blank_option) {
                            $new_blank_options[] = trim($blank_option);
                        }
                        $unique_blank_options = array_unique($new_blank_options);

                        foreach ($unique_blank_options as $blank_option) {
                            if (isset($freq_log[$q_id][$i][$blank_option])) {
                                $tmp_correct_no += $freq_log[$q_id][$i][$blank_option];
                            }
                            if (isset($top_log[$q_id][$i][$blank_option])) {
                                $tmp_top_no += $top_log[$q_id][$i][$blank_option];
                            }
                            if (isset($bottom_log[$q_id][$i][$blank_option])) {
                                $tmp_bottom_no += $bottom_log[$q_id][$i][$blank_option];
                            }
                        }
                        $question_data['parts'][$i]['d'] = calcDiscrimination($user_no, $top_log[$q_id], $bottom_log[$q_id], $i, $unique_blank_options);
                    }

                    $question_data['parts'][$i]['t'] = ($user_total != 0) ? number_format(($tmp_correct_no / $user_total) * 100, 0) : 0;

                    $d_no++;
                    $d_total += $question_data['parts'][$i]['d'];

                    $question_data['parts'][$i]['u'] = ($user_no != 0) ? number_format(($tmp_top_no / $user_no) * 100, 0) : 0;
                    $question_data['parts'][$i]['l'] = ($user_no != 0) ? number_format(($tmp_bottom_no / $user_no) * 100, 0) : 0;

                    if ($score_method == 'Mark per Option') {
                        if ($exclusions->is_question_excluded($q_id)) {
                            $question_data['parts'][$i]['excluded'] = $exclusions->get_exclusion_part_by_qid($q_id, $i - 1);
                        }
                        $ex_no++;
                        $question_data['parts'][$i]['ex_no'] = $ex_no;
                    }

                    $question_data['parts'][$i]['p'] = ($user_total != 0) ? $tmp_correct_no / $user_total : 0;
                    $question_data['parts'][$i]['p_stats'] = pStats($question_data['parts'][$i]['p'], $q_id, $i, $pstats, $pstats_array);
                    $question_data['parts'][$i]['d_stats'] = dStats($question_data['parts'][$i]['d'], $q_id, $i, $dstats, $dstats_array);
                    $question_data['parts'][$i]['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total, $i);

                    if (isset($tmp_std_array[$i - 1])) {
                        $question_data['parts'][$i]['std'] = $tmp_std_array[$i - 1];
                    }
                }
                break;
            case 'dichotomous':
                if (!empty($q_media)) {
                    $question_data['media'] = array($q_media, $q_media_width, $q_media_height, $q_media_alt, '', true);
                }
                if ($score_method == 'Mark per Question') {
                    $ex_no++;
                    $question_data['ex_no'] = $ex_no;
                }
                $i = 0;
                $std_part = 0;
                foreach ($options as $part) {
                    $i++;
                    if (!isset($log[$q_id][$i]['t'])) {
                        $log[$q_id][$i]['t'] = 0;
                    }
                    if (!isset($log[$q_id][$i]['f'])) {
                        $log[$q_id][$i]['f'] = 0;
                    }
                    if (!isset($freq_log[$q_id][$i]['t'])) {
                        $freq_log[$q_id][$i]['t'] = 0;
                    }
                    if (!isset($freq_log[$q_id][$i]['f'])) {
                        $freq_log[$q_id][$i]['f'] = 0;
                    }
                    if (!isset($bottom_log[$q_id][$i]['t'])) {
                        $bottom_log[$q_id][$i]['t'] = 0;
                    }
                    if (!isset($bottom_log[$q_id][$i]['f'])) {
                        $bottom_log[$q_id][$i]['f'] = 0;
                    }
                    if (!isset($top_log[$q_id][$i]['t'])) {
                        $top_log[$q_id][$i]['t'] = 0;
                    }
                    if (!isset($top_log[$q_id][$i]['f'])) {
                        $top_log[$q_id][$i]['f'] = 0;
                    }
                    if (!isset($tmp_std_array[$std_part])) {
                        $tmp_std_array[$std_part] = '';
                    }

                    if ($score_method == 'Mark per Option') {
                        if ($question_data['excluded']) {
                            $question_data['parts'][$i]['excluded'] = $exclusions->is_question_part_excluded($q_id, $i - 1); // extra code added here to validate the part exclusion
                            $question_data['parts'][$i]['exclusions'] = $exclusions->get_exclusion_part_by_qid($q_id, $i - 1);
                        }
                        $ex_no++;
                        $question_data['parts'][$i]['ex_no'] = $ex_no;
                    }

                    if ($part['correct'] == 't') {
                        $question_data['parts'][$i]['d'] = calcDiscrimination($user_no, $top_log[$q_id], $bottom_log[$q_id], $i, 't');
                        $question_data['parts'][$i]['p'] = (isset($freq_log[$q_id]) and $user_total != 0) ? $freq_log[$q_id][$i]['t'] / $user_total : 0;
                        $question_data['parts'][$i]['p_top'] = (isset($top_log[$q_id]) and $user_no != 0) ? $top_log[$q_id][$i]['t'] / $user_no : 0;
                        $question_data['parts'][$i]['p_bottom'] = (isset($bottom_log[$q_id]) and $user_no != 0) ? $bottom_log[$q_id][$i]['t'] / $user_no : 0;
                        $question_data['parts'][$i]['correct'] = true;
                    } else {
                        $question_data['parts'][$i]['d'] = calcDiscrimination($user_no, $top_log[$q_id], $bottom_log[$q_id], $i, 'f');
                        $question_data['parts'][$i]['p'] = (isset($freq_log[$q_id]) and $user_total != 0) ? $freq_log[$q_id][$i]['f'] / $user_total : 0;
                        $question_data['parts'][$i]['p_top'] = (isset($top_log[$q_id]) and $user_no != 0) ? $top_log[$q_id][$i]['f'] / $user_no : 0;
                        $question_data['parts'][$i]['p_bottom'] = (isset($bottom_log[$q_id]) and $user_no != 0) ? $bottom_log[$q_id][$i]['f'] / $user_no : 0;
                        $question_data['parts'][$i]['correct'] = false;
                    }

                    $question_data['parts'][$i]['p_stats'] = pStats($question_data['parts'][$i]['p'], $q_id, $i, $pstats, $pstats_array);
                    $question_data['parts'][$i]['d_stats'] = dStats($question_data['parts'][$i]['d'], $q_id, $i, $dstats, $dstats_array);
                    $question_data['parts'][$i]['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total, $i);
                    $question_data['parts'][$i]['std'] = $tmp_std_array[$std_part];
                    $question_data['parts'][$i]['option_text'] = $part['option_text'];

                    $std_part++;
                }
                break;
            case 'enhancedcalc':
                if (!empty($q_media)) {
                    $question_data['media'] = array($q_media, $q_media_width, $q_media_height, $q_media_alt, '', true);
                }

                if (!isset($freq_log[$q_id][1]['correct'])) {
                    $freq_log[$q_id][1]['correct'] = 0;
                }

                if ($question_data['excluded']) {
                    $question_data['exclusions'] = $exclusions->get_exclusions_by_qid($q_id);
                }

                $question_data['d'] = calcDiscrimination($user_no, $top_log[$q_id], $bottom_log[$q_id], 1, 'correct');
                if ($freq_log[$q_id][1]['correct'] !== 0 and $user_total != 0) {
                    $question_data['t'] = ($freq_log[$q_id][1]['correct'] / $user_total) * 100;
                } else {
                    $question_data['t'] = 0;
                }
                if (isset($top_log[$q_id][1]['correct']) and $user_no != 0) {
                    $question_data['u'] = ($top_log[$q_id][1]['correct'] / $user_no) * 100;
                } else {
                    $question_data['u'] = 0;
                }
                if (isset($bottom_log[$q_id][1]['correct']) and $user_no != 0) {
                    $question_data['l'] = ($bottom_log[$q_id][1]['correct'] / $user_no) * 100;
                } else {
                    $question_data['l'] = 0;
                }

                $ex_no++;
                $question_data['ex_no'] = $ex_no;

                $question_data['std'] = $std;

                if ($freq_log[$q_id][1]['correct'] !== 0 and $user_total != 0) {
                    $question_data['p'] = $freq_log[$q_id][1]['correct'] / $user_total;
                } else {
                    $question_data['p'] = 0;
                }
                $question_data['p_stats'] = pStats($question_data['p'], $q_id, 1, $pstats, $pstats_array);
                $question_data['d_stats'] = dStats($question_data['d'], $q_id, 1, $dstats, $dstats_array);
                $question_data['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total);
                break;
            case 'true_false':
                if (!empty($q_media)) {
                    $question_data['media'] = array($q_media, $q_media_width, $q_media_height, $q_media_alt, '', true);
                }

                if (!isset($log[$q_id][1]['t'])) {
                    $log[$q_id][1]['t'] = 0;
                }
                if (!isset($log[$q_id][1]['f'])) {
                    $log[$q_id][1]['f'] = 0;
                }
                if (!isset($freq_log[$q_id][1]['t'])) {
                    $freq_log[$q_id][1]['t'] = 0;
                }
                if (!isset($freq_log[$q_id][1]['f'])) {
                    $freq_log[$q_id][1]['f'] = 0;
                }
                if (!isset($bottom_log[$q_id][1]['t'])) {
                    $bottom_log[$q_id][1]['t'] = 0;
                }
                if (!isset($bottom_log[$q_id][1]['f'])) {
                    $bottom_log[$q_id][1]['f'] = 0;
                }
                if (!isset($top_log[$q_id][1]['t'])) {
                    $top_log[$q_id][1]['t'] = 0;
                }
                if (!isset($top_log[$q_id][1]['f'])) {
                    $top_log[$q_id][1]['f'] = 0;
                }

                $ex_no++;
                $question_data['ex_no'] = $ex_no;

                $question_data['p_true'] = (isset($freq_log[$q_id]) and $user_total != 0) ? $freq_log[$q_id][1]['t'] / $user_total : 0;
                $question_data['p_top_true'] = (isset($top_log[$q_id]) and $user_no != 0) ? $top_log[$q_id][1]['t'] / $user_no : 0;
                $question_data['p_bottom_true'] = (isset($bottom_log[$q_id]) and $user_no != 0) ? $bottom_log[$q_id][1]['t'] / $user_no : 0;

                $question_data['p_false'] = (isset($freq_log[$q_id]) and $user_total != 0) ? $freq_log[$q_id][1]['f'] / $user_total : 0;
                $question_data['p_top_false'] = (isset($top_log[$q_id]) and $user_no != 0) ? $top_log[$q_id][1]['f'] / $user_no : 0;
                $question_data['p_bottom_false'] = (isset($bottom_log[$q_id]) and $user_no != 0) ? $bottom_log[$q_id][1]['f'] / $user_no : 0;

                if ($options[0]['correct'] == 't') {
                    $question_data['d'] = calcDiscrimination($user_no, $top_log[$q_id], $bottom_log[$q_id], 1, 't');
                    $question_data['p_stats'] = pStats($question_data['p_true'], $q_id, 1, $pstats, $pstats_array);
                    $question_data['correct'] = true;
                } elseif ($options[0]['correct'] == 'f') {
                    $question_data['d'] = calcDiscrimination($user_no, $top_log[$q_id], $bottom_log[$q_id], 1, 'f');
                    $question_data['p_stats'] = pStats($question_data['p_false'], $q_id, 1, $pstats, $pstats_array);
                    $question_data['correct'] = false;
                } else {
                    throw new \Exception('Unexpected answer to true/false question, expected "t" or "f", found ' . $options[0]['correct']);
                }

                $question_data['std'] = $std;
                $question_data['d_stats'] = dStats($question_data['d'], $q_id, 1, $dstats, $dstats_array);
                $question_data['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total, 1);
                break;
            case 'labelling':
                if ($question_data['score_method'] == 'Mark per Question') {
                    $ex_no++;
                    $question_data['ex_no'] = $ex_no;
                }
                $std_part = 0;
                $max_col1 = 0;
                $max_col2 = 0;
                $correct_buf = [];
                $options_buffer = []; // Looks like this replaces $options, but check before changing variable names. Might be worth leaving as-is for clarity.
                $tmp_first_split = explode(';', $options[0]['correct']);
                $tmp_second_split = explode('$', $tmp_first_split[11]);
                for ($label_no = 4; $label_no <= 200; $label_no += 4) {
                    if (isset($tmp_second_split[$label_no])) {
                        if (mb_substr($tmp_second_split[$label_no], 0, 1) != '|') {
                            // The label has text, i.e. is not blank.
                            // The stored answer coordinates are sometimes floating points, rather than integers.
                            // We need to round them to ensure they will always matach the coordiantes that are
                            // stored in the user answers. If we do not do this frequency analysis for the question type
                            // will sometimes not work. See ROGO-1822.
                            $x_coordinate = round($tmp_second_split[$label_no - 2]);
                            $y_coordinate = round($tmp_second_split[$label_no - 1] - 25);
                            $options_buffer[] = trim(mb_substr($tmp_second_split[$label_no], 0, mb_strpos($tmp_second_split[$label_no], '|'))) . '|' . $x_coordinate . '|' . $y_coordinate;
                            if ($x_coordinate >= 220) {
                                $correct_buf[] = $x_coordinate . 'x' . $y_coordinate;
                            }
                        }
                    }
                }

                foreach ($tmp_second_split as $ind_label) {
                    $label_parts = explode('$', $ind_label);
                    if (isset($label_parts[4]) and trim($label_parts[4]) != '') {
                        if ($label_parts[0] < 10) {
                            $max_col1 = $label_parts[0];
                        } else {
                            $max_col2 = $label_parts[0];
                        }
                    }
                }
                $max_col2 -= 10;

                $question_data['max_label'] = max($max_col1, $max_col2);
                $question_data['media'] = array($q_media, $q_media_width, $q_media_height, $q_media_alt, '#FFC0C0');
                $question_data['correct_answer'] = $options[0]['correct']; // may not be as simple as this, also consider 'correct' as possible varname (conflicts elsewhere)

                $i = 1;
                foreach ($correct_buf as $individual_coord) {
                    $option_no = 1;
                    foreach ($options_buffer as $individual_option) { // not sure this is necessary to have the parts and options in the array, but playing it safe for now
                        $first_part = explode('|', $individual_option);
                        $individual_option = trim($first_part[0]);

                        $tmp_parts = explode('~', $individual_option);
                        $text_only = $tmp_parts[0];

                        if ($individual_coord == $first_part[1] . 'x' . $first_part[2]) {
                            $question_data['parts'][$i]['options'][$option_no]['d'] = calcDiscrimination($user_no, $top_log[$q_id], $bottom_log[$q_id], $individual_coord, $text_only);
                            if (isset($tmp_std_array[$std_part])) {
                                $question_data['parts'][$i]['options'][$option_no]['std'] = $tmp_std_array[$std_part];
                            } else {
                                $question_data['parts'][$i]['options'][$option_no]['std'] = '';
                            }

                            $tmp_correct_no = (isset($freq_log[$q_id][$individual_coord][$text_only])) ? $freq_log[$q_id][$individual_coord][$text_only] : 0;
                            $tmp_top_no = (isset($top_log[$q_id][$individual_coord][$text_only])) ? $top_log[$q_id][$individual_coord][$text_only] : 0;
                            $tmp_bottom_no = (isset($bottom_log[$q_id][$individual_coord][$text_only])) ? $bottom_log[$q_id][$individual_coord][$text_only] : 0;

                            $question_data['parts'][$i]['options'][$option_no]['p'] = ($user_total != 0) ? $tmp_correct_no / $user_total : 0;
                            $question_data['parts'][$i]['options'][$option_no]['p_top'] = ($user_no != 0) ? $tmp_top_no / $user_no : 0;
                            $question_data['parts'][$i]['options'][$option_no]['p_bottom'] = ($user_no != 0) ? $tmp_bottom_no / $user_no : 0;

                            if ($score_method == 'Mark per Option') { // may be optimal to have this check once before the option loop, with just ex_no incrementing
                                $question_data['parts'][$i]['options'][$option_no]['excluded'] = $exclusions->is_question_part_excluded($q_id, $i - 1);
                                if ($question_data['excluded']) {
                                    $question_data['parts'][$i]['options'][$option_no]['exclusions'] = $exclusions->get_exclusion_part_by_qid($q_id, $i - 1);
                                }
                                $ex_no++;
                                $question_data['parts'][$i]['options'][$option_no]['ex_no'] = $ex_no;
                            }

                            $question_data['parts'][$i]['options'][$option_no]['p_stats'] = pStats($question_data['parts'][$i]['options'][$option_no]['p'], $q_id, $i, $pstats, $pstats_array);
                            $question_data['parts'][$i]['options'][$option_no]['d_stats'] = dStats($question_data['parts'][$i]['options'][$option_no]['d'], $q_id, $i, $dstats, $dstats_array);
                            $question_data['parts'][$i]['options'][$option_no]['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total, $individual_option); // this seems to be the only entry that uses the individual option string as a reference, instead of a numeric index
                            if (isset($tmp_std_array[$std_part])) {
                                $question_data['parts'][$i]['options'][$option_no]['std'] = $tmp_std_array[$std_part];
                            } else {
                                $question_data['parts'][$i]['options'][$option_no]['std'] = '';
                            }

                            if (mb_strpos(mb_strtolower($individual_option), '.jpg') !== false or mb_strpos(mb_strtolower($individual_option), '.jpeg') !== false or mb_strpos(mb_strtolower($individual_option), '.gif') !== false or mb_strpos(mb_strtolower($individual_option), '.png') !== false) {
                                $question_data['parts'][$i]['options'][$option_no]['media'] = explode('~', $individual_option);
                            } else {
                                $question_data['parts'][$i]['options'][$option_no]['option_text'] = $individual_option;
                            }
                            $std_part++;
                        }
                        $option_no++;
                    }
                    $i++;
                }
                break;
            case 'flash':
                $ex_no++;
                $question_data['ex_no'] = $ex_no;
                $question_data['media'] = array($q_media, $q_media_width, $q_media_height, $q_media_alt, '', true);
                break;
            case 'hotspot':
                $layers = explode('|', $options[0]['correct']);
                $std_parts = explode(',', $std);

                if ($score_method == 'Mark per Question') {
                    $ex_no++;
                    $question_data['ex_no'] = $ex_no;
                }

                $question_data['coords'] = '';
                for ($i = 1; $i <= count($layers); $i++) {
                    if (isset($freq_log[$q_id][$i]['coords'])) {
                        $question_data['coords'] .= $freq_log[$q_id][$i]['coords'] . '|';
                    } else {
                        $question_data['coords'] .= '|';
                    }
                }
                $question_data['coords'] = rtrim($question_data['coords'], '|');

                $question_data['tmp_correct'] = str_replace("'", "\'", trim($options[0]['correct']));
                $question_data['tmp_correct'] = str_replace('&nbsp;', ' ', $question_data['tmp_correct']);
                $question_data['tmp_correct'] = htmlentities(preg_replace('/\r\n/', '', $question_data['tmp_correct']));

                $question_data['media'] = array($q_media, $q_media_width, $q_media_height, $q_media_alt, '', true);

                for ($i = 1; $i <= count($layers); $i++) {
                    $question_data['parts'][$i]['option_text'] = mb_substr($layers[$i - 1], 0, mb_strpos($layers[$i - 1], '~'));
                    $question_data['parts'][$i]['std'] = (isset($std_parts[$i - 1])) ? $std_parts[$i - 1] : '';

                    $question_data['parts'][$i]['d'] = calcDiscrimination($user_no, $top_log[$q_id], $bottom_log[$q_id], $i, 1);
                    $tmp_correct_no = (isset($freq_log[$q_id][$i][1])) ? $freq_log[$q_id][$i][1] : 0;
                    $tmp_top_no = (isset($top_log[$q_id][$i][1])) ? $top_log[$q_id][$i][1] : 0;
                    $tmp_bottom_no = (isset($bottom_log[$q_id][$i][1])) ? $bottom_log[$q_id][$i][1] : 0;
                    $question_data['parts'][$i]['p'] = ($user_total != 0) ? $tmp_correct_no / $user_total : 0;
                    $question_data['parts'][$i]['p_top'] = ($user_no != 0) ? $tmp_top_no / $user_no : 0;
                    $question_data['parts'][$i]['p_bottom'] = ($user_no != 0) ? $tmp_bottom_no / $user_no : 0;

                    if ($score_method == 'Mark per Option') {
                        $ex_no++;
                        $question_data['parts'][$i]['ex_no'] = $ex_no;
                        $question_data['parts'][$i]['exclusions'] = $exclusions->get_exclusion_part_by_qid($q_id, $i - 1);
                    }

                    $question_data['parts'][$i]['p_stats'] = pStats($question_data['parts'][$i]['p'], $q_id, $i, $pstats, $pstats_array);
                    $question_data['parts'][$i]['d_stats'] = dStats($question_data['parts'][$i]['d'], $q_id, $i, $dstats, $dstats_array);
                    $question_data['parts'][$i]['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total, $i);
                }
                break;
            case 'likert':
                if (!empty($q_media)) {
                    $question_data['media'] = array($q_media, $q_media_width, $q_media_height, $q_media_alt, '', true);
                }

                $question_data['scale'] = explode('|', $display_method);
                for ($i = 1; $i < count($question_data['scale']); $i++) {
                    if (isset($freq_log[$q_id][1][$i]) and $user_total != 0) {
                        $question_data['parts'][$i]['t'] = ($freq_log[$q_id][1][$i] / $user_total) * 100;
                    } else {
                        $question_data['parts'][$i]['t'] = 0;
                    }
                }
                $question_data['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total, 1);
                break;
            case 'mcq':
                if (!empty($q_media)) {
                    $question_data['media'] = array($q_media, $q_media_width, $q_media_height, $q_media_alt, '', true);
                }
                if ($question_data['excluded']) {
                    $question_data['exclusions'] = $exclusions->get_exclusions_by_qid($q_id);
                }
                $ex_no++;
                $question_data['ex_no'] = $ex_no;
                $question_data['option_count'] = count($options);

                $i = 0;
                foreach ($options as $individual_option) {
                    $i++;
                    if (isset($freq_log[$q_id][1][$i]) and $user_total != 0) {
                        $question_data['parts'][$i]['t'] = ($freq_log[$q_id][1][$i] / $user_total) * 100;
                    } else {
                        $question_data['parts'][$i]['t'] = 0;
                    }
                    if (isset($top_log[$q_id][1][$i]) and $user_no != 0) {
                        $question_data['parts'][$i]['u'] = ($top_log[$q_id][1][$i] / $user_no) * 100;
                    } else {
                        $question_data['parts'][$i]['u'] = 0;
                    }
                    if (isset($bottom_log[$q_id][1][$i]) and $user_no != 0) {
                        $question_data['parts'][$i]['l'] = ($bottom_log[$q_id][1][$i] / $user_no) * 100;
                    } else {
                        $question_data['parts'][$i]['l'] = 0;
                    }
                    if ($options[0]['correct'] == $i) {
                        $question_data['d'] = calcDiscrimination($user_no, $top_log[$q_id], $bottom_log[$q_id], 1, $i);
                        $tmp_correct_no = (isset($freq_log[$q_id][1][$i])) ? $freq_log[$q_id][1][$i] : 0;
                        $question_data['parts'][$i]['correct'] = true;
                    } else {
                        $question_data['parts'][$i]['correct'] = false;
                    }
                    if ($individual_option['option_text'] != '') {
                        $question_data['parts'][$i]['option_text'] = $individual_option['option_text'];
                    }
                    if (!empty($individual_option['o_media'])) {
                        $question_data['parts'][$i]['media'] = array($individual_option['o_media'], $individual_option['o_media_width'], $individual_option['o_media_height'], $individual_option['o_media_alt'], '', true);
                    }
                }

                if (isset($freq_log[$q_id][1]['a']) and $user_total != 0) {
                    $question_data['t'] = ($freq_log[$q_id][1]['a'] / $user_total) * 100;
                } else {
                    $question_data['t'] = 0;
                }
                if (isset($top_log[$q_id][1]['a']) and $user_no != 0) {
                    $question_data['u'] = ($top_log[$q_id][1]['a'] / $user_no) * 100;
                } else {
                    $question_data['u'] = 0;
                }
                if (isset($bottom_log[$q_id][1]['a']) and $user_no != 0) {
                    $question_data['l'] = ($bottom_log[$q_id][1]['a'] / $user_no) * 100;
                } else {
                    $question_data['l'] = 0;
                }

                $question_data['p'] = ($user_total != 0) ? $tmp_correct_no / $user_total : 0;

                $question_data['p_stats'] = pStats($question_data['p'], $q_id, 1, $pstats, $pstats_array);
                $question_data['d_stats'] = dStats($question_data['d'], $q_id, 1, $dstats, $dstats_array);
                $question_data['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total);
                break;
            case 'mrq':
                if (!empty($q_media)) {
                    $question_data['media'] = array($q_media, $q_media_width, $q_media_height, $q_media_alt, '', true);
                }

                if ($question_data['excluded']) {
                    $question_data['exclusions'] = $exclusions->get_exclusions_by_qid($q_id);
                }
                $ex_no++;
                $question_data['ex_no'] = $ex_no;

                $i = 0;
                $std_part = 0;
                foreach ($options as $individual_option) {
                    $i++;
                    if (!isset($log[$q_id][$i]['y'])) {
                        $log[$q_id][$i]['y'] = 0;
                    }
                    if (isset($freq_log[$q_id][$i]['y']) and $user_total != 0) {
                        $question_data['parts'][$i]['t'] = ($freq_log[$q_id][$i]['y'] / $user_total) * 100;
                    } else {
                        $question_data['parts'][$i]['t'] = 0;
                    }
                    if (isset($top_log[$q_id][$i]['y']) and $user_no != 0) {
                        $question_data['parts'][$i]['u'] = ($top_log[$q_id][$i]['y'] / $user_no) * 100;
                    } else {
                        $question_data['parts'][$i]['u'] = 0;
                    }
                    if (isset($bottom_log[$q_id][$i]['y']) and $user_no != 0) {
                        $question_data['parts'][$i]['l'] = ($bottom_log[$q_id][$i]['y'] / $user_no) * 100;
                    } else {
                        $question_data['parts'][$i]['l'] = 0;
                    }
                    if ($options[$i - 1]['correct'] == 'y') {
                        $question_data['parts'][$i]['correct'] = true;
                        if (isset($tmp_std_array[$i - 1])) {
                            $question_data['parts'][$i]['std'] = $tmp_std_array[$i - 1];
                        } else {
                            $question_data['parts'][$i]['std'] = '';
                        }
                        $std_part++;
                    } else {
                        $question_data['parts'][$i]['std'] = '';
                        $question_data['parts'][$i]['correct'] = false;
                    }
                    if ($individual_option['option_text'] != '') {
                        $question_data['parts'][$i]['option_text'] = $individual_option['option_text'];
                    }
                    if (!empty($individual_option['o_media'])) {
                        $question_data['parts'][$i]['media'] = array($individual_option['o_media'], $individual_option['o_media_width'], $individual_option['o_media_height'], $individual_option['o_media_alt'], '', true);
                    }
                }

                // Abstain
                if (isset($freq_log[$q_id]['a']) and $user_total != 0) {
                    $question_data['t'] = ($freq_log[$q_id]['a'] / $user_total) * 100;
                } else {
                    $question_data['t'] = 0;
                }
                if (isset($top_log[$q_id]['a']) and $user_no != 0) {
                    $question_data['u'] = ($top_log[$q_id]['a'] / $user_no) * 100;
                } else {
                    $question_data['u'] = 0;
                }
                if (isset($bottom_log[$q_id]['a']) and $user_no != 0) {
                    $question_data['l'] = ($bottom_log[$q_id]['a'] / $user_no) * 100;
                } else {
                    $question_data['l'] = 0;
                }

                if (empty($top_log[$q_id]['totalpos']) or empty($bottom_log[$q_id]['totalpos'])) {
                    $question_data['d'] = 0;
                } else {
                    $question_data['d'] = ($top_log[$q_id]['mark'] / $top_log[$q_id]['totalpos']) - ($bottom_log[$q_id]['mark'] / $bottom_log[$q_id]['totalpos']);
                }
                $tmp_pstat = (isset($freq_log[$q_id]['mark']) and isset($freq_log[$q_id]['totalpos']) and $freq_log[$q_id]['totalpos'] > 0) ? $freq_log[$q_id]['mark'] / $freq_log[$q_id]['totalpos'] : 0;
                $question_data['p_stats'] = pStats($tmp_pstat, $q_id, 1, $pstats, $pstats_array);
                $question_data['d_stats'] = dStats($question_data['d'], $q_id, 1, $dstats, $dstats_array);
                $question_data['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total);
                break;
            case 'rank':
                if (!empty($q_media)) {
                    $question_data['media'] = array($q_media, $q_media_width, $q_media_height, $q_media_alt, '', true);
                }

                $rank_no = 0;
                foreach ($options as $individual_option) {
                    if ($individual_option['correct'] > $rank_no and $individual_option['correct'] != 0) {
                        $rank_no = $individual_option['correct'];
                    }
                }

                if ($question_data['excluded']) {
                    $question_data['exclusions'] = $exclusions->get_exclusions_by_qid($q_id);
                }
                $ex_no++;
                $question_data['ex_no'] = $ex_no;

                $i = 0;
                foreach ($options as $individual_option) {
                    $question_data['parts'][$i]['option_text'] = $individual_option['option_text'];
                    for ($rank_position = 1; $rank_position <= $rank_no; $rank_position++) {
                        if (isset($top_log[$q_id][$i][$rank_position])) {
                            $question_data['parts'][$i]['options'][$rank_position]['u'] = ($top_log[$q_id][$i][$rank_position] / $user_no) * 100;
                        } else {
                            $question_data['parts'][$i]['options'][$rank_position]['u'] = 0;
                        }
                        if (isset($bottom_log[$q_id][$i][$rank_position])) {
                            $question_data['parts'][$i]['options'][$rank_position]['l'] = ($bottom_log[$q_id][$i][$rank_position] / $user_no) * 100;
                        } else {
                            $question_data['parts'][$i]['options'][$rank_position]['l'] = 0;
                        }

                        if (!isset($log[$q_id][$i][$rank_position])) {
                            $log[$q_id][$i][$rank_position] = 0;
                        }

                        if ($options[$i]['correct'] == $rank_position) {
                            $question_data['parts'][$i]['options'][$rank_position]['correct'] = true;
                            if (isset($tmp_std_array[$i])) {
                                $question_data['parts'][$i]['options'][$rank_position]['std'] = $tmp_std_array[$i];
                            } elseif (isset($tmp_std_array[0]) and !isset($tmp_std)) {
                                // This is the first displayed option in a ranking with the Mark per question marking method.
                                $question_data['parts'][$i]['options'][$rank_position]['std'] = $tmp_std_array[0];
                            } else {
                                $question_data['parts'][$i]['options'][$rank_position]['std'] = '';
                            }
                        } else {
                            $question_data['parts'][$i]['options'][$rank_position]['correct'] = false;
                        }
                    }
                    $question_data['parts'][$i]['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total, $i);
                    $i++;
                }
                if (empty($top_log[$q_id]['totalpos']) or empty($bottom_log[$q_id]['totalpos'])) {
                    $question_data['d'] = 0;
                } else {
                    $question_data['d'] = ($top_log[$q_id]['mark'] / $top_log[$q_id]['totalpos']) - ($bottom_log[$q_id]['mark'] / $bottom_log[$q_id]['totalpos']);
                }
                $question_data['std'] = (isset($tmp_std_array[$i])) ? $tmp_std_array[$i] : '';
                $tmp_correct_no = (isset($top_log[$q_id]['all_correct'])) ? $top_log[$q_id]['all_correct'] : 0;
                $tmp_bottom_no = (isset($bottom_log[$q_id]['all_correct'])) ? $bottom_log[$q_id]['all_correct'] : 0;

                $question_data['u'] = ($tmp_correct_no / $user_no) * 100;
                $question_data['l'] = ($tmp_bottom_no / $user_no) * 100;
                $question_data['p'] = (isset($freq_log[$q_id]) and $freq_log[$q_id]['totalpos'] != 0) ? $freq_log[$q_id]['mark'] / $freq_log[$q_id]['totalpos'] : 0;

                $question_data['p_stats'] = pStats($question_data['p'], $q_id, 1, $pstats, $pstats_array);
                $question_data['d_stats'] = dStats($question_data['d'], $q_id, 1, $dstats, $dstats_array);
                break;
            case 'sct':
                if (!empty($q_media)) {
                    $question_data['media'] = array($q_media, $q_media_width, $q_media_height, $q_media_alt, '', true);
                }

                if ($question_data['excluded']) {
                    $question_data['exclusions'] = $exclusions->get_exclusions_by_qid($q_id);
                } else {
                    $question_data['exclusions'] = '';
                }
                $ex_no++;
                $question_data['ex_no'] = $ex_no;

                $i = 0;
                foreach ($options as $individual_option) {
                    $i++;
                    $tmp_correct_no = (isset($freq_log[$q_id][1][$i])) ? $freq_log[$q_id][1][$i] : 0;
                    $tmp_top_no = (isset($top_log[$q_id][1][$i])) ? $top_log[$q_id][1][$i] : 0;
                    $tmp_bottom_no = (isset($bottom_log[$q_id][1][$i])) ? $bottom_log[$q_id][1][$i] : 0;

                    $max_correct = 0;
                    $correct_answer_no = 0;
                    $answer_no = 1;
                    foreach ($options as $tmp_correct) {
                        if ($tmp_correct['correct'] > $max_correct) {
                            $max_correct = $tmp_correct['correct'];
                            $correct_answer_no = $answer_no;
                        }
                        $answer_no++;
                    }
                    $question_data['parts'][$i]['correct'] = ($correct_answer_no == $i);
                    $percent_correct = ($user_total != 0) ? $tmp_correct_no / $user_total : 0;
                    $percent_top = ($user_no != 0) ? $tmp_top_no / $user_no : 0;
                    $percent_bottom = ($user_no != 0) ? $tmp_bottom_no / $user_no : 0;
                    $question_data['parts'][$i]['t'] = $percent_correct * 100;
                    $question_data['parts'][$i]['u'] = $percent_top * 100;
                    $question_data['parts'][$i]['l'] = $percent_bottom * 100;
                    $question_data['parts'][$i]['option_text'] = $individual_option['option_text'];
                }

                if (empty($top_log[$q_id]['totalpos']) or empty($bottom_log[$q_id]['totalpos'])) {
                    $question_data['d'] = 0;
                } else {
                    $question_data['d'] = ($top_log[$q_id]['mark'] / $top_log[$q_id]['totalpos']) - ($bottom_log[$q_id]['mark'] / $bottom_log[$q_id]['totalpos']);
                }

                $question_data['p'] = (isset($freq_log[$q_id]) and $freq_log[$q_id]['totalpos'] > 0) ? $freq_log[$q_id]['mark'] / $freq_log[$q_id]['totalpos'] : 0;

                $question_data['p_stats'] = pStats($question_data['p'], $q_id, 1, $pstats, $pstats_array);
                $question_data['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total, 1);

                break;
        }
    } elseif ($q_type == 'textbox') {
        $ex_no++;
        $question_data['ex_no'] = $ex_no;
        if ($question_data['excluded']) {
            $question_data['exclusions'] = $exclusions->get_exclusions_by_qid($q_id);
        }

        $i = 0;
        $marks = array_unique(array_merge(array_keys($freq_log[$q_id][1]), array_keys($top_log[$q_id][1]), array_keys($bottom_log[$q_id][1])));
        sort($marks);
        foreach ($marks as $mark) {
            $i++;
            $question_data['parts'][$i]['mark'] = preg_replace('/^([0-9]+)\.0$/', '\1', $mark); // Strip hanging .0s
            if (isset($freq_log[$q_id][1][$mark])) {
                $question_data['parts'][$i]['t'] = ($freq_log[$q_id][1][$mark] / $user_total) * 100;
            } else {
                $question_data['parts'][$i]['t'] = 0;
            }
            if (isset($top_log[$q_id][1][$mark])) {
                $question_data['parts'][$i]['u'] = ($top_log[$q_id][1][$mark] / $user_no) * 100;
            } else {
                $question_data['parts'][$i]['u'] = 0;
            }
            if (isset($bottom_log[$q_id][1][$mark])) {
                $question_data['parts'][$i]['l'] = ($bottom_log[$q_id][1][$mark] / $user_no) * 100;
            } else {
                $question_data['parts'][$i]['l'] = 0;
            }
        }

        $sortby = 'used';
        $ordering = 'ASC';

        $top_words = array();
        if (isset($top_log[$q_id]['words'])) {
            $i = 0;
            foreach ($top_log[$q_id]['words'] as $word => $used) {
                $top_words[$i]['word'] = $word;
                $top_words[$i]['used'] = $used;
                $i++;
            }
        }
        $top_words = \sort::array_csort($top_words, $sortby, $ordering);

        $bottom_words = array();
        if (isset($bottom_log[$q_id]['words'])) {
            $i = 0;
            foreach ($bottom_log[$q_id]['words'] as $word => $used) {
                $bottom_words[$i]['word'] = $word;
                $bottom_words[$i]['used'] = $used;
                $i++;
            }
        }
        $bottom_words = \sort::array_csort($bottom_words, $sortby, $ordering);

        $question_data['mean_word_count'] = array(
            'top' => (isset($top_log[$q_id]) and $user_no != 0) ? $top_log[$q_id]['word_count'] / $user_no : 0,
            'bottom' => (isset($bottom_log[$q_id]) and $user_no != 0) ? $bottom_log[$q_id]['word_count'] / $user_no : 0
        );
        for ($i = 0; $i < 40; $i++) {
            if (isset($top_words[$i]['word']) or isset($bottom_words[$i]['word'])) {
                if (isset($top_words[$i]['word'])) {
                    $question_data['words']['top'][$i]['used'] = $top_words[$i]['used'];
                    $question_data['words']['top'][$i]['word'] = $top_words[$i]['word'];
                }
                if (isset($bottom_words[$i]['word'])) {
                    $question_data['words']['bottom'][$i]['used'] = $bottom_words[$i]['used'];
                    $question_data['words']['bottom'][$i]['word'] = $bottom_words[$i]['word'];
                }
            }
        }

        if (empty($top_log[$q_id]['totalpos']) or empty($bottom_log[$q_id]['totalpos'])) {
            $question_data['d'] = 0;
        } else {
            $question_data['d'] = ($top_log[$q_id]['mark'] / $top_log[$q_id]['totalpos']) - ($bottom_log[$q_id]['mark'] / $bottom_log[$q_id]['totalpos']);
        }
        if (isset($freq_log[$q_id]['unmarked']) and $freq_log[$q_id]['unmarked'] > 0) {
            $question_data['unmarked'] = true;
            $question_data['unmarked_count'] = $freq_log[$question_data['id']]['unmarked'];
        } else {
            $question_data['unmarked'] = false;
            if (empty($freq_log[$q_id]['totalpos'])) {
                $question_data['p'] = 0;
            } else {
                $question_data['p'] = $freq_log[$q_id]['mark'] / $freq_log[$q_id]['totalpos'];
            }
            $question_data['p_stats'] = pStats($question_data['p'], $q_id, 1, $pstats, $pstats_array);
            $question_data['d_stats'] = dStats($question_data['d'], $q_id, 1, $dstats, $dstats_array);
            $question_data['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total);
        }
    } elseif ($q_type == 'matrix') {
        $matching_source = explode('|', $q_media);
        $matching_width = explode('|', $q_media_width);
        $matching_height = explode('|', $q_media_height);
        $matching_alt = explode('|', $q_media_alt);
        $matching_num = explode('|', $q_media_num);
        $tmp_media_array = array();
        for ($i = 0; $i < count($matching_source); $i++) {
            $tmp_media_array[$matching_num[$i]] = array(
                'source' => $matching_source[$i],
                'width' => $matching_width[$i],
                'height' => $matching_height[$i],
                'alt' => $matching_alt[$i],
            );
        }
        $tmp_ext_scenarios = explode('|', $scenario);
        $tmp_answers_array = explode('|', $options[0]['correct']);

        if ($question_data['score_method'] == 'Mark per Question') {
            $ex_no++;
            $question_data['ex_no'] = $ex_no;
        }

        $question_data['cols'] = 7;
        if ($score_method == 'Mark per Option') {
            $question_data['cols']++;
        }
        $std_on = false;
        for ($i = 0; $i < count($options); $i++) {
            if (isset($tmp_std_array[$i])) {
                $std_on = true;
            }
        }
        if ($std_on) {
            $question_data['cols']++;
        }

        $question_data['options'] = [];

        for ($i = 1; $i <= (mb_substr_count($scenario, '|') + 1); $i++) {
            if ($tmp_ext_scenarios[$i - 1] != '') {
                $option_no = 1;
                foreach ($options as $individual_option) {
                    $question_data['options'][$option_no] = $individual_option['option_text']; // To make sure it's assigned with the same indexes as here, possibly sloppy
                    if ($option_no == 1) {
                        $correct_answer = $tmp_answers_array[$i - 1];
                        $question_data['parts'][$i]['d'] = calcDiscrimination($user_no, $top_log[$q_id], $bottom_log[$q_id], $i, $correct_answer);

                        $tmp_correct_no = (isset($freq_log[$q_id][$i][$correct_answer])) ? $freq_log[$q_id][$i][$correct_answer] : 0;
                        $tmp_top_no = (isset($top_log[$q_id][$i][$correct_answer])) ? $top_log[$q_id][$i][$correct_answer] : 0;
                        $tmp_bottom_no = (isset($bottom_log[$q_id][$i][$correct_answer])) ? $bottom_log[$q_id][$i][$correct_answer] : 0;

                        if ($question_data['score_method'] == 'Mark per Option') {
                            $question_data['parts'][$i]['excluded'] = $exclusions->is_question_part_excluded($q_id, $i - 1); // Might be better to set at question / part level?
                            if ($question_data['parts'][$i]['excluded']) {
                                $question_data['parts'][$i]['exclusions'] = $exclusions->get_exclusion_part_by_qid($q_id, $i - 1);
                            }
                            $ex_no++;
                            $question_data['parts'][$i]['ex_no'] = $ex_no;
                        }

                        $question_data['parts'][$i]['p'] = ($user_total != 0) ? $tmp_correct_no / $user_total : 0;
                        $question_data['parts'][$i]['p_top'] = ($user_no != 0) ? $tmp_top_no / $user_no : 0;
                        $question_data['parts'][$i]['p_bottom'] = ($user_no != 0) ? $tmp_bottom_no / $user_no : 0;

                        $question_data['parts'][$i]['p_stats'] = pStats($question_data['parts'][$i]['p'], $q_id, $i, $pstats, $pstats_array);
                        $question_data['parts'][$i]['d_stats'] = dStats($question_data['parts'][$i]['d'], $q_id, $i, $dstats, $dstats_array);
                        $question_data['parts'][$i]['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total, $i);

                        $question_data['parts'][$i]['t'] = $question_data['parts'][$i]['p'] * 100;
                        $question_data['parts'][$i]['u'] = $question_data['parts'][$i]['p_top'] * 100;
                        $question_data['parts'][$i]['l'] = $question_data['parts'][$i]['p_bottom'] * 100;

                        if (isset($tmp_std_array[$i - 1])) {
                            $question_data['parts'][$i]['std'] = $tmp_std_array[$i - 1];
                        }

                        $question_data['parts'][$i]['scenario'] = $tmp_ext_scenarios[$i - 1];
                    }
                    if ($correct_answer == $option_no) {
                        $question_data['parts'][$i]['correct'] = $option_no;
                    }
                    $option_no++;
                }
            }
        }
    } elseif ($q_type == 'extmatch') {
        $matching_scenarios = explode('|', $scenario);
        $matching_source = explode('|', $q_media);
        $matching_width = explode('|', $q_media_width);
        $matching_height = explode('|', $q_media_height);
        $matching_alt = explode('|', $q_media_alt);
        $matching_num = explode('|', $q_media_num);
        $tmp_ext_scenarios = explode('|', $scenario);
        $tmp_answers_array = explode('|', $options[0]['correct']); // Seems to be duplicated on each array entry
        $tmp_media_array = array();
        for ($i = 0; $i < count($matching_source); $i++) {
            $tmp_media_array[$matching_num[$i]] = array(
                'source' => $matching_source[$i],
                'width' => $matching_width[$i],
                'height' => $matching_height[$i],
                'alt' => $matching_alt[$i],
            );
        }
        $tmp_text_no = 0;
        for ($part_id = 0; $part_id < 10; $part_id++) {
            if (isset($matching_scenarios[$part_id]) and trim(strip_tags($matching_scenarios[$part_id])) != '') {
                $tmp_text_no++;
            }
        }
        $tmp_media_no = 0;
        for ($part_id = 1; $part_id <= 10; $part_id++) {
            if (!empty($tmp_media_array[$part_id]['source'])) {
                $tmp_media_no++;
            }
        }
        $total_scenarios = max($tmp_text_no, $tmp_media_no);

        if ($score_method == 'Mark per Question') {
            $ex_no++;
            $question_data['ex_no'] = $ex_no;
        }

        if (!empty($tmp_media_array[0]['source'])) {
            $question_data['media'] = array($tmp_media_array[0]['source'], $tmp_media_array[0]['width'], $tmp_media_array[0]['height'], $tmp_media_array[0]['alt'], '', true);
        }

        $std_part = 0;
        $section = 0;

        for ($i = 1; $i <= $total_scenarios; $i++) {
            $tmp_correct_no = 0;
            $correct_stems = 0;
            if (!empty($tmp_media_array[$i]['source'])) {
                $question_data['parts'][$i]['media'] = array($tmp_media_array[$i]['source'], $tmp_media_array[$i]['width'], $tmp_media_array[$i]['height'], $tmp_media_array[$i]['alt'], '', true);
            }

            if (isset($tmp_ext_scenarios[$i - 1])) {
                $question_data['parts'][$i]['scenario'] = $tmp_ext_scenarios[$i - 1];
            }

            $option_no = 1;
            foreach ($options as $individual_option) {
                $specific_answers = explode('$', $tmp_answers_array[$i - 1]);
                if (in_array($option_no, $specific_answers)) {
                    $correct_stems++;
                }
                $option_no++;
            }

            $question_data['correct_stems'] = $correct_stems;

            if ($question_data['score_method'] == 'Mark per Option') {
                if ($question_data['excluded']) {
                    $question_data['parts'][$i]['exclusions'] = $exclusions->get_exclusion_part_by_qid($q_id, $section);
                }
                $ex_no++;
                $question_data['parts'][$i]['ex_no'] = $ex_no;
            }

            $sub_d = 0;
            $sub_d_no = 0;
            $option_no = 1;
            $correct_stems = 0;

            foreach ($options as $individual_option) {
                $specific_answers = explode('$', $tmp_answers_array[$i - 1]);
                if ($score_method == 'Mark per Option' and $question_data['excluded'] and $exclusions->get_exclusion_part_by_qid($q_id, $section) == '1') {
                    $question_data['parts'][$i]['options'][$option_no]['excluded'] = '1';
                    $question_data['parts'][$i]['options'][$option_no]['exclusions'] = $exclusions->get_exclusion_part_by_qid($q_id, $section);
                } else { // To avoid the error Undefined array key "excluded" added 'excluded' key and value as zero.
                    $question_data['parts'][$i]['options'][$option_no]['excluded'] = '0';
                    $question_data['parts'][$i]['options'][$option_no]['exclusions'] = '';
                }
                $question_data['parts'][$i]['options'][$option_no]['option_text'] = $individual_option['option_text'];
                if (in_array($option_no, $specific_answers)) {
                    $question_data['parts'][$i]['options'][$option_no]['specific_answers'] = true;
                    if (isset($freq_log[$q_id][$i][$option_no]) and $user_total != 0) {
                        $question_data['parts'][$i]['options'][$option_no]['t'] = ($freq_log[$q_id][$i][$option_no] / $user_total) * 100;
                    } else {
                        $question_data['parts'][$i]['options'][$option_no]['t'] = 0;
                    }
                    if (isset($top_log[$q_id][$i][$option_no]) and $user_no != 0) {
                        $question_data['parts'][$i]['options'][$option_no]['u'] = ($top_log[$q_id][$i][$option_no] / $user_no) * 100;
                    } else {
                        $question_data['parts'][$i]['options'][$option_no]['u'] = 0;
                    }
                    if (isset($bottom_log[$q_id][$i][$option_no]) and $user_no != 0) {
                        $question_data['parts'][$i]['options'][$option_no]['l'] = ($bottom_log[$q_id][$i][$option_no] / $user_no) * 100;
                    } else {
                        $question_data['parts'][$i]['options'][$option_no]['l'] = 0;
                    }
                    $sub_d += $question_data['parts'][$i]['options'][$option_no]['u'] / 100 - $question_data['parts'][$i]['options'][$option_no]['l'] / 100; // Using /100 to avoid re-running calculations twice
                    $sub_d_no++;
                    if (isset($tmp_std_array[$std_part])) {
                        $question_data['parts'][$i]['options'][$option_no]['std'] = $tmp_std_array[$std_part]; // Is this referencing array index? Also, should this just be $question_data['parts']?
                    } else {
                        $question_data['parts'][$i]['options'][$option_no]['std'] = '';
                    }
                    $correct_stems++;
                    if (isset($freq_log[$q_id][$i][$option_no])) {
                        $tmp_correct_no += $freq_log[$q_id][$i][$option_no];
                    }
                    $std_part++;
                } else {
                    $question_data['parts'][$i]['options'][$option_no]['specific_answers'] = false;
                    if (isset($freq_log[$q_id][$i][$option_no]) and $user_total != 0) {
                        $question_data['parts'][$i]['options'][$option_no]['t'] = ($freq_log[$q_id][$i][$option_no] / $user_total) * 100;
                    } else {
                        $question_data['parts'][$i]['options'][$option_no]['t'] = 0;
                    }
                    if (isset($top_log[$q_id][$i][$option_no]) and $user_no != 0) {
                        $question_data['parts'][$i]['options'][$option_no]['u'] = ($top_log[$q_id][$i][$option_no] / $user_no) * 100;
                    } else {
                        $question_data['parts'][$i]['options'][$option_no]['u'] = 0;
                    }
                    if (isset($bottom_log[$q_id][$i][$option_no]) and $user_no != 0) {
                        $question_data['parts'][$i]['options'][$option_no]['l'] = ($bottom_log[$q_id][$i][$option_no] / $user_no) * 100;
                    } else {
                        $question_data['parts'][$i]['options'][$option_no]['l'] = 0;
                    }
                }
                $option_no++;
            }
            $section = $std_part;
            $question_data['parts'][$i]['d'] = ($sub_d / $sub_d_no);
            $d_no++;
            $d_total += $question_data['parts'][$i]['d'];

            $question_data['parts'][$i]['p_stats'] = pStats($tmp_correct_no / ($correct_stems * $user_total), $q_id, $i, $pstats, $pstats_array);
            $question_data['parts'][$i]['d_stats'] = dStats($question_data['parts'][$i]['d'], $q_id, $i, $dstats, $dstats_array);
            $question_data['parts'][$i]['x_stats'] = xStats($q_type, $q_id, $freq_log, $user_total, $i);
        }
    }

    $question_data['legacy_correct'] = $options[array_key_last($options)]['correct'] ?? ''; // Emulate the old behaviour for the data-qcorrect field
    return $question_data;
}

/**
 * Retrieve question main data
 * @param \mysqli $mysqli MySQL object
 * @param $paperID int      Paper unique ID
 * @param $qids int[]|false Optional list of question IDs to restrict data to
 *
 * @return mixed[]
 */
function get_exam_questions(\mysqli $mysqli, $paperID, $qids = false)
{
    $sql = 'SELECT screen, q_id, q_type, theme, scenario, leadin, score_method,
        display_method
        FROM (papers, questions)
        WHERE papers.paper = ? AND papers.question=questions.q_id';

    if ($qids !== false) {
        // Safely bind supplied QIDs to database query.
        $sql .= ' AND q_id IN (' . implode(',', array_fill(0, count($qids), '?')) . ')'
            . ' ORDER BY screen, display_pos';
        $result = $mysqli->prepare($sql);
        $result->bind_param(str_repeat('i', count($qids) + 1), $paperID, ...$qids);
    } else {
        $sql .= ' ORDER BY screen, display_pos';
        $result = $mysqli->prepare($sql);
        $result->bind_param('i', $paperID);
    }

    $result->execute();
    $result->bind_result($screen_no, $q_id, $q_type, $theme, $scenario, $leadin, $score_method, $display_method);
    $result->store_result();

    $question_data = [];
    while ($result->fetch()) {
        $media = QuestionUtils::getMediaAsString($q_id);
        $question_data[$q_id] = array(
            'screen_no' => $screen_no,
            'q_type' => $q_type,
            'theme' => $theme,
            'scenario' => $scenario,
            'leadin' => $leadin,
            'score_method' => $score_method,
            'display_method' => $display_method,
            'q_media' => $media['source'],
            'q_media_width' => $media['width'],
            'q_media_height' => $media['height'],
            'q_media_alt' => $media['alt'],
            'q_media_num' => $media['num']
        );
    }
    $result->free_result();
    return $question_data;
}

/**
 * Retrieve question option data
 * @param $mysqli  \mysqli     MySQL object
 * @param $paperID int         Paper unique ID
 * @param $qids    int[]|false Optional list of question IDs to restrict data to
 *
 * @return mixed[]
 */
function get_exam_options(\mysqli $mysqli, $paperID, $qids = false)
{
    $sql = 'SELECT question, option_text, source, width, height, alt, correct
    FROM papers
    LEFT JOIN options ON papers.question = options.o_id
    LEFT JOIN options_media ON options.id_num = options_media.oid
    LEFT JOIN media ON options_media.mediaid = media.id
    WHERE papers.paper = ?';

    if ($qids !== false) {
        // Safely bind supplied QIDs to database query.
        $sql .= ' AND o_id IN (' . implode(',', array_fill(0, count($qids), '?')) . ')'
            . ' ORDER BY o_id, id_num';
        $result = $mysqli->prepare($sql);
        $result->bind_param(str_repeat('i', count($qids) + 1), $paperID, ...$qids);
    } else {
        $sql .= ' ORDER BY o_id, id_num';
        $result = $mysqli->prepare($sql);
        $result->bind_param('i', $paperID);
    }
    $result->execute();
    $result->bind_result($o_id, $option_text, $o_media, $o_media_width, $o_media_height, $o_media_alt, $correct);
    $result->store_result();
    $option_data = [];

    while ($result->fetch()) {
        $option_data[$o_id][] = array(
            'option_text' => $option_text,
            'o_media' => $o_media,
            'o_media_width' => $o_media_width,
            'o_media_height' => $o_media_height,
            'o_media_alt' => $o_media_alt,
            'correct' => $correct
        );
    }
    $result->free_result();

    return $option_data;
}

/**
 * Retrieve top and bottom cohorts.
 * @param $cohort_percent   int       Cohort percentage
 * @param $students_only    bool      Students-only flag
 * @param $moduleIDs        int[]     Modules paper is in
 * @param $paper_type       int       Paper type
 * @param $repmodule        string    Student module
 * @param $repcourse        string    Student course
 * @param $paperID          int       Paper unique ID
 * @param $startdate        string    Paper start date
 * @param $enddate          string    Paper end date
 * @param $bottom_cohort    array[]   Bottom cohort frequency log
 * @param $top_cohort       array[]   Top cohort frequency log
 * @param $freq_log         array[]   Main frequency log
 * @param $bottom_log_array array[] Bottom log array
 * @param $top_log_array    array[] Top log array
 * @param $user_no          int
 * @param $user_total       int       Total user count
 * @param $mysqli           \mysqli   MySQL object
 *
 * @return void
 */
function capture_log_data(
    int $cohort_percent,
    bool $students_only,
    array $moduleIDs,
    int $paper_type,
    string $repcourse,
    string $repmodule,
    int $paperID,
    string $startdate,
    string $enddate,
    array &$bottom_cohort,
    array &$top_cohort,
    array &$freq_log,
    array &$bottom_log_array,
    array &$top_log_array,
    int &$user_no,
    int &$user_total,
    \mysqli $mysqli
) {
    // Using references for returns for now, could be nicer
    // Get all the users on the module(s) the paper is on.
    $users_on_modules = '';
    if (!empty($moduleIDs)) {
        $moduleIDs_in = "'" . implode(',', array_keys($moduleIDs)) . "'";
        $mod_query = $mysqli->prepare("SELECT idMod, userID, moduleid FROM modules_student, modules WHERE modules.id = modules_student.idMod AND idMod IN ($moduleIDs_in)");
        $mod_query->execute();
        $mod_query->bind_result($idMod, $tmp_userID, $tmp_moduleid);
        $mod_query->store_result();
        while ($mod_query->fetch()) {
            if ($repmodule != '' and $idMod != $repmodule) {
                continue; //this user is not on the module set in repmodule so dont put them in the array
            }
            if ($users_on_modules == '') {
                $users_on_modules = "'" . $tmp_userID . "'";
            } else {
                $users_on_modules .= ",'" . $tmp_userID . "'";
            }
        }
        $mod_query->close();
    }

    $student_modules_sql = '';
    if ($users_on_modules != '' and !empty($repmodule)) {
        $student_modules_sql = " AND log_metadata.userID IN ($users_on_modules)";
    }

    if ($students_only or $paper_type == '0') {
        $from_sql = 'log' . $paper_type . ', users, log_metadata, user_roles ur JOIN roles r ON ur.roleid = r.id';
        $from_sql2 = 'log1, users, log_metadata, user_roles ur JOIN roles r ON ur.roleid = r.id';
        $roles_sql = " AND users.id = ur.userid AND r.name IN ('Student', 'graduate')";
    } else {
        $from_sql = '(log' . $paper_type . ', users, log_metadata)';
        $roles_sql = '';
    }

    // Calculate top and bottom cohorts.
    $time_int = \log::getStartInterval($paper_type);
    if ($paper_type == \assessment::TYPE_FORMATIVE) {
        $progress_time_int = \log::getStartInterval(\assessment::TYPE_PROGRESS);
        $sql = "(
                    SELECT
                        username, sum(mark) AS total_mark, started
                    FROM
                        $from_sql
                    WHERE
                        log0.metadataID = log_metadata.id AND log_metadata.userID = users.id $roles_sql
                        AND paperID = ? AND grade LIKE ?
                        AND DATE_ADD(started, INTERVAL $time_int MINUTE) >= ? AND started <= ?
                        AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE 'Staff%'
                        AND student_grade NOT LIKE '%nhs%'
                        $student_modules_sql
                    GROUP BY username, paperID, started
                ) UNION ALL (
                    SELECT
                        username, sum(mark) AS total_mark, log_metadata.started
                    FROM
                        $from_sql2
                    WHERE
                        log1.metadataID = log_metadata.id AND log_metadata.userID = users.id $roles_sql
                        AND paperID = ?
                        AND DATE_ADD(started, INTERVAL $progress_time_int MINUTE) >= ? AND started <= ?
                        " . str_replace('log0', 'log1', $student_modules_sql) . '
                    GROUP BY username, started
                )
                ORDER BY total_mark ASC, username';
        $result = $mysqli->prepare($sql);
        $result->bind_param('isssiss', $paperID, $repcourse, $startdate, $enddate, $paperID, $startdate, $enddate);
    } else {
        $sql = "SELECT
                    username, sum(mark) AS total_mark, started
                FROM
                    $from_sql
                WHERE
                    log$paper_type.metadataID = log_metadata.id $roles_sql AND log_metadata.userID = users.id
                    AND paperID = ? AND grade LIKE ?
                    AND DATE_ADD(started, INTERVAL $time_int MINUTE) >= ? AND started <= ? 
                    $student_modules_sql 
                GROUP BY
                    username, started
                ORDER BY
                    total_mark ASC, username";
        $result = $mysqli->prepare($sql);
        $result->bind_param('isss', $paperID, $repcourse, $startdate, $enddate);
    }
    $result->execute();
    $result->bind_result($username, $total_mark, $started);
    $result->store_result();

    $student_no = 0;
    $bottom_cohort = array();
    $user_no = round(($result->num_rows / 100) * $cohort_percent);
    $user_total = $result->num_rows;

    if ($user_total == 0) {
        return false;
    } elseif ($user_total == 1) {
        // If a single user load them into top and bottom cohorts.
        $result->fetch();
        $bottom_cohort[$started][$username] = '';
        $top_cohort[$started][$username] = '';
        $student_no = 1;
        $user_no = 1;
    } else {
        while ($result->fetch()) {
            if ($student_no < $user_no) {
                $bottom_cohort[$started][$username] = '';
            } elseif ($student_no >= ($user_total - $user_no)) {
                $top_cohort[$started][$username] = '';
            }
            $student_no++;
        }
    }
    $result->close();

    if ($students_only or $paper_type == '0') {
        $from_sql = 'log' . $paper_type . ', log_metadata, questions, users, user_roles ur JOIN roles r ON ur.roleid = r.id';
        $from_sql2 = 'log1, log_metadata, questions, users, user_roles ur JOIN roles r ON ur.roleid = r.id';
        $roles_sql = " AND users.id = ur.userid AND r.name IN ('Student', 'graduate')";
    } else {
        $from_sql = 'log' . $paper_type . ', log_metadata, questions, users';
        $roles_sql = '';
    }

    if ($paper_type == '0') {
        $result = $mysqli->prepare("(SELECT username, log_metadata.userID, log0.q_id, user_answer, q_type, score_method, display_method, settings, mark, totalpos, option_order, started FROM $from_sql WHERE log0.metadataID = log_metadata.id $roles_sql AND log0.q_id = questions.q_id AND paperID = ? AND grade LIKE ? AND users.id = log_metadata.userID $roles_sql AND started >= ? AND started <= ? $student_modules_sql) UNION ALL (SELECT username, log_metadata.userID, log1.q_id, user_answer, q_type, score_method, display_method, settings, mark, totalpos, option_order, started FROM $from_sql2 WHERE log1.metadataID = log_metadata.id $roles_sql AND log1.q_id=questions.q_id AND paperID = ? AND grade LIKE ? AND users.id = log_metadata.userID $roles_sql AND started >= ? AND started <= ? " . str_replace('log0', 'log1', $student_modules_sql) . ')');
        $result->bind_param('isssisss', $paperID, $repcourse, $startdate, $enddate, $paperID, $repcourse, $startdate, $enddate);
    } else {
        $result = $mysqli->prepare("SELECT username, log_metadata.userID, log$paper_type.q_id, user_answer, q_type, score_method, display_method, settings, mark, totalpos, option_order, started FROM $from_sql WHERE log$paper_type.metadataID = log_metadata.id $roles_sql AND log$paper_type.q_id = questions.q_id AND paperID = ? AND grade LIKE ? AND users.id = log_metadata.userID $roles_sql AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ? $student_modules_sql");
        $result->bind_param('isss', $paperID, $repcourse, $startdate, $enddate);
    }
    $result->execute();
    $result->bind_result($username, $tmp_userID, $question_ID, $tmp_answer, $q_type, $score_method, $display_method, $settings, $mark, $totalpos, $option_order, $started);

    while ($result->fetch()) {
        storeData($freq_log, $question_ID, $tmp_answer, $q_type, $display_method, $settings, $mark, $totalpos, 'all');
        if (isset($bottom_cohort[$started][$username])) {
            storeData($bottom_log_array, $question_ID, $tmp_answer, $q_type, $display_method, $settings, $mark, $totalpos, 'bottom');
        }
        if (isset($top_cohort[$started][$username])) {
            storeData($top_log_array, $question_ID, $tmp_answer, $q_type, $display_method, $settings, $mark, $totalpos, 'top');
        }
    }
    $result->close();
}
