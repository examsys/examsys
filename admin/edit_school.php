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

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$schoolid = check_var('schoolid', 'GET', true, false, true);
$result = $mysqli->prepare('SELECT school, facultyID, code, externalid, externalsys FROM schools WHERE id = ? AND deleted IS NULL');
$result->bind_param('i', $schoolid);
$result->execute();
$result->store_result();
$result->bind_result($school, $curr_faculty, $curr_code, $curr_externalid, $curr_externalsys);
$result->fetch();
if ($result->num_rows == 0) {
    $result->close();
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
$result->close();

$faculties = 0;
$faculty_list = array();
$result = $mysqli->prepare('SELECT id, code, name FROM faculty WHERE deleted IS NULL ORDER BY name');
$result->execute();
$result->bind_result($facultyID, $code, $name);
while ($result->fetch()) {
    $faculty_list[] = array($facultyID, $code, $name);
    $faculties++;
}
$result->close();
?>
<!DOCTYPE html>
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['editschool']); ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/school.css" />

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/schoolforminit.min.js"></script>
  </head>
<body>
<?php
  require '../include/school_options.inc';
  require '../include/toprightmenu.inc';
    
    echo draw_toprightmenu();
?>
<div id="content">

<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="list_schools.php"><?php echo $string['schools'] ?></a></div>
  <div class="page_title"><?php echo $string['editschool'] ?></div>
</div>

  <br />
  <div align="center">
  <form id="theform" name="add_school" method="post" action="" autocomplete="off">
    <div class="form-error"><?php echo $string['duplicateerror'] ?></div>
    <table cellpadding="0" cellspacing="2" border="0">
    <tr><td class="field"><?php echo $string['name'] ?></td><td><input type="text" size="70" maxlength="255" id="school" name="school" value="<?php echo $school ?>" required /></td></tr>
    <tr><td class="field"><?php echo $string['faculty'] ?></td><td><select name="faculty">
    <?php
    foreach ($faculty_list as $faculty) {
        $sel = ($faculty[0] == $curr_faculty) ? ' selected="selected"' : '';
        echo "<option value=\"{$faculty[0]}\"{$sel}>{$faculty[1]} {$faculty[2]}</option>\n";
    }
    ?>
    </select></td></tr>
    <tr><td class="field"><?php echo $string['code'] ?></td><td><input type="text" size="30" maxlength="30" name="code" value="<?php echo $curr_code; ?>"/></td></tr>
    <tr><td class="field"><?php echo $string['externalid'] ?></td><td><?php echo $curr_externalid; ?></td></tr>
    <tr><td class="field"><?php echo $string['externalsys'] ?></td><td><?php echo $curr_externalsys; ?></td></tr>
    </table>
    <p><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>"><input class="cancel" id="cancel" type="button" name="home" value="<?php echo $string['cancel'] ?>" /></p>
  </form>
  </div>
</div>
<?php
// JS utils dataset.
$render = new render($configObject);
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['posturl'] = 'do_edit_school.php?schoolid=' . $schoolid;
$render->render($miscdataset, array(), 'dataset.html');
?>
</body>
</html>
