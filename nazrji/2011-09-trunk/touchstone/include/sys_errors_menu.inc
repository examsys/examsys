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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

?>
<script language="JavaScript">
  function displayDetails() {
    details=window.open("/touchstone/admin/sys_error_details.php?errorID=" + document.getElementById('errorID').value + "","properties","width=900,height=515,left="+(screen.width/2-450)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      details.focus();
    }
  }
</script>

<div id="left-sidebar" class="sidebar">

<br />
<table cellpadding="0" cellspacing="0" border="0" width="185">
<tr><td style="margin:4px">
<tr><td>

<table cellpadding="3" cellspacing="0" border="0" style="margin-left:6px; font-size:90%">
<tr><td><a onclick="displayDetails(); return false;"><img src="../artwork/information_icon.gif" width="16" height="16" alt="home" border="0" /></a></td><td class="menuitem"><a onclick="displayDetails(); return false;"><?php echo $string['displaydetails']; ?></a></td></tr>
</table>

</td></tr>
</table>

<form name="errorsMenu">
<input type="hidden" name="errorID" id="errorID" value="" />
</form>

</div>