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
  if (isset($_POST['submit']) or (isset($_POST['returnhit']) and $_POST['returnhit'] == '1')) {
?>
<html>
<head>
  <title>Rename Keyword</title>
  <script language="JavaScript">
    window.opener.document.getElementById('keytext<?php echo $_POST['index']; ?>').innerHTML = '<?php echo str_replace('"','&quot;',$_POST['new_keyword']); ?>';
    window.opener.document.getElementById('renamelist').value = window.opener.document.getElementById('renamelist').value + ';<?php echo $_POST['keywordID'] . '=' . str_replace('"','&quot;',$_POST['new_keyword']); ?>';
  </script>
</head>
<body onload="window.close();">
<h1>Rename</h1>
<?php
  } else {
?>
<html>
<head>
<title>Rename Keyword</title>
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
h1 {font-size:120%}
</style>
</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<h1>Rename</h1>
<?php
  $keyword_details = $mysqli->prepare("SELECT keyword FROM keywords_user WHERE id=?");
  $keyword_details->bind_param('i', $_GET['keywordID'] );
  $keyword_details->execute();
  $keyword_details->bind_result($keyword);
  $keyword_details->fetch();
  $keyword_details->close();
?>
<div><input type="text" name="new_keyword" size="40" style="width:100%" value="<?php echo $keyword; ?>" onkeypress="illegalChar(event.keyCode)" /></div>
<div style="text-align:right"><input type="submit" name="submit" style="width:80px" value="OK" />&nbsp;<input type="button" name="cancel" value="Cancel" style="width:80px" onclick="window.close();" /></div>
<input type="hidden" name="keywordID" value="<?php echo $_GET['keywordID']; ?>" />
<input type="hidden" name="index" value="<?php echo $_GET['index']; ?>" />
<input type="hidden" name="returnhit" value="" />
</form>
<?php
  }
?>
</body>
</html>