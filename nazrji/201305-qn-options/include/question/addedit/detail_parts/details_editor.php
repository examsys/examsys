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

$mandatory = (isset($mandatory_editor) and $mandatory_editor) ? '<span class="mandatory">*</span> ' : '';
$field_editor = (isset($field_editor)) ? $field_editor : 'scenario';
$label_editor = (isset($label_editor)) ? $label_editor : '<label for="' . $field_editor . '">' . $string['scenario'] . '</label><br /><span class="note">' . $string['scenariomsg'] . '</span>';
$value_editor = (isset($value_editor)) ? $value_editor : $question->get_scenario();
$index_editor = (isset($index_editor)) ? $index_editor++ : 1;
?>
            <tr>
              <th><?php echo $mandatory ?><?php echo $label_editor ?></th>
              <td>
<?php
  echo wysywig_or_non_editable($dis_class, 'edit_common' . $index_editor, $field_editor, $value_editor);
?>
              </td>
            </tr>

