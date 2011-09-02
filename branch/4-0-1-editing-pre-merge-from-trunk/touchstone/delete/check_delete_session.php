<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  
  $tmp_identifier = $_GET['identifier'];
  $tmp_session = $_GET['session'];
  $tmp_moduleID = $_GET['moduleID'];
  
  $question_data = $mysqli->prepare("SELECT DATE_FORMAT(occurrence,'%d/%m/%Y %H:%i'), title FROM sessions WHERE identifier=? AND calendar_year=? AND moduleID=?");
  //$question_data = $mysqli->prepare("SELECT DATE_FORMAT(occurrence,'%d/%m/%Y %H:%i'), title FROM sessions WHERE identifier=3318521689 AND calendar_year='2009/10' AND moduleID='B31B02'");
  $question_data->bind_param('dss', $tmp_identifier, $tmp_session, $tmp_moduleID);
  $question_data->execute();
  $question_data->bind_result($occurrence, $session_title);
  $question_data->fetch();
  $question_data->close();
  
  $mysqli->close();
?>
<html>
<head>
<title>Confirm Session Delete</title>

<style>
  body {margin:0px; background-color:#F1F5FB; font-family:Arial,sans-serif; font-size:80%; text-align:justifed}
</style>
</head>

<body>

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/assessment_bin.png" width="32" height="37" border="0" alt="Recycle Bin" /></td>

<td>
<?php
  echo "<p><strong>$session_title</strong> ($occurrence)</p>\n";
?>
<p>Are you sure you wish to delete this session?<p>

<div style="text-align:right">
<form action="do_delete_session.php" method="post">
<input type="hidden" name="moduleID" value="<?php echo $_GET['moduleID']; ?>" />
<input type="hidden" name="session" value="<?php echo $_GET['session']; ?>" />
<input type="hidden" name="identifier" value="<?php echo $_GET['identifier']; ?>" />
<input type="submit" name="submit" value="Delete Session" />&nbsp;
<input type="button" name="cancel" value=" Cancel " onclick="javascript:window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>