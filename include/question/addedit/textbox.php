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

$num_options = count($question->options);
$columns = range(10, 120, 10);
$rows = range(1, 15);
$editors = array('plain' => $string['plaintext'], 'WYSIWYG' => $string['wysiwyg']);
if($configObject->get_setting('core', 'paper_mathjax')) {
  $editors['mathjax'] = $string['mathjax'];
}
$editor = $question->get_editor();
$terms = $question->get_terms();

if (count($question->options) > 0) {
  $option = reset($question->options);
  $marks_correct = $option->get_marks_correct();
  $option_id = $option->id;
} else {
  $marks_correct = 1;
  $option_id = -1;
}
?>
				<table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
					<tbody>
<?php require_once 'details_common.php' ?>
            <tr>
              <th><label for="columns"><?php echo $string['presentation'] ?></label></th>
              <td>
                <select id="columns" name="columns" class="spaced-right"<?php echo $disabled ?>>
<?php
echo ViewHelper::render_options($columns, $question->get_columns(), 3, false, '', '', " {$string['cols']}");
?>
                </select>
                <label for="rows" class="spaced-right heavy">x</label>
                <select id="rows" name="rows" class="spaced-right-large"<?php echo $disabled ?>>
<?php 
echo ViewHelper::render_options($rows, $question->get_rows(), 3, false, '', '', " {$string['rows']}");
?>
                </select>
                <label for="editor" class="heavy"><?php echo $string['editor'] ?></label>
                <select id="editor" name="editor"<?php echo $disabled ?>>
<?php 
echo ViewHelper::render_options($editors, $editor, 3);
?>
                </select>
              </td>
            </tr>
<?php
require_once 'detail_parts/details_marking.php';
?>
					</tbody>
				</table>

        <table id="q-options" class="form" summary="<?php echo $string['reminders'] ?>">
<?php
// For textbox only, option text is editable
$orig_dis_readonly = $dis_readonly;
$dis_class = $dis_readonly = '';

$index = 1;
foreach ($question->options as $o_id => $option) {
  include 'options/opt_textbox.php';
  $index++;
}

for ($index = $num_options + 1; $index <= $question->max_options; $index++) {
  $option = OptionEdit::option_factory($mysqli, $userObject->get_user_ID(), $question, $index, $string);
  include 'options/opt_textbox.php';
}
?>
          <tbody class="add-option-holder">
            <tr>
              <th>&nbsp;</th>
              <td colspan="2">
                <input class="next-option" value="<?php echo $string['addreminders'] ?>" type="button" />
              </td>
            </tr>
          </tbody>
        </table>

        <div class="form">
          <h2 class="midblue_header"><?php echo $string['assessmentdata'] ?></h2>
        </div>
        
        <table id="q-options" class="form" summary="<?php echo $string['qassessmentsummary'] ?>">
          <tbody>
            <tr>
              <th><label for="terms"><?php echo $string['terms'] ?></label><br /><span class="note"><?php echo $string['termsmsg'] ?></span></th>
              <td>
                <textarea id="terms" name="terms" cols="100" rows="3" class="form-large"<?php echo $orig_dis_readonly ?>><?php echo $terms ?></textarea>
              </td>
            </tr>
          </tbody>
        </table>
<?php
$label_correct = $string['feedback'] . '<br /><span class="note">' . $string['feedbackmsg'] . '</span>';
$feedback_rows = 4;
require_once 'detail_parts/details_general_feedback.php';
?>