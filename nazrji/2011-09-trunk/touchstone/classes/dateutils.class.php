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
* Utility class for date related functionality
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

Class DateUtils {
	// Start of academic year (mm/dd)
	public static $academic_year_start = '07/01';
	
	/**
	 * Get the current academic year in the format 'yyyy/yy', e.g. '2010/11'
	 * @return string
	 */
	static function get_current_academic_year()	{
		return DateUtils::get_academic_year(date('Y/m/d'));
	}
	
	/**
	 * Get the academic year for the given date in the format 'yyyy/yy', e.g. '2010/11'
	 * @param string $date A date in a format that can be accepted by strtotime
	 * @return string
	 */
	static function get_academic_year($date) {
		$date_as_time = strtotime($date);
		$start_this_year = strtotime(date('Y').'/'.self::$academic_year_start);
		
		if ($date_as_time < $start_this_year) {
			$session = (date('Y') - 1) . '/' . date('y');
		} else {
			$session = date('Y') . '/' . (date('y') + 1);
		}
		
		return $session;
	}
}

?>