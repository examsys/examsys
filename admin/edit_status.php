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
require_once '../include/errors.php';

$data = array();

$s_id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : -1;

if ($s_id != -1) {
  $q_status = new QuestionStatus($mysqli, $string, $s_id);
  $title = $string['edit'] . ' ' . $string['status'];
} else {
  $q_status = new QuestionStatus($mysqli, $string, array());
  $title = $string['add'] . ' ' . $string['status'];
}

if (isset($_POST['submit'])) {
  $data['name'] = $_POST['name'];
  $data['exclude_marking'] = (isset($_POST['exclude_marking'])) ? true : false;
  $data['retired'] = (isset($_POST['retired'])) ? true : false;
  $data['is_default'] = (isset($_POST['is_default'])) ? true : false;
  $data['change_locked'] = (isset($_POST['change_locked'])) ? true : false;
  $data['validate'] = (isset($_POST['validate'])) ? true : false;
  $data['display_warning'] = (isset($_POST['display_warning'])) ? true : false;
  $data['colour'] = $_POST['colour'];

  if (isset($q_status)) {
    $q_status->set_name($data['name']);
    $q_status->set_exclude_marking($data['exclude_marking']);
    $q_status->set_retired($data['retired']);
    $q_status->set_is_default($data['is_default']);
    $q_status->set_change_locked($data['change_locked']);
    $q_status->set_validate($data['validate']);
    $q_status->set_display_warning($data['display_warning']);
    $q_status->set_colour($data['colour']);
  } else {
    $q_status = new QuestionStatus($mysqli, $string, $data);
  }

  try {
    if ($q_status->save()) {
      header("location: list_statuses.php");
      exit();
    }
  } catch (ItemExistsException $ex) {
    $error = 'duplicate';
  }
}

$em_checked = ($q_status->get_exclude_marking()) ? ' checked="checked"' : '';
$es_checked = ($q_status->get_retired()) ? ' checked="checked"' : '';
$default_checked = ($q_status->get_is_default()) ? ' checked="checked"' : '';
$locked_checked = ($q_status->get_change_locked()) ? ' checked="checked"' : '';
$validate_checked = ($q_status->get_validate()) ? ' checked="checked"' : '';
$display_warning_checked = ($q_status->get_display_warning()) ? ' checked="checked"' : '';
?>
<!DOCTYPE html>
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Rog&#333;: <?php echo $title . " " . $configObject->get('cfg_install_type') ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu_qstatus.css" />
  <style type="text/css">
    .admin-form {
      width: auto;
      margin: 16px auto;
    }
    th {text-align:right; font-weight:normal}
    td {text-align:left}
    .admin-form th {text-align:right; padding-right:10px}
    .form-error {
      width: 468px;
      margin: 18px auto;
      padding: 16px;
      background-color: #FFD9D9;
      color: #800000;
      border: 2px solid #800000
    }
    .align-center {
      text-align: center;
    }
    #span_colour {
      width: 20px;
      height: 20px;
      border: 1px solid #909090;
      background-color: <?php echo $q_status->get_colour() ?>;
    }
  </style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    $(function () {
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      
      $('form').removeAttr('novalidate');

      $('#span_colour').click(function (e) { e.stopPropagation(); showPicker('colour', e); });
      
      $('html').click(hidePicker);
      
      $('#cancel').click(function() {
        history.back();
      });
      
    });

  </script>
</head>
<body>
<?php
  require '../include/status_options.inc.php';
  require '../include/toprightmenu.inc';
  require '../tools/colour_picker/colour_picker.inc';
	
	echo draw_toprightmenu();
	
?>
  <div id="content">
    <div class="head_title">
      <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
      <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="list_statuses.php"><?php echo $string['statuses'] ?></a></div>
      <div class="page_title"><?php echo $title ?></div>
    </div>

    <form id="theform" name="status_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" autocomplete="off">
<?php

  if (isset($error) and $error = 'duplicate') {
?>
      <div class="form-error"><?php echo $string['duplicateerror'] ?></div>
<?php
  }
?>
      <table class="admin-form">
        <tr><th><label for="name"><?php echo $string['name'] ?></label></th><td><input type="text" size="50" id="name" name="name" value="<?php echo $q_status->get_name(); ?>" required autofocus /></td></tr>
        <tr><th><label for="exclude_marking"><?php echo $string['excludemarking'] ?></label></th><td><input type="checkbox" id="exclude_marking" name="exclude_marking" <?php echo $em_checked; ?> /></td></tr>
        <tr><th><label for="retired"><?php echo $string['retired'] ?></label></th><td><input type="checkbox" id="retired" name="retired" <?php echo $es_checked; ?> /></td></tr>
        <tr><th><label for="is_default"><?php echo $string['default'] ?></label></th><td><input type="checkbox" id="is_default" name="is_default" <?php echo $default_checked; ?> /></td></tr>
        <tr><th><label for="change_locked"><?php echo $string['setlocked'] ?></label></th><td><input type="checkbox" id="change_locked" name="change_locked" <?php echo $locked_checked; ?> /></td></tr>
        <tr><th><label for="validate"><?php echo $string['validate'] ?></label></th><td><input type="checkbox" id="validate" name="validate" <?php echo $validate_checked; ?> /></td></tr>
        <tr><th><label for="display_warning"><?php echo $string['displaywarning'] ?></label></th><td><input type="checkbox" id="display_warning" name="display_warning" <?php echo $display_warning_checked; ?> /></td></tr>
        <tr>
          <th>
            <label for="colour"><?php echo $string['colour'] ?></label>
          </th>
          <td>
            <input type="hidden" id="colour" name="colour" value="<?php echo $q_status->get_colour(); ?>" />
            <div id="span_colour"></div>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="align-center">
            <input type="hidden" name="id" value="<?php echo $s_id ?>" />
            <input type="submit" name="submit" value="<?php echo $string['save'] ?>" class="ok" /><input type="button" name="home" id="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" />
          </td>
        </tr>
      </table>
    </form>
  </div>
  <div style="clear: both;"></div>
</body>
</html>