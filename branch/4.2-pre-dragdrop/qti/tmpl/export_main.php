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
	<title>TouchStone Export to QTI</title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
	<link rel="icon" href="favicon.ico" type="image/x-icon"/>
	<link rel="stylesheet" type="text/css" href="../css/submenu.css" />

	<style>
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

	</style>
<script language="JavaScript">
  function move_in(img_name) {
    document[img_name].src=onImg.src;
  }

  function move_out(img_name) {
    document[img_name].src=offImg.src;
  }
  
  onImg = new Image;
  onImg.src = '../artwork/up_folder_icon_on.gif';
  offImg = new Image;
  offImg.src = '../artwork/up_folder_icon_off.gif';

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
$export_result = $result;
require '../include/paper_options.inc';
$result = $export_result;
?>
<div id="content" class="content" style="font-size:80%">

<?php

$files = array();
$tozip = array();

if (count($result['save']['data']->files) > 1) {

  foreach ($result['save']['data']->files as $title => $file) {
    //if ($dest == "qti12" && ($file->type == "xml" || $file->type == "question" || $file->type == "test" || $file->type == "manifest"))
    //	$files[] = $file;

    $tozip[] = $file;
  }

  $zip = new ZipArchive;
  $res = $zip->open($base_dir.$dir.'/export.zip', ZipArchive::CREATE);
  if ($res === TRUE) {
    foreach ($tozip as $file) {
      //echo "Adding : " . $base_dir.$dir.'/'.$file->filename . "<br />";
      if (file_exists($base_dir.$dir.'/'.$file->filename)) {
        $zip->addFile($base_dir.$dir.'/'.$file->filename, $file->filename);
      } else {
        //echo "File doesnt exist<br />";
        }
    }
    $zip->close();
    $files[] = new ST_File("export.zip", $paper_row['paper_title'], $dir, 'zip');
    //echo 'ok';
    } else {
    //echo 'failed';
    }

} else {
  $files = $result['save']['data']->files;
}

?>

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
echo "</td><td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(1); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"".$string['help']."\" border=\"0\" /></a></td></tr>\n";
echo "<tr><td colspan=\"6\" style=\"height:3px\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>";
echo "</table>";
?>

<div style="margin:9px;" align="center">

<table border="0" cellpadding="4" cellspacing="0" width="500" style="border:1px solid #5582D2"> 
	<tr> 
    <?php $qti_ver = ($dest == "qti12") ? "v1.2.1" : "v2.1"; ?>
		<td valign="middle" align="left" style="background-color:white"><img src="../artwork/statistics_menu_icon.gif" width="36" height="32" alt="Icon" />&nbsp;&nbsp;<span style="font-family:Arial,sans-serif; font-size:16pt; font-weight:bold; color:#5582D2"><?php printf($string['qtiexport'], $qti_ver) ?></span></td> 
	</tr> 
	<tr> 
		<td align="left" style="background-color:#DFE8FF">
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php printf($string['exportsready'], $qti_ver) ?></div>
			<?php foreach ($files as $file) : ?>
				<?php $path = $file->path; ?>
				<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet" />&nbsp;&nbsp;
					<?php echo $string['download'] ?> : <a href="download.php?file=<?php echo(urlencode($file->filename)) ?>&path=<?php echo(urlencode($file->path)) ?>&title=<?php echo(urlencode($file->title)) ?>"><?php echo $file->title ?></a>
				</div>
			<?php endforeach; ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php echo $string['moreinformation']; ?></div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet" />&nbsp;&nbsp;
				<a href="Javascript:newPopup('exports/<?php echo $path ?>/result.html');"><?php echo $string['viewdetails']; ?></a>
			</div>
<?php if ($show_debug) : ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php echo $string['debuginformation']; ?></div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet" />&nbsp;&nbsp;
				<a href="Javascript:newPopup('exports/<?php echo $path ?>/debug_load.html');"><?php echo $string['loadingdebug']; ?></a>
			</div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet" />&nbsp;&nbsp;
				<a href="Javascript:newPopup('exports/<?php echo $path ?>/debug_int.html');"><?php echo $string['intermediateformatdebug']; ?></a>
			</div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet" />&nbsp;&nbsp;
				<a href="Javascript:newPopup('exports/<?php echo $path ?>/debug_save.html');"><?php echo $string['savingdebug']; ?></a>
			</div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet" />&nbsp;&nbsp;
				<a href="Javascript:newPopup('exports/<?php echo $path ?>/debug_res.html');"><?php echo $string['generaldebuginfo']; ?></a>
			</div>
<?php endif; ?>
			<br />
		</td>
	</tr>
</table>

</div>

</td></tr></table>

</div>
</body>

</html>
