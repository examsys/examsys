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
 * The apache module authentication class
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'outline_authentication.class.php';

class apache_auth extends outline_authentication
{
    private $lookupuserobj;

    private $createnewuserassociation = false;
    public $impliments_api_auth_version = 1;
    public $version = 0.9;

    public function register_callback_routines()
    {
        $callbackarray[] = array(array($this, 'auth'), 'auth', $this->number, $this->name);
        $callbackarray[] = array(array($this, 'failauth'), 'postauthfail', $this->number, $this->name);
        $callbackarray[] = array(array($this, 'createnewuserassociation'), 'postauthsuccess', $this->number, $this->name);

        return $callbackarray;
    }

    public function auth($authobj)
    {
        $this->retdata =& $authobj;
        $this->savetodebug('Authing');

        extract($this->settings);

        if (!isset($_SERVER[$this->settings['usernamefield']])) {
            $this->savetodebug('Didnt detect the ' . $this->settings['usernamefield'] . ' field in the $_SERVER php variable');
            $authobj->fail($this->number);
            return $authobj;
        }

        $username = $_SERVER[$this->settings['usernamefield']];

        $this->savetodebug('Now looking up userid in table from username: ' . $username);
        if (!isset($sql_extra)) {
            $sql_extra = '';
        }
        $sql = "SELECT $username_col AS username, $id_col AS id FROM $table WHERE $username_col = ? $sql_extra";

        $result = $this->db->prepare($sql);
        $result->bind_param('s', $username);
        $result->execute();
        $result->store_result();
        $this->savetodebug('sql is:' . $sql . ' with parameter:' . $username);

        $result->bind_result($uname, $id);
        $result->fetch();
        if ($result->num_rows() > 1) {
            // not unique match
            $this->savetodebug('Check 2 record number> 1 multiple user found in lookup');

            $authobj->fail($this->number);
            $authobj->message = 'Incorrect number of records returned';

            return $authobj;
        } elseif ($result->num_rows() == 0) {
            //apache says ok but no association to ExamSys

            $this->savetodebug('Apache authenticated but no local account');
            $data = new stdClass();
            if (!isset($this->settings['search_field'])) {
                $this->settings['search_field'] = 'username';
            }
            $data->{$this->settings['search_field']} = $username;


            if (isset($this->settings['enable_fudgecreateuser']) and $this->settings['enable_fudgecreateuser'] == true) {
                $this->createnewuserassociation = true;
            }
            if (isset($this->settings['disable_ldapmissing']) and $this->settings['disable_ldapmissing'] == true) {
                $this->savetodebug('setting is set to disable lookup');
                $authobj->fail($this->number);
            } else {
                $authobj->lookupmissing($this->number, $data);
            }

            return $authobj;
        }

        if ($result->num_rows() == 1) {
            $this->savetodebug('Successfully authenticated on this module username=' . $username . ' id:' . $id);

            //sucessfull  authentication
            $authobj->success($this->number, $id);
        }


        return $authobj;
    }

    public function failauth($postauthfailreturn)
    {
        $this->savetodebug('Fail function run');
        $this->savetodebug('Not sure what sensible default behaviour is -- let other modules choose as in theory this location is impossible if apache auth is on');

        return $postauthfailreturn;
    }


    public function createnewuserassociation($postauthsuccessobj)
    {
        if ($this->createnewuserassociation !== true) {
            return $postauthsuccessobj;
        }
        if (isset($this->settings['enable_fudgecreateuser']) and $this->settings['enable_fudgecreateuser'] !== true) {
            return $postauthsuccessobj;
        }
        $username_col = $this->settings['username_col'];
        $id_col = $this->settings['id_col'];
        $table = $this->settings['table'];
        $sql = "INSERT INTO $table SET $username_col=?, $id_col=?";
        $result = $this->db->prepare($sql);

        $result->bind_param('si', $this->form['std']->username, $postauthsuccessobj->userid);

        $result->execute();

        return $postauthsuccessobj;
    }
}
