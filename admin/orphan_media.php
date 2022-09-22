<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
require '../include/sidebar_menu.inc';
?>
<!DOCTYPE html>
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
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
</head>

<body>

<?php
  require '../include/admin_options.inc';
  require '../include/toprightmenu.inc';

  echo draw_toprightmenu(236);
  set_time_limit(0);
?>

<div id="content">

<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a></div>
  <div class="page_title"><?php echo $string['removeorphanmedia'] ?></div>
</div>
<?php
  $mediadirectory = rogo_directory::get_directory('media');
  $deleted_files = 0;
  $saved_space = 0;
if (isset($_POST['submit']) and isset($_POST['deletefiles'])) {
    $deletefiles = unserialize(base64_decode(($_POST['deletefiles'])));

    foreach ($deletefiles as $filename) {
        $fullpath = $mediadirectory->fullpath($filename);
        $saved_space += filesize($fullpath);
        if (!unlink($fullpath)) {
            echo '<li>' . $string['deletefailed'] . ' ' . $fullpath . "</li>\n";
        } else {
            echo '<li>' . $string['removed'] . ' ' . $fullpath . "</li>\n";
            $deleted_files++;
        }
    }

    echo '<h1>' . $string['cleanupsummary'] . "</h1>\n";
    echo '<table cellpadding="4" cellspacing="0" border="0" style="margin-left:10px">';
    echo '<tr><td style="width: 175px"><strong>' . $string['filedeleted'] . '</strong></td><td>' . number_format($deleted_files) . "</td></tr>\n";
    echo '<tr><td><strong>' . $string['spacereclaimed'] . '</strong></td><td>' . number_format($saved_space / 1024) . "Kb</td></tr>\n";
    echo '</table>';
} else {
    // List of files that should be kept
    $exempt = array('formulary.gif', 'formulary.html');

    function getImages($html)
    {
        $mediadirectory = rogo_directory::get_directory('media');
        $regexp = '#' . $mediadirectory->url('(.*)') . '#';
        $regexp = str_replace('&', '&amp;', $regexp);
        $regexp = str_replace('?', '\?', $regexp);

        $image_array = array();

        $parts = explode('<img', $html);
        if (count($parts) > 0) {
            // Got some images
            unset($parts[0]);
            foreach ($parts as $image_line) {
                $second_split = explode('src="', $image_line);
                $third_split = explode('"', $second_split[1]);
                $image_src = $third_split[0];
                $matches = array();
                preg_match($regexp, $image_src, $matches);
                if (!empty($matches[1])) {
                    $image_src = $matches[1];
                }
                $image_array[] = $image_src;
            }
        }

        return $image_array;
    }

    $file_array = array();
    $missing_array = array();

    //- Get all the files from the 'media' directory first. ------------------------------
    $default_dir = $mediadirectory->location();
    if (!($dp = opendir($default_dir))) {
        die("Cannot open $default_dir.");
    }
    while ($file = readdir($dp)) {
        // Ignore hidden files
        if (mb_substr($file, 0, 1) != '.') {
            $file_array[$file] = 0;
            if (mb_strpos($file, '.flv') !== false) {
                // Set FLV files to used to protect them as they are indirectly referenced by SWF files.
                $file_array[$file] = 1;
            }
        }
    }
    closedir($dp);

    //- Get all the files from the 'media' table. ------------------------------------
    $result = $mysqli->prepare('SELECT source FROM media');
    $result->execute();
    $result->store_result();
    $result->bind_result($source);
    while ($result->fetch()) {
        if (isset($file_array[$source])) {
            $file_array[$source] = 1;
        } else {
            $missing_array[] = $source;
        }
    }
    $result->free_result();
    $result->close();

    //- Check lead-in field for any images (Latex, etc) ---------------------------------
    $result = $mysqli->prepare("SELECT leadin FROM questions WHERE leadin LIKE '%<img%'");
    $result->execute();
    $result->store_result();
    $result->bind_result($leadin);
    while ($result->fetch()) {
        $images = getImages($leadin);
        if (count($images) > 0) {
            foreach ($images as $image) {
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
            foreach ($images as $image) {
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
                if (mb_strpos($sub_part, '.gif') !== false or mb_strpos($sub_part, '.png') !== false or mb_strpos($sub_part, '.jpg') !== false or mb_strpos($sub_part, '.jpeg') !== false) {
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
    $mysqli->close();

    $tmp_date = mktime(0, 0, 0, date('m'), date('d') - 2, date('Y'));
    $deletefiles = array();
    // Run through the array and remove any files not used.
    echo '<h1>' . $string['deletingfiles'] . "</h1>\n<ul>\n";
    $fileusedcount = 0;
    foreach ($file_array as $filename => $file_used) {
        $fullpath = $mediadirectory->fullpath($filename);
        if ($file_used == 0) {
            $file_date = date('Ymd', filectime($fullpath));
            $current_date = date('Ymd', $tmp_date);
            if (in_array($filename, $exempt)) {
                echo '<li>' . $string['notremoving'] .  " $filename <strong>" . $string['inexamptionslist'] . "</strong>.</li>\n";
            } elseif ($file_date < $current_date) {                // Fix for image hotspot and labelling.
                echo '<li>' . $string['toremove'] .  " $filename</li>\n";
                $deletefiles[] = $filename;
            } else {
                echo '<li>' . $string['notremoving'] . " $filename <strong>" . $string['toonew'] .  "</strong>.</li>\n";
            }
            $fileusedcount++;
        }
    }

    // Ask for confirmation before deleting files.
    if (count($deletefiles) > 0) {
        $serlializedeletefiles = base64_encode(serialize(($deletefiles)));
        ?>

  <form id="theform" name="theform" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <input type="hidden" name="deletefiles" value="<?php echo $serlializedeletefiles; ?>">
  <input type="submit" class="ok" name="submit" value="<?php echo $string['delete']; ?>">

        <?php
    }

    if ($fileusedcount == 0) {
        echo '<li>' . $string['noorphanedfiles'] . "</li>\n";
    }

    echo "</ul>\n";

    if (count($missing_array) > 0) {
        sort($missing_array);
        echo '<h1>' . $string['missingfiles'] . "</h1>\n<ul>";
        $old_filename = '';
        foreach ($missing_array as $filename) {
            if ($filename != '' and $filename != $old_filename) {
                echo "<li>$filename</li>\n";
            }
            $old_filename = $filename;
        }
        echo "</ul>\n";
    }

    echo '<h1>' . $string['cleanupsummary'] . "</h1>\n";

    echo '<table cellpadding="4" cellspacing="0" border="0" style="margin-left:10px">';
    echo '<tr><td style="width: 175px"><strong>' . $string['filedeleted'] . '</strong></td><td>' . number_format($deleted_files) . "</td></tr>\n";
    echo '<tr><td><strong>' . $string['spacereclaimed'] . '</strong></td><td>' . number_format($saved_space / 1024) . "Kb</td></tr>\n";
    echo '</table>';
}
?>
</div>

</body>
</html>
