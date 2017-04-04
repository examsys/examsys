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
require '../include/errors.php';
  
$gridID = check_var('id', 'GET', true, false, true);

function ebelDropdown($dropdownID, $default, $required = false) {
  if ($required) {
    $html = "<select name=\"$dropdownID\" required>\n";
  } else {
    $html = "<select name=\"$dropdownID\">\n";
  }
  $html .= "<option value=\"\"></option>\n";
  for ($individual_category=0; $individual_category<=100; $individual_category++) {
    if ($individual_category == $default) {
      $html .= "<option value=\"$individual_category\" selected>$individual_category%</option>\n";
    } else {
      $html .= "<option value=\"$individual_category\">$individual_category%</option>\n";
    }
  }
  $html .= "</select>\n";
  return $html;
}

if (isset($_POST['submit'])) {
  $types = array('EE', 'EI', 'EN', 'ME', 'MI', 'MN', 'HE', 'HI', 'HN', 'EE2', 'EI2', 'EN2', 'ME2', 'MI2', 'MN2', 'HE2', 'HI2', 'HN2');
  foreach ($types as $type) {
    if (isset($_POST[$type])) {
      $$type = (int)$_POST[$type];
    } else {
      $$type = 0;
    }
  }
  
  $result = $mysqli->prepare("UPDATE ebel_grid_templates SET EE = ?, EI = ?, EN = ?, ME = ?, MI = ?, MN = ?, HE = ?, HI = ?, HN = ?, EE2 = ?, EI2 = ?, EN2 = ?, ME2 = ?, MI2 = ?, MN2 = ?, HE2 = ?, HI2 = ?, HN2 = ?, name = ? WHERE id = ?");
  $result->bind_param('iiiiiiiiiiiiiiiiiisi', $EE, $EI, $EN, $ME, $MI, $MN, $HE, $HI, $HN, $EE2, $EI2, $EN2, $ME2, $MI2, $MN2, $HE2, $HI2, $HN2, $_POST['name'], $gridID);
  $result->execute();
  $result->close();
  
  $mysqli->close();
  
  header("location: list_ebel_grids.php");
  exit();
} else {
  $result = $mysqli->prepare("SELECT EE, EI, EN, ME, MI, MN, HE, HI, HN, EE2, EI2, EN2, ME2, MI2, MN2, HE2, HI2, HN2, name FROM ebel_grid_templates WHERE id = ?");
  $result->bind_param('i', $gridID);
  $result->execute();
  $result->store_result();
  $result->bind_result($EE, $EI, $EN, $ME, $MI, $MN, $HE, $HI, $HN, $EE2, $EI2, $EN2, $ME2, $MI2, $MN2, $HE2, $HI2, $HN2, $name);
  $result->fetch();
  if ($result->num_rows == 0) {
    $result->close();
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  }
  $result->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Rog&#333;: <?php echo $string['edittemplate'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    h1 {font-size:120%; color:#1E3287; margin-bottom:0px}
  </style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
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
      
      $('#cancel').click(function() {
        history.back();
      });
    });
  </script>
</head>
  
<body>
  <?php
    require '../include/ebel_grid_options.php';
		require '../include/toprightmenu.inc';

		echo draw_toprightmenu();
  ?>
  <div id="content">
  
  <div class="head_title">
    <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
    <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./list_ebel_grids.php"><?php echo $string['ebelgridtemplates'] ?></a></div>
    <div class="page_title"><?php echo $string['edittemplate']?></div>
  </div>   

  <blockquote>
  <form id="theform" name="myform" method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $_GET['id']; ?>" autocomplete="off">
 
    <table cellpadding="5" cellspacing="0" border="0">
    <tr><td style="text-align:right"><?php echo $string['templatename']; ?></td><td colspan="3"><input type="text" name="name" size="40" maxlength="255" value="<?php echo $name; ?>" required /></td></tr>
    
    <tr><td colspan="4"><h1><?php echo $string['passmark']; ?></h1></td></tr>
    
    <tr><td>&nbsp;</td><td style="width:170px; text-align:center"><?php echo $string['essential'] ?></td><td style="width:170px; text-align:center"><?php echo $string['important'] ?></td><td style="width:170px; text-align:center"><?php echo $string['nicetoknow'] ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['easy'] ?></td><td style="text-align:center; background-color:#F8F8F2"><?php echo ebelDropdown('EE', $EE, true) ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('EI', $EI, true) ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('EN', $EN, true) ?></td><td style="border:0"><input type="text" value="" name="easy_total" size="8" style="border:0" /></td></tr>
    <tr><td style="text-align:right"><?php echo $string['medium'] ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('ME', $ME, true) ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('MI', $MI, true) ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('MN', $MN, true) ?></td><td style="border:0"><input type="text" value="" name="medium_total" size="8" style="border:0" /></td></tr>
    <tr><td style="text-align:right"><?php echo $string['hard'] ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('HE', $HE, true) ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('HI', $HI, true) ?></td><td style="text-align:center; background-color:#C8C8A6"><?php echo ebelDropdown('HN', $HN, true) ?></td><td style="border:0"><input type="text" value="" name="hard_total" size="8" style="border:0" /></td></tr>
    <tr><td>&nbsp;</td><td style="text-align:center"><input type="text" value="" name="essential_total" size="8" style="text-align:center; border:0" /></td><td style="text-align:center"><input type="text" value="" name="important_total" size="8" style="text-align:center; border:0" /></td><td style="text-align:center"><input type="text" value="" name="nice_total" size="8" style="text-align:center; border:0" /></td></tr>
    
    <tr><td colspan="4"><h1><?php echo $string['distinctionlevel']; ?></h1></td></tr>
    
    <tr><td>&nbsp;</td><td style="width:170px; text-align:center"><?php echo $string['essential']; ?></td><td style="width:170px; text-align:center"><?php echo $string['important']; ?></td><td style="width:170px; text-align:center"><?php echo $string['nicetoknow'] ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['easy'] ?></td><td style="text-align:center; background-color:#F8F8F2"><?php echo ebelDropdown('EE2', $EE2) ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('EI2', $EI2) ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('EN2', $EN2) ?></td><td style="border:0"><input type="text" value="" name="easy_total" size="8" style="border:0" /></td></tr>
    <tr><td style="text-align:right"><?php echo $string['medium'] ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('ME2', $ME2) ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('MI2', $MI2) ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('MN2', $MN2) ?></td><td style="border:0"><input type="text" value="" name="medium_total" size="8" style="border:0" /></td></tr>
    <tr><td style="text-align:right"><?php echo $string['hard'] ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('HE2', $HE2) ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('HI2', $HI2) ?></td><td style="text-align:center; background-color:#C8C8A6"><?php echo ebelDropdown('HN2', $HN2) ?></td><td style="border:0"><input type="text" value="" name="hard_total" size="8" style="border:0" /></td></tr>
    <tr><td>&nbsp;</td><td style="text-align:center"><input type="text" value="" name="essential_total" size="8" style="text-align:center; border:0" /></td><td style="text-align:center"><input type="text" value="" name="important_total" size="8" style="text-align:center; border:0" /></td><td style="text-align:center"><input type="text" value="" name="nice_total" size="8" style="text-align:center; border:0" /></td></tr>
    
    <tr><td colspan="4"style="text-align:center"><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>"><input class="cancel" type="button" name="home" id="cancel" value="<?php echo $string['cancel'] ?>" /></td></tr>
    </table>
    
    <br />
  </form>
  </blockquote>
</div>
<?php
}
?>
</body>
</html>