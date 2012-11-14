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
  function editProperties(paperID) {
    properties=window.open("<?php echo $cfg_root_path ?>/paper/properties.php?paperID=<?php echo $_GET['paperID']; ?>&caller=scheduling&noadd=y","properties","width=862,height=650,left="+(screen.width/2-431)+",top="+(screen.height/2-325)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    properties.focus();
  }
  
  function convtFormative() {
    notice=window.open("<?php echo $cfg_root_path ?>check_convert_formative.php?paperID=<?php echo $_GET['paperID']; ?>","notice","width=350,height=150,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-175,screen.height/2-75);
    if (window.focus) {
      notice.focus();
    }
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform">

<div class="submenuheading" id="papertasks"><?php echo $string['examtasks']; ?></div>

<table class="sidebar_tbl">
<tr><td class="ico"><a href="../paper/details.php?paperID=<?php echo $_GET['paperID']; ?>"><img src="../artwork/link.png" width="16" height="16" alt="" /></a></td><td class="menuitem"><a href="../paper/details.php?paperID=<?php echo $_GET['paperID']; ?>"><?php echo $string['jumptopaper']; ?></a></td></tr>
<tr><td class="ico"><img onclick="editProperties()" src="../artwork/properties_icon.gif" width="16" height="16" alt="" /></td><td class="menuitem"><a href="#" onclick="editProperties(); return false;"><?php echo $string['editproperties']; ?></a></td></tr>
<tr><td class="ico"><img onclick="convtFormative()" src="../artwork/formative_16.gif" width="16" height="16" alt="" /></td><td class="menuitem"><a href="#" onclick="convtFormative(); return false;"><?php echo $string['converttoformative']; ?></a></td></tr>
<tr><td class="ico"><a href="mailto:<?php echo $email . '?subject=Scheduling of ' . $paper_title . '&body=Dear ' . $title . ' ' . $surname . ','; ?>"><img src="../artwork/small_email.png" width="16" height="16" alt="" /></a></td><td class="menuitem"><a href="mailto:<?php echo $email . '?subject=Scheduling of ' . $paper_title . '&body=Dear ' . $title . ' ' . $surname . ','; ?>">Email <?php echo $title . ' ' . $surname; ?></a></td></tr>
</table>

</td></tr>
</table>

<input type="hidden" id="divID" name="divID" value="" />
</form>
</div>
