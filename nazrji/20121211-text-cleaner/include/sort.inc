<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/
function array_csort($marray, $column, $sort_order) {   //coded by Ichier2003
  $sortarr = array();
  foreach ($marray as $row) {
    $sortarr[] = $row[$column];
  }
  
  $sortarr = array_map('strtolower',$sortarr);
  $sort_method = SORT_STRING;
  if ($column == 'mark' or $column == 'duration') $sort_method = SORT_NUMERIC;
  if ($sort_order == 'asc') {
    array_multisort($sortarr, SORT_ASC, $sort_method, $marray);
  } else {
    array_multisort($sortarr, SORT_DESC, $sort_method, $marray);
  }
  return $marray;
}
?>