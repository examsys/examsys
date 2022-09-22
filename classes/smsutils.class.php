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
 * Utility class for Student Management System (SMS) related functions
 *
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once(dirname(__DIR__) . '/include/load_config.php');

abstract class SmsUtils
{
    public $errorinfo;

    public static function GetSmsUtils()
    {
        $configObject = Config::get_instance();
        $cfg_sms_api = $configObject->get('cfg_sms_api');
        if (isset($cfg_sms_api) and $cfg_sms_api != '') {
            require_once($configObject->get('cfg_web_root') . '/plugins/SMS/' . $cfg_sms_api . '.class.php');
            return new $cfg_sms_api();
        }

        return false;
    }

    public function __construct()
    {
        $this->errorinfo['usernamematch'] = array();
        $this->errorinfo['usernamematchdata'] = array();
        $this->errorinfo['unabletodetermineusername'] = array();
        $this->errorinfo['unabletodetermineusernamedata'] = array();
        $this->errorinfo['moduleerrorstate'] = array();
        $this->errorinfo['moduleerrorstatedata'] = array();
        $this->errorinfo['modulenodata'] = array();
        $this->errorinfo['modulenodatadata'] = array();
    }

    public function geterrors()
    {
        return $this->errorinfo;
    }

    abstract protected function getUserData($username);
    abstract protected function getModuleEnrolements($moduleID, $mysqli);
    abstract protected function getStudentSources();
    abstract protected function getModuleSources();
}
