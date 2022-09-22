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
 * outline authentication class.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

$configObject = Config::get_instance();

class outline_authentication
{
    protected $name;
    protected $number;
    protected $returndata;
    protected $retdata;
    protected $form;
    protected $settings;
    protected $db;
    protected $calling_object;
    protected $session;
    protected $request;
    public $debug = array();
    public $debugpointer = 0;
    protected $error = null;
    public $rogoid = false;
    protected $authapiversion;
    protected $callbackarray;
    protected $impliments_api_auth_version = 0;

    /**
     * @param object $calling_object its called from
     * @param array $settings settings options
     * @param int $number the number this is
     * @param string $name the name of it
     * @param object $db a link to db
     * @param object $returndata where data is stored
     * @param object $form a class with form data in
     */
    public function __construct($number, $name, $authapiversion)
    {
        $this->authapiversion = $authapiversion;
        $this->name = $name;
        $this->number = $number;
    }

    /*
    * Check the API version of the stack and the plugin are compatible
    * returns true if it is compatible false otherwise
    */
    public function apicheck()
    {
        if ($this->authapiversion != $this->impliments_api_auth_version) {
            $this->savetodebug('This auth object is implementing an different version of the api than this plugin does');
            $this->set_error('Wrong API');
            return false;
        }

        return true;
    }

    public function set_error($msg)
    {
        if (mb_strlen($this->error) > 0) {
            $this->error .= '<br />';
        }
        $this->error .= $msg;
    }

    public function init($object)
    {
        $this->db = new mysqli();
        $this->db = & $object->db;
        $this->calling_object = & $object->calling_object;
        $this->form = & $object->form;
        $this->settings = & $object->settings;
        $this->session = & $object->calling_object->session;
        $this->request = & $object->calling_object->request;
    }

    public function error_handling($context = null)
    {
        $context1 = array();
        if (is_null($context)) {
            // if no array set get currently define variables in this object
                $context = get_defined_vars($this);
        }

        $context1 = error_handling($context);
        if (isset($context1['settings'])) {
            $context1['settings'] = 'hidden for security';
        }
        return $context1;
    }

    // Fake function used in mocking but if things go wrong have an outline here
    public function mock($callingobject, $settings, $number, $name, $db, $returndata, $form)
    {
        return false;
    }

    /**
     * @param string $debugmessage the debug message to store
     */
    public function savetodebug($debugmessage)
    {
        $this->debug[] = $debugmessage;
    }

    /**
     * @param string $section the section to get the callback from
     *
     * @return mixed
     */
    public function get_callback($section)
    {
        return $this->calling_object->get_callback($section);
    }

    /**
     * @param int $objid the objectid
     *
     * @return mixed
     */
    public function get_new_debug_messages($number = null)
    {
        if (is_null($number)) {
            $returnarray = array();
            while (isset($this->debug[$this->debugpointer])) {
                $returnarray[$this->debugpointer] = $this->debug[$this->debugpointer++];
            }

            return $returnarray;
        } else {
            return $this->calling_object->authPluginObj[$number]->get_new_debug_messages();
        }
    }

    /**
     * @param int $objid the objectid
     *
     * @return mixed
     */
    public function get_module_authinfo($objid)
    {
        return $this->calling_object->authinfo[$objid];
    }


    public function register_callback_sections()
    {
        //this is blank so that classes that dont register anything dont break
        return array();
    }

    /**
     * @param callback $callback routine
     * @param string $section section to register callback in
     * @param string $number the number this object is
     * @param string $name the name this object is
     * @param bool $insert to insert rather than append
     *
     * @return bool
     */
    public function register_callback($callback, $section, $number, $name, $insert = false)
    {
        $this->callbackarray[] = array($callback, $section, $number, $name, $insert);
    }


    /**
     * @return array
     */
    public function register_callback_routines()
    {
        //this is blank so that classes that dont register anything dont break
        return array();
    }

    /**
     * @param $setting string the setting to return or false if it doesnt exist
     *
     * @return mixed
     */
    public function get_settings($setting)
    {
        if (!isset($this->settings[$setting])) {
            return false;
        }

        return $this->settings[$setting];
    }

    public function get_info()
    {
        $data = new stdClass();
        $data->name = $this->name;
        $data->number = $this->number;
        $data->classname = get_class($this);
        $data->classname = mb_substr($data->classname, 0, mb_strpos($data->classname, '_auth'));
        $data->version = $this->version;
        $data->settings = $this->settings;
        $data->api_implimented = $this->impliments_api_auth_version;
        $data->error = $this->error;
        if (isset($this->callbackarray)) {
            foreach ($this->callbackarray as $callback) {
                $funcname = $callback[0][1];
                $where = $callback[1];
                $data->callbackfunctions[] = array($funcname, $where);
            }
        }

        return $data;
    }
}
