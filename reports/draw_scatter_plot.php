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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';

  $Image = ImageCreate(830, 300);

  $negative = 10;
  $scale_start = 0;
  $mydata = file( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_scatter.dat');
  for ($i=0; $i<=count($mydata); $i=$i+2) {
    if (isset($mydata[$i])) {
      $mark = trim($mydata[$i]);
      if ($mark < 0) {
        $negative = 80;
        $scale_start = -10;
      }
    }
  }
  
  $color		= ImageColorAllocate($Image, 255, 255, 255);
  $red    	= ImageColorAllocate($Image, 192, 0, 0);
  $ltgrey 	= ImageColorAllocate($Image, 234, 234, 234);
  $dkgrey		= ImageColorAllocate($Image, 128, 128, 128);
  $black		= ImageColorAllocate($Image, 0, 0, 0);
  $dkgreen	= ImageColorAllocate($Image, 83, 129, 53);

  $font      = dirname(__dir__) . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'SourceSansPro-Regular.ttf';
  $bold_font = dirname(__dir__)  . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'SourceSansPro-Semibold.ttf';

  ImageLine($Image, 45, 250, 740 + $negative, 250, $dkgrey);
  ImageLine($Image, 45, 190, 740 + $negative, 190, $ltgrey);
  ImageLine($Image, 45, 130, 740 + $negative, 130, $ltgrey);
  ImageLine($Image, 45, 70, 740 + $negative, 70, $ltgrey);

  // Label x axis
  if (!isset($_GET['plotuser'])) {
    
    for ($label=$scale_start; $label<=100; $label+=10) {
      if ($label > 0 and $label < 100) {
        imagettftext($Image, 10, 0, ($label * 7) + 34 + $negative, 270, $black, $font, $label);
      } elseif ($label == 100) {
        imagettftext($Image, 10, 0, ($label * 7) + 29 + $negative, 270, $black, $font, $label);
      } else {
        imagettftext($Image, 10, 0, ($label * 7) + 37 + $negative, 270, $black, $font, $label);
      }
      ImageLine($Image, ($label * 7) + 40 + $negative, 250, ($label * 7) + 40 + $negative, 256, $dkgrey);
      if ($label < 100) ImageLine($Image, ($label * 7) + 75 + $negative, 250, ($label * 7) + 75 + $negative, 253, $dkgrey);
    }
  }

  // Label y axis
  ImageLine($Image, 50, 10, 50, 250, $dkgrey);
	if ($negative == 80) {
		ImageLine($Image, 120, 10, 120, 250, $dkgrey);  // Draw extra line at zero.
	}
  for ($label=10; $label<=250; $label+=10) {
    ImageLine($Image, 44, $label, 50, $label, $dkgrey);
  }
  for ($i=1; $i<=4; $i++) {
    imagettftext($Image, 10, 0, 20, 256-($i*60), $black, $font, $i*60);
  }

  // Plot the data points.
	$mydata = file( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_scatter.dat');
  $count_mydata = count($mydata) - 2;
  for ($i=0; $i<$count_mydata; $i=$i+2) {
    $mark = trim($mydata[$i]);
    $duration = round(intval($mydata[$i + 1]) / 60);
    if ($duration > 0 and $mark >= -10) {
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
    imagettftext($Image, 12, 0, 375 + (abs($scale_start)*5), 286, $black, $bold_font, $string['percent']);
  } else {
    imagettftext($Image, 12, 0, 342 + (abs($scale_start)*5), 286, $black, $bold_font, $string['adjustedpercent']);
  }
  imagettftext($Image, 12, 90, 13, 162, $black, $bold_font, $string['time']);

  ImagePNG($Image);

  ImageDestroy($Image);
?>