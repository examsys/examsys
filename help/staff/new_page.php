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

header('Content-Type: text/html; charset=utf8');

$id = null;
$help_system = new OnlineHelp($userObject, $configObject, $string, $notice, 'staff', $language, $mysqli);

if (isset($_POST['save_changes'])) {
	$tmp_body = $_POST['edit1'];
	$tmp_title = $_POST['title'];
  $roles = $_POST['page_roles'];
  
  $articleid = $help_system->create_page($tmp_title, $tmp_body, $roles);

  $mysqli->close();
  header("location: index.php?id=$articleid");
  exit;
} else {
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
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
    <?php $help_system->display_toolbar($id); ?>
  </div>

  <div id="toc">
    <?php $help_system->display_toc($id); ?>
  </div>
  <div id="contents">
<form name="add_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" autocomplete="off">
  <table cellpadding="0" cellspacing="0" border="0" style="width:100%">
  <tr>
  <td style="padding-left:20px"><input type="text" style="font-family:Verdana,sans-serif; color:#295AAD; font-size:160%; border:1px solid #C0C0C0; font-weight:bold" size="50" name="title" value="" placeholder="<?php echo $string['pagetitle']; ?>" required /></td>
  <td style="text-align:right"><select name="page_roles"><option value="Staff">Staff</option><option value="Admin">Admin</option><option value="SysAdmin">SysAdmin</option></select></td>
  </tr>
  </table>
  <br />

  <textarea class="mceEditor" id="edit1" name="edit1" style="width:100%; height:500px"></textarea>

  <div style="text-align:center; padding-top:8px"><input class="ok" type="submit" name="save_changes" value="<?php echo $string['save'] ?>" /><input class="cancel" type="button" name="cancel" value="<?php echo $string['cancel'] ?>" onclick="history.back();" /></div>
</form>
  </div>
</div>
</body>
</html>
<?php
  }
?>