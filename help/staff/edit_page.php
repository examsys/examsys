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

require '../../include/sysadmin_auth.inc';
require '../../include/errors.php';

$pageid = check_var('id', 'REQUEST', true, false, true);
$help_system = new OnlineHelp($userObject, $configObject, $string, $notice, 'staff', $language, $mysqli);

header('Content-Type: text/html; charset=utf8');

$page_details = $help_system->get_page_details($pageid);

if ($page_details === false) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '/artwork/page_not_found.png', '#C00000', true, true);
}

if (isset($_POST['save_changes'])) {
  // Update help file record
  $tmp_body = $_POST['edit1'];
  $tmp_title = $_POST['page_title'];
  $tmp_roles = $_POST['page_roles'];
  
  $help_system->save_page($tmp_title, $tmp_body, $tmp_roles, $pageid, $_POST['edit_id']);
  
  $mysqli->close();
  header("location: index.php?id=$pageid");
  exit;
} elseif (isset($_POST['cancel'])) {
  // Release authoring lock.
  if ($_POST['checkout_authorID'] == $userObject->get_user_ID()) {
    $help_system->release_edit_lock($_POST['edit_id']);
  }
  $mysqli->close();
  header("location: index.php?id=$pageid");
  exit;
} else {
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=7,9,10" >
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

  <title>Rog&#333;: <?php echo $string['help'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/help.css" />

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../../tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
  <script type="text/javascript" src="../../tools/tinymce/jscripts/tiny_mce/tiny_config_help_staff.js"></script>
  <script type="text/javascript" src="../../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../../js/help.js"></script>
  <script>
    $(function () {
		  var docHeight = $(document).height();
			docHeight = docHeight - 135;
		  $('#edit1').css('height', docHeight + 'px');
		});
  </script>
</head>

<body>
<div id="wrapper">
  <div id="toolbar">
    <?php $help_system->display_toolbar($pageid); ?>
  </div>

  <div id="toc">
    <?php $help_system->display_toc($pageid); ?>
  </div>
  <div id="contents">
<form name="add_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?id=$pageid"; ?>" autocomplete="off">
<?php
  if ($page_details['page_type'] == 'pointer') {
    $edit_id = $page_details['body'];
    $ogiginal_details = $help_system->get_page_details($edit_id);
    $page_details['body'] = $ogiginal_details['body'];
  } else {
    $edit_id = $_GET['id'];
  }

  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\"><tr><td style=\"padding-left:20px\"><input type=\"text\" style=\"color:#295AAD; font-size:160%; border: 1px solid #C0C0C0; font-weight:bold\" size=\"50\" name=\"page_title\" value=\"" . $page_details['title'] . "\" required /></td><td style=\"text-align:right\"><select name=\"page_roles\">\n";
  $categories = array('Staff', 'Admin', 'SysAdmin');
  foreach ($categories as $category) {
    if ($category == $page_details['roles']) {
      echo "<option value=\"$category\" selected>$category</option>\n";
    } else {
      echo "<option value=\"$category\">$category</option>\n";
    }
  }

  echo "</select>\n</td></tr></table>\n<br />\n";

  echo "<textarea class=\"mceEditor\" id=\"edit1\" name=\"edit1\" style=\"width:100%; height:500px\">" .  htmlspecialchars($page_details['body'], ENT_NOQUOTES) . "</textarea>\n";

  // Check for lockout.
  $current_time = date('YmdHis');
  $disabled = '';
  if ($userObject->get_user_ID() != $page_details['checkout_authorID']) {
    if ($page_details['checkout_time'] != '' and $current_time - $page_details['checkout_time'] < 10000) {
      $editor = UserUtils::get_user_details($page_details['checkout_authorID'], $mysqli);
      $editor_name = $editor['title'] . ' ' . $editor['initials'] . ' ' . $editor['surname'];
      echo "<script>\n";
      echo "  alert('" . $string['entertitle'] . " $editor_name. " . $string['isinreadonly'] . "')";
      echo "</script>\n";
      $checkout_authorID = $page_details['checkout_authorID'];
      $disabled = ' disabled';
    } else {
      // Set the lock to the current time/author.
      $help_system->set_edit_lock($edit_id);
      
      $checkout_authorID = $userObject->get_user_ID();
    }
  } elseif ($disabled == '' and $userObject->get_user_ID() == $page_details['checkout_authorID']) {
    $checkout_authorID = $userObject->get_user_ID();
  }
  ?>
  <input type="hidden" name="checkout_authorID" value="<?php echo $checkout_authorID ?>" />
  <div style="text-align:center; padding-top:8px"><input class="ok" type="submit" name="save_changes" value="<?php echo $string['save'] ?>"<?php echo $disabled ?> /><input class="cancel" type="submit" name="cancel" value="<?php echo $string['cancel'] ?>" /></div>
  <input type="hidden" name="edit_id" value="<?php echo $edit_id ?>" />
</form>
  </div>
</div>
</body>
</html>
<?php
  $mysqli->close();
  }
?>