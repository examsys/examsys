<tr>
              <th><label for="theme">Theme/Heading</label></th>
              <td>
                <textarea id="theme" name="theme" cols="100" rows="5" class="form-large"><?php echo $question->get_theme() ?></textarea>
                <textarea name="old_theme" cols="100" rows="5" class="form-old"><?php $old_values['theme'] ?></textarea>
                <input name="checkout_author" value="17276" type="hidden" class="form-old" />
              </td>
            </tr>
            <tr>
              <th><label for="notes">Notes</label><br /><span class="note">(visible to students)</span></th>
              <td>
                <textarea id="notes" name="notes" cols="100" rows="2" class="form-large"><?php echo $question->get_notes() ?></textarea>
                <textarea name="old_notes" class="form-old" rows="2" cols="40"><?php $old_values['notes'] ?></textarea>
              </td>
            </tr>
            <tr>
              <th class="field"><label for="scenario">Scenario</label><br /><span class="note">(background info)</span></th>
              <td colspan="2">
                <?php echo wysiwyg_editor('edit_common1', 'scenario', $question->get_scenario()); ?>
                <textarea id="old_scenario" name="old_scenario" cols="100" rows="5" class="form-old"><?php $old_values['scenario'] ?></textarea>
              </td>
            </tr>
            <tr>
              <th><label for="q_media">Change Media</label></th>
              <td>
                <input id="q_media" name="q_media" size="65" type="file" />
                <input name="old_q_media" value="" type="hidden" />
                <input name="old_q_media_width" value="0" type="hidden" />
                <input name="old_q_media_height" value="0" type="hidden" />
              </td>
            </tr>
            <tr>
              <th><span class="mandatory">*</span> <label for="leadin">Lead-in</label><br /><span class="note">(the question)</span></th>
              <td>
                <?php echo wysiwyg_editor('edit_common2', 'leadin', $question->get_leadin()); ?>
                <textarea name="old_leadin" id="old_leadin" cols="100" rows="5" class="form-old"><?php $old_values['leadin'] ?></textarea>
              </td>
            </tr>

