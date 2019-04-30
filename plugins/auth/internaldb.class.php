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
 * The internaldb authentication function.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
 
require_once 'outline_authentication.class.php';

class internaldb_auth extends outline_authentication {

  public $impliments_api_auth_version = 1;
  public $version = 1.0;

  private $updatable = false;

  function register_callback_routines() {
    $callbackarray[] = array(array($this, 'auth'), 'auth', $this->number, $this->name);
    $callbackarray[] = array(array($this, 'failauth'), 'postauthfail', $this->number, $this->name);
    $callbackarray[] = array(array($this, 'update_password'), 'postauthsuccess', $this->number, $this->name);
    $callbackarray[] = array(array($this, 'lookupuser'), 'lookupuser', $this->number, $this->name);
    //$callbackarray[] = array(array($this, 'errordisp'), 'displayerrform', $this->number, $this->name);

    return $callbackarray;
  }

  function failauth($postauthfailreturn) {
    $this->savetodebug('Fail function run');

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


    return $postauthfailreturn;

  }
  
  function errordisp($displayerrformobj) {
    global $string;
    $cfg = Config::get_instance();
    
    $this->savetodebug('adding forgotten password link ');
    $displayerrformobj->li[] = '<a href="' . $cfg->get('cfg_root_path') . '/users/forgotten_password.php">' . $string['forgottenpassword'] . '</a>' ;

    return $displayerrformobj;
  }
  
  function lookupuser($lookupuserobj) {

    if (!isset($lookupuserobj->username)) {
      $this->savetodebug('Lookup user has nothing to lookup');

    }
    extract($this->settings);
    $sql = "SELECT $username_col AS username, $passwd_col AS passwd, $id_col AS id FROM $table WHERE $username_col = ? AND user_deleted IS NULL";
    $result = $this->db->prepare($sql);
    $result->bind_param('s', $lookupuserobj->username);
    $result->execute();
    $result->store_result();
    $result->bind_result($uname, $pass, $id);
    while ($result->fetch()) {
      $datastore = new stdClass();
      $datastore->userid = $id;
      $datastore->uname = $uname;
      $lookupuserobj->results[] = $datastore;
      $this->savetodebug(var_export($datastore, true));
      $lookupuserobj->found = true;
    }

    return $lookupuserobj;
  }

  function auth($authobj) {
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

    $sql = "SELECT $username_col AS username, $passwd_col AS passwd, $id_col AS id, password_expire FROM $table WHERE $username_col = ? AND user_deleted IS NULL";
    $result = $this->db->prepare($sql);
    $result->bind_param('s', $this->form['std']->username);
    $result->execute();
    $result->store_result();

    $result->bind_result($uname, $pass, $id, $password_expire);

    if ($result->num_rows() !== 1) {
      // return not sucessfull either no user or multiple matches
      $this->savetodebug('Check 2 record number not = 1 no user or multiple user found');

      $this->retdata->fail($this->number);
      $this->retdata->message = 'Incorrect number of records returned';

      return $authobj;

    }
    $result->fetch();
    if (substr($pass, 0, 3) == '$1$') {
      $old_encrypt_type = 'MD5';
      $this->savetodebug('Using old encryption');
    } else {
      $old_encrypt_type = 'SHA-512';
    }

    $this->updatable = true;
    $encrypt_password = \encryp::encpw($this->settings['encrypt_salt'], $this->form['std']->username, $this->form['std']->password, $old_encrypt_type);

    $this->savetodebug('encrypted password strings ' . $encrypt_password . ':::' . $pass);

    if ($encrypt_password == $pass and (time() < $password_expire or $password_expire == '')) {
      if ($old_encrypt_type == 'MD5') { // Re-encrypt MD5 passwords using SHA-512.
        $this->savetodebug('Re Encrypting PW');
        $this->update_password();
      }
      $this->updatable = false;
      $this->savetodebug('Successfully authenticated on this module');

      //sucessfull internaldb authentication
      $this->retdata->success($this->number, $id);
      $this->retdata->message = 'Internal DB Correctly Authenticated';
      $this->retdata->postredirect = true;

      return $authobj;
    }
    if (!(time() < $password_expire )) {
      $this->savetodebug('Password Expired');
    } else {
      $this->savetodebug('Password not matching');
    }
    $authobj->fail($this->number);

    return $authobj;
  }

  function update_password($postauthsuccessobj = '') {
    $configObj = Config::get_instance();
    $passwordexpire = $configObj->get('core', 'system_password_expire');
    $this->savetodebug('Called update_password');
    if ($this->updatable === true and (!isset($this->settings['donotupdatepassword']) or (isset($this->settings['donotupdatepassword']) and $this->settings['donotupdatepassword'] !== true))) {
      if ($passwordexpire == null) {
        $days = 30;   // If there is no setting in the config file, default to 30 days.
      } else {
        $days = $passwordexpire;
      }
      $expire = time() + ($days * 24 * 60 * 60);
          
      $this->savetodebug('Updating Password');
      extract($this->settings);
      $encpw_details = \encryp::encpw($this->settings['encrypt_salt'], $this->form['std']->username, $this->form['std']->password);
      $stmt = $this->db->prepare("UPDATE $table SET $passwd_col = ?, password_expire = ? WHERE $username_col = ?");
      $stmt->bind_param('sis', $encpw_details, $expire, $this->form['std']->username);
      $stmt->execute();
      $stmt->close();
    } elseif ((isset($this->settings['donotupdatepassword']) and $this->settings['donotupdatepassword'] === true)) {
      $this->savetodebug('Not updating password due to settings flag');
    }
    return $postauthsuccessobj;
  }

}
