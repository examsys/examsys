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

$unique_moduleid = true;
if (isset($_POST['submit']) and $_POST['moduleid'] != $_POST['old_moduleid']) {
  // Check for unique moduleid
  $tmp_moduleid = trim($_POST['moduleid']);
  $result = $mysqli->prepare("SELECT moduleid FROM modules WHERE moduleid=?");
  $result->bind_param('s', $tmp_moduleid);
  $result->execute();
  $result->store_result();
  $result->bind_result($tmp_moduleid);
  $result->fetch();
  if ($result->num_rows > 0) $unique_moduleid = false;
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

  // Update the properties of the module.
  $tmp_moduleid = trim($_POST['moduleid']);
  $tmp_fullname = trim($_POST['fullname']);
  $tmp_school = $_POST['school'];
  $tmp_sms_api = $_POST['sms_api'];
  $tmp_vle_api = $_POST['vle_api'];
  $tmp_old_moduleid = $_POST['old_moduleid'];
  $tmp_checklist = substr($checklist,1); 
  
  $result = $mysqli->prepare("UPDATE modules SET moduleid=?,fullname=?,active=?, school=?, sms=?, vle_api=?, checklist=? WHERE moduleid=?");
  $result->bind_param('ssisssss', $tmp_moduleid, $tmp_fullname, $active, $tmp_school, $tmp_sms_api, $tmp_vle_api, $tmp_checklist, $tmp_old_moduleid);
  $result->execute();
  $result->close();

  // Update other tables if the Module ID has changed.
  if ($_POST['old_moduleid'] != trim($_POST['moduleid'])) {
    // Teams
    $tmp_moduleid = trim($_POST['moduleid']);
    $tmp_old_moduleid = $_POST['old_moduleid'];
    
    $result = $mysqli->prepare("UPDATE teams SET name=? WHERE name=?");
    $result->bind_param('ss', $tmp_moduleid, $tmp_old_moduleid);
    $result->execute();
    $result->close();
    
    // Properties
    $tmp_old_moduleid = '%' . $_POST['old_moduleid'] . '%';
    $stmt = $mysqli->prepare("SELECT property_id, moduleID FROM properties WHERE moduleID LIKE ?");
    $stmt->bind_param('s', $tmp_old_moduleid);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($paper_property_id, $paper_moduleID);
    while ($stmt->fetch()) {
      $paper_moduleID = str_replace($_POST['old_moduleid'],trim($_POST['moduleid']),$paper_moduleID);
      $result = $mysqli->prepare("UPDATE properties SET moduleID=? WHERE property_id=?");
      $result->bind_param('si', $paper_moduleID, $paper_property_id);
      $result->execute();
      $result->close();
    }
    $stmt->close();

    // Questions
    $tmp_old_moduleid = '%' . $_POST['old_moduleid'] . '%';
    $stmt = $mysqli->prepare("SELECT q_id, q_group FROM questions WHERE q_group LIKE ?");
    $stmt->bind_param('s', $tmp_old_moduleid);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($q_id, $question_moduleID);
    while ($stmt->fetch()) {
      $question_moduleID = str_replace($_POST['old_moduleid'],trim($_POST['moduleid']),$question_moduleID);
      $result = $mysqli->prepare("UPDATE questions SET q_group=? WHERE q_id=?");
      $result->bind_param('si', $question_moduleID, $q_id);
      $result->execute();
      $result->close();
    }
    $stmt->close();
  }

  $mysqli->close();
  header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/admin/list_modules.php");
} else {
  $moduleid = $_GET['moduleid'];
  $stmt = $mysqli->prepare("SELECT moduleid, fullname, active, school, vle_api, checklist, sms FROM modules WHERE moduleid=?");
  $stmt->bind_param('s', $moduleid);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($moduleid, $fullname, $active, $school, $vle_api, $checklist, $sms);
  $stmt->fetch();
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <title>Edit Module</title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />

  <style>
    input, textarea {font-family:Arial,sans-serif; color:black}
    .field {font-weight:bold; text-align:right; padding-right:10px}
  </style>

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
  
  <?php
  if ($unique_moduleid == false) {
  ?>
  function moduleWarning() {
    alert("The module ID <?php echo $tmp_moduleid; ?> is already in use. Please enter an alternative ID.");
  }
  <?php
  }
  ?>
  </script>
  </head>
  <?php
  if ($unique_moduleid == false) {
    echo "<body onload=\"moduleWarning()\">\n";
  } else {
    echo "<body>\n";
  }
  ?>
  <?php
    require '../include/module_options.inc';
  ?>
  <div id="content" class="content" style="font-size:80%">
  <table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr><td style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../index.php">Home</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php">Administrative Tools</a></div><div style="margin-left:10px; font-size:200%; font-weight:bold">Edit Module</div></td></tr>
  <tr><td style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" /></td></tr>
  </table>
  <br />
  <div align="center">
  <form name="myform" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>?moduleid=<?php echo $_GET['moduleid']; ?>">
    <table cellpadding="0" cellspacing="2" border="0" style="text-align:left">
    <?php
    if ($unique_moduleid == false) {
      echo "<tr><td class=\"field\">Module ID</td><td><input type=\"text\" size=\"10\" name=\"moduleid\" style=\"background-color:#FFD9D9; color:#800000; border:1px solid #800000\" value=\"$tmp_moduleid\" /><input type=\"hidden\" name=\"old_moduleid\" value=\"$tmp_moduleid\" /></td></tr>\n";
    } else {
    ?>
      <tr><td class="field">Module ID</td><td><input type="text" size="10" name="moduleid" value="<?php if (isset($_GET['moduleid'])) echo $_GET['moduleid']; ?>" /><input type="hidden" name="old_moduleid" value="<?php echo $moduleid; ?>" /></td></tr>
    <?php
    }
    ?>
    <tr><td class="field">Full name</td><td><input type="text" size="70" name="fullname" value="<?php echo $fullname; ?>" /></td></tr>
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
      if ($row['school'] == $school) {
        echo "<option value=\"" . $row['school'] . "\" selected>" . $row['school'] . "</option>\n";
      } else {
        echo "<option value=\"" . $row['school'] . "\">" . $row['school'] . "</option>\n";
      }
      $old_faculty = $row['faculty'];
    }
    echo "</optgroup>\n</select></td></tr>\n";
    
    if (strpos($checklist,'peer') !== false) {
      $peer = 1;
    } else {
      $peer = 0;
    }
    if (strpos($checklist,'external') !== false) {
      $external = 1;
    } else {
      $external = 0;
    }
    if (strpos($checklist,'stdset') !== false) {
      $stdset = 1;
    } else {
      $stdset = 0;
    }
    if (strpos($checklist,'mapping') !== false) {
      $mapping = 1;
    } else {
      $mapping = 0;
    }
    
  echo '<tr><td class="field">SMS API</td><td><select name="sms_api">';
  foreach ($cfg_sms_sources as $key=>$value) {
    if ($sms == $value) {
      echo "<option value=\"$value\" selected>$key</option>\n";
    } else {
      echo "<option value=\"$value\">$key</option>\n";
    }
  }
  echo '</select></td></tr>';
  ?>
    <tr><td class="field">Objectives API</td><td><select name="vle_api"><option value="">&lt;No lookup&gt;</option><option value="NLE"<?php if ($vle_api == 'NLE') echo ' selected'; ?>>Networked Learning Environment (NLE)</option></select></td></tr>
    <tr><td class="field">Summative Checklist</td><td><input type="checkbox" name="peer"<?php if ($peer == 1) echo ' checked'; ?> /> Peer Review, <input type="checkbox" name="external"<?php if ($external == 1) echo ' checked'; ?> /> External Examiners, <input type="checkbox" name="stdset"<?php if ($stdset == 1) echo ' checked'; ?> /> Standards Setting, <input type="checkbox" name="mapping"<?php if ($mapping == 1) echo ' checked'; ?> /> Mapping</td></tr>
    <tr><td class="field">Active</td><td><input type="checkbox" name="active"<?php if ($active == 1) echo ' checked'; ?> /></td></tr>
  <?php
    echo "</table>\n";
    echo "<input type=\"hidden\" name=\"old_moduleid\" value=\"" . $_GET['moduleid'] . "\" />\n";
  ?>
    <p><input type="submit" style="width:100px" name="submit" value="Save">&nbsp;&nbsp;<input style="width:100px" type="button" name="home" value="Cancel" onclick="javascript:history.back();" /></p>
  </form>
  </div>
</div>
<?php
}
?>
</body>
</html>