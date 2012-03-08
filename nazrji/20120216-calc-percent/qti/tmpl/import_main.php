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
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
	<title><?php echo $string['importfromqti'] ?></title>
	<link rel="stylesheet" type="text/css" href="../css/submenu.css" />

	<script type="text/javascript" src="/qti/js/mootools-1.2.4.js"></script> 

	<style type="text/css">
		body {background-color:white; color:black; font-family:Arial,sans-serif;margin:0px}
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

	</style>
  <script language="JavaScript">
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
// paper_options.inc modifies result!  Store it temporarily
$import_result = $result;
require '../include/paper_options.inc';
$result = $import_result;
?>
<div id="content" class="content" style="font-size:80%">

<?php
echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
echo "<tr><td style=\"background-color:#F1F5FB\" colspan=\"5\"><div class=\"breadcrumb\">";
if ($module != '') {
  echo '<a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module='.$module.'">'.$module.'</a>';
} elseif ($folder != '') {
  echo '<a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder='.$folder.'">'.$folder_name.'</a>';
} else {
  echo '<a href="../staff/index.php">' . $string['home'] . '</a>';
}
echo "</div><div onclick=\"qOff()\" style=\"font-size:220%; font-weight:bold; margin-left:10px\">$paper_title</div>";
echo "</td><td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(1); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n";
echo "<tr><td colspan=\"6\" style=\"height:3px\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>";
echo "</table>";
?>
<br/>
<br/>
<br/>
<br/>
<div style="margin:9px" align="center">
<table border="0" cellpadding="0" cellspacing="0" style="width:500px; border:1px solid #5582D2; text-align:left"> 
	<tr> 
		<td style="background-color:white; width:55px"><img src="../artwork/ims_logo.png" width="47" height="44" alt="IMS Logo" /></td><td style="width:445px"><span style="font-family:Arial,sans-serif; font-size:16pt; font-weight:bold; color:#5582D2">QTI Import</td> 
	</tr> 
	<tr> 
		<td align="left" style="background-color:#DFE8FF" colspan="2"> 
<?php
$total = count($result['load']['data']->questions);
$bad = count($result['load']['errors']);
$num_load_errors = (isset($result['load']['errors'][0])) ? count($result['load']['errors'][0]) : 0;
$num_save_errors = (isset($result['save']['errors'][0])) ? count($result['save']['errors'][0]) : 0;
if (isset($result['load']['errors'][0])) $bad--;
?>
<?php if ($num_load_errors > 0) : ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold; color: red"><?php echo $string['qtiimporterror'] ?>:</div>
<?php foreach ($result['load']['errors'][0] as $error) : ?>
			<div style="margin-left:25px; line-height:150%; color: red"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet">&nbsp;&nbsp;
				<?php echo $error ?>
			</div>
<?php endforeach; ?>
<?php elseif ($num_save_errors > 0) : ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold; color: red"><?php echo $string['qtiimporterror'] ?>:</div>
<?php foreach ($result['save']['errors'][0] as $error) : ?>
			<div style="margin-left:25px; line-height:150%; color:red"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet" />&nbsp;&nbsp;
				<?php echo $error ?>
			</div>
<?php endforeach; ?>
<?php else : ?>
<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php echo $string['qtiimported'] ?></div>
<?php if ($num_save_errors > 0 || $num_load_errors > 0) : ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold; color:red"><?php echo $string['questionproblems'] ?></div>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php echo sprintf($string['hadproblemsimporting'], $bad, $total) ?></div>
<?php else : ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php echo sprintf($string['importedquestions'], $total) ?></div>
<?php endif; ?>
<?php endif; ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php echo $string['moreinformation'] ?></div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet">&nbsp;&nbsp;
				<a href="Javascript:newPopup('imports/<?php echo $dir ?>/result.html');"><?php echo $string['viewdetails'] ?></a>
			</div>
<?php if ($show_debug) : ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php echo $string['debuginformation'] ?></div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet">&nbsp;&nbsp;
				<a href="Javascript:newPopup('imports/<?php echo $dir ?>/debug_load.html');"><?php echo $string['loadingdebug'] ?></a>
			</div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet">&nbsp;&nbsp;
				<a href="Javascript:newPopup('imports/<?php echo $dir ?>/debug_int.html');"><?php echo $string['intermediateformatdebug'] ?></a>
			</div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet">&nbsp;&nbsp;
				<a href="Javascript:newPopup('imports/<?php echo $dir ?>/debug_save.html');"><?php echo $string['savingdebug'] ?></a>
			</div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet">&nbsp;&nbsp;
				<a href="Javascript:newPopup('imports/<?php echo $dir ?>/debug_res.html');"><?php echo $string['generaldebuginfo']?></a>
			</div>
<?php endif; ?>
			<br />
      <div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet">&nbsp;&nbsp;
        <a href="../paper/details.php?paperID=<?php echo $paper ?>&module=<?php echo $module ?>"><?php echo $string['backtopaper'] ?></a>
      </div>
      <br />
		</td>
	</tr>
</table>
</div>
</div>
</body>
</html>