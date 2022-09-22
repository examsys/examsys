<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
ini_set('auto_detect_line_endings', true);

function keywords_from_file($fileName, $userObj, $db)
{

  
    if ($_GET['module'] == '') {
        $type = 'personal';
        $tmp_userID = $userObj->get_user_ID();
        // Get the existing personal keywords.
        $existing_keywords = array();
        $result = $db->prepare('SELECT keyword FROM keywords_user WHERE userID = ?');
        $result->bind_param('i', $tmp_userID);
        $result->execute();
        $result->bind_result($keyword);
        while ($result->fetch()) {
            $existing_keywords[$keyword] = $keyword;
        }
        $result->close();
    } else {
        $type = 'team';
        $tmp_userID = $_GET['module'];
        // Get the existing team keywords for the folder.
          $existing_keywords = array();
        $result = $db->prepare('SELECT keyword FROM keywords_user WHERE userID = ?');
        $result->bind_param('i', $_GET['module']);
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
            $result = $db->prepare('INSERT INTO keywords_user VALUES(NULL, ?, ?, ?)');
            $result->bind_param('iss', $tmp_userID, $separate_line, $type);
            $result->execute();
            $result->close();
        }
    }
}

$file_problem = false;
if (isset($_POST['submit'])) {
    $filename = $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_keywords.txt';
    if ($_FILES['txtfile']['type'] == 'text/plain' and $_FILES['txtfile']['name'] != 'none' and $_FILES['txtfile']['name'] != '') {
        if (!move_uploaded_file($_FILES['txtfile']['tmp_name'], $filename)) {
            echo uploadError($_FILES['txtfile']['error']);
            exit();
        } else {
            keywords_from_file($filename, $userObject, $mysqli);
            unlink($filename);
            header('location: list_keywords.php?paperID=' . $_GET['paperID'] . '&module=' . $_GET['module'] . '&folder=' . $_GET['folder']);
        }
        $mysqli->close();
        exit();
    } else {
        $file_problem = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo  $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['loadkeywords']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css">
</head>

<body>
<?php
  require '../include/folder_keyword_options.inc';
?>

<div id="content">
<br />
<br />

<table class="dialog_border" style="width:600px">
<tr>
<td class="dialog_header" style="width:32px;"><img src="../artwork/upload_48.png" width="48" height="48" alt="Icon" /></td><td class="dialog_header"><?php echo $string['importkeywords']; ?></td>
</tr>
<tr>
<td align="left" colspan="2" class="dialog_body">

<p><?php echo $string['msg1']; ?></p>

<div><?php echo $string['msg2']; ?></div>


<div align="center">
<form name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?paperID=<?php if (isset($_GET['paperID'])) {
    echo $_GET['paperID'];
                                          } ?>&folder=<?php if (isset($_GET['folder'])) {
                                          echo $_GET['folder'];
                                          } ?>&module=<?php echo $_GET['module']; ?>" enctype="multipart/form-data" autocomplete="off">

<?php
if ($file_problem) {
    echo '<div style="color:#C00000"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="!" />&nbsp;Please specify a file for upload.</div>';
}
?>

<p><input type="file" size="50" name="txtfile" required /></p>

<p><input type="submit" class="ok" value="<?php echo $string['loadkeywordsbtn']; ?>" name="submit" /><input class="cancel" type="button" value="<?php echo $string['cancel']; ?>" name="cancel" onclick="history.go(-1)" /></p>
</form>
</div>
</td>
</tr>
</table>

</div>

</body>
</html>
<?php
  $mysqli->close();
?>
