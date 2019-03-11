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

function updateDB($review, $db) {
  $update = $db->prepare("UPDATE std_set SET pass_score = ?, distinction_score = ? WHERE id = ?");
  $update->bind_param('ddi', $review['pass_score'], $review['distinction_score'], $review['std_setID']);
  $update->execute();
  $update->close();
}

function load_ebel_percents($id, $mysqli) {
  $percents = array();

  $result = $mysqli->prepare("SELECT category, percentage FROM ebel WHERE std_setID = ?");
  $result->bind_param('i', $id);
  $result->execute();
  $result->store_result();
  $result->bind_result($category, $percentage);
  while ($result->fetch()) {
    $percents[$category] = round($percentage, 2);
  }
  $result->close();
  
  return $percents;
}

function load_question_ratings($id, $mysqli) {
  $ratings = array();
  
  $result = $mysqli->prepare("SELECT q_type, questionID, rating FROM std_set_questions, questions WHERE std_set_questions.questionID = questions.q_id AND std_setID = ?");
  $result->bind_param('i', $id);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_type, $questionID, $rating);
  while ($result->fetch()) {
    $ratings[] = array('q_type'=>$q_type, 'questionID'=>$questionID, 'rating'=>$rating);
  }
  $result->close();
  
  return $ratings;
}

function get_reviews($mysqli, $mode, $paperID, $total_marks, &$no_reviews = 0) {
  $configObject = Config::get_instance();
  
  $configObject->get('cfg_long_date_time');
  $reviews = array();
  
  $standard_setting = new StandardSetting($mysqli);
  
  $result = $mysqli->prepare("SELECT std_set.id, DATE_FORMAT(std_set,'%Y%m%d%H%i%s') AS std_set, DATE_FORMAT(std_set,'{$configObject->get('cfg_long_date_time')}') AS display_date, method, group_review, title, initials, surname, setterID, distinction_score FROM std_set, users WHERE std_set.setterID = users.id AND paperID = ? ORDER BY std_set");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->store_result();
  $result->bind_result($std_setID, $std_set, $display_date, $method, $group_review, $title, $initials, $surname, $setterID, $distinction_score);
  while ($result->fetch()) {
    $reviews[] = array(
    	'std_setID' => $std_setID,
    	'group_review' => $group_review,
    	'review_total' => 0, 
    	'total_marks' => 0, 
    	'setter_id' => $setterID,
    	'review_no' => 0,
    	'date' => $std_set,
      'display_date' => $display_date,
    	'method' => $method,
      'distinction_score' => $distinction_score,
      'pass_score' => 0,
      'name' => "$title $initials $surname");
  }
  $result->close();
 
  $no_reviews = count($reviews);
  for ($i=0 ; $i<$no_reviews; $i++) {
    // Get any questions to exclude.
    $exclude = array();

    // TODO: use exlcude class.
    $exclude_result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper = ?");
    $exclude_result->bind_param('i', $paperID);
    $exclude_result->execute();
    $exclude_result->store_result();
    $exclude_result->bind_result($q_id, $parts);
    while ($exclude_result->fetch()) {
      $exclude[$q_id] = $parts;
    }
    $exclude_result->close();
  
    // Calculate marks for the current paper.
    $marks_array = array();
    ss_get_marks_correct($mysqli, $paperID, $exclude, $marks_array);
    
    if ($reviews[$i]['method'] == 'Ebel') {
      $ebel_percents = load_ebel_percents($reviews[$i]['std_setID'], $mysqli);
    }
    
    $angoff_review_marks = 0;
    $std_total = 0;
    $question_no = 0;
    $ebel_marks = array('EE'=>0, 'EI'=>0, 'EN'=>0, 'ME'=>0, 'MI'=>0, 'MN'=>0, 'HE'=>0, 'HI'=>0, 'HN'=>0, 'EE2'=>0, 'EI2'=>0, 'EN2'=>0, 'ME2'=>0, 'MI2'=>0, 'MN2'=>0, 'HE2'=>0, 'HI2'=>0, 'HN2'=>0, 'exclude_EE'=>0, 'exclude_EI'=>0, 'exclude_EN'=>0, 'exclude_ME'=>0, 'exclude_MI'=>0, 'exclude_MN'=>0, 'exclude_HE'=>0, 'exclude_HI'=>0, 'exclude_HN'=>0);
    
    $ratings = load_question_ratings($reviews[$i]['std_setID'], $mysqli);
    $rating_no = count($ratings);
		
    if ($reviews[$i]['method'] == 'Modified Angoff' or $reviews[$i]['method'] == 'Ebel') {
      for ($x=0; $x<$rating_no; $x++) {  // Loop around the questions
        $questionID = $ratings[$x]['questionID'];
        $rating = $ratings[$x]['rating'];
        $q_type = $ratings[$x]['q_type'];
      
        if (array_key_exists($questionID, $exclude)) {
          $tmp_exclude = $exclude[$questionID];
        } else {
          $tmp_exclude = '0000000000000000000000000000000000000000';
        }
        $partID = 0;

        if ($rating != '') {
          $rating_array = explode(',', $rating);
          
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
    
          if ($reviews[$i]['method'] == 'Modified Angoff') {
            $tmp_part = 0;
            foreach ($rating_array as $individual_rating) {
              if (isset($marks_array[$questionID][$tmp_part]) and $individual_rating != '') {
                if (substr($tmp_exclude, $tmp_part, 1) == '0') {
                  if ($q_type == 'textbox') {
                    $textbox_marks = $marks_array[$questionID][0];
                    for ($a=0; $a<$textbox_marks; $a++) {
                      if(isset($rating_array[$a])) {
                        $std_total += $rating_array[$a] * ($textbox_marks - $a);
                      }
                    }
                  } else {
                    $std_total += $individual_rating * $marks_array[$questionID][$tmp_part];
                  }
                  if ($q_type != 'mrq' or $individual_rating != '') $question_no++;
                }
              }
              $tmp_part++;
            }
          }
        }
      }
    }
    
    if ($reviews[$i]['method'] == 'Modified Angoff') {
      $reviews[$i]['pass_score'] = ($question_no > 0) ? round($std_total / $angoff_review_marks) : 0;
      $reviews[$i]['review_total'] = $angoff_review_marks;
      $reviews[$i]['distinction_score'] = 'n/a';
    } elseif ($reviews[$i]['method'] == 'Hofstee') {
      $reviews[$i]['review_total'] = $total_marks;
      $results = $standard_setting->get_pass_distinction($reviews[$i]['std_setID']);
      $reviews[$i]['pass_score'] = round($results['pass_score'], 1);
      $reviews[$i]['distinction_score'] = round($results['distinction_score'], 1);
    } elseif ($reviews[$i]['method'] == 'Ebel') {
      $cut_marks = 0.0;
      $cut_marks += ($ebel_marks['EE'] + $ebel_marks['exclude_EE']) * $ebel_percents['EE'] * 100;
      $cut_marks += ($ebel_marks['EI'] + $ebel_marks['exclude_EI']) * $ebel_percents['EI'] * 100;
      $cut_marks += ($ebel_marks['EN'] + $ebel_marks['exclude_EN']) * $ebel_percents['EN'] * 100;
      $cut_marks += ($ebel_marks['ME'] + $ebel_marks['exclude_ME']) * $ebel_percents['ME'] * 100;
      $cut_marks += ($ebel_marks['MI'] + $ebel_marks['exclude_MI']) * $ebel_percents['MI'] * 100;
      $cut_marks += ($ebel_marks['MN'] + $ebel_marks['exclude_MN']) * $ebel_percents['MN'] * 100;
      $cut_marks += ($ebel_marks['HE'] + $ebel_marks['exclude_HE']) * $ebel_percents['HE'] * 100;
      $cut_marks += ($ebel_marks['HI'] + $ebel_marks['exclude_HI']) * $ebel_percents['HI'] * 100;
      $cut_marks += ($ebel_marks['HN'] + $ebel_marks['exclude_HN']) * $ebel_percents['HN'] * 100;
      $reviews[$i]['review_total'] = $ebel_marks['EE'] + $ebel_marks['EI'] + $ebel_marks['EN'] + $ebel_marks['ME'] + $ebel_marks['MI'] + $ebel_marks['MN'] + $ebel_marks['HE'] + $ebel_marks['HI'] + $ebel_marks['HN'] + $ebel_marks['exclude_EE'] + $ebel_marks['exclude_EI'] + $ebel_marks['exclude_EN'] + $ebel_marks['exclude_ME'] + $ebel_marks['exclude_MI'] + $ebel_marks['exclude_MN'] + $ebel_marks['exclude_HE'] + $ebel_marks['exclude_HI'] + $ebel_marks['exclude_HN'];

      $pass_score = ($total_marks == 0) ? $total_marks : ($cut_marks / ($total_marks  * 100)) * 100;
      $reviews[$i]['pass_score'] = round($pass_score, 1);

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
      
      if ($reviews[$i]['distinction_score'] > 0) {
        $distinction_score = ($total_marks == 0) ? $total_marks : (($cut_marks2 / ($total_marks * 100)) * 100);
        $reviews[$i]['distinction_score'] = round($distinction_score, 1);
      }
    }
    $reviews[$i]['total_marks'] = $total_marks;
  }

  return $reviews;
}

function ss_get_marks_correct($mysqli, $paper_id, $exclude, &$marks_array) {
  $partID = 0;
  $old_q_id = 0;
  $stems = 0;
  $correct_no = 0;
  $old_score_method = '';
  
  $result = $mysqli->prepare("SELECT q_type, q_id, marks_correct, marks_incorrect, correct, score_method, option_text, settings FROM papers, questions LEFT JOIN options ON questions.q_id = options.o_id WHERE papers.paper = ? AND papers.question = questions.q_id AND q_type != 'info' ORDER BY q_id, o_id, id_num");
  $result->bind_param('i', $paper_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_type, $q_id, $marks_correct, $marks_incorrect, $correct, $score_method, $option_text, $settings);
  while ($result->fetch()) {
    if ($old_q_id != $q_id and $old_q_id != 0) {
      if ($old_q_type == 'rank' and $old_score_method == 'Bonus Mark') {
        if (isset($exclude[$old_q_id]) and strpos($exclude[$old_q_id], '1') !== false) {
          $marks_array[$old_q_id][$partID] = 0;
        } else {
          $marks_array[$old_q_id][$partID] = $old_marks_correct;
        }
      }
      $stems = 0;
      $correct_no = 0;
      $old_marks_correct = 0;
      $partID = 0;
    }

    $old_q_id = $q_id;
    if (!isset($exclude[$old_q_id])) { // If no record exists set to zero (i.e. included)
      $exclude[$old_q_id] = '00000000000000000000000000000';
    }
    
    if (!is_null($settings) and !is_array($settings)) {
      $settings = json_decode($settings, true);
    }

    switch ($q_type) {
      case 'area':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          $marks_array[$old_q_id][$partID] = $marks_correct;
        }
        break;
      case 'textbox':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          $marks_array[$old_q_id][$partID] = $marks_correct;
        }
        break;
      case 'dichotomous':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          if ($score_method == 'Mark per Option') {
            $marks_array[$old_q_id][$partID] = $marks_correct;
          } elseif ($score_method == 'Mark per Question') {
            $marks_array[$old_q_id][0] = $marks_correct;
          }
        }
        $partID++;
        break;
      case 'enhancedcalc':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          $marks_array[$old_q_id][$partID] = $settings['marks_correct'];
        }
        break;
      case 'true_false':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          $marks_array[$old_q_id][$partID] = $marks_correct;
        }
        break;
      case 'hotspot':
        $tmp_first_split = explode('|', $correct);
        for ($i = 0; $i < count($tmp_first_split); $i++) {
          if (substr($exclude[$old_q_id], $i, 1) == '0') {
            $marks_array[$old_q_id][$i] = $marks_correct;
          } else {
            $marks_array[$old_q_id][$i] = 0;
          }
        }
        break;
      case 'blank':
        $no_blanks = substr_count($option_text, '[blank');
        for ($i = 0; $i < $no_blanks; $i++) {
          if (substr($exclude[$old_q_id], $i, 1) == '0') {
            $marks_array[$old_q_id][$i] = $marks_correct;
          }
        }
        break;
      case 'matrix':
        $tmp_part = 0;
        $matching_correct = explode('|', $correct);
        for ($part_id = 0; $part_id < count($matching_correct); $part_id++) {
          if (trim($matching_correct[$part_id]) != '') {
            if (substr($exclude[$old_q_id], $part_id, 1) == '0') {
              $marks_array[$old_q_id][$tmp_part] = $marks_correct;
            }
            $tmp_part++;
          }
        }
        break;
      case 'extmatch':
        $tmp_part = 0;
        $matching_correct = explode('|', $correct);
        for ($part_id = 0; $part_id < count($matching_correct); $part_id++) {
          $sub_array = explode('$', $matching_correct[$part_id]);
          for ($sub_part_id = 0; $sub_part_id < count($sub_array); $sub_part_id++) {
            if (substr($exclude[$old_q_id], $tmp_part, 1) == '0') {
              $marks_array[$old_q_id][$tmp_part] = $marks_correct;
            } else {
              $marks_array[$old_q_id][$tmp_part] = 0;
            }
            $tmp_part++;
          }
        }
        break;
      case 'flash':
        if (substr($exclude[$old_q_id], 0, 1) == '0') {
          $marks_array[$old_q_id][0] = $marks_correct;
        }
        break;
      case 'mcq':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          if ($correct == $partID + 1) {
            $marks_array[$old_q_id][0] = $marks_correct;
          } 
          $partID++;
        }
        break;
      case 'mrq':
        if (substr($exclude[$old_q_id], $partID, 1) == '0') {
          if ($score_method == 'Mark per Option') {
            if ($correct == 'y') {
              $marks_array[$old_q_id][$partID] = $marks_correct;
            }
            $partID++;
          } elseif ($score_method == 'Mark per Question') {
            $marks_array[$old_q_id][0] = $marks_correct;
          } else {
            $marks_array[$old_q_id][$partID] = $marks_incorrect;
            $partID++;
          }
        }
        break;
      case 'rank':
        if ($correct < 9990) {
          if (strpos($exclude[$old_q_id], '1') === false) {
            $marks_array[$old_q_id][$partID] = $marks_correct;
          } else {
            $marks_array[$old_q_id][$partID] = 0;
          }
          $partID++;
        }
        break;
      case 'labelling':
        $tmp_first_split = explode(';', $correct);
        $tmp_second_split = explode('$', $tmp_first_split[11]);
        for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
          if (substr($tmp_second_split[$label_no], 0, 1) != '|' and $tmp_second_split[$label_no - 2] > 219) {
            if (substr($exclude[$old_q_id], $partID, 1) == '0') {
              $marks_array[$old_q_id][$partID] = $marks_correct;
            } else {
              $marks_array[$old_q_id][$partID] = 0;
            }
            $partID++;
          }
        }
        break;
    }

    $old_q_type = $q_type;
    $old_score_method = $score_method;
    $old_correct = $correct;
    $old_option_text = $option_text;
    $old_marks_correct = $marks_correct;
    if ($q_type == 'mrq') {
      if ($correct == 'y') $correct_no++;
    }
    if ($q_type == 'rank') {
      if ($correct < 9990) $correct_no++;
    }
    $stems++;
  }

  $result->close();

  if ($old_q_type == 'rank' and $old_score_method == 'Bonus Mark') {
    if (isset($exclude[$old_q_id]) and strpos($exclude[$old_q_id], '1') !== false) {
      $marks_array[$old_q_id][$partID] = 0;
    } else {
      $marks_array[$old_q_id][$partID] = $marks_array[$old_q_id][$partID-1];
    }
  }
}

function display_option($firstoption, $q_id, $question_no, &$part_id, $display_option, $std_method, $is_group, &$marks, $exclude, $review_val, &$matching_options, $review_mean, $tmp_reviews, $option_no, &$stdID, &$std_excluded, $total_rank_no, $require_na, $bgcolor, $feedback) {
  global $string, $language, $configObject;
  
  $configObject->get('cfg_root_path');
  $qn_excluded = ($exclude == '1');
  $mediadirectory = rogo_directory::get_directory('media');
  $questiondata = \questiondata::get_datastore($firstoption['q_type']);
  $render = new render($configObject);
  
  switch ($firstoption['q_type']) {
    case 'area':
      if ($display_option['scenario'] == '') {
        echo '<tr><td class="q_no">' . $question_no . '.&nbsp;</td><td>';
      } else {
        echo '<tr><td></td><td>';
      }
      echo $display_option['leadin'] . '<br />';
      //<!-- ======================== HTML5 part ================= -->
      echo "<canvas id='canvas" . $question_no . "' width='" . ($display_option['q_media_width'] + 2) . "' height='" . ($display_option['q_media_height'] + 24) . "'></canvas>\n";
      echo "<div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
      echo "<script>\n";
      echo "setUpQuestion(" . $question_no . ", 'q" . $question_no . "','" . $language . "', '" . $display_option['q_media'] . "', '" . $display_option['correct'] . "', '','','#FFC0C0','area','script');\n";
      echo "</script>\n";
      //<!-- ==================================================== -->
      echo "<br /><br />\n";
      break;
    case 'textbox':
      $settings = json_decode($display_option['settings'], true);
      echo "<blockquote><textarea cols=\"" . $settings['columns'] . "\" rows=\"" . $settings['rows'] . "\"></textarea></blockquote>\n";
      echo "<pre style=\"width:90%; font-style:italic; color:#C00000\">" . wordwrap($feedback, 150) . "</pre>";
      $marks = $display_option['marks_correct'];
      
      $plural = 's';
      if ($std_method == 'modified_angoff') {
        for ($mark_part=$marks; $mark_part>0; $mark_part--) {
          $review_val = get_review('textbox', $std_method, $q_id, $mark_part, $tmp_reviews, $is_group);
          echo reviewScale($question_no, $mark_part, $display_option['marks_correct'], $std_method, $qn_excluded, $review_val, $stdID, $std_excluded, $is_group);
          if ($mark_part == 1) $plural = '';
          echo "<span class=\"part-mark\">$mark_part mark$plural</span><br />\n";
        }
      } else {
        echo "<div style=\"color:#C00000\">" . $string['cannotbeused'] . "</div>\n";
      }
      break;
    case 'dichotomous':
      $tmp_user_answer = $display_option['correct'];
      if (($firstoption['display_method'] == 'TF_NegativeAbstain') or ($firstoption['display_method'] == 'TF_PostiveAbstain') or ($firstoption['display_method'] == "YN_NegativeAbstain") or ($firstoption['display_method'] == "YN_PostiveAbstain")) {
        $abstain = true;
      } else {
        $abstain = false;
      }

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
      if ($abstain) {
        echo '<td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="a" /></td>';
      }
      if ($exclude == '1') {
        echo '<td style="color:red; text-decoration:line-through">';
      } else {
        echo '<td>';
      }
      if ($display_option['option_text'] != '') echo $display_option['option_text'];
      if ($display_option['o_media'] != '') {
        if ($display_option['option_text'] != '') echo '<br />';
        $questiondata->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
        $render->render($questiondata, $string, 'paper/media.html');
      }
      echo "</td>";
      echo '<td>';
      if ($display_option['score_method'] == 'Mark per Option') {
        echo reviewScale($question_no, $part_id, $display_option['marks_correct'], $std_method, ($exclude == '1'), $review_val, $stdID, $std_excluded, $is_group);
      }
      echo '</td>';
      echo "</tr>\n";
      if ($display_option['score_method'] == 'Mark per Option') {
         $marks += $display_option['marks_correct'];
      } else {
        $marks = $display_option['marks_correct'];
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
            echo ">" . $display_option['option_text'] . "<br />";
            $questiondata->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
            $render->render($questiondata, $string, 'paper/media.html');
            echo "</td></tr>\n";
          } else {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td";
            if ($qn_excluded) echo ' style="color:red; text-decoration:line-through"';
            echo ">" . $display_option['option_text'] . "<br />";
            $questiondata->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
            $render->render($questiondata, $string, 'paper/media.html');
            echo "</td></tr>\n";
          }
        } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
          if ($part_id == $display_option['correct']) {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" checked=\"checked\" /></td><td>";
            $questiondata->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
            $render->render($questiondata, $string, 'paper/media.html');
            echo "</td></tr>\n";
          } else {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td>";
            $questiondata->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
            $render->render($questiondata, $string, 'paper/media.html');
            echo "</td></tr>\n";
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
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" checked=\"checked\" />&nbsp;" . $display_option['option_text'] . "<br />";
            $questiondata->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
            $render->render($questiondata, $string, 'paper/media.html');
            echo "&nbsp;&nbsp;";
          } else {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" />&nbsp;" . $display_option['option_text'] . "<br />";
            $questiondata->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
            $render->render($questiondata, $string, 'paper/media.html');
            echo "&nbsp;&nbsp;";
          }
        } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
          if ($part_id == $display_option['correct']) {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" checked=\"checked\" />&nbsp;";
            $questiondata->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
            $render->render($questiondata, $string, 'paper/media.html');
            echo "&nbsp;&nbsp;";
          } else {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" />&nbsp;";
            $questiondata->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
            $render->render($questiondata, $string, 'paper/media.html');
            echo "&nbsp;&nbsp;";
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
        $marks = $display_option['marks_correct'];
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
            echo reviewScale($question_no, $part_id, $display_option['marks_correct'], $std_method, $qn_excluded, $review_val, $stdID, $std_excluded, $is_group);
            echo "</td></tr>\n";
          }
        } else {
          echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td><td$excl_string><span id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span></td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        }
      } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and image
        if ($display_option['correct'] == 'y') {
          if ($display_option['score_method'] == 'Mark per Question') {
            echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked=\"checked\" /><span class=\"active\" id=\"$question_no" . "_" . "$part_id\"$excl_string>" . $display_option['option_text'] . "</span><br /><img src=\"" . $mediadirectory->url($display_option['o_media']) . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" border=\"0\"/></td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
          } else {
            echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked=\"checked\" /><span class=\"active\" id=\"$question_no" . "_" . "$part_id\"$excl_string>" . $display_option['option_text'] . "</span><br /><img src=\"" . $mediadirectory->url($display_option['o_media']) . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" border=\"0\"/>";
            echo "</td><td>&nbsp;</td><td>\n";
            echo reviewScale($question_no, $part_id, $display_option['marks_correct'], $std_method, $qn_excluded, $review_val, $stdID, $std_excluded, $is_group);
            echo "</td></tr>\n";
          }
        } else {
          echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /><span class=\"active\" id=\"$question_no" . "_" . "$part_id\"$excl_string>" . $display_option['option_text'] . "</span><br /><img src=\"" . $mediadirectory->url($display_option['o_media']) . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" border=\"0\"/></td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        }
      } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and image
        if ($display_option['correct'] == 'y' and $display_option['score_method'] != 'Mark per Question') {
          echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked=\"checked\" /><img src=\"" . $mediadirectory->url($display_option['o_media']) . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" border=\"0\"/></td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        } else {
          echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /><img src=\"" . $mediadirectory->url($display_option['o_media']) . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" border=\"0\"/>";
          echo "</td><td>&nbsp;</td><td>\n";
          echo reviewScale($question_no, $part_id, $display_option['marks_correct'], $std_method, $qn_excluded, $review_val, $stdID, $std_excluded, $is_group);
          echo "</td></tr>\n";
        }
      }
      if ($display_option['score_method'] == 'Mark per Option') {
        if ($display_option['correct'] == 'y') {
          $marks += $display_option['marks_correct'];
        }
      } else {
        if ($part_id == 1) {
          $marks += $display_option['marks_correct'];
        }
      }
      break;
    case 'extmatch':
    case 'matrix':
      $matching_options[] = $display_option['option_text'];
      break;
    case 'rank':
      echo "<tr><td class=\"option\"><select name=\"q" . $question_no . "_" . $part_id . "\">\n";
      echo "<option value=\"u\"></option>\n";
      if ($require_na) {
        echo "<option value=\"0\">" . $string['na'] . "</option>\n";
      }
      for ($i=1; $i<=$total_rank_no; $i++) {
        if ($i == $display_option['correct']) {
          echo "<option value=\"" . $i . "\" selected>" . $i;
        } else {
          echo "<option value=\"" . $i . "\">" . $i;
        }
        if ($language == 'en') {
          if ($i == 1) {
            echo 'st';
          } elseif ($i == 2) {
            echo 'nd';
          } elseif ($i == 3) {
            echo 'rd';
          } else {
            echo 'th';
          }
        }
        echo '</option>';
      }
      echo '</select></td>';
      echo "<td";
      if (substr($exclude,0,1) == '1') echo ' style="color:red; text-decoration:line-through"';
      echo ">" . $display_option['option_text'] . "</td><td>&nbsp;</td>\n";
      
      if (($display_option['correct'] != 0 or $display_option['score_method'] == 'Mark per Option') and $display_option['score_method'] != 'Mark per Question') {
        echo "<td>";
        echo reviewScale($question_no, $part_id, $display_option['marks_correct'], $std_method, $qn_excluded, $review_val, $stdID, $std_excluded, $is_group);
        echo "</td>\n";
      } else {
        echo "<td></td>\n";
      }
      echo "</tr>\n";
       
      if ($display_option['score_method'] == 'Mark per Question') {
        if ($part_id == 1) {
          $marks += $display_option['marks_correct'];
        }
      } elseif ($display_option['correct'] != 0) {
        $marks += $display_option['marks_correct'];
      } elseif ($display_option['score_method'] == 'Mark per Option') {
        $marks += $display_option['marks_correct'];
      }
      break;
    case 'blank':
      if ($display_option['display_method'] == 'textboxes') {
        $blank_details = explode('[blank]', $display_option['option_text']);
        $array_size = count($blank_details);
        $blank_count = 1;
        while ($blank_count < $array_size) {
          $reviewScale = '';
          $blank_parts = explode("[/blank]", $blank_details[$blank_count]);
          $results = array();
          $not_used = preg_match("|size=\"([0-9]{1,3})\"|", $blank_details[$blank_count], $results);

          if (isset($results[1]) and $results[1] != '') {
            $blank_size[$blank_count] = $results[1];
          } else {
            $blank_size[$blank_count] = 15;
          }
          $results = array();
          $not_used = preg_match("|mark=\"([0-9]{1,3})\"|",$blank_details[$blank_count],$results);
          if (isset($results[1])) {
            $blank_mark[$blank_count] = $results[1];
          } else {
            $blank_mark[$blank_count] = $display_option['marks_correct'];
          }
          if($display_option['score_method'] == 'Mark per Option') {
            $review_val = get_review('blank', $std_method, $q_id, $blank_count, $tmp_reviews, $is_group);
            $reviewScale .= reviewScale($question_no, $blank_count, $display_option['marks_correct'], $std_method, substr($exclude,$blank_count-1,1), $review_val, $stdID, $std_excluded, $is_group);
          }
          $exclude_str = '';
          if (substr($exclude, $blank_count-1, 1) == '1') {
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
          $not_used = preg_match("/mark=\"[0-9]{1,3}\"/", $blank_details[$blank_count], $results);
          if (isset($results[1])) {
            $blank_mark[$blank_count] = $results[1];
          } else {
            $blank_mark[$blank_count] = $display_option['marks_correct'];
          }
          $blank_details[$blank_count] = substr($blank_details[$blank_count], (strpos($blank_details[$blank_count],']') + 1));
          $answer_options = explode('[/blank]', $blank_details[$blank_count]);
          $answer_list = explode(',', $answer_options[0]);
          $answer_list = param::clean_array($answer_list, param::TEXT);
          echo "<select name=\"q" . $question_no . "_" . $part_id . "\"";
          if (substr($exclude, $blank_count-1, 1) == '1') echo ' style="background-color: rgb(255, 192, 192); color: rgb(128, 0, 0);"';
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
          if ($display_option['score_method'] == 'Mark per Option') {
            $review_val = get_review('blank', $std_method, $q_id, $blank_count, $tmp_reviews, $is_group);
            echo reviewScale($question_no, $part_id, $display_option['marks_correct'], $std_method, substr($exclude,$blank_count-1,1), $review_val, $stdID, $std_excluded, $is_group);
          }
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
      if ($display_option['scenario'] == '') {
        echo '<tr><td class="question_no">' . $question_no . '.&nbsp;</td><td>';
      } else {
        echo '<tr><td></td><td>';
      }
      $extra = '0,1,' . $exclude;
      $tmp_correct = str_replace("'", "\'", trim($display_option['correct']));
      $tmp_correct = str_replace("&nbsp;", " ", $tmp_correct);
      $tmp_correct = preg_replace('/\r\n/', '', $tmp_correct); 
?>
    <div>
		<?php
		  //"<!-- ======================== HTML5 part ================= -->
		  echo "<canvas id='canvas" . $question_no . "' width='" . ($display_option['q_media_width'] + 301) . "' height='" . ($display_option['q_media_height'] + 30) . "'></canvas>\n";
		  echo "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
		  echo "<script>\n";
		  echo "setUpQuestion(" . $question_no . ", 'flash" . $question_no . "', '" . $language . "', '" . $mediadirectory->url($display_option['q_media'], false, false, true) . "', '" . $tmp_correct . "', '', '" . $extra . "','#FFC0C0','hotspot','script');\n";
		  echo "</script>\n";
		  //<!-- ==================================================== -->
		?>
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
      $tmp_second_split = explode('$', $tmp_first_split[11]);
      $tmp_height = $firstoption['q_media_height'];
      if ($tmp_height < ($marks * 55)) $tmp_height = ($marks * 55);
      $display_option['correct'] = str_replace('"','&#034;',$display_option['correct']);
      $display_option['correct'] = str_replace("'",'&#039;',$display_option['correct']);
      echo '<div align="center">';

      //<!-- ======================== HTML5 part ================= -->
      echo "<canvas id='canvas" . $question_no . "' width='" . ($display_option['q_media_width'] + 250) . "' height='" . $tmp_height . "'></canvas>\n";
      echo "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
      echo "<script>\n";
      echo "setUpQuestion(" . $question_no . ", 'flash" . $question_no . "', '" . $language . "', '" . $mediadirectory->url($display_option['q_media'], false, false, true) . "', '" . trim($display_option['correct']) . "', '',',0000000000000000000000000000000000000000,11110','#FFC0C0','labelling','script');\n";
      echo "</script>\n";
      //<!-- ==================================================== -->

      echo "</div>\n";
      echo '<div align="center" style="color:#808080">'.$string['mousereveal'].'</div>';
      for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
        if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 200) {
          $matching_options[] = substr($tmp_second_split[$label_no],0,strpos($tmp_second_split[$label_no],'|'));
        }
      }
      
      echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"0$" . $marks . ";\" />\n";
      break;
    case 'flash':
?>

  <script>
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
    <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab" id="flash<?php echo $question_no; ?>" width="<?php echo $display_option['q_media_width']; ?>" height="<?php echo $display_option['q_media_height']; ?>" align="middle">
    <param name="allowScriptAccess" value="sameDomain" />
    <param name="movie" value="<?php echo $mediadirectory->url($display_option['q_media']); ?>" />
    <param name="quality" value="high" />
    <param name="bgcolor" value="<?php echo $bgcolor; ?>" />
<?php
    if ($display_option['scenario'] != '') {
      echo '<param name="FlashVars" value="' . $display_option['scenario'] . '">';
    }
    echo '<embed src="' . $mediadirectory->url($display_option['q_media']) . '"';
    if ($display_option['scenario'] != '') {
      echo ' FlashVars="' . $display_option['scenario'] . '"';
    }
    echo ' quality="high" bgcolor="' . $bgcolor . '" width="' . $display_option['q_media_width'] . '" height="' . $display_option['q_media_height'] . '" swLiveConnect=true id="flash' . $question_no . '" name="flash' . $question_no . '" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />';
?>
    </object>
  </div>

<?php
      echo "<div>";
      echo reviewScale($question_no, '', $display_option['marks_correct'], $std_method, $qn_excluded, $review_val, $stdID, $std_excluded, $is_group);
      echo "</div>\n";
      echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"0,0\" />\n";
      $marks += $display_option['marks_correct'];
      break;
    case 'sct':
      if ($part_id == 1) {
        echo "<div style=\"color:#C00000\"><strong>" . $string['note'] . "</strong> " . $string['notpossibletostandard'] . "</div>\n";
        $marks++;
      }
  }                  // End switch
}

function reviewScale($q_no, $part_no, $marks, $method, $excluded, $value, &$stdID, &$std_excluded, $is_group = false) {
  global $string;

  $html = '';
  $review_string = '';
  $valid = true;
  
  // Process the passed values for group reviews
  if ($is_group) {
    $review_string = ($value['string'] === 0 or !empty($value['string'])) ? $value['string'] : '';
    $mean = (isset($value['raw_mean'])) ? $value['raw_mean'] : 0;
    $valid = (isset($value['valid'])) ? $value['valid'] : false;
    $tmp_value = (isset($value['mean'])) ? $value['mean'] : 0;
    if ($valid) {
      $review_string = "mean=$mean%, $review_string\n";
    } else {
      $review_string = "<img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"!\" />&nbsp;<span style=\"font-weight:bold\">mean=$mean%, </span><span style=\"color:#C00000; font-weight:bold\">$review_string</span>\n";
    }
    if (isset($value['prev_review']) and $value['prev_review'] > 0) {
      $tmp_value = $value['prev_review'];
      $valid = true;
    }
    $value = $tmp_value;
  }
  
  $extra = '';
  if ($excluded) {
    $dropdown_class = 'dropdown_excluded';
    $extra = 'exclude_';
    $std_excluded++;
  } else {
    $dropdown_class = 'dropdown';
  }

  $qID = $q_no;
  if ($part_no != '') $qID .= '_' . $part_no;
  switch ($method) {
    case 'modified_angoff':
      $html = "<select name=\"std$qID\" class=\"$dropdown_class\">\n<option value=\"\"></option>\n";
      for ($i=0; $i<=100; $i+=5) {
        $sel = ($valid and $value !== '' and $value == $i) ? ' selected="selected"' : '';
        $html .= "<option value=\"$i\"$sel>$i%</option>\n";
      }
      $html .=  "</select>\n";
      $html .= $review_string;
      break;
    case 'ebel':
      $html .= "<select onchange=\"recountCategories();\" id=\"valstd$stdID\" name=\"std$qID\" class=\"$dropdown_class\">\n<option value=\"\"></option>";
      $options = array('EE'=>$string['ee_full'],'EI'=>$string['ei_full'],'EN'=>$string['en_full'],'ME'=>$string['me_full'],'MI'=>$string['mi_full'],'MN'=>$string['mn_full'],'HE'=>$string['he_full'],'HI'=>$string['hi_full'],'HN'=>$string['hn_full']);
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
  $html .= "<input type=\"hidden\" name=\"std" . $stdID . "_marks\" id=\"std" . $stdID . "_marks\" value=\"$marks\" />\n";
  $stdID++;
  return $html;
}

function get_review($q_type, $method, $q_id, $part_no, $prev_reviews, $is_group = false) {
  $current_review = '';
  
  if (!$is_group or isset($prev_reviews[$q_id])) {
    switch ($method) {
      case 'modified_angoff':
        if (isset($prev_reviews[$q_id]) and $prev_reviews[$q_id] != '') {
          $sub_reviews = explode(',', $prev_reviews[$q_id]);
        } else {
          $sub_reviews = array('','','','','','','','','','','','','','','');
        }
        if ($q_type == 'mrq' or $q_type == 'dichotomous' or $q_type == 'rank' or $q_type == 'extmatch' or $q_type == 'matrix' or $q_type == 'blank' or $q_type == 'labelling' or $q_type == 'hotspot') {
          $current_review = (strlen($sub_reviews[$part_no - 1]) > 0) ? $sub_reviews[$part_no - 1] : '';
        } elseif ($q_type == 'textbox') {
          $current_review = (strlen($sub_reviews[count($sub_reviews)-$part_no]) > 0) ? $sub_reviews[count($sub_reviews)-$part_no] : '';
        } else {
          $current_review = (strlen($prev_reviews[$q_id]) > 0) ? $prev_reviews[$q_id] : '';
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
    $min = 100;
		$max = 0;
    foreach ($prev_reviews['user'] as $individual_rater) {
      if ($q_type == 'textbox') {
        $ratings_parts = explode(',',$individual_rater[$q_id]);
        if (isset($ratings_parts[$part_no - 1])) {
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