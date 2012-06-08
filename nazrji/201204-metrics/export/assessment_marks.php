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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/
  
require '../include/staff_auth.inc';
require '../include/class_totals.inc';
  
header('Content-type: application/octet-stream');
header("Content-Disposition: attachment; filename=new_" . str_replace(' ', '_', $paper) . "_marks.csv");

function get_correct_labels($question, $tmp_exclude) {
  $correct_labels = array();

  $tmp_first_split = explode(';', $question['correct'][0]);
  $tmp_second_split = explode('$', $tmp_first_split[11]);
  $i = 0;
  $excluded_no = 0;
  for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
    if (substr($tmp_second_split[$label_no], 0, 1) != '|' and $tmp_second_split[$label_no - 2] > 219) {
      if (substr($tmp_exclude,$i,1) == '0') {
        $x = $tmp_second_split[$label_no-2];
        $y = $tmp_second_split[$label_no-1] - 25;
        $correct_labels[$x . 'x' . $y] = substr($tmp_second_split[$label_no],0,strpos($tmp_second_split[$label_no],'|'));
      } else {
        $excluded_no++;
      }
      $i++;
    }
  }

  return $correct_labels;
}
$numerals = array('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii', 'xiii', 'xiv', 'xv', 'xvi', 'xvii', 'xviii', 'xix', 'xx');

$row_written = 0;
foreach ($user_results as $individual) {
  $tmp_user_ID = $individual['username'];
  // Write out the headings.
  if ($row_written == 0) {
    // Only output personal data if assessment, do not show if survey.
    if ($paper_type < 3) {
      echo '"' . $string['gender'] . '","' . $string['title'] . '","' . $string['surname'] . '","' . $string['firstnames'] . '","' . $string['studentid'] . '","' . $string['course'] . '","' . $string['year'] . '","' . $string['submitted'] . '"';
    } else {
      echo '"' . $string['gender'] . '","' . $string['course'] . '","' . $string['year'] . '","' . $string['submitted'] . '"';
    }
    $q_no = 1;
    foreach ($paper_buffer as $q_id => $question) {
      if (array_key_exists($q_id,$excluded)) {
        $tmp_exclude = $excluded[$q_id];
      } else {
        $tmp_exclude = '0000000000000000000000000000000000000000';
      }
      // If a random question, get the first on the associated questions from the block. If none exist, output nothing
      $skip_random = false;
      if ($question['q_type'] == 'random') {
        if (isset($paper_buffer[$q_id]['random_questions']) and count($paper_buffer[$q_id]['random_questions']) > 0) {
          $question = reset($paper_buffer[$q_id]['random_questions']);

          if ($question['q_type'] == 'blank') {
            $tmp_q_id = key($paper_buffer[$q_id]['random_questions']);
            $question['correct'] = extract_blank_correct($question['option_text'][0], $question['display_method'], $paper_buffer, $tmp_q_id);
          }
          if ($question['q_type'] == 'labelling') {
            $question['correct_labels'] = get_correct_labels($question, $tmp_exclude);
          }
        } else {
          $skip_random = true;
        }
      }
      if (!$skip_random) {
        if ($question['q_type'] == 'extmatch' and $question['score_method'] == 'Mark per Option') {
          $sub_parts = 0;
          $paper_answers = explode('|', $question['correct'][0]);
          for ($a=0; $a<count($paper_answers); $a++) {
            $sub_parts += substr_count($paper_answers[$a], '$');

            if ($paper_answers[$a] != '' and substr($tmp_exclude, $a+$sub_parts, 1) == '0') echo ',Q' . $q_no . $numerals[$a];
          }
        } elseif ($question['q_type'] == 'matrix' and $question['score_method'] == 'Mark per Option') {
          $sub_parts = 0;
          $paper_answers = explode('|', $question['correct'][0]);
          for ($a=0; $a<count($paper_answers); $a++) {
            $sub_parts += substr_count($paper_answers[$a], '$');

            if ($paper_answers[$a] != '' and substr($tmp_exclude, $a+$sub_parts, 1) == '0') echo ',Q' . $q_no . chr($a+65);
          }
        } elseif (($question['q_type'] == 'dichotomous' or $question['q_type'] == 'blank') and $question['score_method'] == 'Mark per Option') {
          for ($a=0; $a<count($question['correct']); $a++) {
            if (substr($tmp_exclude, $a, 1) == '0') echo ',Q' . $q_no . chr($a+65);
          }
        } elseif ($question['q_type'] == 'labelling' and $question['score_method'] == 'Mark per Option') {
          for ($a=0; $a<count($question['correct_labels']); $a++) {
            if (substr($tmp_exclude, $a, 1) == '0') echo ',Q' . $q_no . chr($a+65);
          }
        } elseif ($question['q_type'] == 'hotspot' and $question['score_method'] == 'Mark per Option') {
          $paper_answers = explode('|', $question['correct'][0]);
          for ($a=0; $a<count($paper_answers); $a++) {
            if (substr($tmp_exclude, $a, 1) == '0') echo ',Q' . $q_no . chr($a+65);
          }
        } else {
          if (!array_key_exists($q_id, $excluded)) echo ',Q' . $q_no;
        }
        $q_no++;
      }
    }
    echo "\n";
  }
  
  // Write out the raw data.
  if ($individual['visible'] == 1) {
    if ($paper_type < 3) {
      echo '"' . $individual['gender'] . '","' . $individual['title'] . '","' . $individual['surname'] . '","' . $individual['first_names'] . '","' . $individual['student_id'] . '","' .$individual['student_grade'] . '","' . $individual['year'] . '","' . $individual['display_started'] . '"';
    } else {
      echo '"' . $individual['gender'] . '","' . $individual['student_grade'] . '","' . $individual['year'] . '","' . $individual['display_started'] . '"';
    }
    foreach ($paper_buffer as $q_id => $question) {
      if (array_key_exists($q_id, $excluded)) {
        $tmp_exclude = $excluded[$q_id];
      } else {
        $tmp_exclude = '0000000000000000000000000000000000000000';
      }
      // If a random question, get the one that the user answered, otherwise just get the first and skip if none exist
      $skip_random = false;
      if ($question['q_type'] == 'random') {
        if (isset($paper_buffer[$q_id]['random_questions']) and count($paper_buffer[$q_id]['random_questions']) > 0) {
          $rnd_found = false;
          foreach ($paper_buffer[$q_id]['random_questions'] as $tmp_q_id => $tmp_q) {
            if (isset($individual['mark_array'][$tmp_q_id])) {
              $q_id = $tmp_q_id;
              $question = $tmp_q;
              $rnd_found = true;
              break;
            }
          }
          if (!$rnd_found) {
            $question = reset($paper_buffer[$q_id]['random_questions']);
            $q_id = key($paper_buffer[$q_id]['random_questions']);
          }
        } else {
          $skip_random = true;
        }
      }
      if (!$skip_random) {
        if (isset($individual['mark_array'][$q_id])) {
          $a = 0;
          if (is_array($individual['mark_array'][$q_id])) {
            foreach ($individual['mark_array'][$q_id] as $tmp_mark) {
              echo ',' . $tmp_mark;
              $a++;
            }
          } else {
            echo ',' . $individual['mark_array'][$q_id];
          }
        } else {
          if (($question['q_type'] == 'extmatch' or $question['q_type'] == 'matrix') and $question['score_method'] == 'Mark per Option') {
            $sub_parts = 0;
            $paper_answers = explode("|",$question['correct'][0]);
            for ($a=0; $a<count($paper_answers); $a++) {
              $sub_parts += substr_count($paper_answers[$a],'$');

              if ($paper_answers[$a] != '' and substr($tmp_exclude,$a+$sub_parts,1) == '0') echo ',0';
            }
          } elseif (($question['q_type'] == 'dichotomous' or $question['q_type'] == 'blank') and $question['score_method'] == 'Mark per Option') {
            for ($a=0; $a<count($question['correct']); $a++) {
              if (substr($tmp_exclude,$a,1) == '0') echo ',0';
            }
          } elseif ($question['q_type'] == 'labelling' and $question['score_method'] == 'Mark per Option') {
            for ($a=0; $a<count($question['correct_labels']); $a++) {
              if (substr($tmp_exclude,$a,1) == '0') echo ',0';
            }
          } elseif ($question['q_type'] == 'hotspot' and $question['score_method'] == 'Mark per Option') {
            $paper_answers = explode("|",$question['correct'][0]);
            for ($a=0; $a<count($paper_answers); $a++) {
              if (substr($tmp_exclude,$a,1) == '0') echo ',0';
            }
          } else {
            if (!array_key_exists($q_id,$excluded)) echo ',0';
          }
        }
      }
    }
    echo "\n";
  }
  $row_written++;
}
$mysqli->close();
?>