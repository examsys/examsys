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
* Add a note to a students file
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';

  if (isset($_POST['submit'])) {
    $result = $mysqli->prepare("INSERT INTO student_notes VALUES (NULL, ?, ?, NOW(), ?, ?)");
    $result->bind_param('isis', $_POST['tmp_userID'], $_POST['note'], $_POST['paper'], $userObject->get_user_ID());
    $result->execute();  
    $result->close();
  ?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head><title><?php echo $string['note']; ?></title>
  <?php
    if ($_POST['calling'] == 'class_totals') {
  ?>
  <script language="JavaScript">
    function closeWindow() {
      window.opener.location.reload();
      window.close();
    }
  </script></head>
  <body onload="window.opener.location.reload(); closeWindow();">
  <?php
    } else {
  ?>
  <script language="JavaScript">
    function closeWindow() {
      window.opener.location = "details.php?userID=<?php echo $_POST['tmp_userID']; ?>&tab=notes";
      window.close();
    }
  </script></head>v
  <body onload="closeWindow();">
  <?php
    }
  ?>
  <form>
    <br />&nbsp;<div align="center"><input type="button" name="home" value="   OK   " onclick="closeWindow();" /></div>
  </form>
  <?php
  } else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['note']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    html {
      font-size:90%;
      margin:4px;
      background: -moz-linear-gradient(top, #FFF6BD, #FFEC82);
      background: -webkit-linear-gradient(top, #FFF6BD, #FFEC82);
      background-image: -ms-linear-gradient(top, #FFF6BD 0%, #FFEC82 100%);
      filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#FFF6BD', endColorstr='#FFEC82');
    }
    textarea {
      border: 1px solid #C0C0C0;
      background-color: transparent;
      width: 99%;
      height: 275px;
    }
    select {
      width: 99%;
    }
  </style>
  
 <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
 <script language="JavaScript">
   $(document).ready(function() {
     $("#note").focus();
   });
   
   function checkForm() {
     if ($("#paper").val() == '') {
       alert("<?php echo $string['namecheck']; ?>");
       return false;
     }
   
     if ($("#note").val() == '') {
       alert("<?php echo $string['notecheck']; ?>");
       return false;
     }
     
     return true;
   }
 </script>
</head>

<body>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="myform" onsubmit="return checkForm();">
<?php
  if (isset($_GET['paperID'])) {
    echo "<input type=\"hidden\" name=\"paper\" value=\"" . $_GET['paperID'] . "\" />\n";

    $result = $mysqli->prepare("SELECT title, initials, surname, student_id FROM users LEFT JOIN sid ON users.id = sid.userID WHERE id = ? LIMIT 1");
    $result->bind_param('i', $_GET['userID']);
    $result->execute();
    $result->bind_result($tmp_title, $tmp_initials, $tmp_surname, $tmp_student_id);
    $result->fetch();
    $result->close();
    
    echo "<strong>$tmp_title $tmp_surname, $tmp_initials ($tmp_student_id)</strong></br />\n";
  } else {
    echo $string['papername'] . " <select name=\"paper\" id=\"paper\">\n<option value=\"\"></option>\n";
    $result = $mysqli->prepare("SELECT DISTINCT property_id, paper_title FROM properties WHERE paper_type = '2' AND deleted IS NULL ORDER BY paper_title");
    $result->execute();
    $result->bind_result($property_id, $paper_title);
    while ($result->fetch()) {
      echo "<option value=\"$property_id\">$paper_title</option>\n";
    }
    echo "</select>\n<br />\n";
    $result->close();
  }
  
  echo "<br />" . $string['note'] . "<br />\n";
  echo "<div style=\"text-align:center\"><textarea name=\"note\" id=\"note\"></textarea></div>\n";
?>
<br />
<div style="text-align:center"><input type="submit" style="width:100px" name="submit" value="<?php echo $string['save']; ?>" />&nbsp;<input style="width:100px" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" /></div>
<input type="hidden" name="tmp_userID" value="<?php echo $_GET['userID']; ?>" />
<input type="hidden" name="calling" value="<?php if (isset($_GET['calling'])) echo $_GET['calling']; ?>" />
</form>

</body>
</html>
<?php
}
$mysqli->close();
?>