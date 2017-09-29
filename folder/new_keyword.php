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

require '../include/staff_auth.inc';
require '../include/errors.php';

$exists = false;
$new_keyword = '';

$submit = param::optional('ok', '', param::TEXT, param::FETCH_POST);
$new_keyword = param::optional('new_keyword', '', param::TEXT, param::FETCH_POST);
$module = param::optional('module', '', param::INT, param::FETCH_REQUEST);

if ($submit != '') {
  if ($new_keyword != '') {
    if ($module == '') {
      $type = 'personal';
      $owner = $userObject->get_user_ID();
      } else {
        $type = 'team';
        $owner = $module;
    }
    $result = $mysqli->prepare("SELECT keyword FROM keywords_user WHERE keyword = ? AND userID = ? AND keyword_type = ?");
    $result->bind_param('sis', $new_keyword, $owner, $type);
    $result->execute();  
    $result->store_result();
    $result->bind_result($keyword);
    if ($result->num_rows > 0) {
      $exists = true;
    }
    $result->close();
    if (!$exists) {
      $result = $mysqli->prepare("INSERT INTO keywords_user VALUES (NULL, ?, ?, ?)");
      $result->bind_param('iss', $owner, $new_keyword, $type);
      $result->execute();  
      $result->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['newkeyword']; ?></title>
</head>
<?php
  if ($new_keyword != '') {
    echo "<body onload=\"window.opener.location.href='list_keywords.php?module=" . $module . "'; window.close();\">\n";
  } else {
    echo "<body onload=\"window.close();\">\n";
  }
?>
</body>
</html>
<?php
      exit();
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
<title><?php echo $string['newkeyword']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%; background-color:#EEEEEE; padding:4px}
    h1 {font-size:120%}
    input[type=text] {margin-bottom: 10px}
  </style>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script>
    $(function () {
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');
    });
    
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
      }
    }
    
    
  </script>
</head>

<body>
<h1><?php echo $string['newkeyword']; ?></h1>
<form id="theform" name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
<?php
if ($exists) {
?>
<div><input type="text" class="errfield" style="width:99%" name="new_keyword" onkeypress="illegalChar(event.keyCode)" value="<?php echo $new_keyword; ?>" required autofocus /></div><div align="right"><span style="color:#C00000"><?php echo $string['duplicate']; ?></span>&nbsp;&nbsp;&nbsp;
<?php
} else {
?>
<div><input type="text" style="width:99%" name="new_keyword" onkeypress="illegalChar(event.keyCode)" value="<?php echo $new_keyword; ?>" required autofocus /></div><div align="right">
<?php
}
?>
<input type="submit" name="ok" value="<?php echo $string['ok']; ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" class="cancel" onclick="window.close();" /><input type="hidden" name="module" value="<?php echo $module; ?>" /></div>
</form>

</body>
</html>
<?php
$mysqli->close();
?>