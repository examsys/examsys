<?php
$general_media = (isset($general_media)) ? $general_media : $question->get_media();
if ($general_media['filename'] != '') {
?>
            <tr>
              <th>Current Media</th>
              <td><?php echo display_media($general_media['filename'], $general_media['width'], $general_media['height'], '0'); ?></td>
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
