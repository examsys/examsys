<?php 
$show_notes = (isset($show_notes)) ? $show_notes : true;
?>
            <tr>
              <th><label for="theme">Theme/Heading</label></th>
              <td>
                <textarea id="theme" name="theme" cols="100" rows="5" class="form-large"><?php echo $question->get_theme() ?></textarea>
              </td>
            </tr>
<?php 
if ($show_notes):
?>
            <tr>
              <th><label for="notes">Notes</label><br /><span class="note">(visible to students)</span></th>
              <td>
                <textarea id="notes" name="notes" cols="100" rows="2" class="form-large"><?php echo $question->get_notes() ?></textarea>
              </td>
            </tr>
<?php 
endif;
?>