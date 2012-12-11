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
* @author Ben Parish
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/errors.inc';
  require '../include/load_config.php';

  check_var('paperID', 'GET', true, false);
  check_var('userID', 'GET', true, false);

  $mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get( 'cfg_page_charset' ); ?>" />
  <title><?php echo $string['confirmreset']; ?></title>

  <style type="text/css">
  body {margin:0px; background-color:#F1F5FB; font-family:Arial,sans-serif; font-size:90%; text-align:justifed}
  </style>
</head>

<body>

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
  <td>
    <p><?php echo $string['msg'] ; ?><p>
    <br />

    <div style="text-align:right">
    <form action="do_reset_timer.php" method="post">
    <input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" />
    <input type="hidden" name="userID" value="<?php echo $_GET['userID']; ?>" />
    <input style="width:140px" type="submit" name="submit" value="<?php echo $string['resettimer'] ; ?>" />&nbsp;
    <input style="width:80px" type="button" name="cancel" value=" <?php echo $string['cancel']; ?> " onclick="javascript:window.close();" />
    </form>
    </div>
  </td>
</tr>
</table>

</body>
</html>