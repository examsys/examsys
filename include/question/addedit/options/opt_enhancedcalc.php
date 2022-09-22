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

$classes = array();
if ($index % 2 == 0) {
    $classes[] = 'alt';
}
if ((count($vars) == 0 and $index > 5) or (count($vars) > 0 and $index > count($vars))) {
    $classes[] = 'hide';
}
$class_mod = (count($classes) > 0) ? ' ' . implode(' ', $classes) : '';

$spaced = ($index > 1) ? ' spaced-top spaced-bottom' : ' spaced-bottom';
$disabled = ($dis_class != '') ? ' disabled="disabled"' : '';

$min_value = $variable->get_min();
if (mb_substr($min_value, 0, 3) == 'var' or mb_substr($min_value, 0, 3) == 'ans') {
    $min_link_icon = '../../artwork/variable_link_on.png';
} else {
    $min_link_icon = '../../artwork/variable_link_off.png';
}

$max_value = $variable->get_max();
if (mb_substr($max_value, 0, 3) == 'var' or mb_substr($max_value, 0, 3) == 'ans') {
    $max_link_icon = '../../artwork/variable_link_on.png';
} else {
    $max_link_icon = '../../artwork/variable_link_off.png';
}
?>
          <tbody class="option<?php echo $class_mod ?>">
            <tr>
              <th class="<?php echo $spaced ?>"><?php echo $variable->get_variable() ?></th>
              <td class="align-left<?php echo $spaced ?>">
                <label for="option_min<?php echo $index ?>" class="hide"><?php echo $string['option'] ?> <?php echo $index ?> <?php echo $string['minimum'];?></label>
                <input type="text" id="option_min<?php echo $index ?>" name="option_min<?php echo $index ?>" value="<?php echo $variable->get_min() ?>" class="calc-min form-tiny<?php echo $dis_class ?>"<?php echo $dis_readonly ?> />
                <a href="#" class="variable-link<?php echo $dis_class ?>" rel="option_min<?php echo $index ?>"><img id="minicon<?php echo $index ?>" src="<?php echo $min_link_icon ?>" width="23" height="22" alt="Link" class="form-img" /></a>
                <input name="optionid<?php echo $index ?>" value="<?php echo $variable->id ?>" type="hidden" />
              </td>
              <td class="align-left<?php echo $spaced ?>">
                <label for="option_max<?php echo $index ?>" class="hide"><?php echo $string['option'];?> <?php echo $index ?> <?php echo $string['maximum'];?></label>
                <input type="text" id="option_max<?php echo $index ?>" name="option_max<?php echo $index ?>" value="<?php echo $variable->get_max() ?>" class="form-tiny<?php echo $dis_class ?>"<?php echo $dis_readonly ?> />
                <a href="#" class="variable-link<?php echo $dis_class ?>" rel="option_max<?php echo $index ?>"><img id="maxicon<?php echo $index ?>" src="<?php echo $max_link_icon ?>" width="23" height="22" alt="Link" class="form-img" /></a>
              </td>
              <td class=" align-left<?php echo $spaced ?>">
                <label for="option_decimals<?php echo $index ?>" class="hide"><?php echo $string['option'];?> <?php echo $index ?> <?php echo $string['decimals'];?></label>
                <select id="option_decimals<?php echo $index ?>" name="option_decimals<?php echo $index ?>"<?php echo $disabled ?>>
<?php
echo ViewHelper::render_options($decimals, $variable->get_decimals(), 3);
?>
                </select>
              </td>
              <td class=" align-left<?php echo $spaced ?>">
                <label for="option_increment<?php echo $index ?>" class="hide"><?php echo $string['option'];?> <?php echo $index ?> <?php echo $string['increment'];?></label>
                <input type="text" id="option_increment<?php echo $index ?>" name="option_increment<?php echo $index ?>" value="<?php echo $variable->get_increment() ?>" class="calc-min form-tiny<?php echo $dis_class ?>"<?php echo $dis_readonly ?> />
              </td>
            </tr>
          </tbody>
