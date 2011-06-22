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
require_once '../classes/dateutils.class.php';

$unique_moduleid = true;
if (isset($_POST['submit'])) {
  // Check for unique moduleID
  $moduleid = trim($_POST['moduleid']);
  $result = $mysqli->prepare("SELECT moduleid FROM modules WHERE moduleid=?");
  $result->bind_param('s', $moduleid);
  $result->execute();
  $result->store_result();
  $result->bind_result($tmp_moduleid);
  $result->fetch();
  if ($result->num_rows > 0) {
    $unique_moduleid = false;
  }
  $result->free_result();
  $result->close();
}

if (isset($_POST['submit']) and $unique_moduleid == true) {
  if (isset($_POST['active'])) {
    $active = 1;
  } else {
    $active = 0;
  }
  $checklist = '';
  if (isset($_POST['peer'])) $checklist .= ',peer';
  if (isset($_POST['external'])) $checklist .= ',external';
  if (isset($_POST['stdset'])) $checklist .= ',stdset';
  if (isset($_POST['mapping'])) $checklist .= ',mapping';

  $fullname = trim(stripslashes($_POST['fullname']));
  $tmp_checklist = substr($checklist,1);
  
  $result = $mysqli->prepare("INSERT INTO modules VALUES (NULL,?,?,?,?,?,?,?)");
  $result->bind_param('ssissss', $moduleid, $fullname, $active, $_POST['school'], $_POST['vle_api'], $tmp_checklist, $_POST['sms_api']);
  $result->execute();
  $result->close();

  if (isset($_POST['sms_api']) and $_POST['sms_api'] != '') {
    // Look up SATURN
    $enrolments = 0;
      
    // Get the current academic session
    $session = DateUtils::get_current_academic_year();
    $session_parts = explode('/',$session);

    $module = trim($_POST['moduleid']);
    // UoN code to strip off prefix codes.
    //------------------------------------
    $replaced_module = str_replace('_UNMC','',$module);
    $replaced_module = str_replace('_UNNC','',$replaced_module);
    //------------------------------------
    
    $url = $_POST['sms_api'] . "&code=$replaced_module&year=" . $session_parts[0];
    $returned_data = file_get_contents($url);
    $xml = new SimpleXMLElement($returned_data);
    $enrolement_details = '';

    foreach ($xml->Module->Membership->Student as $student) {
      $student->Title = trim($student->Title);
      $student->Surname = trim($student->Surname);
      $student->Forename = trim($student->Forename);
      $student->CourseCode = trim($student->CourseCode);
      $student->Username = trim($student->Username);
      $student->Email = trim($student->Email);
      $student->Faculty = trim($student->Faculty);
      $student->Gender = trim($student->Gender);
      $student->YearofStudy = trim($student->YearofStudy);
      $student->Faculty = trim($student->Faculty);
     
      $student_data = $mysqli->prepare("SELECT id FROM users WHERE username=? LIMIT 1");            // Do they have a TouchStone user record?
      $student_data->bind_param('s', $student->Username);
      $student_data->execute();
      $student_data->store_result();
      $student_data->bind_result($tmp_userID);
      $student_data->fetch();
      if ($student_data->num_rows == 0) {
        // Create new account for the user
        $tmp_year = 'year' . $student->YearofStudy;
        $names = explode(' ',$student->Forename);
        $initials = '';
        foreach ($names as $tmp_name) {
          $initials .= substr($tmp_name,0,1);
        }
        
        $result = $mysqli->prepare("INSERT INTO users VALUES ('',?,?,?,?,?,?,'Student',NULL,?,?,?,NULL,0,?)");
        $result->bind_param('sssssssssi', $student->CourseCode, $student->Surname, $initials, $student->Title, $student->Username, $student->Email, $student->Faculty, $student->Forename, $student->Gender, $sms->YearofStudy);
        $result->execute();
        $result->close();
         
        $tmp_userID = $mysqli->insert_id;    // Get the new TouchStone userID
      }
      $student_data->close();
      
      $tmp_studentID = trim($student->StudentID);
      $result = $mysqli->prepare("INSERT INTO sid VALUES (?,?)");
      $result->bind_param('si', $tmp_studentID, $tmp_userID);
      $result->execute();
      $result->close();

      // Add student onto the module
      $result = $mysqli->prepare("INSERT INTO student_modules VALUES (NULL,?,?,?,1,1)");
      $result->bind_param('iss', $tmp_userID, $module, $session);
      $result->execute();
      $result->close();
      $enrolments++;
      if ($enrolement_details == '') {
        $enrolement_details = $student->Username;
      } else {
        $enrolement_details .= ',' . $student->Username;
      }
    }

    // Write in a record to sms_imports table
    if ($enrolments > 0) {
      if ($_POST['sms_api'] == 'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=malaysia') {
        $import_type = 'SATURN Malaysia';
      } elseif ($_POST['sms_api'] == 'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=china') {
        $import_type = 'SATURN China';
      } else {
        $import_type = 'SATURN UK';
      }
      
      $result = $mysqli->prepare("INSERT INTO sms_imports VALUES (NULL,NOW(),?,?,?,0,'',?)");
      $result->bind_param('siss', $module, $enrolements, $enrolement_details, $import_type);
      $result->execute();
      $result->close();
    }
  }

  $mysqli->close();
  header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/admin/list_modules.php");
} else {
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <title>Create new Module</title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />

  <style>
    input, textarea {font-family:Arial,sans-serif; color:black}
    .field {font-weight:bold; text-align:right; padding-right:10px}
  </style>

  <script src="../javascript/staff_help.js" type="text/javascript"></script>
  <script language="JavaScript">
  function checkForm() {
    if (myform.moduleid.value == "") {
      alert ("Please enter an Identifier for the module.");
      return false;
    }
    if (myform.fullname.value == "") {
      alert ("Please enter a title for the module.");
      return false;
    }
  }
  </script>
  </head>
  
  <body>
  <?php
    require '../include/module_options.inc';
  ?>
  <div id="content" class="content" style="font-size:80%">
  <table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr><td style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../index.php">Home</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php">Administrative Tools</a></div><div style="margin-left:10px; font-size:200%; font-weight:bold">Create new Module</div></td><td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td></tr>
  <tr><td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" /></td></tr>
  </table>
  <br />
  <div align="center">
  <form name="myform" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <table cellpadding="0" cellspacing="2" border="0" style="text-align:left">
    <?php
    if ($unique_moduleid == false) {
      echo "<tr><td class=\"field\">Module ID</td><td><input type=\"text\" size=\"10\" name=\"moduleid\" style=\"background-color:#FFD9D9; color:#800000; border:1px solid #800000\" value=\"$tmp_moduleid\" /></td></tr>\n";
    } else {
      echo "<tr><td class=\"field\">Module ID</td><td><input type=\"text\" size=\"10\" name=\"moduleid\" value=\"";
      if (isset($_GET['moduleid'])) echo $_GET['moduleid'];
      echo "\" /></td></tr>\n";
    }
    ?>
    <tr><td class="field">Full name</td><td><input type="text" size="70" name="fullname" value="<?php if (isset($_POST['fullname'])) echo $_POST['fullname']; ?>" /></td></tr>
    
<?php
  $old_faculty = '';
  echo "<tr><td class=\"field\">School</td><td><select name=\"school\">\n<option value=\"\"></option>\n";
  $query_string = "SELECT school, faculty FROM schools ORDER BY faculty, school";
  $results = $mysqli->query($query_string);
  while ($row = $results->fetch_assoc()) {
    if ($old_faculty != $row['faculty']) {
      if ($old_faculty != '') echo "</optgroup>\n";
      echo "<optgroup label=\"" . $row['faculty'] . "\">\n";
    }
    if (isset($_POST['school']) and $_POST['school'] == $row['school']) {
      echo "<option value=\"" . $row['school'] . "\" selected>" . $row['school'] . "</option>\n";
    } else {
      echo "<option value=\"" . $row['school'] . "\">" . $row['school'] . "</option>\n";
    }
    $old_faculty = $row['faculty'];
  }
  echo "</optgroup>\n</select></td></tr>\n";
  
  echo '<tr><td class="field">SMS API</td><td><select name="sms_api">';
  foreach ($cfg_sms_sources as $key=>$value) {
    echo "<option value=\"$value\">$key</option>\n";
  }
  echo '</select></td></tr>';
?>
    <tr><td class="field">Objectives API</td><td><select name="vle_api">
    <option value="">&lt;No lookup&gt;</option>
    <option value="NLE"<?php if (isset($_POST['vle_api']) and $_POST['vle_api'] == 'NLE') echo ' selected'; ?>>Networked Learning Environment (NLE)</option>
    </select></td></tr>
    <tr><td class="field">Summative Checklist</td><td><input type="checkbox" name="peer" checked /> Peer Review, <input type="checkbox" name="external" checked /> External Examiners, <input type="checkbox" name="stdset" /> Standards Setting, <input type="checkbox" name="mapping" /> Mapping</td></tr>
    <tr><td class="field">Active</td><td><input type="checkbox" name="active" checked /></td></tr>
    </table>
    <p><input type="submit" style="width:100px" name="submit" value="Add">&nbsp;&nbsp;<input style="width:100px" type="button" name="home" value="Cancel" onclick="javascript:history.back();" /></p>
  </form>
  </div>
</div>
<?php
}
?>
</body>
</html>