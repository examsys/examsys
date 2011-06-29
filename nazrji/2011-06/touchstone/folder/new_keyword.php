<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';
  
  if (isset($_POST['ok']) or (isset($_POST['returnhit']) and $_POST['returnhit'] == '1')) {
    $new_keyword = trim($_POST['new_keyword']);
    if ($new_keyword != '') {
      if ($_POST['module'] == '') {
        $result = $mysqli->prepare("INSERT INTO keywords_user VALUES (NULL,$userID,?,'personal')");
        $result->bind_param('s', $new_keyword);
        $result->execute();  
        $result->close();
      } else {
        // Get the numeric ID of the module to store in keywords_user as the userID.
        $result = $mysqli->prepare("SELECT id FROM modules WHERE moduleid=?");
        $result->bind_param('s', $_POST['module']);
        $result->execute();
        $result->bind_result($tmp_userID);
        $result->fetch();
        $result->close();
      
        $result = $mysqli->prepare("INSERT INTO keywords_user VALUES (NULL,$tmp_userID,?,'team')");
        $result->bind_param('s', $new_keyword);
        $result->execute();  
        $result->close();
      }
    }
?>
<html>
<head>
<title>Add Keyword</title>
</head>
<?php
  if ($new_keyword != '') {
    echo "<body onload=\"window.opener.location.href='list_keywords.php?module=" . $_POST['module'] . "'; window.close();\">\n";
  } else {
    echo "<body onload=\"window.close();\">\n";
  }
?>
</body>
</html>
<?php
  } else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Add Keyword</title>
<script language="JavaScript">
  function illegalChar(codeID) {
    if (codeID == 35) {
      alert("Character '#' illegal - please use alternative characters in keyword.");
      event.returnValue = false;
    } else if (codeID == 38) {
      alert("Character '&' illegal - please use alternative characters in keyword.");
      event.returnValue = false;
    } else if (codeID == 59) {
      alert("Character ';' illegal - please use alternative characters in keyword.");
      event.returnValue = false;
    } else if (codeID == 63) {
      alert("Character '?' illegal - please use alternative characters in keyword.");
      event.returnValue = false;
    } else if (codeID == 64) {
      alert("Character '@' illegal - please use alternative characters in keyword.");
      event.returnValue = false;
    } else if (codeID == 94) {
      alert("Character '^' illegal - please use alternative characters in keyword.");
      event.returnValue = false;
    } else if (codeID == 126) {
      alert("Character '~' illegal - please use alternative characters in keyword.");
      event.returnValue = false;
    } else if (codeID == 13) {
      document.myform.returnhit.value = '1';
      document.myform.submit();
    }
  }
</script>
<style>
body {font-family:Arial,sans-serif; font-size:90%; background-color:#EEEEEE; color:black}
textarea, input[type=text], select {font-family:Arail,sans-serif; border: 1px solid #7F9DB9}
h1 {font-size:120%}
</style>
</head>

<body onload="document.myform.new_keyword.focus();">
<h1>New Keyword</h1>
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<div><input type="text" style="width:100%" name="new_keyword" onkeypress="illegalChar(event.keyCode)" /></div>
<div align="right"><input type="submit" name="ok" value="OK" style="width:80px" />&nbsp;<input type="button" name="cancel" value="Cancel" style="width:80px" onclick="window.close();" /><input type="hidden" name="returnhit" value="" /><input type="hidden" name="module" value="<?php if (isset($_GET['module'])) echo $_GET['module']; ?>" /></div>
</form>

</body>
</html>
<?php
}
$mysqli->close();
?>