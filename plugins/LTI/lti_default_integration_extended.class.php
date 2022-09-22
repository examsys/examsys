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
 *
 * Handles UoN LTI Integration in ExamSys
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class lti_default_integration_extended extends lti_integration
{
    /**
     * Check last time logged in and decide if re-authentication should be done
     * @param string $time last time logged in
     * @return bool true if user require re-authentication
     */
    public function user_time_check($time)
    {
        // takes laast time logged in and optionally the user and decides if reauthentication should be done (true)
        return false;
    }

    /**
     * Convert VLE module shortcode into ExamSys moduleid
     * @param mysqli $mysqli db connection
     * @param string $moduleshortcode VLE module shortcode
     * @param string $course_title VLE module title
     * @return array ExamSys module information
     */
    public function module_code_translate($mysqli, $c_internal_id, $course_title = '')
    {
        // This function translates the incoming course code and course title it returns an array (containing possibly multiple records)
        // of an array containing string if Manual or SMS for sms ones,
        // the module code,
        // a campus code (text) ,
        // school as a string (gets lookedup against ExamSys to get id later,
        // a 1 for self reg enable [0 for disable]
        // and the course title.
        return array(array('Manual', $c_internal_id, 'CampusTODO', 'UNKNOWN School', 0, "MISSING:$course_title"));
    }

    /**
     * Returns the empty string as generic sms does not check against sms for modules also defualt lti only creates manual modules in module_code_translate
     * @param array $data module data from module_code_translate
     * @return string SMS url
     */
    public function sms_api($data)
    {
        return '';
    }

    /**
     * Translate source id in ExamSys external id.
     * @param string $sourceid source id from VLE
     * @return mixed module external id or null
     */
    public function module_id_translate($sourceid)
    {
        return null;
    }
}
