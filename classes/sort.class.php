<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Sort helper class
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 */
class sort
{
    /**
     * Sort a multi-dimensional array
     * @param $marray array to sort
     * @param string $sort_by which column to sort by
     * @param string $sort_order which order to sort by asc/desc
     * @param int $sort_method sorting method
     * @return array
     */
    public static function array_csort($marray, $sort_by, $sort_order, $sort_method = SORT_STRING)
    {
        $sortarr = array();
        foreach ($marray as $row) {
            $sortarr[] = $row[$sort_by];
        }

        $sortarr = array_map('strtolower', $sortarr);
        if ($sort_order == 'asc') {
            array_multisort($sortarr, SORT_ASC, $sort_method, $marray);
        } else {
            array_multisort($sortarr, SORT_DESC, $sort_method, $marray);
        }
        return $marray;
    }
}
