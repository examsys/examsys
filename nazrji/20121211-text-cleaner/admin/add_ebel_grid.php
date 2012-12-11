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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';

function ebelDropdown($dropdownID) {
  $html = "<select name=\"$dropdownID\">\n";
  $html .= "<option value=\"0\"></option>\n";
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
} else {
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['createtemplate'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    h1 {font-size:120%; color:#1E3287; margin-bottom:0px}
    .field {font-weight:bold; text-align:right; padding-right:10px}
  </style>

  <script src="../js/staff_help.js" type="text/javascript"></script>
  </head>
  
  <body>
  <?php
    require '../include/ebel_grid_options.inc';
  ?>
  <div id="content" class="content">
  <table class="header">
  <tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./list_ebel_grids.php"><?php echo $string['ebelgridtemplates']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['createtemplate']; ?></div></td><td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th></tr>
  <tr><th colspan="2" class="bevel"></th></tr>
  </table>
  
  <blockquote>
  <form name="myform" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
 
    <table cellpadding="5" cellspacing="0" border="0">
    <tr><td style="text-align:right"><?php echo $string['templatename']; ?></td><td colspan="3"><input type="textbox" name="name" size="40" /></td></tr>
    
    <tr><td colspan="4"><h1><?php echo $string['passmark']; ?></h1></td></tr>
    
    <tr><td>&nbsp;</td><td style="width:170px; text-align:center"><?php echo $string['essential']; ?></td><td style="width:170px; text-align:center"><?php echo $string['important']; ?></td><td style="width:170px; text-align:center"><?php echo $string['nicetoknow']; ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['easy']; ?></td><td style="text-align:center; background-color:#F8F8F2"><?php echo ebelDropdown('EE'); ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('EI'); ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('EN'); ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['medium']; ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('ME'); ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('MI'); ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('MN'); ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['hard']; ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('HE'); ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('HI'); ?></td><td style="text-align:center; background-color:#C8C8A6"><?php echo ebelDropdown('HN'); ?></td></tr>
    
    <tr><td colspan="4">&nbsp;</td></tr>
    <tr><td colspan="4"><h1><?php echo $string['distinctionlevel']; ?></h1></td></tr>
    
    <tr><td>&nbsp;</td><td style="width:170px; text-align:center"><?php echo $string['essential']; ?></td><td style="width:170px; text-align:center"><?php echo $string['important']; ?></td><td style="width:170px; text-align:center"><?php echo $string['nicetoknow']; ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['easy']; ?></td><td style="text-align:center; background-color:#F8F8F2"><?php echo ebelDropdown('EE2'); ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('EI2'); ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('EN2'); ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['medium']; ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('ME2'); ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('MI2'); ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('MN2'); ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['hard']; ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('HE2'); ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('HI2'); ?></td><td style="text-align:center; background-color:#C8C8A6"><?php echo ebelDropdown('HN2'); ?></td></td></tr>
    
    
    <tr><td colspan="4">&nbsp;</td></tr>
    <tr><td colspan="4"style="text-align:center"><input type="submit" style="width:100px" name="submit" value="<?php echo $string['save']; ?>">&nbsp;&nbsp;<input style="width:100px" type="button" name="home" value="<?php echo $string['cancel']; ?>" onclick="javascript:history.back();" /></td></tr>
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