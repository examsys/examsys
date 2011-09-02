<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

// TODO: check what should happen for options on new questions

$show_notes = false;
?>
				<table id="q-details" class="form" summary="Edit question details">
					<tbody>
<?php
require_once 'detail_parts/details_theme_notes.php';
require_once 'detail_parts/details_media.php';
$mandatory_leadin = false;
$label_leadin = '<label for="leadin">Text</label>';
require_once 'detail_parts/details_leadin.php';
?>
					</tbody>
				</table>
        