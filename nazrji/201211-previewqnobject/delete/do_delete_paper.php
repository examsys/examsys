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
  
  check_var('paperID', 'POST', true, false);

  // Set the deleted field to now and appened the date onto the paper title.
  // This will allow someone to make a new paper with the same name as that being deleted.
  $tmp_paperID = $_POST['paperID'];
  $result = $mysqli->prepare("UPDATE properties SET deleted=NOW(), paper_title=CONCAT(paper_title,' [deleted ',DATE_FORMAT(NOW(),'%d/%m/%Y'),']') WHERE property_id=?");
  $result->bind_param('i', $tmp_paperID);
  $result->execute();  
  $result->close();

  $mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title><?php echo $string['questiondeleted']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  
  <script language="javascript">
    function closeWindow() {
      <?php
        if ($_POST['module'] != '') {
          echo "self.opener.location.href = '../folder/details.php?module=" . $_POST['module'] . "';\n";
        } elseif ($_POST['folder'] != '') {
          echo "self.opener.location.href = '../folder/details.php?folder=" . $_POST['folder'] . "';\n";
        } else {
          echo "self.opener.location.href = '../index.php';\n";
        }
      ?>
      self.close();
    }
  </script>
</head>

<body onload="closeWindow();" style="background-color:#F1F5FB; font-size:90%; text-align:justifed">

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/delete_warning.png" width="48" height="48" alt="<?php echo $string['recyclebin']; ?>" /></td>

<td><p><?php echo $string['msg']; ?><p>

<div style="text-align: center">
<form action="" method="get">
<input type="button" name="cancel" value="    <?php echo $string['ok']; ?>    " onclick="javascript:self.opener.location.href='../folder/details.php?module=<?php echo $_POST['module']; ?>&folder=<?php echo $_POST['folder']; ?>';window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>