<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

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

$vle_api = '';
$map_level = 0;

?>
<!DOCTYPE html>
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['createmodule']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/module.css" />
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/moduleinit.min.js"></script>

<?php
  $vle_apis = $configObject->get('vle_apis');
if (count($vle_apis) > 0) {
    $mu = module_utils::get_instance();
    $vle_apis = $mu->get_vle_api_data($vle_apis);
    $map_levels = array(iCMAPI::LEVEL_SESSION => $string['session'], iCMAPI::LEVEL_MODULE => $string['module']);
}
?>

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

  <form id="theform" name="module_form" method="post" action="" autocomplete="off">

    <div class="form-error"><?php echo $string['duplicateerror'] ?></div>

    <table cellpadding="0" cellspacing="1" border="0" style="text-align:left; margin-left:auto; margin-right:auto">
    <tr><td class="field"><?php echo $string['moduleid'] ?></td><td><input type="text" size="10" maxlength="25" id="modulecode" name="modulecode" value="" required autofocus /></td></tr>
    <tr><td class="field"><?php echo $string['name'] ?></td><td><input type="text" size="70" id="fullname" name="fullname" value="<?php if (isset($_POST['fullname'])) {
        echo $_POST['fullname'];
                          } ?>" required /></td></tr>

<?php
  $old_faculty = '';
  $old_facultycode = '';
  $old_facultyid = 0;
  echo '<tr><td class="field">' . $string['school'] . "</td><td><select id=\"schoolid\" name=\"schoolid\" required>\n<option value=\"\"></option>\n";
  $result = $mysqli->prepare('SELECT schools.id, schools.code, school, faculty.code, faculty.name, faculty.id FROM schools, faculty WHERE schools.facultyID = faculty.id AND schools.deleted IS NULL ORDER BY faculty.code, faculty.name, school');
  $result->execute();
  $result->bind_result($id, $code, $school, $facultycode, $faculty, $facultyid);
while ($result->fetch()) {
    if ($facultyid != $old_facultyid) {
        if ($old_facultycode . ' ' . $old_faculty != '') {
            echo "</optgroup>\n";
        }
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
foreach ($cfg_sms_sources as $key => $value) {
    echo "<option value=\"$value\">$key</option>\n";
}
  // SMS might be IMS enterprise, ExamSys web service or a plugin.
  $externalsys = new \external_systems();
  $extsys = $externalsys->get_all_externalsystems();
foreach ($extsys as $key => $value) {
    echo "<option value=\"$value\">$value</option>\n";
}
  echo '</select></td></tr>';
?>
    <tr><td class="field"><?php echo $string['academicyearstart'] ?></td><td><input type="text"  name="academic_year_start" value="<?php echo $configObject->get_setting('core', 'system_academic_year_start'); ?>" style="width:50px" required /> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="<?php echo $string['tooltip_format'] ?>" /></td></tr>
    <tr><td class="field"><?php echo $string['objapi']; ?></td><td><select id="vle_api" name="vle_api"><option value=""><?php echo $string['nolookup']; ?></option>
<?php
foreach ($vle_apis as $vle_name => $vle_api_data) {
    foreach ($vle_api_data['levels'] as $api_level) {
        ?>
      <option value="<?php echo $vle_name . '~' . $api_level; ?>"><?php echo $vle_api_data['name'] . ' (' . $vle_name . ') - ' . $map_levels[$api_level] . ' ' . $string['level'] ?></option>
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
    <tr><td class="field"><?php echo $string['syncpreviousyear'] ?></td><td><input type="checkbox" name="syncpreviousyear" /><img src="../artwork/tooltip_icon.gif" class="help_tip" title="<?php echo $string['tooltip_syncprev'] ?>" /></td></tr>
    <tr id="ebelgrid" style="display:none"><td class="field"><?php echo $string['ebelgrid'] ?></td><td><select name="ebel_grid_template"><option value=""></option><?php
    $result = $mysqli->prepare('SELECT id, name FROM ebel_grid_templates ORDER BY name');
    $result->execute();
    $result->bind_result($id, $name);
    while ($result->fetch()) {
        echo "<option value=\"$id\">$name</option>\n";
    }
    $result->close();
    ?></select></td></tr>
    <tr><td class="field"><?php echo $string['externalid'] ?></td><td><input type="text" size="30" maxlength="255" name="externalid" value=""></td></tr>
    <tr><td colspan="2" style="text-align:center; padding-top:12px"><input type="submit" class="ok" name="submit" value="<?php echo $string['add'] ?>"><input class="cancel" id="cancel" type="button" name="home" value="<?php echo $string['cancel'] ?>" /></td></tr>
        </table>
    </form>

    </div>
<?php
// JS utils dataset.
$render = new render($configObject);
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['posturl'] = 'do_add_module.php';
$render->render($miscdataset, array(), 'dataset.html');
?>
</body>
</html>
