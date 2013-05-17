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

require '../include/sysadmin_auth.inc';
require_once '../include/errors.inc';
require_once '../classes/userutils.class.php';

$userID = check_var('id', 'POST', true, false, true);

// Check that all the past user IDs actually exist.
$id_list = explode(',', $userID);
foreach ($id_list as $id) {
  if ($id != '') {
    if (!UserUtils::userid_exists($id, $mysqli)) {
      $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
      $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
    }
  }
}

foreach ($id_list as $single_id) {
  if ($single_id != '') {
    $result = $mysqli->prepare("UPDATE users SET user_deleted = NOW() WHERE id = ?");
    $result->bind_param('i', $single_id);
    $result->execute();  
    $result->close();
  }
}

$mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>User Deleted</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />

  <script type="text/javascript">
    function updateParent() {
      window.opener.location.reload();
      self.close();
    }
  </script>
</head>

<body onload="javascript:updateParent();" style="background-color:#F1F5FB; font-size:90%; text-align:justifed">

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/delete_warning.png" width="48" height="48" alt="Recycle Bin" /></td>

<td><p>User successfully deleted.<p>

<div style="text-align: center">
<form action="" method="get">
<input type="button" name="cancel" value="    OK    " onclick="javascript:window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>
