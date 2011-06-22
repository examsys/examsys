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
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

require('../include/sysadmin_auth.inc');

if (isset($_POST['submit'])) {
  $addSchool = "INSERT INTO schools VALUES (NULL, \"" . $_POST['faculty'] . "\", '" . $_POST['school'] . "')";
  if (!mysql_query($addSchool, $link_id)) {
    echo "<p class=\"error\">School Add Error</p>\n<p>Query: " . $addSchool . "</p>\n<p>" . mysql_error($link_id) . "</p>\n";
    echo "</body>\n</html>\n";
    exit();
  }
  
  header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/admin/school_list.php");
} else {
?>
  <html>
  <head>
  <title>Add School</title>

  <style>
    body {font-family:Arial,Helvetica,sans-serif; color:black; background-color:white; margin:0px}
    td {font-size:90%}
    input, textarea {font-family:Arial,Helvetica,sans-serif; color:black}
    .field {font-weight:bold; text-align:right; padding-right:10px}
  </style>

  <script language="JavaScript">
  function checkForm() {
    if (add_school.school.value == "" or add_school.school.value == "School of") {
      alert ("Please enter name for the school.");
      return false;
    }
  }
  </script>
  </head>
  <body>
  <table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr><td style="background-color:#EBEADB"><div style="font-size:220%; font-weight:bold"><a onmouseover="move_in('image1','../artwork/up_folder_icon_on.gif')" onmouseout="move_out('image1','../artwork/up_folder_icon_off.gif')" href="degree_list.php"><img name="image1" src="../artwork/up_folder_icon_off.gif" width="32" height="38" alt="Up" border="0" /></a>&nbsp;Add Degree</div></td></tr>
  <tr><td style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" /></td></tr>
  </table>
  <br />
  <div align="center">
  <form name="add_school" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <table cellpadding="0" cellspacing="2" border="0">
    <tr><td class="field">School</td><td><input type="text" size="70" name="school" value="School of" /></td></tr>
    <tr><td class="field">Faculty</td><td><select name="faculty">
    <option value=""></option>
    <?php
      $school_details = mysql_query("SELECT DISTINCT faculty FROM schools ORDER BY faculty, id",$link_id);
      while ($school_row = mysql_fetch_array($school_details)) {
        echo "<option value=\"" . $school_row['faculty'] . "\">" . $school_row['faculty'] . "</option>\n";
      }
    ?>
    </select></td></tr>
    </table>
    <p><input type="submit" style="width:100px" name="submit" value="Add">&nbsp;&nbsp;<input style="width:100px" type="button" name="home" value="Cancel" onclick="javascript:history.back();" /></p>
  </form>
  </div>
<?php
}
?>
</body>
</html>