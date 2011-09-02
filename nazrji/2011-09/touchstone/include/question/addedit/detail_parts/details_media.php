<?php
$media_for = (isset($media_for)) ? $media_for : 'q';
$media_index = (isset($media_index)) ? $media_index : '';
$media_index_display = ($media_index == '') ? '0' : $media_index;
$current_media = (isset($current_media)) ? $current_media : $question->get_media();
$media_label = (isset($media_label)) ? $media_label : 'Media';
if ($current_media['filename'] != '') {
?>
            <tr>
              <th>Current <?php echo $media_label ?></th>
              <td><?php echo display_media($current_media['filename'], $current_media['width'], $current_media['height'], $media_index_display); ?></td>
            </tr>
<?php      
}
?>
            <tr>
              <th><label for="<?php echo $media_for ?>_media<?php echo $media_index ?>">Change <?php echo $media_label ?></label></th>
              <td>
                <input id="<?php echo $media_for ?>_media<?php echo $media_index ?>" name="<?php echo $media_for ?>_media<?php echo $media_index ?>" size="65" type="file" />
              </td>
            </tr>
