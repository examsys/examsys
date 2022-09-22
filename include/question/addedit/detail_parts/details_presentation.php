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

$presentation_label = (isset($presentation_label)) ? $presentation_label :  $string['presentation'];
$disp_method_class = (isset($disp_method_class)) ? ' class="' . $disp_method_class . '"' : '';
$disabled = ($dis_class == '') ? '' : ' disabled="disabled"';
?>
            <tr>
              <th><label for="display_method"><?php echo $presentation_label ?></label></th>
              <td>
                <select id="display_method" name="display_method"<?php echo $disp_method_class . $disabled ?>>
<?php
echo ViewHelper::render_options($question->get_display_methods(), $question->get_display_method(), 3);
?>
                </select>
              </td>
            </tr>
