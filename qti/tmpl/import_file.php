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
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
	<title>Rog&#333;: <?php echo $string['importfromqti'] ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    span.killer {float:none}
  </style>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    $(function () {
      $('#file_form').validate();
    });
  </script>
</head>

<body>
<?php
require '../include/paper_options.php';
require '../include/toprightmenu.inc';

echo draw_toprightmenu();
?>
<div id="content">
<?php
echo "<div class=\"head_title\">\n";
echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
echo "<div class=\"breadcrumb\">";
$modutils = module_utils::get_instance();
echo '<a href="../index.php">' . $string['home'] . '</a>';
if (isset($_GET['module']) and $_GET['module'] != '') {
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . $modutils->get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
} elseif ($folder != '') {
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $folder . '">' . $folder_name . '</a>';
}
echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper_title . '</a>';

echo "</div><div class=\"page_title\">" . $string['importfromqti'] . "</div>";
echo "</div>";
?>
<br/>
<br/>
<br/>
<br/>

<table cellspacing="0" cellpadding="0" border="0" style="width:500px; text-align:left" class="dialog_border"> 
	<tr> 
		<td class="inline_dialog_header" style="width:55px"><img src="../artwork/ims_logo.png" width="47" height="44" alt="IMS Logo" /></td><td class="dialog_header" style="width:445px">QTI <?php echo $string['import'] ?></td> 
	</tr> 
	<tr> 
		<td class="dialog_body" colspan="2"> 
			
			<div style="padding-top:16px;padding-left:16px;padding-right:16px;">
				<form id="file_form" action="import.php?<?php echo $_SERVER['QUERY_STRING'];?>" method="post" enctype="multipart/form-data" autocomplete="off">
				<table width="100%" cellspacing="0" cellpadding="10">
					<tr>
						<td>
							<?php echo $string['file'] ?>:&nbsp;<input type="file" size="40" name="file" id="file" class="required" />
							<input type="hidden" name="paperID" id="paperID" value="<?php echo $paperID ?>" />
              <input type="hidden" name="module" id="module" value="<?php echo $module ?>" />
						</td>
					</tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
					<tr>
						<td style="text-align:center">
							<input type="submit" name="submit" value="<?php echo $string['import2'] . ' ' . $string['file'] ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" onclick="history.back()" />
						</td>
					</tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
				</table>
				</form>
			</div>
		</td>
	</tr>
</table>

</div>
</body>
</html>
