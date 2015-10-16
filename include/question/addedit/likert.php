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

?>
<script>
$(function() {
  $('#scale_type').change(function() {
    var state_name = 'likert_format';
    var content = $('#scale_type').val();
    updateState(state_name, content);
  });
});
</script>
<?php

$scales = $question->get_scale_types();
if ($mode == 'Add' and isset($state['likert_format'])) {
  $scale_value = $state['likert_format'];
} else {
  $scale_value = $question->get_scale_type();
}
$na_checked = ($question->get_not_applicable() == 'true') ? ' checked="checked"' : '';
if (count($question->options) > 0) {
  $option = reset($question->options);
  $option_id = $option->id;
} else {
  $option_id = -1;
}

?>
				<table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
					<tbody>
<?php require_once 'details_common.php' ?>
            <tr>
              <th><label for="scale_type"><?php echo $string['scale'] ?></label></th>
              <td>
                <select id="scale_type" name="scale_type">
<?php 
$scale_found = false;
foreach ($scales as $scale_group => $scale):
?>
                  <optgroup label="<?php echo $scale_group ?>">
<?php 
  foreach ($scale as $value => $text):
    if ($value == $scale_value) {
      $sel = ' selected="selected"';
      $scale_found = true;
    } else {
      $sel = '';
    }

    // Stop the 'Fail, Borderline, Pass' scale being selected by new questions. It is currently borked.
    if ($value == 'Fail|Borderline|Pass' or $value == 'Neuspěl|Na hraně|Uspěl' or $value == 'Nie zdany|Na granicy|Zdany') {
      $sel .= ' disabled="disabled"';
    }
?>
                    <option value="<?php echo $value ?>"<?php echo $sel ?>><?php echo $text ?></option>
<?php
  endforeach;
?>
                  </optgroup>
<?php
endforeach;

if (!$scale_found) {
  $sel = ' selected="selected"';
  $show_custom = '';
} else {
  $sel = '';
  $show_custom = ' class="hide"';
}
?>
                  <optgroup label="<?php echo $string['custom'] ?>">
                    <option value="custom"<?php echo $sel ?>><?php echo $string['custom'] ?>&hellip;</option>
                  </optgroup>
                </select>
              </td>
            </tr>
            <tr>
              <th><?php echo $string['nacolumn'] ?></th>
              <td>
                <input type="checkbox" id="not_applicable" name="not_applicable"<?php echo $na_checked ?> /> <label for="not_applicable"><?php echo $string['includena'] ?></label>
              </td>
            </tr>
            <tr>
              <th>&nbsp;</th>
              <td>
                <dl id="extended-option-list"<?php echo $show_custom ?>>
<?php
$custom_scale = ($mode == 'Add' and isset($state['likert_format'])) ? explode('|', $state['likert_format']) : $question->get_all_custom_scales();
for ($i = 1; $i <= $question->max_stems; $i++):
  $val = (isset($custom_scale[$i - 1])) ? $custom_scale[$i - 1] : '';
?>
                  <dt><label for="question_custom_scale<?php echo $i ?>"><?php echo $i ?>.</label></dt>
                  <dd>
                    <input type="text" id="question_custom_scale<?php echo $i ?>" name="question_custom_scale<?php echo $i ?>" value="<?php echo $val ?>" class="form-small">
                  </dd>

<?php
endfor;
?>
                </dl>
                &nbsp;
              </td>
            </tr>
					</tbody>
				</table>
        <input name="optionid1" value="<?php echo $option_id ?>" type="hidden" />

<?php require_once 'detail_parts/details_general_feedback.php' ?>
