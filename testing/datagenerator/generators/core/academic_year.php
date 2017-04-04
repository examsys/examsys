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

namespace testing\datagenerator;

use \encryp,
    \module_utils,
    \Config,
    \yearutils;

/**
 * Generates Rogo academic_year.
 *
 * @author Yijun Xue <yijun.xue@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class academic_year extends generator {

  /**
   * Create academic_year
   * 
   * @param array parameters
   *  string parameters[academic_year] This is academic_year value
   *  string parameters[calendar_year] This is calendar_year
   * @throws Exception If passed parameter is invailid
   */
  public function create_academic_year($parameters) {

    $academicyearpattern = '/[1-9]\d{3,}\/\d{2,}/'; //2016/17
    $calendaryearpattern = '/[1-9]\d{3,}/'; //2016

    if (!(preg_match($academicyearpattern, $parameters['academic_year'])) or ! (preg_match($calendaryearpattern, $parameters['calendar_year']))) {
      throw new data_error('year number format is worng, should be like | 2016 | 2016/17 |');
    } else {

      $academic_year = $parameters['academic_year'];
      $calendar_year = (int) $parameters['calendar_year'];
      $db = loader::get_database();
      $sql = "INSERT INTO academic_year(calendar_year, academic_year) VALUES (?,?)";
      $query = $db->prepare($sql);
      $query->bind_param('is', $calendar_year, $academic_year);

      if (!$query->execute()) {
        throw new data_error("academic_year {$calendar_year} not inserted into database");
      }
    }
  }
}
