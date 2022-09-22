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
$plugin_height = max($media['height'] + 25, 400);
if (count($question->options) > 0) {
    $option = reset($question->options);
    $correct = $option->get_correct();
    $option_id = $option->id;
} else {
    $correct = '';
    $option_id = -1;
}
$imageurl = rogo_directory::get_directory('media')->url($media['filename']);
if ($question->get_locked() != '') {
    // The question is locked, limited editing.
    $hotspot_mode = 'correction';
} else {
    // Full editing capabilities.
    $hotspot_mode = 'edit';
}
?>

                <table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
                    <tbody>
<?php
require_once 'detail_parts/details_theme_notes.php';
require_once 'detail_parts/details_scenario.php';
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
                <div id="hs_holder">
                  <p><input type="submit" name="submit" value="Replace Media"/></p>
                  <div
                    id="question1"
                    class="html5 hotspot edit"
                    data-number="1"
                    data-type="hotspot"
                    data-mode="<?php echo $hotspot_mode; ?>"
                    data-image="<?php echo $imageurl; ?>"
                    data-image-width="<?php echo $media['width']; ?>"
                    data-image-height="<?php echo $media['height']; ?>"
                    data-setup="<?php echo htmlentities($correct); ?>"
                 ></div>
                  <input name="optionid1" value="<?php echo $option_id ?>" type="hidden" />
                  <input type="hidden" id="points1" name="points1" value="<?php echo $correct ?>" />
                  <?php if (!isset($_POST['submit']) or $_POST['submit'] != 'Replace Media') { ?>
                    <input type="hidden" id="q_media" name="q_media" value="<?php echo $media['filename'] ?>" />
                    <input type="hidden" id="q_media_width" name="q_media_width" value="<?php echo $media['width'] ?>" />
                    <input type="hidden" id="q_media_height" name="q_media_height" value="<?php echo $media['height'] ?>" />
                  <?php } ?>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

<?php
require_once 'detail_parts/details_marking.php';
require_once 'detail_parts/details_general_feedback.php';
?>


