<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Atack
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/


use LTI\OAuthRequest,
    LTI\OAuthServer,
    LTI\OAuthSignatureMethod_HMAC_SHA1,
    LTI\TrivialOAuthDataStore;

require_once 'lti_util.php';
/**
 * Class to support LTI extends upon base LTI from IMS sample implimentation
 */
class UoN_LTI extends BLTI {

  // following 2 static variables & 2 static functions are from rogostaticsingleton but cant extend that as already extending BLTI class

  static $inst;
  static $class_name = 'UoN_LTI';
  /** @var string Language component name. */  
  protected $langcomponent = 'LTI/error';
  /** @var array language strings */
  protected $strings;
  
  /**
   * Create and return the Global instance of parent::$class_name for use in
   * the Local scope.
   */
  public static function get_instance() {
    if (!is_object(static::$inst)) {
      static::$inst = new static::$class_name;
    }
    return static::$inst;
  }
  /**
   * sets the Mock instance to return. ONLY used for unit testing
   *
   */
  public static function set_mock_instance($obj) {
    static::$inst = $obj;
  }
  
  private $db;
  /**
   * @var array|bool
   */
  private $parm = array('dbtype' => 'mysqli', 'table_prefix' => '');



  function __construct() {
    $langpack = new \langpack();
    $this->strings = $langpack->get_all_strings($this->langcomponent);
  }

  function init_lti0($db) {
    $this->db = $db;
  }

  /**
   * Function to get context title
   * @return lti context title
   */
  public function get_context_title() {
    $title = $this->info['context_title'];
    return $title;
  }

  /**
   * Function to initilise the lti class
   * @param bool $usesession
   * @param bool $doredirect
   * @return
   */
  public function init_lti($usesession = true, $doredirect = false) {
    if (!isset($_REQUEST["lti_message_type"])) {
      $_REQUEST["lti_message_type"] = '';
    }
    if (!isset($_REQUEST["lti_version"])) {
      $_REQUEST["lti_version"] = '';
    }
    if (!isset($_REQUEST["resource_link_id"])) {
      $_REQUEST["resource_link_id"] = '';
    }
      
    // If this request is not an LTI Launch, either
    // give up or try to retrieve the context from session
    if (!is_lti_request()) {
      if ($usesession === false) {
        return;
      }
      if (strlen(session_id()) > 0) {
        if (isset($_SESSION['_lti_row'])) {
          $row = $_SESSION['_lti_row'];
        }
          
        if (isset($row)) {
          $this->row = $row;
        } 
        if (isset($_SESSION['_lti_context_id'])) {
          $context_id = $_SESSION['_lti_context_id'];
        } 
        if (isset($context_id)) {
          $this->context_id = $context_id;
        } 
        if (isset($_SESSION['_lti_context'])) {
          $info = $_SESSION['_lti_context'];
        }
        if (isset($info)) {
          $this->info = $info;
          $this->valid = true;
          return;
        }
        $this->message = "Could not find context in session";
        return;
      }
      $this->message = "Session not available";
      return;
    }

    // Insure we have a valid launch
    if (empty($_REQUEST["oauth_consumer_key"])) {
      $this->message = "Missing oauth_consumer_key in request";
      unset($_SESSION['_lti_context']);
      return;
    }
    $oauth_consumer_key = $_REQUEST["oauth_consumer_key"];

    // Find the secret - either form the parameter as a string or
    // look it up in a database from parameters we are given
    $secret = false;
    $row = false;
    if (is_string($this->parm)) {
      $secret = $this->parm;
    } else if (!is_array($this->parm)) {
      $this->message = "Constructor requires a secret or database information.";
      unset($_SESSION['_lti_context']);
      return;
    } else {
      if ($this->db->error) {
        echo $this->strings['showerror'] . "<br />";
      }

      $stmt = $this->db->prepare("SELECT secret, context_id, name FROM " . $this->parm['table_prefix'] . "lti_keys WHERE oauth_consumer_key = ? AND `deleted` IS NULL");
      $stmt->bind_param('s', $oauth_consumer_key);
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($rsecret, $rcontext_id, $rname);
      $stmt->fetch();

      $secret = $rsecret;
      $name = $rname;
      if (isset($rcontext_id)) {
        $this->context_id = $rcontext_id;
      }

      $stmt->close();
      if (!is_string($secret)) {
        $this->message = "Could not retrieve secret oauth_consumer_key = " . $oauth_consumer_key;
        unset($_SESSION['_lti_context']);
        return;
      }
    }

    // Verify the message signature
    $store = new TrivialOAuthDataStore();
    $store->add_consumer($oauth_consumer_key, $secret);

    $server = new OAuthServer($store);

    $method = new OAuthSignatureMethod_HMAC_SHA1();
    $server->add_signature_method($method);
    $request = OAuthRequest::from_request();

    $this->basestring = $request->get_signature_base_string();

    try {
      $server->verify_request($request);
      $this->valid = true;
    } catch (Exception $e) {
      $this->message = $e->getMessage();
      unset($_SESSION['_lti_context']);
      return;
    }

    // Store the launch information in the session for later
    $newinfo = array();
    foreach ($_POST as $key => $value) {
      if ($key == "basiclti_submit") {
        continue;
      }
      if (strpos($key, "oauth_") === false) {
        $newinfo[$key] = $value;
        continue;
      }
      if ($key == "oauth_consumer_key") {
        $newinfo[$key] = $value;
        continue;
      }
    }
    $newinfo['oauth_consumer_secret'] = $secret;

    $this->info = $newinfo;
    if ($usesession == true and strlen(session_id()) > 0) {
      $_SESSION['_lti_context'] = $this->info;
      unset($_SESSION['_lti_row']);
      unset($_SESSION['_lti_context_id']);
      if ($this->row) {
        $_SESSION['_lti_row'] = $this->row;
      }
      if ($this->context_id) {
        $_SESSION['_lti_context_id'] = $this->context_id;
      }
    }

    if ($this->valid && $doredirect) {
      $this->redirect();
      $this->complete = true;
    }
  }

  /**
   * Load LTI integration based on config 
   * @return object LTI integration
   */
  public function load() {
      $configObject = Config::get_instance();
      if ($configObject->get_setting('core', 'lti_integration') == 'UoN') {
          return new lti_uon_integration_extended();
      } else {
          return new lti_default_integration_extended();
      }
  }

  /**
   * Gets the user linked to an external id.
   *
   * @param string $externalid The id of a user in the external system
   * @param string $consumer_key The consumer key used to connect to the system
   * @return array
   * @throws Exception
   */
  public function get_user_by_external_id($externalid, $consumer_key) {
    if (!isset($this->db)) {
      throw new Exception('lti_no_database');
    }
    $return = array();
    $sql = "SELECT u.id, u.title, u.surname, u.first_names, u.initials, u.username "
        . "FROM " . $this->parm['table_prefix'] . "lti_user lu "
        . "JOIN users u ON lu.lti_user_equ = u.id "
        . "WHERE lu.lti_user_key = ?";
    $result = $this->db->prepare($sql);
    $key = $this->generate_user_key($consumer_key, $externalid);
    $result->bind_param('s', $key);
    $result->execute();
    $result->bind_result($id, $title, $surname, $firstnames, $initials, $username);
    while ($result->fetch()) {
      $return["$id-$externalid"] = array(
        'id' => $id,
        'title' => $title,
        'surname' => $surname,
        'firstnames' => $firstnames,
        'initials' => $initials,
        'username' => $username,
        'externalid' => $externalid,
      );
    }
    return $return;
  }

  /**
   * Gets the eternal system ids attached to a Rogo user.
   *
   * @param string $username A Rogo user name
   * @param int $linkid The id of an LTi ket record.
   * @return array
   * @throws Exception
   */
  public function get_links_by_username($username, $linkid) {
    if (!isset($this->db)) {
      throw new Exception('lti_no_database');
    }
    $return = array();
    $sql = "SELECT u.id, u.title, u.surname, u.first_names, u.initials, u.username, lu.lti_user_key, k.oauth_consumer_key "
        . "FROM " . $this->parm['table_prefix'] . "lti_user lu "
        . "JOIN users u ON lu.lti_user_equ = u.id "
        . "JOIN " . $this->parm['table_prefix'] . "lti_keys k ON lu.lti_user_key LIKE CONCAT(k.oauth_consumer_key, ':%') "
        . "WHERE u.username = ? AND k.id = ?";
    $result = $this->db->prepare($sql);
    $result->bind_param('si', $username, $linkid);
    $result->execute();
    $result->bind_result($id, $title, $surname, $firstnames, $initials, $username, $rawexternalid, $consumer_key);
    while ($result->fetch()) {
      $externalid = substr($rawexternalid, strlen("$consumer_key:"));
      $return["$id-$externalid"] = array(
        'id' => $id,
        'title' => $title,
        'surname' => $surname,
        'firstnames' => $firstnames,
        'initials' => $initials,
        'username' => $username,
        'externalid' => $externalid,
      );
    }
    return $return;
  }
  
  /**
   * Get the details of an LTi key by it's ID in an array containing the following keys:
   * - id
   * - oauth_consumer_key
   * - secret
   * - name
   * - context_id
   *
   * If the passed key is invalid then the values of the keys will all be null.
   *
   * @param int $id
   * @return array
   * @throws Exception
   */
  public function get_lti_key($id) {
    if (!isset($this->db)) {
      throw new Exception('lti_no_database');
    }
    $return = array();
    $sql = "SELECT id, oauth_consumer_key, secret, name, context_id "
        . "FROM " . $this->parm['table_prefix'] . "lti_keys WHERE id = ? "
        . "AND deleted IS NULL LIMIT 1";
    $result = $this->db->prepare($sql);
    $result->bind_param('i', $id);
    $result->execute();
    $result->bind_result($return['id'], $return['oauth_consumer_key'], $return['secret'], $return['name'], $return['context_id']);
    $result->fetch();
    $result->close();
    return $return;
  }
  
  function get_lti_keys($deleted = false) {
    $dataret = array();
    $db = $this->db;
    if ($db->error) {
      echo $this->strings['showerror'] . "<br />";
    }
    $extra = '';
    if (!$deleted) {
      $extra = ' WHERE deleted IS NULL ';
    }
    $stmt = $this->db->prepare("SELECT id, oauth_consumer_key, secret, name, context_id, deleted, updated_on FROM " . $this->parm['table_prefix'] . "lti_keys $extra");
    if ($db->error) {
      echo $this->strings['showerror'] . "<br />";
    }
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($lti_keys_id, $lti_keys_key, $lti_keys_secret, $lti_keys_name, $lti_keys_context_id, $lti_keys_deleted, $lti_keys_updated_on);

    $rows = $stmt->num_rows;
    while ($stmt->fetch()) {
      $dataret[$lti_keys_id] = array('lti_keys_id' => $lti_keys_id, 'lti_keys_key' => $lti_keys_key, 'lti_keys_secret' => $lti_keys_secret, 'lti_keys_name' => $lti_keys_name, 'lti_keys_context_id' => $lti_keys_context_id, 'lti_keys_deleted' => $lti_keys_deleted, 'lti_keys_updated_on' => $lti_keys_updated_on);
    }
    $stmt->close();

    return $dataret;
  }

  function lti_key_exists($keyID) {
    $rows = 0;
    $db = $this->db;
    $stmt = $this->db->prepare("SELECT id FROM " . $this->parm['table_prefix'] . "lti_keys WHERE id = ? AND deleted IS NULL LIMIT 1");
    $stmt->bind_param('i', $keyID);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($lti_keys_id);
    $rows = $stmt->num_rows;
    $stmt->fetch();
    $stmt->close();

    return $rows > 0;
  }

  /**
   * Function to update lti key
   * @param int $ltiid unique id of lti key
   * @param string $ltiname name field of lti key
   * @param string $ltikey key field of lti key
   * @param string $ltisec secret field of lti key
   * @param string optional lticontext override field of lti key
   */
  function update_lti_key($ltiid, $ltiname, $ltikey, $ltisec, $lticontext = '') {
    $db = $this->db;
    if ($db->error) {
      echo $this->strings['showerror'] . "<br />";
    }
    $stmt = $this->db->prepare("UPDATE " . $this->parm['table_prefix'] . "lti_keys SET oauth_consumer_key = ?, secret = ?, context_id = ?, `name` = ? WHERE id = ?");
    $stmt->bind_param('ssssi', $ltikey, $ltisec, $lticontext, $ltiname, $ltiid);
    $stmt->execute();
    $stmt->close();
  }

  /**
   * Function to delete lti key
   * @param int $ltiid the unique id of lti key to delete
   */
  function delete_lti_key($ltiid) {
    $db = $this->db;
    if ($db->error) {
      echo $this->strings['showerror'] . "<br />";
    }
    $stmt = $this->db->prepare("UPDATE " . $this->parm['table_prefix'] . "lti_keys SET deleted = NOW() WHERE id = ?");
    $stmt->bind_param('i', $ltiid);
    $stmt->execute();
    $stmt->close();
  }
  
  /**
   * Deletes the link between an user of an external system and Rogo.
   *
   * @param int $userid The id of a Rogo user.
   * @param type $consumer_key The consumer key for an external system.
   * @param type $externalid The id of the user in the external system.
   * @throws Exception
   */
  public function delete_user_link($userid, $consumer_key, $externalid) {
    if (!isset($this->db)) {
      throw new Exception('lti_no_database');
    }
    $sql = "DELETE FROM " . $this->parm['table_prefix'] . "lti_user "
        . "WHERE lti_user_equ = ? AND lti_user_key = ?";
    $query = $this->db->prepare($sql);
    $key = $this->generate_user_key($consumer_key, $externalid);
    $query->bind_param('is', $userid, $key);
    $query->execute();
  }

  /**
   * Generate the user key for the lti_user table.
   *
   * @param string $consumer_key the consumer kety for the external system.
   * @param string $externalid the identifier of a user from the external system.
   * @return string
   */
  public function generate_user_key($consumer_key, $externalid) {
    return "$consumer_key:$externalid";
  }

  /**
   * Function to add new lti key
   * @param string $ltiname name field of lti key
   * @param string $ltikey key field of lti key
   * @param string $ltisec secret field of lti key
   * @param string $lticontext
   * @internal param int $ltiid unique id of lti key
   * @internal param \optional $string lticontext override field of lti key
   */
  function add_lti_key($ltiname, $ltikey, $ltisec, $lticontext = '') {
    $db = $this->db;
    if ($db->error) {
      echo $this->strings['showerror'] . "<br />";
    }
    $stmt = $this->db->prepare("INSERT INTO " . $this->parm['table_prefix'] . "lti_keys (oauth_consumer_key, secret,context_id, `name`) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $ltikey, $ltisec, $lticontext, $ltiname);
    $stmt->execute();
    $stmt->close();
  }

  /**
   * Function to lookup lti user association
   * @param bool|string $lti_user_key optional lti user key
   * @return false if not found else array containing the associated id and last update time
   */
  function lookup_lti_user($lti_user_key = false) {
    if ($lti_user_key === false) {
      $lti_user_key = $this->getUserKey();
    }
    $stmt = $this->db->prepare("SELECT lti_user_equ, updated_on FROM " . $this->parm['table_prefix'] . "lti_user WHERE lti_user_key = ?");
    if ($this->db->error) {
      echo $this->strings['showerror'] . "<br />";
    }
    $stmt->bind_param('s', $lti_user_key);
    $stmt->execute();
    $stmt->store_result();
    $rows = $stmt->num_rows;
    if ($rows < 1) {
      return false;
    }
    $stmt->bind_result($rogo_id, $updated);
    $stmt->fetch();
    $stmt->close();
    return (array($rogo_id, $updated));
  }

  /**
   * Function to add lti user association
   * @param string $lti_user_equ the associated id to link to
   * @param bool|string $lti_user_key optional the lti key to lookup against
   * @return int of insert id
   */
  function add_lti_user($lti_user_equ, $lti_user_key = false) {
    if ($lti_user_key === false) {
      $lti_user_key = $this->getUserKey();
    }
    $result = $this->db->prepare("INSERT INTO " . $this->parm['table_prefix'] . "lti_user (lti_user_key, lti_user_equ,updated_on) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE updated_on = NOW()");
    $result->bind_param('ss', $lti_user_key, $lti_user_equ);
    $result->execute();
    $ret = $this->db->insert_id;
    $result->close();
    return $ret;
  }

  /**
   * Function to update lti user association date
   * @param bool|string $lti_user_key optional key to update
   * @return
   */
  function update_lti_user($lti_user_key = false) {
    if ($lti_user_key === false) {
      $lti_user_key = $this->getUserKey();
    }
    $result = $this->db->prepare("UPDATE " . $this->parm['table_prefix'] . "lti_user set updated_on = NOW() WHERE lti_user_key = ?");
    if ($this->db->error) {
      echo $this->strings['showerror'] . "<br />";
    }
    $result->bind_param('s', $lti_user_key);
    $result->execute();
    $result->close();
    return;
  }

  /**
   * Function to lookup lti resource association
   * @param bool|string $lti_resource_key optional resource key
   * @return false if missing else array of the internal_id, and the internal type plus when it was updated.
   */
  function lookup_lti_resource($lti_resource_key = false) {
    if ($lti_resource_key === false) {
      $lti_resource_key = $this->getResourceKey();
    }
    $stmt = $this->db->prepare("SELECT internal_id, internal_type, updated_on FROM " . $this->parm['table_prefix'] . "lti_resource WHERE lti_resource_key = ?");
    $stmt->bind_param('s', $lti_resource_key);
    $stmt->execute();
    $stmt->store_result();
    $rows = $stmt->num_rows;
    if ($rows < 1) {
      return false;
    }
    $stmt->bind_result($paperret, $otherret, $updated_on);
    $stmt->fetch();
    $stmt->close();
    return (array($paperret, $otherret, $updated_on));
  }

  /**
   * Function to add a new lti resource association
   * @param string $internal_id is the internal id
   * @param string $internal_type is the internal type
   * @param bool|string $lti_resource_key optional is the lti resource key
   * @return record id
   */
  function add_lti_resource($internal_id, $internal_type, $lti_resource_key = false) {
    if ($lti_resource_key === false) {
      $lti_resource_key = $this->getResourceKey();
    }
    $result = $this->db->prepare("INSERT INTO " . $this->parm['table_prefix'] . "lti_resource (lti_resource_key, internal_id, internal_type, updated_on) VALUES (?, ?, ?, NOW()) ");
    $result->bind_param('sss', $lti_resource_key, $internal_id, $internal_type);
    $result->execute();
    $ret = $this->db->insert_id;
    $result->close();
    return $ret;
  }

  /**
   * Function to update lti resource association
   * @param string $internal_id is the internal id
   * @param string $internal_type is the internal type
   * @param bool|string $lti_resource_key optional is the lti resource key
   * @return false if not found else number of rows
   */
  function update_lti_resource($internal_id, $internal_type, $lti_resource_key = false) {
    if ($lti_resource_key === false) {
      $lti_resource_key = $this->getResourceKey();
    }
    $stmt = $this->db->prepare("UPDATE " . $this->parm['table_prefix'] . "lti_resource SET internal_id = ?, internal_type = ? WHERE lti_resource_key = ?");
    $stmt->bind_param('sss', $internal_id, $internal_type, $lti_resource_key);
    $stmt->execute();
    $rows = $stmt->affected_rows;
    $stmt->close();
    if ($rows > 0) {
      return $rows;
    }
    return false;
  }

  /**
   * Function to add lti context association
   * @param string $c_internal_id is the internal context id
   * @param bool|string $lti_context_key optional is the lti context key
   * @return new row id
   */
  function add_lti_context($c_internal_id, $lti_context_key = false) {
    if ($lti_context_key === false) {
      $lti_context_key = $this->getCourseKey();
    }
    $result = $this->db->prepare("INSERT INTO " . $this->parm['table_prefix'] . "lti_context (lti_context_key, c_internal_id, updated_on) VALUES (?, ?, NOW()) ");
    $db = $this->db;
    if ($db->error) {
      echo $this->strings['showerror'] . "<br />";
      exit();
    }
    $result->bind_param('ss', $lti_context_key, $c_internal_id);
    $result->execute();
    $ret = $this->db->insert_id;
    $result->close();
    return $ret;
  }

  /**
   * Function to lookup lti context
   * @param bool|string $lti_context_key optional the lti context key
   * @return array|bool if false else array with module shortcode and last lti context updated time
   */
  function lookup_lti_context($lti_context_key = false) {
    if ($lti_context_key === false) {
      $lti_context_key = $this->getCourseKey();
    }

    $sql = "SELECT m.moduleid, c.updated_on FROM " . $this->parm['table_prefix'] . "lti_context c, " . $this->parm['table_prefix'] . "modules m
            WHERE c.c_internal_id = m.id AND lti_context_key = ?";
    $stmt = $this->db->prepare($sql);
    $db = $this->db;
    if ($db->error) {
      echo $this->strings['showerror'] . "<br />";
      exit();
    }
    $stmt->bind_param('s', $lti_context_key);
    $stmt->execute();
    $stmt->store_result();
    $rows = $stmt->num_rows;
    if ($rows < 1) {
      return false;
    }
    $stmt->bind_result($moduleid, $updated_on);
    $stmt->fetch();
    $stmt->close();
    return (array($moduleid, $updated_on));
  }

  function get_consumer_secret() {
    if (isset($this->info['oauth_consumer_secret'])) {
      return $this->info['oauth_consumer_secret'];
    }
    return false;
  }

  function send_grade($grade) {

    $oauth_consumer_key = $this->getConsumerKey();
    $oauth_consumer_secret = $this->get_consumer_secret();
    $endpoint = $this->getOutcomeService();
    $sourcedid = $this->getOutcomeSourceDID();

    $response = replaceResultRequest($grade, $sourcedid, $endpoint, $oauth_consumer_key, $oauth_consumer_secret);
    return $response;
  }

  /**
   * Check that paperlink matches that of paper we are viewing
   * @param string $cryptname crypt name of paper
   * @return void
   */
  public function check_paper_link($cryptname) {
    if (isset($_SESSION['lti']['paperlink'])) {
      if ($cryptname !== $_SESSION['lti']['paperlink']) {
        // No match so set lti launch as invalid.
        $this->valid = false;
      }
    }
  }
}
