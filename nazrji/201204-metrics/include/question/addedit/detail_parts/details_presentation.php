<?php
$presentation_label = (isset($presentation_label)) ? $presentation_label :  $string['presentation'];
$disp_method_class = (isset($disp_method_class)) ? ' class="' . $disp_method_class . '"' : '';
$disabled = ($dis_class == '') ? '' : ' disabled="disabled"';
?>
            <tr>
              <th><label for="display_method"><?php echo $presentation_label ?></label></th>
              <td>
                <select id="display_method" name="display_method"<?php echo $disp_method_class . $disabled ?>>
<?php
echo ViewHelper::render_options($question->get_display_methods(), $question->get_display_method(), 3);
?>
                </select>
              </td>
            </tr>
