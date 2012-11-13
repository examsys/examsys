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

function keywords_from_file($fileName,$userObject) {
  global $mysqli, $DISABLEDuserID;

  if ($_GET['module'] == '') {
    $type = 'personal';
    $tmp_userID = $userObject->get_user_ID();
    
    // Get the existing personal keywords.
    $existing_keywords = array();
    $result = $mysqli->prepare("SELECT keyword FROM keywords_user WHERE userID=?");
    $result->bind_param('i', $userObject->get_user_ID());
    $result->execute();
    $result->bind_result($keyword);
    while ($result->fetch()) {
      $existing_keywords[$keyword] = $keyword;
    }
    $result->close();
  } else {
    $type = 'team';
    // Get the ID of the module
    $result = $mysqli->prepare("SELECT id FROM modules WHERE moduleid=?");
    $result->bind_param('s', $_GET['module']);
    $result->execute();
    $result->bind_result($tmp_userID);
    $result->fetch();
    $result->close();

    // Get the existing team keywords for the folder.
    $existing_keywords = array();
    $result = $mysqli->prepare("SELECT keyword FROM keywords_user WHERE userID=?");
    $result->bind_param('i', $tmp_userID);
    $result->execute();
    $result->bind_result($keyword);
    while ($result->fetch()) {
      $existing_keywords[$keyword] = $keyword;
    }
    $result->close();
  }
  
  // Process the file
  $lines = file($fileName);    
  foreach ($lines as $separate_line) {
    $separate_line = trim($separate_line);
    if (!isset($existing_keywords[$separate_line])) {
      $result = $mysqli->prepare("INSERT INTO keywords_user VALUES(NULL, ?, ?, ?)");
      $result->bind_param('iss', $tmp_userID, $separate_line, $type);
      $result->execute();
      $result->close();
    }
  }    
}

if (isset($_POST['submit'])) {
  if ($_FILES['txtfile']['name'] != 'none' and $_FILES['txtfile']['name'] != '') {
    if (!move_uploaded_file($_FILES['txtfile']['tmp_name'], $cfg_tmpdir . $userObject->get_user_ID() . "_keywords.txt"))  {
      echo uploadError($_FILES['txtfile']['error']);
      exit;
    } else {
      keywords_from_file($cfg_tmpdir . $userObject->get_user_ID() . '_keywords.txt',$userObject->get_user_ID());
      unlink($cfg_tmpdir . $userObject->get_user_ID() . '_keywords.txt');
      header("location: list_keywords.php?paperID=". $_GET['paperID'] . "&module=" . $_GET['module'] . "&folder=" . $_GET['folder']);
    }
  }
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title><?php echo $string['loadkeywords']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css">
</head>

<body>
<?php
  require '../include/folder_keyword_options.inc';
?>

<div id="content" class="content">
<br />
<br />

<table class="dialog_border" style="width:600px">
<tr>
<td class="dialog_header" style="width:54px;"><img src="../artwork/import_48.gif" width="48" height="48" alt="Icon" /></td><td class="dialog_header" style="width:90%"><?php echo $string['importkeywords']; ?></td>
</tr>
<tr>
<td align="left" colspan="2" class="dialog_body">

<p><?php echo $string['msg1']; ?></p>

<div><?php echo $string['msg2']; ?></div>


<div align="center">
<form name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?paperID=<?php if (isset($_GET['paperID'])) echo $_GET['paperID']; ?>&folder=<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>" enctype="multipart/form-data">

<p><input type="file" size="50" name="txtfile" /></p>

<p><input type="submit" style="width:130px" value="<?php echo $string['loadkeywordsbtn']; ?>" name="submit" />&nbsp;<input style="width:100px" type="button" value="<?php echo $string['cancel']; ?>" name="cancel" onclick="history.go(-1)" /></p>
</form>
</div>
</td>
</tr>
</table>

</div>


</body>
</html>
<?php
  }
  $mysqli->close();
?>