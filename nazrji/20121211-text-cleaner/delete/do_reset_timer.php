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
require '../include/load_config.php';
require '../classes/logstarttime.class.php';

check_var( 'paperID', 'POST', true, false);
check_var( 'userID', 'POST', true, false);

$userID  = $_POST[ 'userID' ];
$paperID = $_POST[ 'paperID' ];

$log_start_time = new LogStartTime( $userID, $paperID, $mysqli );
$start_time     = $log_start_time->delete();


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get( 'cfg_page_charset' ); ?>" />
  <title><?php echo $string['timerreset']; ?></title>
  <script language="javascript">
    function closeWindow() {
      self.close();
    }
  </script>
</head>

<body onload="closeWindow();" style="margin:0px; background-color:#F1F5FB; font-family:Arial,sans-serif; font-size:90%; text-align:justifed">

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td><p><?php echo $string['msg']; ?><p>

<div style="text-align: center">
<form action="" method="get">
<input type="button" name="cancel" value="    <?php echo $string['ok']; ?>    " onclick="window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>