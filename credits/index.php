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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
  require '../include/load_config.php';
  require '../include/staff_student_auth.inc';
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
	
  <title><?php echo $string['credits']; ?></title>
	
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/rogo_logo.css" />
  <style type="text/css">
    html, body {height: 100%}
		td {vertical-align:top}
    .ok {position:absolute; bottom: -50px}
  </style>
</head>
<body>

<div style="position:absolute; top:12px; left:25px; width:300px">
  <img src="../artwork/r_logo.gif" alt="logo" class="logo_img" />
  <div class="logo_lrg_txt">Rog&#333;</div>
  <div class="logo_small_txt"><?php echo $string['eassessmentmanagementsystem'] ?></div>
</div>

<div style="position:absolute; top:60px; left:20px; font-size:75%; padding-top:10px; padding-right:2px; padding-left:10px">
<br />
<p>Rog&#333; <?php echo $configObject->get_setting('core', 'rogo_version') . ' ' . $string['msg']; ?></p>
<table cellpadding="0" cellspacing="0" border="0" style="width:660px">
<tr><td style="width:240px">
<strong><?php echo $string['designprogramming'] ?></strong><br />
Dr Simon Wilkinson<br />
Dr Rob Ingram<br />
Anthony Brown<br />
Simon Atack<br />
Dr Joseph Baxter<br />
Neill Magill<br />
Barry Oosthuizen<br />
Ben Parish<br />
Josef Martiňák<br />
<br />
<strong>HTML5</strong><br />
Dr Nikodem Miranowicz<br />
<br />
<strong><?php echo $string['languagepacks'] ?></strong><br />
1st Faculty of Medicine, Charles University (Prague)<br />
<br />
<strong>QTI</strong><br />
Adam Clarke<br />
</td>

<td>
<strong><?php echo $string['3rdparty'] ?></strong><br />
<table cellpaddding="0" cellspacing="0" border="0">
<tr><td style="width:120px"><?php echo $string['editor']; ?></td><td>TinyMCE 3.5.11 - <a href="http://tinymce.moxiecode.com/" target="_blank">tinymce.moxiecode.com</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td><?php echo $string['javascriptlibrary']; ?></td><td>jQuery 1.11.1 - <a href="http://jquery.com" target="_blank">jquery.com</a><br />
jQuery UI 1.10.4 - <a href="http://jqueryui.com" target="_blank">http://jqueryui.com</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td><?php echo $string['videoplayer']; ?></td><td>FLV Player 1.6.0 - <a href="http://flv-player.net/players/maxi/" target="_blank">flv-player.net/players/maxi/</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td><?php echo $string['graphics']; ?></td><td><a href="http://www.iconfinder.com/" target="_blank">www.iconfinder.com</a><br />
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

<input type="button" value="OK" name="<?php echo $string['ok'] ?>" class="ok" onclick="window.close()" />
</div>


</body>
</html>