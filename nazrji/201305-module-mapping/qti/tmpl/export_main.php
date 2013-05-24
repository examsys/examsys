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
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['exporttoqti'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />

  <style type="text/css">
    .divider {font-size:80%; padding-left:16px; padding-bottom:2px; font-weight:bold}
    a {color:black}
    a:hover {color:blue}
    .f {float:left; width:375px; padding-left:12px; font-size:80%}
    .recent {color:blue; font-size:90%}
    .param_section {margin:16px;padding:6px;border: 1px solid #dddddd;}
    .exp_table  {border-left: 1px solid #dddddd; border-top: 1px solid #dddddd}
    .exp_table tr td,.exp_table tr th {border-bottom: 1px solid #dddddd; border-right: 1px solid #dddddd; padding: 1px; font-size:80%}
    .paper_head {font-size:140%}
    .screen_head {font-size:120%}
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
$export_result = $result;
require '../include/paper_options.inc';
$result = $export_result;
?>
<div id="content" class="content">

<?php

$files = array();
$tozip = array();

if (count($result['save']['data']->files) > 1) {

  foreach ($result['save']['data']->files as $title => $file) {
    $tozip[] = $file;
  }

  $zip = new ZipArchive;
  $res = $zip->open($base_dir.$dir.'/export.zip', ZipArchive::CREATE);
  if ($res === true) {
    foreach ($tozip as $file) {
      if (file_exists($base_dir.$dir.'/'.$file->filename)) {
        $zip->addFile($base_dir.$dir.'/'.$file->filename, $file->filename);
      }
    }
    $zip->close();
    $files[] = new ST_File("export.zip", $paper_row['paper_title'], $dir, 'zip');
  }
} else {
  $files = $result['save']['data']->files;
}

$qti_ver = ($dest == "qti12") ? "v1.2.1" : "v2.1";

echo "<table class=\"header\">\n";
echo "<tr><th colspan=\"5\"><div class=\"breadcrumb\">";
if ($module != '') {
  echo '<a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module='.$module.'">'.$module.'</a>';
} elseif ($folder != '') {
  echo '<a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder='.$folder.'">'.$folder_name.'</a>';
} else {
  echo '<a href="../staff/index.php">' . $string['home'] . '</a>';
}
echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_row['paper_title'] . '</a></div>';

echo "<div onclick=\"qOff()\" style=\"font-size:220%; font-weight:bold; margin-left:10px\">" . $string['exporttoqti'] . "</div>";
echo "</th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(1); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></th></tr>\n";
echo "<tr><th colspan=\"6\" class=\"bevel\"></th></tr>";
echo "</table>";
?>

<br />
<table border="0" cellpadding="0" cellspacing="0" width="500" class="dialog_border" style="text-align:left">
	<tr>
		<td class="dialog_header" style="width:55px"><img src="../artwork/ims_logo.png" width="47" height="44" alt="IMS Logo" /></td><td class="dialog_header" style="width:445px"><?php echo $string['exporttoqti']; ?></td>
	</tr>
	<tr>
		<td class="dialog_body" colspan="2">
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php printf($string['exportsready'], $qti_ver) ?></div>
			<?php foreach ($files as $file) : ?>
				<?php $path = $file->path; ?>
				<div style="margin-left:25px; line-height:150%"><img src="../artwork/import_16.gif" width="16" height="16" alt="bullet" />&nbsp;&nbsp;
					<strong><a href="download.php?file=<?php echo(urlencode($file->filename)) ?>&path=<?php echo(urlencode($file->path)) ?>&title=<?php echo(urlencode($file->title)) ?>"><?php echo $string['download'] . ' ' . $file->title ?>.xml</a></strong>
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

</body>
</html>
