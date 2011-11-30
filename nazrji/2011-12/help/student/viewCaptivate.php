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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/
  
  require '../../include/staff_student_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Rogō Tutorial<?php echo " $cfg_install_type"; ?></title>
<style>
  html, body {margin:0;	padding:0; height:100%; width:100%}
</style></head>
<body>
<?php
   
   echo "<embed width=\"100%\" height=\"100%\" src='./images/" . $_GET['tutorial'] . "' />";
  
   if (strpos($userroles,'SysAdmin') === false) {   // Don't record the homepage or SysAdmin activities.
    $result = $mysqli->prepare("INSERT INTO help_tutorial_log VALUES (NULL,?,?,NOW(),?)");
    $result->bind_param('sis', 'student', $userID, $_GET['tutorial']);
    $result->execute();  
    $result->close();
  }
?>
</body>
</html>