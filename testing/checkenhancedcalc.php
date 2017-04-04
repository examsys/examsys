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
 * Check enhanced calc setup is OK
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */


$root = str_replace('/include', '/', str_replace('\\', '/', dirname(__FILE__)));
include_once $root . '/../include/load_config.php';
$cfg_web_root = $configObject->get('cfg_web_root');
require_once $cfg_web_root . 'lang/' . $language . '/include/common.php'; // Include common language file that all scripts need
require_once $cfg_web_root . 'include/custom_error_handler.inc';

require '../include/sysadmin_auth.inc';
global $cfg_web_root;
require $cfg_web_root . 'plugins/questions/enhancedcalc/enhancedcalc.class.php';

echo "<html>";
echo "Starting<br><br>";

$enhancedcalcType=$configObject->get('enhancedcalc_type');




$root=$configObject->getbyref('root');
if (!is_null($enhancedcalcType)) {
  require_once $root . 'plugins/questions/enhancedcalc/' .$enhancedcalcType . '.php';
  $name = 'enhancedcalc_' . $enhancedcalcType;
  $enhancedcalcObj1 = new $name($configObject->getbyref('enhancedcalculation'));
} else {
  require_once $root . 'plugins/questions/enhancedcalc/' .'Rrserve.php';
  $enhancedcalcObj1 = new EnhancedCalc_Rrserve($configObject->getbyref('enhancedcalculation'));
}

if($enhancedcalcType=='') {
  $enhancedcalcType='BLANK or MISSING setting that means it defaults to Rserve';
}
$sets=var_export($configObject->getbyRef('enhancedcalculation'),true);
echo "<li>Enhanced Calc is set to <b>$enhancedcalcType</b></li>";
echo "<li>Settings are $sets</li>";

$data=array();
$data[]=array(array('$A' => 2, '$B' => 2),'$A+$B', '4');
$data[]=array(array('$A' => 2, '$B' => 2),'$A*$B', '4');
$data[]=array(array('$A' => 3, '$B' => 3),'$A+$B', '6');
$data[]=array(array('$A' => 3, '$B' => 3),'$A*$B', '9');

$data[]=array(array('$A' => 4, '$B' => 4),'$A+$B', '8');
$data[]=array(array('$A' => 4, '$B' => 4),'$A*$B', '16');

$data[]=array(array('$A' => 8, '$B' => 2),'$A/$B', '4');
$data[]=array(array('$A' => 8, '$B' => 2),'$A-$B', '6');

foreach($data as $individual) {
  $vars=$individual[0];
  $formula=$individual[1];
  $cans=$individual[2];
  try {
$ans=$enhancedcalcObj1->calculate_correct_ans($vars,$formula);
  } catch (Exception $e) {
    $ans = false;
  }
  $check=false;
  $correct=false;
  if(!is_null($cans)) {
    $check=true;
    if($ans === $cans) {
      $correct = true;
    }
  }
  $varlist = 'Where ';
  foreach ($vars as $key => $value) {
    $varlist .= "$key=$value, ";
  }
  if ($ans === false) {
    echo "<li STYLE=\"background: #FF00FF;\">Getting a failed status back from calculation plugin</li>";
  } else {
    if ($check === true) {
      if ($correct == true) {
        //correct
        echo "<li STYLE=\"background: #00FF00;\">$varlist $formula = $ans</li>";
      } else {
        //incorrect
        echo "<li STYLE=\"background: #FF0000;\">$varlist $formula = $ans  Correct Answer Listed as: $cans</li>";
      }

    } else {
      echo "<li>$varlist $formula = $ans</li>";
    }
  }
}
