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
?>
				<table id="q-details" class="form" summary="Edit question details">
					<tbody>
<?php require_once 'details_common.php' ?>
            <tr>
              <th><label for="score_method">Scoring Method</label></th>
              <td>
                <select id="score_method" name="score_method">
<?php
foreach ($question->get_score_methods() as $val => $display) {
  $selected = ($question->get_score_method() == $val) ? ' selected="selected"' : '';
?>
                  <option value="<?php echo $val ?>"<?php echo $selected ?>><?php echo $display ?></option>
<?php
}
?>
                </select>
              </td>
            </tr>
            <tr>
              <th>Presentation</th>
              <td>
<?php
$checked = ($question->get_score_method() == 'other') ? ' checked="checked"' : '';
?>
                <input type="checkbox" id="other" name="other" value="1"<?php echo $checked ?> /> <label for="other">include 'other' textbox <span class="note">(use with surveys)</span></label>
              </td>
            </tr>
            <tr>
              <th><label for="option_order">Option Order</label></th>
              <td>
                <?php echo option_order($question->get_option_order()) ?>
              </td>
            </tr>
					</tbody>
				</table>

<?php require_once 'details_general_feedback.php' ?>
        
        <div class="form">
          <h2>Options</h2>
        </div>
        
        <table id="q-options" class="form" summary="Edit question options">
          <tbody>
            <tr>
              <th colspan="2">&nbsp;</th>
              <th class="small"><strong>Answer</strong></th>
            </tr>
          </tbody>
<?php
$index = 1;
foreach ($question->options as $o_id => $option) {
  include 'options/mrq.php';
  $index++;
}

for ($index = $num_options + 1; $index <= $question->max_options; $index++) {
  $option = new Option($mysqli, $userID);
  include 'options/mrq.php';
}

if($question->get_locked() == '') {
?>
          <tbody id="add-option-holder">
            <tr>
              <th>&nbsp;</th>
              <td colspan="3">
                <input id="next-option" value="Add More Options..." type="button" />
              </td>
            </tr>
          </tbody>
<?php
}
?>          
        </table>
