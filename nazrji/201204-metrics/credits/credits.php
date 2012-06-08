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
  require '../config/config.inc.php';
  require '../include/staff_student_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title>Credits</title>
<style type="text/css">
a {color:blue}
</style>
</head>
<body style="margin:0px; color:black; background-color:white; background-image:url('./background.jpg'); font-size:100%; font-family:Arial,sans-serif">

<div style="position:absolute; top:20px; left:25px"><img src="../artwork/rogo_logo.gif" width="137" height="61" alt="logo" border="0" /></div>

<div style="position:absolute; top:10px; left:510px"><img src="../artwork/black_uon_logo.png" width="167" height="70" alt="University of Nottingham" /></div>

<div style="position:absolute; top:60px; left:20px; font-size:75%; padding-top:10px; padding-right:2px; padding-left:5px">
<br />
<br />
<p>Rogō <?php echo $rogo_version; ?> is copyright &copy; 2012 and is held by the University of Nottingham. It is released under a <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">GPL v3.0</a> open source license whereby partners can modify, extend and contribute to the project.</p>
<br />
<table cellpadding="0" cellspacing="0" border="0" style="width:650px">
<tr><td style="vertical-align:top; width:250px">
<strong>Design &amp; Programming</strong><br />
Dr Simon Wilkinson<br />
Dr Rob Ingram<br />
Anthony Brown<br />
Simon Atack<br />
<br />
<strong>Flash</strong><br />
Fay Cross<br />
<br />
<strong>QTI</strong><br />
Adam Clarke<br />
<br />
<strong>Language Packs</strong><br />
Dr Nikodem Miranowicz</td>

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
<a href="http://pixelmixer.ru/" target="_blank">pixel-mixer.com</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>Calculator</td><td><em>NOTE: Not open source but used with permission</em><br />
<a href="http://www.calculator.org/default.aspx" target="_blank">http://www.calculator.org</a></td></tr>
</table>

</td>
</tr>
</table>
<br />
<br />
<input type="button" value="OK" name="OK" style="width:100px" onclick="window.close()" />
</div>


</body>
</html>