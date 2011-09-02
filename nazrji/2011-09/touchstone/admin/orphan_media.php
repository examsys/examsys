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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
require '../include/sidebar_menu.inc';
set_time_limit (0);

// List of files that should be kept
$exempt = array('formulary.gif','formulary.html');

function getImages($html) {
  $image_array = array();
  
  $parts = explode('<img',$html);
  if (count($parts) > 0) {
    // Got some images
    unset($parts[0]);
    foreach ($parts as $image_line) {
      $second_split = explode('src="',$image_line);
      $third_split = explode('"',$second_split[1]);
      $image_src = $third_split[0];
      $image_src = str_replace('./media/','',$image_src);
      $image_src = str_replace('/touchstone/media/','',$image_src);
      
      $image_array[] = $image_src;
    }
  }
  
  return $image_array;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Orphan Media</title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style>
h1 {font-size:140%; margin-left:10px}
</style>

<script language="JavaScript" src="../javascript/staff_help.js"></script>
<script language="JavaScript" src="../javascript/sidebar.js"></script>
</head>

<body>

<?php
  require '../include/admin_options.inc';
?>

<div id="content" class="content" style="font-size:80%">

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr><td style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../index.php">Home</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php">Administrative Tools</a></div><div style="margin-left:10px; font-size:200%; font-weight:bold">Remove Orphan Media</div></td>
<td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(243); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td></tr>
<tr><td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
</table>
<?php

  $file_array = array();
  $missing_array = array();

  //- Get all the files from the 'media' directory first. ------------------------------
  $default_dir = '../media/';
  if (!($dp = opendir($default_dir))) die ("Cannot open $default_dir.");
  while ($file = readdir($dp)) {
    if ($file != '.' and $file != '..' and $file != '.htaccess') {
      $file_array[$file] = 0;
      if (strpos($file,'.flv') !== false) {
        // Set FLV files to used to protect them as they are indirectly referenced by SWF files.
        $file_array[$file] = 1;
      }
    }
  }
  closedir($dp);

  //- Get all the files from the 'questions' table. ------------------------------------
  $results = $mysqli->query("SELECT q_media FROM questions WHERE q_media != ''");
  while ($row = $results->fetch_assoc()) {
    if (strlen($row['q_media']) != substr_count($row['q_media'],'|')) {     // Extended matching with no graphics.
      $tmp_files = explode('|',$row['q_media']);
      foreach ($tmp_files as $single_file) {
        if (isset($file_array[$single_file])) {
          $file_array[$single_file] = 1;
        } else {
          $missing_array[] = $single_file;
        }
      }
    }
  }
  $results->close();
  
  //- Get all the files from the 'options' table. ------------------------------------
  $results = $mysqli->query("SELECT o_media FROM options WHERE o_media != '' ORDER BY id_num");
  while ($row = $results->fetch_assoc()) {
    if (isset($file_array[$row['o_media']])) $file_array[$row['o_media']] = 1;
  }
  $results->close();

  //- Check lead-in field for any images (Latex, etc) ---------------------------------
  $results = $mysqli->query("SELECT leadin FROM questions WHERE leadin LIKE '%<img%'");
  while ($row = $results->fetch_assoc()) {
    $images = getImages($row['leadin']);
    if (count($images) > 0) {
      foreach($images as $image) {
        if (isset($file_array[$image])) {
          $file_array[$image] = 1;
        } else {
          $missing_array[] = $image;
        }
      }
    }
  }
  $results->close();
  
  //- Check scenario field for any images (Latex, etc) ---------------------------------
  $results = $mysqli->query("SELECT scenario FROM questions WHERE scenario LIKE '%<img%'");
  while ($row = $results->fetch_assoc()) {
    $images = getImages($row['scenario']);
    if (count($images) > 0) {
      foreach($images as $image) {
        if (isset($file_array[$image])) {
          $file_array[$image] = 1;
        } else {
          $missing_array[] = $image;
        }
      }
    }
  }
  $results->close();
  
  $tmp_date = mktime(0, 0, 0, date("m"), date("d")-2, date("Y")); 
  $saved_space = 0;
  $deleted_files = 0;
  // Run through the array and remove any files not used.
  echo "<h1>Deleting Files!</h1>\n<ul>\n"; 
  foreach ($file_array as $filename => $file_used) {
    if ($file_used == 0) {
      $file_date = date("Ymd", filectime("../media/$filename"));
	    $current_date = date("Ymd",$tmp_date);  
      if (in_array($filename,$exempt)) {
        echo "<li>NOT Removing: $filename <strong>in examptions list</strong>.</li>\n";	    
      } elseif ($file_date < $current_date) {                // Fix for image hotspot and labelling.
        $saved_space += filesize("../media/$filename");
		    if (!unlink("../media/$filename")) {
          echo "<li>Delete Failed: ../media/$filename</li>\n";
        } else {        
          echo "<li>Removed: $filename</li>\n";
          $deleted_files++;
        }
      } else {
        echo "<li>NOT Removing: $filename <strong>It is to new</strong>.</li>\n";	    
	    }
    }
  }
  echo "</ul>\n";
  
  $mysqli->close();

  if (count($missing_array) > 0) {
    sort($missing_array);
    echo "<h1>Missing Files!</h1>\n<ul>";
    $old_filename = '';
    foreach ($missing_array as $filename) {
      if ($filename != '' and $filename != $old_filename) echo "<li>$filename</li>\n";
      $old_filename = $filename;
    }
    echo "</ul>\n";
  }

  echo "<h1>Clean-up Summary</h1>\n";

  echo '<table cellpadding="4" cellspacing="0" border="0" style="margin-left:10px">';
  echo "<tr><td style=\"width: 175px\"><strong>Files deleted</strong></td><td>" . number_format($deleted_files) . "</td></tr>\n";
  echo "<tr><td><strong>Space reclaimed</strong></td><td>" . number_format($saved_space / 1024) . "Kb</td></tr>\n";
  echo '</table>';
?>
</div>

</body>
</html>