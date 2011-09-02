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


// TODO: fix use of 'rel' on textarea

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

$roman = array('i','ii','iii','iv','v','vi','vii','viii','ix','x');

// Media, stem and feedback for this question type are compound fields
$all_media = $question->get_all_media();
$stems = $question->get_all_stems();
$all_feedback = $question->get_all_correct_fbacks();
$current_media = array('filename' => $all_media['filenames'][0], 'width' => $all_media['widths'][0], 'height' => $all_media['heights'][0]);

// Work out how many 'questions' to show
$visible_questions = 0;
for ($i = 0; $i < $question->max_stems; $i++) {
  if ((isset($stems[$i]) and $stems[$i] != '') or $all_media['filenames'][$i] != '') $visible_questions = $i + 1;
}
?>
<script type="text/javascript">
//<![CDATA[
<?php // Bit of a hack to get the options section to fit in ?>
$(function () {
  $('#question-holder').addClass('wide');
});
//]]>
</script>

        <div id="extmatch-options">
          <h2>Available Options</h2>
          <dl id="extended-option-list">
<?php
  
  for ($index = 0; $index < $question->max_options; $index++) {
    $mandatory = ($index < 4) ? '<span class="mandatory">*</span> ' : '';
    if ($index < $num_options) {
      $option_text = $options[$option_ids[$index]]->get_text();
      $option_id = $option_ids[$index];
    } else {
      $option_text = '';
      $option_id = -1;
    }
    if ($option_text != '') $option_texts[$index + 1] = chr($index + 65) . '. ' . $option_text;
?>
            <dt><?php echo $mandatory . ' ' . chr($index + 65) ?>.</dt>
            <dd>
              <textarea rows="1" id="option_text<?php echo $index + 1 ?>" name="option_text<?php echo $index + 1 ?>" rel="<?php echo $index + 1 ?>" class="extmatch-option form-small"><?php echo $option_text ?></textarea>
              <input name="optionid<?php echo $index + 1 ?>" value="<?php echo $option_id ?>" type="hidden" />
            </dd>
<?php
  }
?>
          </dl>
        </div>

				<table id="q-details" class="form" summary="Edit question details">
					<tbody>
<?php
require_once 'detail_parts/details_theme_notes.php';
require_once 'detail_parts/details_media.php';
require_once 'detail_parts/details_leadin.php';
?>
            <tr>
              <th><label for="option_order">Option Order</label></th>
              <td>
                <select id="option_order" name="option_order">
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

for ($index = 1; $index <= $question->max_stems; $index++):
  $hidden = ($index > 2 and $index > $visible_questions) ? ' hide' : '';
?>
        <div class="option<?php echo $hidden ?>">
          <div class="form">
            <h2>Scenario <?php echo $roman[$index - 1] ?></h2>
          </div>
          
          <table id="q-options" class="form" summary="Edit scenario <?php echo $roman[$index - 1] ?>">
<?php
  include 'options/opt_extmatch.php';
?>
          </table>
        </div>
<?php
endfor;
?>

<?php
if($question->get_locked() == '') {
?>
        <table id="q-options" class="form" summary="Add more options">
          <tbody id="add-option-holder">
            <tr>
              <th>&nbsp;</th>
              <td colspan="2">
                <input id="next-option" value="Add More Options..." type="button" />
              </td>
            </tr>
          </tbody>
        </table>
<?php
}
?>          

        
