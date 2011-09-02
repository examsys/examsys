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

require '../include/sysadmin_auth.inc';

$unique_degree = true;
if (isset($_POST['submit']) and $_POST['degree'] != $_POST['old_degree']) {
  // Check for unique degree name
  $tmp_degree = trim($_POST['degree']);
  
  $result = $mysqli->prepare("SELECT degree FROM degrees WHERE degree=?");
  $result->bind_param('s', $tmp_degree);
  $result->execute();
  $result->store_result();
  $result->bind_result($tmp_degree);
  $result->fetch();
  if ($result->num_rows > 0) $unique_degree = false;
  $result->free_result();
  $result->close();
}

if (isset($_POST['submit']) and $unique_degree == true) {
  $tmp_degree = trim($_POST['degree']);
  $tmp_school = $_POST['school'];
  $tmp_description = trim($_POST['description']);
  $tmp_degreeID = $_POST['degreeID'];

  $result = $mysqli->prepare("UPDATE degrees SET degree=?, description=?, school=? WHERE id=?");
  $result->bind_param('sssi', $tmp_degree, $tmp_description, $tmp_school, $tmp_degreeID);
  $result->execute();  
  $result->close();
  $mysqli->close();
  header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/admin/list_degrees.php");
} else {
  $degreeID = $_GET['degreeID'];
  $result = $mysqli->prepare("SELECT school, degree, description FROM degrees WHERE id=? LIMIT 1");
  $result->bind_param('i', $degreeID);
  $result->execute();
  $result->bind_result($school, $degree, $description);
  $result->fetch();
  $result->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <title>Edit Degree</title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style>
    input, textarea {font-family:Arial,sans-serif; color:black}
    .field {font-weight:bold; text-align:right; padding-right:10px}
  </style>

  <script language="JavaScript">
  function checkForm() {
    if (edit_degree.degree.value == "") {
      alert ("Please enter a code for the degree.");
      return false;
    }
    if (edit_degree.description.value == "") {
      alert ("Please enter a title for the degree.");
      return false;
    }
  }
  function codeWarning() {
    alert("The degree code <?php echo $tmp_degree; ?> is already in use. Please enter an alternative code.");
  }
  </script>
  </head>
  <?php
  if ($unique_degree == false) {
    echo "<body onload=\"codeWarning()\">\n";
  } else {
    echo "<body>\n";
  }
  require '../include/degree_options.inc';
  ?>
  <div id="content" class="content" style="font-size:80%">
  <table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr><td style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../index.php">Home</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php">Administrative Tools</a></div><div style="margin-left:10px; font-size:200%; font-weight:bold">Edit Degree</div></td></tr>
  <tr><td style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" /></td></tr>
  </table>
  <br />
  <div align="center">
  <form name="edit_degree" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF'] . '?degreeID=' . $degreeID; ?>">
    <table cellpadding="0" cellspacing="2" border="0" style="text-align:left">
    <?php
    if ($unique_degree == false) {
      echo "<tr><td class=\"field\">Code</td><td><input type=\"text\" size=\"10\" name=\"degree\" style=\"background-color:#FFD9D9; color:#800000; border:1px solid #800000\" value=\"$tmp_degree\" /><input type=\"hidden\" name=\"old_degree\" value=\"$tmp_degree\" /></td></tr>\n";
    } else {
      echo "<tr><td class=\"field\">Code</td><td><input type=\"text\" size=\"10\" name=\"degree\" value=\"" . $degree . "\" /><input type=\"hidden\" name=\"old_degree\" value=\"$degree\" /></td></tr>\n";
    }
    ?>
    <tr><td class="field">Title</td><td><input type="text" size="70" name="description" value="<?php echo $description; ?>" /></td></tr>
    <tr><td class="field">School</td><td><select name="school">
    <?php
      $school_details = $mysqli->query("SELECT school FROM schools ORDER BY school");
      while ($school_row = $school_details->fetch_assoc()) {
        if ($school == $school_row['school']) {
          echo "<option value=\"" . $school_row['school'] . "\" selected>" . $school_row['school'] . "</option>\n";
        } else {
          echo "<option value=\"" . $school_row['school'] . "\">" . $school_row['school'] . "</option>\n";
        }
      }
      $school_details->close();
    ?>
    </select></td></tr>
    </table>
    <input type="hidden" name="degreeID" value="<?php echo $degreeID; ?>" />
    <p><input type="submit" style="width:100px" name="submit" value="Save">&nbsp;&nbsp;<input type="button" style="width:100px" name="home" value="Cancel" onclick="javascript:history.back();" /></p>
  </form>
  </div>
<?php
}
$mysqli->close();
?>
</div>
</body>
</html>