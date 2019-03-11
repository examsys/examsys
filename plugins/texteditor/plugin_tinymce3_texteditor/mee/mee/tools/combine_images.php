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
* @author Adam Clarke
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../../../include/sysadmin_auth.inc';

ob_start();
echo "<h2>Combining Images</h2>";

$paths = array();
$paths[] = "tbicons";
$paths[] = "toolbar";
$paths[] = "toolbar/sizes";

$exclude = array();
$exclude["popupmenu.png"] = 1;
$exclude["popup-bottom.png"] = 1;
$exclude["background.png"] = 1;
$exclude["tab_hover.png"] = 1;
$exclude["tab_active.png"] = 1;


function sortimages($a, $b) {
	if ($a['height'] == $b['height'])
		return 0;
	return ($a['height'] < $b['height']) ? -1 : 1;	
}

$width = 800;

$images = array();

foreach($paths as $path) {
	$dh = opendir("../images/$path");
  
	while (false !== ($file = readdir($dh))) 	{
		$filename = "../images/$path/$file";
		$name = "$path/$file";
		
		if ($file == "." || $file == "..") continue;
		if (array_key_exists($file, $exclude)) continue;
		if (!is_file($filename)) continue;
		
		ob_flush();
		$res = imagecreatefrompng($filename);
		list($widthx, $height, $type, $attr)= getimagesize($filename); 
		ob_flush();
		
		$img = array();
		$img['im'] = $res;
		$img['width'] = $widthx;
		$img['height'] = $height;
		$img['filename'] = $filename;
		$img['name'] = $name;
		
		$images[] = $img;
	}
}

usort($images, "sortimages");

$left = 0;
$top = 0;
$rowheight = 0;

foreach ($images as &$img) {
	$right = $left + $img['width'];		
	if ($right > $width) {
		$left = 0;
		$top += $rowheight;	
		$rowheight = 0;
	}
	
	$rowheight = max($rowheight, $img['height']);
	$img['left'] = $left;
	$img['top'] = $top;
	
	$left += $img['width'];
}
unset($img);

$totalheight = $top + $rowheight;

$resim = imagecreatetruecolor($width, $totalheight);
imagealphablending( $resim, false );
imagesavealpha( $resim, true );
$transparent = imagecolorallocatealpha($resim, 255,255,255, 127);
imagefilledrectangle($resim, 0, 0, $width, $totalheight, $transparent);

$output = "MEE.Data.images = {\n";

foreach ($images as $img) {
	//imagealphablending( $img['im'], true );
	imagecopy($resim, $img['im'], $img['left'], $img['top'], 0, 0, $img['width'], $img['height']);
	$output .= "\t'{$img['name']}': { left: {$img['left']}, top: {$img['top']}, width: {$img['width']}, height: {$img['height']} },\n";
}
$output .= "'zzz': 'zzz' };\n";

$target = "../images/combined.png";
echo "Saving as $target<br />";
ob_flush();
imagepng($resim, $target);

$target = "../js/mee.images.js";
echo "Saving js data as $target<br />";
file_put_contents($target, $output);
?>