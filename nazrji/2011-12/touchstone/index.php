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
* Rogō hompage. Uses ../include/options_menu.inc for the sidebar menu.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/staff_student_auth.inc';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<title>Rogō<?php echo " $cfg_install_type"; ?></title>
<style>
body {font-family:Arial,sans-serif}
h1 {font-size:150%; color:#C00000}
</style>
</head>
<body>
<?php
  if (strpos($userroles,'Student') !== false and strpos($userroles,'Staff') === false and strpos($userroles,'Admin') === false and strpos($userroles,'SysAdmin') === false) {
    $url = $protocol. $_SERVER['HTTP_HOST'] . "/students/";
  } elseif ($userroles == 'External Examiner') {
    $url = $protocol. $_SERVER['HTTP_HOST'] . "/reviews/";
  } elseif ($userroles == 'Invigilator') {
    $url = $protocol. $_SERVER['HTTP_HOST'] . "/invigilator/";
  } else {
    $url = $protocol. $_SERVER['HTTP_HOST'] . "/staff/";
  }
?>
<h1>Page has Moved</h1>

<p>The page you have tried going to does not exist. Please update your browser bookmarks/favorites to: <?php echo "<a href=\"$url\" style=\"color:blue; font-weight:bold\">$url</a>"; ?></p>
</body>
</html>
