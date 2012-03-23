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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
//$mysqli->close();

$controls_url = 'add_question_controls.php?';
if (!isset($_GET['paperID'])) {
  $controls_url .= 'module=' . $_GET['module'] . '&amp;objectives=' . $_GET['objectives'] . '&amp;session=' . $_GET['session'];
} else {
  $controls_url .= 'paperID=' . $_GET['paperID'] . '&amp;module=' . $_GET['module'] . '&amp;folder=' . $_GET['folder'] . '&amp;display_pos=' . $_GET['display_pos'] . '&amp;scrOfY=' . $_GET['scrOfY'] . '&amp;max_screen=' . $_GET['max_screen'];
}
$url_mod = (isset($_GET['module'])) ? '?module=' . $_GET['module'] : '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $string['questionsbank'] . $cfg_install_type; ?></title>
</head>
<frameset rows="*,32" frameborder="0" framespacing="0" border="0">
  <frameset cols="134,*" frameborder="0" framespacing="0" border="0">
    <frame scrolling="no" src="add_questions_buttons.php<?php echo $url_mod ?>" name="qbuttons">
    <frame scrolling="no" src="add_questions_iframe.php<?php echo $url_mod ?>" name="qlist">
  </frameset>
  <frame scrolling="no" resizable="no" src="<?php echo $controls_url ?>" name="controls">
  <noframes>
    <body><?php echo $string['frameserr'];?></body>
  </noframes>
</frameset>
</html>
