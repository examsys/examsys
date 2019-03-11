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
* API functionality
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * API Class
 */
class api {

    /**
     * The slim application.
     */
    private $app;
    
    /**
     * The media type.
     */
    private $mediatype;
    
    /**
     * Language pack component.
     */
    private $langcomponent = 'api/api';
    
    /**
     * The log file.
     */
    private $logfile;
    
    /**
     * API object.
     */
    private $api;

    /**
     * The request.
     * @var object
     */
    public $request;

    /**
     * The response.
     * @var object
     */
    public $response;

    /**
     * Constructor
     * @param object $app the slim application
     * @param mysqli $db db connection
     * @param object $configObject configurations
     */
    public function __construct($app, $db, $configObject) {
        $this->app = $app;
        // Get configs.
        $configObject->set_db_object($db);
        $configObject->load_settings('core');
        $settings = (object) $configObject->get_setting('core');
        if (property_exists($settings, 'apilogfile')) {
            $this->logfile = $settings->apilogfile;
        } else {
            $this->logfile = '';
        }
    }
    
    /**
     * Log api request to file 
     * @return string unique id for the request
     */
    public function log_request() {
        $id = uniqid('', true);
        if ($this->logfile != '') {
            $updatelog = "\n\n" . "--" . date("YmdHis") . "--\n\nApi Log Id: " . $id .
            "\nUser Agent: " . $this->get_user_agent() .
            "\nAccess Token: " . $this->get_parameter('access_token') .
            "\nResource Path: " . $this->get_path() . "\n\n" . $this->get_body();
            file_put_contents($this->logfile , $updatelog, FILE_APPEND);
        }
        return $id;
    }
    
    /**
     * Log api response to file 
     * @param string $id unique id linking to the request for this response
     * @param string $xml xml string to log
     */
    public function log_response($id, $xml) {
        if ($this->logfile != '') {
            $updatelog = "\n\n" . "--" . date("YmdHis") . "--\n\nApi Log Id: " . $id .
            "\n\n" . $xml;
            file_put_contents($this->logfile , $updatelog, FILE_APPEND);
        }
    }

    /**
     * Set the header for the response.
     * @param string $type - header type
     */
    public function set_header($type = 'text/xml') {
        $this->response->withHeader("Content-Type", $type);
    }

    /**
     * Get the body of the request.
     * @return string - body of request.
     */
    public function get_body() {
        return $this->request->getBody();
    }

    /**
     * Get the user agent of the request.
     * @return string - user agent 
     */
    public function get_user_agent() {
        return $this->request->getHeaderLine('USER_AGENT');
    }
    
    /**
     * Get a parameter of the request.
     * @param $parameter string parameter name
     * @return string - parameter 
     */
    public function get_parameter($parameter) {
        return $this->request->getQueryParam($parameter);
    }
    
     /**
      * Get the path of the request.
      * @return string - path
      */
    public function get_path() {
        return $this->request->getUri()->getBasePath() . '/' . $this->request->getUri()->getPath();
    }
    
    
    /**
     * Get the media type of the request.
     * @return string|bool - media type if valid, false otherwise
     */
    public function get_mediatype() {
        $mediatype = $this->request->getMediaType();
        if ($mediatype == 'text/xml') {
            $this->mediatype = $mediatype;
            return $mediatype;
        } else {
            return false;
        }
    }
    
    /**
     * Process the request
     * @param string $folder - location of validation schema
     * @param string $type - filename of validation schema
     * @return array - status and response
     */
    public function process($folder, $type) {
        $langpack = new \langpack();
        // Set response header
        $this->set_header($this->get_mediatype());
        // Get body of request.
        $body = $this->get_body();
        $this->api = new \api\apixml($body);
        // Validate request.
        $errorresp = $this->api->validate($folder, $type);
        if (count($errorresp) > 0) {
            return array('BAD', $errorresp);
        } else {
            return array('OK', $this->api->getdata());
        }
    }
    
    /**
     * Parse the request and process it.
     * @param object $tasktype task object
     * @param array $fields expected fields
     * @param array $actions possible actions
     * @param array $perms user permissions
     * @param integer $userid rogo user id linked to web service client
     * @return string - successful operation response or error response
     */
    public function parse($tasktype, $fields, $actions, $perms, $userid) {
        // Parse the request.
        return $this->api->parse($tasktype, $fields, $actions, $perms, $userid);
    }
    
}