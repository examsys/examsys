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

require '../include/staff_auth.inc';
require '../include/errors.inc';

$screen = $_GET['screen'] + 1;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
   <html>
   <head>
   <title>Screen No.<?php echo " $cfg_install_type"; ?></title>

   <style>
     body {font-family:Arial,sans-serif; color:black; margin:0px; background-color:#E5EFFA}
     td {font-size:90%}
     input, textarea {font-family:Arial,sans-serif; color:black}
   </style>

   <script language="JavaScript">
   function updateParent() {
     window.opener.location = "details.php?paperID=<?php if(isset($_GET['paperID'])) echo $_GET['paperID']; ?>&folder=<?php if(isset($_GET['folder'])) echo $_GET['folder']; ?>&module=<?php if(isset($_GET['module'])) echo $_GET['module']; ?>&scrOfY=<?php echo $_GET['scrOfY']; ?>";
     window.close();
   }
   
   </script>
   </head>

<?php
// Change the screen number of the actual question.
if ($result = $mysqli->prepare("UPDATE papers SET screen=? WHERE paper=? AND p_id=?")) {
  $result->bind_param('iii', $screen, $_GET['paperID'], $_GET['questionID']);
  $result->execute();
  $result->close();
} else {
  display_error("Papers Update Error 1", $mysqli->error);
}

// Increase screen number of any questions further down the paper with a lower screen number.
if ($result = $mysqli->prepare("UPDATE papers SET screen=? WHERE screen < ? AND paper=? AND display_pos > ?")) {
  $result->bind_param('iiii', $screen, $screen, $_GET['paperID'],  $_GET['display_pos']);
  $result->execute();
  $result->close();
} else {
  display_error("Papers Update Error 2", $mysqli->error);
}

// Decrease screen number of any questions further up the paper with a higher screen number.
if ($result = $mysqli->prepare("UPDATE papers SET screen=? WHERE screen > ? AND paper=? AND display_pos < ?")) {
  $result->bind_param('iiii', $screen, $screen, $_GET['paperID'],  $_GET['display_pos']);
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
$mysqli->close();
?>
</body>
</html>