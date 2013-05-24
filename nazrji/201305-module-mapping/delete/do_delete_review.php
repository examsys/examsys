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

$setterID = check_var('setterID', 'POST', true, false, true);
$dateID   = check_var('dateID', 'POST', true, false, true);
$paperID  = check_var('paperID', 'POST', true, false, true);

$row_no = 0;
$result = $mysqli->prepare("SELECT id FROM standards_setting WHERE paperID = ? AND setterID = ? AND std_set = ?");
$result->bind_param('iis', $paperID, $setterID, $dateID);
$result->execute();  
$result->store_result();
$row_no = $result->num_rows;
$result->close();

if ($row_no == 0) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

// Delete from standards setting table.
$result = $mysqli->prepare("DELETE FROM standards_setting WHERE paperID = ? AND setterID = ? AND std_set = ?");
$result->bind_param('iis', $paperID, $setterID, $dateID);
$result->execute();  
$result->close();

// Delete from ebel table.
$result = $mysqli->prepare("DELETE FROM ebel WHERE setterID = ? and date_set = ?");
$result->bind_param('is', $setterID, $dateID);
$result->execute();
$result->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Review Deleted</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />

  <script type="text/javascript">
    function closeWindow() {
      window.opener.top.location.reload();
      self.close();
    }
  </script>
</head>

<body onload="closeWindow();" style="background-color:#F1F5FB; font-size:80%; text-align:justifed">

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/delete_warning.png" width="48" height="48" alt="Recycle Bin" /></td>

<td><p>Standards setting review successfully deleted.<p>

<div style="text-align:center">
<form action="" method="get">
<input type="button" name="cancel" value="    OK    " onclick="javascript:window.opener.top.location.reload();window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>