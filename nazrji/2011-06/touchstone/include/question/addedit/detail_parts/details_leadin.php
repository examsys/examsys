<?php
$mandatory_editor = (isset($mandatory_editor)) ? $mandatory_editor : true;
$field_editor = (isset($field_editor)) ? $field_editor : 'leadin';
$label_editor = (isset($label_editor)) ? $label_editor : '<label for="' . $field_editor . '">Lead-in</label><br /><span class="note">(the question)</span>';
$value_editor = $question->get_leadin();
require 'details_editor.php';
?>

