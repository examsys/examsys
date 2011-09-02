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
  function illegalChar(codeID) {
    if (codeID == 38) {
      alert("Character '&' illegal - please use alternative characters in title.");
    } else if (codeID == 63) {
      alert("Character '?' illegal - please use alternative characters in title.");
    } else if (codeID == 64) {
      alert("Character '@' illegal - please use alternative characters in title.");
    } else if (codeID == 94) {
      alert("Character '^' illegal - please use alternative characters in title.");
    } else if (codeID == 126) {
      alert("Character '~' illegal - please use alternative characters in title.");
    }
    event.returnValue = false;
  }
</script>

<div style="display:none; position:fixed; left:190px; top:80px; filter: progid:DXImageTransform.Microsoft.Shadow(direction=120,color=gray,strength=4)" id="copy_submenu" onclick="showCopyMenu(event);">
<form method="post" action="copy.php">
<br />
<br />
<br />

<table cellpadding="1" cellspacing="1" border="0" style="background-color:white; border:1px solid #C0C0C0; font-size:90%">
<tr>
<td colspan="5" style="background-color:#F2B100; height:5px"> </td>
</tr>
<tr><td colspan="5">&nbsp;</td></tr>
<tr>
<td style="text-align:right">Copy Name:</td>
<td colspan="4"><input type="text" name="new_paper" maxlength="255" value="<?php echo $paper_title; ?>" size="65" onkeypress="if (event.keyCode == 38 || event.keyCode == 63 || event.keyCode == 64 || event.keyCode == 94 || event.keyCode == 126)  illegalChar(event.keyCode);" />
<input type="hidden" name="paperID" value="<?php echo $paperID; ?>" />
<input type="hidden" name="module" value="<?php echo $module; ?>" />
<input type="hidden" name="folder" value="<?php echo $folder; ?>" />
</td>
</tr>
<tr><td colspan="5">&nbsp;</td></tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;<input type="radio" name="copytype" value="paperonly" />&nbsp;Paper Only</td>
<td>&nbsp;</td>
<td>&nbsp;<input type="radio" name="copytype" value="paperquestions" checked />&nbsp;Paper and Questions</td>
<td>&nbsp;</td>
</tr>
<tr>
<td style="width:110px" align="center">&nbsp;</td>
<td style="width:170px" align="center"><img src="../artwork/copy_paper_only.gif" width="120" height="130" border="0" /></td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td style="width:170px" align="center"><img src="../artwork/copy_paper_questions.gif" width="120" height="130" border="0" /></td>
<td style="width:110px" align="center">&nbsp;</td>
</tr>
<tr><td colspan="5">&nbsp;</td></tr>
<tr>
<td align="center" colspan="5"><input type="submit" style="width:100px" value="Copy Paper" name="submit" /></td>
</tr>
<tr><td colspan="5">&nbsp;</td></tr>
</table>

</form>
</div>