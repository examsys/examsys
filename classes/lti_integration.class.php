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
* Abstract LTI integration helper
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Abstract class for LTI integration.
 */
abstract class lti_integration {
    /**
     * @var $config Config object
     */
    public $config;

    /**
     * Constructor
     */
    public function __construct() {
        $this->config = Config::get_instance();
    }

    /**
     * Is staff module link editing enabled in lti
     * @param string $data module data
     * @return bool
     */
    public function allow_staff_edit_link() {
        return false;
    }

    /**
     * Is student self reg onto module enabled in lti
     * @return bool
     */
    public function allow_module_self_reg() {
        return $this->config->get_setting('core', 'cfg_lti_allow_module_self_reg');
    }

    /**
     * Is staff self reg onto module enabled in lti
     * @return bool
     */
    public function allow_staff_module_register() {
        return $this->config->get_setting('core', 'cfg_lti_allow_staff_module_register');
    }

    /**
     * Is module creation enabled in lti
     * @return bool
     */
    public function allow_module_create() {
        return $this->config->get_setting('core', 'cfg_lti_allow_module_create');
    }
    
    /**
     * Check last time logged in and decide if re-authentication should be done
     * @param string $time last time logged in
     * @return bool true if user require re-authentication 
     */
    abstract public function user_time_check($time);
    
    /**
     * Convert VLE module shortcode into Rogo moduleid 
     * @param mysqli $mysqli db connection
     * @param string $moduleshortcode VLE module shortcode
     * @param string $course_title VLE module title
     * @return array rogo module information
     */
    abstract public function module_code_translate($mysqli, $c_internal_id, $course_title = '');
    
    /**
     * Returns the sms url appropriate for the item element
     * @param array $data module data from module_code_translate
     * @return string SMS url
     */
    abstract public function sms_api($data);

    /**
     * Translate source id in rogo external id.
     * @param string $sourceid source id from VLE
     * @return mixed module external id or null
     */
    abstract public function module_id_translate($sourceid);
}
