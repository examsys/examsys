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

if ($_GET['part'] == '0') {
  $Image = ImageCreate(51, 265);   // Scale mode
} else {
  $Image = ImageCreate(115, 265);  // Draw box-whisker plot
}

$gap = 24;

$color   = ImageColorAllocate($Image, 255, 255, 255);
$red     = ImageColorAllocate($Image, 255, 0, 0);
$ltgrey  = ImageColorAllocate($Image, 234, 234, 234);
$dkgrey  = ImageColorAllocate($Image, 128, 128, 128);
$black   = ImageColorAllocate($Image, 0, 0, 0);
$blue    = ImageColorAllocate($Image, 0, 192, 192);
$amber   = ImageColorAllocate($Image, 247, 150, 70);
$ltamber = ImageColorAllocate($Image, 251, 198, 155);

$font      = '../fonts/SourceSansPro-Regular.otf';
$bold_font = '../fonts/SourceSansPro-Semibold.otf';

if ($_GET['part'] == '0') {   // Scale mode
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
} else {                    // Draw box-whisker plot
  $min_mark     = $_GET['min'];
  $max_mark     = $_GET['max'];
  $student_mark = $_GET['mark'];
  $q1           = $_GET['q1'];
  $q2           = $_GET['q2'];
  $q3           = $_GET['q3'];
  $passmark     = $_GET['passmark'];
  $exam         = $_GET['exam'];
  
  $trans1 = 70;
  $trans2 = 20;  

  if (strlen($exam) > 35) {
    $break = find_break($exam);
    $line1 = mb_convert_encoding(trim(substr($exam, 0, $break)), 'UTF-8','ISO-8859-2');
    $line2 = mb_convert_encoding(trim(substr($exam, $break)), 'UTF-8','ISO-8859-2');
  } else {
    $line1 = '';
    $line2 = mb_convert_encoding($exam, 'UTF-8','ISO-8859-2');
  }
  
  // halflines y axis
  for ($label=0; $label<=10; $label++) {
    ImageLine($Image, 0, 250 - ($label * $gap), 115, 250 - ($label * $gap), $ltgrey);
  }		
  
  // x axis
  ImageLine($Image, 0, 250, 114, 250, $dkgrey);
  imagettftext($Image, 10, 90, 21, 240, $black, $font, $line1);
  imagettftext($Image, 10, 90, 35, 240, $black, $font, $line2);
  ImageLine($Image, 114, 250, 114, 256, $dkgrey);
  
  //box-and-whiskers
  ImageRectangle($Image, $trans1 - $trans2, 250 - (round($q1, 2) * $gap/10) , $trans1 + $trans2, 250 - (round($q3, 2) * $gap/10) , $blue);		
  ImageLine($Image, $trans1 - $trans2, 250 - (round($q2, 2) * $gap/10)  , $trans1 + $trans2, 250 - (round($q2, 2) * $gap/10), $blue);                // Median vertical

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
  }
}

ImagePNG($Image);
ImageDestroy($Image);
?>