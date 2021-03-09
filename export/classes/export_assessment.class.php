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

namespace export;

use csv\csv_write_exception;

/**
 * Export assessment data to csv file format
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 */
class export_assessment extends exporter
{
    /**
     * Required fields
     * @var array
     */
    public const DEFAULT = array(
        'Gender',
        'Title',
        'Surname',
        'First Names',
        'Student ID',
        'Course',
        'Year',
        'Started',
    );

    /**
     * Convert a string to comma separated HEX numbers to the decimal equivalent
     * @param $data
     * @return string
     */
    public static function hex_to_dec($data)
    {
        $items = explode(',', $data);
        $response = '';

        foreach ($items as $item) {
            $response .= hexdec($item) . ',';
        }

        return rtrim($response, ',');
    }

    /**
     * Swap array indicies
     * @param $array the array
     * @param $ix1 index 1
     * @param $ix2 index 2
     * @return array
     */
    private static function array_swap($array, $ix1, $ix2)
    {
        $tmp = $array[$ix1];
        $array[$ix1] = $array[$ix2];
        $array[$ix2] = $tmp;

        return $array;
    }

    /**
     * Add standard columns
     * @param integer $i question number
     * @param integer $sec section
     * @param string $csv csv string to update
     * @param string $col1 string for first column
     * @param boolean $random random question flag
     * @param string $subsec sub section
     */
    private static function add_random_column_standard($i, $sec, &$csv, $col1, $random, $subsec = '')
    {
        if ($random) {
            $csv[] = $col1 . ':user';
            $csv[] = 'Q' . ($i + 1) . chr($sec + 64) . $subsec . ':correct';
        } else {
            $csv[] = $col1;
        }
    }

    /**
     * Make row safe for csv export.
     * @param array $data row data
     * @return array
     */
    private static function make_safe($row)
    {
        $safe = array();
        foreach ($row as $r) {
            $safe[] = strip_tags($r);
        }
        return $safe;
    }

    /**
     * Dymnamically generated header
     * @var array
     */
    public $dynamic_headers;

    /**
     * Do the assessment export.
     * @param array $data csv lines
     */
    public function execute($data)
    {
        $header = array_merge(self::DEFAULT, $this->dynamic_headers);
        try {
            $this->handler->required_header($header);
            foreach ($data as $line) {
                $this->handler->write_line(self::make_safe($line));
            }
        } catch (csv_write_exception $e) {
            $userObject = \UserObject::get_instance();
            log_error($userObject->get_user_ID(), 'Export Assessment CSV', 'Application Error', $e->getMessage(), $_SERVER['PHP_SELF'], __LINE__, '', null, null, null);
            exit(0);
        }
        $this->handler->send_file();
    }

    /**
     * Create the dynamic header
     * @param array $paper paer info
     * @param \Exclusion $exclusions paper exclusions
     */
    public function create_dynamic_header($paper, $exclusions)
    {
        // Write out the headings.
        $csvdata = array();
        $numerals = array('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii', 'xiii', 'xiv', 'xv', 'xvi', 'xvii', 'xviii', 'xix', 'xx');
        for ($i = 0; $i < count($paper); $i++) {
            $tmp_question_ID = $paper[$i]['ID'];
            $tmp_exclude = $exclusions->get_exclusions_by_qid($tmp_question_ID);
            // If a random question, get the first of the associated questions from the block. If none exist, output nothing
            $question = $paper[$i];
            $skip_random = false;
            $is_random = false;
            if ($question['type'] == 'random' and isset($question['rand_ids'])) {
                $tmp_id = $question['ID'];
                $question = \QuestionUtils::get_correct_answer($question, $question['rand_ids'][0], $this->config->db);
                if ($tmp_id != $question['ID']) {
                    $is_random = true;
                } else {
                    $skip_random = true;
                }
            }
            if (!$skip_random) {
                switch ($question['type']) {
                    case 'blank':
                        for ($sec = 1; $sec <= mb_substr_count($question['correct'], ','); $sec++) {
                            if (mb_substr($tmp_exclude, $sec - 1, 1) == '0') {
                                $col1 = 'Q' . ($i + 1) . chr($sec + 64);
                                self::add_random_column_standard($i, $sec, $csvdata, $col1, $is_random);
                            }
                        }
                        break;
                    case 'extmatch':
                        $correct_parts = explode(',', $question['correct']);
                        $partID = 0;
                        for ($sec = 1; $sec < count($correct_parts); $sec++) {
                            if ($correct_parts[$sec] != '' and mb_substr($tmp_exclude, $partID, 1) == '0') {
                                if (mb_strpos($correct_parts[$sec], '$') === false) {
                                    $col1 = 'Q' . ($i + 1) . $numerals[$sec - 1];
                                    self::add_random_column_standard($i, $sec, $csvdata, $col1, $is_random);
                                } else {
                                    $num_ix = 0;
                                    $correct_subparts = explode('$', $correct_parts[$sec]);
                                    foreach ($correct_subparts as $subpart) {
                                                    $col1 = 'Q' . ($i + 1) . $numerals[$sec - 1] . chr($num_ix + 65);
                                                    self::add_random_column_standard($i, $sec, $csvdata, $col1, $is_random, $numerals[$num_ix]);
                                                    $num_ix++;
                                    }
                                }
                            }
                            $partID += mb_substr_count($correct_parts[$sec], '$') + 1;
                        }
                        break;
                    case 'hotspot':
                        $correct_parts = explode('|', $question['correct']);
                        for ($sec = 0; $sec < count($correct_parts); $sec++) {
                            if (mb_substr($tmp_exclude, $sec, 1) == '0') {
                                  $col1 = 'Q' . ($i + 1) . chr($sec + 65);
                                  self::add_random_column_standard($i, $sec, $csvdata, $col1, $is_random);
                            }
                        }
                        break;
                    case 'labelling':
                        $sec = 1;
                        $tmp_first_split = explode(';', $question['correct']);
                        $tmp_second_split = explode('$', $tmp_first_split[11]);
                        for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
                            if (mb_substr($tmp_second_split[$label_no], 0, 1) != '|' and $tmp_second_split[$label_no - 2] > 219) {
                                if (mb_substr($tmp_exclude, $sec - 1, 1) == '0') {
                                    $col1 = 'Q' . ($i + 1) . chr($sec + 64);
                                    self::add_random_column_standard($i, $sec, $csvdata, $col1, $is_random);
                                }
                                  $sec++;
                            }
                        }
                        break;
                    case 'matrix':
                        $correct_parts = explode(',', $question['correct']);
                        for ($sec = 1; $sec < count($correct_parts); $sec++) {
                            if (mb_substr($tmp_exclude, $sec - 1, 1) == '0' and $correct_parts[$sec] != '') {
                                  $col1 = 'Q' . ($i + 1) . chr($sec + 64);
                                  self::add_random_column_standard($i, $sec, $csvdata, $col1, $is_random);
                            }
                        }
                        break;
                    case 'rank':
                        if ($tmp_exclude{0} == '0') {
                            for ($sec = 1; $sec <= mb_substr_count($question['correct'], ','); $sec++) {
                                  $col1 = 'Q' . ($i + 1) . chr($sec + 64);
                                  self::add_random_column_standard($i, $sec, $csvdata, $col1, $is_random);
                            }
                        }
                        break;
                    case 'true_false':
                    case 'dichotomous':
                        for ($sec = 1; $sec <= mb_substr_count($question['correct'], ','); $sec++) {
                            if (mb_substr($tmp_exclude, $sec - 1, 1) == '0') {
                                  $col1 = 'Q' . ($i + 1) . chr($sec + 64);
                                  self::add_random_column_standard($i, $sec, $csvdata, $col1, $is_random);
                            }
                        }
                        break;
                    case 'mrq':
                        for ($sec = 1; $sec <= mb_substr_count($question['correct'], ','); $sec++) {
                            if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                                  $col1 = 'Q' . ($i + 1) . chr($sec + 64);
                                  self::add_random_column_standard($i, $sec, $csvdata, $col1, $is_random);
                            }
                        }
                        if ($question['score_method'] == 'other') {
                            $csvdata[] = 'Q' . ($i + 1) . '.other';
                        }
                        break;
                    case 'enhancedcalc':
                        if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                            $csvdata[] = 'Q' . ($i + 1) . ':user';
                            $csvdata[] = 'Q' . ($i + 1) . ':correct';
                            if (!$is_random) {
                                  $csvdata[] = 'Q' . ($i + 1) . ':variables';
                            }
                        }
                        break;
                    default:
                        if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                            if ($is_random) {
                                  $csvdata[] = 'Q' . ($i + 1) . ':user';
                                  $csvdata[] = 'Q' . ($i + 1) . ':correct';
                            } else {
                                $csvdata[] = 'Q' . ($i + 1);
                            }
                        }
                        break;
                }
            }
        }
        $this->dynamic_headers = $csvdata;
    }

    /**
     * Create correct answers row
     * @param array $paper paer info
     * @param \Exclusion $exclusions paper exclusions
     * @param string $mode the display mode
     * @param array $string the language pack
     * @param string $language the language in use
     * @return array
     */
    public function create_correct_answer($paper, $exclusions, $mode, $string, $language)
    {
        $csvdata[0][] = $string['correctanswers'];
        $csvdata[0][] = '';
        $csvdata[0][] = '';
        $csvdata[0][] = '';
        $csvdata[0][] = '';
        $csvdata[0][] = '';
        $csvdata[0][] = '';
        $csvdata[0][] = '';

        for ($i = 0; $i < count($paper); $i++) {
            $tmp_question_ID = $paper[$i]['ID'];
            $tmp_exclude = $exclusions->get_exclusions_by_qid($tmp_question_ID);
            // If a random question, get the first of the associated questions from the block. If none exist, output nothing
            $question = $paper[$i];
            $skip_random = false;
            $is_random = false;
            if ($question['type'] == 'random' and isset($question['rand_ids'])) {
                $tmp_id = $question['ID'];
                $question = \QuestionUtils::get_correct_answer($question, $question['rand_ids'][0], $this->config->db);
                if ($tmp_id != $question['ID']) {
                    $is_random = true;
                } else {
                    $skip_random = true;
                }
            }

            if (!$skip_random) {
                switch ($question['type']) {
                    case 'area':
                        if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                            if ($is_random) {
                                $csvdata[0][] = '';
                                $csvdata[0][] = '';
                            } else {
                                $csvdata[0][] = self::hex_to_dec(ltrim($question['correct'], ','));
                            }
                        }
                        break;
                    case 'blank':
                        $correct_parts = explode(',', $question['correct']);
                        for ($partID = 1; $partID < count($correct_parts); $partID++) {
                            if (mb_substr($tmp_exclude, $partID - 1, 1) == '0') {
                                if ($is_random) {
                                    $csvdata[0][] = '';
                                    $csvdata[0][] = '';
                                } else {
                                    $csvdata[0][] = $correct_parts[$partID];
                                }
                            }
                        }
                        break;
                    case 'flash':
                        if ($is_random) {
                            $csvdata[0][] = '';
                            $csvdata[0][] = '';
                        } else {
                            $csvdata[0][] = '';
                        }
                        break;
                    case 'extmatch':
                        $correct_parts = explode(',', $question['correct']);
                        $correct_text_parts = explode("\t", $question['correct_text']);
                        $partID = 1;
                        for ($outer = 1; $outer < count($correct_parts); $outer++) {
                            if ($correct_parts[$outer] != '' and mb_substr($tmp_exclude, $partID - 1, 1) == '0') {
                                if ($is_random) {
                                    $csvdata[0][] = '';
                                    $csvdata[0][] = '';
                                } else {
                                    if ($mode == 'numeric') {
                                                    $csvdata[0][] = str_replace('$', '","', $correct_parts[$outer]);
                                    } else {
                                        if (mb_strpos($correct_parts[$outer], '$') === false) {
                                            $csvdata[0][] = $correct_text_parts[$correct_parts[$outer]];
                                        } else {
                                            $correct_subparts = explode('$', $correct_parts[$outer]);
                                            for ($k = 0; $k < count($correct_subparts); $k++) {
                                                $subpart = $correct_subparts[$k];
                                                $csvdata[0][] = $correct_text_parts[$subpart];
                                            }
                                        }
                                    }
                                }
                            }
                            $partID += mb_substr_count($correct_parts[$outer], '$') + 1;
                        }
                        break;
                    case 'matrix':
                        $correct_parts = explode(',', $question['correct']);
                        $correct_text_parts = explode("\t", $question['correct_text']);
                        for ($partID = 1; $partID < count($correct_parts); $partID++) {
                            if (mb_substr($tmp_exclude, $partID - 1, 1) == '0' and $correct_parts[$partID] != '') {
                                if ($is_random) {
                                    $csvdata[0][] = '';
                                    $csvdata[0][] = '';
                                } else {
                                    if ($mode == 'numeric') {
                                                    $csvdata[0][] = $correct_parts[$partID];
                                    } else {
                                        $csvdata[0][] = $correct_text_parts[$correct_parts[$partID]];
                                    }
                                }
                            }
                        }
                        break;
                    case 'mrq':
                    case 'rank':
                        if ($question['type'] == 'rank') {
                            $question['correct'] = str_replace('0', 'N/A', $question['correct']);
                        }
                        if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                            if ($is_random) {
                                $csvdata[0][] = '';
                                $csvdata[0][] = '';
                            } else {
                                if ($mode == 'numeric') {
                                    $csvdata[0][] = $question['correct'];
                                } else {
                                    $correct_parts = explode(',', $question['correct']);
                                    $correct_text_parts = explode("\t", $question['correct_text']);
                                    for ($j = 1; $j < count($correct_parts); $j++) {
                                        if ($question['type'] == 'mrq' and $correct_parts[$j] == 'y') {
                                            $csvdata[0][] =  $correct_text_parts[$j];
                                        } elseif ($question['type'] == 'rank') {
                                            $csvdata[0][] = \StringUtils::ordinal_suffix($correct_parts[$j], $language);
                                        } else {
                                            $csvdata[0][] = '';
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'hotspot':
                        $correct_parts = explode('|', $question['correct']);
                        for ($partID = 0; $partID < count($correct_parts); $partID++) {
                            if (mb_substr($tmp_exclude, $partID - 1, 1) == '0') {
                                $csvdata[0][] = '';
                            }
                        }
                        break;
                    case 'labelling':
                        $sec = 1;
                        $tmp_first_split = explode(';', $question['correct']);
                        $tmp_second_split = explode('$', $tmp_first_split[11]);
                        for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
                            if (mb_substr($tmp_second_split[$label_no], 0, 1) != '|' and $tmp_second_split[$label_no - 2] > 219) {
                                if (mb_substr($tmp_exclude, $sec - 1, 1) == '0') {
                                    $tmp_third_split = explode('|', $tmp_second_split[$label_no]);
                                    if ($is_random) {
                                        $csvdata[0][] = '';
                                        $csvdata[0][] = '';
                                    } else {
                                        if ($mode == 'numeric') {
                                            $csvdata[0][] =  $tmp_third_split[1];
                                        } else {
                                            if ($ans = mb_strstr($tmp_third_split[0], '~', true)) {
                                                    $csvdata[0][] =  $ans;
                                            } else {
                                                $csvdata[0][] =  $tmp_third_split[0];
                                            }
                                        }
                                    }
                                }
                                  $sec++;
                            }
                        }
                        break;
                    case 'true_false':
                    case 'dichotomous':
                        $correct_parts = explode(',', $question['correct']);
                        for ($partID = 1; $partID < count($correct_parts); $partID++) {
                            if (mb_substr($tmp_exclude, $partID - 1, 1) == '0') {
                                if ($is_random) {
                                    $csvdata[0][] = '';
                                    $csvdata[0][] = '';
                                } else {
                                    $csvdata[0][] =  $correct_parts[$partID];
                                }
                            }
                        }
                        break;
                    case 'textbox':
                        if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                            if ($is_random) {
                                    $csvdata[0][] = '';
                                    $csvdata[0][] = '';
                            } else {
                                    $csvdata[0][] = '';
                            }
                        }
                        break;
                    case 'enhancedcalc':
                        if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                            $settings = json_decode($question['settings'], true);
                            if ($is_random) {
                                    $csvdata[0][] = '';
                                    $csvdata[0][] = '';
                            } else {
                                    $csvdata[0][] = '';
                                    $csvdata[0][] = $settings['answers'][0]['formula'];
                                    $csvdata[0][] = '';
                            }
                        }
                        break;
                    case 'sct':
                        $correct_text_parts = explode("\t", $question['correct_text']);
                        if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                            $correct = '';
                            $parts = explode(',', $question['correct']);
                            $max_correct = 0;
                            for ($partID = 1; $partID < count($parts); $partID++) {
                                if ($parts[$partID] > $max_correct) {
                                    $max_correct = $parts[$partID];
                                    if ($mode == 'numeric') {
                                        $correct = $partID;
                                    } else {
                                        $correct = $correct_text_parts[$partID];
                                    }
                                } elseif ($parts[$partID] == $max_correct and $max_correct > 0) {
                                    if ($mode == 'numeric') {
                                                        $correct .= ',' . $partID;
                                    } else {
                                        $correct .= ' OR ' . $correct_text_parts[$partID];
                                    }
                                }
                            }
                            if ($is_random) {
                                    $csvdata[0][] = '';
                                    $csvdata[0][] = '';
                            } else {
                                $csvdata[0][] =  $correct;
                            }
                        }
                        break;
                    default:
                        if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                            if ($is_random) {
                                    $csvdata[0][] = '';
                                    $csvdata[0][] = '';
                            } else {
                                if ($mode == 'numeric') {
                                    $csvdata[0][] = ltrim($question['correct'], ',');
                                    ;
                                } else {
                                    $corr_index = ltrim($question['correct'], ',');
                                    $correct_text_parts = explode("\t", $question['correct_text']);
                                    if (isset($correct_text_parts[$corr_index])) {
                                        $csvdata[0][] = $correct_text_parts[$corr_index];
                                    } else {
                                        $csvdata[0][] = '';
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        }
        return $csvdata;
    }

    /**
     * Create data row
     * @param $log_array paper log data
     * @param array $paper paer info
     * @param \Exclusion $exclusions paper exclusions
     * @param string $mode the display mode
     * @param array $string the language pack
     * @param string $language the language in use
     * @return array
     */
    public function create_data($log_array, $paper, $exclusions, $mode, $string, $language)
    {
        $csvdata = array();
        $j = 1;
        foreach ($log_array as $individual) {
            // Write out the raw data.
            if (isset($individual['gender'])) {
                $csvdata[$j][] = $individual['gender'];
            } else {
                $csvdata[$j][] = '';
            }
            if (isset($individual['title'])) {
                $csvdata[$j][] = $individual['title'];
            } else {
                $csvdata[$j][] = '';
            }
            if (isset($individual['surname'])) {
                $csvdata[$j][] = $individual['surname'];
            } else {
                $csvdata[$j][] = '';
            }
            if (isset($individual['first_names'])) {
                $csvdata[$j][] = $individual['first_names'];
            } else {
                $csvdata[$j][] = '';
            }
            if (isset($individual['student_id'])) {
                $csvdata[$j][] = $individual['student_id'];
            } else {
                $csvdata[$j][] = '';
            }
            if (isset($individual['course'])) {
                $csvdata[$j][] = $individual['course'];
            } else {
                $csvdata[$j][] = '';
            }
            if (isset($individual['year'])) {
                $csvdata[$j][] = $individual['year'];
            } else {
                $csvdata[$j][] = '';
            }
            if (isset($individual['started'])) {
                $csvdata[$j][] = $individual['started'];
            } else {
                $csvdata[$j][] = '';
            }
            for ($i = 0; $i < count($paper); $i++) {
                $tmp_question_ID = $paper[$i]['ID'];
                $tmp_screen = $paper[$i]['screen'];
                $tmp_exclude = $exclusions->get_exclusions_by_qid($tmp_question_ID);
                // If a random question, get the one that the user answered
                $question = $paper[$i];
                $skip_random = false;
                $is_random = false;
                if ($question['type'] == 'random') {
                    if (isset($question['rand_ids']) and count($question['rand_ids']) > 0) {
                        $rnd_found = false;
                        if (isset($individual[$tmp_screen])) {
                            $screen_ids = array_keys($individual[$tmp_screen]);
                            foreach ($question['rand_ids'] as $tmp_id) {
                                if (in_array($tmp_id, $screen_ids)) {
                                    $rnd_found = true;
                                    $question = \QuestionUtils::get_correct_answer($question, $tmp_id, $this->config->db);
                                    // The id returned will either be that of the question the user answered or the id of the random question.
                                    // We are only interested if it is the id of the question the user answered.
                                    if ($tmp_id == $question['ID']) {
                                          $is_random = true;
                                          $tmp_question_ID = $tmp_id;
                                    } else {
                                        $skip_random = true;
                                    }
                                    break;
                                }
                            }
                        }
                        if (!$rnd_found) {
                            reset($question['rand_ids']);
                            $tmp_question_ID = key($question['rand_ids']);
                            $question = \QuestionUtils::get_correct_answer($question, $tmp_question_ID, $this->config->db);
                        }
                    } else {
                        $skip_random = true;
                    }
                }

                if (!$skip_random) {
                    switch ($question['type']) {
                        case 'area':
                            if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                                if (isset($individual[$tmp_screen][$tmp_question_ID]) and $individual[$tmp_screen][$tmp_question_ID] != '') {
                                        $answer_parts = explode(';', $individual[$tmp_screen][$tmp_question_ID]);
                                    if (count($answer_parts) > 1) {
                                        $csvdata[$j][] =  self::hex_to_dec($answer_parts[1]);
                                    } else {
                                        $csvdata[$j][] = '';
                                    }
                                } else {
                                    $csvdata[$j][] = '';
                                }
                                if ($is_random) {
                                    $csvdata[$j][] =  self::hex_to_dec(ltrim($question['correct'], ','));
                                }
                            }
                            break;
                        case 'blank':
                            $correct_parts = explode(',', $question['correct']);
                            $tmp_answers = (isset($individual[$tmp_screen][$tmp_question_ID])) ? json_decode($individual[$tmp_screen][$tmp_question_ID]) : array_fill(0, count($correct_parts), null);
                            for ($partID = 0; $partID < count($correct_parts) - 1; $partID++) {
                                if (mb_substr($tmp_exclude, $partID, 1) == '0') {
                                    if ($tmp_answers[$partID] != 'u') {
                                          $csvdata[$j][] =  str_replace("\n", ' ', str_replace("\r", ' ', $tmp_answers[$partID]));
                                    } else {
                                        $csvdata[$j][] = '';
                                    }
                                    if ($is_random) {
                                        $csvdata[$j][] = $correct_parts[$partID];
                                    }
                                }
                            }
                            break;
                        case 'enhancedcalc':
                            if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                                if (isset($individual[$tmp_screen][$tmp_question_ID])) {        // Check for missing answers.
                                      $answer = json_decode($individual[$tmp_screen][$tmp_question_ID], true);
                                } else {
                                    $answer = '';
                                }
                                if (isset($answer['uans'])) {
                                    $csvdata[$j][] = $answer['uans'];
                                } else {
                                    $csvdata[$j][] = 'error';
                                }
                                if (isset($answer['cans'])) {
                                    $csvdata[$j][] = $answer['cans'];
                                } else {
                                    $csvdata[$j][] = 'error';
                                }
                                if (!$is_random) {
                                    $variables = '';
                                    if (isset($answer['vars'])) {
                                        foreach ($answer['vars'] as $var_name => $value) {
                                            if ($variables == '') {
                                                $variables .= $value;
                                            } else {
                                                $variables .= ',' . $value;
                                            }
                                        }
                                    }

                                    $csvdata[$j][] = $variables;
                                }
                            }
                            break;
                        case 'true_false':
                        case 'dichotomous':
                            $correct_parts = explode(',', $question['correct']);
                            for ($partID = 0; $partID < count($correct_parts) - 1; $partID++) {
                                if (mb_substr($tmp_exclude, $partID, 1) == '0') {
                                      $part_ans = (isset($individual[$tmp_screen][$tmp_question_ID])) ? mb_substr($individual[$tmp_screen][$tmp_question_ID], $partID, 1) : 'u';
                                    if ($part_ans != 'u') {
                                        $csvdata[$j][] = $part_ans;
                                    } else {
                                        $csvdata[$j][] = '';
                                    }
                                    if ($is_random) {
                                          $csvdata[$j][] = $correct_parts[$partID + 1];
                                    }
                                }
                            }
                            break;
                        case 'extmatch':
                            $correct_parts = explode(',', $question['correct']);
                            $answer_parts = (isset($individual[$tmp_screen][$tmp_question_ID])) ? explode('|', $individual[$tmp_screen][$tmp_question_ID]) : array_fill(0, count($correct_parts), 'u');

                            $partID = 0;
                            for ($outer = 1; $outer < count($correct_parts); $outer++) {
                                if ($correct_parts[$outer] != '' and mb_substr($tmp_exclude, $partID, 1) == '0') {
                                      $correct_subparts = explode('$', $correct_parts[$outer]);
                                      $correct_text_parts = explode("\t", $question['correct_text']);
                                    if (isset($answer_parts[$outer - 1])) {
                                        $answer_subparts = explode('$', $answer_parts[$outer - 1]);
                                        for ($k = 0; $k < count($correct_subparts); $k++) {
                                            $diff = count($correct_subparts) - count($answer_subparts);
                                            if ($diff > 0) {
                                                $answer_subparts = array_pad($answer_subparts, -1 * ($diff + count($answer_subparts)), '-1');
                                            }

                                            if (count($correct_subparts) > 1) {
                                                  $corr_index = array_search($correct_subparts[$k], $answer_subparts);
                                                if ($corr_index !== false and $corr_index > $k) {
                                                    $answer_subparts = self::array_swap($answer_subparts, $k, $corr_index);
                                                }
                                            }

                                            if ($answer_subparts[$k] != -1) {
                                                    $subpart = $answer_subparts[$k];
                                                if ($mode == 'numeric') {
                                                    $csvdata[$j][] = $answer_subparts[$k];
                                                } else {
                                                    if (isset($correct_text_parts[$subpart])) {
                                                                      $csvdata[$j][] = $correct_text_parts[$subpart];
                                                    } else {
                                                        $csvdata[$j][] = '';
                                                    }
                                                }
                                            }
                                            if ($is_random) {
                                                if ($mode == 'numeric') {
                                                    $csvdata[$j][] = $correct_subparts[$k];
                                                } else {
                                                    $csvdata[$j][] = $correct_text_parts[$correct_subparts[$k]];
                                                }
                                            }
                                        }
                                    } else {
                                        for ($k = 0; $k < count($correct_subparts); $k++) {
                                            if ($is_random) {
                                                if ($mode == 'numeric') {
                                                    $csvdata[$j][] = $correct_subparts[$k];
                                                } else {
                                                    $csvdata[$j][] = $correct_text_parts[$correct_subparts[$k]] . '"';
                                                }
                                            }
                                        }
                                    }
                                }
                                $partID += mb_substr_count($correct_parts[$outer], '$') + 1;
                            }
                            break;
                        case 'matrix':
                            $correct_parts = explode(',', $question['correct']);
                            $correct_text_parts = explode("\t", $question['correct_text']);
                            $answer_parts = (isset($individual[$tmp_screen][$tmp_question_ID])) ? explode('|', $individual[$tmp_screen][$tmp_question_ID]) : array_fill(0, count($correct_parts), 'u');

                            for ($partID = 0; $partID < count($correct_parts) - 1; $partID++) {
                                // $correct_parts[0] is always empty
                                if (mb_substr($tmp_exclude, $partID, 1) == '0' and $correct_parts[$partID + 1] != '') {
                                    if (isset($answer_parts[$partID]) and  $answer_parts[$partID] != '' and  $answer_parts[$partID] != 'u') {
                                        if ($mode == 'numeric') {
                                            $csvdata[$j][] = $answer_parts[$partID];
                                        } else {
                                            $csvdata[$j][] = $correct_text_parts[$answer_parts[$partID]];
                                        }
                                    } else {
                                        $csvdata[$j][] = '';
                                    }
                                    if ($is_random) {
                                        if ($mode == 'numeric') {
                                            $csvdata[$j][] =  $correct_parts[$partID + 1];
                                        } else {
                                            $csvdata[$j][] = $correct_text_parts[$correct_parts[$partID + 1]];
                                        }
                                    }
                                }
                            }
                            break;
                        case 'rank':
                            $individual[$tmp_screen][$tmp_question_ID] = (isset($individual[$tmp_screen][$tmp_question_ID])) ? str_replace('0', 'N/A', $individual[$tmp_screen][$tmp_question_ID]) : '';
                            if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                                $correct_parts = explode(',', $question['correct']);
                                $answer_parts = ($individual[$tmp_screen][$tmp_question_ID] != '') ? explode(',', $individual[$tmp_screen][$tmp_question_ID]) : array_fill(0, count($correct_parts), 'u');

                                for ($partID = 0; $partID < count($correct_parts) - 1; $partID++) {
                                    if ($answer_parts[$partID] != 'u') {
                                        $csvdata[$j][] =  ($mode == 'numeric') ? $answer_parts[$partID] : \StringUtils::ordinal_suffix($answer_parts[$partID], $language);
                                    } else {
                                        $csvdata[$j][] = '';
                                    }
                                    if ($is_random) {
                                          $csvdata[$j][] = ($mode == 'numeric') ? $correct_parts[$partID + 1] : \StringUtils::ordinal_suffix($correct_parts[$partID + 1], $language);
                                    }
                                }
                            }
                            break;
                        case 'hotspot':
                            $correct_parts = explode('|', $question['correct']);
                            $answer_parts = (isset($individual[$tmp_screen][$tmp_question_ID])) ? explode('|', $individual[$tmp_screen][$tmp_question_ID]) : array_fill(0, count($correct_parts), 'u');

                            for ($partID = 0; $partID < count($correct_parts); $partID++) {
                                if (mb_substr($tmp_exclude, $partID, 1) == '0') {
                                    if (isset($answer_parts[$partID]) and $answer_parts[$partID] != 'u') {
                                        $csvdata[$j][] =  str_replace(',', 'x', mb_substr($answer_parts[$partID], 2));
                                    } else {
                                        $csvdata[$j][] = '';
                                    }
                                }
                            }
                            break;
                        case 'labelling':
                            $tmp_first_split = (isset($individual[$tmp_screen][$tmp_question_ID])) ? explode(';', $individual[$tmp_screen][$tmp_question_ID]) : array('', '');
                            $tmp_answers = explode('$', $tmp_first_split[1]);
                            $user_answers = array();
                            for ($label_no = 0; $label_no <= count($tmp_answers) - 4; $label_no += 4) {
                                $user_answers[$tmp_answers[$label_no] . 'x' . $tmp_answers[$label_no + 1]] = $tmp_answers[$label_no + 2];
                            }

                            $sec = 1;
                            $cix = 0;
                            $tmp_first_split = explode(';', $question['correct']);
                            $tmp_second_split = explode('$', $tmp_first_split[11]);
                            $label_indexes = array();
                            $answers = array();
                            $correct = array();
                            for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
                                $tmp_third_split = explode('|', $tmp_second_split[$label_no]);
                                $lix = mb_strstr($tmp_third_split[0], '~', true);
                                if ($lix === false) {
                                    $lix = $tmp_third_split[0];
                                }
                                $label_indexes[$lix] = $tmp_third_split[1];
                                if (mb_substr($tmp_exclude, $sec - 1, 1) == '0') {
                                    if (mb_substr($tmp_second_split[$label_no], 0, 1) != '|' and $tmp_second_split[$label_no - 2] > 219) {
                                        $location = $tmp_second_split[$label_no - 2] . 'x' . ($tmp_second_split[$label_no - 1] - 25);
                                        $correct[$cix] = $tmp_third_split[0];
                                        $cix++;
                                        if (isset($user_answers[$location])) {
                                              $answers[] = $user_answers[$location];
                                        } else {
                                            $answers[] = '';
                                        }
                                    }
                                }
                                $sec++;
                            }
                            for ($labelcount = 0; $labelcount < count($answers); $labelcount++) {
                                $answer = $answers[$labelcount];
                                if ($answer != '') {
                                    if ($mode == 'numeric') {
                                        if (isset($label_indexes[$answer])) {
                                            $csvdata[$j][] = $label_indexes[$answer];
                                        } else {
                                            $csvdata[$j][] = '';
                                        }
                                        if ($is_random) {
                                            $csvdata[$j][] =  $label_indexes[$correct[$labelcount]];
                                        }
                                    } else {
                                        $csvdata[$j][] =  $answer;
                                        if ($is_random) {
                                            $csvdata[$j][] =  $correct[$labelcount];
                                        }
                                    }
                                } else {
                                    $csvdata[$j][] = '';
                                }
                            }
                            break;
                        case 'mrq':
                            if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                                $correct_clean = str_replace(',', '', $question['correct']);
                                $correct_text_parts = explode("\t", $question['correct_text']);
                                for ($char_pos = 0; $char_pos < mb_substr_count($question['correct'], ','); $char_pos++) {
                                      $part_ans = (isset($individual[$tmp_screen][$tmp_question_ID])) ? mb_substr($individual[$tmp_screen][$tmp_question_ID], $char_pos, 1) : '';
                                    if ($mode == 'numeric') {
                                        $csvdata[$j][] = $part_ans;
                                    } else {
                                        if ($part_ans == 'y') {
                                                      $csvdata[$j][] =  $correct_text_parts[$char_pos + 1];
                                        } else {
                                            $csvdata[$j][] = '';
                                        }
                                    }
                                    if ($is_random) {
                                        if ($mode == 'numeric') {
                                            $csvdata[$j][] = mb_substr($correct_clean, $char_pos, 1);
                                        } else {
                                            if (mb_substr($correct_clean, $char_pos, 1) == 'y') {
                                                        $csvdata[$j][] =  $correct_text_parts[$char_pos + 1];
                                            } else {
                                                $csvdata[$j][] = '';
                                            }
                                        }
                                    }
                                }
                                $char_pos = mb_substr_count($question['correct'], ',') + 1;
                                if ($question['score_method'] == 'other') {
                                    $part_ans = (isset($individual[$tmp_screen][$tmp_question_ID])) ? mb_substr($individual[$tmp_screen][$tmp_question_ID], $char_pos + 1) : '';
                                    $csvdata[$j][] =  $part_ans;
                                    if ($is_random) {
                                        $csvdata[$j][] = '';
                                    }
                                }
                            }
                            break;
                        case 'textbox':
                            if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                                if (isset($individual[$tmp_screen][$tmp_question_ID])) {
                                      $tmp_data = trim($individual[$tmp_screen][$tmp_question_ID]);
                                } else {
                                    $tmp_data = '<unanswered>';
                                }
                                $tmp_data = trim(preg_replace(array("/(\r\n|\n|\r)/", "/^-/"), array("", "\xE2\x80\x91"), $tmp_data));
                                $tmp_data = str_replace('"', "'", $tmp_data);

                                $csvdata[$j][] =  $tmp_data;
                            }
                            break;
                        case 'sct':
                            if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                                $correct_text_parts = explode("\t", $question['correct_text']);
                                if (isset($individual[$tmp_screen][$tmp_question_ID]) and $individual[$tmp_screen][$tmp_question_ID] != 'u') {
                                    if ($mode == 'numeric') {
                                        $csvdata[$j][] = $individual[$tmp_screen][$tmp_question_ID];
                                    } else {
                                        $csvdata[$j][] = $correct_text_parts[$individual[$tmp_screen][$tmp_question_ID]];
                                    }
                                }
                                if ($is_random) {
                                    $correct = '';
                                    $parts = explode(',', $question['correct']);
                                    $max_correct = 0;
                                    for ($partID = 1; $partID < count($parts); $partID++) {
                                        if ($parts[$partID] > $max_correct) {
                                            $max_correct = $parts[$partID];
                                            $correct = ($mode == 'numeric') ? $partID : $correct_text_parts[$partID];
                                        } elseif ($parts[$partID] == $max_correct and $max_correct > 0) {
                                            if ($mode == 'numeric') {
                                                        $correct .= ',' . $partID;
                                            } else {
                                                $correct .= ' OR ' . $correct_text_parts[$partID];
                                            }
                                        }
                                    }
                                    $csvdata[$j][] =  $correct;
                                }
                            }
                            break;
                        case 'random':
                            // This should only happen if the user answered a question that the user answered has been
                            // unlinked from the random question.
                            $csvdata[$j][] = $string['error_random'];
                            break;
                        default:
                            if (!$exclusions->is_question_excluded($tmp_question_ID)) {
                                $correct_text_parts = explode("\t", $question['correct_text']);
                                if (isset($individual[$tmp_screen][$tmp_question_ID]) and $individual[$tmp_screen][$tmp_question_ID] != 'u') {
                                    if ($mode == 'numeric') {
                                        $csvdata[$j][] = $individual[$tmp_screen][$tmp_question_ID];
                                    } else {
                                        if (isset($correct_text_parts[$individual[$tmp_screen][$tmp_question_ID]])) {
                                                      $csvdata[$j][] = $correct_text_parts[$individual[$tmp_screen][$tmp_question_ID]];
                                        } else {
                                            $csvdata[$j][] = '';
                                        }
                                    }
                                } else {
                                    $csvdata[$j][] = '';
                                }
                                if ($is_random) {
                                    if ($mode == 'numeric') {
                                        $csvdata[$j][] = ltrim($question['correct'], ',');
                                    } else {
                                        $csvdata[$j][] = $correct_text_parts[ltrim($question['correct'], ',')];
                                    }
                                }
                            }
                            break;
                    }
                }
            }
            $j++;
        }
        return $csvdata;
    }
}
