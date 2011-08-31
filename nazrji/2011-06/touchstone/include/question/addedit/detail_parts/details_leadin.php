<?php
$mandatory_editor = (isset($mandatory_leadin)) ? $mandatory_leadin : true;
$field_editor = (isset($field_leadin)) ? $field_leadin : 'leadin';
$label_editor = (isset($label_leadin)) ? $label_leadin : '<label for="' . $field_editor . '">Lead-in</label><br /><span class="note">(the question)</span>';
$value_editor = $question->get_leadin();
require 'details_editor.php';
?>