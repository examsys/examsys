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
$decimals = array('', 0, 1, 2, 3, 4);
$increments = array('', 0.0001, 0.001, 0.02, 0.01, 0.5, 0.2, 0.1, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20, 25, 50, 100, 1000);
$variables = $question->get_variables();
if (count($question->options) > 0) {
  $first = reset($question->options);
  $formula = $first->get_correct();
  $marks = $first->get_marks_correct();
} else {
  $formula = '';
  $marks = 1;
}
?>
				<table id="q-details" class="form" summary="Edit question details">
					<tbody>
<?php require_once 'details_common.php' ?>
					</tbody>
				</table>

<?php
require_once 'detail_parts/details_marking.php';
require_once 'detail_parts/details_general_feedback.php';
?>
        
        <div class="form">
          <h2>Variables</h2>
        </div>
        
        <table id="q-options" class="form" summary="Edit question variables">
          <thead>
            <tr>
              <th>&nbsp;</th>
              <th class="align-left">Min</th>
              <th class="align-left">Max</th>
              <th class="align-left">Decimals</th>
              <th class="align-left">Increment</th>
            </tr>
          </thead>
<?php
// TODO: linking options
$index = 1;
foreach ($question->options as $o_id => $option) {
  $option->set_variable($variables[$index-1]);
  include 'options/opt_calculation.php';
  $index++;
}

for ($index = $num_options + 1; $index <= count($variables); $index++) {
  $option = Option::option_factory($mysqli, $userID, $question, $index);
  $option->set_variable($variables[$index-1]);
  include 'options/opt_calculation.php';
}

// TODO: link to help
?>          
        </table>

        <div class="form">
          <h2>Answer</h2>
        </div>
        
        <table id="q-options" class="form" summary="Edit question variables">
          <tbody>
            <tr>
              <th>
                <label for="option_correct"><span class="mandatory">*</span>Formula</label><br />
                <span class="note"><a href="#" class="help-link" rel="68"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Online Help" border="0" /></a>&nbsp;<a href="#" class="help-link" rel="68">supported functions</a></span>
              </th>
              <td colspan="3">
                <textarea id="option_correct" name="option_correct" cols="100" rows="3" class="form-large"><?php echo $formula ?></textarea>
              </td>
            </tr>            
            <tr>
              <th class="spaced-top"><label for="units">Units</label></th>
              <td class="spaced-top"><input type="text" id="units" name="units" value="<?php echo $question->get_units() ?>" /></td>
              <td class="spaced-top">
                <label for="answer_decimals" class="spaced-right"><strong>Decimals</strong></label>
                <select id="answer_decimals" name="answer_decimals">
<?php
echo ViewHelper::render_options($decimals, $question->get_answer_decimals(), 3);
?>
                </select>
              </td>
              <td class="spaced-top"><label for="tolerance" class="spaced-right"><strong>Tolerance</strong></label><input type="text" id="tolerance" name="tolerance" value="<?php echo $question->get_tolerance() ?>" /></td>
            </tr>
            <tr>
              <th class="spaced-top"><label for="marks"><strong>Marks</strong></label></th>
              <td class="spaced-top">
                <select id="option_marks" name="option_marks">
<?php
echo ViewHelper::render_options(range(1, 20), $marks, 3);
?>
                </select>
              </td>
            </tr>            
          </tbody>
        </table>

