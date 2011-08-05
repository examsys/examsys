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
  $selected = ($question->score_method == $val) ? ' selected="selected"' : '';
?>
                  <option value="<?php echo $val ?>"<?php echo $selected ?>><?php echo $display ?></option>
<?php
}
?>
                </select>
                <input name="old_score_method" value="<?php echo $old_values['score_method'] ?>" type="hidden" />
              </td>
            </tr>
            <tr>
              <th><label for="option_order">Option Order</label></th>
              <td>
                <?php echo option_order($question->option_order) ?>
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
                <input name="old_correct_fback" value="<?php echo $old_values['correct_fback'] ?>" type="hidden" />
              </td>
            </tr>
          </tbody>
        </table>
        
        <table id="q-options" class="form" summary="Edit question options">
          <tbody>
            <tr>
              <th colspan="3">&nbsp;</th>
              <th class="small"><strong>Correct Answer</strong></th>
            </tr>
          </tbody>
          <tbody class="option">
            <tr>
              <th><span class="mandatory">*</span><label for="new_option_text1"><strong>1.</strong></label></th>
              <td colspan="2">
                <textarea name="new_option_text1" id="new_option_text1" cols="90" rows="2" class="form-med-large">collagen</textarea>
                <textarea name="old_option_text1" cols="90" rows="2" class="form-old">collagen</textarea>
                <input name="optionid1" value="280288" type="hidden" />
              </td>
              <td class="small"><input id="correct1" name="correct" value="1" type="radio" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="feedback_right1">Feedback:</label></th>
              <td>
                <textarea cols="85" rows="2" id="feedback_right1" name="feedback_right1" class="form-med"></textarea>
                <textarea name="old_feedback_right1" class="form-old" rows="2" cols="40"></textarea>
              </td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="new_option_media1">Change Media:</label></th>
              <td>
                <input id="new_option_media1" name="new_option_media1" type="file" size="50" />
                <input name="old_option_media1" id="old_option_media1" value="" type="hidden" />
                <input name="old_option_media_width1" value="0" type="hidden" />
                <input name="old_option_media_height1" value="0" type="hidden" />
              </td>
              <td>&nbsp;</td>
            </tr>
          </tbody>
          <tbody class="option">
            <tr>
              <th><span class="mandatory">*</span><label for="new_option_text2"><strong>2.</strong></label></th>
              <td colspan="2">
                <textarea name="new_option_text2" id="new_option_text2" cols="90" rows="2" class="form-med-large">corticosteroid</textarea>
                <textarea name="old_option_text2" cols="90" rows="2" class="form-old">corticosteroid</textarea>
                <input name="optionid1" value="280288" type="hidden" />
              </td>
              <td class="small"><input id="correct2" name="correct" value="2" type="radio" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="feedback_right2">Feedback:</label></th>
              <td>
                <textarea cols="85" rows="2" id="feedback_right2" name="feedback_right2" class="form-med"></textarea>
                <textarea name="old_feedback_right2" class="form-old" rows="2" cols="40"></textarea>
              </td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="new_option_media2">Change Media:</label></th>
              <td>
                <input id="new_option_media2" name="new_option_media2" type="file" size="50" />
                <input name="old_option_media2" id="old_option_media2" value="" type="hidden" />
                <input name="old_option_media_width2" value="0" type="hidden" />
                <input name="old_option_media_height2" value="0" type="hidden" />
              </td>
              <td>&nbsp;</td>
            </tr>
          </tbody>
          <tbody class="option">
            <tr>
              <th><span class="mandatory">*</span><label for="new_option_text3"><strong>3.</strong></label></th>
              <td colspan="2">
                <textarea name="new_option_text3" id="new_option_text3" cols="90" rows="2" class="form-med-large">fatty acid</textarea>
                <textarea name="old_option_text3" cols="90" rows="2" class="form-old">fatty acid</textarea>
                <input name="optionid1" value="280288" type="hidden" />
              </td>
              <td class="small"><input id="correct3" name="correct" value="3" type="radio" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="feedback_right3">Feedback:</label></th>
              <td>
                <textarea cols="85" rows="2" id="feedback_right3" name="feedback_right3" class="form-med"></textarea>
                <textarea name="old_feedback_right3" class="form-old" rows="2" cols="40"></textarea>
              </td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="new_option_media3">Change Media:</label></th>
              <td>
                <input id="new_option_media3" name="new_option_media3" type="file" size="50" />
                <input name="old_option_media3" id="old_option_media3" value="" type="hidden" />
                <input name="old_option_media_width3" value="0" type="hidden" />
                <input name="old_option_media_height3" value="0" type="hidden" />
              </td>
              <td>&nbsp;</td>
            </tr>
          </tbody>
          <tbody class="option hide">
            <tr>
              <th><label for="new_option_text4"><strong>4.</strong></label></th>
              <td colspan="2">
                <textarea name="new_option_text4" id="new_option_text4" cols="90" rows="2" class="form-med-large">glucose</textarea>
                <textarea name="old_option_text4" cols="90" rows="2" class="form-old">glucose</textarea>
                <input name="optionid1" value="280288" type="hidden" />
              </td>
              <td class="small"><input id="correct4" name="correct" value="4" type="radio" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="feedback_right4">Feedback:</label></th>
              <td>
                <textarea cols="85" rows="2" id="feedback_right4" name="feedback_right4" class="form-med"></textarea>
                <textarea name="old_feedback_right4" class="form-old" rows="2" cols="40"></textarea>
              </td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="new_option_media4">Change Media:</label></th>
              <td>
                <input id="new_option_media4" name="new_option_media4" type="file" size="50" />
                <input name="old_option_media4" id="old_option_media4" value="" type="hidden" />
                <input name="old_option_media_width4" value="0" type="hidden" />
                <input name="old_option_media_height4" value="0" type="hidden" />
              </td>
              <td>&nbsp;</td>
            </tr>
          </tbody>
          <tbody class="option hide">
            <tr>
              <th><label for="new_option_text5"><strong>5.</strong></label></th>
              <td colspan="2">
                <textarea name="new_option_text5" id="new_option_text5" cols="90" rows="2" class="form-med-large">heme</textarea>
                <textarea name="old_option_text5" cols="90" rows="2" class="form-old">heme</textarea>
                <input name="optionid1" value="280288" type="hidden" />
              </td>
              <td class="small"><input id="correct5" name="correct" value="5" type="radio" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="feedback_right5">Feedback:</label></th>
              <td>
                <textarea cols="85" rows="2" id="feedback_right5" name="feedback_right5" class="form-med"></textarea>
                <textarea name="old_feedback_right5" class="form-old" rows="2" cols="40"></textarea>
              </td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="new_option_media5">Change Media:</label></th>
              <td>
                <input id="new_option_media5" name="new_option_media5" type="file" size="50" />
                <input name="old_option_media5" id="old_option_media5" value="" type="hidden" />
                <input name="old_option_media_width5" value="0" type="hidden" />
                <input name="old_option_media_height5" value="0" type="hidden" />
              </td>
              <td>&nbsp;</td>
            </tr>
          </tbody>
          <tbody id="next-option-holder">
            <tr>
              <th>&nbsp;</th>
              <td colspan="3">
                <input id="next-option" value="Add More Options..." type="button" />
              </td>
            </tr>
          </tbody>
        </table>
