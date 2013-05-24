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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/
  require '../include/load_config.php';
  require '../include/staff_student_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['credits']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {background-image:url('./background.jpg'); line-height:100%}
  </style>
</head>
<body>

<div style="position:absolute; top:12px; left:25px; width:300px">
  <img src="../artwork/r_logo.gif" width="56" height="60" alt="logo" border="0" style="float:left; padding-right:8px" />
  <div style="color:#1F497D; font-size:30pt; font-weight:bold; line-height:110%">Rogō</div>
  <div style="color:#1F497D; font-size:9pt">eAssessment Management System</div>
</div>

<div style="position:absolute; top:10px; left:510px"><img src="../artwork/black_uon_logo.png" width="167" height="70" alt="University of Nottingham" /></div>

<div style="position:absolute; top:60px; left:20px; font-size:75%; padding-top:10px; padding-right:2px; padding-left:10px">
<br />
<p>Rogō <?php echo $configObject->get('rogo_version') . ' ' . $string['msg']; ?></p>
<table cellpadding="0" cellspacing="0" border="0" style="width:650px">
<tr><td style="vertical-align:top; width:240px">
<strong><?php echo $string['designprogramming']; ?></strong><br />
Dr Simon Wilkinson<br />
Dr Rob Ingram<br />
Anthony Brown<br />
Simon Atack<br />
Ben Parish<br />
<br />
<strong>Flash</strong><br />
Fay Cross<br />
<br />
<strong>QTI</strong><br />
Adam Clarke<br />
<br />
<strong><?php echo $string['languagepacks']; ?></strong><br />
Dr Nikodem Miranowicz</td>

<td style="vertical-align:top">
<strong><?php echo $string['3rdparty']; ?></strong><br />
<table cellpaddding="0" cellspacing="0" border="0">
<tr><td style="width:110px"><?php echo $string['editor']; ?></td><td>TinyMCE 3.5.7 - <a href="http://tinymce.moxiecode.com/" target="_blank">tinymce.moxiecode.com</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td><?php echo $string['javascriptlibrary']; ?></td><td>jQuery 1.6.1 - <a href="http://jquery.com/" target="_blank">jquery.com/</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td><?php echo $string['mp3player']; ?></td><td>MP3 Player 0.6.0 - <a href="http://flash-mp3-player.net/" target="_blank">flash-mp3-player.net</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td><?php echo $string['videoplayer']; ?></td><td>FLV Player 1.6.0 - <a href="http://flv-player.net/players/maxi/" target="_blank">flv-player.net/players/maxi/</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td><?php echo $string['metadata']; ?></td><td>GetID3 1.8.5 - <a href="http://getid3.sourceforge.net/">getid3.sourceforge.net</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td style="vertical-align:top"><?php echo $string['graphics']; ?></td><td><a href="http://www.iconfinder.com/" target="_blank">www.iconfinder.com</a><br />
<a href="http://www.psdgraphics.com/" target="_blank">www.psdgraphics.com</a><br />
<a href="http://pixel-mixer.com/" target="_blank">pixel-mixer.com</a><br />
<a href="http://www.icons-land.com" target="_blank">www.icons-land.com</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td><?php echo $string['calculator']; ?></td><td><em><?php echo $string['calcmsg']; ?></em><br />
<a href="http://www.calculator.org/default.aspx" target="_blank">http://www.calculator.org</a></td></tr>
</table>

</td>
</tr>
</table>

<input type="button" value="OK" name="<?php echo $string['ok']; ?>" style="width:100px" onclick="window.close()" />
</div>


</body>
</html>