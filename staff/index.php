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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/staff_student_auth.inc';
require_once '../include/errors.php';
require_once '../include/sidebar_menu.inc';
require_once '../config/index.inc';

$userObject = UserObject::get_instance();

// Redirect Students (if not also staff), and special users to their own areas.
if ($userObject->has_role('Student') and !($userObject->has_role(array('Staff', 'Admin', 'SysAdmin')))) {
  header("location: ../students/");
  exit();
} elseif ($userObject->has_role('External Examiner') or $userObject->has_role('Internal Reviewer')) {
  header("location: ../reviews/");
  exit();
} elseif ($userObject->has_role('Invigilator')) {
  header("location: ../invigilator/");
  exit();
}

// If we're still here we should be staff
require_once '../include/staff_auth.inc';
?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html; charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;<?php echo ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  
  <style>
    body {background-color: #F0F0F0; line-height: 150%}
    h1 {font-size: 200%}
    a {font-family: 'Courier New'; font-weight: bold}
  </style>
</head>

<body>

<blockquote>
  <h1>Old Page</h1>

  <p>Please update your bookmarks accordingly:</p>

  <ul>
    <?php
      $staff_homepage = NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $cfg_root_path  . '/';
      $summative_homepage = NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $cfg_root_path  . '/paper/';
    ?>
    <li>New staff homepage: <a href="<?php echo $staff_homepage ?>"><?php echo $staff_homepage ?></a></li>
    <li>Summative exam homepage (for students): <a href="<?php echo $summative_homepage ?>"><?php echo $summative_homepage ?></a></li>
  </ul>
</blockquote>
  
</body>
</html>
