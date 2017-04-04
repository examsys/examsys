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
* Changes a summative exam into a formativ quiz. Part of summative exam scheduling system.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/admin_auth.inc';
require_once '../include/errors.php';

$paperid = check_var('paperID', 'POST', true, false, true);

if (!Paper_utils::paper_exists($paperid, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

// Remove the record from scheduling.
$result = $mysqli->prepare("DELETE FROM scheduling WHERE paperID = ?");
$result->bind_param('i', $paperid);
$result->execute();  
$result->close();

// Set start/end dates and the type to 0 (i.e. formative).
$now = date("Y-m-d H:i:s");
$assessment = new assessment($mysqli, $configObject);
$update_params = array(
    'start_date' => array('s', $now),
    'end_date' => array('s', $now),
    'paper_type' => array('s' , $assessment::TYPE_FORMATIVE)
);
$assessment->db_update_assessment($paperid, $update_params);
$mysqli->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Paper Converted</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {background-color:#F1F5FB; font-size:80%; text-align:justifed}
  </style>
  
  <script>
    function closeWindow() {
      window.opener.location.href = 'summative_scheduling.php';
      self.close();
    }
  </script>
</head>

<body onload="closeWindow();">

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/formative.png" width="32" height="32" border="0" alt="" /></td>

<td><p>Paper successfully converted.<p>

<div style="text-align:center">
<form action="" method="get" autocomplete="off">
<input type="button" name="cancel" value="    OK    " onclick="javascript:self.opener.location.href='summative_scheduling.php'; window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>