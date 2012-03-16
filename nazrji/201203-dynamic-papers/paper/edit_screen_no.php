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
require '../include/errors.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
    <title>Screen No.<?php echo " $cfg_install_type"; ?></title>

    <style type="text/css">
      body {font-family:Arial,sans-serif; color:black; margin:0px; background-color:#E5EFFA}
      td {font-size:90%}
      input, textarea {font-family:Arial,sans-serif; color:black}
    </style>

    <script type="text/javascript">
      function updateParent() {
      window.opener.location = "details.php?paperID=<?php if(isset($_POST['paperID'])) echo $_POST['paperID']; ?>&folder=<?php if(isset($_POST['folder'])) echo $_POST['folder']; ?>&module=<?php if(isset($_POST['module'])) echo $_POST['module']; ?>&scrOfY=<?php echo $_POST['scrOfY']; ?>";
      window.close();
      }
    </script>
  </head>

<?php

if (isset($_POST['submit'])) {
  // Change the screen number of the actual question.
  if ($result = $mysqli->prepare("UPDATE papers SET screen=? WHERE paper=? AND p_id=?")) {
    $result->bind_param('iii', $_POST['screen'], $_POST['paperID'], $_POST['questionID']);
    $result->execute();
    $result->close();
  } else {
    display_error("Papers Update Error 1", $mysqli->error);
  }

  // Increase screen number of any questions further down the paper with a lower screen number.
  if ($result = $mysqli->prepare("UPDATE papers SET screen=? WHERE screen < ? AND paper=? AND display_pos > ?")) {
    $result->bind_param('iiii', $_POST['screen'], $_POST['screen'], $_POST['paperID'],  $_POST['display_pos']);
    $result->execute();
    $result->close();
  } else {
    display_error("Papers Update Error 2", $mysqli->error);
  }

  // Decrease screen number of any questions further up the paper with a higher screen number.
  if ($result = $mysqli->prepare("UPDATE papers SET screen=? WHERE screen > ? AND paper=? AND display_pos < ?")) {
    $result->bind_param('iiii', $_POST['screen'], $_POST['screen'], $_POST['paperID'],  $_POST['display_pos']);
    $result->execute();
    $result->close();
  } else {
    display_error("Papers Update Error 3", $mysqli->error);
  }
  ?>
    <body onload="updateParent()">
    <form>
      <br />&nbsp;<div align="center"><input type="button" name="home" value="   OK   " onclick="updateParent();" /></div>
    </form>
  <?php
} else {
?>
<body>
  <br />
  <div align="center">
  <form name="edit_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <p>New Screen No. <select name="screen">
    <?php
      for ($i=1; $i<=60; $i++) {
        if ($i == $_GET['screen']) {
          echo "<option value=\"$i\" selected>$i</option>\n";
        } else {
          echo "<option value=\"$i\">$i</option>\n";
        }
      }
    ?>
    </select></p>
    <input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" />
    <input type="hidden" name="display_pos" value="<?php echo $_GET['display_pos']; ?>" />
    <input type="hidden" name="questionID" value="<?php echo $_GET['questionID']; ?>" />
    <input type="hidden" name="folder" value="<?php echo $_GET['folder']; ?>" />
    <input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" />
    <input type="hidden" name="scrOfY" value="<?php echo $_GET['scrOfY']; ?>" />
    <p><input type="submit" name="submit" value="    OK    ">&nbsp;&nbsp;<input type="button" name="home" value=" Cancel " onclick="javascript:window.close();" /></p>
  </form>
  </div>
<?php
}
$mysqli->close();
?>
</body>
</html>