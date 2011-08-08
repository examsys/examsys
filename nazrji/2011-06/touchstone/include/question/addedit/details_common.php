<tr>
              <th><label for="theme">Theme/Heading</label></th>
              <td>
                <textarea id="theme" name="theme" cols="100" rows="5" class="form-large"><?php echo $question->get_theme() ?></textarea>
              </td>
            </tr>
            <tr>
              <th><label for="notes">Notes</label><br /><span class="note">(visible to students)</span></th>
              <td>
                <textarea id="notes" name="notes" cols="100" rows="2" class="form-large"><?php echo $question->get_notes() ?></textarea>
              </td>
            </tr>
            <tr>
              <th class="field"><label for="scenario">Scenario</label><br /><span class="note">(background info)</span></th>
              <td>
                <?php echo wysiwyg_editor('edit_common1', 'scenario', $question->get_scenario()); ?>
              </td>
            </tr>
<?php
$media = $question->get_media();
if ($media['filename'] != '') {
?>
            <tr>
              <th>Current Media</th>
              <td><?php echo display_media($media['filename'], $media['width'], $media['height'], '0'); ?></td>
            </tr>
<?php      
}
?>
            <tr>
              <th><label for="q_media">Change Media</label></th>
              <td>
                <input id="q_media" name="q_media" size="65" type="file" />
              </td>
            </tr>
            <tr>
              <th><span class="mandatory">*</span> <label for="leadin">Lead-in</label><br /><span class="note">(the question)</span></th>
              <td>
                <?php echo wysiwyg_editor('edit_common2', 'leadin', $question->get_leadin()); ?>
              </td>
            </tr>

