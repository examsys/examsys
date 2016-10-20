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
* SMS plugin functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

namespace plugins;

/**
 * Abstract SMS class.
 * 
 * This class should be extend by classes used define sms plugins.
 */
abstract class plugins_sms extends \plugins\plugins {
    /**
     * Type of the plugin.
     * @var string
     */
    protected $plugin_type = 'SMS';
    /**
     * Get names of sms external systems available to sync with
     * @param mysqli $db db connection
     * @return array|bool list of external systems or false if non available
     */
    static public function get_sms($db) {
        $sms = array();
        $config = \Config::get_instance();
        $plugins = $config->get_setting('plugin_sms', 'enabled_plugin');
        if (!is_null($plugins)) {
            foreach ($plugins as $p) {
                $mappingplugin_object = 'plugins\\SMS\\' . $p . '\\' . $p;
                $smsplugin = new $mappingplugin_object($db);
                $sms[] = $smsplugin->get_name();
            }
        }
        if (count($sms) == 0) {
            return false;
        } else {
            return $sms;
        }
    }
    /**
     * Get all assessments for academic session
     * @params integer $session academic session to sync assessments with
     */
    abstract public function get_assessments($session);
    /**
     * Get all enrolments for academic session
     * @params integer $session academic session to sync enrolments with
     * @params integer $externalid external system module id
     */
    abstract public function get_enrolments($session, $externalid = null);
    /**
     * Update module in an academic session
     * Updates module details and enrolments
     * @params integer $externalid external system module id
     * @params integer $session academic session to sync enrolments with
     */
    abstract public function update_module_enrolments($externalid, $session);
    /**
     * Get faculties/schools.
     */
    abstract public function get_faculties();
    /**
     * Get courses.
     */
    abstract public function get_courses();
    /**
     * Get modules.
     * @params integer $externalid external system module id
     * @params integer $session academic session to sync enrolments with
     */
    abstract public function get_modules($externalid = null, $session = null);
    /**
     * Write a gradebook for an academic session to a file to be processed by campus solutions.
     * @param integer $session academic session to publish gradebook for
     */
    abstract public function publish_gradebook($session);
    /**
     * Check if module import is supported by the plugin
     * @return array|bool import url and translation strings, false if module import not supported
     */
    abstract public function supports_module_import();
    /**
     * Check if faculty/school import is supported by the plugin
     * @return array|bool import url and translation strings, false if faculty import not supported
     */
    abstract public function supports_faculty_import();
    /**
     * Check if course import is supported by the plugin
     * @return array|bool import url and translation strings, false if course import not supported
     */
    abstract public function supports_course_import();
    /**
     * Check if enorlment import is supported by the plugin
     * @return array|bool false if enrolment import not supported
     */
    abstract public function supports_enrol_import();
    /**
     * Check if assessment import is supported by the plugin
     * @return array|bool import url and translation strings, false if assessment import not supported
     */
    abstract public function supports_assessment_import();
    /**
     * Get name of sms
     * @return string name of sms
     */
    abstract public function get_name();
}