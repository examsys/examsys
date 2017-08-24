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
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
require_once 'ims-lti/UoN_LTI.php';
$lti = new UoN_LTI();
$lti->init_lti0($mysqli);
$submit = param::optional('submit', null, param::TEXT, param::FETCH_POST);
if (!is_null($submit)) {
  $ltiname = trim(param::optional('ltiname', null, param::TEXT, param::FETCH_POST));
  $ltikey = trim(param::optional('ltikey', null, param::TEXT, param::FETCH_POST));
  $ltisec = trim(param::optional('ltisec', null, param::TEXT, param::FETCH_POST));
  $lticontext = trim(param::optional('lticontext', null, param::TEXT, param::FETCH_POST));
  $insert_id = $lti->add_lti_key($ltiname, $ltikey, $ltisec, $lticontext);
  header("location: lti_keys_list.php");
  exit();
} else {
  ?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>
  <title>Rog&#333;: <?php echo $string['addltikeys'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    td {
      text-align: left
    }
    .field {
      font-weight: bold;
      text-align: right;
      padding-right: 10px
    }
  </style>

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
    });
  </script>
</head>
<body>
<?php
  require '../include/lti_keys_options.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>
<div id="content">

<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-"/><a href="../admin/index.php"><?php echo $string['administrativetools']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-"/><a href="lti_keys_list.php"><?php echo $string['ltikeys']; ?></a></div>
  <div class="page_title"><?php echo $string['addltikeys']; ?></div>
</div>

  <br/>
  <div align="center">
      <form id="theform" name="add_LTIkeys" method="post" action="<?php echo url::fromGlobals(); ?>" autocomplete="off">
      <table>
        <tr>
          <td class="field"><?php echo $string['name']; ?></td>
          <td><input type="text" size="70" maxlength="255" name="ltiname" id="ltiname" required /></td>
        </tr>
        <tr>
          <td class="field"><?php echo $string['oauth_consume_key']; ?></td>
          <td><input type="text" size="70" maxlength="255" name="ltikey" id="ltikey" required /></td>
        </tr>
        <tr>
          <td class="field"><?php echo $string['oauth_secret']; ?></td>
          <td><input type="text" size="70" maxlength="255" name="ltisec" id="ltisec" required /></td>
        </tr>
        <tr>
          <td class="field"><?php echo $string['oauth_context_id']; ?></td>
          <td><input type="text" size="70" maxlength="255" name="lticontext" id="lticontext" /></td>
        </tr>
      </table>
      <p><input type="submit" class="ok" name="submit" value="<?php echo $string['add']; ?>"/><input class="cancel" type="button" name="home" value="<?php echo $string['cancel']; ?>" onclick="javascript:history.back();"/></p>
    </form>
  </div>
  <?php
}
?>
</div>
</body>
</html>