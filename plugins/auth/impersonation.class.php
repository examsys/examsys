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
 * Handles 'impersonation' whereby a SysAdmin user can log in as someone else.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'outline_authentication.class.php';

class impersonation_auth extends outline_authentication
{
    private $active = false;
    private $demo = false;
    private $newuserid;
    private $lookupuserobj;
    public $impliments_api_auth_version = 1;
    public $version = 0.9;

    public function register_callback_routines()
    {
        $callbackarray[] = array(array($this, 'checkwhattodo'), 'preauth', $this->number, $this->name);
        $callbackarray[] = array(array($this, 'changewhoiam'), 'getauthobj', $this->number, $this->name);
        $callbackarray[] = array(array($this, 'storedata'), 'sessionstore', $this->number, $this->name);
        return $callbackarray;
    }

    public function changewhoiam($getauthobj)
    {
        if (isset($this->session['authenticationObj']['impersonation']['newuserid']) and !is_null($this->session['authenticationObj']['impersonation']['newuserid'])) {
            if (!$getauthobj->userObj->has_role('SysAdmin')) {
                $this->savetodebug('Cannot change user as not a SysAdmin');
            }
            $getauthobj->userObj->impersonate($this->session['authenticationObj']['impersonation']['newuserid']);
        }
        if (isset($this->session['authenticationObj']['impersonation']['demo']) and $this->session['authenticationObj']['impersonation']['demo'] === true) {
            $this->savetodebug('Changing user status to DEMO');
            $getauthobj->userObj->set_demo();
        }

        return $getauthobj;
    }

    public function storedata($sessionstoreobj)
    {
        $this->savetodebug('session store');
        $this->session['authenticationObj']['impersonation']['newuserid'] = $this->newuserid;
        $this->session['authenticationObj']['impersonation']['demo'] = $this->demo;
        return $sessionstoreobj;
    }

    public function checkwhattodo($preauthobj)
    {
        $this->savetodebug('Starting up impersination checking');
        $continue = false;
        if (isset($this->form['std']->username)) {
            if (mb_strpos($this->form['std']->username, $this->settings['separator']) !== false) {
                $usernameparts = explode($this->settings['separator'], $this->form['std']->username);
                if (isset($usernameparts[1])) {
                    $continue = true;
                    $this->savetodebug('found separator char');
                }
            }
        }

        if ($continue !== true) {
            if (isset($this->session['authenticationObj']['impersonation']['newuserid']) or isset($this->session['authenticationObj']['impersonation']['demo'])) {
                $this->savetodebug('Found store data in session for impersonation');
                $this->newuserid = $this->session['authenticationObj']['impersonation']['newuserid'];
                $this->demo = $this->session['authenticationObj']['impersonation']['demo'];
            }

            return $preauthobj;
        }

        if ((strcasecmp($usernameparts[1], 'demo') == 0) or (isset($usernameparts[2]) and strcasecmp($usernameparts[2], 'demo') == 0)) {
            $this->demo = true;
            $this->savetodebug('Demo mode detected');
            $this->active = true;
            $this->form['std']->username = $usernameparts[0];
            if (!(isset($usernameparts[2]) and strcasecmp($usernameparts[2], 'demo') == 0)) {
                return $preauthobj;
            }
        }
        if ((strcasecmp($usernameparts[1], 'staff') == 0) or (isset($usernameparts[2]) and strcasecmp($usernameparts[2], 'staff') == 0)) {
            $this->staff_mode = true;
            $this->savetodebug('Staff log in mode detected');
            $this->active = true;
            $this->form['std']->username = $usernameparts[0];
            if (!(isset($usernameparts[2]) and strcasecmp($usernameparts[2], 'staff') == 0)) {
                return $preauthobj;
            }
        }
        if (!isset($this->lookupuserobj)) {
            $this->lookupuserobj = new stdClass();
            $this->lookupuserobj->username = $usernameparts[1];
            $this->lookupuserobj->found = false;
        }
        list($callbacklist, $callbackregisterdatalist) = $this->get_callback('lookupuser');

        if (is_array(($callbacklist))) {
            foreach ($callbacklist as $number => $callback) {
                call_user_func_array($callback, array($this->lookupuserobj));
                $objid = key($callbackregisterdatalist[$number]);
                $new_messages = $this->get_new_debug_messages($objid);
                foreach ($new_messages as $key => $value) {
                    $info1 = $this->get_module_authinfo($objid);
                    $info = key($info1) . ':' . current($info1);
                    $this->savetodebug("Lookup User:authObj($info)[$number:$key]: $value");
                }
            }
        }

        if ($this->lookupuserobj->found === true) {
            $this->active = true;
            //assuming first lookup is the one we want is check needed for only one id
            $this->newuserid = $this->lookupuserobj->results[0]->userid;
        }

        if ($this->active === true) {
            $this->form['std']->username = $usernameparts[0];
        }

        return $preauthobj;
    }
}
