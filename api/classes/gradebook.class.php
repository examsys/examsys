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
* Gradebook api functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Gradebook class
 */
class gradebook extends \api\abstractpublish {
    
    // Language pack component.
    private $langcomponent = 'api/gradebook';
       
    /**
     * @brief Get data.
     * @param string $filtername 
     * @param integer $filterid
     * @return array
     */
    public function get($filtername, $filterid) {
        $langpack = new \langpack();
        $gradebook = new \gradebook($this->db);
        switch ($filtername) {
            case \gradebook::PAPER:
            case \gradebook::EXTPAPER:
                $grades = $gradebook->get_paper_gradebook($filtername, $filterid);
                break;
            case \gradebook::MODULE:
            case \gradebook::EXTMODULE:
                $grades = $gradebook->get_module_gradebook($filtername, $filterid);
                break;
            default:
                $grades = false;
                break;
        }
        if ($grades) {
            return array('OK', $grades);
        } else {
            return array('BAD', array(sprintf($langpack->get_string($this->langcomponent, 'notfound'), $filtername, $filterid)));
        }
    }
    
}