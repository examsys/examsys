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
* @copyright Copyright (c) 2013 The University of Nottingham
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
      $image_src = str_replace('/media/','',$image_src);
      
      $image_array[] = $image_src;
    }
  }
  
  return $image_array;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Orphan Media</title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    h1 {font-size:140%; margin-left:10px}
  </style>

  <script language="JavaScript" src="../js/staff_help.js"></script>
  <?php echo $configObject->get('cfg_js_root') ?>
  <script language="JavaScript" src="../js/sidebar.js"></script>
</head>

<body>

<?php
  require '../include/admin_options.inc';
?>

<div id="content" class="content">

<table class="header">
<tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['removeorphanmedia']; ?></div></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(243); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th></tr>
<tr><th colspan="2" class="bevel"></th></tr>
</table>
<?php

  $file_array = array();
  $missing_array = array();

  //- Get all the files from the 'media' directory first. ------------------------------
  $default_dir = '../media/';
  if (!($dp = opendir($default_dir))) die ("Cannot open $default_dir.");
  while ($file = readdir($dp)) {
    // Ignore hidden files
    if (substr($file, 0, 1) != '.') {
      $file_array[$file] = 0;
      if (strpos($file,'.flv') !== false) {
        // Set FLV files to used to protect them as they are indirectly referenced by SWF files.
        $file_array[$file] = 1;
      }
    }
  }
  closedir($dp);

  //- Get all the files from the 'questions' table. ------------------------------------
  $result = $mysqli->prepare("SELECT q_media FROM questions WHERE q_media != ''");
  $result->execute();
  $result->store_result();
  $result->bind_result($q_media);
  while ($result->fetch()) {
    if (strlen($q_media) != substr_count($q_media,'|')) {     // Extended matching with no graphics.
      $tmp_files = explode('|', $q_media);
      foreach ($tmp_files as $single_file) {
        if (isset($file_array[$single_file])) {
          $file_array[$single_file] = 1;
        } else {
          $missing_array[] = $single_file;
        }
      }
    }
  }
  $result->close();
  
  //- Get all the files from the 'options' table. ------------------------------------
  $result = $mysqli->prepare("SELECT o_media FROM options WHERE o_media != '' ORDER BY id_num");
  $result->execute();
  $result->store_result();
  $result->bind_result($o_media);
  while ($result->fetch()) {
    if (isset($file_array[$o_media])) $file_array[$o_media] = 1;
  }
  $result->close();

  //- Check lead-in field for any images (Latex, etc) ---------------------------------
  $result = $mysqli->prepare("SELECT leadin FROM questions WHERE leadin LIKE '%<img%'");
  $result->execute();
  $result->store_result();
  $result->bind_result($leadin);
  while ($result->fetch()) {
    $images = getImages($leadin);
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
  $result->close();
  
  //- Check scenario field for any images (Latex, etc) ---------------------------------
  $result = $mysqli->prepare("SELECT scenario FROM questions WHERE scenario LIKE '%<img%'");
  $result->execute();
  $result->store_result();
  $result->bind_result($scenario);
  while ($result->fetch()) {
    $images = getImages($scenario);
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
  $result->close();
  
  //- Check correct field for any images (images used as labels in Labelling question) -----------
  $result = $mysqli->prepare("SELECT correct FROM options, questions WHERE questions.q_id=options.o_id AND q_type='labelling'");
  $result->execute();
  $result->store_result();
  $result->bind_result($correct);
  while ($result->fetch()) {
    $parts = explode(';', $correct);
    if (isset($parts[11])) {
      $sub_parts = explode('|', $parts[11]);
      foreach ($sub_parts as $sub_part) {
        if (strpos($sub_part,'.gif') !== false or strpos($sub_part,'.png') !== false or strpos($sub_part,'.jpg') !== false or strpos($sub_part,'.jpeg') !== false) {
          $image_parts = explode('$', $sub_part);
          $image_text = $image_parts[4];
          $image_filename = explode('~', $image_text);
          $image = $image_filename[0];
          if (isset($file_array[$image])) {
            $file_array[$image] = 1;
          } else {
            $missing_array[] = $image;
          }
        }
      }
    }
  }
  $result->close();
  
  $tmp_date = mktime(0, 0, 0, date("m"), date("d")-2, date("Y")); 
  $saved_space = 0;
  $deleted_files = 0;
  // Run through the array and remove any files not used.
  echo "<h1>" . $string['deletingfiles'] . "</h1>\n<ul>\n"; 
  foreach ($file_array as $filename => $file_used) {
    if ($file_used == 0) {
      $file_date = date("Ymd", filectime("../media/$filename"));
	    $current_date = date("Ymd",$tmp_date);  
      if (in_array($filename,$exempt)) {
        echo "<li>" . $string['notremoving'] .  " $filename <strong>" . $string['inexamptionslist'] . "</strong>.</li>\n";	    
      } elseif ($file_date < $current_date) {                // Fix for image hotspot and labelling.
        $saved_space += filesize("../media/$filename");
		    if (!unlink("../media/$filename")) {
          echo "<li>" . $string['deletefailed'] . " ../media/$filename</li>\n";
        } else {        
          echo "<li>" . $string['removed'] .  " $filename</li>\n";
          $deleted_files++;
        }
      } else {
        echo "<li>" . $string['notremoving'] . " $filename <strong>" . $string['toonew'] .  "</strong>.</li>\n";	    
	    }
    }
  }
  echo "</ul>\n";
  
  $mysqli->close();

  if (count($missing_array) > 0) {
    sort($missing_array);
    echo "<h1>" . $string['missingfiles'] . "</h1>\n<ul>";
    $old_filename = '';
    foreach ($missing_array as $filename) {
      if ($filename != '' and $filename != $old_filename) echo "<li>$filename</li>\n";
      $old_filename = $filename;
    }
    echo "</ul>\n";
  }

  echo "<h1>" . $string['cleanupsummary'] . "</h1>\n";

  echo '<table cellpadding="4" cellspacing="0" border="0" style="margin-left:10px">';
  echo "<tr><td style=\"width: 175px\"><strong>" . $string['filedeleted'] . "</strong></td><td>" . number_format($deleted_files) . "</td></tr>\n";
  echo "<tr><td><strong>" . $string['spacereclaimed'] . "</strong></td><td>" . number_format($saved_space / 1024) . "Kb</td></tr>\n";
  echo '</table>';
?>
</div>

</body>
</html>