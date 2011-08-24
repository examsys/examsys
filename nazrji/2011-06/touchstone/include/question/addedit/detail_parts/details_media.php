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
