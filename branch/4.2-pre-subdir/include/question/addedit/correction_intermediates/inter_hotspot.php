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

// Query log2 table for existing student answers.
$fix_data = '';
$result = $mysqli->prepare("SELECT id, user_answer FROM log2 WHERE q_id=?");
$result->bind_param('i', $question->id);
$result->execute();  
$result->bind_result($id, $user_answer);
while ($row = $result->fetch()) {
  if ($user_answer != 'u') {
    $tmp_user_answer = '';
    $layers = explode('|',$user_answer);
    foreach ($layers as $layer) {
      $sub_parts = explode(',',$layer);
      if ($tmp_user_answer == '') {
        $tmp_user_answer = $sub_parts[1] . ',' . $sub_parts[2];
      } else {
        $tmp_user_answer .= '|' . $sub_parts[1] . ',' . $sub_parts[2];
      }
    }
    $fix_data .= ';' . $id . ',' . $tmp_user_answer;
  }
}
$result->close();
$fix_data = substr($fix_data,1);
  
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
  $('#question-holder').addClass('max');
});
flashTarget = 'option_correct';
//]]>
</script>
        <div class="form">
          <h3>Correction mode</h3>
        </div>
        
        <table class="form" summary="Hotspot flash movie">
          <thead>
            <tr>
              <th class="align-left"><span class="mandatory">*</span> Image</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
<?php
if ($media['filename'] != ''):
?>
                <script language="JavaScript">
                  function swfLoaded1(message) {
                    var num = message.substring(5,message.length);
                    setUpFlash(num, message, '<?php echo $media['filename']; ?>', '<?php echo trim($_POST['points1']); ?>', '<?php echo $fix_data; ?>');
                  }
                  write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash1" width="<?php echo ($media['width'] + 306); ?>" height="<?php echo $plugin_height; ?>" align="middle">');
                  write_string('<param name="allowScriptAccess" value="always" />');
                  write_string('<param name="movie" value="hotspot_correct.swf" />');
                  write_string('<param name="quality" value="high" />');
                  write_string('<param name="bgcolor" value="#FFFFFF" />');
                  write_string('<embed src="hotspot_correct.swf" quality="high" bgcolor="#FFFFFF" width="<?php echo ($media['width'] + 306); ?>" height="<?php echo $plugin_height; ?>" swliveconnect="true" id="flash1" name="flash1" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
                  write_string('</object>');
                </script>
<?php
endif;
?>                
                <input type="hidden" name="option_correct1" id="option_correct1" value="" />
                <input type="hidden" name="corrected" value="OK" />
                <input type="hidden" name="paperID" value="<?php echo $_POST['paperID']; ?>" />
                <input type="hidden" name="module" value="<?php echo $_POST['module']; ?>" />
                <input type="hidden" name="calling" value="<?php echo $_POST['calling']; ?>" />
                <input type="hidden" name="folder" value="<?php echo $_POST['folder']; ?>" />
                <input type="hidden" name="scrOfY" value="<?php echo $_POST['scrOfY']; ?>" />
                <input type="hidden" name="points" value="<?php if (isset($points)) echo $points; ?>" />
                <input type="hidden" name="points1" value="<?php echo $_POST['points1']; ?>" />
                <input type="hidden" name="correctedpoints" value="" />
                <input type="hidden" name="checkout_author" value="<?php if (isset($_POST['checkout_author'])) echo $_POST['checkout_author']; ?>" />
              </td>
            </tr>
            <tr>
              <td class="align-centre">
                The green dots show students answers which have now been marked as correct.<br />If you need to make further corrections please click 'OK' and then re-edit the question.
              </td>
            </tr>
          </tbody>
        </table>

