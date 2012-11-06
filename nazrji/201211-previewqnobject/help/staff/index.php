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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Rogō <?php echo $string['help'] . ' ' . $cfg_install_type; ?></title>
</head>
<frameset rows="52,*" border="0" frameborder="0" framespacing="0">
  <frame name="toolbar" noresize src="toolbar.php<?php if (isset($_GET['highlight'])) echo '?highlight=' . $_GET['highlight']; ?>" scrolling="no" border="0" frameborder="0" marginheight="0" marginwidth="0">
  <frameset cols="25%,*" border="0">
    <frameset rows="20,*" border="0" frameborder="0" framespacing="0">
      <frame name="toc_title" src="toc_title.php" scrolling="no" border="0" frameborder="0" marginheight="0" marginwidth="0">
      <frame src="toc.php<?php if (isset($_GET['id'])) echo '?id=' . $_GET['id']; ?>" name="navigation" id="navigation" scrolling="no" title="Navigation"/>
    </frameset>
      <?php
        if (isset($_GET['id'])) {
          $tmp_highlight = '';
          if (isset($_GET['highlight'])) $tmp_highlight = '&highlight=' . $_GET['highlight']; 
          if (isset($_GET['section'])) {
            echo "<frame src=\"display_page.php?id=" . $_GET['id'] . $tmp_highlight . "&section=" . $_GET['section'] . "\" name=\"content\" id=\"content\" scrolling=\"yes\" />\n";
          } else {
            echo "<frame src=\"display_page.php?id=" . $_GET['id'] . $tmp_highlight . "\" name=\"content\" id=\"content\" scrolling=\"yes\" />\n";
          }
        } else {
          echo "<frame src=\"display_page.php?id=1$tmp_highlight\" name=\"content\" id=\"content\" scrolling=\"yes\" />\n";
        }
      ?>
    <noframes>
      <body></body>
    </noframes>
  </frameset>
</frameset>
</html>