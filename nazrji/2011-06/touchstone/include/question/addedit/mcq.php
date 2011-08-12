				<table id="q-details" class="form" summary="Edit question details">
					<tbody>
<?php require_once 'details_common.php' ?>
            <tr>
              <th><label for="score_method">Presentation</label></th>
              <td>
                <select id="score_method" name="score_method">
<?php
$score_methods = array('vertical' => 'Vertical Option Button', 'vertical_other' => 'Vertical Option Buttons (with \'other\' textbox)', 'horizontal' => 'Horizontal Option Button', 'dropdown' => 'Dropdown List');
foreach ($score_methods as $val => $display) {
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
              <th><label for="option_order">Option Order</label></th>
              <td>
                <?php echo option_order($question->get_option_order()) ?>
              </td>
            </tr>
					</tbody>
				</table>

        <table id="q-feedback" class="form" summary="Edit question feedback">
          <tbody>
            <tr>
              <th><label for="correct_fback">General Feedback</label></th>
              <td>
                <textarea id="correct_fback" name="correct_fback" cols="100" rows="3" class="form-large">right</textarea>
              </td>
            </tr>
          </tbody>
        </table>
        
        <div class="form">
          <h2>Options</h2>
        </div>
        
        <table id="q-options" class="form" summary="Edit question options">
          <tbody>
            <tr>
              <th colspan="3">&nbsp;</th>
              <th class="small"><strong>Correct Answer</strong></th>
            </tr>
          </tbody>
<?php
$i = 1;
foreach ($question->options as $o_id => $option) {
  echo render_option($option, $i, count($question->options));
  $i++;
}

for ($i = count($question->options) + 1; $i <= $question->max_options; $i++) {
  $option = new Option($mysqli, $userID);
  echo render_option($option, $i, count($question->options));
}

if($question->get_locked() == '') {
?>
          <tbody id="next-option-holder">
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

<?php

function render_option($option, $index, $current_options) {
  $mandatory = ($index <= 3) ? '<span class="mandatory">*</span>' : '';
  $hidden = ($index > 5 and $index > $current_options) ? ' hide' : '';
  $spaced = ($index > 1) ? ' class="spaced"' : '';
  $correct = ($option->get_correct() == $index) ? ' checked="checked"' : '';
  $html = <<< OPTION
          <tbody class="option{$hidden}">
            <tr{$spaced}>
              <th{$spaced}>{$mandatory}<label for="option_text{$index}"><strong>{$index}.</strong></label></th>
              <td colspan="2"{$spaced}>
                <textarea name="option_text{$index}" id="option_text{$index}" cols="90" rows="2" class="form-med-large">{$option->get_text()}</textarea>
                <input name="optionid{$index}" value="{$option->id}" type="hidden" />
              </td>
              <td class="small"><input id="option_correct{$index}" name="option_correct" value="{$index}" type="radio"{$correct} /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="option_correct_fback{$index}">Feedback:</label></th>
              <td>
                <textarea cols="85" rows="2" id="option_correct_fback{$index}" name="option_correct_fback{$index}" class="form-med">{$option->get_correct_fback()}</textarea>
              </td>
              <td>&nbsp;</td>
            </tr>

OPTION;

  if ($option->id != -1) {
    $media = $option->get_media();
    if ($media['filename'] != '') {
      $current_media_html =  display_media($media['filename'], $media['width'], $media['height'], $index); 
      $html .= <<< OPTION
              <tr>
                <td>&nbsp;</td>
                <th>Current Media:</th>
                <td>{$current_media_html}</td>
                <td>&nbsp;</td>
              </tr>

OPTION;
    }
  }

  $html .= <<< OPTION
            <tr>
              <td>&nbsp;</td>
              <th><label for="option_media{$index}">Change Media:</label></th>
              <td>
                <input id="option_media{$index}" name="option_media{$index}" type="file" size="50" />
              </td>
              <td>&nbsp;</td>
            </tr>
          </tbody>

OPTION;

  return $html;
}
?>        
