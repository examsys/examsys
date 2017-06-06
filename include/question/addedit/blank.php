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

$num_options = count($question->options);
if($num_options > 0) {
  $option = reset($question->options);
  $option_id = $option->id;
  $scanario_text = $option->get_text();
  if ($question->get_display_method() == 'textboxes') {
    $inst1_hidden = ' hide';
    $inst2_hidden = '';
  } else {
    $inst1_hidden = '';
    $inst2_hidden = ' hide';
  }
} else {
  $option_id = -1;
  $scanario_text = '';
  $inst1_hidden = '';
  $inst2_hidden = ' hide';
}
$scenario_message = <<< MESSAGE
<span class="note blank-instructions{$inst1_hidden}" id="instructions1">{$string['blankinstructionsddl']}</span>
<span class="note blank-instructions{$inst2_hidden}" id="instructions2">{$string['blankinstructionstextboxes']}</span>
MESSAGE;
$scenario_height = 250;

?>
				<table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
					<tbody>
<?php
require_once 'detail_parts/details_theme_notes.php';
require_once 'detail_parts/details_media.php';
require_once 'detail_parts/details_leadin.php';
$disp_method_class = 'blank-display';
require_once 'detail_parts/details_presentation.php';
?>
            <tr>
              <th>&nbsp;</th>
              <td><?php echo $scenario_message ?></td>
            </tr>
            <tr>
              <th class="align-top"><span class="mandatory">*</span> <label for="option_text"><?php echo $string['question'] ?></label></th>
              <td>
<?php
  echo wysywig_or_non_editable($dis_class, 'edit_common1', 'option_text', $scanario_text, 'mceEditorBasic', 695, 250);
?>
              </td>
            </tr>
					</tbody>
				</table>
        <input name="optionid1" value="<?php echo $option_id ?>" type="hidden" />

<?php
require_once 'detail_parts/details_marking.php';
require_once 'detail_parts/details_general_feedback.php';
?>