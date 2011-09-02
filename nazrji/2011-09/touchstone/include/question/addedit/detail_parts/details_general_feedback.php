<?php
$label_correct = (isset($label_correct)) ? $label_correct : 'General Feedback';
$label_incorrect = (isset($label_incorrect)) ? $label_incorrect : 'Feedback if Incorrect<br /><span class="note">(leave blank to use default)</span>';
$show_incorrect = (isset($show_incorrect)) ? $show_incorrect : false;
$feedback_rows = (isset($feedback_rows)) ? $feedback_rows : 3;
?>
        <table id="q-feedback" class="form" summary="Edit question feedback">
          <tbody>
            <tr>
              <th><label for="correct_fback"><?php echo $label_correct ?></label></th>
              <td>
                <textarea id="correct_fback" name="correct_fback" cols="100" rows="<?php echo $feedback_rows ?>" class="form-large"><?php echo $question->get_correct_fback() ?></textarea>
              </td>
            </tr>
<?php
if ($show_incorrect):
?>
            <tr>
              <th><label for="incorrect_fback"><?php echo $label_incorrect ?></label></th>
              <td>
                <textarea id="incorrect_fback" name="incorrect_fback" cols="100" rows="<?php echo $feedback_rows ?>" class="form-large"><?php echo $question->get_incorrect_fback() ?></textarea>
              </td>
            </tr>
<?php
endif;
?>
          </tbody>
        </table>
