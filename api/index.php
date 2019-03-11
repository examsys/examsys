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
* API routing functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

require_once '../include/load_config.php';

$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), 
                                 $configObject->get('cfg_db_webservice_user'), 
                                 $configObject->get('cfg_db_webservice_passwd'), 
                                 $configObject->get('cfg_db_database'), 
                                 $configObject->get('cfg_db_charset'), 
                                 UserNotices::get_instance(), 
                                 $configObject->get('dbclass'));

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$app = new \Slim\App();
$oauth = new oauth($configObject);
$render = new render($configObject);
$langpack = new \langpack();

// Set up api.
$api = new \api\api($app, $mysqli, $configObject);

// Only routes only available if enabled.
if ($configObject->get_setting('core', 'cfg_api_enabled')) {

    // Request oauth token.
    $app->post('/requesttoken', function(ServerRequestInterface $request, ResponseInterface $response) use($oauth, $api) {
        // Set api.
        $api->request = $request;
        $api->response = $response;
        $api->set_header();
        $oauth->request_token();
    });

    // Enrolment request.
    $app->post('/modulemanagement/enrol', function(ServerRequestInterface $request, ResponseInterface $response) use($api, $mysqli, $oauth, $render, $langpack) {
        $requesttype = 'modulemanagement';
        $responsetype = 'moduleManagementEnrolResponse';
        $operations = array('enrol', 'unenrol');
        $fields = array('userid', 'attempt', 'moduleid', 'session', 'studentid', 'moduleextid', 'moduleextsys');
        $xsd = 'enrolrequest';
        process($requesttype, $operations, $fields, $responsetype, $oauth, $api, $langpack, $render, $xsd, $mysqli, $response, $request);
    });

    // Module management request.
    $app->post('/modulemanagement', function(ServerRequestInterface $request, ResponseInterface $response) use($api, $mysqli, $oauth, $render, $langpack) {
        $requesttype = 'modulemanagement';
        $responsetype = 'moduleManagementResponse';
        $operations = array('create', 'update', 'delete');
        $fields = array('id', 'modulecode', 'name', 'school', 'faculty', 'sms', 'externalid', 'schoolextid', 'externalsys', 'newexternalid');
        $xsd = 'managementrequest';
        process($requesttype, $operations, $fields, $responsetype, $oauth, $api, $langpack, $render, $xsd, $mysqli, $response, $request);
    });

    // Course management request.
    $app->post('/coursemanagement', function(ServerRequestInterface $request, ResponseInterface $response) use($api, $mysqli, $oauth, $render, $langpack) {
        $requesttype = 'coursemanagement';
        $responsetype = 'courseManagementResponse';
        $operations = array('create', 'update', 'delete');
        $fields = array('id', 'name', 'description', 'school', 'faculty', 'externalid', 'schoolextid', 'externalsys');
        $xsd = 'managementrequest';
        process($requesttype, $operations, $fields, $responsetype, $oauth, $api, $langpack, $render, $xsd, $mysqli, $response, $request);
    });

    // School management request.
    $app->post('/schoolmanagement', function(ServerRequestInterface $request, ResponseInterface $response) use($api, $mysqli, $oauth, $render, $langpack) {
        $requesttype = 'schoolmanagement';
        $responsetype = 'schoolManagementResponse';
        $operations = array('create', 'update', 'delete');
        $fields = array('id', 'name', 'faculty', 'externalid', 'facultyextid', 'code', 'externalsys');
        $xsd = 'managementrequest';
        process($requesttype, $operations, $fields, $responsetype, $oauth, $api, $langpack, $render, $xsd, $mysqli, $response, $request);
    });

    // Faculty management request.
    $app->post('/facultymanagement', function(ServerRequestInterface $request, ResponseInterface $response) use($api, $mysqli, $oauth, $render, $langpack) {
        $requesttype = 'facultymanagement';
        $responsetype = 'facultyManagementResponse';
        $operations = array('create', 'update', 'delete');
        $fields = array('id', 'name', 'externalid', 'code', 'externalsys');
        $xsd = 'managementrequest';
        process($requesttype, $operations, $fields, $responsetype, $oauth, $api, $langpack, $render, $xsd, $mysqli, $response, $request);
    });

    // User management request.
    $app->post('/usermanagement', function(ServerRequestInterface $request, ResponseInterface $response) use($api, $mysqli, $oauth, $render, $langpack) {
        $requesttype = 'usermanagement';
        $responsetype = 'userManagementResponse';
        $operations = array('create', 'update', 'delete');
        $fields = array('id', 'username', 'title', 'forename', 'surname', 'initials', 'email', 'password',
            'course', 'gender', 'year', 'role', 'studentid', 'modules');
        $xsd = 'managementrequest';
        process($requesttype, $operations, $fields, $responsetype, $oauth, $api, $langpack, $render, $xsd, $mysqli, $response, $request);
    });
    // Assessment management request
    $app->post('/assessmentmanagement', function(ServerRequestInterface $request, ResponseInterface $response) use($api, $mysqli, $oauth, $render, $langpack) {
        $requesttype = 'assessmentmanagement';
        $responsetype = 'assessmentManagementResponse';
        $operations = array('create', 'schedule', 'delete', 'update');
        $fields = array('id', 'owner', 'type', 'title', 'startdatetime', 'enddatetime', 'modules', 'session', 'labs', 'month',
            'cohort_size', 'sittings', 'barriers', 'campus', 'notes', 'timezone', 'duration', 'externalid', 'externalsys', 'extmodules');
        $xsd = 'managementrequest';
        process($requesttype, $operations, $fields, $responsetype, $oauth, $api, $langpack, $render, $xsd, $mysqli, $response, $request);
    });
    /**
     * Gradebook consumption request
     * 
     * @param mysqli $mysqli - db connection
     * @param object $oauth - oauth object
     * @param object $api - api object
     * @param object $render - render object
     * @param object $langpack - language object
     * @return object
     */
    $app->get('/gradebook/{filtername}/{filterid}', function(ServerRequestInterface $request, ResponseInterface $response, $args) use($mysqli, $oauth, $api, $render, $langpack) {
        // Set api.
        $api->request = $request;
        $api->response = $response;
        $api->set_header();
        // Log request.
        $apiid = $api->log_request();

        // Check for auth tokens
        $client_id = $oauth->check_auth();
        if ($client_id == 'INVALID_TOKEN') {
            $response_xml = $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', 'invalidtoken')));
            $api->log_response($apiid, $response_xml);
            echo $response_xml;
        } else {
            //Check Permission
            if (!$oauth->check_permissions('gradebook', $client_id)) {
                $response_xml = $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', 'nopermission')));
                $api->log_response($apiid, $response_xml);
                echo $response_xml;
            } else {
            
                $resp = array();
                $gradebook = new \api\gradebook($mysqli);
                // Map template.
                if (in_array($args['filtername'], array('paper', 'extpaper'))) {
                    $templatename = 'paper';
                } else {
                    $templatename = 'module';
                }
                $external = new \external_systems();
                // What external system is the client mapped to.
                $user_id = $oauth->get_client_user($client_id);
                $extsysinfo = $external->get_mapped_externalsystem_info($client_id);
                $extsys = $extsysinfo['name'];
                // Process the request.
                $request = $gradebook->get($args['filtername'], $args['filterid'], $extsys);
                $resp = $request[1];
                if ($request[0] == 'OK') {
                    $template = 'api/' . $templatename . '_gradebook.xml';
                } else {
                    $template = 'api/error.xml';
                }
            
                // Render response.
                $response_xml = $render->render_xml($template, 'gradebookResponse', $resp);
                $api->log_response($apiid, $response_xml);
                $body = $response->getBody();
                $body->write($response_xml);
                return $response;
            }
        }
    });
}

$container = $app->getContainer();
/**
 * 404 error handling.
 * @param object slim container
 * @param object $render - render object
 * @param object $api - api object
 * @param object $langpack - language object
 * @return object
 */
$container['notFoundHandler'] = function ($container, $render, $api, $langpack) {
    return function ($request, $response) use ($container, $render, $api, $langpack) {
        // Set api.
        $api->request = $request;
        $api->response = $response;
        // Log request.
        $apiid = $api->log_request();
        $response_xml = $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', '404')));
        $api->log_response($apiid, $response_xml);
        return $container['response']->withStatus(404)
            ->withHeader('Content-Type', 'text/xml')
            ->write($response_xml);
    };
};

/**
 * 500 error handling.
 * @param object slim container
 * @param object $render - render object
 * @param object $api - api object
 * @param object $langpack - language object
 * @return object
 */
$container['errorHandler'] = function ($container, $render, $api, $langpack) {
    return function ($request, $response, $exception) use ($container, $render, $api, $langpack) {
        // Set api.
        $api->request = $request;
        $api->response = $response;
        // Log request.
        $apiid = $api->log_request();
        $response_xml = $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', '500')));
        $api->log_response($apiid, $response_xml);
        return $container['response']->withStatus(500)
            ->withHeader('Content-Type', 'text/xml')
            ->write($response_xml);
    };
};

/**
 * Process the web service request.
 * 
 * All request are authenticated, validated and processed.
 * @param string $requesttype - name of request
 * @param array $operations - operations available in request
 * @param array $fields - expected request fields
 * @param string $responsetype - name of response
 * @param object $oauth - oauth object
 * @param object $api - api object
 * @param object $langpack - language object
 * @param object $render - render object
 * @param string $xsd - xsd filename
 * @param mysqli $mysqli - db connection
 * @param object $response - response interface
 * @param object $request - request interface
 * @return object
 */
function process ($requesttype, $operations, $fields, $responsetype, $oauth, $api, $langpack, $render, $xsd, $mysqli, $response, $request) {
    // Set api.
    $api->request = $request;
    $api->response = $response;
    $api->set_header();
    // Log request.
    $apiid = $api->log_request();

    // Check for auth tokens
    $client_id = $oauth->check_auth();
    if ($client_id == 'INVALID_TOKEN') {
        $response_xml = $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', 'invalidtoken')));
        $api->log_response($apiid, $response_xml);
        echo $response_xml;
    } else {
        //Check Permissions
        foreach ($operations as $operation) {
            $perm[$operation] = $oauth->check_permissions($requesttype . '/' . $operation, $client_id);
        }

        // Log request.
        $apiid = $api->log_request();
        
        // Check media type - only text/xml supported currently.
        if (!$api->get_mediatype()) {
            $response_xml = $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', 'mediatype')));
            $api->log_response($apiid, $response_xml);
            echo $response_xml;
        } else {
            $responsedata = array();
            $classname = '\\api\\' . $requesttype;
            $requestobject = new $classname($mysqli, $client_id);

            // Process the request.
            $data = $api->process($requesttype, $xsd, $request);
            
            // XML.
            $user_id = $oauth->get_client_user($client_id);
            if ($data[0] == 'OK') {
                $responsedata = $api->parse($requestobject, $fields, $operations, $perm, $user_id);
                $template = 'api/success.xml';
            } else {
                $responsedata = $data[1];
                $template = 'api/error.xml';
            }
            
            // Render response.
            $response_xml = $render->render_xml($template, $responsetype, $responsedata);
            $api->log_response($apiid, $response_xml);
            $body = $response->getBody();
            $body->write($response_xml);
            return $response;
        }
    }
}

$app->run();