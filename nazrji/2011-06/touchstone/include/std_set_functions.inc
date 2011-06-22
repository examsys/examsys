<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

$stdID = 0;
function reviewScale($q_type, $tmp_q_id, $q_no, $tmp_part_no, $set_method, $prev_reviews, $status) {
  global $mrq_part, $stdID, $std_excluded;
  if (strpos($status,'1') !== false) {
    $bgcolor = '#FFC0C0';
    $fgcolor = '#800000';
    $std_excluded++;
  } else {
    $bgcolor = '#D6DFF7';
    $fgcolor = 'black';
  }

  $qID = $q_no;
  $extra = '';
  if ($tmp_part_no != '') $qID .= '_' . $tmp_part_no;
  $html = '';
  switch ($set_method) {
    case 'modified_angoff':
      $html = "<select name=\"std$qID\" style=\"background-color:$bgcolor; color:$fgcolor\">\n<option value=\"\"></option>";
      if (isset($prev_reviews[$tmp_q_id]) and $prev_reviews[$tmp_q_id] != '') {
        $sub_reviews = explode(',', $prev_reviews[$tmp_q_id]);
      } else {
        $sub_reviews = array('','','','','','','','','','','','','','','');
      }
      if ($q_type == 'mrq') {
        $current_review = $sub_reviews[$mrq_part - 1];
      } elseif ($q_type == 'dichotomous' or $q_type == 'rank' or $q_type == 'extmatch' or $q_type == 'matrix' or $q_type == 'blank' or $q_type == 'labelling' or $q_type == 'hotspot') {
        $current_review = (!empty($sub_reviews[$tmp_part_no - 1])) ? $sub_reviews[$tmp_part_no - 1] : '';
      } elseif ($q_type == 'textbox') {
        $current_review = $sub_reviews[count($sub_reviews)-$tmp_part_no];
      } else {
        $current_review = $prev_reviews[$tmp_q_id];
      }

      for ($i=0; $i<=100; $i+=5) {
        if ($current_review == $i and $current_review != '') {
          $html .= "<option value=\"$i\" selected>$i%</option>\n";
        } else {
          $html .= "<option value=\"$i\">$i%</option>\n";
        }
      }
      $html .=  "</select>\n";
      break;
    case 'ebel':
      if ($q_type == 'dichotomous' or $q_type == 'extmatch' or $q_type == 'mrq' or $q_type == 'matrix' or $q_type == 'blank' or $q_type == 'rank' or $q_type == 'labelling' or $q_type == 'hotspot') {
        if (isset($prev_reviews[$tmp_q_id])) {
          $sub_reviews = explode(',', $prev_reviews[$tmp_q_id]);
        } else {
          $sub_reviews = array('','','','','','','','','','','','','','','');
        }
        if ($q_type == 'rank') {
          $current_review = (empty($sub_reviews[$mrq_part - 1])) ? '' : $sub_reviews[$mrq_part - 1];
        } else {
          $current_review = (empty($sub_reviews[$tmp_part_no - 1])) ? '' : $sub_reviews[$tmp_part_no - 1];
        }
      } else {
        if (isset($prev_reviews[$tmp_q_id])) {
          $current_review = $prev_reviews[$tmp_q_id];
        } else {
          $current_review = '';
        }
      }
      $html .= "<select onchange=\"recountCategories();\" id=\"valstd$stdID\" name=\"std$qID\" style=\"background-color:$bgcolor; color:$fgcolor\">\n<option value=\"\"></option>";
      $options = array('EE'=>'Easy - Essential','EI'=>'Easy - Important','EN'=>'Easy - Nice to know','ME'=>'Medium - Essential','MI'=>'Medium - Important','MN'=>'Medium - Nice to know','HE'=>'Hard - Essential','HI'=>'Hard - Important','HN'=>'Hard - Nice to know');
      foreach ($options as $value=>$title) {
        if (strpos($status,'1') !== false) $extra = 'exclude_';
        if (str_replace('exclude_','',$current_review) == $value) {
          $html .= "<option value=\"" . $extra . $value . "\" selected>$value: $title</option>";
        } else {
          $html .= "<option value=\"" . $extra . $value . "\">$value: $title</option>";
        }
      }
      $html .= "</select>\n";
      break;
  }
  $stdID++;
  return $html;
}

function checkVariables($number) {
  global $A, $B, $C, $D, $E, $F, $G, $H;
  eval("\$new_number = \"$number\";");
  return $new_number;
}

function display_options($options,$q_id, $current_theme, $current_scenario, $current_leadin, $current_notes, $q_type, $std_method, $tmp_reviews, $exclude) {
  global $marking, $question_no, $question_offset, $part_id, $labelcolor, $old_likert_label, $A, $B, $C, $D, $E, $F, $G, $H, $li_set, $bgcolor, $mrq_part, $total_marks, $old_correct_fback;

  if (array_key_exists($q_id,$exclude)) {
    $tmp_exclude = $exclude[$q_id];
  } else {
    $tmp_exclude = '0000000000000000000000000000000000000000';
  }

  $option_no = count($options);
  $part_id = 0;
  $marks = 0;
  if ($options[0]['q_type'] != 'likert') $old_likert_label = '';

  if ($options[0]['q_type'] == 'dichotomous') {
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
    if ($marking < 2) {
      if (($options[0]['score_method'] == 'TF_NegativeAbstain') or ($options[0]['score_method'] == 'TF_PostiveAbstain')) {
        echo "<tr><td></td><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">True</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">False</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">Abstain</td><td>&nbsp;</td></tr>\n";
      } elseif (($options[0]['score_method'] == 'TF_Negative') or ($options[0]['score_method'] == 'TF_Positive')) {
        echo "<tr><td></td><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">True</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">False</td></tr>\n";
      } elseif (($options[0]['score_method'] == 'YN_NegativeAbstain') or ($options[0]['score_method'] == 'YN_PostiveAbstain')) {
        echo "<tr><td></td><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">No</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">Abstain</td><td>&nbsp;</td></tr>\n";
      } else {
        echo "<tr><td></td><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">No</td></tr>\n";
      }
    } else {
      if ($options[0]['score_method'] == 'TF_Positive' or $options[0]['score_method'] == 'TF_Negative' or $options[0]['score_method'] == 'TF_NegativeAbstain') {
        echo "<tr><td></td><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">True</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">False</td><td></td></tr>\n";
      } else {
        echo "<tr><td></td><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">No</td><td></td></tr>\n";
      }
    }
  } elseif ($options[0]['q_type'] == 'mcq') {
    echo '<blockquote>';
    if ($options[0]['score_method'] == 'vertical' or $options[0]['score_method'] == 'vertical_other') {
      echo '<table cellpadding="2" cellspacing="0" border="0">';
    }
    if ($options[0]['score_method'] == 'dropdown') {
      echo '<div class="option"><select name="q' . $question_no . '" size="1">';
      echo '<option value=""></option>';
    }
  } elseif ($options[0]['q_type'] == 'mrq') {
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
  } elseif ($options[0]['q_type'] == 'rank') {
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
  } elseif ($options[0]['q_type'] == 'extmatch' or $options[0]['q_type'] == 'matrix') {
    $matching_scenarios = explode('|', $options[0]['scenario']);
    $matching_media = explode('|', $options[0]['q_media']);
    $matching_media_width = explode('|', $options[0]['q_media_width']);
    $matching_media_height = explode('|', $options[0]['q_media_height']);
    $matching_options = array();
    $matching_users_answers = explode('|', $options[0]['correct']);
  }

  $i = 0;
  $mrq_part = 1;
  foreach ($options as $display_option) {
    $part_id++;
    switch ($options[0]['q_type']) {
      case 'textbox':
        $textbox_size = explode('x',$display_option['score_method']);
        echo "<blockquote><textarea style=\"font-family:Arial,sans-serif\" cols=\"" . $textbox_size[0] . "\" rows=\"" . $textbox_size[1] . "\"></textarea></blockquote>\n";
        echo "<pre style=\"width:90%; font-family:Arial,sans-serif; font-style:italic; color:#C00000\">" . wordwrap($old_correct_fback, 150) . "</pre>";
        $marks = $display_option['marks'];
         
        if ($std_method == 'modified_angoff') {
          for ($mark_part=$marks; $mark_part>0; $mark_part--) {
            echo reviewScale('textbox', $q_id, $question_no, $mark_part, $std_method, $tmp_reviews, substr($tmp_exclude,$mark_part,1)) . " $mark_part marks<br />\n";
          }
        } else {
          echo "<div style=\"color:#C00000\"><strong>Note:</strong> Ebel method cannot be used to standard set textbox questions.</div>\n";
        }
        break;
      case 'dichotomous':
        $tmp_user_answer = $display_option['correct'];
        if (($options[0]['score_method'] == 'TF_NegativeAbstain') or ($options[0]['score_method'] == 'TF_PostiveAbstain') or ($options[0]['score_method'] == "YN_NegativeAbstain") or ($options[0]['score_method'] == "YN_PostiveAbstain")) $abstain = true;

        echo '<tr><td>' .  reviewScale('dichotomous', $q_id, $question_no, $part_id, $std_method, $tmp_reviews, substr($tmp_exclude,$part_id-1,1)) . '</td><td align="center">';
        if ($tmp_user_answer == 't') {
          echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" checked /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" /></td>';
        } elseif ($tmp_user_answer == 'f') {
          echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" checked /></td>';
        } elseif ($tmp_user_answer == 'a') {
          echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" /></td>';
        } else {
          echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" /></td>';
        }
        if (substr($tmp_exclude,$part_id-1,1) == '1') {
          echo '<td style="color:red; text-decoration:line-through">';
        } else {
          echo '<td>';
        }
        if ($display_option['option_text'] != '') echo stripslashes($display_option['option_text']);
        if ($display_option['o_media'] != '') {
          if ($display_option['option_text'] != '') echo '<br />';
          echo display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']);
        }
        echo "</td></tr>\n";
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
          }
        break;
      case 'mcq':
        if ($options[0]['score_method'] == 'vertical' or $options[0]['score_method'] == 'vertical_other') {
          if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No media
            if ($part_id == $display_option['correct']) {
              echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" checked /></td><td";
              if (isset($exclude[$q_id])) echo ' style="color:red; text-decoration:line-through"';
              echo ">" . $display_option['option_text'] . "</td></tr>\n";
            } else {
              echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td";
              if (isset($exclude[$q_id])) echo ' style="color:red; text-decoration:line-through"';
              echo ">" . $display_option['option_text'] . "</td></tr>\n";
            }
          } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and media
            if ($part_id == $display_option['correct']) {
              echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" checked /></td><td";
              if (isset($exclude[$q_id])) echo ' style="color:red; text-decoration:line-through"';
              echo ">" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "</td></tr>\n";
            } else {
              echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td";
              if (isset($exclude[$q_id])) echo ' style="color:red; text-decoration:line-through"';
              echo ">" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "</td></tr>\n";
            }
          } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
            if ($part_id == $display_option['correct']) {
              echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" checked /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "</td></tr>\n";
            } else {
              echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "</td></tr>\n";
            }
          }
        } elseif ($options[0]['score_method'] == 'horizontal') {
          if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No media
            if ($part_id == $display_option['correct']) {
              echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" checked />&nbsp;<span";
              if (isset($exclude[$q_id])) echo ' style="color:red; text-decoration:line-through"';
              echo ">" . $display_option['option_text'] ."</span>&nbsp;&nbsp;";
            } else {
              echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" />&nbsp;<span";
              if (isset($exclude[$q_id])) echo ' style="color:red; text-decoration:line-through"';
              echo ">" . $display_option['option_text'] . "&nbsp;&nbsp;";
            }
          } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and media
            if ($part_id == $display_option['correct']) {
              echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" checked />&nbsp;" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "&nbsp;&nbsp;";
            } else {
              echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" />&nbsp;" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "&nbsp;&nbsp;";
            }
          } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
            if ($part_id == $display_option['correct']) {
              echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" checked />&nbsp;" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "&nbsp;&nbsp;";
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
        if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No image
          if ($display_option['correct'] == 'y') {
            if ($display_option['score_method'] == 'AllItemsCorrect') {
              echo "<tr><td>&nbsp;</td><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked /></td><td";
              if (isset($exclude[$q_id])) echo ' style="color:red; text-decoration:line-through"';
              echo ">" . stripslashes($display_option['option_text']) . "</td></tr>\n";
            } else {
              echo "<tr><td>" .  reviewScale('mrq', $q_id, $question_no, $part_id, $std_method, $tmp_reviews, substr($tmp_exclude,0,1)) . "</td><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked /></td><td";
              if (isset($exclude[$q_id])) echo ' style="color:red; text-decoration:line-through"';
              echo ">" . stripslashes($display_option['option_text']) . "</td></tr>\n";
              $mrq_part++;
            }
          } else {
            echo "<tr><td></td><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td><td";
            if (isset($exclude[$q_id])) echo ' style="color:red; text-decoration:line-through"';
            echo "><span id=\"$question_no" . "_" . "$part_id\">" . stripslashes($display_option['option_text']) . "</span></td></tr>\n";
          }
        } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and image
          if ($display_option['correct'] == 'y') {
            if ($display_option['score_method'] == 'AllItemsCorrect') {
              echo "<tr><td>&nbsp;</td><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked /><span class=\"active\" id=\"$question_no" . "_" . "$part_id\">" . stripslashes($display_option['option_text']) . "</span><br /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" border=\"0\"/></td></tr>\n";
            } else {
              echo "<tr><td>" .  reviewScale('mrq', $q_id, $question_no, $part_id, $std_method, $tmp_reviews, substr($tmp_exclude,0,1)) . "</td><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked /><span class=\"active\" id=\"$question_no" . "_" . "$part_id\">" . stripslashes($display_option['option_text']) . "</span><br /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" border=\"0\"/></td></tr>\n";
              $mrq_part++;
            }
          } else {
            echo "<tr><td></td><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /><span class=\"active\" id=\"$question_no" . "_" . "$part_id\">" . stripslashes($display_option['option_text']) . "</span><br /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" border=\"0\"/></td></tr>\n";
          }
        } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and image
          if ($display_option['correct'] == 'y' and $display_option['score_method'] != 'AllItemsCorrect') {
            echo "<tr><td>&nbsp;</td><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . stripslashes($display_option['o_media_height']) . "\" border=\"0\"/></td></tr>\n";
          } else {
            echo "<tr><td>" .  reviewScale('mrq', $q_id, $question_no, $part_id, $std_method, $tmp_reviews, substr($tmp_exclude,0,1)) . "</td><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . stripslashes($display_option['o_media_height']) . "\" border=\"0\"/></td></tr>\n";
            $mrq_part++;
          }
        }
        if ($display_option['score_method'] == 'SelectedPositive') {
          if ($display_option['correct'] == 'y') $marks++;  // 1 mark for correct options only
        } elseif ($display_option['score_method'] == 'AllItemsCorrect') {
          if ($part_id == 1) $marks++;
        } else {
          $marks++;  // 1 mark for each and every item
        }
        break;
      case 'extmatch':
      case 'matrix':
        $matching_options[] = $display_option['option_text'];
        break;
      case 'rank':
        $total_rank_no = 0;
        $require_na = false;
        for ($i=0; $i<$option_no; $i++) {
          if ($options[$i]['correct'] != 0) {
            $total_rank_no++;
          }
          if ($options[$i]['correct'] == 0) $require_na = true;
        }

        if ($part_id == 1 and $display_option['score_method'] == 'BonusMark') {
          $marks++;  // Add one mark at start in case all options are answered correctly
        }

        if ($display_option['correct'] != 0 or $display_option['score_method'] == 'StrictOrder') {
          echo "<tr><td>" .  reviewScale('rank', $q_id, $question_no, $part_id, $std_method, $tmp_reviews, substr($tmp_exclude,0,1)) . "</td><td class=\"option\"><select name=\"q" . $question_no . "_" . $part_id . "\">\n";
        } else {
          echo "<tr><td></td><td class=\"option\"><select name=\"q" . $question_no . "_" . $part_id . "\">\n";
        }
        $mrq_part++;
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
        if (isset($exclude[$q_id])) echo ' style="color:red; text-decoration:line-through"';
        echo ">" . $display_option['option_text'] . "</td>\n</tr>\n";
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
        $date_details = explode('|',$display_option['score_method']);
        $answer_array = preg_split('/[/\s:]+/',$display_option['correct']);
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
        if ($date_details[0] == 1 or $date_details[0] == 4) {
          $answer_day = $answer_array[1];
          $answer_month = $answer_array[0];
          echo '<select name="q' . $question_no . '_month">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_month=1; $tmp_month<=12; $tmp_month++) {
            if ($tmp_month < 10) {
              if (intval($answer_month) == $tmp_month) {
                echo "<option value=\"0$tmp_month\" selected>0$tmp_month</option>\n";
              } else {
                echo "<option value=\"0$tmp_month\">0$tmp_month</option>\n";
              }
            } else {
              if (intval($answer_month) == $tmp_month) {
                echo "<option value=\"$tmp_month\" selected>$tmp_month</option>\n";
              } else {
                echo "<option value=\"$tmp_month\">$tmp_month</option>\n";
              }
            }
          }
          echo '</select> / ';
        }
        if ($date_details[0] == 1 or $date_details[0] == 2 or $date_details[0] == 3 or $date_details[0] == 4 or $date_details[0] == 5) {
          echo '<select name="q' . $question_no . '_day">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_day=1; $tmp_day<=31; $tmp_day++) {
            if ($tmp_day < 10) {
              if (intval($answer_day) == $tmp_day) {
                echo "<option value=\"0$tmp_day\" selected>0$tmp_day</option>\n";
              } else {
                echo "<option value=\"0$tmp_day\">0$tmp_day</option>\n";
              }
            } else {
              if (intval($answer_day) == $tmp_day) {
                echo "<option value=\"$tmp_day\" selected>$tmp_day</option>\n";
              } else {
                echo "<option value=\"$tmp_day\">$tmp_day</option>\n";
              }
            }
          }
          echo '</select>';
          if ($date_details[0] != 4 and $date_details[0] != 5) {
            echo ' / <select name="q' . $question_no . '_month">';
            echo "\n<option value=\"\"></option>\n";
            for ($tmp_month=1; $tmp_month<=12; $tmp_month++) {
              if ($tmp_month < 10) {
                if (intval($answer_month) == $tmp_month) {
                  echo "<option value=\"0$tmp_month\" selected>0$tmp_month</option>\n";
                } else {
                  echo "<option value=\"0$tmp_month\">0$tmp_month</option>\n";
                }
              } else {
                if (intval($answer_month) == $tmp_month) {
                  echo "<option value=\"$tmp_month\" selected>$tmp_month</option>\n";
                } else {
                  echo "<option value=\"$tmp_month\">$tmp_month</option>\n";
                }
              }
            }
            echo '</select>';
          } elseif ($date_details[0] == 5) {
            $month_array = array('January','February','March','April','May','June','July','August','September','October','November','December');
            $tmp_month = 1;
            echo ' / <select name="q' . $question_no . '_month">';
            echo "\n<option value=\"\"></option>\n";
            foreach ($month_array as $individual_month) {
              if ($tmp_month < 10) {
                if (intval($answer_month) == $tmp_month) {
                  echo "<option value=\"0$tmp_month\" selected>$individual_month</option>\n";
                } else {
                  echo "<option value=\"0$tmp_month\">$individual_month</option>\n";
                }
              } else {
                if (intval($answer_month) == $tmp_month) {
                  echo "<option value=\"$tmp_month\" selected>$individual_month</option>\n";
                } else {
                  echo "<option value=\"$tmp_month\">$individual_month</option>\n";
                }
              }
              $tmp_month++;
            }
            echo '</select>';
          }
          echo ' / <select name="q' . $question_no . '_year">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_year=$date_details[1]; $tmp_year<=$date_details[2]; $tmp_year++) {
            if (intval($answer_year) == $tmp_year) {
              echo "<option value=\"$tmp_year\" selected>$tmp_year</option>\n";
            } else {
              echo "<option value=\"$tmp_year\">$tmp_year</option>\n";
            }
          }
          echo "</select>\n";
        }
        if ($date_details[0] == 1 or $date_details[0] == 2 or $date_details[0] == 6 or $date_details[0] == 7) {
          if ($date_details[0] < 6) echo '&nbsp;&nbsp;&nbsp;';
          echo '<select name="q' . $question_no . '_hour">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_hour=0; $tmp_hour<=23; $tmp_hour++) {
            if ($tmp_hour < 10) {
              if (intval($answer_hour) == $tmp_hour) {
                echo "<option value=\"0$tmp_hour\" selected>0$tmp_hour</option>\n";
              } else {
                echo "<option value=\"0$tmp_hour\">0$tmp_hour</option>\n";
              }
            } else {
              if (intval($answer_hour) == $tmp_hour) {
                echo "<option value=\"$tmp_hour\">$tmp_hour</option>\n";
              } else {
                echo "<option value=\"$tmp_hour\">$tmp_hour</option>\n";
              }
            }
          }
          echo "</select>\n";
          echo ' : <select name="q' . $question_no . '_minute">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_min=0; $tmp_min<=60; $tmp_min++) {
            if ($tmp_min < 10) {
              if (intval($answer_minutes) == $tmp_min) {
                echo "<option value=\"0$tmp_min\" selected>0$tmp_min</option>\n";
              } else {
                echo "<option value=\"0$tmp_min\">0$tmp_min</option>\n";
              }
            } else {
              if (intval($answer_minutes) == $tmp_min) {
                echo "<option value=\"$tmp_min\" selected>$tmp_min</option>\n";
              } else {
                echo "<option value=\"$tmp_min\">$tmp_min</option>\n";
              }
            }
          }
          echo "</select>\n";
          if ($date_details[0] == 1 or $date_details[0] == 6) {
            echo ' : <select name="q' . $question_no . '_second">';
            echo "\n<option value=\"\"></option>\n";
            for ($tmp_sec=0; $tmp_sec<=60; $tmp_sec++) {
              if ($tmp_sec < 10) {
                if (intval($answer_seconds) == $tmp_sec) {
                  echo "<option value=\"0$tmp_sec\" selected>0$tmp_sec</option>\n";
                } else {
                  echo "<option value=\"0$tmp_sec\">0$tmp_sec</option>\n";
                }
              } else {
                if (intval($answer_seconds) == $tmp_sec) {
                  echo "<option value=\"$tmp_sec\" selected>$tmp_sec</option>\n";
                } else {
                  echo "<option value=\"$tmp_sec\">$tmp_sec</option>\n";
                }
              }
            }
            echo "</select>\n";
          }
        }
        echo "</p>\n";

        echo "<div>" .  reviewScale('timedate', $q_id, $question_no, $part_id, $std_method, $tmp_reviews, substr($tmp_exclude,0,1)) . "</div>\n";
        $marks++;
        break;
      case 'blank':
        if ($display_option['score_method'] == 'textboxes') {
	      $blank_details = explode('[blank',$display_option['option_text']);
	      $array_size = count($blank_details);
	      $blank_count = 1;
	      while ($blank_count < $array_size) {
	        $blank_details[$blank_count] = '[blank' . $blank_details[$blank_count];
	        $results = array();
	        $not_used = ereg("size=\"([0-9]{1,3})\"",$blank_details[$blank_count],$results);

	        if ($results[1]) {
	          $blank_size[$blank_count] = $results[1];
	        } else {
	          $blank_size[$blank_count] = 15;
	        }
	        $results=array();
	        $not_used = ereg("mark=\"([0-9]{1,3})\"",$blank_details[$blank_count],$results);
	        if ($results[1]) {
	          $blank_mark[$blank_count] = $results[1];
	        } else {
	          $blank_mark[$blank_count] = 1;
	        }
	        $reviewScale = reviewScale('blank', $q_id, $question_no, $blank_count, $std_method, $tmp_reviews, substr($tmp_exclude,$blank_count-1,1));
	        $screen_output[$blank_count] = preg_replace("/\[blank.*\].*\[/blank\]/","<input type=\"text\" name=\"q" . $question_no . "_" . $blank_count . "\" size=\"$blank_size[$blank_count]\" />$reviewScale",$blank_details[$blank_count]);
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
          echo '<p><span';
          if (substr($tmp_exclude,0,1) == '1') echo ' style="color:red; text-decoration:line-through"';
          echo '>' . $blank_details[0];
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
            echo "</span><select name=\"q" . $question_no . "_" . $part_id . "\">\n";
            echo "<option value=\"u\"></option>\n";
            $correct_answer = $answer_list[0];
            shuffle($answer_list);
            for ($i=1; $i<=count($answer_list); $i++) {
              if (trim($answer_list[$i-1]) == trim($correct_answer)) {
                echo "<option value=\"" . trim($answer_list[$i-1]) . "\" selected>" . trim($answer_list[$i-1]) . "</option>\n";
              } else {
                echo "<option value=\"" . trim($answer_list[$i-1]) . "\">" . trim($answer_list[$i-1]) . "</option>\n";
              }
            }
            echo '</select> ' . reviewScale('blank', $q_id, $question_no, $part_id, $std_method, $tmp_reviews, substr($tmp_exclude,$blank_count-1,1));

            echo '<span';
            if (substr($tmp_exclude,$blank_count,1) == '1') echo ' style="color:red; text-decoration:line-through"';
            echo '>' .  $answer_options[1]; // Bit after the closing [/blank] tag.
            $blank_count++;
            $part_id++;
          }
          echo '</span></p>';
          if (count($blank_mark) > 0) {
            foreach ($blank_mark as $individual_mark) {
              $marks += $individual_mark;
            }
          }
        }
        break;
      case 'hotspot':
?>

  <div>
  <script language="JavaScript">
    function swfLoaded<?php echo $question_no; ?>(message) {
      var num = message.substring(5,message.length);
      setUpFlash(num, message, '<?php echo $options[0]['q_media']; ?>', '<?php echo trim($options[0]['correct']); ?>', '');
    }
    write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo ($options[0]['q_media_width'] + 300); ?>" height="<?php echo ($options[0]['q_media_height'] + 2); ?>" align="middle">');
    write_string('<param name="allowScriptAccess" value="always" />');
    write_string('<param name="movie" value="/touchstone/paper/hotspot_answer.swf" />');
    write_string('<param name="quality" value="high" />');
    write_string('<param name="bgcolor" value="<?php echo $bgcolor; ?>" />');
    write_string('<embed src="/touchstone/paper/hotspot_answer.swf" quality="high" bgcolor="<?php echo $bgcolor; ?>" width="<?php echo ($options[0]['q_media_width'] + 300); ?>" height="<?php echo ($options[0]['q_media_height'] + 2); ?>" swliveconnect="true" id="flash<?php echo $question_no; ?>" name="flash<?php echo $question_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
    write_string('</object>');
  </script>
  </div>
<?php
        $layers = explode('|', $options[0]['correct']);
        echo "<table summary=\"Options for Hotspot question\">\n";
        for ($i=1; $i<=$display_option['marks']; $i++) {
          echo "<tbody><tr><td style=\"padding-right: 12px\">" .  chr($i+64) . ')&nbsp;' . substr($layers[$i-1], 0, strpos($layers[$i-1], '~')) . "</td><td>" . reviewScale('hotspot', $q_id, $question_no, $i, $std_method, $tmp_reviews, substr($tmp_exclude,$i-1,1)) . "</td></tr></tbody>\n";
        }
        echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"u\" />\n";
        $marks = $display_option['marks'];
        echo '</table>';
        break;
      case 'labelling':
        $tmp_first_split = explode(';', $options[0]['correct']);
        $tmp_second_split = explode('$', $tmp_first_split[8]);
        $tmp_height = $options[0]['q_media_height'];
        if ($tmp_height < ($marks * 55)) $tmp_height = ($marks * 55);
        
        echo '<div align="center">';
        echo '<script language="JavaScript">';
        $options[0]['correct'] = str_replace('"','&#034;',stripslashes($options[0]['correct']));
        $options[0]['correct'] = str_replace("'",'&#039;',stripslashes($options[0]['correct']));
?> 
      function swfLoaded<?php echo $question_no; ?>(message) {
        var num = message.substring(5,message.length);
        setUpFlash(num, message, '<?php echo $options[0]['q_media']; ?>', '<?php echo trim($options[0]['correct']); ?>', '','');
      }
      write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo ($options[0]['q_media_width'] + 250); ?>" height="<?php echo $tmp_height; ?>" align="middle">');
      write_string('<param name="allowScriptAccess" value="always" />');
      write_string('<param name="movie" value="/touchstone/paper/label_answer.swf" />');
      write_string('<param name="quality" value="high" />');
      write_string('<param name="bgcolor" value="<?php echo $bgcolor; ?>" />');
      write_string('<embed src="/touchstone/paper/label_answer.swf" quality="high" bgcolor="<?php echo $bgcolor; ?>" width="<?php echo ($options[0]['q_media_width'] + 250); ?>" height="<?php echo $tmp_height; ?>" swliveconnect="true" id="flash<?php echo $question_no; ?>" name="flash<?php echo $question_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
      write_string('</object>');
<?php
        echo "</script>\n";
        echo "</div>\n";
        
        echo "<blockquote>\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
        for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
          if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 200) {
            echo "<tr><td>" . chr($part_id + 64) . ".</td><td>" . substr($tmp_second_split[$label_no],0,strpos($tmp_second_split[$label_no],'|')) . "</td><td>" . reviewScale('labelling', $q_id, $question_no, $part_id, $std_method, $tmp_reviews, substr($tmp_exclude,$part_id-1,1)) . "</td></tr>\n";
            $part_id++;
            $marks++;
          }
        }
        echo "</table>\n</blockquote>\n";
        
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
        echo "<div>" .  reviewScale('flash', $q_id, $question_no, '', $std_method, $tmp_reviews, substr($tmp_exclude,0,1)) . "</div>\n";
        echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"0,0\" />\n";
        $marks += $display_option['marks'];
        break;
    }                  // End switch
    $i++;
  }                    // End foreach loop

  if ($options[0]['q_type'] == 'mcq') {
    if ($options[0]['score_method'] == 'vertical' or $options[0]['score_method'] == 'vertical_other') {
      if ($options[0]['score_method'] == 'vertical_other') {
        echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"other\" /></td><td>Other <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "[" . $part_id . "].checked=true\" name=\"q" . $question_no . "_other\" /></td></tr>\n";
      }
      echo '</table></blockquote>';
      echo "<div>" .  reviewScale('mcq', $q_id, $question_no, '', $std_method, $tmp_reviews, substr($tmp_exclude,0,1)) . "</div>\n";
    } elseif ($options[0]['score_method'] == 'dropdown') {
      echo '</select>&nbsp;' .  reviewScale('mcq', $q_id, $question_no, '', $std_method, $tmp_reviews, substr($tmp_exclude,0,1)) . "</div>\n";
    }
  } elseif ($options[0]['q_type'] == 'calculation') {
    $score_array = explode(',',$options[0]['score_method']);
    if ($li_set == 1) {
      echo '<tr><td></td><td>';
    } else {
      echo '<tr><td class="question_no">' . (($question_no + $question_offset) - 1) . '.&nbsp;</td><td>';
    }
    //$new_leadin = "echo \"<p>$current_leadin</p>\"";
    //eval("$new_leadin;");
    $search = array('$A','$B','$C','$D','$E','$F','$G','$H');
    $replace = array($A,$B,$C,$D,$E,$F,$G,$H);
    echo "<p>" . str_ireplace($search,$replace,$current_leadin) . "</p>\n";
    
    echo "<p><input type=\"text\" style=\"text-align:right\" name=\"q" . $question_no . "\" size=\"10\" />" . $score_array[2] .  "</p>\n";
    echo "<div>" . reviewScale('calculation', $q_id, $question_no, '', $std_method, $tmp_reviews, substr($tmp_exclude,0,1)) . "</div>\n";
    $marks = 1;
  } elseif ($options[0]['q_type'] == 'dichotomous' or $options[0]['q_type'] == 'mrq' or $options[0]['q_type'] == 'rank') {
    if ($options[0]['score_method'] == 'other') {
      $part_id++;
      echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td>";
      echo "<td>Other <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "_" . $part_id . ".checked=true\" name=\"q" . $question_no . "_other\" value=\"\" /></td></tr>\n";
    }
    echo '</table>';
    if ($options[0]['q_type'] == 'mrq') {
      echo "</blockquote>\n";
      if ($options[0]['score_method'] == 'AllItemsCorrect') {
        $part_id++;
        echo "<div>" . reviewScale('mrq', $q_id, $question_no, 1, $std_method, $tmp_reviews, substr($tmp_exclude,0,1)) . "</div>\n";
      }
    } elseif ($options[0]['q_type'] == 'rank') {
      echo "<input type=\"hidden\" id=\"rank" . $question_no . "_options\" name=\"rank" . $question_no . "_options\" value=\"$part_id\" />\n";
      if ($options[0]['score_method'] == 'BonusMark') {
        echo "<br />\n<div>All items correct: " . reviewScale('rank', $q_id, $question_no, ($part_id+1), $std_method, $tmp_reviews, substr($tmp_exclude,0,1)) . "</div>\n";
        echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"". ($marks-1) . "\" />\n";
      } elseif ($options[0]['score_method'] == 'AllItemsCorrect') {
        echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"$part_id\" />\n";
      } else {
        echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"$marks\" />\n";
      }
    }
  } elseif ($options[0]['q_type'] == 'extmatch') {
    $matching_answers = explode('|', $options[0]['correct']);

    if ($matching_media[0] != '') {
      echo '<p align="center">' . display_media($matching_media[0],$matching_media_width[0],$matching_media_height[0]) . '</p>';
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
    echo '<ol type="i">';
    $rating_part = 0;
    for ($part_id=1; $part_id<=$scenario_no; $part_id++) {
      $answer_no = substr_count($matching_answers[$part_id-1],'$') + 1;
      $marks += substr_count($matching_answers[$part_id - 1],'$') + 1;

      echo '<li>';
      if ($matching_scenarios[$part_id - 1] != '') {
        echo "<div";
        if (substr($tmp_exclude,$part_id-1,1) == '1') echo ' style="color:red; text-decoration:line-through"';
        echo ">" . $matching_scenarios[$part_id - 1] . "</div>";
      }
      if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') {
        echo "<p>" . display_media($matching_media[$part_id],$matching_media_width[$part_id],$matching_media_height[$part_id]) . "</p>\n";
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
        echo '&nbsp;' .  reviewScale('extmatch', $q_id, $question_no, $rating_part+1, $std_method, $tmp_reviews, substr($tmp_exclude,$rating_part,1)) . '</div></li>';
        $rating_part++;
      } else {
        echo '<div class="no_marks">(Hold down &lt;CTRL&gt; key, then click mouse to toggle options on/off)</div>';
        echo '</div><br /></li>';
        for ($i=1; $i<=$answer_count; $i++) {
          echo '<div>' .  reviewScale('extmatch', $q_id, $question_no, $rating_part+1, $std_method, $tmp_reviews, substr($tmp_exclude,$rating_part,1)) . '</div>';
          $rating_part++;
        }
      }
      echo '<br />';
    }
    echo '</ol><input type="hidden" name="multimatching' . $question_no . '_options" value="' . count($matching_options) . '" /><input type="hidden" name="multimatching' . $question_no . '_correct_no" value="' . $marks . '" />';
  } elseif ($options[0]['q_type'] == 'matrix') {
    $part_id = 1;
    if ($matching_media[0] != '') {
      echo "<p align=\"center\">" . display_media($matching_media[0],$matching_media_width[0],$matching_media_height[0]) . "</p>\n";
    }
    echo '<table cellpadding="2" cellspacing="0" border="1">';
    echo "<tr>\n<td colspan=\"3\">&nbsp;</td>";
    foreach ($matching_options as $single_option) {
      echo '<td>' . $single_option . '</td>';
    }
    echo "<tr>\n";
    $numerals = array('0','i','ii','iii','iv','v','vi','vii','viii','ix','x');
    foreach ($matching_scenarios as $single_scenario) {
      if (trim($single_scenario) != '') {
        echo '<tr><td>' .  reviewScale('matrix',$q_id, $question_no, $part_id, $std_method, $tmp_reviews, substr($tmp_exclude,$part_id-1,1)) . '</td><td align="right">' . $numerals[$part_id] . '.</td><td';
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
        echo "</tr>\n";
        $part_id++;
      }
    }
    echo '</table>';
    $marks = $part_id - 1;
  }

  // Display possible marks for question (if not Survey)
  if ($options[0]['q_type'] != 'hotspot' and $options[0]['q_type'] != 'likert' and $options[0]['q_type'] != 'mcq' and $options[0]['q_type'] != 'mrq' and $options[0]['q_type'] != 'calculation') echo '</blockquote>';
  if ($q_type < 3) {
    if ($marks == 1) {
      echo "<div style=\"color: #808080; font-size:80%\">(1 mark)</div>\n<br /></td></tr>\n";
    } elseif ($marks > 1) {
      if ($options[0]['q_type'] == 'dichotomous' and ($options[0]['score_method'] == 'TF_Negative' or $options[0]['score_method'] == 'TF_NegativeAbstain' or $options[0]['score_method'] == 'YN_Negative' or $options[0]['score_method'] == 'YN_NegativeAbstain')) {
        echo "<div class=\"no_marks\">($marks marks, negative marking)</div>\n<br /></td></tr>\n";
      } elseif ($options[0]['q_type'] == 'mrq') {
        if ($options[0]['score_method'] == 'SelectedPositive') {
          echo "<div class=\"no_marks\">($marks marks)</div>\n<br /></td></tr>\n";
        } else {
          echo "<div class=\"no_marks\">($marks marks, all options items marked with negative marking)</div>\n<br /></td></tr>\n";
        }
      } elseif ($options[0]['q_type'] == 'rank') {
        if ($options[0]['score_method'] == 'BonusMark') {
          echo "<div class=\"no_marks\">(" . ($marks - 1) . "&nbsp;marks for correct options, plus 1 bonus mark for fully correct order)</div>\n";
        } elseif ($display_option['score_method'] == 'AllItemsCorrect') {
          echo "<div class=\"no_marks\">($marks&nbsp;mark)</div>\n<br /></td></tr>\n";
        } elseif ($options[0]['score_method'] == 'OrderNeighbours') {
          echo "<div class=\"no_marks\">($marks marks, half marks for neighbours)</div>\n<br /></td></tr>\n";
        } else {
          echo "<div class=\"no_marks\">($marks marks, exact order)</div>\n<br /></td></tr>\n";
        }
      } else {
        echo "<div class=\"no_marks\">($marks marks)</div>\n<br /></td></tr>\n";
      }
    }
    $total_marks += $marks;
  }
}
?>
