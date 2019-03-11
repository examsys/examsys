<?php
ob_start();
echo "<h2>Combining Images for HTML5 activities</h2>";
$paths = array();
//$paths[] = "tbicons";
$paths[] = "toolbar";
//$paths[] = "toolbar/sizes";

$exclude = array();
//$exclude["popupmenu.png"] = 1;
//$exclude["popup-bottom.png"] = 1;
//$exclude["background.png"] = 1;
//$exclude["tab_hover.png"] = 1;
//$exclude["tab_active.png"] = 1;


function sortimages($a, $b)
{
	if ($a['height'] == $b['height'])
		return 0;
	return ($a['height'] < $b['height']) ? -1 : 1;	
}

$width = 800;

$images = array();

foreach($paths as $path)
{
	$dh = opendir("images/$path");
	while (false !== ($file = readdir($dh)))
	{
		$filename = "images/$path/$file";
		$name = "$path/$file";
		
		if ($file == "." || $file == "..") continue;
		if (array_key_exists($file,$exclude)) continue;
		if (!is_file($filename)) continue;
		
		//echo "Loading image $filename ... ";
		
		ob_flush();
		$res = imagecreatefrompng($filename);
		list($widthx, $height, $type, $attr)= getimagesize($filename); 
		//echo "done<br />";
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

//$width = 500;
$left = 0;
$top = 0;
$rowheight = 0;

foreach($images as &$img)
{
	$right = $left + $img['width'];		
	if ($right > $width)
	{
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

$output = "var menuImages = {\n";

foreach($images as $img)
{
	//imagealphablending( $img['im'], true );
	imagecopy($resim, $img['im'], $img['left'], $img['top'], 0, 0, $img['width'], $img['height']);
	$output .= "\t'{$img['name']}': { left: {$img['left']}, top: {$img['top']}, width: {$img['width']}, height: {$img['height']} },\n";
}
$output .= "'zzz': 'zzz' };\n";

//imagealphablending( $resim, false );

$target = "images/combined.png";
echo "Saving as $target<br />";
ob_flush();
imagepng($resim, $target);

$target = "html5.images.min.js";
echo "Saving js data as $target<br />";
file_put_contents($target, $output);
/*echo "<pre>";
print_r($images);
echo "</pre>";*/
?>