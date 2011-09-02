<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

  $mydata = array();
  $mydata = unserialize(urldecode($_GET['disdata']));
  $max_frequency = 0;
  $TotalMarks = 0;
  $TotalStudents = 0;
  for ($i=1; $i<=100; $i++) {
    if ($mydata[$i] > $max_frequency) {
      $max_frequency = $mydata[$i];
    }
    if( $mydata[$i] > 0 ) {
      if(!isset($minMark))
      	$minMark = $i;
      	
      $TotalMarks += $mydata[$i] * $i;
      $TotalStudents += $mydata[$i];
    }
  }
  
  $mean = round($TotalMarks/$TotalStudents,0);
  
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

  $Image = ImageCreate(800, 300);

  $color = ImageColorAllocate($Image, 255, 255, 255);
  $red = ImageColorAllocate($Image, 255, 0, 0);
  $ltgrey = ImageColorAllocate($Image, 234, 234, 234);
  $dkgrey = ImageColorAllocate($Image, 128, 128, 128);
  $black = ImageColorAllocate($Image, 0, 0, 0);
  $dkgreen = ImageColorAllocate($Image, 0, 128, 0);
  $blue =  ImageColorAllocate($Image, 0, 192, 192);


  //caculate Xaxis reduction for anon feedback
  if (isset($_GET['plotuser'])) {
    $reduction_factor = ($minMark * 7) - 40;
  } else {
    $reduction_factor = 0;
  }

  // Label x axis
  if (!isset($_GET['plotuser'])) {
    for ($label=0; $label<=100; $label+=10) {
      if ($label > 0 and $label < 100) {
        ImageString($Image, 2, ($label * 7) + 35, 260, $label, $black);
      } elseif ($label == 100) {
        ImageString($Image, 2, ($label * 7) + 29, 260, $label, $black);
      } else {
        ImageString($Image, 2, ($label * 7) + 38, 260, $label, $black);
      }
      ImageLine($Image, ($label * 7) + 40, 250, ($label * 7) + 40, 256, $dkgrey);
    }
    for ($label=5; $label<=95; $label+=10) {
      ImageLine($Image, ($label * 7) + 40, 250, ($label * 7) + 40, 253, $dkgrey);
    }
  }

  
  // Label y axis
  for ($label=$label_inc; $label<=$points; $label+=$label_inc) {
    if ($label < 10) {
      ImageString($Image, 2, 25, 245 - ($label * $gap), $label, $black);
    } else {
      ImageString($Image, 2, 20, 245 - ($label * $gap), $label, $black);
    }
    ImageLine($Image, 35, 250 - ($label * $gap), 40, 250 - ($label * $gap) , $dkgrey);
    
    ImageLine($Image, 41, 250 - ($label * $gap), 740  - $reduction_factor, 250 - ($label * $gap), $ltgrey);
  }

  ImageLine($Image, 40, 10, 40, 250, $dkgrey);
  
  
  ImageLine($Image, 35, 250, 45, 250, $dkgrey);
  
  
  ImageLine($Image, 45, 250, 50, 255, $dkgrey);
  ImageLine($Image, 50, 255, 60, 245, $dkgrey);
  ImageLine($Image, 60, 245, 65, 250, $dkgrey);
  
  
  ImageLine($Image, 65, 250, 740 - $reduction_factor, 250, $dkgrey);

  for ($i=1; $i<=100; $i++) {
    if ($mydata[$i] > 0) {
      //if ($i < $_GET['pmk']) {
      //  ImageFilledRectangle($Image, ($i * 7) + 38 - $reduction_factor, 250 - ($mydata[$i] * $gap), ($i * 7) + 43 - $reduction_factor, 250, $red);
      //} else {
        ImageFilledRectangle($Image, ($i * 7) + 38 - $reduction_factor, 250 - ($mydata[$i] * $gap), ($i * 7) + 43 - $reduction_factor, 250, $dkgreen);
      //}
    }
  }
  if (isset($_GET['plotuser'])) {
    ImageString($Image, 2, 50, 260, "Worst", $black);
    ImageString($Image, 2, 700 - $reduction_factor, 260, "Best", $black);
    ImageString($Image, 3, 345 - ($reduction_factor / 2), 278 , "Performance", $black);
  } else {
    if ($_GET['rndmk'] == 1) {
      ImageString($Image, 3, 345, 278, "Adjusted Percentage Mark", $black);
    } else {
      ImageString($Image, 3, 355, 278, "Percentage Mark", $black);
    }
  }
  ImageStringUp($Image, 3, 0, 166, "Occurrance", $black);
  
  if ($_GET['plotuser'] != '') {
    if ($label < 100) {
      ImageString($Image, 2, ($_GET['plotuser'] * 7) + 32 - $reduction_factor, 10, "You", $blue);
      ImageString($Image, 2, ($mean * 7) + 32 - $reduction_factor, 0, "Mean", $red);
    } else {
      ImageString($Image, 2, ($_GET['plotuser'] * 7) + 26 - $reduction_factor, 10, "You", $blue);
      ImageString($Image, 2, ($mean * 7) + 26 - $reduction_factor, 0, "Mean", $red);
    }
    ImageLine($Image, ($_GET['plotuser'] * 7) + 40 - $reduction_factor, 22, ($_GET['plotuser'] * 7) + 40 - $reduction_factor, 250, $blue);
    ImageLine($Image, ($mean * 7) + 40 - $reduction_factor, 12, ($mean * 7) + 40 - $reduction_factor, 250, $red);
  }

  ImagePNG($Image);

  ImageDestroy($Image);
?>