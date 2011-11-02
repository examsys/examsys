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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

$mandatory = ($index <= 2) ? '<span class="mandatory">*</span>' : '';
$hidden = (($num_options == 0 and $index > 5) or ($num_options > 0 and $index > $num_options)) ? ' hide' : '';
$correct = ($option->get_correct() == $index) ? ' checked="checked"' : '';
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
              <th<?php echo $spaced ?>><?php echo $mandatory ?><label for="option_text<?php echo $index ?>"><?php printf($string['optiontext'], $index) ?></label></th>
              <td<?php echo $spaced ?>>
                <textarea name="option_text<?php echo $index ?>" id="option_text<?php echo $index ?>" cols="90" rows="2" class="form-med-large"><?php echo $option->get_text() ?></textarea>
                <input name="optionid<?php echo $index ?>" value="<?php echo $option->id ?>" type="hidden" />
              </td>
              <td class="small align-centre"><input id="option_correct<?php echo $index ?>" name="option_correct" value="<?php echo $index ?>" type="radio"<?php echo $correct ?> /></td>
            </tr>
<?php

  if ($option->id != -1) { 
    $media = $option->get_media();
    if ($media['filename'] != '') {
      $current_media_html =  display_media($media['filename'], $media['width'], $media['height'], $index); 
?>
              <tr<?php echo $alt_c ?>>
                <th><?php echo $string['current'] . ' ' . $string['media'] ?></th>
                <td><?php echo $current_media_html ?></td>
                <td>&nbsp;</td>
              </tr>
<?php
     }
   }

?>
            <tr<?php echo $alt_c ?>>
              <th><label for="option_media<?php echo $index ?>"><?php echo $string['change'] . ' ' . $string['media'] ?></label></th>
              <td>
                <input id="option_media<?php echo $index ?>" name="option_media<?php echo $index ?>" type="file" size="50" />
              </td>
              <td>&nbsp;</td>
            </tr>
            <tr<?php echo $alt_c ?>>
              <th class="spaced-bottom"><label for="option_correct_fback<?php echo $index ?>"><?php echo $string['feedback'] ?></label></th>
              <td class="spaced-bottom">
                <textarea cols="85" rows="2" id="option_correct_fback<?php echo $index ?>" name="option_correct_fback<?php echo $index ?>" class="form-med-large"><?php echo $option->get_correct_fback() ?></textarea>
              </td>
              <td class="spaced-bottom">&nbsp;</td>
            </tr>
          </tbody>
