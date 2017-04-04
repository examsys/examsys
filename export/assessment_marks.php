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

$displayDebug = false; //disable debug output in this script as it effects the output

$paperID    = check_var('paperID', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($_GET['paperID'], $mysqli, $string);

$paper            = $propertyObj->get_paper_title();
$marking          = $propertyObj->get_marking();
$pass_mark        = $propertyObj->get_pass_mark();
$distinction_mark = $propertyObj->get_distinction_mark();
$paper_type       = $propertyObj->get_paper_type();

$percent      = (isset($_GET['percent'])) ? $_GET['percent'] : 100;
$ordering     = (isset($_GET['ordering'])) ? $_GET['ordering'] : 'asc';
$absent       = (isset($_GET['absent'])) ? $_GET['absent'] : 0;
$sortby       = (isset($_GET['sortby'])) ? $_GET['sortby'] : 'name';
$studentsonly = (isset($_GET['studentsonly'])) ? $_GET['studentsonly'] : 1;
$repcourse    = (isset($_GET['repcourse'])) ? $_GET['repcourse'] : '%';
$repmodule    = (isset($_GET['repmodule'])) ? $_GET['repmodule'] : '';

$report = new ClassTotals($studentsonly, $percent, $ordering, $absent, $sortby, $userObject, $propertyObj, $startdate, $enddate, $repcourse, $repmodule, $mysqli, $string);
$report->compile_report(false);

$user_results = $report->get_user_results();
$paper_buffer = $report->get_paper_buffer();
$exclusions   = $report->get_exclusions();

$user_no = count($user_results);
header('Pragma: public');
header('Content-type: application/octet-stream');
header("Content-Disposition: attachment; filename=new_" . str_replace(' ', '_', $paper) . "_EM.csv");

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

$csv = '';

$row_written = 0;
foreach ($user_results as $individual) {
  $tmp_user_ID = $individual['username'];
  // Write out the headings.
  if ($row_written == 0) {
    // Only output personal data if assessment, do not show if survey.
    if ($paper_type < 3) {
      $csv .= '"' . $string['gender'] . '","' . $string['title'] . '","' . $string['surname'] . '","' . $string['firstnames'] . '","' . $string['studentid'] . '","' . $string['course'] . '","' . $string['year'] . '","' . $string['submitted'] . '"';
    } else {
      $csv .= '"' . $string['gender'] . '","' . $string['course'] . '","' . $string['year'] . '","' . $string['submitted'] . '"';
    }
    $q_no = 1;
    foreach ($paper_buffer as $q_id => $question) {
      $tmp_exclude = $exclusions->get_exclusions_by_qid($q_id);
      
      // If a random question, get the first on the associated questions from the block. If none exist, output nothing
      $skip_random = false;
      if ($question['q_type'] == 'random') {
        if (isset($paper_buffer[$q_id]['random_questions']) and count($paper_buffer[$q_id]['random_questions']) > 0) {
          $question = reset($paper_buffer[$q_id]['random_questions']);

          //if ($question['q_type'] == 'blank') {
          //  $tmp_q_id = key($paper_buffer[$q_id]['random_questions']);
          //  $question['correct'] = extract_blank_correct($question['option_text'][0], $question['display_method'], $paper_buffer, $tmp_q_id);
          // }
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

            if ($paper_answers[$a] != '' and substr($tmp_exclude, $a+$sub_parts, 1) == '0') $csv .= ',Q' . $q_no . $numerals[$a];
          }
        } elseif ($question['q_type'] == 'matrix' and $question['score_method'] == 'Mark per Option') {
          $sub_parts = 0;
          $paper_answers = explode('|', $question['correct'][0]);
          for ($a=0; $a<count($paper_answers); $a++) {
            $sub_parts += substr_count($paper_answers[$a], '$');

            if ($paper_answers[$a] != '' and substr($tmp_exclude, $a+$sub_parts, 1) == '0') $csv .= ',Q' . $q_no . chr($a+65);
          }
        } elseif (($question['q_type'] == 'dichotomous' or $question['q_type'] == 'blank') and $question['score_method'] == 'Mark per Option') {
          for ($a=0; $a<count($question['correct']); $a++) {
            if ($tmp_exclude{$a} == '0') $csv .= ',Q' . $q_no . chr($a+65);
          }
        } elseif ($question['q_type'] == 'labelling' and $question['score_method'] == 'Mark per Option') {
          for ($a=0; $a <(count($question['correct_labels']) + substr_count($tmp_exclude, '1')); $a++) {
            if ($tmp_exclude{$a} == '0') $csv .= ',Q' . $q_no . chr($a+65);
          }
        } elseif ($question['q_type'] == 'hotspot' and $question['score_method'] == 'Mark per Option') {
          $paper_answers = explode('|', $question['correct'][0]);
          for ($a=0; $a<count($paper_answers); $a++) {
            if ($tmp_exclude{$a} == '0') $csv .= ',Q' . $q_no . chr($a+65);
          }
        } else {
          if ($tmp_exclude{0} == '0') $csv .= ',Q' . $q_no;
        }
        $q_no++;
      }
    }
    $csv .= "\n";
  }

  // Write out the raw data.
  if ($individual['visible'] == 1) {
    if ($paper_type < 3) {
      $csv .= '"' . $individual['gender'] . '","' . $individual['title'] . '","' . $individual['surname'] . '","' . $individual['first_names'] . '","' . $individual['student_id'] . '","' .$individual['student_grade'] . '","' . $individual['year'] . '","' . $individual['display_started'] . '"';
    } else {
      $csv .= '"' . $individual['gender'] . '","' . $individual['student_grade'] . '","' . $individual['year'] . '","' . $individual['display_started'] . '"';
    }
    foreach ($paper_buffer as $q_id => $question) {
        $tmp_exclude = $exclusions->get_exclusions_by_qid($q_id);

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
              $csv .= ',' . $tmp_mark;
              $a++;
            }
          } else {
            $csv .= ',' . $individual['mark_array'][$q_id];
          }
        } else {
          if (($question['q_type'] == 'extmatch' or $question['q_type'] == 'matrix') and $question['score_method'] == 'Mark per Option') {
            $sub_parts = 0;
            $paper_answers = explode("|",$question['correct'][0]);
            for ($a=0; $a<count($paper_answers); $a++) {
              $sub_parts += substr_count($paper_answers[$a],'$');

              if ($paper_answers[$a] != '' and substr($tmp_exclude,$a+$sub_parts,1) == '0') $csv .= ',0';
            }
          } elseif (($question['q_type'] == 'dichotomous' or $question['q_type'] == 'blank') and $question['score_method'] == 'Mark per Option') {
            for ($a=0; $a<count($question['correct']); $a++) {
              if ($tmp_exclude{$a} == '0') $csv .= ',0';
            }
          } elseif ($question['q_type'] == 'labelling' and $question['score_method'] == 'Mark per Option') {
            for ($a=0; $a < count($question['correct_labels']); $a++) {
              if ($tmp_exclude{$a} == '0') $csv .= ',0';
            }
          } elseif ($question['q_type'] == 'hotspot' and $question['score_method'] == 'Mark per Option') {
            $paper_answers = explode("|",$question['correct'][0]);
            for ($a=0; $a<count($paper_answers); $a++) {
              if ($tmp_exclude{$a} == '0') $csv .= ',0';
            }
          } else {
            //if (!array_key_exists($q_id,$excluded)) $csv .= ',0';
            if ($tmp_exclude{0} == '0') $csv .= ',0';
          }
        }
      }
    }
    $csv .= "\n";
  }
  $row_written++;
}

if ($row_written == 0) {
  $csv = $string['nodata'];
}

echo mb_convert_encoding($csv, "UTF-16LE", "UTF-8");

$mysqli->close();
?>