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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

  function fullQuestionType($abreviation, $string) {
    switch ($abreviation) {
      case 'area':
        $fullname = $string['area'];
        break;
      case 'blank':
        $fullname = $string['blank'];
        break;
      case 'branching':
        $fullname = $string['branching'];
        break;
      case 'enhancedcalc':
        $fullname = $string['calculation'];
        break;
      case 'dichotomous':
        $fullname = $string['dichotomous'];
        break;
      case 'extmatch':
        $fullname = $string['extmatch'];
        break;
      case 'flash':
        $fullname = $string['flash'];
        break;
      case 'hotspot':
        $fullname = $string['hotspot'];
        break;
      case 'info':
        $fullname = $string['info'];
        break;
      case 'keyword_based':
        $fullname = $string['keyword_based_short'];
        break;
      case 'labelling':
        $fullname = $string['labelling'];
        break;
      case 'likert':
        $fullname = $string['likert'];
        break;
      case 'matrix':
        $fullname = $string['matrix'];
        break;
      case 'mcq':
        $fullname = $string['mcq'];
        break;
      case 'mrq':
        $fullname = $string['mrq'];
        break;
      case 'rank':
        $fullname = $string['rank'];
        break;
      case 'random':
        $fullname = $string['random_short'];
        break;
      case 'sct';
        $fullname = $string['sct_short'];
        break;
      case 'textbox':
        $fullname = $string['textbox'];
        break;
      case 'true_false':
        $fullname = $string['true_false'];
        break;
      case '%':
        $fullname = $string['alltypes'];
        break;
    }
    return $fullname;
  }
?>