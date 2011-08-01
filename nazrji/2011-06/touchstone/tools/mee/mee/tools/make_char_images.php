<?php

echo "Making Images<br>";

$toconvert = array();
//$toconvert['&#x221A;'] = 'MathJax_Main-Regular';
$toconvert['&#x2211;'] = 'MathJax_Size2-Regular';
$toconvert['D'] = 'MathJax_Main-Regular';
$toconvert['I'] = 'MathJax_Main-Regular';

foreach ( $toconvert as $char => $font )
{
	echo "Converting <span style='font-family:$font;font-size:300%;'>$char</span><br>";
	
	$im = imagecreatetruecolor(1000,1000);
	$white = imagecolorallocate($im, 255, 255, 255);
	imagefilledrectangle($im, 0, 0, 1000, 1000, $white);
	$black = imagecolorallocate($im, 0, 0, 0);
	imagecolortransparent($im, $white);
	$font = 'fonts/' . $font . '.ttf';
	echo $font ."<br>";
	imagettftext($im, 400, 0, 200, 700, $black, $font, $char);
	imagepng($im,$char.".png");
}