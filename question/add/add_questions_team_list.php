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

require '../../include/staff_auth.inc';

if (isset($_GET['paperID'])) {
  $paperID = $_GET['paperID'];
} else {
  $paperID = '';
}

if (isset($_GET['display_pos'])) {
  $display_pos = $_GET['display_pos'];
} else {
  $display_pos = '';
}

if (isset($_GET['module'])) {
  $module = $_GET['module'];
} else {
  $module = '';
}

if (isset($_GET['folder'])) {
  $folder = $_GET['folder'];
} else {
  $folder = '';
}

if (isset($_GET['scrOfY'])) {
  $scrOfY = $_GET['scrOfY'];
} else {
  $scrOfY = '';
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['byteam']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/header.css" />
  <style type="text/css">
    body {font-size:90%}
    a:link {color:black}
    a:visited {color:black}
    a:hover {color:black}
    .foldername {float:left; width:380px; height:60px; padding-left:12px; font-size:80%}
  </style>
</head>

<body>
<table class="header">
<tr><th colspan="5" style="font-size:160%; font-weight:bold">&nbsp;<?php echo $string['byteam']; ?></th></tr>
</table>
<?php
  $user_modules = $userObject->get_staff_modules();

  if (count($user_modules) > 0) {
		$sql = "SELECT modules.id, modules.moduleid, modules.fullname, COUNT(groupID) AS count_no
			FROM (modules_staff, modules)
			WHERE modules_staff.idMod=modules.id AND idMod IN (" . implode(',', array_keys($user_modules)) . ")
			GROUP BY modules.fullname, modules.moduleid, modules.id";
		$result = $mysqli->prepare($sql);
		$result->execute();
		$result->bind_result($mod_id, $moduleid, $module_name, $count_no);
		while ($result->fetch()) {
			echo '<div class="foldername">';
			echo '<table cellpadding="0" cellspacing="0" border="0" style="font-size:100%"><tr><td style="width:66px" align="center">';
			echo '  <a href="add_questions_list.php?type=team&teamID=' . $mod_id . '&paperID=' . $paperID . '&display_pos=' . $display_pos . '&module=' . $module . '&folder=' . $folder . ' &scrOfY=' . $scrOfY . '"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="' . $module_name . '" /></a><td>';
			echo '  <td width="290"><a href="add_questions_list.php?type=team&teamID=' . $mod_id . '&paperID=' . $paperID . '&display_pos=' . $display_pos . '&module=' . $module . '&folder=' . $folder . ' &scrOfY=' . $scrOfY . '">' . $moduleid . ': ' . $module_name . '</a><br />';
			echo '  <span style="color:#808080">' . $count_no . ' ' . $string['members'] . '</span></td></tr></table>';
			echo "</div>\n";
		}
		$result->close();
	}
  $mysqli->close();
?>
</body>
</html>