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
  
  $tmp_identifier = $_GET['identifier'];
  $tmp_session = $_GET['session'];
  $tmp_moduleID = $_GET['moduleID'];
  
  $question_data = $mysqli->prepare("SELECT DATE_FORMAT(occurrence,'$cfg_long_date_time'), title FROM sessions WHERE identifier=? AND calendar_year=? AND moduleID=?");
  $question_data->bind_param('dss', $tmp_identifier, $tmp_session, $tmp_moduleID);
  $question_data->execute();
  $question_data->bind_result($occurrence, $session_title);
  $question_data->fetch();
  $question_data->close();
  
  $mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title><?php echo $string['confirmsessiondelete']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />
</head>

<body>

<table>
<tr>
<td class="icon"><img src="../artwork/delete_warning.png" width="48" height="48" border="0" alt="<?php echo $string['recyclebin']; ?>" /></td>

<td>
<?php
  echo "<p><strong>$session_title</strong> ($occurrence)</p>\n<p>" . $string['msg'] . "</p>\n";
?>

<div style="text-align:right">
<form action="do_delete_session.php" method="post">
<input type="hidden" name="moduleID" value="<?php echo $_GET['moduleID']; ?>" />
<input type="hidden" name="session" value="<?php echo $_GET['session']; ?>" />
<input type="hidden" name="identifier" value="<?php echo $_GET['identifier']; ?>" />
<input type="submit" name="submit" value="<?php echo $string['deletesession']; ?>" />&nbsp;
<input type="button" name="cancel" value=" <?php echo $string['cancel']; ?> " onclick="javascript:window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>