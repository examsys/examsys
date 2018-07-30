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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('Rog&#333;: ' . $string['questionsbank']); ?></title>
</head>

  <frameset rows="*,32" frameborder="0" framespacing="0" border="0">
    <frameset cols="154,*" frameborder="0" framespacing="0" border="0">
      <frame scrolling="no" src="add_questions_buttons.php" name="qbuttons">
      <frame scrolling="no" src="add_questions_iframe.php" name="qlist">
    </frameset>
    <frame scrolling="no" resizable="no" src="add_question_controls.php?paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $_GET['module']; ?>&folder=<?php echo $_GET['folder']; ?>&display_pos=<?php echo $_GET['display_pos']; ?>&scrOfY=<?php echo $_GET['scrOfY']; ?>&max_screen=<?php echo $_GET['max_screen']; ?>" name="controls">
  </frameset>
  <noframes>
    <?php echo $string['frameserr'];?>
  </noframes>
</html>
