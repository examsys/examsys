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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';

  $mydata = file( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_distribution.dat');
  $mydata = unserialize($mydata[0]);
  
  $max_frequency = 0;
  $negative = 10;
  $scale_start = 0;
  $min_mark = 100;
  $max_mark = 0;
  for ($i=-100; $i<=100; $i++) {
    if (isset($mydata[$i])) {
      if ($mydata[$i] > 0) {
        if ($i > $max_mark) $max_mark = $i;
        if ($i < $min_mark) $min_mark = $i;
      }
      if ($mydata[$i] > $max_frequency) {
        $max_frequency = $mydata[$i];
      }
      if ($mydata[$i] > 0 and $i < 0) {
        $negative = 80;
        $scale_start = -10;
      }
    }
  }
 	if ($min_mark<-10) $min_mark=-10;

  // Calculate y axis scaling.
  if ($max_frequency <= 10) {
    $gap = 24;
    $points = 10;
    $label_inc = 1;
  } elseif ($max_frequency > 10 and $max_frequency <= 20) {
    $gap = 12;
    $points = 20;
    $label_inc = 2;
  } elseif ($max_frequency > 20 and $max_frequency <= 30) {
    $gap = 8;
    $points = 30;
    $label_inc = 2;
  } elseif ($max_frequency > 30 and $max_frequency <= 40) {
    $gap = 6;
    $points = 40;
    $label_inc = 5;
  } elseif ($max_frequency > 40 and $max_frequency <= 50) {
    $gap = 4.25;
    $points = 50;
    $label_inc = 5;
  } elseif ($max_frequency > 50 and $max_frequency <= 60) {
    $gap = 3;
    $points = 60;
    $label_inc = 10;
  } elseif ($max_frequency > 60 and $max_frequency <= 70) {
    $gap = 3;
    $points = 70;
    $label_inc = 10;
  } elseif ($max_frequency > 70 and $max_frequency <= 80) {
    $gap = 3;
    $points = 80;
    $label_inc = 10;
  } elseif ($max_frequency > 80 and $max_frequency <= 90) {
    $gap = 2.5;
    $points = 90;
    $label_inc = 10;
  } else {
    $gap = 2;
    $points = 100;
    $label_inc = 10;
  } 

  $Image = ImageCreate(830, 300);

  $color   = ImageColorAllocate($Image, 255, 255, 255);
  $red     = ImageColorAllocate($Image, 192, 0, 0);
  $ltgrey  = ImageColorAllocate($Image, 234, 234, 234);
  $dkgrey  = ImageColorAllocate($Image, 128, 128, 128);
  $black   = ImageColorAllocate($Image, 0, 0, 0);
  $dkgreen = ImageColorAllocate($Image, 83, 129, 53);
  $blue    = ImageColorAllocate($Image, 91, 155, 213);
  
  $font      = dirname(__dir__) . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'SourceSansPro-Regular.ttf';
  $bold_font = dirname(__dir__)  . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'SourceSansPro-Semibold.ttf';

  // Label x axis
  if (!isset($_GET['plotuser'])) {
    for ($label=$scale_start; $label<=100; $label+=10) {
      if ($label > 0 and $label < 100) {
        imagettftext($Image, 10, 0, ($label * 7) + 34 + $negative, 280, $black, $font, $label);
      } elseif ($label == 100) {
        imagettftext($Image, 10, 0, ($label * 7) + 29 + $negative, 280, $black, $font, $label);
      } else {
        imagettftext($Image, 10, 0, ($label * 7) + 37 + $negative, 280, $black, $font, $label);
      }
      ImageLine($Image, ($label * 7) + 40 + $negative, 260, ($label * 7) + 40 + $negative, 266, $dkgrey);
      if ($label < 100) ImageLine($Image, ($label * 7) + 75 + $negative, 260, ($label * 7) + 75 + $negative, 263, $dkgrey);
    }
  }

  // Label y axis
  for ($label=0; $label<=$points; $label+=$label_inc) {
    ImageLine($Image, 41, 260 - ($label * $gap), 740 + $negative, 260 - ($label * $gap), $ltgrey);
  }

  if ($negative > 10) {
    ImageLine($Image, 40 + $negative, 20, 40 + $negative, 260, $dkgrey);
  }
  ImageLine($Image, 50, 20, 50, 260, $dkgrey);
  ImageLine($Image, 50, 260, 740 + $negative, 260, $dkgrey);

  // Add quartile lines
  if (isset($_GET['q1']) and isset($_GET['q2']) and isset($_GET['q3'])) {
    for ($i=1; $i<=3; $i++) {
      $quartile = round($_GET["q$i"], 2);
      imagedashedline($Image, ($quartile * 7) + 40 + $negative, 20, ($quartile * 7) + 40 + $negative, 260, $blue);
    }
  }
  imagedashedline($Image, ($min_mark * 7) + 38 + $negative, 20, ($min_mark * 7) + 38 + $negative, 260, $blue);
  imagedashedline($Image, ($max_mark * 7) + 43 + $negative, 20, ($max_mark * 7) + 43 + $negative, 260, $blue);
  ImageRectangle($Image, (round($_GET["q1"], 2) * 7) + 40 + $negative, 1, (round($_GET["q3"], 2) * 7) + 40 + $negative, 13, $blue);
  
  ImageLine($Image, (round($_GET["q2"], 2) * 7) + 40 + $negative, 1, (round($_GET["q2"], 2) * 7) + 40 + $negative, 12, $blue);                // Median vertical

  ImageLine($Image, ($min_mark * 7) + 38 + $negative, 1, ($min_mark * 7) + 38 + $negative, 13, $blue);                // Min vertical
  ImageLine($Image, ($min_mark * 7) + 38 + $negative, 7, (round($_GET["q1"], 2) * 7) + 40 + $negative, 7, $blue);   // Min whisker
  
  ImageLine($Image, ($max_mark * 7) + 43 + $negative, 1, ($max_mark * 7) + 43 + $negative, 13, $blue);                // Max vertical
  ImageLine($Image, ($max_mark * 7) + 43 + $negative, 7, (round($_GET["q3"], 2) * 7) + 40 + $negative, 7, $blue);   // Max whisker

  for ($i=$scale_start; $i<=100; $i++) {
    if (isset($mydata[$i]) and $mydata[$i] > 0) {
      if ($i < $_GET['pmk']) {
        ImageFilledRectangle($Image, ($i * 7) + 38 + $negative, 260 - ($mydata[$i] * $gap), ($i * 7) + 43 + $negative, 260, $red);
      } elseif ($i >= $_GET['pmk'] and $i < $_GET['distinction_mark']) {
        ImageFilledRectangle($Image, ($i * 7) + 38 + $negative, 260 - ($mydata[$i] * $gap), ($i * 7) + 43 + $negative, 260, $black);
      } else {
        ImageFilledRectangle($Image, ($i * 7) + 38 + $negative, 260 - ($mydata[$i] * $gap), ($i * 7) + 43 + $negative, 260, $dkgreen);
      }
    }
  }
  if (isset($_GET['plotuser'])) {
    ImageString($Image, 2, 50, 260, "Worst", $black);
    ImageString($Image, 2, 700, 260, "Best", $black);
    ImageString($Image, 3, 345, 278, "Performance", $black);
  } else {
    if ($_GET['adjust'] == '0') {
      imagettftext($Image, 12, 0, 375 + (abs($scale_start)*5), 296, $black, $bold_font, $string['percent']);
    } else {
      imagettftext($Image, 12, 0, 342 + (abs($scale_start)*5), 296, $black, $bold_font, $string['adjustedpercent']);
    }
  }
  imagettftext($Image, 12, 90, 12, 182, $black, $bold_font, $string['occurrance']);
  
  if (isset($_GET['plotuser']) and $_GET['plotuser'] != '') {
    if ($label < 100) {
      ImageString($Image, 2, ($_GET['plotuser'] * 7) + 32, 0, "You", $blue);
    } else {
      ImageString($Image, 2, ($_GET['plotuser'] * 7) + 26, 0, "You", $blue);
    }
    ImageLine($Image, ($_GET['plotuser'] * 7) + 40, 12, ($_GET['plotuser'] * 7) + 40, 250, $blue);
  }

  // Label y axis
  for ($label=0; $label<=$points; $label+=$label_inc) {
    if ($label < 10) {
      imagettftext($Image, 10, 0, 35, 265 - ($label * $gap), $black, $font, $label);
    } else {
      imagettftext($Image, 10, 0, 30, 265 - ($label * $gap), $black, $font, $label);
    }
    ImageLine($Image, 45, 260 - ($label * $gap), 50, 260 - ($label * $gap), $dkgrey);
  }

  ImagePNG($Image);

  ImageDestroy($Image);
?>