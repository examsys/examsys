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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  function fullQuestionType($abreviation) {
    switch ($abreviation) {
      case 'blank':
        $fullname = 'Fill-in-the-Blank';
        break;
      case 'branching':
        $fullname = 'Branching';
        break;
      case 'calculation':
        $fullname = 'Calculation';
        break;
      case 'dichotomous':
        $fullname = 'Dichotomous';
        break;
      case 'extmatch':
        $fullname = 'Extended Matching';
        break;
      case 'flash':
        $fullname = 'Flash&nbsp;Interface';
        break;
      case 'hotspot':
        $fullname = 'Image Hotspot';
        break;
      case 'info':
        $fullname = 'Information Block';
        break;
      case 'keyword_based':
        $fullname = 'Keyword-based';
        break;
      case 'labelling':
        $fullname = 'Labelling';
        break;
      case 'likert':
        $fullname = 'Likert Scale';
        break;
      case 'matrix':
        $fullname = 'Matrix';
        break;
      case 'mcq':
        $fullname = 'Multiple Choice';
        break;
      case 'mrq':
        $fullname = 'Multiple Response';
        break;
      case 'rank':
        $fullname = 'Ranking';
        break;
      case 'random':
        $fullname = 'Random';
        break;
      case 'sct';
        $fullname = 'Script Concordance';
        break;
      case 'textbox':
        $fullname = 'Text Box';
        break;
      case 'timedate':
        $fullname = 'Time/Date';
        break;
      case '%':
        $fullname = 'All Types';
        break;
    }
    return $fullname;
  }
?>