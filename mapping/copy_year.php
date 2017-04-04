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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once '../include/mapping.inc';

$errors = array();

if (empty($_POST['source_y']) or empty($_POST['dest_y']) or empty($_POST['moduleID'])) {
	$errors[] = "Undefined source or destination year";
} elseif ($_POST['source_y'] == $_POST['dest_y']) {
	$errors[] = "Source and destination years cannot be the same";
} else {
	// Get the sessions for the source year

  $module_code = module_utils::get_moduleid_from_id($_POST['moduleID'], $mysqli);
  $modules_array = array($_POST['moduleID'] => $module_code);
	$objectives = getObjectives($modules_array, $_POST['source_y'], '', '', $mysqli);
	
	try {
		copyObjectives($objectives, $_POST['moduleID'], $module_code, $_POST['dest_y'], $mysqli);
	} catch(Exception $ex) {
		$errors[] = "An error occured when copying the objectives. Please try again.";
	}
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Rog&#333;: Copy Objectives<?php echo ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
</head>
<?php
if (count($errors) == 0) {
	echo "<body onload=\"window.location='" . $configObject->get('cfg_root_path') . "/mapping/sessions_list.php?module=" . $_POST['moduleID'] . "';\"></body></html>";
} else {
?>
  <body onclick="hideMenus()">
  <div id="content">
    <table border="0" width="100%" height="100%">
    <tr><td valign="middle">
    <div align="center">

    <table border="0" cellpadding="4" cellspacing="1" style="background-color:#FF0000">
    <tr>
    <td valign="middle" style="background-color: white"><img src="../artwork/access_denied.png" width="48" height="48" alt="Warning" />&nbsp;&nbsp;<span style="font-size:150%; font-weight:bold; color:#C00000">Error</span></td>
    </tr>
    <tr>
    <td style="background-color:#FFC0C0">
    	<p>Sorry, there was a problem copying your objectives please review the following error(s) and try again:</p>
    	<ul>
<?php
	foreach($errors as $error) {
?>
    		<li style="font-size:90%"><?php echo $error; ?></li>
<?php
	}
?>
			</ul>
    <div align="center"><input style="width:120px" type="button" value="&lt; Back" name="back" onclick="window.history.go(-1);"></div>
    </td>
    </tr>
    </table>

    </div>
    </td></tr>
    </table>
    </body>
    </html>
<?php
}
?>