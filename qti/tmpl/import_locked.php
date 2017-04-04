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
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */
require_once '../include/staff_auth.inc';
?>
<!DOCTYPE html>
<html onscroll="scrollXY();" onclick="hideMenus();">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
	<title>Rogō Export to QTI</title>
  
	<link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
	<style type="text/css">
		.divider {font-size:80%; padding-left:16px; padding-bottom:2px; font-weight:bold}
		a {color:black}
		a:hover {color:blue}
		.f {float:left; width:375px; padding-left:12px; font-size:80%}
		.recent {color:blue; font-size:90%}
		.param_section {margin:16px;padding:6px;border: 1px solid #dddddd;}

	.exp_table {
		border-left: 1px solid #dddddd;
		border-top: 1px solid #dddddd;
	}

	.exp_table tr td,.exp_table tr th	{
		border-bottom: 1px solid #dddddd;
		border-right: 1px solid #dddddd;
		padding: 1px;
		font-size:80%;
	}
	
	.paper_head {
		font-size:140%;
	}
	
	.screen_head {
		font-size:120%;
	}
	</style>
  
	<script type="text/javascript" src="js/mootools-1.2.4.js"></script> 
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script>
    // Popup window code
    function newPopup(url) {
      notice=window.open(url,"properties","width=827,height=510,left="+(screen.width/2-325)+",top="+(screen.height/2-250)+",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        notice.focus();
      }
    }
  </script>
</head>

<?php
require '../include/paper_options.php';
?>
<div id="content">
<?php
echo "<table class=\"header\">\n";
echo "<tr><th colspan=\"5\"><div class=\"breadcrumb\">";
if ($module != '') {
  echo '<a href="../index.php">Home</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../module/index.php?module='.$module.'">'.$module.'</a>';
} elseif ($folder != '') {
  echo '<a href="../index.php">Home</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/index.php?folder='.$folder.'">'.$folder_name.'</a>';
} else {
  echo '<a href="../index.php">Home</a>';
}
echo "</div><div onclick=\"qOff()\" style=\"font-size:220%; font-weight:bold; margin-left:10px\">$paper_title</div>";
echo "</th><th style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(1); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n";
echo "</table>";
?>
<br/>
<br/>
<br/>
<br/>
<div style="margin:9px;" align="center">

<table border="0" cellpadding="0" cellspacing="0" style="width:600px; border:1px solid #5582D2; text-align:left"> 
	<tr> 
		<td style="background-color:white; width:55px"><img src="../artwork/ims_logo.png" width="47" height="44" alt="IMS Logo" /></td><td style="width:445px"><span style="font-size:16pt; font-weight:bold; color:#5582D2"><?php echo $string['qtiimport'] ?></span></td>
	</tr> 
	<tr> 
		<td align="left" style="background-color:#fff2a4" colspan="2"> 
			
			<div style="padding-top:16px;padding-left:16px;padding-right:16px;">
          <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
			     <tr><td colspan="2" style="height:32px; text-align:right"><img src="../artwork/paper_locked_padlock.png" width="19" height="24" alt="Locked" />&nbsp;&nbsp;</td><td colspan="7" style="height:32px; vertical-align:middle;"><strong><?php echo $string['paperlocked'] ?></strong>&nbsp;&nbsp;&nbsp;<?php echo $string['paperlockedmsg'] ?><a href="#" class="blacklink" onclick="launchHelp(189); return false;">Click for more details.</a></td></tr>
			    </table>
          <br/>
      </div>
		</td>
	</tr>
</table>

</div>

</div>
</body>

</html>
