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

$mandatory_editor = (isset($mandatory_leadin)) ? $mandatory_leadin : true;
$field_editor = (isset($field_leadin)) ? $field_leadin : 'leadin';
$label_editor = (isset($label_leadin)) ? $label_leadin : '<label for="' . $field_editor . '">' . $string['leadin'] . '</label><br /><span class="note">' . $string['leadinmsg'] . '</span>';
$value_editor = $question->get_leadin();
require 'details_editor.php';
?>