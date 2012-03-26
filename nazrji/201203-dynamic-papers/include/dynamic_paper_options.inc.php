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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once '../classes/dateutils.class.php';
$mode = (isset($mode)) ? $mode : 'session';
?>
<div id="left-sidebar" class="sidebar">
  <h2><?php echo $string['mapping'] ?></h2>
  <ul id="break_controls" class="menu_list">
<?php
if ($mode == 'session') {
?>
    <li id="map_qns" class="greymenuitem"><a href="#"><?php echo $string['mapquestions'] ?></a></li>
<?php
} else {
?>
    <li id="add_qns" class="menuitem"><a href="#"><?php echo $string['addquestions'] ?></a></li>
    <li id="map_qns" class="greymenuitem"><a href="#"><?php echo $string['mapsessions'] ?></a></li>
<?php
}
?>
  </ul>
</div>
