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
if ($index == 1) {
    $classes[] = 'required';
}
if ((count($answers) == 0 and $index > 2) or (count($answers) > 0 and $index > count($answers))) {
    $classes[] = 'hide';
}
$class_mod = (count($classes) > 0) ? ' ' . implode(' ', $classes) : '';

$spaced = ($index > 1) ? ' spaced-top spaced-bottom' : ' spaced-bottom';
$disabled = ($dis_class != '') ? ' disabled="disabled"' : '';
?>
          <tbody class="answer<?php echo $class_mod ?>">
            <tr>
              <th>&nbsp;</th>
              <td>
                <input type="text" id="option_formula<?php echo $index ?>" name="option_formula<?php echo $index ?>" class="formula form-med" value="<?php echo $answer->get_formula() ?>">
              </td>
              <td class="align-top"><input type="text" name="option_units<?php echo $index ?>" id="option_units<?php echo $index ?>" class="form-small" value="<?php echo $answer->get_units() ?>" /></td>
            </tr>
          </tbody>
