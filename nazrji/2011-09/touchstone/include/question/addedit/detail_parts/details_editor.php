<?php
$mandatory = (isset($mandatory_editor) and $mandatory_editor) ? '<span class="mandatory">*</span> ' : '';
$field_editor = (isset($field_editor)) ? $field_editor : 'scenario';
$label_editor = (isset($label_editor)) ? $label_editor : '<label for="' . $field_editor . '">Scenario</label><br /><span class="note">(background info)</span>';
$value_editor = (isset($value_editor)) ? $value_editor : $question->get_scenario();
$index_editor = (isset($index_editor)) ? $index_editor++ : 1;
?>
            <tr>
              <th><?php echo $mandatory ?><?php echo $label_editor ?></th>
              <td>
                <?php echo wysiwyg_editor('edit_common' . $index_editor, $field_editor, $value_editor); ?>
              </td>
            </tr>

