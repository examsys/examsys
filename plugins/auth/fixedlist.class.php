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
 * The fixedlist authentication function.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'outline_authentication.class.php';

class fixedlist_auth extends outline_authentication
{
    public $impliments_api_auth_version = 1;
    public $version = 0.9;
    private $updatable = false;

    public function register_callback_routines()
    {
        $callbackarray[] = array(array($this, 'auth'), 'auth', $this->number, $this->name);
        $callbackarray[] = array(array($this, 'failauth'), 'postauthfail', $this->number, $this->name);
        return $callbackarray;
    }

    public function auth($authobj)
    {
        $this->retdata =& $authobj;
        $this->savetodebug('Authing');
        extract($this->settings);
        if (!isset($this->form['std']->username) or !isset($this->form['std']->password) or $this->form['std']->username == '' or $this->form['std']->password == '') {
            //return not sucessfull do not try
            $this->savetodebug('Check 1 blank entries');
            $this->retdata->fail($this->number);
            $this->retdata->message = 'Not valid entry for username or password';
            return $authobj;
        }

        $success = false;
        foreach ($this->settings['authusers'] as $user => $upasswd) {
            if ($user == $this->form['std']->username and $upasswd == $this->form['std']->password) {
                $success = true;
                break;
            }
        }

        if ($success === false) {
            $this->retdata->fail($this->number);
            $this->retdata->message = 'Doesnt match list of users';
            return $authobj;
        }

        $this->retdata->username = $this->form['std']->username;
        if (isset($this->settings['lookupuser_db']) and $this->settings['lookupuser_db'] === true) {
            $sql = "SELECT $id_col as id FROM $table WHERE $username_col = ?";
            $result = $this->db->prepare($sql);
            $result->bind_param('s', $this->form['std']->username);
            $result->execute();
            $result->store_result();
            $result->bind_result($id);
            if ($result->num_rows() !== 1) {
                // return not sucessfull either no user or multiple matches
                $this->savetodebug('Check records number not = 1 no user or multiple user found');
                $this->retdata->fail($this->number);
                $this->retdata->message = 'Incorrect number of records returned';
                return $authobj;
            }
            $result->fetch();
            $this->savetodebug('Successfully authenticated and lookedup user via db on this module');
            //sucessfull internaldb authentication
            $this->retdata->success($this->number, $id);
            $this->retdata->message = 'Fixed List Correctly Authenticated';
        } elseif (isset($this->settings['lookupuser_list']) and $this->settings['lookupuser_list'] === true) {
            $this->savetodebug('Successfully authenticated and lookedup user via list on this module');
            //sucessfull internaldb authentication
            $this->retdata->success($this->number, $this->settings['authuserlookup'][$this->form['std']->username]);
            $this->retdata->message = 'Fixed List Correctly Authenticated';
        } else {
            $this->savetodebug('Not looking up user just returning -9999 as user');
            $this->retdata->success($this->number, -9999);
            $this->retdata->message = 'Not looking up user just returning -9999 as userid';
        }

        return $authobj;
    }

    public function failauth($postauthfailreturn)
    {
        $this->savetodebug('Fail function run');
        //default behaviour is to display username/password form
        $postauthfailreturn->form = 'std';
        $postauthfailreturn->exit = true;
        return $postauthfailreturn;
    }
}
