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
 * The Cosign authentication class
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'outline_authentication.class.php';
require_once dirname(dirname(__DIR__)) . '/cosign/cosign.class.php';

class cosign_auth extends outline_authentication
{
    private $lookupuserobj;

    public $impliments_api_auth_version = 1;
    public $version = 0.9;
    protected $cosign;

    public function init($object)
    {
        parent::init($object);
        $this->cosign = new cosign($this->settings['cosign_cfg'], $this);
    }

    public function register_callback_routines()
    {
        $callbackarray[] = array(array($this, 'auth'), 'auth', $this->number, $this->name);
        $callbackarray[] = array(array($this, 'failauth'), 'postauthfail', $this->number, $this->name);
        $callbackarray[] = array(array($this, 'loginbutton'), 'displaystdform', $this->number, $this->name);

        return $callbackarray;
    }

    public function failauth($postauthfailreturn)
    {
        $this->savetodebug('postauthfail run');
        $postauthfailreturn->form = 'std';
        $postauthfailreturn->exit = true;

        return $postauthfailreturn;
    }

    public function loginbutton($displaystdformobj)
    {
        if (isset($this->settings['cosign_button']) and $this->settings['cosign_button'] === true) {
            $this->savetodebug('Login Button for cosign login enabled');

            $this->savetodebug('Adding New Button');
            $newbutton = new displaystdformobjbutton();
            $newbutton->type = 'submit';
            $newbutton->value = ' Login via Co-Sign ';
            $newbutton->name = 'cosignlogin';
            $newbutton->class = 'cosignlogin';
            $displaystdformobj->buttons[] = $newbutton;

            $displaystdformobj->disablerequired = true;
        }

        // Possibility of making button to POST via jquery to the cosign login page with our login data then detect response
        return $displaystdformobj;
    }

    public function auth($authobj)
    {
        $this->retdata =& $authobj;
        $this->savetodebug('Authing');

        $this->savetodebug('Query string info: ' . $_SERVER['QUERY_STRING']);
        foreach ($this->request as $key => $value) {
            if ($key != 'ROGO_PW') {
                $requestmod[$key] = $value;
            } else {
                $requestmod[$key] = '*******';
            }
        }
        $this->savetodebug('request string decoded info: ' . var_export($requestmod, true));

        // Run cosing auth if button is enabled and pressed or button always if that mode is enabled or if it receives a query string containing cosign

        if ((isset($this->settings['cosign_button']) and $this->settings['cosign_button'] === true and isset($this->request['cosignlogin'])) or ( ((isset($this->settings['cosign_button']) and $this->settings['cosign_button'] === false   ) or (!isset($this->settings['cosign_button'])) )     ) or (mb_strpos($_SERVER['QUERY_STRING'], 'cosign') !== false )) {
            // Button is enabled
            $this->savetodebug('starting cosign auth process');

            $status = $this->cosign->cosign_auth();
        } else {
            // Button is disabled
            $this->savetodebug('conditions for cosign login not met');
            return $authobj;
        }

        if ($status === false) {
            $this->savetodebug('cosign block reports auth as failed');
            $authobj->fail($this->number);
            $authobj->message = 'Not valid cosign';

            return $authobj;
        }

        $this->savetodebug('cosign block reports auth as SUCCEEDED');
        // Bit below looks username up in the ExamSys table to get an id.

        extract($this->settings);

        if (isset($_SERVER[$this->settings['usernamefield']])) {
            $username = $_SERVER[$this->settings['usernamefield']];
        } else {
            $username = '';
        }


        if ($username == '') {
            $this->savetodebug('Check 1 blank entries blank username');

            $authobj->fail($this->number);
            $authobj->message = 'Not valid entry for username or password';

            return $authobj;
        }

        $this->savetodebug('Now looking up userid in table from username');
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
            // Cosign says OK but no association to ExamSys

            $this->savetodebug('cosign authenticated but no local account');
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

            // Sucessfull  authentication
            $authobj->success($this->number, $id);
        }

        return $authobj;
    }
}
