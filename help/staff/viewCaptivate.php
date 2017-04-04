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
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
require '../include/errors.php';

$tutorial = check_var('tutorial', 'GET', true, false, true);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=utf-8" />
  <title>Online Tutorial<?php echo " " . $configObject->get('cfg_install_type') ?></title>
  <style type="text/css">
    html, body {margin:0;	padding:0; height:100%; width:100%}
  </style>
</head>
<body>
<?php

   echo "<embed width=\"100%\" height=\"100%\" src=\"./images/$tutorial\" />";

   if (!$userObject->has_role('SysAdmin')) {   // Don't record the homepage or SysAdmin activities.
    $result = $mysqli->prepare("INSERT INTO help_tutorial_log VALUES (NULL, ?, ?, NOW(), ?)");
    $result->bind_param('sis', 'staff', $userObject->get_user_ID(), $tutorial);
    $result->execute();
    $result->close();
  }
?>
</body>
</html>