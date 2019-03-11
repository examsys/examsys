<?php

use LTI\OAuthConsumer,
    LTI\OAuthRequest,
    LTI\OAuthServer,
    LTI\OAuthSignatureMethod_HMAC_SHA1,
    LTI\TrivialOAuthDataStore;

$OAuth_last_computed_siguature = false;

// Returns true if this is a Basic LTI message
// with minimum values to meet the protocol
function is_lti_request() {
  $good_message_type = $_REQUEST["lti_message_type"] == "basic-lti-launch-request";
  $good_lti_version = $_REQUEST["lti_version"] == "LTI-1p0";
  $resource_link_id = $_REQUEST["resource_link_id"];
  if ($good_message_type and $good_lti_version and isset($resource_link_id) ) return(true);
  return false;
}

// Basic LTI Class that does the setup and provides utility
// functions
class BLTI {

	public $valid = false;
	public $complete = false;
	public $message = false;
	public $basestring = false;
	public $info = false;
	public $row = false;
	public $context_id = false;  // Override context_id
	public $consumer_id = false;
	public $user_id = false;
	public $course_id = false;
	public $resource_id = false;

	function __construct($parm = false, $usesession = true, $doredirect = true) {
    // If this request is not an LTI Launch, either
    // give up or try to retrieve the context from session
    if (!is_lti_request()) {
      if ($usesession === false) return;
      if (strlen(session_id()) > 0) {
        $row = $_SESSION['_lti_row'];
        if (isset($row)) $this->row = $row;
        $context_id = $_SESSION['_lti_context_id'];
        if (isset($context_id)) $this->context_id = $context_id;
        $info = $_SESSION['_lti_context'];
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
    if (empty($_REQUEST['oauth_consumer_key'])) {
      $this->message = 'Missing oauth_consumer_key in request';
      return;
    }
    $oauth_consumer_key = $_REQUEST['oauth_consumer_key'];

    // Find the secret - either form the parameter as a string or
    // look it up in a database from parameters we are given
    $secret = false;
    $row = false;
    if (is_string($parm)) {
      $secret = $parm;
    } elseif (!is_array($parm)) {
      $this->message = 'Constructor requires a secret or database information.';
      return;
    } else {      
        
      $key_column = $parm['key_column'] ? $parm['key_column'] : 'oauth_consumer_key';
      $table = \param::clean($parm['table'], \param::ALPHA);
      $sql = "SELECT * FROM  $table WHERE $key_column = ?";
      $result = $mysqli->prepare($sql);
      $result->bind_param('i', $oauth_consumer_key);
      $result->execute();
      $result->store_result();
      $num_rows = $result->num_rows;
      
      if ($num_rows != 1) {
        $this->message = 'Your consumer is not authorized oauth_consumer_key=' . $oauth_consumer_key;
        return;
      } else {
        while ($row = mysqli_fetch_assoc($result)) {
          $secret = $row[$parms['secret_column'] ? $parms['secret_column'] : 'secret'];
          $context_id = $row[$parms['context_column'] ? $parms['context_column'] : 'context_id'];
          if ( $context_id ) $this->context_id = $context_id;
          $this->row = $row;
          break;
        }
        if (!is_string($secret)) {
          $this->message = 'Could not retrieve secret oauth_consumer_key=' . $oauth_consumer_key;
          return;
        }
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
      return;
    }

    // Store the launch information in the session for later
    $newinfo = array();
    foreach($_POST as $key => $value) {
      if ($key == 'basiclti_submit') continue;
      if (strpos($key, 'oauth_') === false ) {
        $newinfo[$key] = $value;
        continue;
      }
      if ($key == 'oauth_consumer_key') {
        $newinfo[$key] = $value;
        continue;
      }
    }

    $this->info = $newinfo;
    if ($usesession == true and strlen(session_id()) > 0) {
      $_SESSION['_lti_context'] = $this->info;
      unset($_SESSION['_lti_row']);
      unset($_SESSION['_lti_context_id']);
      if ( $this->row ) $_SESSION['_lti_row'] = $this->row;
      if ( $this->context_id ) $_SESSION['_lti_context_id'] = $this->context_id;
    }

    if ($this->valid and $doredirect) {
      $this->redirect();
      $this->complete = true;
    }
	}

	function addSession($location) {
		if ( ini_get('session.use_cookies') == 0 ) {
			if ( strpos($location,'?') > 0 ) {
				$location = $location . '&';
			} else {
				$location = $location . '?';
			}
			$location = $location . session_name() . '=' . session_id();
		}
		return $location;
	}

	function isInstructor() {
		$roles = $this->info['roles'];
		$roles = strtolower($roles);
		if ( ! ( strpos($roles,"instructor") === false ) ) return true;
		if ( ! ( strpos($roles,"administrator") === false ) ) return true;
		return false;
	}

	function getUserEmail() {
		$email = $this->info['lis_person_contact_email_primary'];
		if ( strlen($email) > 0 ) return $email;
		# Sakai Hack
		$email = $this->info['lis_person_contact_emailprimary'];
		if ( strlen($email) > 0 ) return $email;
		return false;
	}

	function getUserShortName() {
		$email = $this->getUserEmail();
		$givenname = $this->info['lis_person_name_given'];
		$familyname = $this->info['lis_person_name_family'];
		$fullname = $this->info['lis_person_name_full'];
		if ( strlen($email) > 0 ) return $email;
		if ( strlen($givenname) > 0 ) return $givenname;
		if ( strlen($familyname) > 0 ) return $familyname;
		return $this->getUserName();
	}

	function getUserName() {
		$givenname = $this->info['lis_person_name_given'];
		$familyname = $this->info['lis_person_name_family'];
		$fullname = $this->info['lis_person_name_full'];
		if ( strlen($fullname) > 0 ) return $fullname;
		if ( strlen($familyname) > 0 and strlen($givenname) > 0 ) return $givenname + $familyname;
		if ( strlen($givenname) > 0 ) return $givenname;
		if ( strlen($familyname) > 0 ) return $familyname;
		return $this->getUserEmail();
	}

	// Name spaced
	function getUserKey() {
		$oauth = $this->info['oauth_consumer_key'];
		$id = $this->info['user_id'];
		if ( strlen($id) > 0 and strlen($oauth) > 0 ) return $oauth . ':' . $id;
		return false;
	}

	// Un-Namespaced
	function getUserLKey() {
		$id = $this->info['user_id'];
		if ( strlen($id) > 0 ) return $id;
		return false;
	}

	function setUserID($new_id) {
		$this->user_id = $new_id;
	}

	function getUserID() {
		return $this->user_id;
	}

	function getUserImage() {
		$image = $this->info['user_image'];
		if ( strlen($image) > 0 ) return $image;
		$email = $this->getUserEmail();
		if ( $email === false ) return false;
		$size = 40;
		$grav_url = $_SERVER['HTTPS'] ? 'https://' : 'http://';
		$grav_url = $grav_url . "www.gravatar.com/avatar.php?gravatar_id=".md5( strtolower($email) )."&size=".$size;
		return $grav_url;
	}

	function getResourceKey() {
		$oauth = $this->info['oauth_consumer_key'];
		$id = $this->info['resource_link_id'];
		if ( strlen($id) > 0 and strlen($oauth) > 0 ) return $oauth . ':' . $id;
		return false;
	}

	function getResourceLKey() {
		$id = $this->info['resource_link_id'];
		if ( strlen($id) > 0 ) return $id;
		return false;
	}

	function setResourceID($new_id) {
		$this->resource_id = $new_id;
	}

	function getResourceID() {
		return $this->resource_id;
	}

	function getResourceTitle() {
		$title = $this->info['resource_link_title'];
		if ( strlen($title) > 0 ) return $title;
		return false;
	}

	function getConsumerKey() {
		$oauth = $this->info['oauth_consumer_key'];
		return $oauth;
	}

	function setConsumerID($new_id) {
		$this->consumer_id = $new_id;
	}

	function getConsumerID() {
		return $this->consumer_id;
	}

	function getCourseLKey() {
		if ( $this->context_id ) return $this->context_id;
		$id = $this->info['context_id'];
		if ( strlen($id) > 0 ) return $id;
		return false;
	}

	function getCourseKey() {
		if ( $this->context_id ) return $this->context_id;
		$oauth = $this->info['oauth_consumer_key'];
		$id = $this->info['context_id'];
		if ( strlen($id) > 0 and strlen($oauth) > 0 ) return $oauth . ':' . $id;
		return false;
	}

	function setCourseID($new_id) {
		$this->course_id = $new_id;
	}

	function getCourseID() {
		return $this->course_id;
	}

	/**
	 * Get the external ID for VLE course.
	 * @return mixed clean value or null.
	 */
	function getExternalID() {
		$sourcedid = $this->info['lis_course_section_sourcedid'];
		if (empty($sourcedid)) {
			return null;
		}
		return $sourcedid;
	}

	function getCourseName() {
		$label = $this->info['context_label'];
		$title = $this->info['context_title'];
		$id = $this->info['context_id'];
		if ( strlen($label) > 0 ) return $label;
		if ( strlen($title) > 0 ) return $title;
		if ( strlen($id) > 0 ) return $id;
		return false;
	}

	function getCSS() {
		$list = $this->info['launch_presentation_css_url'];
		if ( strlen($list) < 1 ) return array();
		return explode(',',$list);
	}

	function getOutcomeService() {
		$retval = $this->info['lis_outcome_service_url'];
		if ( strlen($retval) > 1 ) return $retval;
		return false;
	}

	function getOutcomeSourceDID() {
		$retval = $this->info['lis_result_sourcedid'];
		if ( strlen($retval) > 1 ) return $retval;
		return false;
	}

	function redirect($url = false) {
		if ( $url === false ) {
			$host = $_SERVER['HTTP_HOST'];
			$uri = $_SERVER['PHP_SELF'];
			$location = $_SERVER['HTTPS'] ? 'https://' : 'http://';
			$location = $location . $host . $uri;
		} else {
			$location = $url;
		}

		if ( headers_sent() ) {
			echo '<a href="' . htmlentities($location).'">Continue</a>' . "\n";
		} else {
			$location = htmlentities($this->addSession($location));
			header("Location: $location");
			exit();
		}
	}

	function dump() {
		if (!$this->valid or $this->info == false) return "Context not valid\n";
		$ret = "";
		if ( $this->isInstructor() ) {
				$ret .= "isInstructor() = true\n";
		} else {
				$ret .= "isInstructor() = false\n";
		}
		$ret .= "getConsumerKey() = ".$this->getConsumerKey()."\n";
		$ret .= "getUserLKey() = ".$this->getUserLKey()."\n";
		$ret .= "getUserKey() = ".$this->getUserKey()."\n";
		$ret .= "getUserID() = ".$this->get_user_ID()."\n";
		$ret .= "getUserEmail() = ".$this->getUserEmail()."\n";
		$ret .= "getUserShortName() = ".$this->getUserShortName()."\n";
		$ret .= "getUserName() = ".$this->getUserName()."\n";
		$ret .= "getUserImage() = ".$this->getUserImage()."\n";
		$ret .= "getResourceKey() = ".$this->getResourceKey()."\n";
		$ret .= "getResourceID() = ".$this->getResourceID()."\n";
		$ret .= "getResourceTitle() = ".$this->getResourceTitle()."\n";
		$ret .= "getCourseName() = ".$this->getCourseName()."\n";
		$ret .= "getCourseKey() = ".$this->getCourseKey()."\n";
		$ret .= "getCourseID() = ".$this->getCourseID()."\n";
		$ret .= "getOutcomeSourceDID() = ".$this->getOutcomeSourceDID()."\n";
		$ret .= "getOutcomeService() = ".$this->getOutcomeService()."\n";
		return $ret;
	}

  public function get_course_id() {
    return $this->course_id;
  }
}

function sendOAuthBodyPOST($method, $endpoint, $oauth_consumer_key, $oauth_consumer_secret, $content_type, $body) {
	$hash = base64_encode(sha1($body, TRUE));

	$parms = array('oauth_body_hash' => $hash);

	$test_token = '';
	$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
	$test_consumer = new OAuthConsumer($oauth_consumer_key, $oauth_consumer_secret, NULL);

	$acc_req = OAuthRequest::from_consumer_and_token($test_consumer, $test_token, $method, $endpoint, $parms);
	$acc_req->sign_request($hmac_method, $test_consumer, $test_token);

	// Pass this back up "out of band" for debugging
	global $LastOAuthBodyBaseString;
	$LastOAuthBodyBaseString = $acc_req->get_signature_base_string();

	$header = $acc_req->to_header();
	$header = $header . "\r\nContent-Type: " . $content_type . "\r\n";

	$headers = explode("\r\n",$header);
	$response = sendXmlOverPost($endpoint, $body, $headers);

	if ($response['result'] === false) {
		$configObject = Config::get_instance();
		$logger = new \logger($configObject->db);
		$userObj = \UserObject::get_instance();
		$userid = $userObj->get_user_ID();
		$type = 'LTI Post';
		$errorstring = "Problem reading data from " . $endpoint . ", " . $response['error'];
		$errorfile = $_SERVER['PHP_SELF'];
		$errorline = __LINE__ - 10;
		$logger->record_application_warning($userid, $type, $errorstring, $errorfile, $errorline);
	}
	return $response['result'];
}

/**
 * Send some xml via a curl post
 *
 * @param string $url service to send request to
 * @param string $xml the xml to send
 * @param string $header the header to send
 * @return array
 */
function sendXmlOverPost($url, $xml, $header) {
  $configObject = Config::get_instance();
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);

  // For xml, change the content-type.
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ask for results to be returned
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $configObject->get_setting('core', 'lti_ssl_verifypeer'));
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $configObject->get_setting('core', 'lti_ssl_verifyhost'));
  // Send to remote and return data to caller.
  $response['result'] = curl_exec($ch);
  $response['error'] = curl_error($ch);
  curl_close($ch);
  return $response;
}

function getPOXGradeRequest() {
    return '<?xml version = "1.0" encoding = "UTF-8"?>
<imsx_POXEnvelopeRequest xmlns = "http://www.imsglobal.org/services/ltiv1p1/xsd/imsoms_v1p0">
  <imsx_POXHeader>
    <imsx_POXRequestHeaderInfo>
      <imsx_version>V1.0</imsx_version>
      <imsx_messageIdentifier>MESSAGE</imsx_messageIdentifier>
    </imsx_POXRequestHeaderInfo>
  </imsx_POXHeader>
  <imsx_POXBody>
    <OPERATION>
      <resultRecord>
        <sourcedGUID>
          <sourcedId>SOURCEDID</sourcedId>
        </sourcedGUID>
        <result>
          <resultScore>
            <language>en-us</language>
            <textString>GRADE</textString>
          </resultScore>
        </result>
      </resultRecord>
    </OPERATION>
  </imsx_POXBody>
</imsx_POXEnvelopeRequest>';
}

/**
 * Post grade
 * @param float $grade the grade
 * @param string $sourcedid the source system item id
 * @param string $endpoint teh source system
 * @param string $oauth_consumer_key oauth consumer key
 * @param string $oauth_consumer_secret oauth consumer secret
 * @return array|null
 */
function replaceResultRequest($grade, $sourcedid, $endpoint, $oauth_consumer_key, $oauth_consumer_secret) {
	$method="POST";
	$content_type = "application/xml";
	$operation = 'replaceResultRequest';
	$postBody = str_replace(
			array('SOURCEDID', 'GRADE', 'OPERATION','MESSAGE'),
			array($sourcedid, $grade, $operation, uniqid()),
			getPOXGradeRequest());

	$response = sendOAuthBodyPOST($method, $endpoint, $oauth_consumer_key, $oauth_consumer_secret, $content_type, $postBody);
	if ($response !== false) {
		return parseResponse($response);
	}
	return null;
}

function parseResponse($response) {
	$retval = Array();
	try {
		$xml = new SimpleXMLElement($response);
		$imsx_header = $xml->imsx_POXHeader->children();
		$parms = $imsx_header->children();
		$status_info = $parms->imsx_statusInfo;
		$retval['imsx_codeMajor'] = (string) $status_info->imsx_codeMajor;
		$retval['imsx_severity'] = (string) $status_info->imsx_severity;
		$retval['imsx_description'] = (string) $status_info->imsx_description;
		$retval['imsx_messageIdentifier'] = (string) $parms->imsx_messageIdentifier;
		$imsx_body = $xml->imsx_POXBody->children();
		$operation = $imsx_body->getName();
		$retval['response'] = $operation;
		$parms = $imsx_body->children();
	} catch (Exception $e) {
		throw new Exception('Error: Unable to parse XML response' . $e->getMessage());
	}

	if ( $operation == 'readResultResponse' ) {
	 try {
			$retval['language'] =(string) $parms->result->resultScore->language;
			$retval['textString'] = (string) $parms->result->resultScore->textString;
	 } catch (Exception $e) {
			throw new Exception("Error: Body parse error: ".$e->getMessage());
	 }
	}
	return $retval;
}
