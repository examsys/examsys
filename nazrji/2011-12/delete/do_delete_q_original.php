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
  require '../include/errors.inc';

  check_var('q_id', 'POST', true, false);

  $tmp_q_id = $_POST['q_id'];
  $result = $mysqli->prepare("UPDATE questions SET deleted=NOW() WHERE q_id=?");
  $result->bind_param('i', $tmp_q_id);
  $result->execute();  
  $result->close();
  $mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['questiondeleted']; ?></title>
<script language="javascript">
  function updateParent() {
    window.opener.location.reload();
    self.close();
  }
</script>
</head>

<body onload="javascript:updateParent();" style="margin:0px; background-color:#F1F5FB; font-family:Arial,sans-serif; font-size:90%; text-align:justifed">

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/delete_warning.png" width="48" height="48" border="0" alt="<?php echo $string['recyclebin']; ?>" /></td>

<td><p><?php echo $string['msg']; ?><p>

<div style="text-align: center">
<form action="" method="get">
<input type="button" name="cancel" value="<?php echo $string['ok']; ?>" style="width:100px" onclick="javascript:window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>
