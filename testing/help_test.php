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
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Help pages internal consistency test</title>

</head>
<body>

<div id="main">
<?php
$target = 'staff';

if (isset($_GET['target'])) {
    $target = $_GET['target'];
}

$targettable = $target . '_help';
$dbresult = $mysqli->prepare('SELECT id, body, title, type FROM ' . $targettable . ' ORDER BY id');
$dbresult->execute();
$help_toc = array();
$help_img = array();
$dbresult->bind_result($id, $body, $title, $type);

while ($dbresult->fetch()) {
    $help_toc[$id]['id'] = $id;
    $help_toc[$id]['body'] = $body;
    $help_toc[$id]['type'] = $type;
    $help_toc[$id]['title'] = $title;
    $help_toc[$id]['links'] = '';
}
$dbresult->close();

echo '<a href="help_test.php?target=staff">staff</a> ';
echo '<a href="help_test.php?target=student">student</a>';
echo '<h1>Help pages internal consistency test</h1>';

//reading image files' names
$avail_images = array();
$help_directory = rogo_directory::get_directory('help_' . $target);
$pubs = $help_directory->location();

if ($handle = opendir($pubs)) {
    while (false !== ($file = readdir($handle))) {
        if ($file != '' && $file != '.' && $file != '..' && $file != '.DS_Store') {
            $avail_images[$file] = 1;
        }
    }
    closedir($handle);
}

//internal links
$result = '';

foreach ($help_toc as $help_item) {
    $test = explode('?id=', $help_item['body']);
    if (count($test) > 1) {
        for ($i = 1; $i < count($test); $i++) {
            $pos1 = mb_strpos($test[$i], '"');
            $pos2 = mb_strpos($test[$i], '>', $pos1) + 1;
            $pos3 = mb_strpos($test[$i], '</a>', $pos1);
            $link = mb_substr($test[$i], 0, $pos1);
            $text = mb_substr($test[$i], $pos2, $pos3 - $pos2);
            if (isset($help_toc[$link])) {
                $help_toc[$link]['links'] .= $help_item['id'] . ',';
            } else {
                $result .= 'link reference is missing in: "<strong><a href="../help/' . $target . '/index.php?id=' . $help_item['id'] . '">' . $help_item['title'] . '</a></strong>" (id=<strong><a href="../help/' . $target . '/index.php?id=' . $help_item['id'] . '">' . $help_item['id'] . '</a></strong>) to: "' . $text . '" (id=' . $link . ')<br />';
            }
        }
    }
}

echo '<h3>Broken internal links:</h3>';
echo $result;

if ($result == '') {
    echo ' - not detected.';
}

echo '<hr>';
//incorporated images
$helpimage_regexp = '#src=".*filename=(?<filename>.*)" alt=".*?" width="(?<width>.*?)" height="(?<height>.*?)"#';

foreach ($help_toc as $help_item) {
    //search for <img scr=
    $test = array();
    $imagecount = preg_match_all($helpimage_regexp, $help_item['body'], $test);
    if ($imagecount > 0) {
        for ($i = 0; $i < $imagecount; $i++) {
            $code = $test['filename'][$i];
            $width = $test['width'][$i];
            $height = $test['height'][$i];
            if (!isset($help_img[$code])) {
                $help_img[$code] = array();
            }
            array_push($help_img[$code], array($help_item['id'], $width, $height));
        }
    } else {
        //search for background-image: url
        $test = explode(' url(', $help_item['body']);
        if (count($test) > 1) {
            for ($i = 1; $i < count($test); $i++) {
                $code = preg_split("/\'|\"/", $test[$i]);
                $w = - 2;
                $h = - 2;
                if (!isset($help_img[$code[1]])) {
                    $help_img[$code[1]] = array();
                }
                if (count($code) >= 2) {
                    array_push($help_img[$code[1]], array($help_item['id'], $w, $h));
                }
            }
        }
    }
}

$result1 = '';
$result2 = '';
$result3 = '';
$result_array_2 = array();
$result_array_3 = array();
$i = 0;

foreach ($help_img as $img_item => $img_ids) {
    $path = $help_directory->fullpath($img_item);
    if (file_exists($path)) {
        if (!($img_size = getimagesize($path))) {
            $img_size = false;
        }
    }

    if (!$img_size) {
        $result1 .= 'image "' . $img_item . '" is missing - ';
    }
    foreach ($img_ids as $item_id => $item_val) {
        $i++;
        if (!$img_size && $item_val != '') {
            $result1 .= '"<a href="../help/' . $target . '/index.php?id=' . $item_val[0] . '">' . $help_toc[$item_val[0]]['title'] . '</a>" (id=<a href="../help/' . $target . '/index.php?id=' . $item_val[0] . '">' . $item_val[0] . '</a>) ';
        }
        if ($img_size) {
            array_push($help_img[$img_item][$item_id], $img_size[0], $img_size[1]);

            if (($help_img[$img_item][$item_id][1] * 1 != $help_img[$img_item][$item_id][3]) || ($help_img[$img_item][$item_id][2] * 1 != $help_img[$img_item][$item_id][4])) {
                if ($help_img[$img_item][$item_id][1] == '-1' || $help_img[$img_item][$item_id][2] == '-1') {
                    $result3 = 'Dimensions ( width="' . $help_img[$img_item][$item_id][3] . '" height="' . $help_img[$img_item][$item_id][4] . '" ) for image "' . $img_item . '" are ';
                    $result3 .= 'not fully set ';
                    $result3 .= 'in: "<a href="../help/' . $target . '/index.php?id=' . $item_val[0] . '">' . $help_toc[$item_val[0]]['title'] . '</a>" (id=<a href="../help/' . $target . '/index.php?id=' . $item_val[0] . '">' . $item_val[0] . '</a>)<br />';
                    $result_array_3[$item_val[0] * 1000 + $i] = $result3;
                } elseif ($help_img[$img_item][$item_id][1] != '-2') {
                    $result2 = 'Dimensions ( width="' . $help_img[$img_item][$item_id][3] . '" height="' . $help_img[$img_item][$item_id][4] . '" ) for image "' . $img_item . '" are ';
                    $result2 .= 'set to ( width="' . $help_img[$img_item][$item_id][1] . '" height="' . $help_img[$img_item][$item_id][2] . '" ) ';
                    $result2 .= 'in: "<a href="../help/' . $target . '/index.php?id=' . $item_val[0] . '">' . $help_toc[$item_val[0]]['title'] . '</a>" (id=<a href="../help/' . $target . '/index.php?id=' . $item_val[0] . '">' . $item_val[0] . '</a>)<br />';
                    $result_array_2[$item_val[0] * 1000 + $i] = $result2;
                }
            }
        }
    }
    if (!$img_size) {
        $result1 .= '<br />';
    }
}

echo '<h3>Missing images:</h3>';
echo $result1;

if ($result1 == '') {
    echo ' - not detected.<br />';
}

echo '<hr />';
echo '<h3>Image dimensions\' inconsistencies:</h3>';
ksort($result_array_2);

foreach ($result_array_2 as $result2) {
    echo $result2;
}

ksort($result_array_3);

foreach ($result_array_3 as $result3) {
    echo $result3;
}
if (count($result_array_3) == 0 && count($result_array_2) == 0) {
    echo ' - not detected.<br />';
}


foreach ($help_img as $img_item => $img_ids) {
    $avail_images[$img_item] = 2;
}

foreach ($avail_images as $img_item => $img_use) {
    $img_items = preg_replace('/_/', '\\_', $img_item);
    $sql = 'SELECT id, deleted FROM ' . $targettable . " WHERE body LIKE '%$img_items%'; "; //COLLATE latin1_general_cs
    $dbresult2 = $mysqli->prepare($sql);
    $dbresult2->execute();
    $dbresult2->bind_result($id, $del);
    while ($dbresult2->fetch()) {
        if ($id != null && $avail_images[$img_item] < 5) {
            $avail_images[$img_item] = ($avail_images[$img_item] * 10 + 1);
        }
        if ($avail_images[$img_item] == 11) {
            $avail_images[$img_item] = (1 * $id + 1000);
        }
        if ($del != null) {
            $avail_images[$img_item] = (1 * $id + 2000);
        }
    }
    $dbresult2->close();
}

$img_count = 0;
foreach ($help_img as $img_item => $img_ids) {
    if (mb_strpos($img_item, 'images') > -1) {
        $img_count++;
    }
}

echo '<hr>';
echo 'Number of images used from "images" folder:' . ($img_count) . '<br />';
echo 'Number of images available from "images" folder:' . (count($avail_images)) . '<br />';
echo 'Number of unused images from "images" folder:' . (count($avail_images) - count($help_img)) . '<br />';
echo 'Number of images used from other locations:' . (count($help_img) - $img_count) . '<br />';

echo '<h3>Unused images:</h3>';
$result = '';

foreach ($avail_images as $img_item => $img_use) {
    if ($img_use == 1) {
        $imgurl = $help_directory->url($img_item);
        $result .= "<li><a href=\"$imgurl\">$img_item</a></li>";
        unlink($help_directory->fullpath($img_item));
    }
}

echo '<ol>' . $result . '</ol>';

if ($result == '') {
    echo ' - not found.<br />';
}

echo '<h3>Files from deleted pages:</h3>';
$result = '';

foreach ($avail_images as $img_item => $img_use) {
    if ($img_use >= 2000) {
        $imgurl = $help_directory->url($img_item);
        $result .= "<li><a href=\"$imgurl\">$img_item</a> on page: <a href='../help/$target/index.php?id=" . ($img_use - 2000) . "'>#" . ($img_use - 2000) . '</a></li>';
    }
}

echo '<ol>' . $result . '</ol>';

if ($result == '') {
    echo ' - not found.<br />';
}

echo "<h3>'Unusually' used files:</h3>";
$result = '';

foreach ($avail_images as $img_item => $img_use) {
    if ($img_use >= 1000 && $img_use < 2000) {
        $imgurl = $help_directory->url($img_item);
        $result .= "<li><a href=\"$imgurl\">$img_item</a> on page: <a href='../help/$target/index.php?id=" . ($img_use - 1000) . "'>#" . ($img_use - 1000) . '</a></li>';
    }
}

echo '<ol>' . $result . '</ol>';

if ($result == '') {
    echo ' - not found.<br />';
}

echo '<hr><h2>Help pages ids:</h2>';
$div_num = round(count($help_toc) / 15);
echo '<table><tr><td><ol>';
$i = 0;
$j = 1;

foreach ($help_toc as $help_item) {
    $i++;
    if ($i > ($div_num * $j)) {
        $j++;
        echo '</ol></td><td><ol start=' . $i . '>';
    }
    echo '<li><strong><a href="../help/' . $target . '/index.php?id=' . $help_item['id'] . '">' . $help_item['id'] . '</a></strong></li>';
}

echo '</ol></td></tr></table>';

if (isset($_GET['content']) && $_GET['content'] == 'show') {
    echo '<he>';
    foreach ($help_toc as $help_item) {
        echo '<h3>' . $help_item['title'] . '(' . $help_item['id'] . ')</h3>';
        echo $help_item['body'];
    }
}

$mysqli->close();
?>
</div>
</body>
</html>
