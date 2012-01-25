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
require '../include/errors.inc';
  
check_var('id', 'GET', true, false);

function ebelDropdown($dropdownID, $default) {
  $html = "<select name=\"$dropdownID\">\n";
  $html .= "<option value=\"0\"></option>\n";
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
  $result = $mysqli->prepare("UPDATE ebel_grid_templates SET EE=?, EI=?, EN=?, ME=?, MI=?, MN=?, HE=?, HI=?, HN=?, EE2=?, EI2=?, EN2=?, ME2=?, MI2=?, MN2=?, HE2=?, HI2=?, HN2=?, name=? WHERE id=?");
  $result->bind_param('iiiiiiiiiiiiiiiiiisi', $_POST['EE'], $_POST['EI'], $_POST['EN'], $_POST['ME'], $_POST['MI'], $_POST['MN'], $_POST['HE'], $_POST['HI'], $_POST['HN'], $_POST['EE2'], $_POST['EI2'], $_POST['EN2'], $_POST['ME2'], $_POST['MI2'], $_POST['MN2'], $_POST['HE2'], $_POST['HI2'], $_POST['HN2'], $_POST['name'], $_GET['id']);
  $result->execute();
  $result->close();
  
  $mysqli->close();
  header("location: list_ebel_grids.php");
} else {
  $result = $mysqli->prepare("SELECT EE, EI, EN, ME, MI, MN, HE, HI, HN, EE2, EI2, EN2, ME2, MI2, MN2, HE2, HI2, HN2, name FROM ebel_grid_templates WHERE id=?");
  $result->bind_param('i', $_GET['id']);
  $result->execute();
  $result->bind_result($EE, $EI, $EN, $ME, $MI, $MN, $HE, $HI, $HN, $EE2, $EI2, $EN2, $ME2, $MI2, $MN2, $HE2, $HI2, $HN2, $name);
  $result->fetch();
  $result->close();

?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <title><?php echo $string['edittemplate'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />

  <style>
    h1 {font-size:120%; color:#1E3287}
    input, textarea {font-family:Arial,sans-serif; color:black}
    .field {font-weight:bold; text-align:right; padding-right:10px}
  </style>

  <script src="../js/staff_help.js" type="text/javascript"></script>
  </head>
  
  <body>
  <?php
    require '../include/ebel_grid_options.inc';
  ?>
  <div id="content" class="content" style="font-size:80%">
  <table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr><td style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./list_ebel_grids.php"><?php echo $string['ebelgridtemplates']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['edittemplate']; ?></div></td><td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td></tr>
  <tr><td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" /></td></tr>
  </table>
  
  <blockquote>
  <form name="myform" method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $_GET['id']; ?>">
 
    <table cellpadding="5" cellspacing="0" border="0">
    <tr><td style="text-align:right"><?php echo $string['templatename']; ?></td><td colspan="3"><input type="textbox" name="name" size="40" value="<?php echo $name; ?>" /></td></tr>
    
    <tr><td colspan="4"><h1><?php echo $string['passmark']; ?></h1></td></tr>
    
    <tr><td>&nbsp;</td><td style="width:170px; text-align:center"><?php echo $string['essential']; ?></td><td style="width:170px; text-align:center"><?php echo $string['important']; ?></td><td style="width:170px; text-align:center"><?php echo $string['nicetoknow']; ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['easy']; ?></td><td style="text-align:center; background-color:#F8F8F2"><?php echo ebelDropdown('EE', $EE); ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('EI', $EI); ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('EN', $EN); ?></td><td style="border:0px"><input type="text" value="" name="easy_total" size="8" style="border:0px" /></td></tr>
    <tr><td style="text-align:right"><?php echo $string['medium']; ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('ME', $ME); ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('MI', $MI); ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('MN', $MN); ?></td><td style="border:0px"><input type="text" value="" name="medium_total" size="8" style="border:0px" /></td></tr>
    <tr><td style="text-align:right"><?php echo $string['hard']; ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('HE', $HE); ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('HI', $HI); ?></td><td style="text-align:center; background-color:#C8C8A6"><?php echo ebelDropdown('HN', $HN); ?></td><td style="border:0px"><input type="text" value="" name="hard_total" size="8" style="border:0px" /></td></tr>
    <tr><td>&nbsp;</td><td style="text-align:center"><input type="text" value="" name="essential_total" size="8" style="text-align:center; border:0px" /></td><td style="text-align:center"><input type="text" value="" name="important_total" size="8" style="text-align:center; border:0px" /></td><td style="text-align:center"><input type="text" value="" name="nice_total" size="8" style="text-align:center; border:0px" /></td></tr>
    
    <tr><td colspan="4">&nbsp;</td></tr>
    <tr><td colspan="4"><h1><?php echo $string['distinctionlevel']; ?></h1></td></tr>
    
    <tr><td>&nbsp;</td><td style="width:170px; text-align:center"><?php echo $string['essential']; ?></td><td style="width:170px; text-align:center"><?php echo $string['important']; ?></td><td style="width:170px; text-align:center"><?php echo $string['nicetoknow']; ?></td></tr>
    <tr><td style="text-align:right"><?php echo $string['easy']; ?></td><td style="text-align:center; background-color:#F8F8F2"><?php echo ebelDropdown('EE2', $EE2); ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('EI2', $EI2); ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('EN2', $EN2); ?></td><td style="border:0px"><input type="text" value="" name="easy_total" size="8" style="border:0px" /></td></tr>
    <tr><td style="text-align:right"><?php echo $string['medium']; ?></td><td style="text-align:center; background-color:#F0F0E6"><?php echo ebelDropdown('ME2', $ME2); ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('MI2', $MI2); ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('MN2', $MN2); ?></td><td style="border:0px"><input type="text" value="" name="medium_total" size="8" style="border:0px" /></td></tr>
    <tr><td style="text-align:right"><?php echo $string['hard']; ?></td><td style="text-align:center; background-color:#E4E4D2"><?php echo ebelDropdown('HE2', $HE2); ?></td><td style="text-align:center; background-color:#D5D5BB"><?php echo ebelDropdown('HI2', $HI2); ?></td><td style="text-align:center; background-color:#C8C8A6"><?php echo ebelDropdown('HN2', $HN2); ?></td><td style="border:0px"><input type="text" value="" name="hard_total" size="8" style="border:0px" /></td></tr>
    <tr><td>&nbsp;</td><td style="text-align:center"><input type="text" value="" name="essential_total" size="8" style="text-align:center; border:0px" /></td><td style="text-align:center"><input type="text" value="" name="important_total" size="8" style="text-align:center; border:0px" /></td><td style="text-align:center"><input type="text" value="" name="nice_total" size="8" style="text-align:center; border:0px" /></td></tr>
    
    
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