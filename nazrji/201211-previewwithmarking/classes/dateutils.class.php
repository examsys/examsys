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
* Utility class for date related functionality
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

Class date_utils {
	// Start of academic year (mm/dd)
	public static $academic_year_start = '07/01';
	
	/**
	 * Get the current academic year in the format 'yyyy/yy', e.g. '2010/11'
	 * @return string
	 */
	static function get_current_academic_year()	{
		return date_utils::get_academic_year(date('Y/m/d'));
	}

  static function inc_academic_year($year) {
    $first_part = substr($year, 0, 4);
    $first_part++;
    $second_part = substr($year, 5, 2);
    $second_part++;
    
    $next_year = $first_part . '/' . $second_part;
    
    return $next_year;
  }

	/**
	 * Get the academic year for the given date in the format 'yyyy/yy', e.g. '2010/11'
	 * @param string $date A date in a format that can be accepted by strtotime
	 * @return string
	 */
	static function get_academic_year($date) {
    global $cfg_academic_year_start;
    
		$date_as_time = strtotime($date);
    if (isset($cfg_academic_year_start)) {
      $start_this_year = strtotime(date('Y') . '/' . $cfg_academic_year_start);
    } else {
      $start_this_year = strtotime(date('Y') . '/' . self::$academic_year_start);
    }
		if ($date_as_time < $start_this_year) {
			$session = (date('Y') - 1) . '/' . date('y');
		} else {
			$session = date('Y') . '/' . (date('y') + 1);
		}
		
		return $session;
	}
  
  static function timedate_select($prefix, $imput_date='') {
    global $string;
    
    $split_year = substr($imput_date,0,4);
    $split_month = substr($imput_date,4,2);
    $split_day = substr($imput_date,6,2);
    $split_hour = substr($imput_date,8,2);
    $split_minute = substr($imput_date,10,2);
    
    $html = '';

    // Day
    $html .= "<select name=\"" . $prefix . "day\">\n";
    for ($i = 1; $i < 32; $i++) {
      if ($i < 10) {
        if ($i == $split_day) {
          $html .= "<option value=\"0$i\" selected>";
        } else {
          $html .= "<option value=\"0$i\">";
        }
      } else {
        if ($i == $split_day) {
          $html .= "<option value=\"$i\" selected>";
        } else {
          $html .= "<option value=\"$i\">";
        }
      }
      if ($i < 10) $html .= '0';
      $html .= "$i</option>\n";
    }
    $html .= "</select>\n";
    
    // Month
    $html .= "<select name=\"" . $prefix . "month\">\n";
    $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
    for ($i=0; $i<12; $i++) {
      $trans_month = mb_substr($string[$months[$i]],0,3,'UTF-8');
      if (($split_month-1) == $i) {
        if ($i < 9) {
          $html .= "<option value=\"0" . ($i+1) . "\" selected>$trans_month</option>\n";
        } else {
          $html .= "<option value=\"" . ($i+1) . "\" selected>$trans_month</option>\n";
        }
      } else {
        if ($i < 9) {
          $html .= "<option value=\"0" . ($i+1) . "\">$trans_month</option>\n";
        } else {
          $html .= "<option value=\"" . ($i+1) . "\">$trans_month</option>\n";
        }
      }
    }
    $html .= "</select>\n";
    
    // Year
    $html .= "<select name=\"" . $prefix . "year\">\n";
    for ($i = 2002; $i < 2021; $i++) {
      if ($i == $split_year) {
        $html .= "<option value=\"$i\" selected>$i</option>\n";
      } else {
        $html .= "<option value=\"$i\">$i</option>\n";
      }
    }
    $html .= "</select>\n<select name=\"" . $prefix . "time\">\n";
    // Time
    $times = array('000000'=>'00:00','003000'=>'00:30','010000'=>'01:00','013000'=>'01:30','020000'=>'02:00','023000'=>'02:30','030000'=>'03:00','033000'=>'03:30','040000'=>'04:00','043000'=>'04:30','050000'=>'05:00','053000'=>'05:30','060000'=>'06:00','063000'=>'06:30','070000'=>'07:00','073000'=>'07:30','080000'=>'08:00','083000'=>'08:30','090000'=>'09:00','093000'=>'09:30','100000'=>'10:00','103000'=>'10:30','110000'=>'11:00','113000'=>'11:30','120000'=>'12:00','123000'=>'12:30','130000'=>'13:00','133000'=>'13:30','140000'=>'14:00','143000'=>'14:30','150000'=>'15:00','153000'=>'15:30','160000'=>'16:00','163000'=>'16:30','170000'=>'17:00','173000'=>'17:30','180000'=>'18:00','183000'=>'18:30','190000'=>'19:00','193000'=>'19:30','200000'=>'20:00','203000'=>'20:30','210000'=>'21:00','213000'=>'21:30','220000'=>'22:00','223000'=>'22:30','230000'=>'23:00','233000'=>'23:30');
    foreach ($times as $key => $value) {
      if ($key == $split_hour . $split_minute . '00') {
        $html .= "<option value=\"" . $key . "\" selected>" . $value . "</option>\n";
      } else {
        $html .= "<option value=\"" . $key . "\">" . $value . "</option>\n";
      }
    }
    $html .= "</select>\n";    
    
    return $html;
  }
}

?>