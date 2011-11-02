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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  $root = (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'] . '/';
  require_once $root . 'config/config.inc.php';
  require_once $root . 'classes/lang.class.php';
  require '../../include/staff_auth.inc';
  $mysqli->close();
?>
<html>
<head>
<title><?php echo $string['questionsbank'] . $cfg_install_type; ?></title>
</head>

  <frameset rows="*,32" frameborder="0" framespacing="0" border="0">
    <frameset cols="134,*" frameborder="0" framespacing="0" border="0">
      <frame scrolling="no" src="add_questions_buttons.php" name="qbuttons">
      <frame scrolling="no" src="add_questions_iframe.php" name="qlist">
    </frameset>
    <frame scrolling="no" resizable="no" src="add_question_controls.php?paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $_GET['module']; ?>&folder=<?php echo $_GET['folder']; ?>&display_pos=<?php echo $_GET['display_pos']; ?>&scrOfY=<?php echo $_GET['scrOfY']; ?>&max_screen=<?php echo $_GET['max_screen']; ?>" name="controls">
  </frameset>
  <noframes>
    <?php echo $string['frameserr'];?>
  </noframes>
</html>
