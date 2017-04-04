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
 * Utility class for academic years supported by system.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 */
class yearutils {

    /**
     * A mysqli object.
     */
    private $mysqli;
    
    /**
     * A string mm/dd that states the start of the academic year.
     */
    private $academic_year_start;

    /**
     * Constant for all academic years.
     */
    const ALL = "ALL";
    /**
     * Constant for academic years viewable in the calendar.
     */
    const CAL = "CAL";
    /**
     * Constant for academic years viewable in statistics.
     */
    const STAT = "STAT";
    /**
     * Constant for academic years visible in both the calendar and statisics.
     */
    const BOTH = "BOTH";

    /**
     * Called when the object is unserialised.
     */
    public function __wakeup() {
        // The serialised database object will be invalid,
        // this object should only be serialised during an error report,
        // so adding the current database connect seems like a waste of time.
        $this->mysqli = null;
    }

    /**
     * Constructor
     * @param rogo db $mysqli
     */
    function __construct($mysqli) {
        $configObject = Config::get_instance();

        $this->mysqli = $mysqli;
        // Start of academic year (mm/dd)
        $year_start = $configObject->get('cfg_academic_year_start');
        if ($this->check_year_start_format($year_start)) {
            $this->academic_year_start = $configObject->get('cfg_academic_year_start');
        } else {
            $this->academic_year_start = '07/01';
        }
    }


    /**
     * Get years supported by the system.
     *
     * @param string $state - filter which years to retrieve - ALL, CAL (active calendar years), STAT (active statistical years),
     *        BOTH (active calendar and statistical years)
     * @return array - associative array of calendar and academic years
     */
    public function get_supported_years($state = self::ALL) {

        if ($state == self::STAT) {
            $filter = "WHERE stat_status = 1 AND deleted is NULL ORDER BY calendar_year ASC";
        } else if ($state == self::CAL) {
            $filter = "WHERE cal_status = 1 AND deleted is NULL ORDER BY calendar_year ASC";
        } else if ($state == self::BOTH) {
            $filter = "WHERE cal_status = 1 AND stat_status = 1 AND deleted is NULL ORDER BY calendar_year ASC";
        } else {
            $filter = "WHERE deleted is NULL ORDER BY calendar_year ASC";
        }

        $supported_years = array();
        $result = $this->mysqli->prepare("SELECT calendar_year, academic_year FROM academic_year $filter");
        $result->execute();
        $result->bind_result($calendar_year, $academic_year);
        while ($result->fetch()) {
            $supported_years[$calendar_year] = $academic_year;
        }
        $result->close();
        return $supported_years;
    }

    /**
     * Create options list for a drop down menu of sessions.
     *
     * @param char $paper_type type of paper
     * @param string $calendar_year - current calendar year
     * @param array $string - language sting array
     * @return string - options list
     */
    public function get_calendar_year_dropdown_options($paper_type, $calendar_year, $string) {
        $list = "";
        if ($paper_type != '2' and $paper_type != '4') {
            $list = "<option value=\"\">" . $string['na'] .  "</option>\n";
        }

        $years = $this->get_supported_years();

        foreach ($years as $calendar => $academic) {
            $list .= "<option value=\"" . $calendar . "\"";
            if ($calendar_year == $calendar) {
                $list .= 'selected';
            }
            $list .= ">" . $academic . "</option>\n";
        }
        return $list;
    }

    /**
     * Checks the format of the start year is mm/dd
     * @param string - $specific_year_start - Academic year start for the specifc module.
     * @return string - True is correct format, flase otherwise
     */
    public function check_year_start_format($specific_year_start) {

        // Fisrt check correct format xx/xx
        if(!preg_match('/([0-9]{2})\/([0-9]{2})/', $specific_year_start)) {
            return false;
        }
        
        // Second check date
        $year = date('Y');
        list($month, $day) = explode('/', $specific_year_start);
        return checkdate($month, $day, $year);

    }
    
    /**
     * Get the current academic session
     * @param string - $specific_year_start - Academic year start for the specifc module in the format 'mm/dd'.
     * @return string - The current academic year.
     */
    public function get_current_session($specific_year_start = '') {

        $date_as_time = strtotime(date('Y/m/d'));
        if ($this->check_year_start_format($specific_year_start)) {
            $start_this_year = strtotime(date('Y') . '/' . $specific_year_start);
        } else {
            $start_this_year = strtotime(date('Y') . '/' . $this->academic_year_start);
        }

        if ($date_as_time < $start_this_year) {
            $session = date('Y') - 1;
        } else {
            $session = date('Y');
        }

        return $session;
    }

    /**
     * Get the next academic session
     * @param string - $specific_year_start - Academic year start for the specifc module in the format 'mm/dd'.
     * @return string - The next academic year.
     */
    public function get_next_session($specific_year_start = '') {

        $date_as_time = strtotime(date('Y/m/d'));
        if ($this->check_year_start_format($specific_year_start)) {
            $start_this_year = strtotime(date('Y') . '/' . $specific_year_start);
        } else {
            $start_this_year = strtotime(date('Y') . '/' . $this->academic_year_start);
        }

        if ($date_as_time < $start_this_year) {
            $session = date('Y');
        } else {
            $session = date('Y') + 1;
        }

        return $session;
    }

    /**
     * Get the current academic session
     * @param int $calendar_year - the calendar year
     * @return string - The associated academic year
     */
    public function get_academic_session($calendar_year) {

        $result = $this->mysqli->prepare("SELECT academic_year FROM academic_year WHERE calendar_year = ?");
        $result->bind_param('i', $calendar_year);
        $result->execute();
        $result->bind_result($academic_year);
        $result->store_result();
        $result->fetch();
        $result->close();
        return $academic_year;
    }

    /**
     * Check if calendar year already exists.
     * @param int $calendar_year - the calendar year
     * @return bool - true if calendar year exists, false otherwise
     */
    public function check_calendar_year($calendar_year) {

        $result = $this->mysqli->prepare("SELECT 1 FROM academic_year WHERE calendar_year = ? LIMIT 1");
        $result->bind_param('i', $calendar_year);
        $result->execute();
        $result->store_result();
        $result->fetch();
        if ($result->num_rows == 1) {
            $result->close();
            return true;
        }

        $result->close();
        return false;

    }
    
    /**
     * Check atleast two academic session exists.
     * @return bool - number of active academic sessions
     */
     public function count_active_academic_session() {

        $result = $this->mysqli->prepare("SELECT count(calendar_year) FROM academic_year WHERE deleted IS NULL");
        $result->execute();
        $result->bind_result($count);
        $result->fetch();
        return $count;

    }

    /**
     * Delete an academic year by setting a flag
     * @param int $year - calendat year
     *
     * @return bool - Return false if no year is passed.
     */
     public function delete_year($calendar_year, $user) {
        if ($calendar_year == '') {
          return false;
        }

        $result = $this->mysqli->prepare("UPDATE academic_year SET deleted = NOW(), deletedby = ? WHERE calendar_year = ? AND deleted is NULL");
        $result->bind_param('ii', $user, $calendar_year);
        $result->execute();
        $result->close();
    }

    /**
     * Check if calendar year is in use.
     * @param int $calendar_year - the calendar year
     * @return bool - true if calendar year is in use, false otherwise
     */
    public function check_calendar_year_in_use($calendar_year) {

        $result = $this->mysqli->prepare("(SELECT calendar_year FROM modules_student WHERE calendar_year = ?) "
          . "UNION (SELECT calendar_year FROM objectives WHERE calendar_year = ?) "
          . "UNION (SELECT calendar_year FROM properties WHERE calendar_year = ?) "
          . "UNION (SELECT calendar_year FROM relationships WHERE calendar_year = ?) "
          . "UNION (SELECT calendar_year FROM sessions WHERE calendar_year = ?) "
          . "UNION (SELECT academic_year FROM sms_imports WHERE academic_year = ?) "
          . "UNION (SELECT calendar_year FROM users_metadata WHERE calendar_year = ?) LIMIT 1");
        $result->bind_param('iiiiiii', $calendar_year, $calendar_year, $calendar_year, $calendar_year, $calendar_year, $calendar_year, $calendar_year);
        $result->execute();
        $result->store_result();
        $result->fetch();
        if ($result->num_rows == 1) {
            $result->close();
            return true;
        }
        $result->close();
        return false;

    }
}
