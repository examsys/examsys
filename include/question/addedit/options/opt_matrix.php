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

// TODO: hide
$alt_c = ($index %2 == 1) ? ' class="alt"' : '';
$stem = (isset($stems[$index - 1])) ? $stems[$index - 1] : '';
?>
          <tr<?php echo $alt_c ?>>
            <th class="separated">
              <label for="question_stem<?php echo $index ?>" class="hide">Stem <?php echo $index ?></label>
              <input type="text" id="question_stem<?php echo $index ?>" name="question_stem<?php echo $index ?>" value="<?php echo $stem ?>" title="<?php echo $stem ?>" class="form-tiny" />
            </th>
<?php
for ($i = 1; $i <= $question->max_options; $i++):
  $option_text = ($i <= $num_options) ? $options[$option_ids[$i - 1]]->get_text() : '';
  $selected = ($option_text != '' and $stem != '' and isset($correct_answers[$index - 1]) and $correct_answers[$index - 1] == $i) ? ' checked="checked"' : '';
  ?>
            <td class="separated">
              <label for="option_correct<?php echo $index . '_' . $i ?>" class="hide">Answer <?php echo $index . '.' . $i ?></label>
              <input type="radio" id="option_correct<?php echo $index . '_' . $i ?>" name="option_correct<?php echo $index ?>" value="<?php echo $i ?>"<?php echo $selected ?> />
            </td>
<?php
endfor;
?>
          </tr>
            