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
  $checklist = '';
  if (isset($_POST['peer'])) $checklist .= ',peer';
  if (isset($_POST['external'])) $checklist .= ',external';
  if (isset($_POST['stdset'])) $checklist .= ',stdset';
  if (isset($_POST['mapping'])) $checklist .= ',mapping';

  // Update the properties of the module.
  $tmp_moduleid = trim($_POST['moduleid']);
  $tmp_fullname = trim($_POST['fullname']);
  $tmp_checklist = substr($checklist,1); 
  
  $result = $mysqli->prepare("UPDATE modules SET moduleid=?, fullname=?, active=?, sms=?, vle_api=?, checklist=?, selfenroll=?, schoolid=?, neg_marking=?, ebel_grid_template=? WHERE moduleid=?");
  $result->bind_param('ssisssiiiis', $tmp_moduleid, $tmp_fullname, $active, $_POST['sms_api'], $_POST['vle_api'], $tmp_checklist, $selfenroll, $_POST['schoolid'], $neg_marking, $_POST['ebel_grid_template'], $_POST['old_moduleid']);
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
      $paper_moduleID = str_replace($_POST['old_moduleid'], trim($_POST['moduleid']), $paper_moduleID);
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
      $question_moduleID = str_replace($_POST['old_moduleid'], trim($_POST['moduleid']), $question_moduleID);
      $result = $mysqli->prepare("UPDATE questions SET q_group=? WHERE q_id=?");
      $result->bind_param('si', $question_moduleID, $q_id);
      $result->execute();
      $result->close();
    }
    $stmt->close();
  }

  $mysqli->close();
  header("location: list_modules.php");
} else {
  $moduleid = $_GET['moduleid'];
  $stmt = $mysqli->prepare("SELECT moduleid, fullname, active, school, vle_api, checklist, sms, selfenroll, neg_marking, ebel_grid_template FROM modules, schools WHERE modules.schoolid=schools.id AND moduleid=?");
  $stmt->bind_param('s', $moduleid);
  $stmt->execute();
  $stmt->bind_result($moduleid, $fullname, $active, $school, $vle_api, $checklist, $sms, $selfenroll, $neg_marking, $current_ebel_grid);
  $stmt->fetch();
  $stmt->close();
  
  require_once '../classes/smsutils.class.php';

  $SMS = SMSutils::GetSmsUtils();
  $cfg_sms_sources = array();
  if (is_object($SMS)) {
    $cfg_sms_sources =  $SMS->getModuleSources();
  }
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $string['editmodule'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    input, textarea {font-family:Arial,sans-serif; color:black}
    .field {font-weight:bold; text-align:right; padding-right:10px}
  </style>

  <script language="JavaScript">
    function checkForm() {
      if (myform.moduleid.value == "") {
        alert ("<?php echo $string['entermoduleidentifier']; ?>");
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
  
  <?php
  if ($unique_moduleid == false) {
  ?>
  function moduleWarning() {
    alert("<?php echo sprintf($string['moduleidinuse'], $tmp_moduleid); ?>");
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
  <table class="header">
  <tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['editmodule']; ?></div></th></tr>
  <tr><th class="bevel"></th></tr>
  </table>
  <br />
  <div align="center">
  <form name="myform" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>?moduleid=<?php echo $_GET['moduleid']; ?>">
    <table cellpadding="0" cellspacing="2" border="0" style="text-align:left">
    <?php
    if ($unique_moduleid == false) {
      echo "<tr><td class=\"field\">" . $string['moduleid'] . "</td><td><input type=\"text\" size=\"10\" name=\"moduleid\" style=\"background-color:#FFD9D9; color:#800000; border:1px solid #800000\" value=\"$tmp_moduleid\" /><input type=\"hidden\" name=\"old_moduleid\" value=\"$tmp_moduleid\" /></td></tr>\n";
    } else {
    ?>
      <tr><td class="field"><?php echo $string['moduleid']; ?></td><td><input type="text" size="10" name="moduleid" value="<?php if (isset($_GET['moduleid'])) echo $_GET['moduleid']; ?>" /><input type="hidden" name="old_moduleid" value="<?php echo $moduleid; ?>" /></td></tr>
    <?php
    }
    ?>
    <tr><td class="field"><?php echo $string['name']; ?></td><td><input type="text" size="70" name="fullname" value="<?php echo $fullname; ?>" /></td></tr>
  <?php
    $old_faculty = '';
    echo "<tr><td class=\"field\">" . $string['school'] . "</td><td><select name=\"schoolid\">\n<option value=\"\"></option>\n";
    $result = $mysqli->prepare("SELECT schools.id, school, faculty.name FROM schools, faculty WHERE schools.facultyID=faculty.id ORDER BY faculty.name, school");
    $result->execute();
    $result->bind_result($id, $list_school, $faculty);
    while ($result->fetch()) {
      if ($old_faculty != $faculty) {
        if ($old_faculty != '') echo "</optgroup>\n";
        echo "<optgroup label=\"$faculty\">\n";
      }
      if ($school == $list_school) {
        echo "<option value=\"$id\" selected>$list_school</option>\n";
      } else {
        echo "<option value=\"$id\">$list_school</option>\n";
      }
      $old_faculty = $faculty;
    }
    $result->close();
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
    
    echo '<tr><td class="field">' . $string['smsapi'] . '</td><td><select name="sms_api">';
    foreach ($cfg_sms_sources as $key=>$value) {
      if ($sms == $value) {
        echo "<option value=\"$value\" selected>$key</option>\n";
      } else {
        echo "<option value=\"$value\">$key</option>\n";
      }
    }
    echo '</select></td></tr>';
  ?>
    <tr><td class="field"><?php echo $string['objapi']; ?></td><td><select name="vle_api"><option value=""><?php echo $string['nolookup']; ?></option><option value="NLE"<?php if ($vle_api == 'NLE') echo ' selected'; ?>><?php echo $string['nle']; ?></option></select></td></tr>
    <tr><td class="field"><?php echo $string['summativechecklist']; ?></td><td><input type="checkbox" name="peer"<?php if ($peer == 1) echo ' checked'; ?> /> <?php echo $string['peerreview']; ?>, <input type="checkbox" name="external"<?php if ($external == 1) echo ' checked'; ?> /> <?php echo $string['externalexaminers']; ?>, <input onclick="showHideGrid()" type="checkbox" id="stdset" name="stdset"<?php if ($stdset == 1) echo ' checked'; ?> /> <?php echo $string['standardssetting']; ?>, <input type="checkbox" name="mapping"<?php if ($mapping == 1) echo ' checked'; ?> /> <?php echo $string['mapping']; ?></td></tr>
    <tr><td class="field"><?php echo $string['active']; ?></td><td><input type="checkbox" name="active"<?php if ($active == 1) echo ' checked'; ?> /></td></tr>
    <tr><td class="field"><?php echo $string['allowselfenrol']; ?></td><td><input type="checkbox" name="selfenroll"<?php if ($selfenroll == 1) echo ' checked'; ?> /></td></tr>
    <tr><td class="field"><?php echo $string['negativemarking']; ?></td><td><input type="checkbox" name="neg_marking"<?php if ($neg_marking == 1) echo ' checked'; ?> /></td></tr>
    <tr id="ebelgrid" style="display:<?php
    if ($stdset == 1) {
      echo 'block';
    } else {
      echo 'none';
    }
    ?>"><td class="field"><?php echo $string['ebelgrid']; ?></td><td><select name="ebel_grid_template"><option value=""></option><?php
    $result = $mysqli->prepare("SELECT id, name FROM ebel_grid_templates ORDER BY name");
    $result->execute();
    $result->bind_result($id, $name);
    while ($result->fetch()) {
      if ($id == $current_ebel_grid) {
        echo "<option value=\"$id\" selected>$name</option>\n";
      } else {
        echo "<option value=\"$id\">$name</option>\n";
      }
    }
    $result->close();
    ?></select></td></tr>
  <?php
    echo "</table>\n";
    echo "<input type=\"hidden\" name=\"old_moduleid\" value=\"" . $_GET['moduleid'] . "\" />\n";
  ?>
    <p><input type="submit" style="width:100px" name="submit" value="<?php echo $string['save']; ?>">&nbsp;&nbsp;<input style="width:100px" type="button" name="home" value="<?php echo $string['cancel']; ?>" onclick="javascript:history.back();" /></p>
  </form>
  </div>
</div>
<?php
}
?>
</body>
</html>