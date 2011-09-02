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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

$num_options = count($question->options);
$columns = range(10, 120, 10);
$rows = range(1, 15);
$editors = array('plain' => 'Plain Text', 'WYSIWYG' => 'WYSIWYG');

if(count($question->options) > 0) {
  $option = reset($question->options);
  $marks_correct = $option->get_marks_correct();
  $terms = $option->get_correct();
  $editor = $option->get_text();
  $option_id = $option->id;
} else {
  $marks_correct = 1;
  $terms = '';
  $editor = 'plain';
  $option_id = -1;
}
?>
				<table id="q-details" class="form" summary="Edit question details">
					<tbody>
<?php require_once 'details_common.php' ?>
            <tr>
              <th><label for="columns">Presentation</label></th>
              <td>
                <select id="columns" name="columns" class="spaced-right">
<?php
echo ViewHelper::render_options($columns, $question->get_columns(), 3, false, '', '', ' cols');
?>
                </select>
                <label for="rows" class="spaced-right"><strong>x</strong></label>
                <select id="rows" name="rows" class="spaced-right-large">
<?php 
echo ViewHelper::render_options($rows, $question->get_rows(), 3, false, '', '', ' rows');
?>
                </select>
                <label for="editor"><strong>Editor</strong></label>
                <select id="option_text" name="option_text">
<?php 
echo ViewHelper::render_options($editors, $editor, 3);
?>
                </select>
              </td>
            </tr>
					</tbody>
				</table>

        <div class="form">
          <h2>Assessment Data</h2>
        </div>
        
        <table id="q-options" class="form" summary="Edit question assessment data">
          <tbody>
            <tr>
              <th class="spaced-top"><label for="option_marks_correct">Marks</label></th>
              <td class="spaced-top">
                <select id="option_marks_correct" name="option_marks_correct">
                  <option value="" />
<?php
echo ViewHelper::render_options(range(1, 20), $marks_correct, 3);
?>
                </select>
                <input name="optionid1" value="<?php echo $option_id ?>" type="hidden" />
              </td>
            </tr>            
            <tr>
              <th><label for="terms">Terms</label><br /><span class="note">(separate with semicolons)</span></th>
              <td>
                <textarea id="option_correct" name="option_correct" cols="100" rows="3" class="form-large"><?php echo $terms ?></textarea>
              </td>
            </tr>
          </tbody>
        </table>
<?php
$label_correct = 'Feedback<br /><span class="note">(model answer for assessments)</span>';
$feedback_rows = 4;
require_once 'detail_parts/details_general_feedback.php';
?>