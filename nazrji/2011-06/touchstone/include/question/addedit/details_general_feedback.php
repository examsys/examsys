        <table id="q-feedback" class="form" summary="Edit question feedback">
          <tbody>
            <tr>
              <th><label for="correct_fback">General Feedback</label></th>
              <td>
                <textarea id="correct_fback" name="correct_fback" cols="100" rows="3" class="form-large"><?php echo $question->get_correct_fback() ?></textarea>
              </td>
            </tr>
          </tbody>
        </table>
