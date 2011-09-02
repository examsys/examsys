<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/sysadmin_auth.inc';
  require '../include/sidebar_menu.inc';
  set_time_limit (0);
  
  if ($_POST['Backup'] == 'Create Backup') {
    $backupdir = '/var/tmp/touchstone/';
    $mysqlDump = "/usr/bin/mysqldump";
    $mysqlDumpUser = "notts_nle";
    $mysqlDumpPassword = "";
  	  	
    //copy code
    mkdir($backupdir,0777);
  	
    $output = system("cp -R $cfg_web_root $backupdir");
  	
    //dump databases
    $output = system($mysqlDump . " -u $mysqlDumpUser touchstone > $backupdir/touchstone" . date("Ymd") . ".sql");
  	
    //compress
    $filename = "touchstoneBackup" . date("Ymd") . ".tar.gz";
    $filepath = $backupdir . $filename;
    $output = system("tar -czf $filepath $backupdir" . "* ");
 
    $size = trim(`stat -c%s $backupdir$filename`);
 	
    //download
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename=" . $filename .";");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: $size");
    readfile("$backupdir$filename"); 

    //cleanup
    $output = shell_exec("rm -rf $backupdir");  
    $output = shell_exec("rm $downloadPath$filename");  
    exit; 
  }
  $mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Backup Touchstone</title>

<script language="JavaScript" src="../javascript/sidebar.js"></script>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style>
a {font-family:Arial,sans-serif; color:#215DC6}
a:hover {color:#428EFF}
a.heading {color:#215DC6; font-weight:bold}
a.heading:hover {color:#428EFF; font-weight:bold}
</style>
</head>

<body>

<?php
  require '../include/admin_options.inc';
?>

<div id="content" class="content" style="font-size:80%">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr><td style="background-color:#EBEADB"><div style="font-size:200%; font-weight:bold"><a onmouseover="move_in('image1')" onmouseout="move_out('image1')" href="index.php"><img name="image1" src="../artwork/up_folder_icon_off.gif" style="vertical-align:middle" width="32" height="38" alt="Up" border="0" /></a>&nbsp;Admin/Backup</div></td></tr>
<tr><td style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
</table>
<br /><br />
<div align="center">
<form name="backup" method="post" action="">
<table cellpadding="0" cellspacing="0" border="0" width="700" style="font-size:100%; text-align:justify; line-height:180%">
	<tr><td rowspan="2" style="width:200px"><img src="../artwork/backup_image.png" width="164" height="159" alt="backup" /></td><td>Click 'Create Backup' below to start the backup. This will create a compressed file (tar.gz) of the code, media files and databases used by TouchStone. This takes approximately 2 minutes to complete. You will then be asked where you would like to save the file (approx. 500MB).</td></tr>
	<tr><td align="center"><br /><input type="Submit" name="Backup" value="Create Backup" style="width:150px" /></td></tr>
</form>
</div>
</div>
</body>
</html>