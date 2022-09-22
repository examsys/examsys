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
 * Sidebar menu for the recycle bin.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

?>

<div id="left-sidebar" class="sidebar">
<form name="PapersMenu" action="" autocomplete="off">
<div id="menu1a">
    <div class="grey menuitem"><img class="sidebar_icon" src="../artwork/import_grey_16.gif" alt="" /><?php echo $string['restorethisitem'] ?></div>
</div>

<div style="display:none" id="menu1b">
    <div class="menuitem" id="restore"><a onclick="return false" href="#"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="" /><?php echo $string['restorethisitem'] ?></a></div>
</div>

<input type="hidden" id="itemID" name="itemID" value="" />
</form>
</div>
