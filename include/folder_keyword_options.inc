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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  if (isset($_GET['module'])) {
    $module = $_GET['module'];
  } else {
    $module = '';
  }
?>
<script language="JavaScript">
  function newKeyword() {
    keywordwin=window.open("/folder/new_keyword.php?module=<?php echo $module; ?>","keywords","width=350,height=120,left="+(screen.width/2-175)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      keywordwin.focus();
    }
  }

  function editKeyword() {
    keywordwin=window.open("/folder/edit_keyword.php?keywordID=" + document.getElementById('keywordID').value + "&module=<?php echo $module; ?>","keywords","width=350,height=120,left="+(screen.width/2-175)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      keywordwin.focus();
    }
  }

  function deleteKeyword() {
    keywordwin=window.open("/delete/check_delete_team_keyword.php?keywordID=" + document.getElementById('keywordID').value + "&module=<?php echo $module; ?>","keywords","width=350,height=120,left="+(screen.width/2-175)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      keywordwin.focus();
    }
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform">
<br />

<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%">
<tr><td>

<table style="display:block" cellpadding="3" cellspacing="0" border="0" id="menu1a">
<tr><td><img onclick="newKeyword()" src="../artwork/key.png" width="16" height="16" alt="key" border="0" /></a></td><td class="menuitem"><a href="#" onclick="newKeyword()"><?php echo $string['createnewkeyword']; ?></a></td></tr>
<tr><td><img src="../artwork/edit_grey.png" width="16" height="16" alt="" border="0" /></td><td class="greymenuitem"><?php echo $string['editkeyword']; ?></td></tr>
<tr><td><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="" border="0" /></td><td class="greymenuitem"><?php echo $string['deletekeyword']; ?></td></tr>
<?php
  if (isset($keyword_list) and count($keyword_list) > 0) {
    echo "<tr><td><a href=\"export_keywords.php?module=$module\"><img src=\"../artwork/export_16.gif\" width=\"16\" height=\"16\" alt=\"Export\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"export_keywords.php?module=$module\">" . $string['exportkeywords'] . "</a></td></tr>\n";
  } else {
    echo "<tr><td><img src=\"../artwork/export_grey_16.gif\" width=\"16\" height=\"16\" alt=\"Export\" border=\"0\" /></td><td class=\"greymenuitem\">" . $string['exportkeywords'] . "</td></tr>\n";
  }
?>
<tr><td><a href="import_keywords.php?module=<?php echo $module; ?>"><img src="../artwork/import_16.gif" width="16" height="16" alt="Import" border="0" /></a></td><td class="menuitem"><a href="import_keywords.php?module=<?php echo $module; ?>"><?php echo $string['importkeywords']; ?></a></td></tr>
</table>

<table style="display:none" cellpadding="3" cellspacing="0" border="0" id="menu1b">
<tr><td><img onclick="newKeyword()" src="../artwork/key.png" width="16" height="16" alt="key" border="0" /></a></td><td class="menuitem"><a href="#" onclick="newKeyword()"><?php echo $string['createnewkeyword']; ?></a></td></tr>
<tr><td><img onclick="editKeyword()" src="../artwork/edit.png" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="editKeyword(); return false;"><?php echo $string['editkeyword']; ?></a></td></tr>
<tr><td><img onclick="deleteKeyword()" src="../artwork/red_cross.png" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="deleteKeyword(); return false;"><?php echo $string['deletekeyword']; ?></a></td></tr>
<tr><td><a href="export_keywords.php?module=<?php echo $module; ?>"><img src="../artwork/export_16.gif" width="16" height="16" alt="Export" border="0" /></a></td><td class="menuitem"><a href="export_keywords.php?module=<?php echo $module; ?>"><?php echo $string['exportkeywords']; ?></a></td></tr>
<tr><td><a href="import_keywords.php?module=<?php echo $module; ?>"><img src="../artwork/import_16.gif" width="16" height="16" alt="Import" border="0" /></a></td><td class="menuitem"><a href="import_keywords.php?module=<?php echo $module; ?>"><?php echo $string['importkeywords']; ?></a></td></tr>
</table>
</td></tr>
</table>

</td></tr>
</table>

<input type="hidden" name="id" id="keywordID" value="" />
<input type="hidden" name="oldID" value="" />
</form>
</div>
