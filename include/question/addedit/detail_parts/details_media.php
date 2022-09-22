<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

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
$media_alt_label = (isset($media_alt_label)) ? $media_alt_label : $string['mediaalt'];
if ($dis_class != '') {
    $disabled = ' disabled="disabled"';
    $locked = true;
} else {
    $disabled = '';
    $locked = false;
}
if ($current_media['filename'] != '' and $current_media['num'] == 0) {
    $configObj = Config::get_instance();
    $questiondata = \questiondata::get_datastore($question->get_type());
    $render = new render($configObj);
    $questiondata->set_media($current_media['filename'], $current_media['width'], $current_media['height'], $current_media['alt'], '', false, $media_index_display, $locked);
    ?>
            <tr>
              <th><?php echo $string['current'] . ' ' . $media_label ?></th>
              <td>
                <?php
                $render->render($questiondata, $string, 'paper/media.html');
                ?>
              </td>
            </tr>
    <?php
    if ($current_media['alt'] != '') {
        ?>
            <tr>
                <th><?php echo $string['current'] . ' ' . $media_alt_label ?></th>
                <td>
                    <textarea id="currentalt" name="currentalt" class="filepickertextarea" <?php echo $disabled; ?>><?php echo $current_media['alt']; ?></textarea>
                </td>
            </tr>
        <?php
    }
}
?>
            <tr>
              <th><label for="<?php echo $media_for ?>_media<?php echo $media_index ?>"><?php echo $string['change'] . ' ' . $media_label ?></label></th>
              <td>
                <button id="filepicker<?php echo $media_for ?>_media<?php echo $media_index ?>" data-mediaid="<?php echo $media_for ?>_media<?php echo $media_index ?>" class="filepicker" <?php echo $disabled ?>><?php echo $string['uploadmedia']; ?></button>
                  <div id="filepickersection<?php echo $media_for ?>_media<?php echo $media_index ?>" class="filepickersection">
                  <?php
                    $mediadata = array(
                        'mediaid' => $media_for . '_media' . $media_index,
                        'mediaalt' => 'alt_' . $media_for . '_media' . $media_index,
                        'mediaagreement' => 'agreement_' . $media_for . '_media' . $media_index,
                        'mediadecorative' => 'dec_' . $media_for . '_media' . $media_index,
                        'medianum' => 'num_' . $media_for . '_media' . $media_index,
                        'num' => ($media_index == '' ? 0 : $media_index)
                    );
                    $render = new render($configObject);
                    echo $render->render($mediadata, $string, 'filepicker.html');
                    ?>
                </div>
              </td>
            </tr>
