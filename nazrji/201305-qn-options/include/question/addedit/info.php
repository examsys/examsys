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

$show_notes = false;
?>
				<table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
					<tbody>
<?php
require_once 'detail_parts/details_theme_notes.php';
require_once 'detail_parts/details_media.php';
$mandatory_leadin = false;
$label_leadin = "<label for=\"leadin\">{$string['text']}</label>";
require_once 'detail_parts/details_leadin.php';
if (count($question->options) > 0) {
  $option = reset($question->options);
  $option_id = $option->id;
} else {
  $option_id = -1;
}
?>
					</tbody>
				</table>
        <input name="optionid1" value="<?php echo $option_id ?>" type="hidden" />
        