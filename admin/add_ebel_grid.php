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

function ebelDropdown($dropdownID, $required = false) {
  if ($required) {
    $html = "<select name=\"$dropdownID\" required>\n";
  } else {
    $html = "<select name=\"$dropdownID\">\n";
  }
  $html .= "<option value=\"\"></option>\n";
  for ($individual_category=0; $individual_category<=100; $individual_category++) {
    $html .= "<option value=\"$individual_category\">$individual_category%</option>\n";
  }
  $html .= "</select>\n";
  return $html;
}
  
if (isset($_POST['submit'])) {
  $result = $mysqli->prepare("INSERT INTO ebel_grid_templates VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $result->bind_param('iiiiiiiiiiiiiiiiiis', $_POST['EE'], $_POST['EI'], $_POST['EN'], $_POST['ME'], $_POST['MI'], $_POST['MN'], $_POST['HE'], $_POST['HI'], $_POST['HN'], $_POST['EE2'], $_POST['EI2'], $_POST['EN2'], $_POST['ME2'], $_POST['MI2'], $_POST['MN2'], $_POST['HE2'], $_POST['HI2'], $_POST['HN2'], $_POST['name']);
  $result->execute();
  $result->close();

  $mysqli->close();
  
  header("location: list_ebel_grids.php");
  exit();
} else {
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
	<title>Rog&#333;: <?php echo $string['createtemplate'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
	<link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    h1 {font-size:120%; color:#1E3287; margin-bottom:0px}
  </style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/staff_help.js"></script>
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
    <div class="page_title"><?php echo $string['createtemplate'] ?></div>
  </div>
  
  <blockquote>
  <form id="theform" name="myform" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" autocomplete="off">
 
    <table cellpadding="5" cellspacing="0" border="0">
    <tr><td style="text-align:right"><?php echo $string['templatename']; ?></td><td colspan="3"><input type="text" name="name" size="40" maxlength="255" required autofocus /></td></tr>
    
    <tr><td colspan="4"><h1><?php echo $string['passmark']; ?></h1></td></tr>
    
    <tr><td>&nbsp;</td><td style="width:170px; text-align:center"><?php echo $string['essential'] ?></td><td style="width:170px; text-align:center"><?php echo $string['important'] ?></td><td style="width:170px; text-align:center"><?php echo $string['nicetoknow'] ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['easy'] ?></td><td style="text-align:center; background-color:#F8F8F2"><?php echo ebelDropdown('EE', true) ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('EI', true) ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('EN', true) ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['medium'] ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('ME', true) ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('MI', true) ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('MN', true) ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['hard'] ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('HE', true) ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('HI', true) ?></td><td style="text-align:center; background-color:#C8C8A6"><?php echo ebelDropdown('HN', true) ?></td></tr>
    
    <tr><td colspan="4">&nbsp;</td></tr>
    <tr><td colspan="4"><h1><?php echo $string['distinctionlevel']; ?></h1></td></tr>
    
    <tr><td>&nbsp;</td><td style="width:170px; text-align:center"><?php echo $string['essential'] ?></td><td style="width:170px; text-align:center"><?php echo $string['important'] ?></td><td style="width:170px; text-align:center"><?php echo $string['nicetoknow'] ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['easy'] ?></td><td style="text-align:center; background-color:#F8F8F2"><?php echo ebelDropdown('EE2') ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('EI2') ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('EN2') ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['medium'] ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('ME2') ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('MI2') ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('MN2') ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['hard'] ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('HE2') ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('HI2') ?></td><td style="text-align:center; background-color:#C8C8A6"><?php echo ebelDropdown('HN2') ?></td></td></tr>
    
    
    <tr><td colspan="4">&nbsp;</td></tr>
    <tr><td colspan="4"style="text-align:center"><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>"><input class="cancel" id="cancel" type="button" name="home" value="<?php echo $string['cancel'] ?>" /></td></tr>
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