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

require '../include/admin_auth.inc';
require '../include/errors.inc';
  
check_var('gridID', 'POST', true, false);

$result = $mysqli->prepare("DELETE FROM ebel_grid_templates WHERE id=?");
$result->bind_param('i', $_POST['gridID']);
$result->execute();  
$result->close();

$mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Grid Deleted</title>
  <script type="text/javascript">
    function closeWindow() {
      window.opener.location.href = '../admin/list_ebel_grids.php';
      self.close();
    }
  </script>
</head>

<body onload="closeWindow();" style="margin:0px; background-color:#F1F5FB; font-family:Arial,sans-serif; font-size:80%; text-align:justifed">

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/assessment_bin.png" width="32" height="32" border="0" alt="Recycle Bin" /></td>

<td><p>Grid template successfully deleted.<p>

<div style="text-align:center">
<form action="" method="get">
<input type="button" name="cancel" value="    OK    " onclick="javascript:self.opener.location.href='/admin/list_ebel_grids.php';window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>