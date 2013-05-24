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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

$options = $question->options;
$option_ids = array_keys($options);
$num_options = count($options);
if ($num_options > 0) {
  $first = reset($options);
  $correct_answers = $first->get_all_corrects();
} else {
  $correct_answers = array();
}
$option_texts = array();
$disabled = ($dis_class != '') ? ' disabled="disabled"' : '';

// Stem for this question type is a compound field
$stems = $question->get_all_stems();
?>
				<table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
					<tbody>
<?php
require_once 'detail_parts/details_theme_notes.php';
require_once 'detail_parts/details_media.php';
require_once 'detail_parts/details_leadin.php';
?>
            <tr>
              <th><label for="option_order"><?php echo $string['optionorder'] ?></label></th>
              <td>
                <select id="option_order" name="option_order"<?php echo $disabled ?>>
<?php 
echo ViewHelper::render_options($question->get_option_orders(), $question->get_option_order(), 3);
?>
                </select>
              </td>
            </tr>
					</tbody>
				</table>
        
<?php
require_once 'detail_parts/details_marking.php';
require_once 'detail_parts/details_general_feedback.php';
?>
        <div class="form">
          <h2 class="midblue_header"><?php echo $string['options'] ?></h2>
        </div>

        <table id="q-options" class="form align-centre bordered auto-sized" summary="<?php echo $string['qeditsummary'] ?>">
          <tr>
            <th>&nbsp;</th>
<?php
for ($index = 1; $index <= $question->max_options; $index++):
  if ($index <= $num_options) {
    $option_text = $options[$option_ids[$index - 1]]->get_text();
    $option_id = $option_ids[$index - 1];
  } else {
    $option_text = '';
    $option_id = -1;
  }
?>
            <td>
              <label for="option_text<?php echo $index ?>" class="hide"><?php echo $string['option'];?> <?php echo $index ?></label>
              <input type="text" id="option_text<?php echo $index ?>" name="option_text<?php echo $index ?>" value="<?php echo $option_text ?>" title="<?php echo $option_text ?>" class="form-minute<?php echo $dis_class ?>"<?php echo $dis_readonly ?> />
              <input name="optionid<?php echo $index ?>" value="<?php echo $option_id ?>" type="hidden" />
            </td>
<?php
endfor;
?>
          </tr>
<?php
for ($index = 1; $index <= $question->max_stems; $index++):
?>
          
<?php
  include 'options/opt_matrix.php';
?>
<?php
endfor;
?>
        </table>
