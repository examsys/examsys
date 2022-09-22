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
 * The already logged in authentication class
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'outline_authentication.class.php';

class alreadyloggedin_auth extends outline_authentication
{
    private $lookupuserobj;
    public $impliments_api_auth_version = 1;
    public $version = 0.9;

    public function register_callback_routines()
    {
        if (isset($this->settings['disabled']) and $this->settings['disabled'] === true) {
            return array();
        }

        $callbackarray[] = array(array($this, 'auth'), 'auth', $this->number, $this->name);
        $callbackarray[] = array(array($this, 'store_user'), 'sessionstore', $this->number, $this->name);
        $callbackarray[] = array(array($this, 'update_time'), 'postauthsuccess', $this->number, $this->name);
        return $callbackarray;
    }

    public function auth($authobj)
    {
        if (isset($this->settings['disabled']) and $this->settings['disabled'] === true) {
            $this->retdata->fail($this->number);
            $this->savetodebug('disabling alreadyloggedin as setting has been set to do so');
            return $authobj;
        }

        $this->retdata =& $authobj;
        $this->savetodebug('Authing');
        $this->savetodebug(str_replace("\n", '', trim(rtrim(var_export($this->session, true)))));
        if (isset($this->session['authenticationObj']['loggedin']['userid']) and ($this->session['authenticationObj']['loggedin']['userid'] > 0 or $this->session['authenticationObj']['loggedin']['userid'] < -999) and $this->session['authenticationObj']['loggedin']['userid'] != '' and $this->session['authenticationObj']['loggedin']['userid'] != 'null' and is_int($this->session['authenticationObj']['loggedin']['userid'])) {
            $this->savetodebug('userid found in session');
            if (isset($this->settings['timeout']) and $this->settings['timeout'] != 0 and (($this->session['authenticationObj']['loggedin']['time'] + $this->settings['timeout']) > time())) {
                $this->savetodebug('Timeout is set and run out');
                $this->retdata->fail($this->number);
                return $authobj;
            } else {
                $this->savetodebug('Successfully authenticated');
                $this->retdata->success($this->number, $this->session['authenticationObj']['loggedin']['userid']);
                $this->retdata->success = true;
                $this->rogoid = $this->session['authenticationObj']['loggedin']['userid'];
                return $authobj;
            }
        }

        $this->savetodebug('No valid userid found in session');
        $this->retdata->fail($this->number);
        return $authobj;
    }

    public function store_user($sessionstoreobj)
    {
        $this->savetodebug('session store');
        $this->session['authenticationObj']['loggedin']['userid'] = $this->calling_object->get_userid();
        $this->session['authenticationObj']['loggedin']['time'] = time();
        $this->session['authenticationObj']['attempt'] = 0;
        return $sessionstoreobj;
    }

    public function update_time($postauthsuccessobj = '')
    {
        $this->savetodebug('Updated stored time in session');
        $this->session['authenticationObj']['loggedin']['time'] = time();
        if (!isset($this->lookupuserobj)) {
            $this->lookupuserobj = new stdClass();
        }

        list($callbacklist, $callbackregisterdatalist) = $this->get_callback('sessionstore');
        //  run this when needing to store auth data to session

        if (is_array(($callbacklist))) {
            foreach ($callbacklist as $number => $callback) {
                call_user_func_array($callback, array(&$this->lookupuserobj));
                $objid = key($callbackregisterdatalist[$number]);
                $new_messages = $this->get_new_debug_messages($objid);
                foreach ($new_messages as $key => $value) {
                    $info1 = $this->get_module_authinfo($objid);
                    $info = key($info1) . ':' . current($info1);
                    $this->savetodebug("Session Store:authObj($info)[$number:$key]: $value");
                }
            }
        }

        return $postauthsuccessobj;
    }
}
