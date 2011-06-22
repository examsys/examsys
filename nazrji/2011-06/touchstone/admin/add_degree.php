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
if (isset($_POST['submit'])) {
  // Check for unique username
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
  $tmp_school = $_POST['school'];
  $tmp_degree = trim($_POST['degree']);
  $tmp_description = trim(stripslashes($_POST['description']));
  
  $result = $mysqli->prepare("INSERT INTO degrees VALUES (NULL,?,?,?)");
  $result->bind_param('sss', $tmp_school, $tmp_degree, $tmp_description);
  $result->execute();  
  $result->close();
  $mysqli->close();
  header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/admin/list_degrees.php");
} else {
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <title>Add Degree</title>
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
  </script>
  </head>
  
  <body>
  <?php
    require '../include/degree_options.inc';
  ?>
  <div id="content" class="content" style="font-size:80%">
  <table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr><td style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../index.php">Home</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php">Administrative Tools</a></div><div style="margin-left:10px; font-size:200%; font-weight:bold">Create new Degree</div></td></tr>
  <tr><td style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" /></td></tr>
  </table>
  <br />
  <div align="center">
  <form name="edit_degree" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <table cellpadding="0" cellspacing="2" border="0" style="text-align:left">
    <?php
    if ($unique_degree == false) {
      echo "<tr><td class=\"field\">Code</td><td><input type=\"text\" size=\"10\" name=\"degree\" style=\"background-color:#FFD9D9; color:#800000; border:1px solid #800000\" value=\"$tmp_degree\" /></td></tr>\n";
    } else {
    ?>
      <tr><td class="field">Code</td><td><input type="text" size="10" name="degree" value="<?php if (isset($_GET['moduleid'])) echo $_GET['moduleid']; ?>" /></td></tr>
    <?php
    }
    ?>
    <tr><td class="field">Title</td><td><input type="text" size="70" name="description" value="<?php if (isset($_POST['description'])) echo stripslashes($_POST['description']); ?>" /></td></tr>
    <tr><td class="field">School</td><td><select name="school">
    <option value=""></option>
    <?php
      $school_details = $mysqli->query("SELECT school, faculty FROM schools ORDER BY faculty, school");
      $old_faculty = '';
      while ($school_row = $school_details->fetch_assoc()) {
        if ($school_row['faculty'] != $old_faculty) {
          if ($old_faculty != '') echo "</optgroup>\n";
          echo "<optgroup label=\"" . $school_row['faculty'] . "\">\n";
        }
        if (isset($_POST['school']) and $_POST['school'] == $school_row['school']) {
          echo "<option value=\"" . $school_row['school'] . "\" selected>" . $school_row['school'] . "</option>\n";
        } else {
          echo "<option value=\"" . $school_row['school'] . "\">" . $school_row['school'] . "</option>\n";
        }
        $old_faculty = $school_row['faculty'];
      }
      echo "</optgroup>\n";
      $school_details->close();
    ?>
    </select></td></tr>
    </table>
    <p><input type="submit" style="width:100px" name="submit" value="Add">&nbsp;&nbsp;<input style="width:100px" type="button" name="home" value="Cancel" onclick="javascript:history.back();" /></p>
  </form>
  </div>
<?php
}
$mysqli->close();
?>
</div>

</body>
</html>