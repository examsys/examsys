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
* Import student module registrations form SMS export
*
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/admin_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
  <title>TouchStone: Import Modules<?php echo " $cfg_install_type"; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style>
    p {color:#003366; font-family:Arial,sans-serif; margin:0px; padding:0px}
    h1 {font-size:120%; font-weight:bold}
    img { border-style:none; border-width:0px)
  </style>
  </head>

  <body>
<?php
  require '../include/user_search_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<br />
<br />
<?php
  if (isset($_POST['submit'])) {
    if ($_FILES['csvfile']['name'] != 'none' and $_FILES['csvfile']['name'] != '') {
      if (!move_uploaded_file($_FILES['csvfile']['tmp_name'], "/tmp/cohort_update.csv"))  {
        echo 'Problem - ';
        if ($_FILES['csvfile']['error'] == "0") {
          echo("Value 0: No problem, the file is uploaded.");
        } elseif ($_FILES['csvfile']['error'] == "1") {
          echo("Value 1: The uploaded file was bigger then  upload_max_filesize in php.ini.");
        } elseif ($_FILES['csvfile']['error'] == "2") {
          echo("Value 2: The uploaded file was bigger then MAX_FILE_SIZE in html-form.");
        } elseif ($_FILES['csvfile']['error'] == "3") {
          echo("Value 3: File partialy uploaded.");
        } elseif ($_FILES['csvfile']['error'] == "4") {
          echo("Value 4: No file was uploaded.");
        } else {
          echo("Other problem: " . $_FILES['csvfile']['error']);
        }
        exit;
      } else {
        ?>
        <br /><br /><br />
        <div align="center">
        <table border="0" cellpadding="4" cellspacing="0" style="border:1px solid #5582D2; font-size:120%">
        <tr>
        <td valign="middle" align="left" style="background-color:white"><img src="/touchstone/artwork/users_icon_big.gif" width="32" height="26" alt="Icon" />&nbsp;&nbsp;<span style="font-family:Arial,sans-serif; font-size:140%; font-weight:bold; color:#5582D2">Adding Modules From (<?php echo $_FILES['csvfile']['name'] ;?>)</span></td>
        </tr>
        <tr>
        <td align="left" style="background-color:#DFE8FF">

        <?php
        //get a list of touchstone modules
        $SQL = "SELECT DISTINCT moduleid FROM touchstone.modules";
        $res = $mysqli->query($SQL) OR die(mysql_error());
        $touchstone_modules = Array();
        while($row = $res->fetch_assoc()) {
          $touchstone_modules[] = $row['moduleid'];
        }

        $modulesAdded = 0;
        $missing_users = Array();
        $lines = file("/tmp/cohort_update.csv");

        // Build an array of unique student names.
        $students = array();
        foreach($lines as $separate_line) {
          if (strpos($separate_line,'"') !== false) {
            $separate_line = str_replace('","','~',$separate_line);
            $separate_line = str_replace('"','',$separate_line);
          } else {
            $separate_line = str_replace(',','~',$separate_line);
          }
          $fields = explode('~',$separate_line);
          $email = trim($fields[12]);
          $username = explode("@",$email);
          $username = $username[0];
          $session = trim('20' . $fields[1]);
          $students[$username]['username'] = $username;
          $students[$username]['session'] = $session;
          $students[$username]['modules'] = array();
        }

        // Query the modules for each student
        foreach ($students as $student) {

          $result = $mysqli->prepare("SELECT id FROM users WHERE username=?");
          $result->bind_param('s', $student['username']);
          $result->execute();
          $result->store_result();
          $result->bind_result($student_databaseID);

          if ($result->num_rows() > 0) {
            $result->fetch();
            $students[$student['username']]['dbID'] = $student_databaseID;

            $result2 = $mysqli->prepare("SELECT moduleid FROM student_modules WHERE userID=? AND calendar_year=?");
            $result2->bind_param('is', $student_databaseID, $student['session']);
            $result2->execute();
            $result2->store_result();
            $result2->bind_result($moduleid);
            while ($result2->fetch()) {
              if (in_array($moduleid,$touchstone_modules)) {
                $students[$student['username']]['modules'][] = $moduleid;
              }
            }
            $result2->close();
          }

          $result->close();
        }

        foreach($lines as $separate_line) {
          if (strpos($separate_line,'"') !== false) {
            $separate_line = str_replace('","','~',$separate_line);
            $separate_line = str_replace('"','',$separate_line);
          } else {
            $separate_line = str_replace(',','~',$separate_line);
          }
          $fields = explode('~',$separate_line);

          if (!stristr($fields[0],"Module Mnem")) {
            $sid = trim($fields[3]);
            $module = $fields[0];
            $session = trim('20' . $fields[1]);
            $email = trim($fields[12]);
            $username = explode("@",$email);
            $username = $username[0];
            if (in_array($module,$touchstone_modules)) {
              if (!in_array($module,$students[$username]['modules'])) {
                $tmp_userID = $students[$username]['dbID'];
                if ($tmp_userID != '') {
                  $update = $mysqli->prepare("INSERT INTO student_modules VALUES(NULL, ?, ?, ?, 1, 0)");
                  $update->bind_param('iss', $tmp_userID, $module, $session);
                  $update->execute();
                  $update->close();
                } else {
                }
                $modulesAdded++;
              } else {
                $missing_users[$sid]['module'][] = $module;
                $missing_users[$sid]['forname'] = $fields[2];
                $missing_users[$sid]['surname'] = $fields[1];
              }
            }
          }
        }
      }
    }
    unlink("/tmp/cohort_update.csv");

    echo "<h2>$modules Added Modules added</h2>";
    echo "<p>" . count($missing_users) . " Missing users</p>";
    foreach($missing_users as $sid => $module) {
      echo "<p>$sid:" . $module['surname'] . ', ' . $module['forname'] . "</p>";
      foreach($module['module'] as $moduleid) {
        echo "<p style=\"margin-left:10px\">$moduleid</p>";
      }
    }

    $mysqli->close();
    ?>
    </div>
    </td>
    </tr>
    </table>
    </div>
    </td></tr>
    </table>
    <?php
  } else {
?>
<table border="0" width="100%" height="100%">
<tr><td valign="middle">
<div align="center">
<table border="0" cellpadding="4" cellspacing="0" style="width:70%; border:1px solid #5582D2">
<tr>
<td valign="middle" align="left" style="background-color:white"><img src="../artwork/modules_icon_32.gif" width="32" height="32" alt="Icon" />&nbsp;&nbsp;<span style="font-family:Arial,sans-serif; font-size:140%; font-weight:bold; color:#5582D2">Import Modules</span></td>
</tr>
<tr>
<td align="left" style="background-color:#DFE8FF">

<p>CSV file should be in the SATURN export format. Each csv file should contain all the students registered
to the school. (Data can be obtained from SATURN using 'Student Exports / Modules II / Faculty of Medicine')</p>
<br />
<div>Please select the CVS file you wish to load:</div>
<br />
<div align="center">
<form name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
<p><input type="file" size="50" name="csvfile" /></p>
<br />
<p><input type="submit" style="width:100px" value="Import" name="submit" />&nbsp;<input style="width:100px" type="button" value="Cancel" name="cancel" onclick="history.go(-1)" /></p>
</form>
</div>
</td>
</tr>
</table>

</div>
</td></tr>
</table>
</div>
<?php
  }
?>
</body>
</html>