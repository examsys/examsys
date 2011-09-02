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
<span class="note{$inst1_hidden}" id="instructions1">To create a blank input box place [blank] and [/blank] tags around the options you wish to add.<br />Always put the correct answer as the <strong>first</strong> option, followed by the distractors (all options are randomised automatically).<br />e.g. Tyrannosaurus <span class="blank-tag">[blank]</span>Rex,Roger,Roderick,Ramsey<span class="blank-tag">[/blank]</span> was a large bipedal flesh-eating...</span>
<span class="note{$inst2_hidden}" id="instructions2">To create a blank input box place [blank] and [/blank] tags around the options you wish to add.<br />Within the [blank] tags add the correct answer and any alternatives also deemed to be correct (separate with commas).<br />e.g. What country are we in <span class="blank-tag">[blank]</span>UK,United Kingdom,Britain,Great Britain,GB<span class="blank-tag">[/blank]</span>?</span>
MESSAGE;
$scenario_height = 250;

?>
				<table id="q-details" class="form" summary="Edit question details">
					<tbody>
<?php
require_once 'detail_parts/details_theme_notes.php';
require_once 'detail_parts/details_media.php';
require_once 'detail_parts/details_leadin.php';
?>
            <tr>
              <th><label for="display_method">Display Mode</label></th>
              <td>
                <select id="display_method" name="display_method">
<?php
echo ViewHelper::render_options($question->get_display_methods(), $question->get_display_method(), 3);
?>
                </select>
              </td>
            </tr>
            <tr>
              <th>&nbsp;</th>
              <td><?php echo $scenario_message ?></td>
            </tr>
            <tr>
              <th class="align-top"><label for="option_text">Question</label></th>
              <td>
                <?php echo wysiwyg_editor('edit_common1', 'option_text', $scanario_text, 695, 250); ?>
              </td>
            </tr>
					</tbody>
				</table>
        <input name="optionid1" value="<?php echo $option_id ?>" type="hidden" />

<?php
require_once 'detail_parts/details_marking.php';
require_once 'detail_parts/details_general_feedback.php';
?>