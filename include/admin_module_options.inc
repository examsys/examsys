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

// Calculate what the current academic session is.
$yearutils = new yearutils($mysqli);
$current_session = $yearutils->get_current_session();
$next_session = $yearutils->get_next_session();
$academic_year = $yearutils->get_academic_session($current_session);
$next_academic_year = $yearutils->get_academic_session($next_session);
?>
<script>
  $(document).ready(function() {
      $('#sms').click(function() {
        $('#sms').append('<img src="../artwork/working.gif" class="busyicon" />');
     });
     $('#sms2').click(function() {
        $('#sms2').append('<img src="../artwork/working.gif" class="busyicon" />');
     });
  });
  function editModule() {
    window.location.href='./edit_module.php?moduleid=' + $('#lineID').val();
  }

  function jumpToModule() {
    window.location = '<?php echo $configObject->get('cfg_root_path') ?>/module/index.php?module=' + $('#lineID').val();
  }

  function studentCohort() {
    window.location = '<?php echo $configObject->get('cfg_root_path') ?>/users/search.php?search_surname=&search_username=&student_id=&module=' + $('#lineID').val() + '&calendar_year=<?php echo $current_session ?>&students=on&submit=Search&userID=&email=&oldUserID=&tmp_surname=&tmp_courseID=&tmp_yearID=';
  }

  function deleteModule() {
    notice=window.open("../delete/check_delete_module.php?idMod=" + $('#lineID').val() + "","notice","width=450,height=180,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-225,screen.height/2-90);
    if (window.focus) {
      notice.focus();
    }
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform" autocomplete="off">

<div class="submenuheading" id="currentmodule"><?php echo $string['currentmodule']; ?></div>
<div id="menu1a">
	<div class="menuitem"><a href="add_module.php"><img class="sidebar_icon" src="../artwork/module_icon_16.png" alt="<?php echo $string['createmodule']; ?>" /><?php echo $string['createmodule'] ?></a></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/edit_grey.png" alt="<?php echo $string['editmodule'] ?>" /><?php echo $string['editmodule'] ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/red_cross_grey.png" alt="<?php echo $string['deletemodule'] ?>" /><?php echo $string['deletemodule'] ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/shortcut_16_grey.png" alt="<?php echo $string['modulefolder'] ?>" /><?php echo $string['modulefolder'] ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/shortcut_16_grey.png" alt="<?php echo $string['studentcohort'] ?>" /><?php echo $string['studentcohort'] ?> (<?php echo $academic_year ?>)</div>
</div>

<div style="display:none" id="menu1b">
	<div class="menuitem"><a href="add_module.php"><img class="sidebar_icon" src="../artwork/module_icon_16.png" alt="<?php echo $string['createmodule'] ?>" /><?php echo $string['createmodule'] ?></a></div>
	<div class="menuitem"><a href="#" onclick="editModule(); return false;"><img class="sidebar_icon" src="../artwork/edit.png" alt="<?php echo $string['editmodule'] ?>" /><?php echo $string['editmodule'] ?></a></div>
	<div class="menuitem"><a href="#" onclick="deleteModule(); return false;"><img class="sidebar_icon" src="../artwork/red_cross.png" alt="<?php echo $string['deletemodule'] ?>" /><?php echo $string['deletemodule'] ?></a></div>
	<div class="menuitem"><a href="#" onclick="jumpToModule(); return false;"><img class="sidebar_icon" src="../artwork/shortcut_16.png" alt="<?php echo $string['modulefolder'] ?>" /><?php echo $string['modulefolder'] ?></a></div>
	<div class="menuitem"><a href="#" onclick="studentCohort(); return false;"><img class="sidebar_icon" src="../artwork/shortcut_16.png" alt="<?php echo $string['studentcohort'] ?>" /><?php echo $string['studentcohort'] ?> (<?php echo $academic_year ?>)</a></div>
</div>

<br />

<div class="submenuheading" id="currentmodule"><?php echo $string['moduleimports']; ?></div>
<div id="menu2">
    <div class="menuitem"><a href="bulk_import_modules.php"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="" /><?php echo $string['bulkmoduleimport'] ?></a></div>
    <?php
        $smsplugin_name = plugin_manager::get_plugin_type_enabled('plugin_sms');
        if ($configObject->get('cfg_sms_api') != '' or count($smsplugin_name) > 0) {
    ?>
    <div class="menuitem"><a href="sms_import_summary.php"><img class="sidebar_icon" src="../artwork/shortcut_16.png" alt="<?php echo $string['importsummary']; ?>" /><?php echo $string['importsummary'] ?></a></div>
    <?php
        }
    ?>
    <?php
        if ($configObject->get('cfg_sms_api') != '') {
    ?>
    <div class="menuitem"><a href="run_users_from_SMS.php?session=<?php echo $current_session ?>"><img class="sidebar_icon" src="../artwork/ims_16.png" alt="<?php echo $string['importsummary'] ?>" />Run Imports (<?php echo $academic_year ?>)</a></div>
    <div class="menuitem"><a href="run_users_from_SMS.php?session=<?php echo $next_session ?>"><img class="sidebar_icon" src="../artwork/ims_16.png" alt="<?php echo $string['importsummary'] ?>" />Run Imports (<?php echo $next_academic_year ?>)</a></div>
    <?php
        }
    ?>

    <?php
    if ($userObject->has_role(array('SysAdmin'))) {
        foreach($smsplugin_name as $name) {
            $smspluginns = 'plugins\SMS\\' . $name. '\\' . $name;
            $smsplugin = new $smspluginns($mysqli, $userObject->get_user_ID());
            $moduleimport = $smsplugin->supports_module_import();
            if ($moduleimport !== false) {
    ?>
    <div id="sms" class="menuitem"><a class="help_tip" title="<?php echo $moduleimport['tooltip'] ?>" href="<?php echo $moduleimport['url'] ?>?session=<?php echo $current_session ?>"><img class="sidebar_icon" src="../artwork/sync_16.png" alt="" /><?php echo $moduleimport['blurb'] ?> (<?php echo $academic_year ?>)</a></div>
    <div id="sms2" class="menuitem"><a class="help_tip" title="<?php echo $moduleimport['tooltip'] ?>" href="<?php echo $moduleimport['url'] ?>?session=<?php echo $next_session ?>"><img class="sidebar_icon" src="../artwork/sync_16.png" alt="" /><?php echo $moduleimport['blurb'] ?> (<?php echo $next_academic_year ?>)</a></div>
    <?php
            }
        }
    }
    ?>
</div>
<input type="hidden" name="lineID" id="lineID" value="" />
</form>
</div>
