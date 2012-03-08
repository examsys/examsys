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

  require '../include/staff_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Qualitative Analysis<?php echo " $cfg_install_type"; ?></title>
</head>
  <frameset rows="75,*" frameborder="no" border="0">
    <frame marginwidth="0" src="qualitative_options.php?paperID=<?php echo $_GET['paperID']; ?>&startdate=<?php echo $_GET['startdate']; ?>&enddate=<?php echo $_GET['enddate']; ?>&module=<?php echo $_GET['module']; ?>&repcourse=<?php echo $_GET['repcourse']; ?>&repyear=<?php echo $_GET['repyear']; ?>&folder=<?php echo $_GET['folder']; ?>" name="options">
    <frame marginwidth="0" src="qualitative_results.php?paperID=<?php echo $_GET['paperID']; ?>&startdate=<?php echo $_GET['startdate']; ?>&enddate=<?php echo $_GET['enddate']; ?>&module=<?php echo $_GET['module']; ?>&repcourse=<?php echo $_GET['repcourse']; ?>&repyear=<?php echo $_GET['repyear']; ?>" name="results">
  </frameset>
  <noframes>
    Sorry, you need frames to use the Rogō.
  </noframes>
</html>
