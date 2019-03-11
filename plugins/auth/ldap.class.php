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
 *
 * The ldap authentication function.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
require_once 'outline_authentication.class.php';
global $language;
$cfg_web_root = $configObject->get('cfg_web_root');
include_once $cfg_web_root . "lang/{$language}/include/common.php";

class ldap_auth extends outline_authentication {

  public $impliments_api_auth_version = 1;
  public $version = 0.9;

  private $createnewuserassociation = false;

  function register_callback_routines() {
    $callbackarray[] = array(array($this, 'auth'), 'auth', $this->number, $this->name);
    $callbackarray[] = array(array($this, 'failauth'), 'postauthfail', $this->number, $this->name);
    $callbackarray[] = array(array($this, 'createnewuserassociation'), 'postauthsuccess', $this->number, $this->name);
    $callbackarray[] = array(array($this, 'errordisp'), 'displayerrform', $this->number, $this->name);

    return $callbackarray;
  }

  function createnewuserassociation($postauthsuccessobj) {
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

  function errordisp($displayerrformobj) {
    global $string;

    $this->savetodebug('adding ldap notice to error screen');
    $displayerrformobj->li[] = $string['tsonldap'];

    return $displayerrformobj;
  }

  function failauth($postauthfailreturn) {
    $this->savetodebug('Fail function passed ' . var_export($postauthfailreturn, true));

    //default behaviour is to display username/password form
    $postauthfailreturn->form = 'std';
    $postauthfailreturn->exit = true;

    if ((isset($this->settings['displayfailuremessagenumber']) and $postauthfailreturn->attempt >= $this->settings['displayfailuremessagenumber']) or (!isset($this->settings['displayfailuremessagenumber']) and $postauthfailreturn->attempt > 3)) {
      $this->savetodebug('Requisite number of fail attempts so display error form');
      $postauthfailreturn->form = 'err';
      $postauthfailreturn->exit = true;
    }

    if (isset($this->settings['continueonfail'])) {
      $this->savetodebug('Setting to carry on despite setting things');
      $postauthfailreturn->exit = false;
      $postauthfailreturn->stop = false;
    }
    $this->savetodebug('post run ' . var_export($postauthfailreturn, true));

    return $postauthfailreturn;

  }


  function auth($authobj) {
    global $string;

    $this->retdata =& $authobj;
    $this->savetodebug('Authing');
    extract($this->settings);

    if (!isset($this->form['std']->username) or !isset($this->form['std']->username) or $this->form['std']->username == '' or $this->form['std']->password == '') {
      //return not sucessfull do not try
      $this->savetodebug('Check 1 blank entries');

      $authobj->fail($this->number);
      $authobj->message = 'Not valid entry for username or password';

      return $authobj;
    }
    
    if (isset($ldap_port)) {
      $ldap = ldap_connect($ldap_server, $ldap_port);
    } else {
      $ldap = ldap_connect($ldap_server);
    }
    
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
    
    if (isset($ldap_set_option)) {
      foreach($ldap_set_option as $ldap_opt_key => $ldap_opt_val) {
        ldap_set_option($ldap,$ldap_opt_key,$ldap_opt_val);
      }
    }
    if (!isset($ldap_bind_rdn)) {
      $ldap_bind_rdn      = null;
      $ldap_bind_password = null;
    }

    if (ldap_bind($ldap, $ldap_bind_rdn, $ldap_bind_password)) {
      $this->savetodebug('Sucessfull initial bind to ldap server');
      if (is_array($ldap_search_dn)) {
        $ldpcount = count($ldap_search_dn);
        $ldapconn = array();
        for($i=0; $i<$ldpcount; $i++) {
          $ldapconn[] = $ldap;
        }
      } else {
        $ldapconn = $ldap;
      }
      if (!($search = @ldap_search($ldapconn, $ldap_search_dn, $ldap_user_prefix . $this->form['std']->username))) {
        $this->savetodebug($string['ldapservernosearch']);
        $authobj->fail($this->number);

        return $authobj;
      } else {
        $info = ldap_get_entries($ldap, $search);

        if ($info['count'] == 1) {
          $this->savetodebug('Found user in ldap');
          $dn = $info[0]['dn'];
        } else {
          $this->savetodebug('<strong>' . $string['noldapaccount'] . '</strong>');
          $authobj->fail($this->number);

          return $authobj;
        }
      }
      
      $configObject = Config::get_instance();
      if (@ldap_bind($ldap, $dn, iconv($configObject->get('cfg_page_charset'), 'UTF-8', $this->form['std']->password))) {

        $this->savetodebug('Successfully bound to ldap as the user with their password');
        ldap_unbind($ldap);

        $this->savetodebug('Now looking up userid in table from username');
        if(!isset($sql_extra)) {
          $sql_extra = '';
        }
        $sql = "SELECT $username_col AS username, $id_col AS id FROM $table WHERE $username_col = ? $sql_extra";
        $result = $this->db->prepare($sql);
        $result->bind_param('s', $this->form['std']->username);
        $result->execute();
        $result->store_result();
        $this->savetodebug('sql is:' . $sql . ' with parameter:' . $this->form['std']->username);

        $result->bind_result($uname, $id);
        $result->fetch();

        $this->savetodebug('uname:' . $uname . ' id:' . $id);
        if ($result->num_rows() > 1) {
          // not unique match
          $this->savetodebug('Check 2 record number not = 1 no user or multiple user found in lookup');

          $authobj->fail($this->number);
          $authobj->message = 'Incorrect number of records returned';

          return $authobj;
        }

        if ($result->num_rows() == 0) {
          //lookup ok but no association to rogo

          $this->savetodebug('LDAP Record found but no local account');
          $data = new stdClass();
          if(!isset($this->settings['search_field'])) {
            $this->settings['search_field'] = 'username';
          }
          $data->{$this->settings['search_field']} = $this->form['std']->username;

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


        $this->savetodebug('Successfully authenticated on this module username=' . $this->form['std']->username . ' id:' . $id);

        //sucessfull internaldb authentication
        $authobj->success($this->number, $id);

        return $authobj;
      } else {
        $this->savetodebug($string['incorrectpassword']);
        $authobj->fail($this->number);

        return $authobj;
      }
    } else {
      $this->savetodebug('Could not Bind to ldap server');
      $authobj->fail($this->number);

      $this->set_error('Could not bind to ldap server');

      return $authobj;
    }

    return $authobj;
  }

}
