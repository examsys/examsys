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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

$mandatory = '<span class="mandatory">*</span>';
if ($index % 2 == 0) {
    $alt = ' alt';
    $alt_c = ' class="alt"';
} else {
    $alt = $alt_c = '';
}
$spaced = ($index > 1) ? " class=\"spaced-top{$alt}\"" : $alt_c;
?>
          <tbody class="option">
            <tr<?php echo $spaced ?>>
              <th<?php echo $spaced ?>><?php echo $mandatory ?><label for="option_text<?php echo $index ?>"><?php echo $string['option'];?> <?php echo $index ?></label></th>
              <td<?php echo $spaced ?>>
                <textarea name="option_text<?php echo $index ?>" id="option_text<?php echo $index ?>" cols="90" rows="1" class="form-med-large form-fixed sct-option" readonly="readonly"><?php echo $option->get_text() ?></textarea>
                <input name="optionid<?php echo $index ?>" value="<?php echo $option->id ?>" type="hidden" />
              </td>
              <td class="small align-right">
                <select id="option_correct<?php echo $index ?>" name="option_correct<?php echo $index ?>"<?php echo $disabled ?>>
<?php
echo ViewHelper::render_options($experts, $option->get_correct(), 3);
?>
                </select>
              </td>
            </tr>
            <tr<?php echo $alt_c ?>>
              <th class="spaced-bottom"><label for="option_correct_fback<?php echo $index ?>"><?php echo $string['feedback'] ?></label></th>
              <td class="spaced-bottom">
                <textarea cols="85" rows="2" id="option_correct_fback<?php echo $index ?>" name="option_correct_fback<?php echo $index ?>" class="form-med-large<?php echo $dis_class ?>"<?php echo $dis_readonly ?>><?php echo $option->get_correct_fback() ?></textarea>
              </td>
              <td class="spaced-bottom">&nbsp;</td>
            </tr>
          </tbody>
