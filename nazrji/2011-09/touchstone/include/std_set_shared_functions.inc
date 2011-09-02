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
    ss_get_marks_correct($mysqli, $paperID, $exclude, $marks_array);
  
  
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
           
          $pass_score = ($total_marks == 0) ? $total_marks : ($cut_marks / ($total_marks  * 100)) * 100;
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
          $distinction_score = ($total_marks == 0) ? $total_marks : (($cut_marks2 / ($total_marks * 100)) * 100);
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
            if($reviews_row['q_type'] != 'mrq' or $part != '') $question_no++;
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
        $pass_score = ($total_marks == 0) ? $total_marks : (($cut_marks / ($total_marks * 100)) * 100);
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
      $distinction_score = ($total_marks == 0) ? $total_marks : (($cut_marks2 / ($total_marks * 100)) * 100);
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


function ss_get_marks_correct($mysqli, $paper_id, $exclude, &$marks_array) {
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

function display_option($firstoption, $q_id, $question_no, &$part_id, $display_option, $std_method, $is_group, &$marks, $exclude, $review_val, &$matching_options, $review_mean, $tmp_reviews, $option_no, &$stdID, &$std_excluded, $total_rank_no, $require_na, $bgcolor, $feedback) {
  $qn_excluded = ($exclude == '1');
  
  switch ($firstoption['q_type']) {
    case 'textbox':
      $textbox_size = explode('x',$display_option['score_method']);
      echo "<blockquote><textarea style=\"font-family:Arial,sans-serif\" cols=\"" . $textbox_size[0] . "\" rows=\"" . $textbox_size[1] . "\"></textarea></blockquote>\n";
      echo "<pre style=\"width:90%; font-family:Arial,sans-serif; font-style:italic; color:#C00000\">" . wordwrap($feedback, 150) . "</pre>";
      $marks = $display_option['marks'];
      
      $plural = 's';
      if ($std_method == 'modified_angoff') {
        for ($mark_part=$marks; $mark_part>0; $mark_part--) {
          $review_val = get_review('textbox', $std_method, $q_id, $mark_part, $tmp_reviews, $is_group);
          echo reviewScale($question_no, $mark_part, $display_option['marks'], $std_method, $qn_excluded, $review_val, $stdID, $std_excluded, $is_group);
          if($mark_part == 1) $plural = '';
          echo "<span class=\"part-mark\">$mark_part mark$plural</span><br />\n";
        }
      } else {
        echo "<div style=\"color:#C00000\"><strong>Note:</strong> Ebel method cannot be used to standard set textbox questions.</div>\n";
      }
      break;
    case 'dichotomous':
      $tmp_user_answer = $display_option['correct'];
      if (($firstoption['score_method'] == 'TF_NegativeAbstain') or ($firstoption['score_method'] == 'TF_PostiveAbstain') or ($firstoption['score_method'] == "YN_NegativeAbstain") or ($firstoption['score_method'] == "YN_PostiveAbstain")) $abstain = true;

      echo '<tr>';
      echo '<td align="center">';
      if ($tmp_user_answer == 't') {
        echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" checked=\"checked\" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" /></td>';
      } elseif ($tmp_user_answer == 'f') {
        echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" checked=\"checked\" /></td>';
      } elseif ($tmp_user_answer == 'a') {
        echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" /></td>';
      } else {
        echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" /></td>';
      }
      if ($exclude == '1') {
        echo '<td style="color:red; text-decoration:line-through">';
      } else {
        echo '<td>';
      }
      if ($display_option['option_text'] != '') echo $display_option['option_text'];
      if ($display_option['o_media'] != '') {
        if ($display_option['option_text'] != '') echo '<br />';
        echo display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']);
      }
      echo "</td>";
      echo '<td>';
      echo reviewScale($question_no, $part_id, $display_option['marks'], $std_method, ($exclude == '1'), $review_val, $stdID, $std_excluded, $is_group);
      echo '</td>';
      echo "</tr>\n";
      $marks++;
      break;
    case 'calculation':
      $figures = explode(',',$display_option['option_text']);
      switch ($part_id) {
        case 1:
          $A = '<strong>$A</strong> <span style="color:#C00000; font-weight:bold">(' . $figures[0] . ' to ' . $figures[1] . ')</span>';
          break;
        case 2:
          $B = '<strong>$B</strong> <span style="color:#C00000; font-weight:bold">(' . $figures[0] . ' to ' . $figures[1] . ')</span>';
          break;
        case 3:
          $C = '<strong>$C</strong> <span style="color:#C00000; font-weight:bold">(' . $figures[0] . ' to ' . $figures[1] . ')</span>';
          break;
        case 4:
          $D = '<strong>$D</strong> <span style="color:#C00000; font-weight:bold">(' . $figures[0] . ' to ' . $figures[1] . ')</span>';
          break;
        case 5:
          $E = '<strong>$E</strong> <span style="color:#C00000; font-weight:bold">(' . $figures[0] . ' to ' . $figures[1] . ')</span>';
          break;
        case 6:
          $F = '<strong>$F</strong> <span style="color:#C00000; font-weight:bold">(' . $figures[0] . ' to ' . $figures[1] . ')</span>';
          break;
        case 7:
          $G = '<strong>$G</strong> <span style="color:#C00000; font-weight:bold">(' . $figures[0] . ' to ' . $figures[1] . ')</span>';
          break;
        case 8:
          $H = '<strong>$H</strong> <span style="color:#C00000; font-weight:bold">(' . $figures[0] . ' to ' . $figures[1] . ')</span>';
          break;
        case 9:
          $I = '<strong>$I</strong> <span style="color:#C00000; font-weight:bold">(' . $figures[0] . ' to ' . $figures[1] . ')</span>';
          break;
        case 10:
          $J = '<strong>$J</strong> <span style="color:#C00000; font-weight:bold">(' . $figures[0] . ' to ' . $figures[1] . ')</span>';
          break;
        }
      break;
    case 'mcq':
      if ($firstoption['display_method'] == 'vertical' or $firstoption['display_method'] == 'vertical_other') {
        if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No media
          if ($part_id == $display_option['correct']) {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" checked=\"checked\" /></td><td";
            if ($qn_excluded) echo ' style="color:red; text-decoration:line-through"';
            echo ">" . $display_option['option_text'] . "</td></tr>\n";
          } else {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td";
            if ($qn_excluded) echo ' style="color:red; text-decoration:line-through"';
            echo ">" . $display_option['option_text'] . "</td></tr>\n";
          }
        } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and media
          if ($part_id == $display_option['correct']) {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" checked=\"checked\" /></td><td";
            if ($qn_excluded) echo ' style="color:red; text-decoration:line-through"';
            echo ">" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "</td></tr>\n";
          } else {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td";
            if ($qn_excluded) echo ' style="color:red; text-decoration:line-through"';
            echo ">" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "</td></tr>\n";
          }
        } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
          if ($part_id == $display_option['correct']) {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" checked=\"checked\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "</td></tr>\n";
          } else {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "</td></tr>\n";
          }
        }
      } elseif ($firstoption['display_method'] == 'horizontal') {
        if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No media
          if ($part_id == $display_option['correct']) {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" checked=\"checked\" />&nbsp;<span";
            if ($qn_excluded) echo ' style="color:red; text-decoration:line-through"';
            echo ">" . $display_option['option_text'] ."</span>&nbsp;&nbsp;";
          } else {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" />&nbsp;<span";
            if ($qn_excluded) echo ' style="color:red; text-decoration:line-through"';
            echo ">" . $display_option['option_text'] . "&nbsp;&nbsp;";
          }
        } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and media
          if ($part_id == $display_option['correct']) {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" checked=\"checked\" />&nbsp;" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "&nbsp;&nbsp;";
          } else {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" />&nbsp;" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "&nbsp;&nbsp;";
          }
        } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
          if ($part_id == $display_option['correct']) {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" checked=\"checked\" />&nbsp;" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "&nbsp;&nbsp;";
          } else {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" />&nbsp;" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "&nbsp;&nbsp;";
          }
        }
      } else {
        if ($part_id == $display_option['correct']) {
          echo "<option value=\"" . $part_id . "\" selected>" . $display_option['option_text'] . "</option>\n";
        } else {
          echo "<option value=\"" . $part_id . "\">" . $display_option['option_text'] . "</option>\n";
        }
      }
      if ($part_id == $display_option['correct']) {
        $marks = $display_option['marks'];
      }
      break;
    case 'mrq':
      $excl_string = '';
      if (substr($exclude,0,1) == '1') $excl_string = ' style="color:red; text-decoration:line-through"';
      if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No image
        if ($display_option['correct'] == 'y') {
          if ($display_option['score_method'] == 'Mark per Question') {
            echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked=\"checked\" /></td><td$excl_string>" . $display_option['option_text'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
          } else {
            echo "<tr>";
            echo "<td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked=\"checked\" /></td><td$excl_string>" . $display_option['option_text'] . "</td><td>&nbsp;</td><td>\n";
            echo reviewScale($question_no, $part_id, $display_option['marks'], $std_method, $qn_excluded, $review_val, $stdID, $std_excluded, $is_group);
            echo "</td></tr>\n";
          }
        } else {
          echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td><td$excl_string><span id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span></td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        }
      } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and image
        if ($display_option['correct'] == 'y') {
          if ($display_option['score_method'] == 'Mark per Question') {
            echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked=\"checked\" /><span class=\"active\" id=\"$question_no" . "_" . "$part_id\"$excl_string>" . $display_option['option_text'] . "</span><br /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" border=\"0\"/></td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
          } else {
            echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked=\"checked\" /><span class=\"active\" id=\"$question_no" . "_" . "$part_id\"$excl_string>" . $display_option['option_text'] . "</span><br /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" border=\"0\"/>";
            echo "</td><td>&nbsp;</td><td>\n";
            echo reviewScale($question_no, $part_id, $display_option['marks'], $std_method, $qn_excluded, $review_val, $stdID, $std_excluded, $is_group);
            echo "</td></tr>\n";
          }
        } else {
          echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /><span class=\"active\" id=\"$question_no" . "_" . "$part_id\"$excl_string>" . $display_option['option_text'] . "</span><br /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" border=\"0\"/></td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        }
      } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and image
        if ($display_option['correct'] == 'y' and $display_option['score_method'] != 'Mark per Question') {
          echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked=\"checked\" /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" border=\"0\"/></td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        } else {
          echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" border=\"0\"/>";
          echo "</td><td>&nbsp;</td><td>\n";
          echo reviewScale($question_no, $part_id, $display_option['marks'], $std_method, $qn_excluded, $review_val, $stdID, $std_excluded, $is_group);
          echo "</td></tr>\n";
        }
      }
      if ($display_option['score_method'] == 'Mark per Option') {
        if ($display_option['correct'] == 'y') {
          $marks += $display_option['marks'];
        }
      }
      //if ($display_option['score_method'] == 'SelectedPositive') {
      //  if ($display_option['correct'] == 'y') $marks++;  // 1 mark for correct options only
      //} elseif ($display_option['score_method'] == 'AllItemsCorrect') {
      //  if ($part_id == 1) $marks++;
      //} else {
      //  $marks++;  // 1 mark for each and every item
      //}
      break;
    case 'extmatch':
    case 'matrix':
      $matching_options[] = $display_option['option_text'];
      break;
    case 'rank':
      if ($part_id == 1 and $display_option['score_method'] == 'Bonus Mark') {
        $marks++;  // Add one mark at start in case all options are answered correctly
      }

      echo "<tr><td class=\"option\"><select name=\"q" . $question_no . "_" . $part_id . "\">\n";
      echo "<option value=\"u\"></option>\n";
      if ($require_na) {
        echo "<option value=\"0\">N/A</option>\n";
      }
      for ($i=1; $i<=$total_rank_no; $i++) {
        if ($i == $display_option['correct']) {
          echo "<option value=\"" . $i . "\" selected>" . $i;
        } else {
          echo "<option value=\"" . $i . "\">" . $i;
        }
        if ($i == 1) {
          echo 'st';
        } elseif ($i == 2) {
          echo 'nd';
        } elseif ($i == 3) {
          echo 'rd';
        } else {
          echo 'th';
        }
        echo '</option>';
      }
      echo '</select></td>';
      echo "<td";
      if ($qn_excluded) echo ' style="color:red; text-decoration:line-through"';
      echo ">" . $display_option['option_text'] . "</td><td>&nbsp;</td>\n";
      
      if ($display_option['correct'] != 0 or $display_option['score_method'] == 'StrictOrder') {
        echo "<td>";
        echo reviewScale($question_no, $part_id, $display_option['marks'], $std_method, $qn_excluded, $review_val, $stdID, $std_excluded, $is_group);
        echo "</td>\n";
      } else {
        echo "<td></td>\n";
      }
      echo "</tr>\n";
      if ($display_option['score_method'] == 'AllItemsCorrect') {
        if ($part_id == 1) {
          $marks++;
        }
      } elseif ($display_option['correct'] != 0) {
        $marks++;
      } elseif ($display_option['score_method'] == 'StrictOrder') {
        $marks++;
      }
      break;
    case 'timedate':
      if($qn_excluded) {
        $bgcolor = '#FFC0C0';
        $fgcolor = '#800000';
        $extra = 'exclude_';
        } else {
        $bgcolor = '#D6DFF7';
        $fgcolor = 'black';
      }
              
      $date_details = explode('|',$display_option['score_method']);
      $answer_array = preg_split('/[\/\s:]+/',$display_option['correct']);
      if ($date_details[0] == 1 or $date_details[0] == 2 or $date_details[0] == 3 or $date_details[0] == 5) {
        $answer_day = $answer_array[0];
        $answer_month = $answer_array[1];
        $answer_year = $answer_array[2];
      } elseif ($date_details[0] == 4) {
        $answer_month = $answer_array[0];
        $answer_day = $answer_array[1];
        $answer_year = $answer_array[2];
      }
      if ($date_details[0] == 1 or $date_details[0] == 2) {
        $answer_hour = $answer_array[3];
        $answer_minutes = $answer_array[4];
              if ($date_details[0] == 1) $answer_seconds = trim($answer_array[5]);
      }
      if ($date_details[0] == 6 or $date_details[0] == 7 or $date_details[0] == 8) {
        $answer_hour = trim($answer_array[0]);
        $answer_minutes = $answer_array[1];
        if ($date_details[0] == 6) $answer_seconds = trim($answer_array[2]);
      }
      echo '<p>';
      if ($date_details[0] == 4) {
        $answer_day = $answer_array[1];
        $answer_month = $answer_array[0];
        echo '<select name="q' . $question_no . '_month" style="background-color:' . $bgcolor . '; color:' . $fgcolor . '">';
        echo "\n<option value=\"\"></option>\n";
        for ($tmp_month=1; $tmp_month<=12; $tmp_month++) {
          if ($tmp_month < 10) {
            if (intval($answer_month) == $tmp_month) {
              echo "<option value=\"0$tmp_month\" selected=\"selected\">0$tmp_month</option>\n";
            } else {
              echo "<option value=\"0$tmp_month\">0$tmp_month</option>\n";
            }
          } else {
            if (intval($answer_month) == $tmp_month) {
              echo "<option value=\"$tmp_month\" selected=\"selected\">$tmp_month</option>\n";
            } else {
              echo "<option value=\"$tmp_month\">$tmp_month</option>\n";
            }
          }
        }
        echo '</select> / ';
      }
      if ($date_details[0] == 1 or $date_details[0] == 2 or $date_details[0] == 3 or $date_details[0] == 4 or $date_details[0] == 5) {
        echo '<select name="q' . $question_no . '_day" style="background-color:' . $bgcolor . '; color:' . $fgcolor . '">';
        echo "\n<option value=\"\"></option>\n";
        for ($tmp_day=1; $tmp_day<=31; $tmp_day++) {
          if ($tmp_day < 10) {
            if (intval($answer_day) == $tmp_day) {
              echo "<option value=\"0$tmp_day\" selected=\"selected\">0$tmp_day</option>\n";
            } else {
              echo "<option value=\"0$tmp_day\">0$tmp_day</option>\n";
            }
          } else {
            if (intval($answer_day) == $tmp_day) {
              echo "<option value=\"$tmp_day\" selected=\"selected\">$tmp_day</option>\n";
            } else {
              echo "<option value=\"$tmp_day\">$tmp_day</option>\n";
            }
          }
        }
        echo '</select>';
        if ($date_details[0] != 4 and $date_details[0] != 5) {
          echo ' / <select name="q' . $question_no . '_month" style="background-color:' . $bgcolor . '; color:' . $fgcolor . '">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_month=1; $tmp_month<=12; $tmp_month++) {
            if ($tmp_month < 10) {
              if (intval($answer_month) == $tmp_month) {
                echo "<option value=\"0$tmp_month\" selected=\"selected\">0$tmp_month</option>\n";
              } else {
                echo "<option value=\"0$tmp_month\">0$tmp_month</option>\n";
              }
            } else {
              if (intval($answer_month) == $tmp_month) {
                echo "<option value=\"$tmp_month\" selected=\"selected\">$tmp_month</option>\n";
              } else {
                echo "<option value=\"$tmp_month\">$tmp_month</option>\n";
              }
            }
          }
          echo '</select>';
        } elseif ($date_details[0] == 5) {
          $month_array = array('January','February','March','April','May','June','July','August','September','October','November','December');
          $tmp_month = 1;
          echo ' / <select name="q' . $question_no . '_month" style="background-color:' . $bgcolor . '; color:' . $fgcolor . '">';
          echo "\n<option value=\"\"></option>\n";
          foreach ($month_array as $individual_month) {
            if ($tmp_month < 10) {
              if (intval($answer_month) == $tmp_month) {
                echo "<option value=\"0$tmp_month\" selected=\"selected\">$individual_month</option>\n";
              } else {
                echo "<option value=\"0$tmp_month\">$individual_month</option>\n";
              }
            } else {
              if (intval($answer_month) == $tmp_month) {
                echo "<option value=\"$tmp_month\" selected=\"selected\">$individual_month</option>\n";
              } else {
                echo "<option value=\"$tmp_month\">$individual_month</option>\n";
              }
            }
            $tmp_month++;
          }
          echo '</select>';
        }
        echo ' / <select name="q' . $question_no . '_year" style="background-color:' . $bgcolor . '; color:' . $fgcolor . '">';
        echo "\n<option value=\"\"></option>\n";
        for ($tmp_year=$date_details[1]; $tmp_year<=$date_details[2]; $tmp_year++) {
          if (intval($answer_year) == $tmp_year) {
            echo "<option value=\"$tmp_year\" selected=\"selected\">$tmp_year</option>\n";
          } else {
            echo "<option value=\"$tmp_year\">$tmp_year</option>\n";
          }
        }
        echo "</select>\n";
      }
      if ($date_details[0] == 1 or $date_details[0] == 2 or $date_details[0] == 6 or $date_details[0] == 7) {
        if ($date_details[0] < 6) echo '&nbsp;&nbsp;&nbsp;';
        echo '<select name="q' . $question_no . '_hour" style="background-color:' . $bgcolor . '; color:' . $fgcolor . '">';
        echo "\n<option value=\"\"></option>\n";
        for ($tmp_hour=0; $tmp_hour<=23; $tmp_hour++) {
          if ($tmp_hour < 10) {
            if (intval($answer_hour) == $tmp_hour) {
              echo "<option value=\"0$tmp_hour\" selected=\"selected\">0$tmp_hour</option>\n";
            } else {
              echo "<option value=\"0$tmp_hour\">0$tmp_hour</option>\n";
            }
          } else {
            if (intval($answer_hour) == $tmp_hour) {
              echo "<option value=\"$tmp_hour\" selected=\"selected\">$tmp_hour</option>\n";
            } else {
              echo "<option value=\"$tmp_hour\">$tmp_hour</option>\n";
            }
          }
        }
        echo "</select>\n";
        echo ' : <select name="q' . $question_no . '_minute" style="background-color:' . $bgcolor . '; color:' . $fgcolor . '">';
        echo "\n<option value=\"\"></option>\n";
        for ($tmp_min=0; $tmp_min<=60; $tmp_min++) {
          if ($tmp_min < 10) {
            if (intval($answer_minutes) == $tmp_min) {
              echo "<option value=\"0$tmp_min\" selected=\"selected\">0$tmp_min</option>\n";
            } else {
              echo "<option value=\"0$tmp_min\">0$tmp_min</option>\n";
            }
          } else {
            if (intval($answer_minutes) == $tmp_min) {
              echo "<option value=\"$tmp_min\" selected=\"selected\">$tmp_min</option>\n";
            } else {
              echo "<option value=\"$tmp_min\">$tmp_min</option>\n";
            }
          }
        }
        echo "</select>\n";
        if ($date_details[0] == 1 or $date_details[0] == 6) {
          echo ' : <select name="q' . $question_no . '_second" style="background-color:' . $bgcolor . '; color:' . $fgcolor . '">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_sec=0; $tmp_sec<=60; $tmp_sec++) {
            if ($tmp_sec < 10) {
              if (intval($answer_seconds) == $tmp_sec) {
                echo "<option value=\"0$tmp_sec\" selected=\"selected\">0$tmp_sec</option>\n";
              } else {
                echo "<option value=\"0$tmp_sec\">0$tmp_sec</option>\n";
              }
            } else {
              if (intval($answer_seconds) == $tmp_sec) {
                echo "<option value=\"$tmp_sec\" selected=\"selected\">$tmp_sec</option>\n";
              } else {
                echo "<option value=\"$tmp_sec\">$tmp_sec</option>\n";
              }
            }
          }
          echo "</select>\n";
        }
      }
      echo "</p>\n";

      echo "<div>";
      echo reviewScale($question_no, '', $display_option['marks'], $std_method, $qn_excluded, $review_val, $stdID, $std_excluded, $is_group);
      echo "</div>\n";
      $marks++;
      break;
    case 'blank':
      if ($display_option['display_method'] == 'textboxes') {
        $blank_details = explode('[blank]',$display_option['option_text']);
        $array_size = count($blank_details);
        $blank_count = 1;
        while ($blank_count < $array_size) {
          $blank_parts = explode("[/blank]", $blank_details[$blank_count]);
          $results = array();
          $not_used = preg_match("|size=\"([0-9]{1,3})\"|", $blank_details[$blank_count], $results);

          if (isset($results[1]) and $results[1] != '') {
            $blank_size[$blank_count] = $results[1];
          } else {
            $blank_size[$blank_count] = 15;
          }
          $results=array();
          $not_used = preg_match("|mark=\"([0-9]{1,3})\"|",$blank_details[$blank_count],$results);
          if (isset($results[1])) {
            $blank_mark[$blank_count] = $results[1];
          } else {
            $blank_mark[$blank_count] = 1;
          }
          $review_val = get_review('blank', $std_method, $q_id, $blank_count, $tmp_reviews, $is_group);
          $reviewScale .= reviewScale($question_no, $blank_count, $display_option['marks'], $std_method, substr($exclude,$blank_count-1,1), $review_val, $stdID, $std_excluded, $is_group);
          $exclude_str = '';
          if (substr($exclude,$blank_count-1,1) == '1') {
            $exclude_str =  ' style="background-color:#FFC0C0; color:#800000"';
          }
          $screen_output[$blank_count] = "<input type=\"text\" name=\"q" . $question_no . "_" . $blank_count . "\" value=\"" . $blank_parts[0] . "\" size=\"$blank_size[$blank_count]\"$exclude_str />" . $reviewScale . $blank_parts[1];
          $blank_count++;
        }
        $screen_output_all = '';
        foreach ($screen_output as $value) {
          $screen_output_all .= $value;
        }
        echo "<p class=\"stem\">" . $blank_details[0].$screen_output_all . "</p>\n";
        if (count($blank_mark) > 0) {
          foreach ($blank_mark as $individual_mark) {
            $marks += $individual_mark;
          }
        }
      } else {
        // Dropdown lists.
        $blank_details = explode('[blank',$display_option['option_text']);
        $no_blanks = count($blank_details);
        echo '<p>' . $blank_details[0];
        $blank_count = 1;
        while ($blank_count < $no_blanks) {
        $not_used = preg_match("/mark=\"[0-9]{1,3}\"/",$blank_details[$blank_count],$results);
        if (isset($results[1])) {
          $blank_mark[$blank_count] = $results[1];
        } else {
          $blank_mark[$blank_count] = 1;
        }
          $blank_details[$blank_count] = substr($blank_details[$blank_count], (strpos($blank_details[$blank_count],']') + 1));
          $answer_options = explode('[/blank]',$blank_details[$blank_count]);
          $answer_list = explode(',',$answer_options[0]);
          echo "<select name=\"q" . $question_no . "_" . $part_id . "\"";
          if (substr($exclude,$blank_count-1,1) == '1') echo ' style="background-color: rgb(255, 192, 192); color: rgb(128, 0, 0);"';
          echo ">\n";
          echo "<option value=\"u\"></option>\n";
          $correct_answer = $answer_list[0];
          shuffle($answer_list);
          for ($i=1; $i<=count($answer_list); $i++) {
            if (trim($answer_list[$i-1]) == trim($correct_answer)) {
              echo "<option value=\"" . trim($answer_list[$i-1]) . "\" selected=\"selected\">" . trim($answer_list[$i-1]) . "</option>\n";
            } else {
              echo "<option value=\"" . trim($answer_list[$i-1]) . "\">" . trim($answer_list[$i-1]) . "</option>\n";
            }
          }
          echo '</select> ';
          $review_val = get_review('blank', $std_method, $q_id, $blank_count, $tmp_reviews, $is_group);
          echo reviewScale($question_no, $part_id, $display_option['marks'], $std_method, substr($exclude,$blank_count-1,1), $review_val, $stdID, $std_excluded, $is_group);
          
          echo $answer_options[1]; // Bit after the closing [/blank] tag.
          $blank_count++;
          $part_id++;
        }
        echo '</p>';
        if (count($blank_mark) > 0) {
          foreach ($blank_mark as $individual_mark) {
            $marks += $individual_mark;
          }
        }
      }
      break;
    case 'hotspot':
      echo '<tr><a name="' . $question_no . '"></a><td class="question_no">' . $question_no . '.&nbsp;</td><td>';
?>

  <div>
  <script language="JavaScript">
    function swfLoaded<?php echo $question_no; ?>(message) {
      var num = message.substring(5,message.length);
      setUpFlash(num, message, '<?php echo $firstoption['q_media']; ?>', '<?php echo trim($firstoption['correct']); ?>', '', '0,1,0000000000000');
    }
    write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo ($firstoption['q_media_width'] + 300); ?>" height="<?php echo ($firstoption['q_media_height'] + 2); ?>" align="middle">');
    write_string('<param name="allowScriptAccess" value="always" />');
    write_string('<param name="movie" value="/touchstone/paper/hotspot_answer.swf" />');
    write_string('<param name="quality" value="high" />');
    write_string('<param name="bgcolor" value="<?php echo $bgcolor; ?>" />');
    write_string('<embed src="/touchstone/paper/hotspot_answer.swf" quality="high" bgcolor="<?php echo $bgcolor; ?>" width="<?php echo ($firstoption['q_media_width'] + 300); ?>" height="<?php echo ($firstoption['q_media_height'] + 2); ?>" swliveconnect="true" id="flash<?php echo $question_no; ?>" name="flash<?php echo $question_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
    write_string('</object>');
  </script>
  </div>
<?php
      $layers = explode('|', $firstoption['correct']);
      for ($i=1; $i<=count($layers); $i++) {
        $matching_options[] = substr($layers[$i-1], 0, strpos($layers[$i-1], '~'));
      }
      echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"u\" />\n";
      break;
    case 'labelling':
      $tmp_first_split = explode(';', $firstoption['correct']);
      $tmp_second_split = explode('$', $tmp_first_split[8]);
      $tmp_height = $firstoption['q_media_height'];
      if ($tmp_height < ($marks * 55)) $tmp_height = ($marks * 55);
      
      echo '<div align="center">';
      echo '<script language="JavaScript">';
      $firstoption['correct'] = str_replace('"','&#034;',$firstoption['correct']);
      $firstoption['correct'] = str_replace("'",'&#039;',$firstoption['correct']);
?> 
      function swfLoaded<?php echo $question_no; ?>(message) {
        var num = message.substring(5,message.length);
        setUpFlash(num, message, '<?php echo $firstoption['q_media']; ?>', '<?php echo trim($firstoption['correct']); ?>', '','');
      }
      write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo ($firstoption['q_media_width'] + 250); ?>" height="<?php echo $tmp_height; ?>" align="middle">');
      write_string('<param name="allowScriptAccess" value="always" />');
      write_string('<param name="movie" value="/touchstone/paper/label_answer.swf" />');
      write_string('<param name="quality" value="high" />');
      write_string('<param name="bgcolor" value="<?php echo $bgcolor; ?>" />');
      write_string('<embed src="/touchstone/paper/label_answer.swf" quality="high" bgcolor="<?php echo $bgcolor; ?>" width="<?php echo ($firstoption['q_media_width'] + 250); ?>" height="<?php echo $tmp_height; ?>" swliveconnect="true" id="flash<?php echo $question_no; ?>" name="flash<?php echo $question_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
      write_string('</object>');
<?php
      echo "</script>\n";
      echo "</div>\n";
      
      for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
        if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 200) {
          $matching_options[] = substr($tmp_second_split[$label_no],0,strpos($tmp_second_split[$label_no],'|'));
        }
      }
      
      echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"0$" . $marks . ";\" />\n";
      break;
    case 'flash':
?>

  <script language="JavaScript">
    var isInternetExplorer = navigator.appName.indexOf("Microsoft") != -1;
    function flash<?php echo $question_no; ?>_DoFSCommand(command, args) {
      var flash<?php echo $question_no; ?>Obj = isInternetExplorer ? document.all.flash<?php echo $question_no; ?> : document.flash<?php echo $question_no; ?>;
      document.questions.q<?php echo $question_no; ?>.value = args;
    }
    if (navigator.appName && navigator.appName.indexOf("Microsoft") != -1 && navigator.userAgent.indexOf("Windows") != -1 && navigator.userAgent.indexOf("Windows 3.1") == -1) {
      document.write('<script language=\"VBScript\"\>\n');
      document.write('On Error Resume Next\n');
      document.write('Sub flash<?php echo $question_no; ?>_FSCommand(ByVal command, ByVal args)\n');
      document.write('	Call flash<?php echo $question_no; ?>_DoFSCommand(command, args)\n');
      document.write('End Sub\n');
      document.write('</script\>\n');
    }
  </script>

  <div align="center">
    <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo $display_option['q_media_width']; ?>" height="<?php echo $display_option['q_media_height']; ?>" align="middle">
    <param name="allowScriptAccess" value="sameDomain" />
    <param name="movie" value="../media/<?php echo $display_option['q_media']; ?>" />
    <param name="quality" value="high" />
    <param name="bgcolor" value="<?php echo $bgcolor; ?>" />
<?php
    if ($display_option['scenario'] != '') {
      echo '<param name="FlashVars" value="' . $display_option['scenario'] . '">';
    }
    echo '<embed src="../media/' . $display_option['q_media'] . '"';
    if ($display_option['scenario'] != '') {
      echo ' FlashVars="' . $display_option['scenario'] . '"';
    }
    echo ' quality="high" bgcolor="' . $bgcolor . '" width="' . $display_option['q_media_width'] . '" height="' . $display_option['q_media_height'] . '" swLiveConnect=true id="flash' . $question_no . '" name="flash' . $question_no . '" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />';
?>
    </object>
  </div>

<?php
      echo "<div>";
      echo reviewScale($question_no, '', $display_option['marks'], $std_method, $qn_excluded, $review_val, $stdID, $std_excluded, $is_group);
      echo "</div>\n";
      echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"0,0\" />\n";
      $marks += $display_option['marks'];
      break;
    case 'sct':
      if($part_id == 1) echo "<div style=\"color:#C00000\"><strong>Note:</strong> It is not possible to standard set Script Concordance questions.</div>\n";
  }                  // End switch
}

function reviewScale($q_no, $part_no, $marks, $method, $excluded, $value, &$stdID, &$std_excluded, $is_group = false) {
  $html = '';
  $review_string = '';
  $valid = true;
  
  // Process the passed values for group reviews
  if ($is_group) {
    $review_string = (!empty($value['string'])) ? $value['string'] : '';
    $mean = (isset($value['raw_mean'])) ? $value['raw_mean'] : 0;
    $valid = (isset($value['valid'])) ? $value['valid'] : false;
    $tmp_value = (isset($value['mean'])) ? $value['mean'] : 0;
    if ($valid) {
      $review_string = "mean=$mean%, $review_string\n";
    } else {
      $review_string = "<img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"!\" /><span style=\"font-weight:bold\">mean=$mean%, </span><span style=\"color:red; font-weight:bold\">$review_string</span>\n";
    }
    if (isset($value['prev_review']) and $value['prev_review'] > 0) {
      $tmp_value = $value['prev_review'];
      $valid = true;
    }
    $value = $tmp_value;
  }
  
  $extra = '';
  if ($excluded) {
    $bgcolor = '#FFC0C0';
    $fgcolor = '#800000';
    $extra = 'exclude_';
    $std_excluded++;
  } else {
    $bgcolor = '#D6DFF7';
    $fgcolor = 'black';
  }

  $qID = $q_no;
  if ($part_no != '') $qID .= '_' . $part_no;
  switch ($method) {
    case 'modified_angoff':
      $html = "<select name=\"std$qID\" style=\"background-color:$bgcolor; color:$fgcolor\">\n<option value=\"\"></option>";
      for ($i=0; $i<=100; $i+=5) {
        if ($valid and $value == $i) {
          $html .= "<option value=\"$i\" selected=\"selected\">$i%</option>\n";
        } else {
          $html .= "<option value=\"$i\">$i%</option>\n";
        }
      }
      $html .=  "</select>\n";
      $html .= $review_string;
      break;
    case 'ebel':
      $html .= "<select onchange=\"recountCategories();\" id=\"valstd$stdID\" name=\"std$qID\" style=\"background-color:$bgcolor; color:$fgcolor\">\n<option value=\"\"></option>";
      $options = array('EE'=>'Easy - Essential','EI'=>'Easy - Important','EN'=>'Easy - Nice to know','ME'=>'Medium - Essential','MI'=>'Medium - Important','MN'=>'Medium - Nice to know','HE'=>'Hard - Essential','HI'=>'Hard - Important','HN'=>'Hard - Nice to know');
      foreach ($options as $ebel_value=>$title) {
        if ($valid and str_replace('exclude_','',$value) == $ebel_value) {
          $html .= "<option value=\"" . $extra . $ebel_value . "\" selected=\"selected\">$ebel_value: $title</option>\n";
        } else {
          $html .= "<option value=\"" . $extra . $ebel_value . "\">$ebel_value: $title</option>\n";
        }
      }
      $html .= "</select>\n";
      break;
  }
  //$marks = 4;  // remove this line
  $html .= "<input type=\"hidden\" id=\"std" . $stdID . "_marks\" value=\"$marks\" />\n";
  $stdID++;
  return $html;
}

function get_review($q_type, $method, $q_id, $part_no, $prev_reviews, $is_group = false) {
  $current_review = '';
  
  if(!$is_group or isset($prev_reviews[$q_id])) {
    switch ($method) {
      case 'modified_angoff':
        if (isset($prev_reviews[$q_id]) and $prev_reviews[$q_id] != '') {
          $sub_reviews = explode(',', $prev_reviews[$q_id]);
        } else {
          $sub_reviews = array('','','','','','','','','','','','','','','');
        }
        if ($q_type == 'mrq' or $q_type == 'dichotomous' or $q_type == 'rank' or $q_type == 'extmatch' or $q_type == 'matrix' or $q_type == 'blank' or $q_type == 'labelling' or $q_type == 'hotspot') {
          $current_review = (!empty($sub_reviews[$part_no - 1])) ? $sub_reviews[$part_no - 1] : '';
        } elseif ($q_type == 'textbox') {
          $current_review = $sub_reviews[count($sub_reviews)-$part_no];
        } else {
          $current_review = (isset($prev_reviews[$q_id])) ? $prev_reviews[$q_id] : '';
        }
        break;
      case 'ebel':
        if ($q_type == 'dichotomous' or $q_type == 'extmatch' or $q_type == 'mrq' or $q_type == 'matrix' or $q_type == 'blank' or $q_type == 'rank' or $q_type == 'labelling' or $q_type == 'hotspot') {
          if (isset($prev_reviews[$q_id])) {
            $sub_reviews = explode(',', $prev_reviews[$q_id]);
          } else {
            $sub_reviews = array('','','','','','','','','','','','','','','');
          }
          $current_review = (empty($sub_reviews[$part_no - 1])) ? '' : $sub_reviews[$part_no - 1];
        } else {
          if (isset($prev_reviews[$q_id])) {
            $current_review = $prev_reviews[$q_id];
          }
        }
        break;
    }
  }
  if ($is_group) {
    $rating_string = '';
    $prev_review = 0;
    $ratings_array = array();
    $rating_total = 0;
    $min = 100; $max = 0;
    foreach ($prev_reviews['user'] as $individual_rater) {
      if ($q_type == 'textbox') {
        $ratings_parts = explode(',',$individual_rater[$q_id]);
        if(isset($ratings_parts[$part_no - 1])) {
          $this_rating = $ratings_parts[count($ratings_parts) - $part_no];
        } else {
          // This is bad missing data, maybe as the result of changes to the question.  Fill in missing values with 0 temporarily
          $this_rating = 0;
          $individual_rater[$q_id] .= ',0';
        }
      } elseif ($q_type == 'dichotomous' or $q_type == 'extmatch' or $q_type == 'mrq' or $q_type == 'matrix' or $q_type == 'blank' or $q_type == 'rank' or $q_type == 'labelling' or $q_type == 'hotspot') {
        $ratings_parts = explode(',',$individual_rater[$q_id]);
        $this_rating = $ratings_parts[$part_no - 1];
      } else {
        $this_rating = $individual_rater[$q_id];
      }
      $ratings_array[] = $this_rating;
      $rating_total += $this_rating;
      $min = ($this_rating < $min) ? $this_rating : $min;
      $max = ($this_rating > $max) ? $this_rating : $max;
      $rater_name = $individual_rater['name'];
      if ($rating_string == '') {
        $rating_string = $rater_name . '=' . $this_rating . '%';
      } else {
        $rating_string .= ', ' . $rater_name . '=' . $this_rating . '%';
      }
    }
    $raw_mean = round($rating_total / count($ratings_array));
    // Round mean to nearest 5 to match values in scale
    $review_mean = round(($rating_total / count($ratings_array)) / 5) * 5;
    
    // Return previous group review value if it exists
    if(isset($prev_reviews[$q_id])) {
      $prev_review = $current_review;
    }
    
    $current_review = array('raw_mean' => $raw_mean, 'mean' => $review_mean, 'prev_review' => $prev_review, 'string' => $rating_string, 'valid' => ($max <= $min + 10));
  }
  
  return $current_review;
}

?>