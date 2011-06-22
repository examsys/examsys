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
* Load new users from SMS export
*
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/errors.inc';
  require './users.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>TouchStone: Load Students<?php echo " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
</head>

<body>
<?php
  include '../include/user_search_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<?php
  if (isset($_POST['submit'])) {
    if ($_FILES['csvfile']['name'] != 'none' and $_FILES['csvfile']['name'] != '') {
      if (!move_uploaded_file($_FILES['csvfile']['tmp_name'], "/tmp/new_cohort.csv"))  {
        echo uploadError($_FILES['csvfile']['error']);
        exit;
      } else {
        $users = add_users_from_file('/tmp/new_cohort.csv');
        unlink('/tmp/new_cohort.csv');
	if (isset($users['error'])) {
	  echo "<p>No users added due to the following errors:</p><ul>";
	  foreach ($users['error'] as $msg) {
	    echo $msg;
          }
          echo "</ul>";
	} else {
	  echo "<ul>\n";
	  echo "<li>" . count($users['added']) . " users added</li>\n";
	  echo "<li>" . count($users['updated']) . " existing users updated</li>\n";
	  echo "</ul>\n";
	}
      }
    }
  } else {
    // Display upload form.
?>
<br />
<br />
<table border="0" width="100%" height="100%" style="font-size:120%">
<tr><td valign="middle">
<div align="center">

<table border="0" cellpadding="4" cellspacing="0" style="border:1px solid #5582D2; width:85%">
<tr>
<td valign="middle" align="left" style="background-color:white"><img src="../artwork/user_female_32.png" width="32" height="26" alt="Icon" />&nbsp;&nbsp;<span style="font-family:Arial,sans-serif; font-size:140%; font-weight:bold; color:#5582D2">Import Students</span></td>
</tr>
<tr>
<td align="left" style="background-color:#DFE8FF">

<p>CSV file should contain the columns in the following order: student_id, Full name, First names, surname, title , Course code, Degree qual aim, Degree Title, Year of course, Mode of study, School, Subject, Attendance status, Registered, Fee Status, Fee band, Date of entry, Local email (NB this is the SATURN export format).</p>

<div>Please select the CVS file you wish to load:</div>


<div align="center">
<form name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
<p><input type="file" size="50" name="csvfile" /></p>

<div align="center"><input type="checkbox" name="welcome" value="1" />&nbsp;Send welcome email to user</div>
<p><input type="submit" style="width:100px" value="Import" name="submit" />&nbsp;<input style="width:100px" type="button" value="Cancel" name="cancel" onclick="history.go(-1)" /></p>
</form>
</div>
</td>
</tr>
</table>

</div>
</td></tr>
</table>
<?php
  }
  $mysqli->close();
?>
</div>

</body>
</html>
