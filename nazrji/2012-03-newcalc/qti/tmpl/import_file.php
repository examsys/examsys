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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html onscroll="scrollXY();" onclick="hideMenus();">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
	<title><?php echo $string['importfromqti'] ?></title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
	<link rel="icon" href="favicon.ico" type="image/x-icon"/>
	<link rel="P3Pv1" href="https://touchstone.nottingham.ac.uk/w3c/p3p.xml">
	<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
	<link rel="stylesheet" type="text/css" href="css/highlight.css" /> 
	<link rel="stylesheet" type="text/css" href="css/wizard.css" /> 
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
	<style type="text/css">
		body {background-color:white; color:black; font-family:Arial,sans-serif;margin:0px;}
		.divider {font-size:80%; padding-left:16px; padding-bottom:2px; font-weight:bold}
		a {color:black}
		a:hover {color:blue}
		.f {float:left; width:375px; padding-left:12px; font-size:80%}
		.recent {color:blue; font-size:90%}
		.param_section {margin:16px;padding:6px;border: 1px solid #dddddd;}

	.exp_table 
	{
		border-left: 1px solid #dddddd;
		border-top: 1px solid #dddddd;
	}

	.exp_table tr td,.exp_table tr th
	{
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
label.error {
  display: block;
  color: #f00;
}
	</style>
<script src="../js/staff_help.js" type="text/javascript"></script>
<script language="JavaScript">
  // Popup window code
  function newPopup(url) {
    notice=window.open(url,"properties","width=827,height=510,left="+(screen.width/2-325)+",top="+(screen.height/2-250)+",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }
$(function () { $('#file_form').validate(); });
</script>
</head>

<?php
require '../include/paper_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<?php
echo "<table class=\"header\">\n";
echo "<tr><th colspan=\"5\"><div class=\"breadcrumb\">";
if ($module != '') {
  echo '<a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module='.$module.'">'.$module.'</a>';
} elseif ($folder != '') {
  echo '<a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder='.$folder.'">'.$folder_name.'</a>';
} else {
  echo '<a href="../staff/index.php">' . $string['home'] . '</a>';
}
echo "</div><div onclick=\"qOff()\" style=\"font-size:220%; font-weight:bold; margin-left:10px\">$paper_title</div>";
echo "</th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(1); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></th></tr>\n";
echo "<tr><th colspan=\"6\" class=\"bevel\"></th></tr>";
echo "</table>";
?>
<br/>
<br/>
<br/>
<br/>
<div style="margin:9px;" align="center">

<table border="0" cellpadding="0" cellspacing="0" style="width:500px; border:1px solid #95AEC8; text-align:left"> 
	<tr> 
		<td style="background-color:white; width:55px"><img src="../artwork/ims_logo.png" width="47" height="44" alt="IMS Logo" /></td><td style="width:445px"><span style="font-family:Arial,sans-serif; font-size:16pt; font-weight:bold; color:#5582D2">QTI <?php echo $string['import'] ?></span></td> 
	</tr> 
	<tr> 
		<td align="left" style="background-color:#DFE8FF" colspan="2"> 
			
			<div style="padding-top:16px;padding-left:16px;padding-right:16px;">
				<form id="file_form" action="import.php?<?php echo $_SERVER['QUERY_STRING'];?>" method="post" enctype="multipart/form-data">
				<table width="100%" cellspacing="0" cellpadding="10">
					<tr>
						<td>
							<strong><?php echo $string['file'] ?></strong>&nbsp;<input type="file" size="40" name="file" id="file" class="required" />
							<input type="hidden" name="paperID" id="paperID" value="<?php echo $paper ?>" />
              <input type="hidden" name="module" id="module" value="<?php echo $module ?>" />
						</td>
					</tr>
					<tr>
						<td style="text-align:center">
							<input type="submit" name="submit" value="<?php echo $string['import2'] . ' ' . $string['file'] ?>" style="width:100px" />&nbsp;<input type="button" name="cancel" value="<?php echo $string['cancel'] ?>" style="width:100px" onclick="javascript:history.back()" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<input type="button" name="help" value="<?php echo $string['help'] ?>" style="width:100px" onclick="javascript:launchHelp(224)" />
						</td>
					</tr>
				</table>
				</form>
			</div>
		</td>
	</tr>
</table>

</div>

</div>
</body>

</html>
