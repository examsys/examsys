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
<script language="JavaScript">
  function viewDetails() {
    window.location = "scheduling_details.php?paperID=" + document.getElementById('divID').value;
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform">
<br />

<table style="margin-left:9px; font-size:90%; display:block" cellpadding="3" cellspacing="0" border="0" id="menu1a">
<tr><td class="ico"><img src="../artwork/information_icon_grey.gif" width="16" height="16" alt="" border="0" /></td><td class="greymenuitem"><?php echo $string['viewdetails']; ?></td></tr>
</table>

<table style="margin-left:9px; font-size:90%; display:none" cellpadding="3" cellspacing="0" border="0" id="menu1b">
<tr><td class="ico"><img onclick="viewDetails()" src="../artwork/information_icon.gif" width="16" height="16" alt="" /></td><td class="menuitem"><a href="#" onclick="viewDetails(); return false;"><?php echo $string['viewdetails']; ?></a></td></tr>
</table>

</td></tr>
</table>

<input type="hidden" id="divID" name="divID" value="" />
</form>
</div>
