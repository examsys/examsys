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
* Draws the distribution bar chart used with class_totals.php.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../classes/mathsutils.class.php';

  $mydata = file( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_distribution.dat');
  $mydata = unserialize($mydata[0]);
  
	$gap = 24;
  $points = 10;
  $label_inc = 1;
		
  $max_frequency = 0;
  $negative = 10;
  $scale_start = 0;
  $min_mark = 100;
  $max_mark = 0;
  for ($i=-10; $i<=100; $i++) {
    if (isset($mydata[$i])) {
      if ($mydata[$i] > 0) {
        if ($i > $max_mark) $max_mark = $i;
        if ($i < $min_mark) $min_mark = $i;
      }
      if ($mydata[$i] > $max_frequency) {
        $max_frequency = $mydata[$i];
      }
      if ($mydata[$i] > 0 and $i < 0) {
        $negative = 70;
        $scale_start = -10;
      }
    }
  }
  $img_width = 100;
  if ($_GET["part"]=='0') $img_width = 51;
  $Image = ImageCreate($img_width, 265);
  

  $color   = ImageColorAllocate($Image, 255, 255, 255);
  $red     = ImageColorAllocate($Image, 255, 0, 0);
	$dkred  = ImageColorAllocate($Image, 200, 0, 0);
  $ltgrey  = ImageColorAllocate($Image, 234, 234, 234);
  $dkgrey  = ImageColorAllocate($Image, 128, 128, 128);
  $black   = ImageColorAllocate($Image, 0, 0, 0);
  $dkgreen = ImageColorAllocate($Image, 0, 128, 0);
  $blue    = ImageColorAllocate($Image, 0, 192, 192);
  $bblue    = ImageColorAllocate($Image, 0, 0, 255);
	$white   = ImageColorAllocate($Image, 255, 255, 255);
	$amber   = ImageColorAllocate($Image, 247, 150, 70);
	$ltamber   = ImageColorAllocate($Image, 251, 198, 155);
	//$ltamber   = ImageColorAllocate($Image, 252, 221, 196);
  $font      = '../fonts/SourceSansPro-Regular.otf';
  $bold_font = '../fonts/SourceSansPro-Semibold.otf';
  
  // Convert strings from UTF8 to Latin
  $string['occurrance'] = mb_convert_encoding($string['occurrance'], 'ISO-8859-2', 'UTF-8');
  $string['percent'] = mb_convert_encoding($string['percent'], 'UTF-8','ISO-8859-2');
  $string['adjustedpercent'] = mb_convert_encoding($string['adjustedpercent'], 'ISO-8859-2', 'UTF-8');
  $exam = '';if (isset($_GET['exam'])) $exam = mb_convert_encoding($_GET['exam'], 'UTF-8','ISO-8859-2');
  
	$trans1 = 56;
	$trans2 = 20;  

  // Add quartile lines
	if ($_GET["part"]=='1') {
	  // halflines y axis
    for ($label=0; $label<=10; $label++) {
			ImageLine($Image, 0, 260 - ($label * $gap), 100, 260 - ($label * $gap), $ltgrey);
		}		
		
		//box-and-whiskers
    //ImageLine($Image, $trans1-$trans2-2, 260 - ($_GET["passmark"] * $gap/10), $trans1+$trans2+2, 260 - ($_GET["passmark"] * $gap/10), $dkred);  //passmark

		ImageRectangle($Image, $trans1 - $trans2, 260 - (round($_GET["q1"], 2) * $gap/10) , $trans1 + $trans2, 260 - (round($_GET["q3"], 2) * $gap/10) , $blue);		
		ImageLine($Image, $trans1 - $trans2, 260 - (round($_GET["q2"], 2) * $gap/10)  , $trans1 + $trans2, 260 - (round($_GET["q2"], 2) * $gap/10), $blue);                // Median vertical

		ImageLine($Image, $trans1 - $trans2, 260 - ($min_mark * $gap/10), $trans1 + $trans2, 260 - ($min_mark * $gap/10) , $blue);                // Min vertical
		ImageLine($Image, $trans1, 260 - ($min_mark * $gap/10), $trans1, 260 - (round($_GET["q1"], 2) * $gap/10), $blue);   // Min whisker		
		ImageLine($Image, $trans1 - $trans2, 260 - ($max_mark * $gap/10), $trans1 + $trans2, 260 - ($max_mark * $gap/10) , $blue);                // Max vertical
		ImageLine($Image, $trans1, 260 - ($max_mark * $gap/10), $trans1, 260 - (round($_GET["q3"], 2) * $gap/10), $blue);   // Max whisker

		// x axis
    ImageLine($Image, 0, 260, 100, 260, $dkgrey);
    imagettftext($Image, 12, 90, 25, 250, $black, $font, $exam);
		
		//passmark
		//ImageLine($Image, $trans1-$trans2-2, 260 - ($_GET["passmark"] * $gap/10), $trans1-$trans2+4, 260 - ($_GET["passmark"] * $gap/10), $dkred);
		//ImageLine($Image, $trans1+$trans2-4, 260 - ($_GET["passmark"] * $gap/10), $trans1+$trans2+2, 260 - ($_GET["passmark"] * $gap/10), $dkred);
		$style = array($red, $red, $red, $red, $red, IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT );
		$style2 = array($red, IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT );
		//$style = array($dkred, $dkred, $dkred, $dkred, $dkred, IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT , IMG_COLOR_TRANSPARENT );
		imagesetstyle($Image, $style);
    ImageLine($Image, $trans1-$trans2-7, 260 - ($_GET["passmark"] * $gap/10), $trans1+$trans2+7, 260 - ($_GET["passmark"] * $gap/10), IMG_COLOR_STYLED);
		
		//mark
		$marksize = 3;
  	ImageLine($Image, $trans1-$marksize-1, 260 - ($_GET["mark"] * $gap/10) - $marksize, $trans1+$marksize-1, 260 - ($_GET["mark"] * $gap/10)+$marksize, $ltamber);
  	ImageLine($Image, $trans1-$marksize-1, 260 - ($_GET["mark"] * $gap/10) + $marksize, $trans1+$marksize-1, 260 - ($_GET["mark"] * $gap/10)-$marksize, $ltamber);
  	ImageLine($Image, $trans1-$marksize+1, 260 - ($_GET["mark"] * $gap/10) - $marksize, $trans1+$marksize+1, 260 - ($_GET["mark"] * $gap/10)+$marksize, $ltamber);
  	ImageLine($Image, $trans1-$marksize+1, 260 - ($_GET["mark"] * $gap/10) + $marksize, $trans1+$marksize+1, 260 - ($_GET["mark"] * $gap/10)-$marksize, $ltamber);
  	ImageLine($Image, $trans1-$marksize, 260 - ($_GET["mark"] * $gap/10) - $marksize, $trans1+$marksize, 260 - ($_GET["mark"] * $gap/10)+$marksize, $amber);
  	ImageLine($Image, $trans1+$marksize, 260 - ($_GET["mark"] * $gap/10) - $marksize, $trans1-$marksize, 260 - ($_GET["mark"] * $gap/10)+$marksize, $amber);
	}
	
	// Label y axis
  if ($_GET["part"]=='0') {
		for ($label=1; $label<10; $label++) {
			imagettftext($Image, 10, 0, 25, 265 - ($label * $gap), $black, $font, 10 * $label);
  	  ImageLine($Image, 45, 260 - ($label * $gap), 50, 260 - ($label * $gap), $dkgrey);
		}
		imagettftext($Image, 10, 0, 20, 25, $black, $font, '100');
		ImageLine($Image, 45, 20, 50, 20, $dkgrey);
		imagettftext($Image, 10, 0, 35, 265, $black, $font, '0');
		ImageLine($Image, 45, 260, 50, 260, $dkgrey);
		
		ImageLine($Image, 50, 20, 50, 260, $dkgrey);
    imagettftext($Image, 12, 90, 12, 182, $black, $bold_font, $string['percent']);
	}
	
  ImagePNG($Image);
  ImageDestroy($Image);
?>