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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../lang/' . $language . '/paper/details.php';
require '../lang/' . $language . '/include/dynamic_paper_options.inc.php';
require '../lang/' . $language . '/include/dynamic_papers.inc.php';

$string['dynamicpapers'] = 'Dynamic Papers';
$string['start'] = 'Start';
$string['owner'] = 'Owner';
$string['question'] = 'Question';
$string['objectives'] = 'Objectives';
$string['type'] = 'Type';
$string['marks'] = 'Marks';
$string['modified'] = 'Modified';
$string['passmark'] = 'Pass Mark';
$string['mappingbysession'] = 'Mapping by Session';
$string['bysession'] = 'by Session';
$string['byquestion'] = 'by Question';
$string['longitudinal'] = 'Longitudinal';
$string['warning'] = 'Warning';
$string['nomatchsession'] = 'The session in the paper title (%s) does not match the paper session (%s).';
$string['mustselectobjectives'] = 'Plese select objectives to which the questions will be mapped';
$string['ajaxerror'] = 'There was a problem carrying out your action. Please refresh the page and try again';
$string['ajaxconfirm'] = 'Are you sure?';
$string['nosessions'] = 'No sessions mapped for module in %s.';
?>