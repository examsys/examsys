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
* Draws the scatter plot used with class_totals.php
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';

  $Image = ImageCreate(830, 300);

  $negative = 0;
  $scale_start = 0;
  $mydata = file($cfg_tmpdir . $userObject->get_user_ID() . '_scatter.dat');
  for ($i=0; $i<=count($mydata); $i=$i+2) {
    if (isset($mydata[$i])) {
      $mark = trim($mydata[$i]);
      if ($mark < 0) {
        $negative = 70;
        $scale_start = -10;
      }
    }
  }
  
  $color = ImageColorAllocate($Image, 255, 255, 255);
  $red = ImageColorAllocate($Image, 255, 0, 0);
  $ltgrey = ImageColorAllocate($Image, 234, 234, 234);
  $dkgrey = ImageColorAllocate($Image, 128, 128, 128);
  $black = ImageColorAllocate($Image, 0, 0, 0);
  $dkgreen = ImageColorAllocate($Image, 0, 128, 0);

  ImageLine($Image, 40 + $negative, 10, 40 + $negative, 250, $dkgrey);
  ImageLine($Image, 40, 250, 740 + $negative, 250, $dkgrey);
  ImageLine($Image, 41, 190, 740 + $negative, 190, $ltgrey);
  ImageLine($Image, 41, 130, 740 + $negative, 130, $ltgrey);
  ImageLine($Image, 41, 70, 740 + $negative, 70, $ltgrey);

  // Convert strings from UTF8 to Latin
  $string['time'] = mb_convert_encoding($string['time'], 'ISO-8859-2', 'UTF-8');
  $string['percent'] = mb_convert_encoding($string['percent'], 'ISO-8859-2', 'UTF-8');
  $string['adjustedpercent'] = mb_convert_encoding($string['adjustedpercent'], 'ISO-8859-2', 'UTF-8');

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
  for ($label=10; $label<=250; $label+=10) {
    ImageLine($Image, 34 + $negative, $label, 40 + $negative, $label, $dkgrey);
  }
  for ($i=1; $i<=4; $i++) {
    ImageString($Image, 2, 14 + $negative, 243-($i*60), $i*60, $black);
  }

  $mydata = file($cfg_tmpdir . $userObject->get_user_ID() . '_scatter.dat');
  $count_mydata = count($mydata) - 2;
  for ($i=0; $i<$count_mydata; $i=$i+2) {
    $mark = trim($mydata[$i]);
    if ($mark >= -10) {
      $duration = round($mydata[$i + 1] / 60);
      if ($mark < $_GET['pmk']) {
        ImageFilledRectangle($Image, ($mark * 7) + 40 + $negative, 249 - $duration, ($mark * 7) + 41 + $negative, 250 - $duration, $red);
      } elseif ($mark >= $_GET['pmk'] and $mark < $_GET['distinction_mark']) {
        ImageFilledRectangle($Image, ($mark * 7) + 40 + $negative, 249 - $duration, ($mark * 7) + 41 + $negative, 250 - $duration, $black);
      } else {
        ImageFilledRectangle($Image, ($mark * 7) + 40 + $negative, 249 - $duration, ($mark * 7) + 41 + $negative, 250 - $duration, $dkgreen);
      }
    }
  }

  if ($_GET['adjust'] == '0') {
    ImageString($Image, 3, 355 + (abs($scale_start)*5), 278, $string['percent'], $black);
  } else {
    ImageString($Image, 3, 345 + (abs($scale_start)*5), 278, $string['adjustedpercent'], $black);
  }
  ImageStringUp($Image, 3, 0, 166, $string['time'], $black);

  ImagePNG($Image);

  ImageDestroy($Image);
?>