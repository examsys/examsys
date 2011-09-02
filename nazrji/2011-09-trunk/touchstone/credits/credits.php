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
  require '../config/config.inc.php';
  require '../include/staff_student_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Credits</title>
<style>
a {color:blue}
</style>
</head>
<body style="margin:0px; color:black; background-color:white; background-image:url('./background.jpg'); font-size:100%; font-family:Arial,sans-serif">

<div style="position:absolute; top:15px; left:20px; font-size:28pt; font-family:Arial,sans-serif; font-weight:bold; color:#003163">TouchStone</div>
<div style="position:absolute; top:52px; left:26px; font-size:10pt; font-family:Arial,sans-serif; font-weight:bold; color:#003163">Assessment Management System</div>
<div style="position:absolute; top:6px; left:250px; font-size:8pt; font-family:Arial,sans-serif"><img src="logo.gif" width="65" height="69" alt="logo" /></div>

<div style="position:absolute; top:10px; left:510px"><img src="uon_logo_blue.png" width="170" height="68" alt="The University of Nottingham" /></div>

<div style="position:absolute; top:60px; left:20px; font-size:75%; padding-top:10px; padding-right:2px; padding-left:5px">
<br />
<br />
<p>TouchStone <?php echo $ts_version; ?> is copyright &copy; 2011 and is held by the University of Nottingham. It is released under a <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">GPL v3.0</a> open source license whereby partners can modify, extend and contribute to the project.</p>
<br />
<table cellpadding="0" cellspacing="0" border="0" style="width:650px">
<tr><td style="vertical-align:top; width:250px">
<strong>Design &amp; Programming</strong><br />
Dr Simon Wilkinson<br />
Anthony Brown<br />
Dr Rob Ingram<br />
<br />
<strong>Flash</strong><br />
Fay Cross<br />
Heather Rai<br />
<br />
<strong>QTI</strong><br />
Adam Clarke<br />
<br />
<strong>Logo</strong><br />
Nuno Jorge</td>

<td style="vertical-align:top">
<strong>3rd Party subsystems</strong><br />
<table cellpaddding="0" cellspacing="0" border="0">
<tr><td style="width:110px">JavaScript Editor</td><td>TinyMCE 3.4.2 - <a href="http://tinymce.moxiecode.com/" target="_blank">tinymce.moxiecode.com</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>MP3 Player</td><td>MP3 Player 0.6.0 - <a href="http://flash-mp3-player.net/" target="_blank">flash-mp3-player.net</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>FLV Video Player</td><td>FLV Player 1.6.0 - <a href="http://flv-player.net/players/maxi/" target="_blank">flv-player.net/players/maxi/</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>Metadata</td><td>GetID3 1.8.5 - <a href="http://getid3.sourceforge.net/">getid3.sourceforge.net</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td style="vertical-align:top">Graphics</td><td><a href="http://www.iconfinder.com/" target="_blank">www.iconfinder.com</a><br />
<a href="http://www.psdgraphics.com/category/icons/" target="_blank">www.psdgraphics.com/category/icons/</a><br />
<a href="http://pixel-mixer.com/" target="_blank">pixel-mixer.com</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>Calculator</td><td><em>NOTE: Not open source but used with permission</em><br />
<a href="http://www.calculator.org/default.aspx" target="_blank">http://www.calculator.org</a></td></tr>
</table>

</td>
</tr>
</table>
<input type="button" value="OK" name="OK" style="width:100px" onclick="window.close()" />
</div>


</body>
</html>