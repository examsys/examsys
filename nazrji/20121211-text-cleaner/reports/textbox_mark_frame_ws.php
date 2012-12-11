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

  require '../include/staff_auth.inc';
  $mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Rogō - Question <?php echo $_GET['qNo']; ?></title>
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
  <link rel="icon" href="favicon.ico" type="image/x-icon"/>
</head>
  <frameset rows="55,*" frameborder="0">
    <frame src="textbox_header.php?<?php echo $_SERVER['QUERY_STRING']; ?>" name="header" scrolling="no">
    <frameset cols="50%,50%">
      <frame src="textbox_display_paper.php?<?php echo $_SERVER['QUERY_STRING']; ?>" name="menu">
      <frame src="textbox_marking.php?<?php echo $_SERVER['QUERY_STRING']; ?>" name="body">
    </frameset>
  </frameset>
  <noframes>
    Sorry, you need frames to use the Rogō.
  </noframes>
</html>