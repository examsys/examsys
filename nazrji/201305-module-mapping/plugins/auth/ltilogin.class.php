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
 * The lti login authentication function.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once $configObject->get('cfg_web_root') . 'LTI/ims-lti/UoN_LTI.php';
require_once 'outline_authentication.class.php';
require_once $configObject->get('cfg_web_root') . 'classes/lti_integration.class.php';

class ltilogin_auth extends outline_authentication {


  public $impliments_api_auth_version = 1;
  public $version = 0.9;

  protected $lti;

  function init($object) {
    parent::init($object);
    $this->lti = UoN_LTI::get_instance();

    $this->lti->init_lti0($this->db);
    $this->savetodebug('Starting LTI');
    $this->lti->init_lti();
    if (!isset($this->lti_i)) {
      $this->lti_i = lti_integration::load();
    }
  }

  function register_callback_routines() {
    $callbackarray[] = array(array($this, 'auth'), 'auth', $this->number, $this->name);
    $callbackarray[] = array(array($this, 'registeruserwithlti'), 'postauthsuccess', $this->number, $this->name);
    $callbackarray[] = array(array($this, 'displaystdform'), 'displaystdform', $this->number, $this->name);

    return $callbackarray;
  }


  function auth($authobj) {
    $this->retdata =& $authobj;

    if ($this->lti->valid !== true) {
      $this->savetodebug('Not valid LTI Launch: ' . $this->lti->message);
      $authobj->fail($this->number);

      return $authobj;
    }

    $this->savetodebug('Starting to lookup user');
    $returned = $this->lti->lookup_lti_user();

    $this->savetodebug('Data returned from lti lookup was: ' . var_export($returned, true));

    if ($returned !== false) {

      $sql="SELECT username from users where id = ?";
      $result=$this->db->prepare($sql);
      $result->bind_param('i', $returned[0]);
      $result->execute();
      $result->store_result();
      $result->bind_result($username);

      $authneeded = $this->lti_i->user_time_check($returned[1], $username);
      if ($authneeded === true) {
        $this->session['authenticationobj']['ltilogin']['needsreuserlookup'] = true;
        $authobj->fail($this->number);
        return $authobj;
      }

      $this->retdata->success = true;
      $this->retdata->form = 'std';
      $this->rogoid = $returned[0];
      $this->retdata->rogoid = & $this->rogoid;
      $this->retdata->url = '';
      $authobj->retdata = & $this->retdata;
      $this->savetodebug('LTI lookup successful');

      $authobj->success($this->number, $this->rogoid);

      return $authobj;
    }

    //TODO check logic in real world situation
    if ((!isset($authobj->manualsignup) or (isset($this->settings['lti_lookup_set_authobj_manualsignup_override']) and $this->settings['lti_lookup_set_authobj_manualsignup_override'] == true)) and (isset($this->settings['lti_lookup_set_authobj_manualsignup']) and $this->settings['lti_lookup_set_authobj_manualsignup'] == true)) {
      $authobj->manualsignup = true;
      $this->savetodebug('Setting the manualsignup to true on authobj');

    }

    if (isset($this->settings['lti_lookup_failasmissing']) and $this->settings['lti_lookup_failasmissing'] == true) {
      $data = new stdClass();
      $this->savetodebug('Returning status as missing info');

      $ltinamefield = 'fullname';
      $ltiemailfield = 'email';
      $ltiidfield = 'ltiid';
      $lticonsumerkeyfield = 'lticonsumerkey';
      $ltiinstructorfield = 'ltiinstructor';
      $ltifromltifield = 'ltienabled';
      if (isset($this->settings['lti_lookup_name_field'])) {
        $ltinamefield = $this->settings['lti_lookup_name_field'];
      }
      if (isset($this->settings['lti_lookup_email_field'])) {
        $ltiemailfield = $this->settings['lti_lookup_email_field'];
      }
      if (isset($this->settings['lti_lookup_ltiid_field'])) {
        $ltiidfield = $this->settings['lti_lookup_ltiid_field'];
      }
      if (isset($this->settings['lti_lookup_lticonsumerkey_field'])) {
        $lticonsumerkeyfield = $this->settings['lti_lookup_lticonsumerkey_field'];
      }
      if (isset($this->settings['lti_lookup_ltiinstructor_field'])) {
        $ltiinstructorfield = $this->settings['lti_lookup_ltiinstructor_field'];
      }
      if (isset($this->settings['lti_lookup_disabledcreateuser'])) {
        $data->disablecreateuser = $this->settings['lti_lookup_disabledcreateuser'];
      }
      if (isset($this->settings['lti_lookup_fromlti_field'])) {
        $ltifromltifield = $this->settings['lti_lookup_fromlti_field'];
      }

      $this->savetodebug('setting return fields  ' . "ltinamefield:$ltinamefield ltiemailfield:$ltiemailfield ltiidfield:$ltiidfield lticonsumerkeyfield:$lticonsumerkeyfield ltiinstructorfield:$ltiinstructorfield");

      if (isset($this->settings['lti_lookup_set_session_needsuserlookup']) and $this->settings['lti_lookup_set_session_needsuserlookup'] == true) {
        $_SESSION['authenticationobj']['ltilogin']['needsuserlookup'] = true;
      }

      $data->$ltifromltifield = true;
      $data->$ltinamefield = $this->lti->getUserName();
      $data->$ltiemailfield = $this->lti->getUserEmail();
      $data->$ltiidfield = $this->lti->getUserKey();
      $data->$lticonsumerkeyfield = $this->lti->getConsumerKey();
      $data->$ltiinstructorfield = $this->lti->isInstructor();


      $authobj->lookupmissing($this->number, $data);

      return $authobj;
    }

    if (isset($this->settings['lti_lookup_skipusesignup']) and $this->settings['lti_lookup_skipusesignup'] == true) {
      $this->session['authenticationobj']['ltilogin']['needsuserlookup'] = false;
    } else {
      //set session to be needing user lookup later
      $this->session['authenticationobj']['ltilogin']['needsuserlookup'] = true;
    }

    // lti valid but no user id associated with it.
    // need to authenticate the user but ignore lti & already logged in etc

    return $authobj;


  }

  function registeruserwithlti($postauthsuccessobj) {

    if (isset($_SESSION['authenticationobj']['ltilogin']['needsreuserlookup']) and $_SESSION['authenticationobj']['ltilogin']['needsreuserlookup'] === true) {
      $this->lti->update_lti_user();
      $_SESSION['authenticationobj']['ltilogin']['needsreuserlookup'] = false;
      return $postauthsuccessobj;
    }

    if (!isset($_SESSION['authenticationobj']['ltilogin']['needsuserlookup']) or $_SESSION['authenticationobj']['ltilogin']['needsuserlookup'] === false) {
      return $postauthsuccessobj;
    }
    $this->savetodebug('storing rogo userid against lti user');
    $rogoid = $postauthsuccessobj->userid;
    $this->lti->add_lti_user($rogoid);
    $_SESSION['authenticationobj']['ltilogin']['needsuserlookup'] = false;

    return $postauthsuccessobj;
  }

  function displaystdform($displaystdformobj) {
    global $string;
    if (isset($this->session['authenticationobj']['ltilogin']['needsreuserlookup']) and  $this->session['authenticationobj']['ltilogin']['needsreuserlookup'] === true) {

      $message = new stdClass();
      $message->pretext = '';
      $message->posttext = '';

      $message->content = $string['authentication_lti_reauthmessage'];
      $displaystdformobj->messages[] = $message;
      $displaystdformobj->replace = true;
    }

    if (isset($this->session['authenticationobj']['ltilogin']['needsuserlookup']) and  $this->session['authenticationobj']['ltilogin']['needsuserlookup'] === true) {

      $message = new stdClass();
      $message->pretext = '';
      $message->posttext = '';

      $message->content = $string['authentication_lti_authmessage'];
      $displaystdformobj->messages[] = $message;
      $displaystdformobj->replace = true;
    }

    return $displaystdformobj;
  }

}
