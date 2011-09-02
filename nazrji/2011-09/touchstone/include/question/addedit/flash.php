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


// TODO: fix use of 'rel' on textarea

$options = $question->options;
$option_ids = array_keys($options);
$num_options = count($options);
if ($num_options > 0) {
  $option = reset($options);
  $option_media = $option->get_media();
  $marks_correct = $option->get_marks_correct();
  $option_id = $option->id;
} else {
  $option_media = array('filename' => '', 'width' => '0', 'height' => '0');
  $marks_correct = 1;
  $option_id = -1;
}

?>
<script type="text/javascript">
//<![CDATA[
<?php // Bit of a hack to get the flash to stay centred ?>
$(function () {
  $('#question-holder').addClass('max');
});
//]]>
</script>

				<table id="q-details" class="form" summary="Edit question details">
					<tbody>
<?php
require_once 'detail_parts/details_theme_notes.php';
require_once 'detail_parts/details_leadin.php';
$current_media = $question->get_media();
$media_label = 'Question SWF';
require 'detail_parts/details_media.php';
$current_media = $option_media;
$media_label = 'Answer SWF';
$media_for = 'option';
$media_index = '1';
require 'detail_parts/details_media.php';
?>
            <tr>
              <th class="spaced-top"><label for="option_marks_correct1"><strong>Marks</strong></label></th>
              <td class="spaced-top">
                <input name="optionid1" value="<?php echo $option_id ?>" type="hidden" />
                <input name="option_text1" value="Flash option placeholder text (non-editable, not displayed to students)" type="hidden" />
                <select id="option_marks_correct1" name="option_marks_correct1">
<?php
echo ViewHelper::render_options(range(1, 20), $marks_correct, 3);
?>
                </select>
              </td>
            </tr>            
					</tbody>
				</table>
