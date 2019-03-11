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

require_once $cfg_web_root . 'include/std_set_shared_functions.inc';

$stdID = 0;

function display_options($screen, $options, $q_id, $current_theme, $current_scenario, $current_leadin, $current_notes, $q_type, $std_method, $tmp_reviews, $tmp_exclude, $is_group) {
  global $marking, $question_no, $part_id, $labelcolor, $old_likert_label, $li_set, $bgcolor, $total_marks, $old_correct_fback, $stdID, $std_excluded, $string;
  static $allanswers;
  if (!isset($allanswers)) {
    $allanswers = array();
  }

  $display_inline = array('dichotomous', 'mrq', 'rank', 'flash');
  $mediadirectory = rogo_directory::get_directory('media');

  $configObj = Config::get_instance();
  $questiondata = \questiondata::get_datastore($options[0]['q_type']);
  $render = new render($configObj);

  $option_no = count($options);
  $total_rank_no = 0;
  $require_na = false;
  $part_id = 0;
  $marks = 0;
  if ($options[0]['q_type'] != 'likert') $old_likert_label = '';

  // Determine if negative marking is used.
  // NOTE: Enhanced calc handled separately below
  $neg_marking = false;
  foreach ($options as $tmp_option) {
    if ($tmp_option['marks_incorrect'] < 0) $neg_marking = true;
  }


  if ($options[0]['q_type'] == 'dichotomous') {
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
    if ($marking < 2) {
      if (($options[0]['display_method'] == 'TF_NegativeAbstain') or ($options[0]['display_method'] == 'TF_PostiveAbstain')) {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">" . $string['true'] . "</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">" . $string['false'] . "</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">Abstain</td><td width=\"45%\">&nbsp;</td><td>&nbsp;</td></tr>\n";
      } elseif (($options[0]['display_method'] == 'TF_Negative') or ($options[0]['display_method'] == 'TF_Positive')) {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">" . $string['true'] . "</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">" . $string['false'] . "</td><td width=\"45%\">&nbsp;</td><td>&nbsp;</td></tr>\n";
      } elseif (($options[0]['display_method'] == 'YN_NegativeAbstain') or ($options[0]['display_method'] == 'YN_PostiveAbstain')) {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">" . $string['yes'] . "</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">" . $string['no'] . "</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">Abstain</td><td width=\"45%\">&nbsp;</td><td>&nbsp;</td></tr>\n";
      } else {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">" . $string['yes'] . "</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">" . $string['no'] . "</td><td width=\"45%\">&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
    } else {
      if ($options[0]['display_method'] == 'TF_Positive' or $options[0]['display_method'] == 'TF_Negative' or $options[0]['display_method'] == 'TF_NegativeAbstain') {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">" . $string['true'] . "</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">" . $string['false'] . "</td><td width=\"45%\">&nbsp;</td><td>&nbsp;</td></tr>\n";
      } else {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">" . $string['yes'] . "</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">" . $string['no'] . "</td><td width=\"45%\">&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
    }
  } elseif ($options[0]['q_type'] == 'mcq') {
    echo '<blockquote>';
    if ($options[0]['display_method'] == 'vertical' or $options[0]['display_method'] == 'vertical_other') {
      echo '<table cellpadding="2" cellspacing="0" border="0">';
    }
    if ($options[0]['display_method'] == 'dropdown') {
      echo '<div class="option"><select name="q' . $question_no . '" size="1">';
      echo '<option value=""></option>';
    }
  } elseif ($options[0]['q_type'] == 'mrq') {
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
  } elseif ($options[0]['q_type'] == 'rank') {
    if (strpos($tmp_exclude,'1') !== false) {    // If any option is excluded, exclude the whole question.
      $tmp_exclude = '1111111111111111111111111111111111111111';
    }
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';

    for ($i=0; $i<$option_no; $i++) {
      if ($options[$i]['correct'] != 0) {
        $total_rank_no++;
      }
      if ($options[$i]['correct'] == 0) $require_na = true;
    }
  } elseif ($options[0]['q_type'] == 'extmatch' or $options[0]['q_type'] == 'matrix') {
    $matching_scenarios = explode('|', $options[0]['scenario']);
    $matching_media = explode('|', $options[0]['q_media']);
    $matching_media_width = explode('|', $options[0]['q_media_width']);
    $matching_media_height = explode('|', $options[0]['q_media_height']);
    $matching_options = array();
    $matching_users_answers = explode('|', $options[0]['correct']);
  }

  if ($options[0]['q_type'] != 'info') {
    $i = 0;
    foreach ($options as $display_option) {
      $part_id++;
      $review_val = (in_array($options[0]['q_type'], $display_inline)) ? get_review($options[0]['q_type'], $std_method, $q_id, $part_id, $tmp_reviews, $is_group) : 0;

      switch ($options[0]['q_type']) {
        case 'blank':
          $exclude_this = $tmp_exclude;
          break;
        case 'mcq':       // MRQs and MCQs have multiple parts but are excluded as a whole
        case 'mrq':
          $exclude_this = substr($tmp_exclude,0,1);
          break;
        default:
          $exclude_this = substr($tmp_exclude,($part_id-1),1);
          break;
      }
      $feedback = ($options[0]['q_type'] == 'textbox') ? $old_correct_fback : '';
      display_option($options[0], $q_id, $question_no, $part_id, $display_option, $std_method, $is_group, $marks, $exclude_this, $review_val, $matching_options, 0, $tmp_reviews, $option_no, $stdID, $std_excluded, $total_rank_no, $require_na, $bgcolor, $feedback);
      $i++;
    }                    // End foreach loop
  }

  switch($options[0]['q_type']) {
    case 'area':
      $review_val = get_review($options[0]['q_type'], $std_method, $q_id, 1, $tmp_reviews, $is_group);
      echo "<div>";
      echo reviewScale($question_no, '', $options[0]['marks_correct'], $std_method, substr($tmp_exclude,0,1), $review_val, $stdID, $std_excluded, $is_group);
      echo "</div>\n";
      $marks = $options[0]['marks_correct'];
      break;
    case 'mcq':
      $review_val = get_review($options[0]['q_type'], $std_method, $q_id, 1, $tmp_reviews, $is_group);
      $scale_html = reviewScale($question_no, '', $options[0]['marks_correct'], $std_method, substr($tmp_exclude,0,1), $review_val, $stdID, $std_excluded, $is_group);
      if ($options[0]['display_method'] == 'vertical' or $options[0]['display_method'] == 'vertical_other') {
        if ($options[0]['display_method'] == 'vertical_other') {
          echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"other\" /></td><td>" . $string['other'] . " <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "[" . $part_id . "].checked=true\" name=\"q" . $question_no . "_other\" /></td></tr>\n";
        }
        echo '</table></blockquote>';
        echo "<div>";
        echo $scale_html;
        echo "</div>\n";
      } elseif ($options[0]['display_method'] == 'dropdown') {
        echo '</select>&nbsp;';
        echo $scale_html;
        echo "</div>\n";
      }
      break;
    case 'blank':
      if ($options[0]['score_method'] == 'Mark per Question') {
        $review_val = get_review($options[0]['q_type'], $std_method, $q_id, 1, $tmp_reviews, $is_group);
        echo '<br />' . reviewScale($question_no, 1, $options[0]['marks_correct'], $std_method, substr($tmp_exclude,0,1), $review_val, $stdID, $std_excluded, $is_group);
        $marks = $options[0]['marks_correct'];
      }
      break;
    case 'enhancedcalc':
      $question['settings'] = $options[0]['settings'];
      $question['theme'] = $current_theme;
      $question['leadin'] = $current_leadin;
      $question['scenario'] = $current_scenario;      
      $question['q_id'] = $q_id;      
      
      require_once '../plugins/questions/enhancedcalc/enhancedcalc.class.php';
      if (!isset($configObj)) {
        $configObj = Config::get_instance();
      }
      $question['object'] = new EnhancedCalc($configObj);
      $question['object']->load($question);
      $question['object']->load_all_user_answers($allanswers);

      $extra = array('num_on_screen' => $question_no, 'reviewers' => true);

      echo '<tr><td class="q_no">' . $question_no. '.&nbsp;</td><td>';
      $question['object']->render_paper($extra);

      $allanswers[$screen][$q_id]['uans'] =  $question['object']->get_real_answer();

      $marks = $question['object']->calculate_question_mark();

      $review_val = get_review($options[0]['q_type'], $std_method, $q_id, 1, $tmp_reviews, $is_group);
      echo "<div>" . reviewScale($question_no, '', $marks, $std_method, substr($tmp_exclude,0,1), $review_val, $stdID, $std_excluded, $is_group) . "</div>\n";

      // Negative marked?
      $neg_marking = $question['object']->is_negative_marked();

      break;
    case 'dichotomous':
      echo "</blockquote></table>";
      if ($options[0]['score_method'] == 'Mark per Question') {
        $review_val = get_review($options[0]['q_type'], $std_method, $q_id, 1, $tmp_reviews, $is_group);
        echo '<br />' . reviewScale($question_no, 1, $options[0]['marks_correct'], $std_method, substr($tmp_exclude,0,1), $review_val, $stdID, $std_excluded, $is_group);
      }
      break;
    case 'true_false':
      $review_val = get_review($options[0]['q_type'], $std_method, $q_id, 1, $tmp_reviews, $is_group);
      echo '<blockquote>';

      $true_checked = '';
      $false_checked = '';

      if ($options[0]['display_method'] == 'dropdown') {
        echo "<select name=\"q" . $question_no . "\">\n";
        echo "<option value=\"\"></option>\n";
        if ($options[0]['correct'] == 't') {
          echo "<option value=\"t\" selected>" . $string['true'] . "</option>\n";
        } else {
          echo "<option value=\"t\">" . $string['true'] . "</option>\n";
        }
        if ($options[0]['correct'] == 'f') {
          echo "<option value=\"f\" selected>" . $string['false'] . "</option>\n";
        } else {
          echo "<option value=\"f\">" . $string['false'] . "</option>\n";
        }
        echo "</select>\n";
        echo reviewScale($question_no, '', $options[0]['marks_correct'], $std_method, substr($tmp_exclude,0,1), $review_val, $stdID, $std_excluded, $is_group);
        echo '</blockquote>';
      } else {
        if ($options[0]['correct'] == 't') $true_checked = ' checked="checked"';
        if ($options[0]['correct'] == 'f') $false_checked = ' checked="checked"';

        echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"t\"$true_checked />&nbsp;True";
        if ($options[0]['display_method'] == 'horizontal') {
          echo '&nbsp;&nbsp;&nbsp;';
        } elseif ($options[0]['display_method'] == 'vertical') {
          echo '<br />';
        }
        echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"f\"$false_checked />&nbsp;False<br />\n";
        echo '</blockquote>';
        echo reviewScale($question_no, '', $options[0]['marks_correct'], $std_method, substr($tmp_exclude,0,1), $review_val, $stdID, $std_excluded, $is_group);
      }

      $marks = $options[0]['marks_correct'];
      break;
    case 'mrq':
    case 'rank':
      if ($options[0]['score_method'] == 'other') {
        $part_id++;
        echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td>";
        echo "<td>" . $string['other'] . " <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "_" . $part_id . ".checked=true\" name=\"q" . $question_no . "_other\" value=\"\" /></td></tr>\n";
      }
      echo '</table>';
      echo "<input type=\"hidden\" id=\"rank" . $question_no . "_options\" name=\"rank" . $question_no . "_options\" value=\"$part_id\" />\n";
      if ($options[0]['score_method'] == 'Bonus Mark') {
        $review_val = get_review($options[0]['q_type'], $std_method, $q_id, ($part_id+1), $tmp_reviews, $is_group);
        echo "<br />\n<div>" . $string['allitemscorrect'] . ": ";
        echo reviewScale($question_no, ($part_id+1), $options[0]['marks_correct'], $std_method, substr($tmp_exclude,0,1), $review_val, $stdID, $std_excluded, $is_group);
        echo "</div>\n";
        echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"". ($marks-1) . "\" />\n";
      } elseif ($options[0]['score_method'] == 'Mark per Question') {
        $review_val = get_review($options[0]['q_type'], $std_method, $q_id, 1, $tmp_reviews, $is_group);
        echo "<br />\n<div>";
        echo reviewScale($question_no, 1, $options[0]['marks_correct'], $std_method, substr($tmp_exclude,0,1), $review_val, $stdID, $std_excluded, $is_group);
        echo "</div>\n";
        echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"$part_id\" />\n";
      } else {
        echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"$marks\" />\n";
      }
      break;
    case 'extmatch':
      $matching_answers = explode('|', $options[0]['correct']);

      if ($matching_media[0] != '') {
        echo '<div class="mediadiv">';
        $questiondata->set_media($matching_media[0], $matching_media_width[0], $matching_media_height[0], '');
        $render->render($questiondata, $string, 'paper/media.html');
        echo '</div>';
      }
      $text_scenarios = 0;
      for ($part_id=0; $part_id<10; $part_id++) {
        if (isset($matching_scenarios[$part_id]) and $matching_scenarios[$part_id] != '') $text_scenarios++;
      }
      $media_scenarios = 0;
      for ($part_id=1; $part_id<10; $part_id++) {
        if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') $media_scenarios++;
      }
      $scenario_no = max($text_scenarios, $media_scenarios);
      echo '<ol class="extmatch">';
      $rating_part = 0;
      for ($part_id=1; $part_id<=$scenario_no; $part_id++) {
        $answer_no = substr_count($matching_answers[$part_id-1],'$') + 1;
        if ($options[0]['score_method'] == 'Mark per Option') {
          $marks += (substr_count($matching_answers[$part_id - 1],'$') + 1) * $options[0]['marks_correct'];
        }
        echo '<li>';
        if ($matching_scenarios[$part_id - 1] != '') {
          echo "<div";
          if (substr($tmp_exclude,$part_id-1,1) == '1') echo ' style="color:red; text-decoration:line-through"';
          echo ">" . $matching_scenarios[$part_id - 1] . "</div>";
        }
        if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') {
          echo "<p>";
          $questiondata->set_media($matching_media[$part_id], $matching_media_width[$part_id], $matching_media_height[$part_id], '');
          $render->render($questiondata, $string, 'paper/media.html');
          echo "</p>\n";
        }
        $sub_answers = explode('$',$matching_answers[$part_id - 1]);
        $list_size = 10;
        if (count($matching_options) < 10) $list_size = count($matching_options);
        if ($answer_no == 1) {
          echo "<select name=\"q" . $question_no . "_" . $part_id . "\" size=\"1\">\n";
          echo "<option value=\"u\"></option>\n";
        } else {
          echo "<br />\n";
          echo "<div class=\"option\"><select name=\"q" . $question_no . "_" . $part_id . "[]\" id=\"q" . $question_no . "_" . $part_id . "\" multiple=\"multiple\" size=\"$list_size\">\n";
        }

        $answer_count = 0;
        $multi_answers = explode('$', $matching_users_answers[$part_id - 1]);
        $tmp_option_no = 1;
        foreach ($matching_options as $single_option) {
          $tmp_answer_match = false;
          foreach ($multi_answers as $separate_tmp_answer) {
            if ($separate_tmp_answer == $tmp_option_no) {
              $tmp_answer_match = true;
              $answer_count++;
            }
          }
          if ($tmp_answer_match == true) {
            echo "<option value=\"$tmp_option_no\" selected>$single_option</option>\n";
          } else {
            echo "<option value=\"$tmp_option_no\">$single_option</option>\n";
          }
          $tmp_option_no++;
        }
        echo '</select>';
        if ($answer_no == 1) {
          echo '&nbsp;';
          $review_val = get_review($options[0]['q_type'], $std_method, $q_id, $rating_part + 1, $tmp_reviews, $is_group);
          if ($options[0]['score_method'] == 'Mark per Option') {
            echo reviewScale($question_no, $rating_part + 1, $options[0]['marks_correct'], $std_method, substr($tmp_exclude,($part_id - 1),1), $review_val, $stdID, $std_excluded, $is_group);
          }
          echo '</div></li>';
          $rating_part++;
        } else {
          echo '<div class="no_marks">(Hold down &lt;CTRL&gt; key, then click mouse to toggle options on/off)</div>';
          echo '</div><br /></li>';
          for ($i=1; $i<=$answer_count; $i++) {
            $review_val = get_review($options[0]['q_type'], $std_method, $q_id, $rating_part + 1, $tmp_reviews, $is_group);
            echo '<div>';
            if ($options[0]['score_method'] == 'Mark per Option') {
              echo reviewScale($question_no, $rating_part + 1, $options[0]['marks_correct'], $std_method, substr($tmp_exclude,($part_id - 1),1), $review_val, $stdID, $std_excluded, $is_group);
            }
            echo '</div>';
            $rating_part++;
          }
        }
        echo '<br />';
      }
      echo '</ol>';
      if ($options[0]['score_method'] == 'Mark per Question') {
        //$stdID = 1;
        $review_val = get_review($options[0]['q_type'], $std_method, $q_id, 1, $tmp_reviews, $is_group);
        echo reviewScale($question_no, 1, $options[0]['marks_correct'], $std_method, substr($tmp_exclude,0,1), $review_val, $stdID, $std_excluded, $is_group);
        $marks += $options[0]['marks_correct'];
      }
      echo '<input type="hidden" name="multimatching' . $question_no . '_options" value="' . count($matching_options) . '" /><input type="hidden" name="multimatching' . $question_no . '_correct_no" value="' . $marks . '" />';
      break;
    case 'labelling':
      $part_id = 1;
      if ($options[0]['score_method'] == 'Mark per Option') {
        echo "<blockquote>\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
        foreach ($matching_options as $single_option) {
          $review_val = get_review($options[0]['q_type'], $std_method, $q_id, $part_id, $tmp_reviews, $is_group);
          echo "<tr><td>" . chr($part_id + 64) . ".</td><td>";
          if (strpos($single_option,'.gif') !== false or strpos($single_option,'.png') !== false or strpos($single_option,'.jpg') !== false or strpos($single_option,'.jpeg') !== false) {
            $image_parts = explode('~', $single_option);
            echo '<img src="' . $mediadirectory->url($image_parts[0]) . '" width="' . $image_parts[1] . '" height="' . $image_parts[2] . '" border="0" />';
          } else {
            echo $single_option;
          }
          echo "</td><td>&nbsp;</td><td>";
          echo reviewScale($question_no, $part_id, $options[0]['marks_correct'], $std_method, substr($tmp_exclude,$part_id - 1,1), $review_val, $stdID, $std_excluded, $is_group);
          echo "</td></tr>\n";
          $part_id++;
          $marks += $options[0]['marks_correct'];
        }
        echo "</table>\n</blockquote>\n";
      } else {
        $review_val = get_review($options[0]['q_type'], $std_method, $q_id, 1, $tmp_reviews, $is_group);
        echo reviewScale($question_no, 1, $options[0]['marks_correct'], $std_method, substr($tmp_exclude,$part_id - 1,1), $review_val, $stdID, $std_excluded, $is_group);
        $marks += $options[0]['marks_correct'];
      }
      break;
    case 'hotspot':
      $part_id = 1;
      echo "<table summary=\"Options for Hotspot question\">\n";
      $excl_string = '';

      foreach ($matching_options as $single_option) {
        if (substr($tmp_exclude,$part_id - 1,1) == '1') {
          $excl_string = ' ;color:red; text-decoration:line-through';
        }
        $review_val = get_review($options[0]['q_type'], $std_method, $q_id, $part_id, $tmp_reviews, $is_group);
        echo "<tbody><tr><td style=\"padding-right: 12px$excl_string\">" .  chr($part_id+64) . ')&nbsp;' . $single_option . "</td><td>&nbsp;</td><td>";
        if ($options[0]['score_method'] == 'Mark per Option') {
          echo reviewScale($question_no, $part_id, $options[0]['marks_correct'], $std_method, substr($tmp_exclude,$part_id - 1,1), $review_val, $stdID, $std_excluded, $is_group);
          $marks += $options[0]['marks_correct'];
        }
        echo "</td></tr></tbody>\n";
        $part_id++;
      }
      echo '</table>';
      if ($options[0]['score_method'] == 'Mark per Question') {
        $review_val = get_review($options[0]['q_type'], $std_method, $q_id, 1, $tmp_reviews, $is_group);
        echo reviewScale($question_no, 1, $options[0]['marks_correct'], $std_method, substr($tmp_exclude,0,1), $review_val, $stdID, $std_excluded, $is_group);
        $marks = $options[0]['marks_correct'];
      }
      break;
    case 'matrix':
      $part_id = 1;
      if ($matching_media[0] != '') {
        echo "<div class=\"mediadiv\">";
        $questiondata->set_media($matching_media[0], $matching_media_width[0], $matching_media_height[0], '');
        $render->render($questiondata, $string, 'paper/media.html');
        echo "</div>\n";
      }
      echo '<table cellpadding="2" cellspacing="0" border="1" class="matrix">';
      echo "<tr><td colspan=\"2\">&nbsp;</td>\n";
      foreach ($matching_options as $single_option) {
        echo '<td>' . $single_option . '</td>';
      }
      if ($options[0]['score_method'] == 'Mark per Option') {
        echo '<td>&nbsp;</td>';
      }
      echo "</tr>\n";
      foreach ($matching_scenarios as $single_scenario) {
        $review_val = get_review($options[0]['q_type'], $std_method, $q_id, $part_id, $tmp_reviews, $is_group);
        if (trim($single_scenario) != '') {
          echo '<tr>';
          echo '<td align="right">' . chr(64 + $part_id) . '.</td><td';
          if (substr($tmp_exclude,$part_id-1,1) == '1') echo ' style="color:red; text-decoration:line-through"';
          echo '>' . $single_scenario . '</td>';
          $answer_no = 1;
          foreach ($matching_options as $single_option) {
            if ($matching_users_answers[$part_id - 1] == $answer_no) {
              echo '<td><div align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="' . $answer_no . '" checked /></div></td>';
            } else {
              echo '<td><div align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="' . $answer_no . '" /></div></td>';
            }
            $answer_no++;
          }
          if ($options[0]['score_method'] == 'Mark per Option') {
            echo '<td>' . reviewScale($question_no, $part_id, $options[0]['marks_correct'], $std_method, substr($tmp_exclude,$part_id - 1,1), $review_val, $stdID, $std_excluded, $is_group) . '</td>';
            $marks += $options[0]['marks_correct'];
          }
          echo "</tr>\n";
          $part_id++;
        }
      }
      echo '</table>';
      if ($options[0]['score_method'] == 'Mark per Question') {
        $review_val = get_review($options[0]['q_type'], $std_method, $q_id, 1, $tmp_reviews, $is_group);
        $marks = $options[0]['marks_correct'];
        echo '<br />' . reviewScale($question_no, 1, $options[0]['marks_correct'], $std_method, substr($tmp_exclude,0,1), $review_val, $stdID, $std_excluded, $is_group);
      }
      break;
  }

  // Display possible marks for question (if not Survey)
  if ($options[0]['q_type'] != 'hotspot' and $options[0]['q_type'] != 'likert' and $options[0]['q_type'] != 'mcq' and $options[0]['q_type'] != 'dichotomous') echo '</blockquote>';
  if ($q_type < 3 and $options[0]['q_type'] != 'info') {

      echo "<div class=\"mk\">($marks ";
      if ($marks == 1) {
        echo $string['mark'];
      } else {
        echo $string['marks'];
      }
      if ($options[0]['score_method'] == 'Bonus Mark') {
        $plural = ($options[0]['marks_correct'] == 1) ?  $string['mark'] : $string['marks'];
        echo ' ' . sprintf($string['bonusmark'], $options[0]['marks_correct'], $plural);  // Used on ranking questions
        $marks += $options[0]['marks_correct'];
      }
    if ($neg_marking) echo ', ' . $string['negmarking'];
    echo ")</div>\n<br /></td></tr>\n";

    $total_marks += $marks;
  }
}
?>
