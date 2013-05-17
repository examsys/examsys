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

$correct = ($option->get_correct() == $index) ? ' checked="checked"' : '';
if ($index %2 == 0) {
  $alt_c = ' class="alt"';
} else {
  $alt_c = '';
}
$spaced = ($index > 1) ? ' spaced-top spaced-bottom' : ' spaced-bottom';
$disabled = ($dis_class != '') ? ' disabled="disabled"' : '';
?>
          <tbody class="option">
            <tr<?php echo $alt_c ?>>
              <th class="<?php echo $spaced ?>">$<?php echo $option->get_variable() ?></th>
              <td class="align-left<?php echo $spaced ?>">
                <label for="option_min<?php echo $index ?>" class="hide"><?php echo $string['option'];?> <?php echo $index ?> <?php echo $string['minimum'];?></label>
                <input type="text" id="option_min<?php echo $index ?>" name="option_min<?php echo $index ?>" value="<?php echo $option->get_min() ?>" class="calc-min form-tiny<?php echo $dis_class ?>"<?php echo $dis_readonly ?> />
                <a href="#" class="variable-link<?php echo $dis_class ?>" rel="option_min<?php echo $index ?>"><img id="minicon<?php echo $index ?>" src="../../artwork/variable_link_off.png" width="23" height="22" alt="Link" class="form-img" /></a>
                <input name="optionid<?php echo $index ?>" value="<?php echo $option->id ?>" type="hidden" />
              </td>
              <td class="align-left<?php echo $spaced ?>">
                <label for="option_max<?php echo $index ?>" class="hide"><?php echo $string['option'];?> <?php echo $index ?> <?php echo $string['maximum'];?></label>
                <input type="text" id="option_max<?php echo $index ?>" name="option_max<?php echo $index ?>" value="<?php echo $option->get_max() ?>" class="form-tiny<?php echo $dis_class ?>"<?php echo $dis_readonly ?> />
                <a href="#" class="variable-link<?php echo $dis_class ?>" rel="option_max<?php echo $index ?>"><img id="maxicon<?php echo $index ?>" src="../../artwork/variable_link_off.png" width="23" height="22" alt="Link" class="form-img" /></a>
              </td>
              <td class=" align-left<?php echo $spaced ?>">
                <label for="option_decimals<?php echo $index ?>" class="hide"><?php echo $string['option'];?> <?php echo $index ?> <?php echo $string['decimals'];?></label>
                <select id="option_decimals<?php echo $index ?>" name="option_decimals<?php echo $index ?>"<?php echo $disabled ?>>
<?php
echo ViewHelper::render_options($decimals, $option->get_decimals(), 3);
?>
                </select>
              </td>
              <td class=" align-left<?php echo $spaced ?>">
                <label for="option_increment<?php echo $index ?>" class="hide"><?php echo $string['option'];?> <?php echo $index ?> <?php echo $string['increment'];?></label>
                <select id="option_increment<?php echo $index ?>" name="option_increment<?php echo $index ?>"<?php echo $disabled ?>>
<?php
echo ViewHelper::render_options($increments, $option->get_increment(), 3);
?>
                </select>
              </td>
            </tr>
          </tbody>
