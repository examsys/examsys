            <tr>
              <th><label for="display_method">Presentation</label></th>
              <td>
                <select id="display_method" name="display_method">
<?php
echo ViewHelper::render_options($question->get_display_methods(), $question->get_display_method(), 3);
?>
                </select>
              </td>
            </tr>
