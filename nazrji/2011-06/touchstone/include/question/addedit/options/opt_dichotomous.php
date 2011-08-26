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

$hidden = ($index > $num_options) ? ' hide' : '';
$correct_t = ($option->get_correct() == 't') ? ' checked="checked"' : '';
$correct_f = ($option->get_correct() == 'f') ? ' checked="checked"' : '';
if ($index %2 == 0) {
  $alt = ' alt';
  $alt_c = ' class="alt"';
} else {
  $alt = $alt_c = '';
}
$spaced = ($index > 1) ? " class=\"spaced-top{$alt}\"" : $alt_c;
?>
          <tbody class="option<?php echo $hidden ?>">
            <tr<?php echo $spaced ?>>
              <th<?php echo $spaced ?>><label for="option_text<?php echo $index ?>">Stem #<?php echo $index ?></label></th>
              <td<?php echo $spaced ?>>
                <textarea name="option_text<?php echo $index ?>" id="option_text<?php echo $index ?>" cols="90" rows="2" class="form-med-large"><?php echo $option->get_text() ?></textarea>
                <input name="optionid<?php echo $index ?>" value="<?php echo $option->id ?>" type="hidden" />
              </td>
              <td class="small">
                <input type="radio" id="option_correct<?php echo $index ?>_<?php echo $question->get_answer_positive() ?>" name="option_correct<?php echo $index ?>" value="<?php echo $question->get_answer_positive() ?>"<?php echo $correct_t ?> /> <label for="option_correct<?php echo $index ?>_t" class="heavy spaced-right"><?php echo $labels['true'] ?></label>
                <input type="radio" id="option_correct<?php echo $index ?>_<?php echo $question->get_answer_negative() ?>" name="option_correct<?php echo $index ?>" value="<?php echo $question->get_answer_negative() ?>"<?php echo $correct_f ?> /> <label for="option_correct<?php echo $index ?>_f" class="heavy"><?php echo $labels['false'] ?></label>
            </tr>
<?php

  if ($option->id != -1) { 
    $media = $option->get_media();
    if ($media['filename'] != '') {
      $current_media_html =  display_media($media['filename'], $media['width'], $media['height'], $index); 
?>
              <tr<?php echo $alt_c ?>>
                <th>Current Media:</th>
                <td><?php echo $current_media_html ?></td>
                <td>&nbsp;</td>
              </tr>
<?php
     }
   }

?>
            <tr<?php echo $alt_c ?>>
              <th><label for="option_media<?php echo $index ?>">Change Media</label></th>
              <td>
                <input id="option_media<?php echo $index ?>" name="option_media<?php echo $index ?>" type="file" size="50" />
              </td>
              <td>&nbsp;</td>
            </tr>
            <tr<?php echo $alt_c ?>>
              <th><label for="option_correct_fback<?php echo $index ?>">Feedback if Correct:</label><br /><span class="note warning-severe">(default feedback)</span></th>
              <td>
                <textarea cols="85" rows="2" id="option_correct_fback<?php echo $index ?>" name="option_correct_fback<?php echo $index ?>" class="form-med-large"><?php echo $option->get_correct_fback() ?></textarea>
              </td>
              <td>&nbsp;</td>
            </tr>
            <tr<?php echo $alt_c ?>>
              <th class="spaced-bottom"><label for="option_incorrect_fback<?php echo $index ?>">Feedback if Incorrect:</label><br /><span class="note">(leave blank to use default)</span></th>
              <td class="spaced-bottom">
                <textarea cols="85" rows="2" id="option_incorrect_fback<?php echo $index ?>" name="option_incorrect_fback<?php echo $index ?>" class="form-med-large"><?php echo $option->get_incorrect_fback() ?></textarea>
              </td>
              <td class="spaced-bottom">&nbsp;</td>
            </tr>
          </tbody>
