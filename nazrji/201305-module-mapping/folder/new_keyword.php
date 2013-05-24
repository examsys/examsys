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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';
  
if (isset($_POST['ok']) or (isset($_POST['returnhit']) and $_POST['returnhit'] == '1')) {
  $new_keyword = trim($_POST['new_keyword']);
  if ($new_keyword != '') {
    if ($_POST['module'] == '') {
      $result = $mysqli->prepare("INSERT INTO keywords_user VALUES (NULL, ?, ?, 'personal')");
      $result->bind_param('is', $userObject->get_user_ID(), $new_keyword);
      $result->execute();  
      $result->close();
    } else {
      $result = $mysqli->prepare("INSERT INTO keywords_user VALUES (NULL, ?, ?, 'team')");
      $result->bind_param('is', $_POST['module'], $new_keyword);
      $result->execute();  
      $result->close();
    }
  }
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['newkeyword']; ?></title>
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
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
<title><?php echo $string['newkeyword']; ?></title>
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
<h1><?php echo $string['newkeyword']; ?></h1>
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<div><input type="text" style="width:99%" name="new_keyword" onkeypress="illegalChar(event.keyCode)" /></div>
<div align="right"><input type="submit" name="ok" value="<?php echo $string['ok']; ?>" style="width:80px" />&nbsp;<input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" style="width:80px" onclick="window.close();" /><input type="hidden" name="returnhit" value="" /><input type="hidden" name="module" value="<?php if (isset($_GET['module'])) echo $_GET['module']; ?>" /></div>
</form>

</body>
</html>
<?php
}
$mysqli->close();
?>