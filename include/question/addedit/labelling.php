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

$media = $question->get_media();
$plugin_height = max($media['height'] + 25, 475);
if (count($question->options) > 0) {
    $option = reset($question->options);
    $correct = $option->get_correct();
    $option_id = $option->id;
} else {
    $correct = '';
    $option_id = -1;
}
?>

                <table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
                    <tbody>
<?php
require_once 'detail_parts/details_theme_notes.php';
require_once 'detail_parts/details_scenario.php';
require_once 'detail_parts/details_leadin.php';
?>
                    </tbody>
                </table>

        <table class="form hotspot" summary="Hotspot flash movie">
          <thead>
            <tr>
              <th class="align-left"><span class="mandatory">*</span> <?php echo $string['image'] ?></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
<?php

if ($media['filename'] != '') :
    $img_str = '';
    if (mb_strtolower($mode) != mb_strtolower($string['edit'])) {
        foreach ($label_images as $lab_img) {
            if (isset($lab_img['filename']) and $lab_img['filename'] != '') {
                $img_str .= implode(',', $lab_img) . ';';
            }
        }
    }
    $configObject          = Config::get_instance();
    echo "<canvas class='labelling' id='canvas1'
    data-qno='1'
    data-qmedia='" . $media['filename'] . "'
    data-qcorrect='" .  trim(str_replace('"', '&#034', str_replace("'", '&#039', str_replace('�', '&#172', $correct))))  . "'
    data-user='undefined'
    data-marking='" . $img_str . "'
    width='" . ($media['width'] + 222) . "' height='" . $plugin_height . "'></canvas>\n";
    echo "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
endif;
?>
                <input name="optionid1" value="<?php echo $option_id ?>" type="hidden" />
                <input type="hidden" id="points1" name="points1" value="<?php echo $correct ?>" />
                <input type="hidden" id="q_media" name="q_media" value="<?php echo $media['filename'] ?>" />
                <input type="hidden" id="q_media_width" name="q_media_width" value="<?php echo $media['width'] ?>" />
                <input type="hidden" id="q_media_height" name="q_media_height" value="<?php echo $media['height'] ?>" />
              </td>
            </tr>
          </tbody>
        </table>

<?php
require_once 'detail_parts/details_marking.php';
require_once 'detail_parts/details_general_feedback.php';
?>