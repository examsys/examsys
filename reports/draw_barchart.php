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

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$total_possible_mark = check_var('tpm', 'GET', true, false, true);
$student_mark        = check_var('mark', 'GET', true, false, true);
$median              = check_var('median', 'GET', true, false, true);

$Image = ImageCreate(300, 65);  
$g_x1 = 4;
$g_y1 = 16;
$g_x2 = 290;
$g_y2 = 60;

$color   = ImageColorAllocate($Image, 255, 255, 255);
$ltgrey  = ImageColorAllocate($Image, 234, 234, 234);
$dkgrey  = ImageColorAllocate($Image, 140, 140, 140);
$black   = ImageColorAllocate($Image, 0, 0, 0);
$amber   = ImageColorAllocate($Image, 247, 150, 70);

$font      = '../fonts/SourceSansPro-Regular.otf';
$bold_font = '../fonts/SourceSansPro-Semibold.otf';

$intervals = array(0, 1, 1, 1, 1, 1, 2, 2, 2, 3, 2, 2, 3, 3, 3, 3, 4, 3, 3, 3, 4);

//small ticks
$gap1 = ($g_x2 - $g_x1)/$total_possible_mark;
for ($label = 0; $label <= $total_possible_mark; $label++) {
	ImageLine($Image, $g_x1+$gap1*$label, $g_y1, $g_x1+$gap1*$label, $g_y1-2, $dkgrey);
}

//large ticks and halflines
$this_interval = 4;
if (count($intervals)>$total_possible_mark) $this_interval=$intervals[$total_possible_mark];
$step = $total_possible_mark/$this_interval;
$gap2 = ($g_x2 - $g_x1)/$step;

for ($label=0; $label<=$step; $label++) {
	$this_x = $g_x1+$gap2*$label;
	$this_label = $label*$this_interval;
		
	if ($total_possible_mark-$this_label<$this_interval) {		
		$this_x = $g_x2;
		$this_label = $total_possible_mark;
	}
	$align = 2;
  if ($this_label > 9) $align = 6;
	imagettftext($Image, 10, 0, $this_x-$align, $g_y1-6, $black, $font, $this_label);
	ImageLine($Image, $this_x, $g_y1, $this_x, $g_y1-4, $dkgrey);
	ImageLine($Image, $this_x, $g_y1+1, $this_x, $g_y2, $ltgrey);
}

//bars
$gap3 = 3;
$gap4 = ($g_y2 - $g_y1 + $gap3)/2;

//student mark bar
$student_mark = round($student_mark, 1);
ImageFilledRectangle($Image, $g_x1, $g_y1 + $gap3, $g_x1 + $gap1 * $student_mark, $g_y1 + $gap4 - $gap3, $amber);		
ImageRectangle($Image, $g_x1, $g_y1 + $gap3, $g_x1 + $gap1 * $student_mark, $g_y1 + $gap4 - $gap3, $black);
if (strlen($student_mark) > 2) {
  imagettftext($Image, 10, 0, $g_x1 + ($gap1 * $student_mark) - 20, $g_y1 + 15, $color, $bold_font, $student_mark);
} elseif ($student_mark > 0) {
  imagettftext($Image, 10, 0, $g_x1 + ($gap1 * $student_mark) - 10, $g_y1 + 15, $color, $bold_font, $student_mark);
}

  //median mark bar
$median = round($median, 1);
ImageFilledRectangle($Image, $g_x1, $g_y1 + $gap4, $g_x1 + $gap1 * $median, $g_y2 - $gap3, $color);		
ImageRectangle($Image, $g_x1, $g_y1 + $gap4, $g_x1 + $gap1 * $median, $g_y2 - $gap3, $black);

if (strlen($median) > 2) {
  imagettftext($Image, 10, 0, $g_x1 + ($gap1 * $median) - 19, $g_y1 + 37, $black, $font, $median);
} elseif ($median > 0) {
  imagettftext($Image, 10, 0, $g_x1 + ($gap1 * $median) - 9, $g_y1 + 37, $black, $font, $median);
}

//axis
ImageLine($Image, $g_x1, $g_y1, $g_x2, $g_y1, $dkgrey);
ImageLine($Image, $g_x1, $g_y1, $g_x1, $g_y2, $dkgrey);

ImagePNG($Image);
ImageDestroy($Image);
?>