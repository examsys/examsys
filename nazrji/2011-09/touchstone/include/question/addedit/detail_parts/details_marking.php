        <table id="q-details" class="form" summary="Edit question details">
          <tbody>
            <tr>
              <th>Marking</th>
              <td>
                <label for="score_method" class="heavy">Method</label>
                <select id="score_method" name="score_method" class="spaced-right-large">
<?php
echo ViewHelper::render_options($question->get_score_methods(), $question->get_score_method(), 3, true);
?>
                </select>
              </td>
            </tr>
          </tbody>
        </table>
