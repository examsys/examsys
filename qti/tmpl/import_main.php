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
require_once '../include/errors.php';

if (isset($_GET['module'])) {
  $module = $_GET['module'];
} else {
  $module = '';
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
	<title><?php echo $string['importfromqti'] ?></title>
  
	<link rel="stylesheet" type="text/css" href="../css/body.css" />
	<link rel="stylesheet" type="text/css" href="../css/header.css" />
	<link rel="stylesheet" type="text/css" href="../css/dialog.css" />
	<link rel="stylesheet" type="text/css" href="../css/submenu.css" />

	<script type="text/javascript" src="./js/mootools-1.2.4.js"></script> 
  
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
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
$import_result = $result;
require '../include/paper_options.php';
require '../include/toprightmenu.inc';

echo draw_toprightmenu();

$result = $import_result;
?>
<div id="content">

<div class="head_title">
<div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
<?php
echo "<div class=\"breadcrumb\">";
echo '<a href="../index.php">' . $string['home'] . '</a>';
if ($module != '') {
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $module . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
} elseif ($folder != '') {
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $folder . '">' . $folder_name . '</a>';
}
echo "</div><div class=\"page_title\">$paper_title</div>";
echo "</div>";

if (!isset($qtiimportdirectory)) {
  // Just in case the file is included from pages where this is not set.
  $qtiimportdirectory = rogo_directory::get_directory('qti_import');
}
?>
<br/>
<br/>
<br/>
<br/>

<table border="0" cellpadding="0" cellspacing="0" class="dialog_border" style="width:500px; text-align:left"> 
	<tr> 
		<td class="dialog_header" style="width:55px"><img src="../artwork/ims_logo.png" width="47" height="44" alt="IMS Logo" /></td><td class="dialog_header" style="width:445px; font-size:160%; font-weight:bold; color:#5582D2"><?php echo $string['qtiimport'] ?></td>
	</tr> 
	<tr> 
		<td align="left" class="dialog_body" colspan="2"> 
<?php
if (isset($result['load']['data'])) $total = count($result['load']['data']->questions);
$bad = count($result['load']['errors']);
$num_load_errors = (isset($result['load']['errors'][0])) ? count($result['load']['errors'][0]) : 0;
$num_save_errors = (isset($result['save']['errors'][0])) ? count($result['save']['errors'][0]) : 0;
if (isset($result['load']['errors'][0])) $bad--;
?>
<?php if ($num_load_errors > 0) : ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold; color: red"><?php echo $string['qtiimporterror'] ?>:</div>
<?php foreach ($result['load']['errors'][0] as $error) : ?>
			<div style="margin-left:25px; line-height:150%; color: red"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet">&nbsp;
				<?php echo $error ?>
			</div>
<?php endforeach; ?>
<?php elseif ($num_save_errors > 0) : ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold; color: red"><?php echo $string['qtiimporterror'] ?>:</div>
<?php foreach ($result['save']['errors'][0] as $error) : ?>
			<div style="margin-left:25px; line-height:150%; color:red"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet" />&nbsp;
				<?php echo $error ?>
			</div>
<?php endforeach; ?>
<?php else : ?>
<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php echo $string['qtiimported'] ?></div>
<?php if ($num_save_errors > 0 || $num_load_errors > 0) : ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold; color:red"><?php echo $string['questionproblems'] ?></div>
			<div style="margin-left:25px; line-height:150%; margin-top:10px"><?php echo sprintf($string['hadproblemsimporting'], $bad, $total) ?></div>
<?php else : ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php echo sprintf($string['importedquestions'], $total) ?></div>
<?php endif; ?>
<?php endif; ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php echo $string['moreinformation'] ?></div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet">&nbsp;
				<a href="" onclick="newPopup('<?php echo $qtiimportdirectory->url($dir . '/result.html'); ?>'); return false;"><?php echo $string['viewdetails'] ?></a>
			</div>
<?php if ($show_debug) : ?>
			<div style="margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold"><?php echo $string['debuginformation'] ?></div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet">&nbsp;
				<a href="" onclick="newPopup('<?php echo $qtiimportdirectory->url($dir . '/debug_load.html'); ?>'); return false;"><?php echo $string['loadingdebug'] ?></a>
			</div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet">&nbsp;
				<a href="" onclick="newPopup('<?php echo $qtiimportdirectory->url($dir . '/debug_int.html'); ?>'); return false;"><?php echo $string['intermediateformatdebug'] ?></a>
			</div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet">&nbsp;
				<a href="" onclick="newPopup('<?php echo $qtiimportdirectory->url($dir . '/debug_save.html'); ?>'); return false;"><?php echo $string['savingdebug'] ?></a>
			</div>
			<div style="margin-left:25px; line-height:150%"><img src="../artwork/bullet_outline.gif" width="16" height="16" alt="bullet">&nbsp;
				<a href="" onclick="newPopup('<?php echo $qtiimportdirectory->url($dir . '/debug_res.html'); ?>'); return false;"><?php echo $string['generaldebuginfo']?></a>
			</div>
<?php endif; ?>
			<br />
      <div style="margin-left:25px"><input type="button" name="back" class="ok" value="<?php echo $string['backtopaper'] ?>" onclick="window.location='../paper/details.php?paperID=<?php echo $paperID ?>&module=<?php echo $module ?>'" />
      </div>
      <br />
		</td>
	</tr>
</table>
</div>

</body>
</html>