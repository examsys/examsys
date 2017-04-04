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
?>
<script>
//<![CDATA[
<?php // Bit of a hack to get the flash to stay centred ?>
$(function () {
  $('#question-holder').addClass('hotspot');
});
flashTarget = 'points';
//]]>
</script>

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
									<?php
									if ($media['filename'] != '' and !$show_correction_intermediate):
										$tmp_correct = str_replace("'", "\'", trim($correct));
										$tmp_correct = str_replace("&nbsp;", " ", $tmp_correct);
										$tmp_correct = preg_replace('/\r\n/', '', $tmp_correct);

    									$configObject          = Config::get_instance();
    									//<!-- ======================== HTML5 part ================= -->
    									echo "<canvas id='canvas1' width='" . ($media['width'] + 300) . "' height='" . ($plugin_height) . "'></canvas>\n";
    									echo "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
    									echo "<script language='JavaScript' type='text/javascript'>\n";
    									echo "setUpQuestion(1, 'flash1', '" . $language . "', '" . $media['filename'] . "', '" . $tmp_correct . "','','','#FFC0C0','hotspot','edit'); \n";
    									echo "</script>\n";
    									//<!-- ==================================================== -->
									endif;
									?>                
                  <input name="optionid1" value="<?php echo $option_id ?>" type="hidden" />
                  <input type="hidden" id="points1" name="points1" value="<?php echo $correct ?>" />
                  <input type="hidden" id="q_media" name="q_media" value="<?php echo $media['filename'] ?>" />
                  <input type="hidden" id="q_media_width" name="q_media_width" value="<?php echo $media['width'] ?>" />
                  <input type="hidden" id="q_media_height" name="q_media_height" value="<?php echo $media['height'] ?>" />
                </div>
              </td>
            </tr>
          </tbody>
        </table>

<?php
require_once 'detail_parts/details_marking.php';
require_once 'detail_parts/details_general_feedback.php';
?>
        

