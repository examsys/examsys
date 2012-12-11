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

if (isset($_POST['submit'])) {
  $result = $mysqli->prepare("UPDATE keywords_user SET keyword=? WHERE id=?");
  $result->bind_param('si', $_POST['new_keyword'], $_POST['keywordID']);
  $result->execute();  
  $result->close();
  ?>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
<title><?php echo $string['editkeyword']; ?></title>
</head>
<body onload="window.opener.location.href='list_keywords.php?module=<?php echo $_POST['module']; ?>'; window.close();">
</body>
</html>
  <?php
} else {
  $result = $mysqli->prepare("SELECT keyword FROM keywords_user WHERE id=?");
  $result->bind_param('i', $_GET['keywordID']);
  $result->execute();
  $result->bind_result($keyword);
  $result->fetch();
  $result->close();
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['editkeyword']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%; background-color:#EEEEEE; padding:4px}
    h1 {font-size:120%}
  </style>
  <script language="JavaScript">
    function illegalChar(codeID) {
      if (codeID == 35) {
        alert("<?php echo $string['character']; ?> '#' <?php echo $string['illegal']; ?>");
        event.returnValue = false;
      } else if (codeID == 38) {
        alert("<?php echo $string['character']; ?> '&' <?php echo $string['illegal']; ?>");
        event.returnValue = false;
      } else if (codeID == 59) {
        alert("<?php echo $string['character']; ?> ';' <?php echo $string['illegal']; ?>");
        event.returnValue = false;
      } else if (codeID == 63) {
        alert("<?php echo $string['character']; ?> '?' <?php echo $string['illegal']; ?>");
        event.returnValue = false;
      } else if (codeID == 64) {
        alert("<?php echo $string['character']; ?> '@' <?php echo $string['illegal']; ?>");
        event.returnValue = false;
      } else if (codeID == 94) {
        alert("<?php echo $string['character']; ?> '^' <?php echo $string['illegal']; ?>");
        event.returnValue = false;
      } else if (codeID == 126) {
        alert("<?php echo $string['character']; ?> '~' <?php echo $string['illegal']; ?>");
        event.returnValue = false;
      } else if (codeID == 13) {
        document.myform.returnhit.value = '1';
        document.myform.submit();
      }
    }
  </script>
</head>

<body onload="document.myform.new_keyword.focus();">
<h1><?php echo $string['editkeyword']; ?></h1>
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<div><input type="text" style="width:99%" name="new_keyword" value="<?php echo $keyword; ?>" onkeypress="illegalChar(event.keyCode)" /><input type="hidden" name="keywordID" value="<?php echo $_GET['keywordID']; ?>" /></div>
<div align="right"><input type="submit" name="submit" value="<?php echo $string['ok']; ?>" style="width:80px" />&nbsp;<input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" style="width:80px" onclick="window.close();" /><input type="hidden" name="returnhit" value="" /><input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" /></div>
</form>

</body>
</html>
<?php
}
$mysqli->close();
?>