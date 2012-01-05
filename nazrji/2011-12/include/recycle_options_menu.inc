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
  function restoreItem() {
    document.location.href='restore_item.php?item_id=' + document.PapersMenu.itemID.value + '&type=' + document.PapersMenu.itemType.value;
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="PapersMenu" action="">
<br />
<table id="banktasks" cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold"><?php echo $string['recyclebintasks']; ?></td></tr>
<table cellpadding="0" cellspacing="0" border="0" id="menu1" style="margin-left:6px; font-size:90%">
<tr><td>

<table style="display:block" cellpadding="3" cellspacing="0" border="0" id="menu1a">
<tr><td><img src="../artwork/restore_grey.png" width="16" height="16" alt="" border="0" /></td><td class="greymenuitem"><?php echo $string['restorethisitem']; ?></td></tr>
</table>

<table style="display:none" cellpadding="3" cellspacing="0" border="0" id="menu1b">
<tr><td><a id="restore" onclick="restoreItem(); return false;" href="#"><img src="../artwork/restore.png" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a id="restore" onclick="restoreItem(); return false;" href="#"><?php echo $string['restorethisitem']; ?></a></td></tr>
</table>

</td></tr>
</table>

</td></tr>
</table>

<input type="hidden" name="itemID" value="" />
<input type="hidden" name="itemType" value="" />
<input type="hidden" name="lineNo" value="" />
<input type="hidden" name="oldLineNo" value="" />
</form>
</div>
