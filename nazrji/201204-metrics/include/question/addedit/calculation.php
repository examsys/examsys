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

$num_options = count($question->options);
$decimals = array('', 0, 1, 2, 3, 4, 5, 6, 7, 8);
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
				<table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
					<tbody>
<?php require_once 'details_common.php' ?>
					</tbody>
				</table>

<?php
require_once 'detail_parts/details_marking.php';
require_once 'detail_parts/details_general_feedback.php';
?>
        
        <div class="form">
          <h2><?php echo $string['variables'] ?></h2>
        </div>
        
        <table id="q-options" class="form" summary="Edit question variables">
          <thead>
            <tr>
              <th>&nbsp;</th>
              <th class="align-left"><?php echo $string['min'] ?></th>
              <th class="align-left"><?php echo $string['max'] ?></th>
              <th class="align-left"><?php echo $string['decimals'] ?></th>
              <th class="align-left"><?php echo $string['increment'] ?></th>
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
  $option = Option::option_factory($mysqli, $userID, $question, $index, $string);
  $option->set_variable($variables[$index-1]);
  include 'options/opt_calculation.php';
}

// TODO: link to help
?>          
        </table>

        <div class="form">
          <h2><?php echo $string['answer'] ?></h2>
        </div>
        
        <table id="q-options" class="form" summary="Edit question variables">
          <tbody>
            <tr>
              <th>
                <label for="option_correct"><span class="mandatory">*</span><?php echo $string['formula'] ?></label><br />
                <span class="note"><a href="#" class="help-link" rel="68"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['onlinehelp'] ?>" border="0" /></a>&nbsp;<a href="#" class="help-link" rel="68"><?php echo $string['suppfunctions'] ?></a></span>
              </th>
              <td colspan="2">
                <textarea id="option_correct" name="option_correct" cols="100" rows="3" class="form-large"><?php echo $formula ?></textarea>
              </td>
            </tr>            
            <tr>
              <th class="spaced-top"><label for="units"><?php echo $string['units'] ?></label></th>
              <td class="spaced-top"><input type="text" id="units" name="units" value="<?php echo $question->get_units() ?>" /></td>
              <td class="spaced-top">
                <label for="answer_decimals" class="spaced-right"><strong><?php echo $string['decimals'] ?></strong></label>
                <select id="answer_decimals" name="answer_decimals">
<?php
echo ViewHelper::render_options($decimals, $question->get_answer_decimals(), 3);
?>
                </select>
              </td>
            </tr>
            <tr>
              <th class="spaced-top"><img src="../../artwork/information_icon.gif" width="16" height="16" alt="Information" title="<?php echo $string['percenttolerance'] ?>" class="tiptop" /> <?php echo $string['tolerance'] ?></th>
              <td class="spaced-top"><label for="tolerance_full" class="spaced-right"><strong><?php echo $string['tolerance_full'] ?></strong></label><input type="text" id="tolerance_full" name="tolerance_full" value="<?php echo $question->get_tolerance_full() ?>" /></td>
              <td class="spaced-top"><span class="marks-partial<?php echo $show_partial ?>"><label for="tolerance_partial" class="spaced-right"><strong><?php echo $string['tolerance_partial'] ?></strong></label><input type="text" id="tolerance_partial" name="tolerance_partial" value="<?php echo $question->get_tolerance_partial() ?>" /></span></td>
            </tr>
          </tbody>
        </table>

