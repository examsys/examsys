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
* @author Niko Miranowicz, Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_student_auth.inc';
require_once '../include/errors.php';

$scale        = check_var('scale', 'GET', true, false, true);
$exam         = check_var('exam', 'GET', true, false, true);
$min_mark     = check_var('min', 'GET', true, false, true);
$max_mark     = check_var('max', 'GET', true, false, true);
$student_mark = check_var('mark', 'GET', true, false, true);
$q1           = check_var('q1', 'GET', true, false, true);
$q2           = check_var('q2', 'GET', true, false, true);
$q3           = check_var('q3', 'GET', true, false, true);
$passmark     = check_var('passmark', 'GET', true, false, true);

function find_break($text) {
  $break = 0;
  $txt_len = strlen($text);
  for ($i=25; $i<$txt_len; $i++) {
    if ($text{$i} == ' ' or $text{$i} == '_' or $text{$i} == '-' or $text{$i} == ':' or $text{$i} == ',') {
      if ($break == 0) $break = $i;
    }
  }
  
  return $break;
}

if ($scale == '1') {
  $Image = ImageCreate(166, 265);   // Scale mode
} else {
  $Image = ImageCreate(115, 265);  // Draw box-whisker plot
}

$gap = 24;

$color   = ImageColorAllocate($Image, 255, 255, 255);
$red     = ImageColorAllocate($Image, 192, 0, 0);
$ltgrey  = ImageColorAllocate($Image, 234, 234, 234);
$dkgrey  = ImageColorAllocate($Image, 128, 128, 128);
$black   = ImageColorAllocate($Image, 0, 0, 0);
$blue    = ImageColorAllocate($Image, 91, 155, 213);
$amber   = ImageColorAllocate($Image, 247, 150, 70);
$ltamber = ImageColorAllocate($Image, 251, 198, 155);

$font      = '../fonts/SourceSansPro-Regular.ttf';
$bold_font = '../fonts/SourceSansPro-Semibold.ttf';

if ($scale == '1') {   // Scale mode
  for ($label=1; $label<10; $label++) {
    imagettftext($Image, 10, 0, 25, 255 - ($label * $gap), $black, $font, 10 * $label);
    ImageLine($Image, 45, 250 - ($label * $gap), 50, 250 - ($label * $gap), $dkgrey);
  }
  imagettftext($Image, 10, 0, 20, 15, $black, $font, '100');
  ImageLine($Image, 45, 10, 50, 10, $dkgrey);
  imagettftext($Image, 10, 0, 35, 255, $black, $font, '0');
  ImageLine($Image, 45, 250, 50, 250, $dkgrey);
  
  ImageLine($Image, 50, 10, 50, 257, $dkgrey);
  imagettftext($Image, 12, 90, 12, 132, $black, $bold_font, $string['percent']);
  
  for ($label=0; $label<=10; $label++) {
    ImageLine($Image, 51, 250 - ($label * $gap), 70, 250 - ($label * $gap), $ltgrey);
  }
  $trans1 = 121;
  $margin = 51;  
} else {
  $trans1 = 70;
  $margin = 0;
}

$trans2 = 20;  

if (strlen($exam) > 35) {
  $break = find_break($exam);
  $line1 = trim(substr($exam, 0, $break));
  $line2 = trim(substr($exam, $break));
} else {
  $line1 = '';
  $line2 = $exam;
}

// halflines y axis
for ($label=0; $label<=10; $label++) {
  ImageLine($Image, 0 + $margin, 250 - ($label * $gap), 115 + $margin, 250 - ($label * $gap), $ltgrey);
}		

// x axis
ImageLine($Image, $margin + 0, 250, $margin + 114, 250, $dkgrey);
imagettftext($Image, 10, 90, $margin + 21, 240, $black, $font, $line1);
imagettftext($Image, 10, 90, $margin + 35, 240, $black, $font, $line2);
ImageLine($Image, $margin + 114, 250, $margin + 114, 256, $dkgrey);

//box-and-whiskers
ImageRectangle($Image, $trans1 - $trans2, 250 - (round($q1, 2) * $gap/10) , $trans1 + $trans2, 250 - (round($q3, 2) * $gap/10) , $blue);		
$q2 = floor($q2);
ImageLine($Image, $trans1 - $trans2, 250 - ($q2 * $gap/10)  , $trans1 + $trans2, 250 - ($q2 * $gap/10), $blue);                // Median vertical
ImageLine($Image, $trans1 - $trans2, 249 - ($q2 * $gap/10)  , $trans1 + $trans2, 249 - ($q2 * $gap/10), $blue);                // Median vertical

ImageLine($Image, $trans1 - $trans2, 250 - ($min_mark * $gap/10), $trans1 + $trans2, 250 - ($min_mark * $gap/10) , $blue);                // Min vertical
ImageLine($Image, $trans1, 250 - ($min_mark * $gap/10), $trans1, 250 - (round($q1, 2) * $gap/10), $blue);   // Min whisker		
ImageLine($Image, $trans1 - $trans2, 250 - ($max_mark * $gap/10), $trans1 + $trans2, 250 - ($max_mark * $gap/10) , $blue);                // Max vertical
ImageLine($Image, $trans1, 250 - ($max_mark * $gap/10), $trans1, 250 - (round($q3, 2) * $gap/10), $blue);   // Max whisker

//passmark
$style = array($red, $red, $red, $red, $red, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT);
imagesetstyle($Image, $style);
ImageLine($Image, $trans1-$trans2-7, 250 - ($passmark * $gap/10), $trans1+$trans2+7, 250 - ($passmark * $gap/10), IMG_COLOR_STYLED);

//mark
if ($student_mark !== '') {
  $marksize = 3;
  ImageLine($Image, $trans1-$marksize-1, 250 - ($student_mark * $gap/10) - $marksize, $trans1+$marksize-1, 250 - ($student_mark * $gap/10)+$marksize, $ltamber);
  ImageLine($Image, $trans1-$marksize-1, 250 - ($student_mark * $gap/10) + $marksize, $trans1+$marksize-1, 250 - ($student_mark * $gap/10)-$marksize, $ltamber);
  ImageLine($Image, $trans1-$marksize+1, 250 - ($student_mark * $gap/10) - $marksize, $trans1+$marksize+1, 250 - ($student_mark * $gap/10)+$marksize, $ltamber);
  ImageLine($Image, $trans1-$marksize+1, 250 - ($student_mark * $gap/10) + $marksize, $trans1+$marksize+1, 250 - ($student_mark * $gap/10)-$marksize, $ltamber);
  ImageLine($Image, $trans1-$marksize, 250 - ($student_mark * $gap/10) - $marksize, $trans1+$marksize, 250 - ($student_mark * $gap/10)+$marksize, $amber);
  ImageLine($Image, $trans1+$marksize, 250 - ($student_mark * $gap/10) - $marksize, $trans1-$marksize, 250 - ($student_mark * $gap/10)+$marksize, $amber);

  imagettftext($Image, 10, 0, $margin + 55, 264, $amber, $bold_font, round($student_mark) . '%');
}


ImagePNG($Image);
ImageDestroy($Image);
?>