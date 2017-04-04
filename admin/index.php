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

require '../include/sysadmin_auth.inc';
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

<title>Rog&#333;: Admin<?php echo ' ' . $configObject->get('cfg_install_type') ?></title>

<link rel="stylesheet" type="text/css" href="../css/header.css" />
<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/admin.css" />

<?php echo $configObject->get('cfg_js_root') ?>
<script type="text/javascript" src="../js/staff_help.js"></script>
<script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="../js/sidebar.js"></script>
<script type="text/javascript" src="../js/toprightmenu.js"></script>
<script>
  $(function () {
    $("#clear_training_module").click(function() {
		  var msg = '<?php echo $string['msg1']; ?>';
			return confirm(msg);
		});

    $("#clear_old_logs").click(function() {
		  var msg = '<?php echo $string['msg2']; ?>';
			return confirm(msg);
		});
	});

</script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();

  // How many guest accounts are reserved
  $results = $mysqli->query("SELECT id FROM temp_users");
  $temp_account_no = $results->num_rows;
  $results->close();

  // How many system errors are there
  $results = $mysqli->query("SELECT id FROM sys_errors WHERE fixed IS NULL");
  $sys_error_no = $results->num_rows;
  $results->close();

  // How many system errors are there
  $results = $mysqli->query("SELECT id FROM save_fail_log");
  $save_fail_log_no = $results->num_rows;
  $results->close();

  // How many announcements are there
  $results = $mysqli->query("SELECT id FROM announcements WHERE startdate <= NOW() AND enddate >= NOW() AND deleted IS NULL");
  $announcement_no = $results->num_rows;
  $results->close();

  // How many papers need scheduling
  $results = $mysqli->query("SELECT property_id FROM (properties, scheduling) WHERE (start_date IS NULL AND end_date IS NULL) AND properties.property_id = scheduling.paperID AND deleted IS NULL");
  $scheduling_no = $results->num_rows;
  $results->close();

  $mysqli->close();
?>
<div id="content">

<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a></div>
  <div class="page_title"><?php echo $string['administrativetools'] ?></div>
</div>
  
<?php
  if ($temp_account_no > 0) {
    $string['clearguestaccounts'] .= ' <span class="corners"><span class="num">' . $temp_account_no . '</span></span>';
  }

  if ($sys_error_no > 0) {
    $string['systemerrors'] .= ' <span class="corners"><span class="num">' . $sys_error_no . '</span></span>';
  }
  
  if ($save_fail_log_no > 0) {
    $string['savefailattempts'] .= ' <span class="corners"><span class="num">' . $save_fail_log_no . '</span></span>';    
  }

  if ($announcement_no > 0) {
    $string['announcments'] .= ' <span class="corners"><span class="num">' . $announcement_no . '</span></span>';
  }

  if ($scheduling_no > 0) {
    $string['summativescheduling'] .= ' <span class="corners"><span class="num">' . $scheduling_no . '</span></span>';
  }

  $summative_year =  date('Y');
  if (date('n') < 7) {
    $summative_year--;
  }

  $menudata = array();
  $menudata['academicsessions']     = array('academic_sessions.php', 'sessions.png');
  $menudata['authentication']       = array('./oauth/list_oauth.php', 'auth.png');
  $menudata['bug']                  = array('https://rogo-eassessment.atlassian.net', 'bug.png');
  $menudata['calendar']             = array('calendar.php#week' . date("W"), 'calendar_icon.png');
  $menudata['clearguestaccounts']   = array('clear_guest_users.php', 'clear_guest_users.png');
  $menudata['clearoldlogs']         = array('clear_old_logs.php', 'clear_logs.png');
  $menudata['clearorphanmedia']     = array('orphan_media.php', 'remove_orphan_icon.png');
  $menudata['cleartraining']        = array('clear_training_module.php', 'training.png');
  $menudata['computerlabs']         = array('list_labs.php', 'computer_lab_48.png');
  $menudata['courses']              = array('list_courses.php', 'courses_icon.png');
  $menudata['deniedlogwarnings']    = array('view_access_denied.php', 'access_denied.png');
  $menudata['ebelgridtemplates']    = array('list_ebel_grids.php', 'grid_48.png');
  $menudata['faculties']            = array('list_faculties.php', 'faculty.png');
  $menudata['imslti']               = array('../LTI/lti_keys_list.php', 'lti_key_48.png');
  if ($configObject->get('cfg_ims_enabled')) { 
    $menudata['imssettings']          = array('../plugins/ims/ims_settings.php', 'ims_logo.png');
  }
  $menudata['modules']              = array('list_modules.php', 'modules_icon.png');
  $menudata['announcments']         = array('list_announcements.php', 'news_48.png');
  $menudata['phpinfo']              = array('phpinfo.php', 'php.png');
  $menudata['questionstatuses']     = array('list_statuses.php', 'status_icon.png');
  $menudata['savefailattempts']     = array('list_save_fails.php', 'save_fail_48.png');
  $menudata['schools']              = array('list_schools.php', 'school_icon.png');
  $menudata['statistics']           = array('../statistics/index.php', 'statistics.png');
  if ($configObject->get('cfg_summative_mgmt')) {  // Enable summative management scheduling if not activated.
        $menudata['summativescheduling'] = array('summative_scheduling.php', 'summative_scheduling.png');
  }
  $menudata['systemerrors']         = array('sys_error_list.php', 'system_errors.png');
  $menudata['systeminformation']    = array('system_info.php', 'information.png');
  $menudata['testing']              = array('../testing/', 'crash_test.png');
  $menudata['usermanagement']       = array('../users/search.php', 'user_accounts_icon.png');
  $menudata['plugins']       = array('./plugins/list_plugins.php', 'plugins.png');
  $menudata['config']        = array('config.php', 'config.png');
  if ($configObject->get('cfg_setting_icons_order')) {
    foreach($configObject->get('cfg_setting_icons_order') as $iconkey) {
        if (($iconkey == 'summativescheduling' && !$configObject->get('cfg_summative_mgmt')) || empty($menudata[$iconkey])) continue;
        $parts = explode('.php', $menudata[$iconkey][0]);
        echo '<a class="blacklink" href="' . $menudata[$iconkey][0] . '" id="' . $parts[0] . '">';
        echo '<div class="container"><img src="../artwork/' . $menudata[$iconkey][1] . '" alt="" class="icon" /><br />' . $string[$iconkey] . '</div></a>';
    }
  }
  else {
    foreach($menudata as $menukey => $menuitem) {
        $parts = explode('.php', $menuitem[0]);
        echo '<a class="blacklink" href="' . $menuitem[0] . '" id="' . $parts[0] . '">';
        echo '<div class="container"><img src="../artwork/' . $menuitem[1] . '" alt="" class="icon" /><br />' . $string[$menukey] . '</div></a>';
    }
  }

?>
</div>
  
</div>

</body>
</html>
