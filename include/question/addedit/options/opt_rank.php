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

$hidden = (($num_options == 0 and $index > 6) or ($num_options > 0 and $index > $num_options)) ? ' hide' : '';
for ($i = 0; $i <= 20; $i++) {
    $postfix = '';
    if ($language == 'en') {
        $postfix = 'th';
        if ($i == 1) {
            $postfix = 'st';
        }
        if ($i == 2) {
            $postfix = 'nd';
        }
        if ($i == 3) {
            $postfix = 'rd';
        }
    }
    if ($i == 0) {
        $correct_vals[$i] = $string['na'];
    } else {
        $correct_vals[$i] = $i . $postfix;
    }
}
if ($index % 2 == 0) {
    $alt_c = ' class="alt"';
} else {
    $alt_c = '';
}
$spaced = ($index > 1) ? ' spaced-top' : '';
?>
          <tbody class="option<?php echo $hidden ?>">
            <tr<?php echo $alt_c ?>>
              <th class="spaced-bottom<?php echo $spaced ?>"><label for="option_text<?php echo $index ?>"><?php echo $index ?>.</label></th>
              <td class="spaced-bottom<?php echo $spaced ?>">
                <input type="text" name="option_text<?php echo $index ?>" id="option_text<?php echo $index ?>" value="<?php echo $option->get_text() ?>" class="form-med-large<?php echo $dis_class ?>"<?php echo $dis_readonly ?> />
                <input name="optionid<?php echo $index ?>" value="<?php echo $option->id ?>" type="hidden" />
              </td>
              <td class="small align-centre spaced-bottom<?php echo $spaced ?>">
                <select id="option_correct<?php echo $index ?>" name="option_correct<?php echo $index ?>">
<?php
echo ViewHelper::render_options($correct_vals, $option->get_correct(), 3, true);
?>
                </select>
              </td>
            </tr>
          </tbody>
