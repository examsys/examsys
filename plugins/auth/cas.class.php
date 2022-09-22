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
 * The CAS authentication class
 *
 * NOTE: The PHP CAS library can be downloaded from: https://wiki.jasig.org/display/casc/phpcas
 *
 * @author Seun Ojo, Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package
 */

session_start(); // may need this line to use session variables (depending on the setup)

require_once 'outline_authentication.class.php';
// require_once $_SERVER['DOCUMENT_ROOT'] . '/CAS/config.php'; // or require_once $configObject->get('cfg_web_root') . 'CAS/config.php'; i.e. location of config file
require_once dirname(dirname(__DIR__)) . '/CAS/CAS.php'; // or require_once $configObject->get('cfg_web_root') . 'CAS/CAS.php'; i.e. location of CAS library

class cas_auth extends outline_authentication
{
    private $lookupuserobj;

    public $impliments_api_auth_version = 1;
    public $version = 0.9;

    public function init($object)
    {
        parent::init($object);
        /*
        // uncomment this section if forcing CAS authentication

        phpCAS::client($this->server_version, $this->server_hostname, $this->server_port, $this->server_uri);
        phpCAS::forceAuthentication(); // this will automatically redirect to the named CAS server login page if not logged in OR allow users through if logged in
        if (isset($_REQUEST['logout'])) {
          phpCAS::logout();
        }

        // if not forcing CAS authentication, do nothing
        */
    }

    public function register_callback_routines()
    {
        $callbackarray[] = array(array($this, 'auth'), 'auth', $this->number, $this->name);
        $callbackarray[] = array(array($this, 'failauth'), 'postauthfail', $this->number, $this->name);

        return $callbackarray;
    }

    public function failauth($postauthfailreturn)
    {
        $this->savetodebug('postauthfail run');
        $postauthfailreturn->form = 'std';
        $postauthfailreturn->exit = true;

        return $postauthfailreturn;
    }

    /*
    function loginbutton($displaystdformobj) {
    if (isset($this->settings['cas_button']) and $this->settings['cas_button'] === true) {
      $this->savetodebug('Login Button for cas login enabled');

      $this->savetodebug('Adding New Button');
      $newbutton = new displaystdformobjbutton();
      $newbutton->type = 'submit';
      $newbutton->value = ' Login via CAS ';
      $newbutton->name = 'caslogin';
      $newbutton->class = 'caslogin';
      $displaystdformobj->buttons[] = $newbutton;

      $displaystdformobj->disablerequired = true;
    }

    // Possibility of making button to POST via jquery to the cas login page with our login data then detect response
    return $displaystdformobj;
    }
    */

    public function auth($authobj)
    {
        $this->retdata =& $authobj;
        $this->savetodebug('Authing');

        if (isset($_SESSION['phpCAS']['user'])) { // CAS sets this variable during authentication
            $sql = 'SELECT username, id FROM users WHERE username = ?';
            $result = $this->db->prepare($sql);
            $result->bind_param('s', $this->session['phpCAS']['user']);
            $result->execute();
            $result->store_result();
            $result->bind_result($uname, $id);
            $result->fetch();

            if ($result->num_rows() == 0) {
                $this->savetodebug('No ExamSys account found for CAS user');
                $authobj->fail($this->number);
            } else {
                $this->savetodebug('successful CAS authentication');
                $authobj->success($this->number, $id);
            }
            $result->close();
        } else {
            $this->savetodebug('failed CAS authentication');
            $authobj->fail($this->number);
        }

        return $authobj;
    }
}
