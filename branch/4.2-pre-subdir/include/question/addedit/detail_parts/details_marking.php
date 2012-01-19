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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

$marks_positive = range(1, 20);
$marks_negative = array(0, -0.5, -1, -2, -3, -4, -5, -6, -7, -8, -9, -10);
$marks_partial = array_merge(range(0, 1, 0.1), range(2, 5));
if (count($question->options) > 0) {
  $option = reset($question->options);
  $mark_correct = $option->get_marks_correct();
  $mark_incorrect = $option->get_marks_incorrect();
  $mark_partial = number_format($option->get_marks_partial(), 1);
} else {
  $mark_correct = 1;
  $mark_incorrect = 0;
  $mark_partial = 0;
}
$allow_neg = $question->allow_negative_marks($module);
$allow_change_method = ($question->allow_change_marking_method()) ? '' : ' disabled="disabled"';
?>
        <table id="q-marking" class="form" summary="<?php echo $string['qeditsummary'] ?>">
          <tbody>
            <tr>
              <th><label for="score_method" class="heavy"><?php echo $string['markingmethod'] ?></label></th>
              <td>
                
                <select id="score_method" name="score_method" class="spaced-right-large"<?php echo $allow_change_method ?>>
<?php
echo ViewHelper::render_options($question->get_score_methods(), $question->get_score_method('int'), 3, true);
?>
                </select>
                <label for="option_marks_correct" class="heavy"><?php echo $string['markscorrect']?></label>
                <select id="option_marks_correct" name="option_marks_correct" class="spaced-right-large">
<?php
echo ViewHelper::render_options($marks_positive, $mark_correct, 3);
?>
                </select>
<?php
if ($question->allow_partial_marks()):
  $show_partial = ($question->get_score_method() == $string['allowpartial']) ? '' : ' hide';
?>
                <span class="marks-partial<?php echo $show_partial ?>">
                  <label for="option_marks_partial" class="heavy"><?php echo $string['markspartial']?></label>
                  <select id="option_marks_partial" name="option_marks_partial" class="spaced-right-large">
<?php
echo ViewHelper::render_options($marks_partial, $mark_partial, 3);
?>
                  </select>
                </span>
<?php
endif;
if ($allow_neg):
?>
                <label for="option_marks_incorrect" class="heavy"><?php echo $string['marksincorrect']?></label>
                <select id="option_marks_incorrect" name="option_marks_incorrect">
<?php
echo ViewHelper::render_options($marks_negative, $mark_incorrect, 3);
?>
                </select>
<?php
endif;
?>
              </td>
            </tr>
          </tbody>
        </table>
