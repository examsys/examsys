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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$SMS = SMSutils::GetSmsUtils();
$cfg_sms_sources = array();
if (is_object($SMS)) {
  $cfg_sms_sources =  $SMS->getModuleSources();
}
$cfg_sms_sources = array($string['nolookup'] => '') + $cfg_sms_sources;

$unique_moduleid = true;
$tmp_modulecode = '';
$vle_api = '';
$map_level = 0;

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
  $fullname = $schoolid = $sms_api = '';
  $peer = $stdset = $mapping = false;

  if (isset($_POST['fullname']))  $fullname = trim($_POST['fullname']);
  if (isset($_POST['peer']))      $peer = true;
  if (isset($_POST['external']))  $external = true;
  if (isset($_POST['stdset']))    $stdset = true;
  if (isset($_POST['mapping']))   $mapping = true;
  if (isset($_POST['schoolid']))  $schoolid = $_POST['schoolid'];
  $externalid = check_var('externalid', 'POST', false, false, true);
  if (isset($_POST['vle_api'])) {
    $vle_data = $_POST['vle_api'];
    if ($vle_data == '') {
      $map_level = 0;
      $vle_api = '';
    } else {
      $vle_parts = explode('~', $vle_data);
      $vle_api = $vle_parts[0];
      $map_level = $vle_parts[1];
    }
  }
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

  $academic_year_start = trim($_POST['academic_year_start']);

  $ebel_grid_template = $_POST['ebel_grid_template'];

  $modID = module_utils::add_modules($modulecode, $fullname, $active, $schoolid, $vle_api, $sms_api, $selfenroll, $peer, $external, $stdset, $mapping, $neg_marking, $ebel_grid_template, $mysqli, $sms_import, $timed_exams, $exam_q_feedback, $add_team_members, $map_level, $academic_year_start, $externalid);

  // New sytle SMS enrolments.
  $yearutils = new yearutils($mysqli);
  $session = $yearutils->get_current_session();
  $userObj = userObject::get_instance();
  $smsplugin_name = plugin_manager::get_plugin_type_enabled('plugin_sms');
  foreach($smsplugin_name as $name) {
    $smspluginns = 'plugins\SMS\\' . $name. '\\' . $name;
    $smsplugin = new $smspluginns($mysqli, $userObj->get_user_ID());
    if (!is_null($externalid) and $smsplugin->supports_module_import() !== false and $smsplugin->supports_enrol_import() !== false) {
        $smsplugin->update_module_enrolments($externalid, $session);
    }
  }
  
  
  header("location: list_modules.php");
  exit();
} else {
?>
<!DOCTYPE html>
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: Create new Module<?php echo " " . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .field {text-align:right; padding-right:10px}
    .form-error {
      width: 468px;
      margin: 18px auto;
      padding: 16px;
      background-color: #FFD9D9;
      color: #800000;
      border: 2px solid #800000
  </style>

  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script type="text/javascript" src="../js/jquery-ui-1.10.4.min.js"></script>
  <script type="text/javascript" src="../js/system_tooltips.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
<?php
  $vle_apis = $configObject->get('vle_apis');
  if (count($vle_apis) > 0) {
    $mu = module_utils::get_instance();
    $vle_apis = $mu->get_vle_api_data($vle_apis);
    $map_levels = array(iCMAPI::LEVEL_SESSION => $string['session'], iCMAPI::LEVEL_MODULE => $string['module']);
  }
?>

    $(function () {
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');

<?php
  if ($unique_moduleid == false) {
?>
      $('#modulecode').addClass('errfield');
<?php
  }
?>
      $('#stdset').click(function() {
        if ($('#stdset').prop('checked')) {
          $('#ebelgrid').show();
        } else {
          $('#ebelgrid').hide();
        }
      });
      $('#cancel').click(function() {
        history.back();
      });    
    });
  </script>
  </head>

  <body>
  <?php
    require '../include/admin_module_options.inc';
		require '../include/toprightmenu.inc';

		echo draw_toprightmenu(233);
  ?>
  <div id="content">
  <div class="head_title">
		<div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
		<div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" /><a href="list_modules.php"><?php echo $string['modules'] ?></a></div>
		<div class="page_title"><?php echo $string['createmodule']; ?></div>
  </div>
	
  <br />

  <form id="theform" name="module_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" autocomplete="off">
<?php
  if ($unique_moduleid == false) {
?>
    <div class="form-error"><?php echo $string['duplicateerror'] ?></div>
<?php
  }
?>
    <table cellpadding="0" cellspacing="1" border="0" style="text-align:left; margin-left:auto; margin-right:auto">
    <tr><td class="field"><?php echo $string['moduleid'] ?></td><td><input type="text" size="10" maxlength="25" id="modulecode" name="modulecode" value="<?php echo $tmp_modulecode ?>" required autofocus /></td></tr>
    <tr><td class="field"><?php echo $string['name'] ?></td><td><input type="text" size="70" id="fullname" name="fullname" value="<?php if (isset($_POST['fullname'])) echo $_POST['fullname'] ?>" required /></td></tr>

<?php
  $old_faculty = '';
  $old_facultycode = '';
  $old_facultyid = 0;
  echo "<tr><td class=\"field\">" . $string['school'] . "</td><td><select id=\"schoolid\" name=\"schoolid\" required>\n<option value=\"\"></option>\n";
  $result = $mysqli->prepare("SELECT schools.id, schools.code, school, faculty.code, faculty.name, faculty.id FROM schools, faculty WHERE schools.facultyID = faculty.id AND schools.deleted IS NULL ORDER BY faculty.code, faculty.name, school");
  $result->execute();
  $result->bind_result($id, $code, $school, $facultycode, $faculty, $facultyid);
  while ($result->fetch()) {
    if ($facultyid != $old_facultyid) {
      if ($old_facultycode . ' ' . $old_faculty != '') echo "</optgroup>\n";
      echo "<optgroup label=\"$facultycode $faculty\">\n";
    }
    if (isset($_POST['schoolid']) and $_POST['schoolid'] == $id) {
      echo "<option value=\"$id\" selected>$code $school</option>\n";
    } else {
      echo "<option value=\"$id\">$code $school</option>\n";
    }
    $old_facultyid = $facultyid;
    $old_faculty = $faculty;
    $old_facultycode = $facultycode;
  }
  $result->close();
  echo "</optgroup>\n</select></td></tr>\n";

  echo '<tr><td class="field">' . $string['smsapi'] . '</td><td><select name="sms_api">';
  foreach ($cfg_sms_sources as $key=>$value) {
    echo "<option value=\"$value\">$key</option>\n";
  }
  // SMS might be a plugin.
  $plugins = array();
  $userObj = userObject::get_instance();
  $smsplugin_name = plugin_manager::get_plugin_type_enabled('plugin_sms');
  foreach($smsplugin_name as $name) {
    $smspluginns = 'plugins\SMS\\' . $name. '\\' . $name;
    $smsplugin = new $smspluginns($mysqli, $userObj->get_user_ID());
    $value = $smsplugin->get_name();
    echo "<option value=\"$value\">$value</option>\n";
  }
  echo '</select></td></tr>';
?>
    <tr><td class="field"><?php echo $string['academicyearstart'] ?></td><td><input type="text"  name="academic_year_start" value="<?php echo $configObject->get('cfg_academic_year_start') ?>" style="width:50px" required /> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="<?php echo $string['tooltip_format'] ?>" /></td></tr>
    <tr><td class="field"><?php echo $string['objapi']; ?></td><td><select id="vle_api" name="vle_api"><option value=""><?php echo $string['nolookup']; ?></option>
<?php
  foreach ($vle_apis as $vle_name => $vle_api_data) {
    foreach ($vle_api_data['levels'] as $api_level) {
      $selected = ($vle_api == $vle_name and $map_level == $api_level) ? ' selected="selected"' : '';

    ?>
      <option value="<?php echo $vle_name . '~' . $api_level; ?>"<?php echo $selected ?>><?php echo $vle_api_data['name'] . ' (' . $vle_name . ') - ' . $map_levels[$api_level] . ' ' . $string['level'] ?></option>
    <?php
    }
  }
?>
    </select>
    <div id="map_level_holder"></div>
    </td></tr>
    <tr><td class="field"><?php echo $string['summativechecklist'] ?></td><td><input type="checkbox" name="peer" checked="checked" /><?php echo $string['peerreview'] ?>, <input type="checkbox" name="external" checked /><?php echo $string['externalexaminers'] ?>, <input type="checkbox" id="stdset" name="stdset" /><?php echo $string['standardssetting'] ?>, <input type="checkbox" name="mapping" /><?php echo $string['mapping'] ?></td></tr>
    <tr><td class="field"><?php echo $string['active'] ?></td><td><input type="checkbox" name="active" checked /></td></tr>
    <tr><td class="field"><?php echo $string['allowselfenrol'] ?></td><td><input type="checkbox" name="selfenroll" /></td></tr>
    <tr><td class="field"><?php echo $string['negativemarking'] ?></td><td><input type="checkbox" name="neg_marking" checked="checked" /></td></tr>
    <tr><td class="field"><?php echo $string['timedexams'] ?></td><td><input type="checkbox" name="timed_exams" /></td></tr>
    <tr><td class="field"><?php echo $string['questionbasedfeedback'] ?></td><td><input type="checkbox" name="exam_q_feedback" checked="checked" /></td></tr>
    <tr><td class="field"><?php echo $string['addteammembers'] ?></td><td><input type="checkbox" name="add_team_members" checked="checked" /></td></tr>
    <tr id="ebelgrid" style="display:none"><td class="field"><?php echo $string['ebelgrid'] ?></td><td><select name="ebel_grid_template"><option value=""></option><?php
    $result = $mysqli->prepare("SELECT id, name FROM ebel_grid_templates ORDER BY name");
    $result->execute();
    $result->bind_result($id, $name);
    while ($result->fetch()) {
      echo "<option value=\"$id\">$name</option>\n";
    }
    $result->close();
    ?></select></td></tr>
    <?php
      $sms = \plugins\plugins_sms::get_sms($mysqli);
      if ($sms !== false) {
    ?>
    <tr><td class="field"><?php echo $string['externalid'] ?></td><td><input type="text" size="30" maxlength="255" name="externalid" value=""></td></tr>
    <?php
      }
    ?>
    <tr><td colspan="2" style="text-align:center; padding-top:12px"><input type="submit" class="ok" name="submit" value="<?php echo $string['add'] ?>"><input class="cancel" id="cancel" type="button" name="home" value="<?php echo $string['cancel'] ?>" /></td></tr>
		</table>
	</form>

	</div>
<?php
}
?>
</body>
</html>
