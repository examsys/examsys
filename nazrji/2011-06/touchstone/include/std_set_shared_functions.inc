<?php

function get_reviews($mysqli, $mode, $paperID, $total_marks, &$no_reviews = 0) {
  $reviews = array();
  
  if($mode == 'index') {
    $reviews_query = $mysqli->query("SELECT q_type, questionID, category, percentage, group_review, DATE_FORMAT(std_set,'%Y%m%d%H%i%s') AS std_set, DATE_FORMAT(std_set,'%d/%m/%y %H:%i') AS display_date, rating, standards_setting.setterID, method, title, initials, surname, paper_title FROM (standards_setting, properties, questions, users) LEFT JOIN ebel ON (standards_setting.setterID=ebel.setterID AND standards_setting.std_set=ebel.date_set) WHERE standards_setting.questionID=questions.q_id AND standards_setting.paperID=properties.property_id AND standards_setting.setterID=users.id AND paperID=$paperID ORDER BY standards_setting.std_set DESC, standards_setting.setterID, standards_setting.id");
  } else {
    $reviews_query = $mysqli->query("SELECT '' AS q_type, questionID, '' AS category, 0 AS percentage, group_review, DATE_FORMAT(std_set,'%Y%m%d%H%i%s') AS std_set, DATE_FORMAT(std_set,'%d/%m/%y %H:%i') AS display_date, rating, standards_setting.setterID, method, title, initials, surname, '' AS paper_title FROM standards_setting, users WHERE standards_setting.setterID=users.id AND paperID=$paperID ORDER BY standards_setting.std_set DESC, standards_setting.setterID, standards_setting.id");
  }
  
  if ($reviews_query->num_rows > 0) {
    // Get any questions to exclude.
    $exclude = array();
    $exclude_query = $mysqli->query("SELECT q_id, parts FROM question_exclude WHERE q_paper=$paperID");
    while ($row = $exclude_query->fetch_assoc()) {
      $exclude[$row['q_id']] = $row['parts'];
    }
    $exclude_query->close();
  
    // Calculate marks for the current paper.
    $marks_array = array();
    ss_get_marks($mysqli, $paperID, $exclude, $marks_array);
  
  
    $old_date = '';
    $old_title = '';
    $old_initials = '';
    $old_surname = '';
    $old_questionID = 0;
    $question_no = 0;
    $std_total = 0;
    $review_no = 0;
    $ebel_percents = array('EE'=>0,'EI'=>0,'EN'=>0,'ME'=>0,'MI'=>0,'MN'=>0,'HE'=>0,'HI'=>0,'HN'=>0);
    $ebel_marks = array('EE'=>0,'EI'=>0,'EN'=>0,'ME'=>0,'MI'=>0,'MN'=>0,'HE'=>0,'HI'=>0,'HN'=>0);
    $angoff_review_marks = 0;
  
    $reviews = array();
    
    while ($reviews_row = $reviews_query->fetch_assoc()) {
      $paper_title = $reviews_row['paper_title'];
      if ($old_date != $reviews_row['std_set'] and $old_date != '') {     // New review date
        $review_no++;
        if ($old_method == 'Modified Angoff') {
          // $question_no can be 0 in some cases if questions have been excluded
          $pass_score = ($question_no > 0) ? round($std_total/$question_no) : 0;
          $review_total = $angoff_review_marks;
          $distinction_score = 'n/a';
        } elseif ($old_method == 'Ebel') {
          $cut_marks = 0.0;
          $cut_marks += $ebel_marks['EE'] * $ebel_percents['EE'] * 100;
          $cut_marks += $ebel_marks['EI'] * $ebel_percents['EI'] * 100;
          $cut_marks += $ebel_marks['EN'] * $ebel_percents['EN'] * 100;
          $cut_marks += $ebel_marks['ME'] * $ebel_percents['ME'] * 100;
          $cut_marks += $ebel_marks['MI'] * $ebel_percents['MI'] * 100;
          $cut_marks += $ebel_marks['MN'] * $ebel_percents['MN'] * 100;
          $cut_marks += $ebel_marks['HE'] * $ebel_percents['HE'] * 100;
          $cut_marks += $ebel_marks['HI'] * $ebel_percents['HI'] * 100;
          $cut_marks += $ebel_marks['HN'] * $ebel_percents['HN'] * 100;
          $review_total = $ebel_marks['EE'] + $ebel_marks['EI'] + $ebel_marks['EN'] + $ebel_marks['ME'] + $ebel_marks['MI'] + $ebel_marks['MN'] + $ebel_marks['HE'] + $ebel_marks['HI'] + $ebel_marks['HN'];
           
          $pass_score = ($cut_marks / ($total_marks  * 100)) * 100;
          $pass_score = round($pass_score,1);
  
          $cut_marks2 = 0.0;
          $cut_marks2 += $ebel_marks['EE'] * $ebel_percents['EE2'] * 100;
          $cut_marks2 += $ebel_marks['EI'] * $ebel_percents['EI2'] * 100;
          $cut_marks2 += $ebel_marks['EN'] * $ebel_percents['EN2'] * 100;
          $cut_marks2 += $ebel_marks['ME'] * $ebel_percents['ME2'] * 100;
          $cut_marks2 += $ebel_marks['MI'] * $ebel_percents['MI2'] * 100;
          $cut_marks2 += $ebel_marks['MN'] * $ebel_percents['MN2'] * 100;
          $cut_marks2 += $ebel_marks['HE'] * $ebel_percents['HE2'] * 100;
          $cut_marks2 += $ebel_marks['HI'] * $ebel_percents['HI2'] * 100;
          $cut_marks2 += $ebel_marks['HN'] * $ebel_percents['HN2'] * 100;
          $distinction_score = (($cut_marks2 / ($total_marks * 100)) * 100);
          $distinction_score = round($distinction_score,1);
        }
        if($old_group_review == 'No') $no_reviews++;
        $reviews[] = array(
        	'group_review' => $old_group_review,
        	'review_total' => $review_total, 
        	'total_marks' => $total_marks, 
        	'setter_id' => $old_setterID,
        	'review_no' => $review_no,
        	'date' => $old_date,
          'display_date' => $old_display_date,
        	'method' => $old_method,
          'distinction_score' => $distinction_score,
          'pass_score' => $pass_score,
          'name' => "$old_title $old_initials $old_surname");
        $ebel_percents = array('EE'=>0,'EI'=>0,'EN'=>0,'ME'=>0,'MI'=>0,'MN'=>0,'HE'=>0,'HI'=>0,'HN'=>0);
        $ebel_marks = array('EE'=>0,'EI'=>0,'EN'=>0,'ME'=>0,'MI'=>0,'MN'=>0,'HE'=>0,'HI'=>0,'HN'=>0);
        $question_no = 0;
        $std_total = 0;
        $angoff_review_marks = 0;
      }
      $category = $reviews_row['category'];
      $ebel_percents[$category] = $reviews_row['percentage'];
      if ($old_questionID != $reviews_row['questionID']) {
        $questionID = $reviews_row['questionID'];
        if (array_key_exists($questionID,$exclude)) {
          $tmp_exclude = $exclude[$questionID];
        } else {
          $tmp_exclude = '0000000000000000000000000000000000000000';
        }
        $partID = 0;
  
        if ($reviews_row['rating'] != '') {
          $rating_array = explode(',',$reviews_row['rating']);
          foreach ($rating_array as $individual_rating) {
            if (isset($marks_array[$questionID][$partID]) and $individual_rating != '') {
              if (isset($ebel_marks[$individual_rating])) {
                $ebel_marks[$individual_rating] += $marks_array[$questionID][$partID];
              } else {
                $ebel_marks[$individual_rating] = $marks_array[$questionID][$partID];
              }
              $angoff_review_marks += $marks_array[$questionID][$partID];
            }
            $partID++;
          }
        }
      }
  
      if ($reviews_row['rating'] != '') {
        $q_sections = explode(',',$reviews_row['rating']);
        $tmp_part = 0;
        foreach ($q_sections as $part) {
          if (substr($tmp_exclude,$tmp_part,1) == '0') {
            if ($reviews_row['q_type'] == 'textbox') {
              $std_total += $part * (count($q_sections) - $tmp_part);
            } else {
              $std_total += $part;
            }
            $question_no++;
          }
          $tmp_part++;
        }
      }
      $old_date = $reviews_row['std_set'];
      $old_display_date = $reviews_row['display_date'];
      $old_method = $reviews_row['method'];
      $old_title = $reviews_row['title'];
      $old_initials = $reviews_row['initials'];
      $old_surname = $reviews_row['surname'];
      $old_setterID = $reviews_row['setterID'];
      $old_group_review = $reviews_row['group_review'];
      $old_questionID = $reviews_row['questionID'];
    }  // End while loop
  
      $review_no++;
      if ($old_method == 'Modified Angoff') {
        if ($question_no > 0) {
          $pass_score = round($std_total/$question_no);
        } else {
          $pass_score = 0;
        }
        $review_total = $angoff_review_marks;
        $distinction_score = 'n/a';
      } elseif ($old_method == 'Ebel') {
        $cut_marks = 0.0;
        $cut_marks += $ebel_marks['EE'] * $ebel_percents['EE'] * 100;
        $cut_marks += $ebel_marks['EI'] * $ebel_percents['EI'] * 100;
        $cut_marks += $ebel_marks['EN'] * $ebel_percents['EN'] * 100;
        $cut_marks += $ebel_marks['ME'] * $ebel_percents['ME'] * 100;
        $cut_marks += $ebel_marks['MI'] * $ebel_percents['MI'] * 100;
        $cut_marks += $ebel_marks['MN'] * $ebel_percents['MN'] * 100;
        $cut_marks += $ebel_marks['HE'] * $ebel_percents['HE'] * 100;
        $cut_marks += $ebel_marks['HI'] * $ebel_percents['HI'] * 100;
        $cut_marks += $ebel_marks['HN'] * $ebel_percents['HN'] * 100;
        $review_total = $ebel_marks['EE'] + $ebel_marks['EI'] + $ebel_marks['EN'] + $ebel_marks['ME'] + $ebel_marks['MI'] + $ebel_marks['MN'] + $ebel_marks['HE'] + $ebel_marks['HI'] + $ebel_marks['HN'];
        $pass_score = (($cut_marks / ($total_marks * 100)) * 100);
        $pass_score = round($pass_score,1);
  
      $cut_marks2 = 0.0;
      $cut_marks2 += $ebel_marks['EE'] * $ebel_percents['EE2'] * 100;
      $cut_marks2 += $ebel_marks['EI'] * $ebel_percents['EI2'] * 100;
      $cut_marks2 += $ebel_marks['EN'] * $ebel_percents['EN2'] * 100;
      $cut_marks2 += $ebel_marks['ME'] * $ebel_percents['ME2'] * 100;
      $cut_marks2 += $ebel_marks['MI'] * $ebel_percents['MI2'] * 100;
      $cut_marks2 += $ebel_marks['MN'] * $ebel_percents['MN2'] * 100;
      $cut_marks2 += $ebel_marks['HE'] * $ebel_percents['HE2'] * 100;
      $cut_marks2 += $ebel_marks['HI'] * $ebel_percents['HI2'] * 100;
      $cut_marks2 += $ebel_marks['HN'] * $ebel_percents['HN2'] * 100;
      $distinction_score = (($cut_marks2 / ($total_marks * 100)) * 100);
      $distinction_score = round($distinction_score,1);
    }
    if($old_group_review == 'No') $no_reviews++;
    $reviews[] = array(
    	'group_review' => $old_group_review,
    	'review_total' => $review_total, 
    	'total_marks' => $total_marks, 
    	'setter_id' => $old_setterID,
    	'review_no' => $review_no,
    	'date' => $old_date,
      'display_date' => $old_display_date,
    	'method' => $old_method,
      'distinction_score' => $distinction_score,
      'pass_score' => $pass_score,
      'name' => "$old_title $old_initials $old_surname");
  }

  $reviews_query->close();

  return $reviews;
}


function ss_get_marks($mysqli, $paper_id, $exclude, &$marks_array) {
  $partID = 0;
  $old_q_id = 0;
  $stems = 0;
  $correct_no = 0;
  $old_score_method = '';
  $question_data = $mysqli->query("SELECT q_type, q_id, marks, correct, score_method, option_text FROM (papers, questions, options) WHERE papers.paper=".$paper_id." AND papers.question=questions.q_id AND questions.q_id=options.o_id AND q_type != 'info' ORDER BY q_id, o_id, id_num");
  while ($row = $question_data->fetch_assoc()) {
    if ($old_q_id != $row['q_id'] and $old_q_id != 0) {
      if ($old_q_type == 'rank' and $old_score_method == 'BonusMark') {
        if (isset($exclude[$old_q_id]) and strpos($exclude[$old_q_id], '1') !== false) {
          $marks_array[$old_q_id][$partID] = 0;
        } else {
          $marks_array[$old_q_id][$partID] = 1;
        }
      }
      $stems = 0;
      $correct_no = 0;
      $temp_marks = 0;
      $partID = 0;
    }

    $old_q_id = $row['q_id'];
    if (!isset($exclude[$old_q_id])) { // If no record exists set to zero (i.e. included)
      $exclude[$old_q_id] = '00000000000000000000000000000';
    }

    switch ($row['q_type']) {
      case 'textbox':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          $marks_array[$old_q_id][$partID] = $row['marks'];
        }
        break;
      case 'dichotomous':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          $marks_array[$old_q_id][$partID] = 1;
        } else {
          $marks_array[$old_q_id][$partID] = 0;
        }
        $partID++;
        break;
      case 'calculation':
      case 'timedate':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          $marks_array[$old_q_id][$partID] = 1;
        }
        break;
      case 'hotspot':
        $tmp_first_split = explode('|', $row['correct']);
        for ($i = 0; $i < count($tmp_first_split); $i++) {
          if (substr($exclude[$old_q_id], $i, 1) == '0') {
            $marks_array[$old_q_id][$i] = 1;
          } else {
            $marks_array[$old_q_id][$i] = 0;
          }
        }
        break;
      case 'blank':
        $no_blanks = substr_count($row['option_text'], '[blank');
        for ($i = 0; $i < $no_blanks; $i++) {
          if (substr($exclude[$old_q_id], $i, 1) == '0') {
            $marks_array[$old_q_id][$i] = 1;
          }
        }
        break;
      case 'matrix':
        $tmp_part = 0;
        $matching_correct = explode('|', $row['correct']);
        for ($part_id = 0; $part_id < count($matching_correct); $part_id++) {
          if (trim($matching_correct[$part_id]) != '') {
            if (substr($exclude[$old_q_id], $part_id, 1) == '0') {
              $marks_array[$old_q_id][$tmp_part] = 1;
            }
            $tmp_part++;
          }
        }
        break;
      case 'extmatch':
        $tmp_part = 0;
        $matching_correct = explode('|', $row['correct']);
        for ($part_id = 0; $part_id < count($matching_correct); $part_id++) {
          $sub_array = explode('$', $matching_correct[$part_id]);
          for ($sub_part_id = 0; $sub_part_id < count($sub_array); $sub_part_id++) {
            if (substr($exclude[$old_q_id], $tmp_part, 1) == '0') {
              $marks_array[$old_q_id][$tmp_part] = 1;
            } else {
              $marks_array[$old_q_id][$tmp_part] = 0;
            }
            $tmp_part++;
          }
        }
        break;
      case 'mcq':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          $marks_array[$old_q_id][$partID] = $row['marks'];
          $partID++;
        }
        break;
      case 'mrq':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          if ($row['score_method'] == 'SelectedPositive') {
            if ($row['correct'] == 'y') {
              $marks_array[$old_q_id][$partID] = 1;
            }
            $partID++;
          } elseif ($row['score_method'] == 'AllItemsCorrect') {
            $marks_array[$old_q_id][0] = 1;
          } else {
            $marks_array[$old_q_id][$partID] = 1;
            $partID++;
          }
        }
        break;
      case 'rank':
        if ($row['correct'] < 9990) {
          if (substr($exclude[$old_q_id], $partID, 1) == '0') {
            $marks_array[$old_q_id][$partID] = 1;
          } else {
            $marks_array[$old_q_id][$partID] = 0;
          }
          $partID++;
        }
        break;
      case 'labelling':
        $tmp_first_split = explode(';', $row['correct']);
        $tmp_second_split = explode('$', $tmp_first_split[8]);
        for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
          if (substr($tmp_second_split[$label_no], 0, 1) != '|' and $tmp_second_split[$label_no - 2] > 219) {
            if (substr($exclude[$old_q_id], $partID, 1) == '0') {
              $marks_array[$old_q_id][$partID] = 1;
            } else {
              $marks_array[$old_q_id][$partID] = 0;
            }
            $partID++;
          }
        }
        break;
    }

    $old_q_type = $row['q_type'];
    $old_score_method = $row['score_method'];
    $old_correct = $row['correct'];
    $old_option_text = $row['option_text'];
    $temp_marks = $row['marks'];
    if ($row['q_type'] == 'mrq') {
      if ($row['correct'] == 'y') $correct_no++;
    }
    if ($row['q_type'] == 'rank') {
      if ($row['correct'] < 9990) $correct_no++;
    }
    $stems++;
  }

  $question_data->close();

  if ($old_q_type == 'rank' and $old_score_method == 'BonusMark') {
    if (isset($exclude[$old_q_id]) and strpos($exclude[$old_q_id], '1') !== false) {
      $marks_array[$old_q_id][$partID] = 0;
    } else {
      $marks_array[$old_q_id][$partID] = 1;
    }
  }
}
?>