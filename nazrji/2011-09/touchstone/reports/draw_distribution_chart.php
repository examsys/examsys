<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Draws the distribution bar chart used with class_totals.php.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  $mydata = file("../temp/" . $_SERVER['PHP_AUTH_USER'] . "_distribution.dat");
  $mydata = unserialize($mydata[0]);
  
  $max_frequency = 0;
  $negative = 0;
  $scale_start = 0;
  for ($i=-10; $i<=100; $i++) {
    if (isset($mydata[$i])) {
      if ($mydata[$i] > $max_frequency) {
        $max_frequency = $mydata[$i];
      }
      if ($mydata[$i] > 0 and $i < 0) {
        $negative = 70;
        $scale_start = -10;
      }
    }
  }
  
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

  $color = ImageColorAllocate($Image, 255, 255, 255);
  $red = ImageColorAllocate($Image, 255, 0, 0);
  $ltgrey = ImageColorAllocate($Image, 234, 234, 234);
  $dkgrey = ImageColorAllocate($Image, 128, 128, 128);
  $black = ImageColorAllocate($Image, 0, 0, 0);
  $dkgreen = ImageColorAllocate($Image, 0, 128, 0);
  $blue =  ImageColorAllocate($Image, 0, 192, 192);

  // Label x axis
  if (!isset($_GET['plotuser'])) {
    
    for ($label=$scale_start; $label<=100; $label+=10) {
      if ($label > 0 and $label < 100) {
        ImageString($Image, 2, ($label * 7) + 35 + $negative, 260, $label, $black);
      } elseif ($label == 100) {
        ImageString($Image, 2, ($label * 7) + 29 + $negative, 260, $label, $black);
      } else {
        ImageString($Image, 2, ($label * 7) + 38 + $negative, 260, $label, $black);
      }
      ImageLine($Image, ($label * 7) + 40 + $negative, 250, ($label * 7) + 40 + $negative, 256, $dkgrey);
      if ($label < 100) ImageLine($Image, ($label * 7) + 75 + $negative, 250, ($label * 7) + 75 + $negative, 253, $dkgrey);
    }
  }

  // Label y axis
  for ($label=0; $label<=$points; $label+=$label_inc) {
    ImageLine($Image, 41, 250 - ($label * $gap), 740 + $negative, 250 - ($label * $gap), $ltgrey);
  }

  ImageLine($Image, 40 + $negative, 10, 40 + $negative, 250, $dkgrey);
  ImageLine($Image, 40, 250, 740 + $negative, 250, $dkgrey);

  for ($i=$scale_start; $i<=100; $i++) {
    if (isset($mydata[$i]) and $mydata[$i] > 0) {
      if ($i < $_GET['pmk']) {
        ImageFilledRectangle($Image, ($i * 7) + 38 + $negative, 250 - ($mydata[$i] * $gap), ($i * 7) + 43 + $negative, 250, $red);
      } elseif ($i >= $_GET['pmk'] and $i < $_GET['distinction_mark']) {
        ImageFilledRectangle($Image, ($i * 7) + 38 + $negative, 250 - ($mydata[$i] * $gap), ($i * 7) + 43 + $negative, 250, $black);
      } else {
        ImageFilledRectangle($Image, ($i * 7) + 38 + $negative, 250 - ($mydata[$i] * $gap), ($i * 7) + 43 + $negative, 250, $dkgreen);
      }
    }
  }
  if (isset($_GET['plotuser'])) {
    ImageString($Image, 2, 50, 260, "Worst", $black);
    ImageString($Image, 2, 700, 260, "Best", $black);
    ImageString($Image, 3, 345, 278, "Performance", $black);
  } else {
    if ($_GET['adjust'] == '0') {
      ImageString($Image, 3, 355 + (abs($scale_start)*5), 278, "Percent", $black);
    } else {
      ImageString($Image, 3, 345 + (abs($scale_start)*5), 278, "Adjusted Percent", $black);
    }
  }
  ImageStringUp($Image, 3, 0, 166, "Occurrance", $black);
  
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
      ImageString($Image, 2, 25 + $negative, 244 - ($label * $gap), $label, $black);
    } else {
      ImageString($Image, 2, 20 + $negative, 244 - ($label * $gap), $label, $black);
    }
    ImageLine($Image, 35 + $negative, 250 - ($label * $gap), 40 + $negative, 250 - ($label * $gap), $dkgrey);
  }

  ImagePNG($Image);

  ImageDestroy($Image);
?>