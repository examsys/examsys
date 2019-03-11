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
  function viewDetails() {
    window.location = "scheduling_details.php?paperID=" + $('#lineID').val();
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform" autocomplete="off">
<div id="menu1a">
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/information_icon_grey.gif" alt="" /><?php echo $string['viewdetails'] ?></div>
</div>

<div style="display:none" id="menu1b">
	<div class="menuitem"<a href="#" onclick="viewDetails(); return false;"><img class="sidebar_icon" src="../artwork/information_icon.gif" alt="" /><?php echo $string['viewdetails'] ?></a></div>
</div>
<div id="menu1c">
<?php
    if ($userObject->has_role(array('SysAdmin'))) {
        $smsplugin_name = plugin_manager::get_plugin_type_enabled('plugin_sms');
        foreach($smsplugin_name as $name) {
            $smspluginns = 'plugins\SMS\\' . $name . '\\' . $name;
            $smsplugin = new $smspluginns($userObject->get_user_ID());
            $assessmentimport = $smsplugin->supports_assessment_import() ;
            if ($assessmentimport !== false) {
    ?>
    <div id="sms" class="menuitem">
        <a class="help_tip" title="<?php echo $assessmentimport['tooltip'] ?>" href="<?php echo $assessmentimport['url'] ?>?session=<?php echo $current_session ?>"><img class="sidebar_icon" src="../artwork/sync_16.png" alt="" /><?php echo $assessmentimport['blurb'] ?> (<?php echo $academic_year ?>)</a>
    </div>
    <div id="sms2" class="menuitem">
        <a class="help_tip" title="<?php echo $assessmentimport['tooltip'] ?>" href="<?php echo $assessmentimport['url'] ?>?session=<?php echo $next_session ?>"><img class="sidebar_icon" src="../artwork/sync_16.png" alt="" /><?php echo $assessmentimport['blurb'] ?> (<?php echo $next_academic_year ?>)</a>
    </div>
    <?php
            }
        }
    }
    ?>
</div>
<input type="hidden" id="lineID" name="lineID" value="" />
</form>
</div>
