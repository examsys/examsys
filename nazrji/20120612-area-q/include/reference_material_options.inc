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

?>
<div id="left-sidebar" class="sidebar">
<form name="myform">
<br />

<table style="margin-left:9px; font-size:90%; display:block" cellpadding="3" cellspacing="0" border="0" id="menu1a">
<tr><td class="ico"><a href="add_ref_material.php?module=<?php echo $_GET['module']; ?>"><img src="../artwork/ref_16.png" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="add_ref_material.php?module=<?php echo $_GET['module']; ?>"><?php echo $string['creatematerial']; ?></a></td></tr>
<tr><td class="ico"><img src="../artwork/edit_grey.png" width="16" height="16" alt="" border="0" /></td><td class="greymenuitem"><?php echo $string['editmaterial']; ?></td></tr>
<tr><td class="ico"><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="" border="0" /></td><td class="greymenuitem"><?php echo $string['deletematerial']; ?></td></tr>
</table>

<table style="margin-left:9px; font-size:90%; display:none" cellpadding="3" cellspacing="0" border="0" id="menu1b">
<tr><td class="ico"><a href="add_ref_material.php"><img src="../artwork/ref_16.png" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="add_ref_material.php"><?php echo $string['creatematerial']; ?></a></td></tr>
<tr><td class="ico"><img onclick="editReference()" src="../artwork/edit.png" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="editReference(); return false;"><?php echo $string['editmaterial']; ?></a></td></tr>
<tr><td class="ico"><img onclick="deleteReference()" src="../artwork/red_cross.png" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="deleteReference(); return false;"><?php echo $string['deletematerial']; ?></a></td></tr>
</table>

<input type="hidden" id="divID" name="divID" value="" />
<input type="hidden" id="oldID" name="oldID value="" />
</form>
</div>
