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
* Oauth package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

/**
 * Oauth helper class.
 * Interfaces with the vendor/bshaffer/oauth2-server-php
 */
class oauth {
    
    /**
     * The db connection.
     * @var mysqli 
     */
    private $db;
    
    /**
     * The oauth server object.
     */
    private $server;
    
    /**
     * The oauth storage object.
     */
    private $storage;

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
     * Constructor
     * @param object $configObject - rogo configuration object
     * @return void 
     */
    function __construct($configObject) {
        $this->db = \DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), 
                                 $configObject->get('cfg_db_sysadmin_user'), 
                                 $configObject->get('cfg_db_sysadmin_passwd'), 
                                 $configObject->get('cfg_db_database'), 
                                 $configObject->get('cfg_db_charset'), 
                                 \UserNotices::get_instance(), 
                                 $configObject->get('dbclass'));
        $dsn = "mysql:dbname=" . $configObject->get('cfg_db_database') . ";" . "host=" . $configObject->get('cfg_db_host');
        $username = $configObject->get('cfg_db_sysadmin_user');
        $password = $configObject->get('cfg_db_sysadmin_passwd');
        // $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
        $this->storage = new \OAuth2\Storage\Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));
        // Config options for server.
        $config = array(
            'access_lifetime' => $configObject->get('cfg_oauth_access_lifetime'),
            'refresh_token_lifetime' => $configObject->get('cfg_oauth_refresh_token_lifetime'),
            'always_issue_new_refresh_token' => $configObject->get('cfg_oauth_always_issue_new_refresh_token')
        );
        // Pass a storage object or array of storage objects to the OAuth2 server class
        $this->server = new \OAuth2\Server($this->storage, $config);
        // Add the "Authorization Code" grant type 
        $this->server->addGrantType(new \OAuth2\GrantType\AuthorizationCode($this->storage));
        // Add the "Refresh Token" grant type
        $this->server->addGrantType(new \OAuth2\GrantType\RefreshToken($this->storage, $config));
    }
    
    /**
     * Delete ALL permissions for client.
     * @param string $action 
     * @param string $client
     * @return bool 
     */
    private function delete_permissions($client) {
        $result = $this->db->prepare("DELETE FROM webservice_permissions WHERE client_id = ?");
        $result->bind_param('s', $client);
        $result->execute();
        $result->close();
    }
    
    /**
     * Check if client has permission to take an action
     * @param string $action 
     * @param string $client 
     * @return bool 
     */
    public function check_permissions($action, $client) {
        $result = $this->db->prepare("SELECT count(client_id) FROM webservice_permissions WHERE client_id = ? AND action = ? AND access = true");
        $result->bind_param('ss', $client, $action);
        $result->execute();
        $result->bind_result($count);
        $result->fetch();
        $result->close();
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Enable/Disable permission for client.
     * @param string $action 
     * @param string $client 
     * @param bool $access 
     * @return void 
     */
    public function set_permission($action, $client, $access) {
        $result = $this->db->prepare("UPDATE webservice_permissions SET access = ? WHERE client_id = ? AND action = ?");
        $result->bind_param('iss', $access, $client, $action);
        $result->execute();
        // If adding permision insert on failed update.
        if ($access == true and $this->db->affected_rows == 0) {
            $this->add_permission($action, $client, $access);
        }
        $result->close();
    }
    
    /**
     * Create permission for client.
     * @param string $action 
     * @param string $client 
     * @param bool $access 
     * @return void 
     */
    public function add_permission($action, $client, $access) {
        $result = $this->db->prepare("INSERT INTO webservice_permissions (client_id, action, access) values (?, ?, ?)");
        $result->bind_param('ssi', $client, $action, $access);
        $result->execute();
        $result->close();
    }

    /**
     * Get ouath storage object
     */
    public function get_storage() {
        return $this->storage;
    }
    
    /**
     * Handle a request to a resource and authenticate the access token
     * return string|void - client id if authenticated, void otherwise
     */
    public function check_auth() {
        $verify = $this->server->verifyResourceRequest(\OAuth2\Request::createFromGlobals());
        if (!$verify) {
            return 'INVALID_TOKEN';
        } 
        $token = $this->server->getAccessTokenData(\OAuth2\Request::createFromGlobals());
        return $token['client_id'];
    }
    
    /**
     * Handle a request for an OAuth2.0 access token and send the response to the client 
     */
    public function request_token() {
        $this->server->handleTokenRequest(\OAuth2\Request::createFromGlobals())->send('xml');
    }
    
    /**
     * Authorise an OAuth2.0 access token
     * @param bool $authorised is the token authorised
     * @param int $userid the user authorising
     * @return array - success (true) or failure (false) and status message.
     */
    public function authorise($authorised = false, $userid = '') {
        $request = \OAuth2\Request::createFromGlobals();
        $response = new \OAuth2\Response();
        // validate the authorize request
        if (!$this->server->validateAuthorizeRequest($request, $response)) {
            $response->send('xml');
            return array(false, 'Validation Failure');
        }
        try {
            $this->server->handleAuthorizeRequest($request, $response, $authorised, $userid);
        } catch (Exception $e){
            return array(false, $e->getMessage());
        }
        $response->send('xml');
        return array(true, 'OK');
    }
    
     /**
     * Check if the access/refresh token exists
     * @param string $id - access/refresh token
     * @return string|bool token type or false
     */
    public function id_exists($id) {
        $result = $this->db->prepare("SELECT count(access_token) FROM oauth_access_tokens WHERE access_token = ?");
        $result->bind_param('s', $id);
        $result->execute();
        $result->bind_result($count);
        $result->fetch();
        $result->close();
        if ($count == 1) {
            return 'access_token';
        } else {
            $result = $this->db->prepare("SELECT count(refresh_token) FROM oauth_refresh_tokens WHERE refresh_token = ?");
            $result->bind_param('s', $id);
            $result->execute();
            $result->bind_result($count);
            $result->fetch();
            $result->close();
            if ($count == 1) {
                return 'refresh_token';
            } else {
                return false;
            }
        }
    }
    
    /**
     * Delete the access/refresh token
     * @param string $id - access/refresh token
     * @param string $type - token type
     * @return void
     */
    public function delete_auth($id, $type) {
        if ($type == 'access_token') {
            $result = $this->db->prepare("DELETE FROM oauth_access_tokens WHERE access_token = ?");
            $result->bind_param('s', $id);
            $result->execute();
            $result->close();
        } elseif ($type == 'refresh_token') {
            $result = $this->db->prepare("DELETE FROM oauth_refresh_tokens WHERE refresh_token = ?");
            $result->bind_param('s', $id);
            $result->execute();
            $result->close();
        }
    }
    
    /**
     * get the rogo user id of the oauth client
     * @param string $client - oauth client
     * @return int|bool - user id if one exists, false otherwise
     */
    public function get_client_user($client) {
        $result = $this->db->prepare("SELECT user_id FROM oauth_clients WHERE client_id = ?");
        $result->bind_param('s', $client);
        $result->execute();
        $result->store_result();
        $result->bind_result($userid);
        $result->fetch();
        if ($result->num_rows == 0) {
            $result->close();
            return false;
        }
        $result->close();
        return $userid;
    }
    
    /**
     * Check if client exists
     * @param string $client - oauth client
     * @return  bool
     */
    public function check_oauthclient($client) {
        $result = $this->db->prepare("SELECT count(client_id) FROM oauth_clients WHERE client_id = ?");
        $result->bind_param('s', $client);
        $result->execute();
        $result->bind_result($count);
        $result->fetch();
        $result->close();
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Delete the oauth client
     * @param string $client - oauth client
     * @return void
     */
    public function delete_oauthclient($client) {
        $result = $this->db->prepare("DELETE FROM oauth_clients WHERE client_id = ?");
        $result->bind_param('s', $client);
        $result->execute();
        $result->close();
        // Delete permissions.
        $this->delete_permissions($client);
    }
}
