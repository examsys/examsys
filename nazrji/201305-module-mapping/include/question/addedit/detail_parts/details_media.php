<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

$media_for = (isset($media_for)) ? $media_for : 'q';
$media_index = (isset($media_index)) ? $media_index : '';
$media_index_display = ($media_index == '') ? '0' : $media_index;
$current_media = (isset($current_media)) ? $current_media : $question->get_media();
$media_label = (isset($media_label)) ? $media_label : $string['media'];
if ($dis_class != '') {
  $disabled = ' disabled="disabled"';
  $locked = true;
} else {
  $disabled = '';
  $locked = false;
}
if ($current_media['filename'] != '') {
?>
            <tr>
              <th><?php echo $string['current'] . ' ' . $media_label ?></th>
              <td><?php echo display_media($current_media['filename'], $current_media['width'], $current_media['height'], '', $media_index_display, $locked); ?></td>
            </tr>
<?php      
}
?>
            <tr>
              <th><label for="<?php echo $media_for ?>_media<?php echo $media_index ?>"><?php echo $string['change'] . ' ' . $media_label ?></label></th>
              <td>
                <input id="<?php echo $media_for ?>_media<?php echo $media_index ?>" name="<?php echo $media_for ?>_media<?php echo $media_index ?>" size="65" type="file"<?php echo $disabled ?> />
              </td>
            </tr>
