<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

$media = $question->get_media();
$plugin_height = max($media['height'] + 25, 380);
if (count($question->options) > 0) {
  $option = reset($question->options);
  $correct = $option->get_correct();
} else {
  $correct = '';
}
?>
<script type="text/javascript">
//<![CDATA[
<?php // Bit of a hack to get the flash to stay centred ?>
$(function () {
  $('#question-holder').addClass('max');
});
flashTarget = 'points';
//]]>
</script>

				<table id="q-details" class="form" summary="Edit question details">
					<tbody>
<?php
require_once 'detail_parts/details_theme_notes.php';
require_once 'detail_parts/details_scenario.php';
?>
					</tbody>
				</table>
        
        <table class="form" summary="Hotspot flash movie">
          <thead>
            <tr>
              <th class="align-left"><span class="mandatory">*</span> Image</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <script type="text/javascript">
                  function swfLoaded1(message) {
                    var num = message.substring(5,message.length);
                    setUpFlash(num, message, '<?php echo $media['filename']; ?>', '<?php echo str_replace("'", "\'", trim($correct)); ?>');
                  }
                  write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash1" width="<?php echo ($media['width'] + 306); ?>" height="<?php echo $plugin_height; ?>" align="middle">');
                  write_string('<param name="allowScriptAccess" value="always" />');
                  write_string('<param name="movie" value="../add/hotspot_add.swf" />');
                  write_string('<param name="quality" value="high" />');
                  write_string('<param name="bgcolor" value="#F1F5FB" />');
                  write_string('<embed src="../add/hotspot_add.swf" quality="high" bgcolor="#F1F5FB" width="<?php echo ($media['width'] + 306); ?>" height="<?php echo $plugin_height; ?>" swliveconnect="true" id="flash1" name="flash1" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
                  write_string('</object>');
                </script>
                <input type="hidden" id="points1" name="points1" value="<?php echo $correct ?>" />
                <input type="file" id="q_media" name="q_media" value="<?php echo $media['filename'] ?>" class="hide" />
              </td>
            </tr>
          </tbody>
        </table>

<?php
require_once 'detail_parts/details_marking.php';
require_once 'detail_parts/details_general_feedback.php';
?>
        

