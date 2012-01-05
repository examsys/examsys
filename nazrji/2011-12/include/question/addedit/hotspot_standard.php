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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

$media = $question->get_media();
$plugin_height = max($media['height'] + 25, 380);
if (count($question->options) > 0) {
  $option = reset($question->options);
  $correct = $option->get_correct();
  $option_id = $option->id;
} else {
  $correct = '';
  $option_id = -1;
}
?>
<script type="text/javascript">
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
<?php
if ($media['filename'] != '' and !$show_correction_intermediate):
?>
                <script type="text/javascript">
                  function swfLoaded1(message) {
                    var num = message.substring(5,message.length);
                    setUpFlash(num, message, '<?php echo $language; ?>', '<?php echo $media['filename']; ?>', '<?php echo str_replace("'", "\'", trim($correct)); ?>');
                  }
                  write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash1" width="<?php echo ($media['width'] + 306); ?>" height="<?php echo $plugin_height; ?>" align="middle">');
                  write_string('<param name="allowScriptAccess" value="always" />');
                  write_string('<param name="movie" value="../add/hotspot_add.swf" />');
                  write_string('<param name="quality" value="high" />');
                  write_string('<param name="bgcolor" value="#F1F5FB" />');
                  write_string('<param name="wmode" value="opaque" />');
                  write_string('<embed src="../add/hotspot_add.swf" quality="high" bgcolor="#F1F5FB" width="<?php echo ($media['width'] + 306); ?>" height="<?php echo $plugin_height; ?>" swliveconnect="true" id="flash1" name="flash1" align="middle" wmode="opaque" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
                  write_string('</object>');
                </script>
<?php
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
        

