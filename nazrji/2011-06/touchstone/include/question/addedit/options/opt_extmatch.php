<?php
// TODO: hide
$mandatory = ($index <= 2) ? '<span class="mandatory">*</span> ' : '';
if ($index %2 == 0) {
  $alt_c = ' class="alt"';
} else {
  $alt = $alt_c = '';
}
$stem = (isset($stems[$index - 1])) ? $stems[$index - 1] : '';
$feedback = (isset($all_feedback[$index - 1])) ? $all_feedback[$index - 1] : '';
$selected = (isset($correct_answers[$index - 1])) ? $correct_answers[$index - 1] : '';
$select_size = (count($option_texts) < 10) ? count($option_texts) : 10;
?>
            <tr<?php echo $alt_c ?>>
              <th><?php echo $mandatory ?><label for="edit_extmatch<?php echo $index ?>">Stem</label></th>
              <td>
                <?php echo wysiwyg_editor('edit_extmatch' . $index, 'question_stem' . strval($index), $stem); ?>
              </td>
            </tr>
<?php

if ($all_media['filenames'][$index] != '') {
  $current_media_html =  display_media($all_media['filenames'][$index], $all_media['widths'][$index], $all_media['heights'][$index], $index); 
?>
              <tr<?php echo $alt_c ?>>
                <th>Current Media:</th>
                <td><?php echo $current_media_html ?></td>
              </tr>
<?php
}
?>
            <tr<?php echo $alt_c ?>>
              <th><label for="question_media<?php echo $index ?>">Change Media</label></th>
              <td>
                <input id="question_media<?php echo $index ?>" name="question_media<?php echo $index ?>" type="file" size="50" />
              </td>
            </tr>
            <tr<?php echo $alt_c ?>>
              <th><label for="option_correct_fback<?php echo $index ?>">Feedback</label></th>
              <td>
                <textarea cols="85" rows="2" id="question_correct_fback<?php echo $index ?>" name="question_correct_fback<?php echo $index ?>" class="form-med-large"><?php echo $feedback ?></textarea>
              </td>
            </tr>
            <tr<?php echo $alt_c ?>>
              <th><label for="option_correct<?php echo $index ?>">Correct Answers</label><br /><span class="note">(Use &lt;ctrl&gt; plus mouse<br />to select several items)</span></th>
              <td>
                <select id="option_correct<?php echo $index ?>" name="option_correct<?php echo $index ?>[]" multiple="multiple" size="<?php echo $select_size ?>" class="extmatch-correct">
<?php
echo ViewHelper::render_options($option_texts, $selected, 3);
?>
                </select>
              </td>
            </tr>
            