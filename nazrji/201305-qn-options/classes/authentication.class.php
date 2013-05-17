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
 * Authentication routine which permits staff and student access to a page.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once $cfg_web_root . '/classes/lookup.class.php';
require_once $cfg_web_root . '/classes/userutils.class.php';

define('ROGO_AUTH_OBJ_FAILED', 0);
define('ROGO_AUTH_OBJ_SUCCESS', 1);
define('ROGO_AUTH_OBJ_LOOKUPONLY', 2);

/*
 * Rogos main authentication stack and plugin system
 */
class Authentication {

  private $userid;
  private $password;
  private $db, $configObj;
  private $config;
  public $returndata;
  public $debug;
  public $success;
  public $form;
  public $authPluginObj;

  public $username;

  public $successfullauthmodule;

  private $callbackregister;
  private $callbackregisterdata;

  public $authinfo;

  public $session;
  public $request;

  public $impliments_api_auth_version = 1;

  public $callbacktypes = array('init', 'lookupuser', 'preauth', 'auth', 'postauth', 'postauthsuccess', 'postauthfail', 'displaystdform', 'displayerrform', 'getauthobj', 'sessionstore');

  public $initobj, $lookupuserobj, $preauthobj, $authobj, $postauthobj, $postauthsuccesobj, $postauthfailobj, $displaystdformobj, $displayerrformobj, $getauthobj, $sessionstoreobj;


  function __construct(&$configObj, &$db, &$request, &$session) {

    $this->db = & $db;
    $this->configObj = & $configObj;

    $this->request = & $request;
    $this->session = & $session;

    $this->debug = array();

    if ($this->load_config()) {
      //if the config is ok setup the auth stack
      $this->setup();
    }
  }

  /*
   * verify the config file contains vlaid authentication settings
   *
   * @return bool
   */
  private function load_config() {
    $config_ok = true;

    $notice = UserNotices::get_instance();

    $this->config = $this->configObj->getbyref('authentication');

    if (!isset($this->config)) {
      global $string;
      $notice->display_notice_and_exit($this->db, $string['NoAuthenticationConfigured'], $string['NoAuthenticationConfiguredmessage'], $string['NoAuthenticationConfiguredmessage'], '../artwork/software_64.png', '#C00000');
      $config_ok = false;
    }

    $this->debug[] = 'Loaded Config for authentication';

    return $config_ok;
  }

  /*
   *  Parse the config and register the relevant callbacks in the auth plugins
   */
  private function setup() {
    $notfound = true;
    foreach ($this->config as $opt) {
      if ($opt[0] === 'alreadyloggedin') {
        $notfound = false;
        break;
      }
    }

    if ($notfound === true) {
      array_unshift($this->config, array('alreadyloggedin', array('timeout' => 0), 'Internal Authentication'));
    }

    // get form data here?
    $this->form['std'] = new stdClass();
    if (isset($this->request['rogo-login-form-std'])) {

      $this->form['std']->username = $this->request['ROGO_USER'];
      $this->form['std']->password = '***HIDDEN***';
      $this->debug[] = 'Standard form data found - Storing in object ' . var_export($this->form, true);
      $this->form['std']->password = $this->request['ROGO_PW'];
    }

    if (!isset($this->session['authenticationObj']['attempt'])) {
      $this->session['authenticationObj']['attempt'] = 0;
      $this->debug[] = 'Creating SESSION attempt data';
    }

    foreach ($this->config as $number => $auth) {
      $authtype = $auth[0];
      $authtype1 = $authtype . '_auth';
      $settings = $auth[1];
      $name = $auth[2];

      //TODO this knackers unit testing ERROR Nesting level too deep -  recursive dependency?
      //$this->debug[] = "Loading auth #$number with Type:$authtype Settings:" . str_replace("\n", "\n", var_export($settings, true));
      $this->returndata[$number] = new authtypereturn();
      $this->authinfo[$number] = array($name => $authtype);

      $object = new stdClass();
      $object->db =& $this->db;
      $object->calling_object =& $this;
      $object->form =& $this->form;
      $object->settings = $settings;

      if (!isset($settings['mockclass'])) {
        require_once $this->configObj->get('cfg_web_root') . 'plugins/auth/' . $authtype . '.class.php';
        $this->authPluginObj[$number] = new $authtype1($number, $name, $this->impliments_api_auth_version);
      } else {
        $this->authPluginObj[$number] = & $settings['mockclass'];
      }

      $res = $this->authPluginObj[$number]->apicheck();
      if ($res === false) {
        $this->debug[] = '********* Disabled module #' . $number . ':' . $name . ' as it implements an old a version of the api. *********';
      } else {
        $this->authPluginObj[$number]->init($object);

        $this->debug[] = "Running Registering callback routines for #$number";

        $callbacks = $this->authPluginObj[$number]->register_callback_routines();
        foreach ($callbacks as $callbackitem) {
          if (!isset($callbackitem[4])) {
            $callbackitem[4] = false;
          }
          $this->register_callback($callbackitem[0], $callbackitem[1], $callbackitem[2], $callbackitem[3], $callbackitem[4]);
        }
        $this->append_auth_object_debug($number);
      }
    }

    $initobj = new stdClass();

    if (isset($this->callbackregister['init'])) {
      foreach ($this->callbackregister['init'] as $number => $callback) {
        $initobj = call_user_func_array($callback, array($initobj));
        $objid = key($this->callbackregisterdata['init'][$number]);
        $this->append_auth_object_debug($objid);
      }
    }
  }

  function register_callback_section($section) {
    foreach ($section as $addition) {
      if (!in_array($addition, $this->callbacktypes)) {
        $this->callbacktypes[] = $addition;
      }
    }
  }

  function register_callback($callback, $section, $number, $name, $insert = false) {
    if (!in_array($section, $this->callbacktypes) or !is_callable($callback)) {
      //attempting to register callback to invalid section
      //maybe log name of function as well?
      $this->debug[] = 'register_callback FAILED ' . $section . ' from ' . get_class($callback[0]) . ' id:' . $number . ' with name:' . $name; // . var_export($callback,true);
      $this->authPluginObj[$number]->set_error($string['Authentication_callback_failure1'] . "($section)" . $string['Authentication_callback_failure2'] . " ($callback[1])");

      return false;
    }
    $this->debug[] = 'register_callback success ' . $section . ' from ' . get_class($callback[0]) . ' id:' . $number . ' with name:' . $name; // . var_export($callback,true);
    if ($insert == true) {
      array_unshift($this->callbackregister[$section], $callback);
      array_unshift($this->callbackregisterdata[$section], array($number => $name));
    } else {
      $this->callbackregister[$section][] = $callback;
      $this->callbackregisterdata[$section][] = array($number => $name);

    }

    return true;
  }

  function get_callback($section) {
    return array(&$this->callbackregister[$section], &$this->callbackregisterdata[$section]);
  }

  /*
   * Disply the standard Rogo login form
   */
  function display_std_form($string) {
    $displaystdformobj = new stdClass();

    if (isset($this->callbackregister['displaystdform'])) {
      foreach ($this->callbackregister['displaystdform'] as $number => $callback) {
        $displaystdformobj = call_user_func_array($callback, array($displaystdformobj));
        $objid = key($this->callbackregisterdata['displaystdform'][$number]);
        $this->append_auth_object_debug($objid);
      }
    }

    $override = $this->configObj->get('cfg_web_root') . '/config/login_form.php';
    $this->debug[] = 'Display form';
    if (file_exists($override)) {
      require $override;
    } else {
      require $this->configObj->get('cfg_web_root') . '/include/login_form.php';
    }
  }

  function display_error_form($display = true) {
    $override = $this->configObj->get('cfg_web_root') . '/config/login_error_form.php';

    $displayerrformobj = new stdClass();
    $displayerrformobj->override =& $override;

    if (isset($this->callbackregister['displayerrform'])) {
      foreach ($this->callbackregister['displayerrform'] as $number => $callback) {
        $displayerrformobj = call_user_func_array($callback, array($displayerrformobj));
        $objid = key($this->callbackregisterdata['displayerrform'][$number]);
        $this->append_auth_object_debug($objid);
      }
    }

    $this->debug[] = 'Display error form & reset attempt count';
    $this->session['authenticationObj']['attempt'] = 0;
    if ($display) {
      if (file_exists($override)) {
        require $override;
      } else {
        require $this->configObj->get('cfg_web_root') . '/include/login_error_form.php';
      }
    }
  }

  /**
   * @return bool if authentication was successful
   */
  function do_authentication($string) {
    $this->success = false;
    $this->debug[] = 'Starting authentication';

    $preauthobj = new stdClass();


    if (isset($this->callbackregister['preauth'])) {
      foreach ($this->callbackregister['preauth'] as $number => $callback) {
        $preauthobj = call_user_func_array($callback, array($preauthobj));
        $objid = key($this->callbackregisterdata['preauth'][$number]);
        $this->append_auth_object_debug($objid);
      }
    }

    $authobj = new authobjreturn();

    if (isset($this->callbackregister['auth'])) {
      foreach ($this->callbackregister['auth'] as $number => $callback) {
        $authobj = call_user_func_array($callback, array($authobj));
        $objid = key($this->callbackregisterdata['auth'][$number]);
        $this->append_auth_object_debug($objid);
        if ($authobj->returned === ROGO_AUTH_OBJ_SUCCESS) {
          $this->success = true;
          $this->userid = $authobj->rogoid;
          if (isset($authobj->username) and $authobj->username!='') {
            $this->username = $authobj->username;
          }
          $this->debug[] = '******* Rogo ID is:: ' . $this->userid . " from object $objid:" . $this->callbackregisterdata['auth'][$number][$objid] . ' *******';
          $this->successfullauthmodule[] = $objid;

        } elseif ($authobj->returned === ROGO_AUTH_OBJ_LOOKUPONLY) {
          $this->debug[] = '* User authenticated but no matching rogo id found, attempting to lookup the user with info supplied from module *';

          //lookupuser
          $lookup = Lookup::get_instance($this->configObj, $this->db);

          //$authobj->data contains lookup info;
          $data=new stdClass();
          $data->lookupdata = clone $authobj->data;
          $info = $lookup->userlookup($data);

          $lookupdebug = $lookup->debug_as_array();
          foreach ($lookupdebug as $line) {
            $this->debug[] = 'Lookup Debug: ' . $line;
          }

          //minimum fields to create an new user username
          $createuser = true;
          $authentication_fields_required_to_create_user = $this->configObj->get('authentication_fields_required_to_create_user');
          if (!is_null($authentication_fields_required_to_create_user)) {
            foreach ($authentication_fields_required_to_create_user as $value) {
              if (!isset($info->lookupdata->$value)) {
                $createuser = false;
                $this->debug[] = 'Not creating user as the ' . $value . ' field is missing';
              }
            }
          }
          if (isset($info->lookupdata->disabled) and $info->lookupdata->disabled == true) {
            $createuser = false;
          }

          if (isset($info->lookupdata->multiple) and $info->lookupdata->multiple == true) {
            $createuser = false;
          }

          if ($createuser == true) {
            $this->debug[] = 'Going to try and create new user';
            // $this->debug[] = 'Data is: ' . var_export($info->lookupdata, true);
            $arraycheck = array('username', 'title', 'firstname', 'surname', 'email', 'coursecode', 'gender', 'yearofstudy', 'role', 'studentID', 'school', 'coursetitle', 'initials');
            foreach ($arraycheck as $itemcheck) {
              if (!isset($info->lookupdata->$itemcheck)) {
                $info->lookupdata->$itemcheck = '';
              }
            }
            $newuserid = UserUtils::create_extended_user($info->lookupdata->username, $info->lookupdata->title, $info->lookupdata->firstname, $info->lookupdata->surname, $info->lookupdata->email, $info->lookupdata->coursecode, $info->lookupdata->gender, $info->lookupdata->yearofstudy, $info->lookupdata->role, $info->lookupdata->studentID, $this->db, $info->lookupdata->school, $info->lookupdata->coursetitle, $info->lookupdata->initials, $this->form['std']->password);
            if ($newuserid !== false) {
              //new account created
              $authobj->success($objid, $newuserid);
              $this->success = true;
              $this->userid = $authobj->rogoid;
              $this->debug[] = '******* Rogo ID is:: ' . $this->userid . " after a user lookup from object $objid:" . $this->callbackregisterdata['auth'][$number][$objid] . ' *******';
            }
          }

        }

        if (($this->success and (($this->authPluginObj[$objid]->get_settings('dont_break_on_success') === false) or (($this->authPluginObj[$objid]->get_settings('dont_break_on_success') !== false) and !$this->authPluginObj[$objid]->get_settings('dont_break_on_success'))))) {
          break;
        }
      }
    }

    $postauthobj = new stdClass();
    $postauthobj->authobj = $authobj;
    if (isset($this->callbackregister['postauth'])) {
      foreach ($this->callbackregister['postauth'] as $number => $callback) {
        $postauthobj = call_user_func_array($callback, array($postauthobj));
        $objid = key($this->callbackregisterdata['postauth'][$number]);
        $this->append_auth_object_debug($objid);
      }
    }

    if ($this->success === false) {
      //failed
      $postauthfailobj = new postauthfailreturn();
      $postauthfailobj->authobj = $authobj;
      $postauthfailobj->postauthobj = $postauthobj;

      $this->session['authenticationObj']['attempt']++;
      if (isset($this->callbackregister['postauthfail'])) {
        foreach ($this->callbackregister['postauthfail'] as $number => $callback) {
          $postauthfailobj = call_user_func_array($callback, array($postauthfailobj));
          $objid = key($this->callbackregisterdata['postauthfail'][$number]);
          $this->append_auth_object_debug($objid);
          $this->debug[] = 'parameters after running ' . var_export($this->postauthfailobj, true);
          if (isset($postauthfailobj->callback)) {
            $postauthfailobj = call_user_func_array($postauthfailobj->callback, array($postauthfailobj));
            if ($postauthfailobj->exit === true) {
              $notice = UserNotices::get_instance();
              $notice->exit_php();

              return false; //just incase and needed for testing
            }
          }

          if ($postauthfailobj->form == 'err') {
            $this->display_error_form();
            if (!is_null($this->configObj->get('display_auth_debug')) and $this->configObj->get('display_auth_debug') == true) {
              $this->display_debug();
            }
            if ($postauthfailobj->exit === true) {
              $notice = UserNotices::get_instance();
              $notice->exit_php();

              return false; //just incase and needed for testing
            }
          }

          if ($postauthfailobj->form == 'std') {
            $this->display_std_form($string);
            if (!is_null($this->configObj->get('display_auth_debug')) and $this->configObj->get('display_auth_debug') == true) {
              $this->display_debug();
            }
            if ($postauthfailobj->exit === true) {
              $notice = UserNotices::get_instance();
              $notice->exit_php();

              return false; //just incase and needed for testing
            }
          }

          if (isset($postauthfailobj->url)) {
            header("Location: {$postauthfailobj->url}");
            if ($postauthfailobj->exit === true) {
              $notice = UserNotices::get_instance();
              $notice->exit_php();

              return false; //just incase and needed for testing
            }
          }


          if ($postauthfailobj->stop === true) {
            break;
          }
        }

        //failed but no callbacks or callbacks finished
        $notice = UserNotices::get_instance();
        $notice->display_notice_and_exit(
          $this->db,
          $string['Authentication_issue1'],
          sprintf($string['Authentication_issue2'], $this->configObj->get('support_email'), $this->configObj->get('support_email'), $this->configObj->get('support_email'), $this->debug_to_string()),
          sprintf($string['Authentication_issue2'], $this->configObj->get('support_email'), $this->configObj->get('support_email'), $this->configObj->get('support_email'), $this->debug_to_string()),
          '/artwork/user_info_48.png', '#C00000',
          true,
          true);
      }
    }

    if ($this->success !== true) {
      $this->debug[] = 'Success is not TRUE or FALSE';

      //something went very wrong;
      return false;
    }

    // the auth has succeeded as above will stop it if its not true
    $postauthsuccessobj = new stdClass();
    $postauthsuccessobj->authobj = $authobj;
    $postauthsuccessobj->postauthobj = $postauthobj;
    $postauthsuccessobj->userid =& $this->userid;

    if (isset($this->callbackregister['postauthsuccess'])) {
      foreach ($this->callbackregister['postauthsuccess'] as $number => $callback) {
        $this->debug[] = 'run authsuccess callback ' . get_class($callback[0]) . ':' . $callback[1];
        $postauthsuccessobj = call_user_func_array($callback, array($postauthsuccessobj));
        $objid = key($this->callbackregisterdata['postauthsuccess'][$number]);
        $this->append_auth_object_debug($objid);
      }
    }

    // need to save some data for allready logged in authentication
    $this->store_data_in_session();
  }

  function store_data_in_session() {
    $this->session['authenticationObj']['loggedin']['userid'] = $this->get_userid();
    $this->session['authenticationObj']['loggedin']['time'] = time();
    $this->session['authenticationObj']['attempt'] = 0;
  }


  function get_userid() {
    return $this->userid;
  }

  function get_password() {
    return $this->form['std']->password;
  }

  function get_username() {
    if(isset($this->username) and $this->username!='') {
      return $this->username;
    }
    return false;
  }

  function append_auth_object_debug($number, $desc = '') {
    $new_messages = $this->authPluginObj[$number]->get_new_debug_messages();
    foreach ($new_messages as $key => $value) {
      $info1 = $this->authinfo[$number];
      $info = key($info1) . ':' . current($info1);
      $this->debug[] = "authObj($info)[$number:$key]:$desc: $value";
    }
  }

  function display_debug() {
    var_dump($this->debug);
  }

  function debug_to_string() {
    return implode('<br />', $this->debug);
  }

  function get_auth_obj(&$getauth) {
    global $string;
    if (!is_object($getauth)) {

      $getauthobj->userid = $getauth;
      $getauthobj->userObj = new UserObject($this->configObj, $this->db);
      $getauthobj->userObj->load($getauth);
    } else {
      $getauthobj = & $getauth;
      if (!isset($getauthobj->userObj)) {

        //serious error
        $getauthobj->userObj = new UserObject($this->configObj, $this->db);
      }
      if ($this->get_userid() < 1) {
        $notice = UserNotices::get_instance();
        $notice->display_notice_and_exit(
          $this->db,
          $string['Authentication_notloggedin1'],
          sprintf($string['Authentication_notloggedin2'], $this->configObj->get('support_email'), $this->configObj->get('support_email'), $this->debug_to_string()),
          sprintf($string['Authentication_notloggedin2'], $this->configObj->get('support_email'), $this->configObj->get('support_email'), $this->debug_to_string()),
          '/artwork/user_info_48.png',
          '#C00000',
          true,
          true);

      }
      $getauthobj->userObj->load($this->get_userid());
    }
    //$uID = $this->get_userID();

    if (isset($this->callbackregister['getauthobj'])) {
      foreach ($this->callbackregister['getauthobj'] as $number => $callback) {
        $this->debug[] = 'run getauthobj callback ' . get_class($callback[0]) . ':' . $callback[1];
        $getauthobj = call_user_func_array($callback, array($getauthobj));
        $objid = key($this->callbackregisterdata['getauthobj'][$number]);
        $this->append_auth_object_debug($objid);
      }
    }

    return $getauthobj->userObj;
  }


  function version_info($formatted = false, $advanced = false) {
    $data = new stdClass();
    $data->plugins = array();
    foreach ($this->authPluginObj as $authobj) {
      $data->plugins[] = $authobj->get_info();
    }
    $data->callbacks = array();
    foreach ($this->callbacktypes as $value) {

      if (isset($this->callbackregister[$value])) {
        foreach ($this->callbackregister[$value] as $order => $callitem) {
          $dat = new stdClass();
          $dat->functionname = $callitem[1];
          $callbackdat = $this->callbackregisterdata[$value][$order];
          $dat->plugindescname = current($callbackdat);
          $dat->pluginconfigid = key($callbackdat);
          $data->callbacks[$value][] = $dat;
        }

      } else {
        $data->callbacks[$value] = array();
      }

    }


    if ($formatted == false) {
      return $data;
    }
    if ($advanced == false) {
      //basic view

      $return_data = '';
      $error = false;
      foreach ($data->plugins as $number => $item) {
        if (count($item->error) > 0) {
          $error = true;
        }
        if ($number != 0) {
          $return_data .= $number . '. ' . $item->name . ' <i>(' . $item->classname . ')</i><br />';
        }

      }

      if ($error) {
        $return_data = '<div style="background-color: #cc0000;">' . $return_data . '</div>';
      }

    } else {
      //advanced view

    }

    return $return_data;
  }

  /**
   * Check if the authentication stack is using a plugin of a given type
   * @param  string  $type The class name of the plugin for which to check
   * @return boolean       True if the plugin is loaded in the current authentication stack
   */
  function has_plugin_type($type) {
    $found = false;

    $data->plugins = array();
    foreach ($this->authPluginObj as $authobj) {
      $info = $authobj->get_info();
      if ($info->classname == $type) {
        $found = true;
        break;
      }
    }

    return $found;
  }
}

class authtypereturn {
  public $success, $rogoid, $url, $message;

  function __construct() {
    $this->debug = array();
    $this->debugpointer = 0;
    $this->success = false;
    $this->rogoid = 0;
    $this->url = '';
    $this->message = '';
  }

}

/*
 * authobjreturn is the object passed to the auth plugins auth callback
 * and holds the current state of the auth
 */
class authobjreturn {
  public $returned;
  public $returneds;
  public $rogoid;
  public $rogoids;
  public $data;
  public $datas;
  public $statuses;
  public $username;

  function __construct() {
    $this->returned = ROGO_AUTH_OBJ_FAILED;
    $this->returneds = array();
    $this->statuses = array();
    $this->rogoid = 0;
    $this->rogoids = array();
    $this->data = new stdClass();
    $this->datas = array();

  }

  /*
   * set the authobjreturn objet to fail state
   */
  function fail($number) {
    $this->returned = ROGO_AUTH_OBJ_FAILED;
    $this->returneds[] = $this->returned;
    $this->statuses[$number] = $this->returned;
    $this->rogoid = 0;

  }

  /*
   * set the authobjreturn objet to success state
   */
  function success($number, $rogoid) {
    $this->rogoid = $rogoid;
    $this->rogoids[] = $this->rogoid;
    $this->returned = ROGO_AUTH_OBJ_SUCCESS;
    $this->returneds[] = $this->returned;
    $this->statuses[$number] = $this->returned;

  }

  function lookupmissing($number, $data) {
    $this->rogoid = 0;
    $this->returned = ROGO_AUTH_OBJ_LOOKUPONLY;
    $this->returneds[] = $this->returned;
    $this->statuses[$number] = $this->returned;
    $this->data = $data;
    $this->datas[] = $this->data;
  }

}


class postauthfailreturn extends stdClass {
  public $attempt;
  public $form;
  public $url;
  public $callback;
  public $stop;
  public $exit;

  function __construct() {
    $this->attempt = $_SESSION['authenticationObj']['attempt'];
    $this->stop = false;
    $this->exit = false;
  }
}

class displaystdformobjbutton extends stdClass {
  public $pretext;
  public $posttext;
  public $type;
  public $name;
  public $value;
  public $style;

  function __construct() {
    $this->pretext = '';
    $this->posttext = '';
    $this->type = '';
    $this->name = '';
    $this->value = '';
    $this->style = '';
  }

}

class displaystdformobjfield extends stdClass {
  public $description;
  public $type;
  public $name;
  public $defaultvalue;

  function __construct() {
    $this->description = '';
    $this->type = '';
    $this->name = '';
    $this->defaultvalue = '';

  }

}