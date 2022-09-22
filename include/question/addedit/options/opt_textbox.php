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

$hidden = (($num_options == 0 and $index > 5) or ($num_options > 0 and $index > $num_options)) ? ' hide' : '';
if ($index % 2 == 0) {
    $alt = ' alt';
    $alt_c = ' class="alt"';
} else {
    $alt = $alt_c = '';
}
$spaced = ($index > 1) ? " class=\"spaced-top{$alt}\"" : $alt_c;
$locked = ($dis_class != '');
?>
          <tbody class="option<?php echo $hidden ?>">
            <tr<?php echo $spaced ?>>
              <th<?php echo $spaced ?>><label for="option_text<?php echo $index ?>"><?php printf($string['reminder_no'], $index) ?></label></th>
              <td<?php echo $spaced ?>>
                <textarea name="option_text<?php echo $index ?>" id="option_text<?php echo $index ?>" cols="90" rows="2" class="form-med-large<?php echo $dis_class ?>"<?php echo $dis_readonly ?>><?php echo $option->get_text() ?></textarea>
                <input name="optionid<?php echo $index ?>" value="<?php echo $option->id ?>" type="hidden" />
<?php
if ($index == 1) {
    ?>
                <input name="option_correct<?php echo $index ?>" value="placeholder" type="hidden" /> 
    <?php
}
?>
              </td>
            </tr>
          </tbody>
