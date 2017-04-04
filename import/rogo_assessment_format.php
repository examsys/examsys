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

require '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once '../include/media.inc';

$paperID = check_var('paperID', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$paper = $propertyObj->get_paper_title();

if (isset($_POST['submit'])) {
	$rafObject = new RAF($userObject, $configObject, $mysqli, $string);
	$rafObject->import($paperID);

	$mysqli->close();
	
	header("location: ../paper/details.php?paperID=$paperID");
} else {
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
	
  <title>Rog&#333;: <?php echo $string['importraf']; ?></title>
	
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <style type="text/css">
    span.killer {float:none}
  </style>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body>
<?php
  require '../include/paper_options.php';
  require '../include/toprightmenu.inc';

  echo draw_toprightmenu();
?>
<div id="content">
<div class="head_title">
<div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
<div class="breadcrumb">
<?php
$modutils = module_utils::get_instance();
echo '<a href="../index.php">' . $string['home'] . '</a>';
if ($module != '') {
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $module . '">' . $modutils->get_moduleid_from_id($module, $mysqli) . '</a>';
} elseif ($folder != '') {
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $folder . '">' . $folder_name . '</a>';
}
echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper . '</a></div>';

echo "</div><div class=\"page_title\">" . $string['importraf'] . "</div>";
echo "</div>";
?>
<form name="myform" id="myform" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data" autocomplete="off">
<table cellspacing="0" cellpadding="10" border="0" style="margin-top:70px; width:500px; text-align:left" class="dialog_border"> 
	<tr> 
		<td class="inline_dialog_header" style="width:55px"><img src="../artwork/raf_file.png" width="48" height="48" /></td><td class="dialog_header" style="width:445px"><?php echo $string['importraf'] ?></td> 
	</tr> 
	<tr> 
		<td class="dialog_body" colspan="2">
				<table width="100%" cellspacing="0" cellpadding="10">
					<tr>
						<td>
							<?php echo $string['file'] ?>&nbsp;<input type="file" size="40" name="raffile" id="raffile" class="required" />
						</td>
					</tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
					<tr>
						<td style="text-align:center">
							<input type="submit" name="submit" value="<?php echo $string['importfile'] ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" onclick="history.back()" />
						</td>
					</tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
				</table>
		
		</td>
	</tr>
</table>

</form>
</div>

</body>
</html>
<?php
}
?>