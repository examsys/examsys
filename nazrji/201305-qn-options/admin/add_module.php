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
* @copyright Copyright (c) 2013 The University of Nottingham
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
$tmp_modulecode = '';

if (isset($_POST['submit'])) {
  // Check for unique moduleID
  $modulecode = trim($_POST['modulecode']);
  
  if (module_utils::module_exists($modulecode, $mysqli)) {
    $unique_moduleid = false;
  }
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
  
  $sms_import = 1;
  
  if (isset($_POST['timed_exams'])) {
    $timed_exams = 1;
  } else {
    $timed_exams = 0;
  }
  if (isset($_POST['exam_q_feedback'])) {
    $exam_q_feedback = 1;
  } else {
    $exam_q_feedback = 0;
  }
  if (isset($_POST['add_team_members'])) {
    $add_team_members = 1;
  } else {
    $add_team_members = 0;
  }

  $ebel_grid_template = $_POST['ebel_grid_template'];

  $modID = module_utils::add_modules($modulecode, $fullname, $active, $schoolid, $vle_api, $sms_api, $selfenroll, $peer, $external, $stdset, $mapping, $neg_marking, $ebel_grid_template, $mysqli, $sms_import, $timed_exams, $exam_q_feedback, $add_team_members);

  $mysqli->close();
  header("location: list_modules.php");
  exit;
} else {
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Create new Module<?php echo " " . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .field {font-weight:bold; text-align:right; padding-right:10px}
    .error {color:#800000}
    input.error, select.error {background-color:#FFD9D9; border:1px solid #800000}
  </style>

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script language="JavaScript">
    $(function () {
      $('#module_form').validate({
        messages: {
          modulecode: '<div><?php echo $string['entermoduleid']; ?></div>',
          fullname: '<div><?php echo $string['entermoduletitle']; ?></div>',
          schoolid: '<div><?php echo $string['selectschool']; ?></div>'
        }
      });
<?php
  if ($unique_moduleid == false) {
?>
      $('#modulecode').addClass('error');
<?php
  }
?>
    });

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
  <div id="content" class="content">
  <table class="header">
  <tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['createmodule']; ?></div></th><th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></th></tr>
  <tr><th colspan="2" class="bevel"></th></tr>
  </table>
  <br />
  <div align="center">
  <form id="module_form" name="module_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <table cellpadding="0" cellspacing="2" border="0" style="text-align:left">
    <tr><td class="field"><?php echo $string['moduleid'] ?></td><td><input type="text" size="10" id="modulecode" name="modulecode" value="<?php echo $tmp_modulecode ?>" class="required" /></td></tr>
    <tr><td class="field"><?php echo $string['name'] ?></td><td><input type="text" size="70" id="fullname" name="fullname" value="<?php if (isset($_POST['fullname'])) echo $_POST['fullname']; ?>" class="required" /></td></tr>

<?php
  $old_faculty = '';
  echo "<tr><td class=\"field\">" . $string['school'] . "</td><td><select id=\"schoolid\" name=\"schoolid\" class=\"required\">\n<option value=\"\"></option>\n";
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
    <option value="UoNCM"<?php if (isset($_POST['vle_api']) and $_POST['vle_api'] == 'UoNCM') echo ' selected'; ?>>Curriculum Map (UoNCM)</option>
    <option value="NLE"<?php if (isset($_POST['vle_api']) and $_POST['vle_api'] == 'NLE') echo ' selected'; ?>>Networked Learning Environment (NLE)</option>
    </select></td></tr>
    <tr><td class="field"><?php echo $string['summativechecklist']; ?></td><td><input type="checkbox" name="peer" checked="checked" /> <?php echo $string['peerreview']; ?>, <input type="checkbox" name="external" checked /> <?php echo $string['externalexaminers']; ?>, <input onclick="showHideGrid()" type="checkbox" id="stdset" name="stdset" /> <?php echo $string['standardssetting']; ?>, <input type="checkbox" name="mapping" /> <?php echo $string['mapping']; ?></td></tr>
    <tr><td class="field"><?php echo $string['active']; ?></td><td><input type="checkbox" name="active" checked /></td></tr>
    <tr><td class="field"><?php echo $string['allowselfenrol']; ?></td><td><input type="checkbox" name="selfenroll" /></td></tr>
    <tr><td class="field"><?php echo $string['negativemarking']; ?></td><td><input type="checkbox" name="neg_marking" checked="checked" /></td></tr>
    <tr><td class="field"><?php echo $string['timedexams']; ?></td><td><input type="checkbox" name="timed_exams" /></td></tr>
    <tr><td class="field"><?php echo $string['questionbasedfeedback']; ?></td><td><input type="checkbox" name="exam_q_feedback" checked="checked" /></td></tr>
    <tr><td class="field"><?php echo $string['addteammembers']; ?></td><td><input type="checkbox" name="add_team_members" checked="checked" /></td></tr>
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