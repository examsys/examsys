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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package Authentication
 */

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

  /**
   * Called when the object is unserialised.
   */
  public function __wakeup() {
    // The serialised database object will be invalid,
    // this object should only be serialised during an error report,
    // so adding the current database connect seems like a waste of time.
    $this->db = null;
  }

	/**
	 * @param object $configObj - Configuration object
	 * @param object $db    		- Link to mysqli
	 * @param array $request    - Usually made up of $_REQUEST data
	 * @param array $session    - $_SESSION data passed in
	 *
	 */
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
   * Verify the config file contains vlaid authentication settings.
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
   *  Parse the config and register the relevant callbacks in the auth plugins.
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
      array_unshift($this->config, array('alreadyloggedin', array('timeout' => 0), 'Internal Authentication'));		// Add in 'already logged in' plugin so don't re-authenticate every page.
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

  /*
   * Add extra callbacks sections.
   * @param array $section - Array of callback types.
   */
  function register_callback_section($section) {
    foreach ($section as $addition) {
      if (!in_array($addition, $this->callbacktypes)) {
        $this->callbacktypes[] = $addition;
      }
    }
  }

  /*
   * Registers a single callback.
   * @param callable $callback 	- Contains function to run.
	 * @param string $section			- Which section to register itself into.
	 * @param int $number					- Internal identifier for the plugin.
	 * @param string $name				- Internal name of the plugin.
	 * @param bool $insert				- True  = insert at the top of the list, False = append to the end.
   */
  function register_callback($callback, $section, $number, $name, $insert = false) {
    global $string;
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

  /*
   * Get a list of available callbacks for the section.
	 * @param string $section	- Name of the section.
   */
	 function get_callback($section) {
    return array(&$this->callbackregister[$section], &$this->callbackregisterdata[$section]);
  }

  /*
   * Disply the standard Rogo login form
	 * @param string $string	- Language strings.
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

  /*
   * Display the standard Rogo login form
	 * @param bool $display	- True = display form after failing to log in, False = no form displayed but still runs callback routines.
   */
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
   * Custom error handler
	 * @param array $context	- Unused.
	 * @return array 					- List of the current object's variables.
   */
	 function error_handling($context = null) {
    return error_handling($this);
  }


  /**
	 * @param string $string	- Language strings.
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
          if (isset($authobj->username) and $authobj->username != '') {
            $this->username = $authobj->username;
          }
          $this->debug[] = '******* Rogo ID is:: ' . $this->userid . " from object $objid:" . $this->callbackregisterdata['auth'][$number][$objid] . ' *******';
          $this->successfullauthmodule[] = $objid;

        } elseif ($authobj->returned === ROGO_AUTH_OBJ_LOOKUPONLY) {
          $this->debug[] = '* User authenticated but no matching rogo id found, attempting to lookup the user with info supplied from module *';

          //lookupuser
          $lookup = Lookup::get_instance($this->configObj, $this->db);

          //$authobj->data contains lookup info;
          $data = new stdClass();
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
              if (!isset($info->lookupdata->$value) or(isset($info->lookupdata->$value) and $info->lookupdata->$value == '')) {

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
          } else {
            // Log not creating user and why
            $username = 'UNKNOWN';
            if (isset($this->form['std']->username)) {
              $username=$this->form['std']->username;
            }
            $userid = 0;
            $errfile = 'Authentication';
            $errline = 0;
            $errstr = 'Couldnt create user see variables for more info';
            $variables = array('lookup' => &$lookup, 'info' => &$info, 'authentication' => &$this);
            log_error($userid, $username, 'Application Warning', $errstr, $errfile, $errline, '', null, $variables, null);
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

              return false; //just in case and needed for testing
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

              return false; //just in case and needed for testing
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

              return false; //just in case and needed for testing
            }
          }

          if (isset($postauthfailobj->url)) {
            header("Location: {$postauthfailobj->url}");
            if ($postauthfailobj->exit === true) {
              $notice = UserNotices::get_instance();
              $notice->exit_php();

              return false; //just in case and needed for testing
            }
          }


          if ($postauthfailobj->stop === true) {
            break;
          }
        }

        //failed but no callbacks or callbacks finished
        $notice = UserNotices::get_instance();
         if (!is_null($this->configObj->get('display_auth_debug')) and $this->configObj->get('display_auth_debug') == true) {
            $msg = $string['Authentication_issue2'];
            $reason = $string['Authentication_issue2'];
        } else {
            $msg = $string['Authentication_issue2nodebug'];
            $reason = $string['Authentication_issue2nodebug'];
        }
        $contactemail = support::get_email();
        $notice->display_notice_and_exit(
          $this->db,
          $string['Authentication_issue1'],
          sprintf($msg, $contactemail, $contactemail, $contactemail, $this->debug_to_string()),
          sprintf($reason, $contactemail, $contactemail, $contactemail, $this->debug_to_string()),
          '/artwork/fingerprint_48.png', '#C00000',
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

    // If redirect is set, redirect user to same page but with GET
    if ($authobj->postredirect) {
      header('Location: ' . $_SERVER['REQUEST_URI'], true, 303);
      exit();
    }
  }

  /**
   * Stores data in the session
   */
  function store_data_in_session() {
    $this->session['authenticationObj']['loggedin']['userid'] = $this->get_userid();
    $this->session['authenticationObj']['loggedin']['time'] = time();
    $this->session['authenticationObj']['attempt'] = 0;
  }

  /**
   * Return the user ID.
   */
  function get_userid() {
    return $this->userid;
  }

  /**
   * Return the password as entered by the user.
   */
  function get_password() {
    return $this->form['std']->password;
  }

  /**
   * Return the username as entered by the user.
   */
  function get_username() {
    if (isset($this->username) and $this->username != '') {
      return $this->username;
    }
    return false;
  }

  /**
   * Adds information to debugging log.
	 * @param int $number		- Internal plugin identifier.
	 * @param string $desc	- Description of the internal plugin.
   */
  function append_auth_object_debug($number, $desc = '') {
    $new_messages = $this->authPluginObj[$number]->get_new_debug_messages();
    foreach ($new_messages as $key => $value) {
      $info1 = $this->authinfo[$number];
      $info = key($info1) . ':' . current($info1);
      $this->debug[] = "authObj($info)[$number:$key]:$desc: $value";
    }
  }

  /**
   * Display debugging log.
   */
  function display_debug() {
    var_dump($this->debug);
  }

  /**
   * Return debugging log.
	 * @return string - Debugging log.
   */
  function debug_to_string() {
    return implode('<br />', $this->debug);
  }

  /**
   * Returns a user object.
	 * @param object $getauth - Normally empty auth_obj but can be used to request a specific user.
	 * @return object - User object.
   */
	 function get_auth_obj(&$getauth) {
    global $string;
    if (!is_object($getauth)) {

      $getauthobj->userid = $getauth;
      $getauthobj->userObj = new UserObject($this->configObj, $this->db);
      $getauthobj->userObj->load($getauth);
    } else {
      $getauthobj = & $getauth;
			
      if (!isset($getauthobj->userObj)) {
        // Serious error - we have no user object.
        $getauthobj->userObj = new UserObject($this->configObj, $this->db);
      }
      if ($this->get_userid() < 1) {
        $notice = UserNotices::get_instance();
        if (!is_null($this->configObj->get('display_auth_debug')) and $this->configObj->get('display_auth_debug') == true) {
            $msg = $string['Authentication_notloggedin2'];
            $reason = $string['Authentication_notloggedin2'];
        } else {
            $msg = $string['Authentication_notloggedin2nodebug'];
            $reason = $string['Authentication_notloggedin2nodebug'];
        }
        $contactemail = support::get_email();
        $notice->display_notice_and_exit(
          $this->db,
          $string['Authentication_notloggedin1'],
          sprintf($msg, $contactemail, $contactemail, $this->debug_to_string()),
          sprintf($reason, $contactemail, $contactemail, $this->debug_to_string()),
          '/artwork/fingerprint_48.png',
          '#C00000',
          true,
          true);

      }
      $getauthobj->userObj->load($this->get_userid());
    }

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

  /**
   * Clears the config object.
   */
	 function clear_configObj() {
    $this->config = 'Config Object: removed for security';
    $this->configObj = 'Config Object: removed for security';
  }

  /**
   * Clears the config object.
   */
  function __Clone() {
    $this->config = 'Config Object: removed for security';
    $this->configObj = 'Config Object: removed for security';
  }
	
  /**
   * Returns information about all the authentication plugins.
	 * @param bool $formatted - True = return HTML formated data, False = array of data.
	 * @param bool $advanced	- Not yet written.
	 * @return array/string		- Information about the plugins.
   */
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
        if (!empty($item->error)) {
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
      //advanced view - Not yet written.

    }

    return $return_data;
  }

  /**
   * Check if the authentication stack is using a plugin of a given type
   * @param string $type - The class name of the plugin for which to check
   * @return boolean     - True if the plugin is loaded in the current authentication stack
   */
  function has_plugin_type($type) {
    $found = false;

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
