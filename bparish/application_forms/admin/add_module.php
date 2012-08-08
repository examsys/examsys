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

require_once '../include/sysadmin_auth.inc';

require_once '../classes/dateutils.class.php';
require_once '../classes/smsutils.class.php';
require_once '../classes/moduleutils.class.php';
require_once '../classes/userutils.class.php';

$SMS = SMSutils::GetSmsUtils();
$cfg_sms_sources = array();
if (is_object($SMS)) {
  $cfg_sms_sources =  $SMS->getModuleSources();
}
  
$unique_moduleid = true;
if (isset($_POST['submit'])) {
  // Check for unique moduleID
  //TODO this has been moved to moduleutils
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
  if (isset($_POST['selfenroll'])) {
    $selfenroll = 1;
  } else {
    $selfenroll = 0;
  }
  if (isset($_POST['neg_marking'])) {
    $neg_marking = 1;
  } else {
    $neg_marking = 0;
  }
  $fullname = $schoolid = $vle_api = $sms_api = '';
  $peer = $stdset = $mapping = false;
  
  if (isset($_POST['fullname']))  $fullname = trim($_POST['fullname']);
  if (isset($_POST['peer']))      $peer = true;
  if (isset($_POST['external']))  $external = true;
  if (isset($_POST['stdset']))    $stdset = true;
  if (isset($_POST['mapping']))   $mapping = true;
  if (isset($_POST['schoolid']))  $schoolid = $_POST['schoolid'];
  if (isset($_POST['vle_api']))   $vle_api = $_POST['vle_api'];
  if (isset($_POST['sms_api']))   $sms_api = $_POST['sms_api'];
  
  $ebel_grid_template = $_POST['ebel_grid_template'];
  
  module_utils::add_modules($moduleid, $fullname, $active, $schoolid, $vle_api, $sms_api, $selfenroll, $peer, $external, $stdset, $mapping, $neg_marking, $ebel_grid_template, $mysqli);
  
  if (isset($_POST['sms_api']) and $_POST['sms_api'] != '') {
    $enrolements = 0;
      
    // Get the current academic session
    $session = date_utils::get_current_academic_year();
    $session_parts = explode('/',$session);

    $module = trim($_POST['moduleid']);
    // UoN code to strip off prefix codes.
    //------------------------------------
    $replaced_module = str_replace('_UNMC','',$module);
    $replaced_module = str_replace('_UNNC','',$replaced_module);
    //------------------------------------
    
    $url = $_POST['sms_api'] . "&code=$replaced_module&year=" . $session_parts[0];
    $returned_data = @file_get_contents($url);
    if ($returned_data !== false) {
      $xml = new SimpleXMLElement($returned_data);
      $enrolement_details = '';
      
      if (isset($xml->Module)) {
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
          
          // Create new account for the user
          $names = explode(' ',$student->Forename);
          $initials = '';
          foreach ($names as $tmp_name) {
            $initials .= substr($tmp_name,0,1);
          }
          $tmp_userID = UserUtils::usernameExists($student->Username, $mysqli);
          if ($tmp_userID === false) {
            $tmp_userID = UserUtils::createUser($student->Username, '', $student->Title, $student->Forename, $student->Surname, $student->Email, $student->CourseCode, $student->Gender, $student->YearofStudy, 'Student', $student->StudentID, $mysqli);
          }
          // Add student onto the module
          UserUtils::add_student_to_module($tmp_userID, $module, 1, $session, $mysqli);
          
          $enrolements++;
          if ($enrolement_details == '') {
            $enrolement_details = $student->Username;
          } else {
            $enrolement_details .= ',' . $student->Username;
          }
        }
      }
    }

    // Write in a record to sms_imports table
    if ($enrolements > 0) {
      if ($_POST['sms_api'] == 'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=malaysia') {
        $import_type = 'SATURN Malaysia';
      } elseif ($_POST['sms_api'] == 'http://saturn-exports.nottingham.ac.uk/touchstone.ashx?campus=china') {
        $import_type = 'SATURN China';
      } else {
        $import_type = 'SATURN UK';
      }
      
      $result = $mysqli->prepare("INSERT INTO sms_imports VALUES (NULL, NOW(), ?, ?, ?, 0, '', ?)");
      $result->bind_param('siss', $module, $enrolements, $enrolement_details, $import_type);
      $result->execute();
      $result->close();
    }
  }

  $mysqli->close();
  header("location: list_modules.php");
} else {
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Create new Module<?php echo " $cfg_install_type"; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    input, textarea {font-family:Arial,sans-serif; color:black}
    .field {font-weight:bold; text-align:right; padding-right:10px}
  </style>

  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script language="JavaScript">
    function checkForm() {
      if (myform.moduleid.value == "") {
        alert ("<?php echo $string['entermoduleid']; ?>");
        return false;
      }
      if (myform.fullname.value == "") {
        alert ("<?php echo $string['entermoduletitle']; ?>");
        return false;
      }
    }

    function showHideGrid() {
      if (document.getElementById('stdset').checked) {
        document.getElementById('ebelgrid').style.display = 'block';
      } else {
        document.getElementById('ebelgrid').style.display = 'none';
      }
    }
  </script>
  </head>
  
  <body>
  <?php
    require '../include/module_options.inc';
  ?>
  <div id="content" class="content" style="font-size:80%">
  <table class="header">
  <tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['createmodule']; ?></div></th><th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></th></tr>
  <tr><th colspan="2" class="bevel"></th></tr>
  </table>
  <br />
  <div align="center">
  <form name="myform" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <table cellpadding="0" cellspacing="2" border="0" style="text-align:left">
    <?php
    if ($unique_moduleid == false) {
      echo "<tr><td class=\"field\">" . $string['moduleid'] . "</td><td><input type=\"text\" size=\"10\" name=\"moduleid\" style=\"background-color:#FFD9D9; color:#800000; border:1px solid #800000\" value=\"$tmp_moduleid\" /></td></tr>\n";
    } else {
      echo "<tr><td class=\"field\">" . $string['moduleid'] . "</td><td><input type=\"text\" size=\"10\" name=\"moduleid\" value=\"";
      if (isset($_GET['moduleid'])) echo $_GET['moduleid'];
      echo "\" /></td></tr>\n";
    }
    ?>
    <tr><td class="field"><?php echo $string['name']; ?></td><td><input type="text" size="70" name="fullname" value="<?php if (isset($_POST['fullname'])) echo $_POST['fullname']; ?>" /></td></tr>
    
<?php
  $old_faculty = '';
  echo "<tr><td class=\"field\">" . $string['school'] . "</td><td><select name=\"schoolid\">\n<option value=\"\"></option>\n";
  $result = $mysqli->prepare("SELECT schools.id, school, faculty.name FROM schools, faculty WHERE schools.facultyID=faculty.id AND schools.deleted IS NULL ORDER BY faculty.name, school");
  $result->execute();
  $result->bind_result($id, $school, $faculty);
  while ($result->fetch()) {
    if ($old_faculty != $faculty) {
      if ($old_faculty != '') echo "</optgroup>\n";
      echo "<optgroup label=\"$faculty\">\n";
    }
    if (isset($_POST['schoolid']) and $_POST['schoolid'] == $id) {
      echo "<option value=\"$id\" selected>$school</option>\n";
    } else {
      echo "<option value=\"$id\">$school</option>\n";
    }
    $old_faculty = $faculty;
  }
  $result->close();
  echo "</optgroup>\n</select></td></tr>\n";
  
  echo '<tr><td class="field">' . $string['smsapi'] . '</td><td><select name="sms_api">';
  echo '<option value="">' . $string['nolookup'] . '</option>';
  foreach ($cfg_sms_sources as $key=>$value) {
    echo "<option value=\"$value\">$key</option>\n";
  }
  echo '</select></td></tr>';
?>
    <tr><td class="field"><?php echo $string['objapi']; ?></td><td><select name="vle_api">
    <option value=""><?php echo $string['nolookup']; ?></option>
    <option value="NLE"<?php if (isset($_POST['vle_api']) and $_POST['vle_api'] == 'NLE') echo ' selected'; ?>>Networked Learning Environment (NLE)</option>
    </select></td></tr>
    <tr><td class="field"><?php echo $string['summativechecklist']; ?></td><td><input type="checkbox" name="peer" checked /> <?php echo $string['peerreview']; ?>, <input type="checkbox" name="external" checked /> <?php echo $string['externalexaminers']; ?>, <input onclick="showHideGrid()" type="checkbox" id="stdset" name="stdset" /> <?php echo $string['standardssetting']; ?>, <input type="checkbox" name="mapping" /> <?php echo $string['mapping']; ?></td></tr>
    <tr><td class="field"><?php echo $string['active']; ?></td><td><input type="checkbox" name="active" checked /></td></tr>
    <tr><td class="field"><?php echo $string['allowselfenrol']; ?></td><td><input type="checkbox" name="selfenroll" /></td></tr>
    <tr><td class="field"><?php echo $string['negativemarking']; ?></td><td><input type="checkbox" name="neg_marking" checked /></td></tr>
    <tr id="ebelgrid" style="display:none"><td class="field"><?php echo $string['ebelgrid']; ?></td><td><select name="ebel_grid_template"><option value=""></option><?php
    $result = $mysqli->prepare("SELECT id, name FROM ebel_grid_templates ORDER BY name");
    $result->execute();
    $result->bind_result($id, $name);
    while ($result->fetch()) {
      echo "<option value=\"$id\">$name</option>\n";
    }
    $result->close();
    ?></select></td></tr>
    </table>
    <p><input type="submit" style="width:100px" name="submit" value="<?php echo $string['add']; ?>">&nbsp;&nbsp;<input style="width:100px" type="button" name="home" value="<?php echo $string['cancel']; ?>" onclick="javascript:history.back();" /></p>
  </form>
  </div>
</div>
<?php
}
?>
</body>
</html>