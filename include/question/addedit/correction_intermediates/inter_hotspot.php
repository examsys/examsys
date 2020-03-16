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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

$points1 = param::optional('points1', '', param::TEXT, param::FETCH_POST);

// Get student data for display.
$fix_data = '';
$current_data = '';
$result = $mysqli->prepare('SELECT id, user_answer FROM log2 WHERE q_id = ?');
$result->bind_param('i', $question->id);
$result->execute();
$result->bind_result($id, $user_answer);
while ($result->fetch()) {
    if ($user_answer != 'u') {
        $tmp_user_answer = '';
        $layers = explode('|', $user_answer);
        foreach ($layers as $layer) {
            if ($tmp_user_answer != '') {
                $tmp_user_answer .= '|';
            }
            $layeranswer = hotspot_helper::get_instance()->layer_answer_strip_correct_information($layer);
            // Nothing to add if layer unaswered.
            if ($layeranswer != hotspot_helper::UNSWERED_QUESTION) {
                $tmp_user_answer .= $layeranswer;
            }
        }
        $fix_data .= ';' . $id . ',' . $tmp_user_answer;
        // Temp Mark the students answers based on the new hotspots.
        $correct = hotspot_helper::get_instance()->mark($tmp_user_answer, $points1);
        $current_data .= ';' . $correct;
    }
}
$result->close();
$fix_data = substr($fix_data, 1);
  
$media = $question->get_media();
$plugin_height = max($media['height'] + 25, 380);
$imageurl = rogo_directory::get_directory('media')->url($media['filename']);
?>

        <div class="form">
          <h3><?php echo $string['correctionmode']; ?></h3>
        </div>
        
        <table class="form" summary="Hotspot flash movie">
          <thead>
            <tr>
              <th class="align-left"><span class="mandatory">*</span> <?php echo $string['image']; ?></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div
                    id="question1"
                    class="html5 hotspot analysis"
                    data-number="1"
                    data-type="hotspot"
                    data-mode="analysis"
                    data-image="<?php echo $imageurl; ?>"
                    data-image-width="<?php echo $media['width']; ?>"
                    data-image-height="<?php echo $media['height']; ?>"
                    data-setup="<?php echo htmlentities(trim($points1)); ?>"
                    data-answers="<?php echo $current_data; ?>"
                ></div>
                <input type="hidden" name="option_correct1" id="option_correct1" value="<?php echo $fix_data; ?>" />
                <input type="hidden" name="option_marks_correct" id="option_marks_correct" value="<?php echo $_POST['option_marks_correct']; ?>" />
                <input type="hidden" name="option_marks_incorrect" id="option_marks_incorrect" value="<?php echo $_POST['option_marks_incorrect']; ?>" />
                <input type="hidden" name="corrected" value="OK" />
                <input type="hidden" name="paperID" value="<?php echo $_POST['paperID']; ?>" />
                <input type="hidden" name="module" value="<?php echo $_POST['module']; ?>" />
                <input type="hidden" name="calling" value="<?php echo $_POST['calling']; ?>" />
                <input type="hidden" name="folder" value="<?php echo $_POST['folder']; ?>" />
                <input type="hidden" name="scrOfY" value="<?php echo $_POST['scrOfY']; ?>" />
                <input type="hidden" name="points1" value="<?php echo $points1; ?>" />
                <input type="hidden" name="checkout_author" value="
                <?php
                if (isset($_POST['checkout_author'])) {
                    echo $_POST['checkout_author'];
                }
                ?>
                " />
              </td>
            </tr>
            <tr>
              <td class="align-centre">
                <?php echo $string['hotspotcorrection']; ?>
              </td>
            </tr>
          </tbody>
        </table>

