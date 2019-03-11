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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

// TODO: hide
$mandatory = ($index <= 2) ? '<span class="mandatory">*</span> ' : '';
if ($index %2 == 0) {
  $alt_c = ' class="alt"';
} else {
  $alt = $alt_c = '';
}
$stem = (isset($stems[$index - 1])) ? $stems[$index - 1] : '';
$feedback = (isset($all_feedback[$index - 1])) ? $all_feedback[$index - 1] : '';
$selected = (isset($correct_answers[$index - 1])) ? $correct_answers[$index - 1] : '';
$select_size = (count($option_texts) < 10) ? count($option_texts) : 10;
$locked = ($dis_class != '');
?>
            <tr<?php echo $alt_c ?>>
              <th><?php echo $mandatory ?><label for="edit_extmatch<?php echo $index ?>"><?php echo $string['stem'] ?></label></th>
              <td>
<?php
  echo wysywig_or_non_editable($dis_class, 'edit_extmatch' . $index, 'question_stem' . strval($index), $stem);
?>
              </td>
            </tr>
<?php

if (isset($all_media['filenames'][$index]) and $all_media['filenames'][$index] != '') {
  $configObj = Config::get_instance();
  $questiondata = new plugins\questions\extmatch\renderdata();
  $render = new render($configObj);
  $questiondata->set_media($all_media['filenames'][$index], $all_media['widths'][$index], $all_media['heights'][$index], '', false, $index, $locked);
?>
              <tr<?php echo $alt_c ?>>
                <th><?php echo $string['current'] . ' ' . $string['media'] ?></th>
                <td>
                  <?php
                  $render->render($questiondata, $string, 'paper/media.html');
                  ?>
                </td>
              </tr>
<?php
}
?>
            <tr<?php echo $alt_c ?>>
              <th><label for="question_media<?php echo $index ?>"><?php echo $string['change'] . ' ' . $string['media'] ?></label></th>
              <td>
                <input id="question_media<?php echo $index ?>" name="question_media<?php echo $index ?>" type="file" size="50"<?php echo $disabled ?> />
              </td>
            </tr>
            <tr<?php echo $alt_c ?>>
              <th><label for="option_correct_fback<?php echo $index ?>"><?php echo $string['feedback'] ?></label></th>
              <td>
                <textarea cols="85" rows="2" id="question_correct_fback<?php echo $index ?>" name="question_correct_fback<?php echo $index ?>" class="form-med-large<?php echo $dis_class ?>"<?php echo $dis_readonly ?>><?php echo $feedback ?></textarea>
              </td>
            </tr>
            <tr<?php echo $alt_c ?>>
              <th><label for="option_correct<?php echo $index ?>"><?php echo $string['correctanswers'] ?></label><br /><span class="note"><?php echo $string['correctanswersmsg'] ?></span></th>
              <td>
                <select id="option_correct<?php echo $index ?>" name="option_correct<?php echo $index ?>[]" multiple="multiple" size="<?php echo $select_size ?>" class="extmatch-correct">
<?php
echo ViewHelper::render_options($option_texts, $selected, 3);
?>
                </select>
              </td>
            </tr>
            