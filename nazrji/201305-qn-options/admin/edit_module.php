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

require '../include/sysadmin_auth.inc';
require_once '../include/errors.inc';
require_once '../classes/moduleutils.class.php';
require_once '../classes/logger.class.php';

check_var('moduleid', 'GET', true, false, false);

if (!module_utils::get_moduleid_from_id($_GET['moduleid'], $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$stmt = $mysqli->prepare("SELECT moduleid, fullname, active, schools.id, school, vle_api, checklist, sms, selfenroll, neg_marking, ebel_grid_template, timed_exams, exam_q_feedback, add_team_members FROM modules, schools WHERE modules.schoolid = schools.id AND modules.id = ?");
$stmt->bind_param('i', $_GET['moduleid']);
$stmt->execute();
$stmt->bind_result($modulecode, $fullname, $active, $schoolid, $school, $vle_api, $checklist, $sms, $selfenroll, $neg_marking, $current_ebel_grid, $timed_exams, $exam_q_feedback, $add_team_members);
$stmt->fetch();
$stmt->close();
  
$unique_moduleid = true;
if (isset($_POST['submit']) and $_POST['modulecode'] != $_POST['old_modulecode']) {
  // Check for unique moduleid
  $new_modulecode = trim($_POST['modulecode']);
  $result = $mysqli->prepare("SELECT moduleid FROM modules WHERE moduleid = ?");
  $result->bind_param('s', $new_modulecode);
  $result->execute();
  $result->store_result();
  $result->bind_result($new_modulecode);
  $result->fetch();
  if ($result->num_rows > 0) $unique_moduleid = false;
  $result->free_result();
  $result->close();
}

if (isset($_POST['submit']) and $unique_moduleid == true) {
  if (isset($_POST['active'])) {
    $new_active = 1;
  } else {
    $new_active = 0;
  }
  if (isset($_POST['selfenroll'])) {
    $new_selfenroll = 1;
  } else {
    $new_selfenroll = 0;
  }
  if (isset($_POST['neg_marking'])) {
    $new_neg_marking = 1;
  } else {
    $new_neg_marking = 0;
  }
  $new_checklist = '';
  if (isset($_POST['peer']))     $new_checklist .= ',peer';
  if (isset($_POST['external'])) $new_checklist .= ',external';
  if (isset($_POST['stdset']))   $new_checklist .= ',stdset';
  if (isset($_POST['mapping']))  $new_checklist .= ',mapping';

  // Update the properties of the module.
  $new_modulecode = trim($_POST['modulecode']);
  $new_fullname = trim($_POST['fullname']);
  $new_checklist = substr($new_checklist, 1);

  if (isset($_POST['timed_exams'])) {
    $new_timed_exams = 1;
  } else {
    $new_timed_exams = 0;
  }
  if (isset($_POST['exam_q_feedback'])) {
    $new_exam_q_feedback = 1;
  } else {
    $new_exam_q_feedback = 0;
  }
  if (isset($_POST['add_team_members'])) {
    $new_add_team_members = 1;
  } else {
    $new_add_team_members = 0;
  }

  if ($new_modulecode != '' and $new_fullname != '' and $_POST['schoolid'] != '') {
    $result = $mysqli->prepare("UPDATE modules SET moduleid = ?, fullname = ?, active = ?, sms = ?, vle_api = ?, checklist = ?, selfenroll = ?, schoolid = ?, neg_marking = ?, ebel_grid_template = ?, timed_exams = ?, exam_q_feedback = ?, add_team_members = ? WHERE id = ?");
    $result->bind_param('ssisssiiiiiiii', $new_modulecode, $new_fullname, $new_active, $_POST['sms_api'], $_POST['vle_api'], $new_checklist, $new_selfenroll, $_POST['schoolid'], $new_neg_marking, $_POST['ebel_grid_template'], $new_timed_exams, $new_exam_q_feedback, $new_add_team_members, $_GET['moduleid']);
    $result->execute();
    $result->close();
  }
  
  // Log any changes
  $logger = new Logger($mysqli);
  if ($modulecode != $new_modulecode)                     $logger->track_change('Module', $_GET['moduleid'], $userObject->get_user_ID(), $modulecode, $new_modulecode, $string['moduleid']);
  if ($fullname != $new_fullname)                         $logger->track_change('Module', $_GET['moduleid'], $userObject->get_user_ID(), $fullname, $new_fullname, $string['name']);
  if ($schoolid != $_POST['schoolid'])                    $logger->track_change('Module', $_GET['moduleid'], $userObject->get_user_ID(), $schoolid, $_POST['schoolid'], $string['school']);
  if ($sms != $_POST['sms_api'])                          $logger->track_change('Module', $_GET['moduleid'], $userObject->get_user_ID(), $sms, $_POST['sms_api'], $string['smsapi']);
  if ($vle_api != $_POST['vle_api'])                      $logger->track_change('Module', $_GET['moduleid'], $userObject->get_user_ID(), $vle_api, $_POST['vle_api'], $string['objapi']);
  if ($checklist != $new_checklist)                       $logger->track_change('Module', $_GET['moduleid'], $userObject->get_user_ID(), $checklist, $new_checklist, $string['summativechecklist']);
  if ($active != $new_active)                             $logger->track_change('Module', $_GET['moduleid'], $userObject->get_user_ID(), $active, $new_active, $string['active']);
  if ($selfenroll != $new_selfenroll)                     $logger->track_change('Module', $_GET['moduleid'], $userObject->get_user_ID(), $selfenroll, $new_selfenroll, $string['allowselfenrol']);
  if ($neg_marking != $new_neg_marking)                   $logger->track_change('Module', $_GET['moduleid'], $userObject->get_user_ID(), $neg_marking, $new_neg_marking, $string['negativemarking']);
  if ($timed_exams != $new_timed_exams)                   $logger->track_change('Module', $_GET['moduleid'], $userObject->get_user_ID(), $timed_exams, $new_timed_exams, $string['timedexams']);
  if ($exam_q_feedback != $new_exam_q_feedback)           $logger->track_change('Module', $_GET['moduleid'], $userObject->get_user_ID(), $exam_q_feedback, $new_exam_q_feedback, $string['questionbasedfeedback']);
  if ($add_team_members != $new_add_team_members)         $logger->track_change('Module', $_GET['moduleid'], $userObject->get_user_ID(), $add_team_members, $new_add_team_members, $string['addteammembers']);
  if ($current_ebel_grid != $_POST['ebel_grid_template']) $logger->track_change('Module', $_GET['moduleid'], $userObject->get_user_ID(), $current_ebel_grid, $_POST['ebel_grid_template'], $string['ebelgrid']);

  $mysqli->close();
  header("location: list_modules.php");
  exit;
} else {
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
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['editmodule'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

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
        document.getElementById('ebelgrid').style.display = 'table-row';
      } else {
        document.getElementById('ebelgrid').style.display = 'none';
      }
    }

    function setSidebarMenu() {
      $('#menu1a').css('display','none');
      $('#menu1b').css('display','block');
      $('#lineID').val('<?php echo $_GET['moduleid']; ?>');
    }

    $(document).ready(setSidebarMenu);

  <?php
  if ($unique_moduleid == false) {
  ?>
  function moduleWarning() {
    alert("<?php echo sprintf($string['moduleidinuse'], $new_modulecode); ?>");
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
  <div id="content" class="content">
  <table class="header">
  <tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['editmodule']; ?></div></th></tr>
  <tr><th class="bevel"></th></tr>
  </table>
  <br />
  <div align="center">
  <form id="module_form" name="module_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?moduleid=<?php echo $_GET['moduleid']; ?>">
    <table cellpadding="0" cellspacing="2" border="0" style="text-align:left">
    <tr><td class="field"><?php echo $string['moduleid'] ?></td><td><input type="text" size="10" id="modulecode" name="modulecode" value="<?php echo $modulecode ?>" class="required" /></td></tr>
    <tr><td class="field"><?php echo $string['name'] ?></td><td><input type="text" size="70" id="fullname" name="fullname" value="<?php echo $fullname ?>" class="required" /></td></tr>
  <?php
    $old_faculty = '';
    echo "<tr><td class=\"field\">" . $string['school'] . "</td><td><select id=\"schoolid\" name=\"schoolid\" class=\"required\">\n<option value=\"\"></option>\n";
    $result = $mysqli->prepare("SELECT schools.id, school, faculty.name FROM schools, faculty WHERE schools.facultyID=faculty.id AND schools.deleted IS NULL ORDER BY faculty.name, school");
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

    if (strpos($checklist, 'peer') !== false) {
      $peer = 1;
    } else {
      $peer = 0;
    }
    if (strpos($checklist, 'external') !== false) {
      $external = 1;
    } else {
      $external = 0;
    }
    if (strpos($checklist, 'stdset') !== false) {
      $stdset = 1;
    } else {
      $stdset = 0;
    }
    if (strpos($checklist, 'mapping') !== false) {
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
    <tr><td class="field"><?php echo $string['objapi']; ?></td><td><select name="vle_api"><option value=""><?php echo $string['nolookup']; ?></option><option value="UoNCM"<?php if ($vle_api == 'UoNCM') echo ' selected'; ?>><?php echo $string['uoncm']; ?></option><option value="NLE"<?php if ($vle_api == 'NLE') echo ' selected'; ?>><?php echo $string['nle']; ?></option></select></td></tr>
    <tr><td class="field"><?php echo $string['summativechecklist']; ?></td><td><input type="checkbox" name="peer"<?php if ($peer == 1) echo ' checked="checked"'; ?> /> <?php echo $string['peerreview']; ?>, <input type="checkbox" name="external"<?php if ($external == 1) echo ' checked'; ?> /> <?php echo $string['externalexaminers']; ?>, <input onclick="showHideGrid()" type="checkbox" id="stdset" name="stdset"<?php if ($stdset == 1) echo ' checked'; ?> /> <?php echo $string['standardssetting']; ?>, <input type="checkbox" name="mapping"<?php if ($mapping == 1) echo ' checked'; ?> /> <?php echo $string['mapping']; ?></td></tr>
    <tr><td class="field"><?php echo $string['active']; ?></td><td><input type="checkbox" name="active"<?php if ($active == 1) echo ' checked="checked"'; ?> /></td></tr>
    <tr><td class="field"><?php echo $string['allowselfenrol']; ?></td><td><input type="checkbox" name="selfenroll"<?php if ($selfenroll == 1) echo ' checked="checked"'; ?> /></td></tr>
    <tr><td class="field"><?php echo $string['negativemarking']; ?></td><td><input type="checkbox" name="neg_marking"<?php if ($neg_marking == 1) echo ' checked="checked"'; ?> /></td></tr>
    <tr><td class="field"><?php echo $string['timedexams']; ?></td><td><input type="checkbox" name="timed_exams"<?php if ($timed_exams == 1) echo ' checked="checked"'; ?> /></td></tr>
    <tr><td class="field"><?php echo $string['questionbasedfeedback']; ?></td><td><input type="checkbox" name="exam_q_feedback"<?php if ($exam_q_feedback == 1) echo ' checked="checked"'; ?> /></td></tr>
    <tr><td class="field"><?php echo $string['addteammembers']; ?></td><td><input type="checkbox" name="add_team_members"<?php if ($add_team_members == 1) echo ' checked="checked"'; ?> /></td></tr>
    <tr id="ebelgrid" style="display:<?php
    if ($stdset == 1) {
      echo 'table-row';
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
    echo "<input type=\"hidden\" name=\"old_modulecode\" value=\"" . $modulecode . "\" />\n";
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