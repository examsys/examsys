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

<div style="z-index:20; display:none; background-color:white; position:fixed; left:190px; top:100px; box-shadow: 2px 2px 2px #808080" id="copy_submenu" onclick="showCopyMenu(true, event);">
<form method="post" action="../paper/copy.php">

<table cellpadding="1" cellspacing="1" border="0" style="background-color:white; border:1px solid #C0C0C0; font-size:90%">
<tr>
<td colspan="5" style="background-color:#F2B100; height:5px; padding:0px"> </td>
</tr>
<tr><td colspan="5">&nbsp;</td></tr>
<tr>
<td style="text-align:right"><?php echo $string['copyname']; ?></td>
<td colspan="4"><input type="text" name="new_paper" maxlength="255" value="<?php echo $paper_title; ?>" size="65" />
<input type="hidden" name="paperID" value="<?php echo $paperID; ?>" />
<input type="hidden" name="module" value="<?php echo $module; ?>" />
<input type="hidden" name="folder" value="<?php echo $folder; ?>" />
</td>
</tr>
<tr><td colspan="5">&nbsp;</td></tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;<input type="radio" name="copytype" value="paperonly" />&nbsp;<?php echo $string['paperonly']; ?></td>
<td>&nbsp;</td>
<td>&nbsp;<input type="radio" name="copytype" value="paperquestions" checked />&nbsp;<?php echo $string['paperandquestions']; ?></td>
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
<td align="center" colspan="5"><input type="submit" style="width:100px" value="<?php echo $string['copypaper']; ?>" name="submit" /></td>
</tr>
<tr><td colspan="5">&nbsp;</td></tr>
</table>

</form>
</div>