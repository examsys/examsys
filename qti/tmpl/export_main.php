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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
require_once '../include/staff_auth.inc';
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['exporttoqti'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />

  <style type="text/css">
    .divider {font-size:80%; padding-left:16px; padding-bottom:2px; font-weight:bold}
    .f {float:left; width:375px; padding-left:12px; font-size:80%}
    .recent {color:blue; font-size:90%}
    .param_section {margin:16px;padding:6px;border: 1px solid #dddddd;}
    .exp_table  {border-left: 1px solid #dddddd; border-top: 1px solid #dddddd}
    .exp_table tr td,.exp_table tr th {border-bottom: 1px solid #dddddd; border-right: 1px solid #dddddd; padding: 1px; font-size:80%}
    .paper_head {font-size:140%}
    .screen_head {font-size:120%}
  </style>
	
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
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
// paper_options.php modifies result!  Store it temporarily
$export_result = $result;

require '../include/paper_options.php';
require '../include/toprightmenu.inc';

echo draw_toprightmenu();

$result = $export_result;
?>
<div id="content">

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
    $files[] = new ST_File("export.zip", $paper_title, $dir, 'zip');
  }
} else {
  $files = $result['save']['data']->files;
}

$qti_ver = ($dest == "qti12") ? "v1.2.1" : "v2.1";

echo "<div class=\"head_title\">\n";
echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
echo "<div class=\"breadcrumb\">";
if ($module != '') {
  echo '<a href="../index.php">' . $string['home'] . '</a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $module . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
} elseif ($folder != '') {
  echo '<a href="../index.php">' . $string['home'] . '</a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $folder . '">' . $folder_name . '</a>';
} else {
  echo '<a href="../index.php">' . $string['home'] . '</a>';
}
echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_title . '</a></div>';

echo "<div class=\"page_title\">" . $string['exporttoqti'] . "</div>";
echo "</div>";

if (!isset($qtiexportdirectory)) {
  // Just in case the file is included from pages where this is not set.
  $qtiexportdirectory = rogo_directory::get_directory('qti_export');
}
?>

<br />
<table border="0" cellpadding="0" cellspacing="0" width="500" class="dialog_border" style="text-align:left">
	<tr>
		<td class="dialog_header" style="width:55px"><img src="../artwork/ims_logo.png" width="47" height="44" alt="IMS Logo" /></td><td class="dialog_header" style="width:445px"><?php echo $string['exporttoqti'] ?></td>
	</tr>
	<tr>
		<td class="dialog_body" colspan="2">
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php printf($string['exportsready'], $qti_ver) ?></div>
			<?php foreach ($files as $file) : ?>
				<?php $path = $file->path; ?>
				<div style="margin-left:25px; line-height:150%"><img src="../artwork/download_16.gif" width="16" height="16" alt="bullet" />&nbsp;
					<strong><a href="download.php?file=<?php echo(urlencode($file->filename)) ?>&path=<?php echo(urlencode($file->path)) ?>&title=<?php echo(urlencode($file->title)) ?>"><?php echo $string['download'] . ' ' . $file->title ?>.xml</a></strong>
				</div>
			<?php endforeach; ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php echo $string['moreinformation']; ?></div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet" />&nbsp;
				<a href="" onclick="newPopup('<?php echo $qtiexportdirectory->url($path . '/result.html'); ?>'); return false;"><?php echo $string['viewdetails']; ?></a>
			</div>
<?php if ($show_debug) : ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php echo $string['debuginformation']; ?></div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet" />&nbsp;
				<a href="" onclick="newPopup('<?php echo $qtiexportdirectory->url($path . '/debug_load.html'); ?>'); return false;"><?php echo $string['loadingdebug']; ?></a>
			</div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet" />&nbsp;
				<a href="" onclick="newPopup('<?php echo $qtiexportdirectory->url($path . '/debug_int.html'); ?>'); return false;"><?php echo $string['intermediateformatdebug']; ?></a>
			</div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet" />&nbsp;
				<a href="" onclick="newPopup('<?php echo $qtiexportdirectory->url($path . '/debug_save.html'); ?>'); return false;"><?php echo $string['savingdebug']; ?></a>
			</div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet" />&nbsp;
				<a href="" onclick="newPopup('<?php echo $qtiexportdirectory->url($path . '/debug_res.html'); ?>'); return false;"><?php echo $string['generaldebuginfo']; ?></a>
			</div>
<?php endif; ?>
			<br />
		</td>
	</tr>
</table>

</body>
</html>
