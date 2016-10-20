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
                                     
$app = new \Slim\Slim();
$oauth = new oauth($configObject);
$render = new render($configObject);
$langpack = new \langpack();

// Set up api.
$api = new \api\api($app, $mysqli, $configObject);
$api->set_header();

// Only routes only available if enabled.
if ($configObject->get_setting('core', 'cfg_api_enabled')) {

    // Request oauth token.
    $app->post('/requesttoken', function() use($oauth) {
        $oauth->request_token();
    });

    // Enrolment request.
    $app->post('/modulemanagement/enrol', function() use($api, $mysqli, $oauth, $render, $langpack) {
        $request = 'modulemanagement';
        $response = 'moduleManagementEnrolResponse';
        $operations = array('enrol', 'unenrol');
        $fields = array('userid', 'attempt', 'moduleid', 'session', 'studentid', 'moduleextid');
        $xsd = 'enrolrequest';
        process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli);
    });

    // Module management request.
    $app->post('/modulemanagement', function() use($api, $mysqli, $oauth, $render, $langpack) {
        $request = 'modulemanagement';
        $response = 'moduleManagementResponse';
        $operations = array('create', 'update', 'delete');
        $fields = array('id', 'modulecode', 'name', 'school', 'faculty', 'sms', 'externalid', 'schoolextid', 'externalsys');
        $xsd = 'managementrequest';
        process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli);
    });

    // Course management request.
    $app->post('/coursemanagement', function() use($api, $mysqli, $oauth, $render, $langpack) {
        $request = 'coursemanagement';
        $response = 'courseManagementResponse';
        $operations = array('create', 'update', 'delete');
        $fields = array('id', 'name', 'description', 'school', 'faculty', 'externalid', 'schoolextid', 'externalsys');
        $xsd = 'managementrequest';
        process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli);
    });

    // School management request.
    $app->post('/schoolmanagement', function() use($api, $mysqli, $oauth, $render, $langpack) {
        $request = 'schoolmanagement';
        $response = 'schoolManagementResponse';
        $operations = array('create', 'update', 'delete');
        $fields = array('id', 'name', 'faculty', 'externalid', 'facultyextid', 'code', 'externalsys');
        $xsd = 'managementrequest';
        process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli);
    });

    // Faculty management request.
    $app->post('/facultymanagement', function() use($api, $mysqli, $oauth, $render, $langpack) {
        $request = 'facultymanagement';
        $response = 'facultyManagementResponse';
        $operations = array('create', 'update', 'delete');
        $fields = array('id', 'name', 'externalid', 'code', 'externalsys');
        $xsd = 'managementrequest';
        process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli);
    });

    // User management request.
    $app->post('/usermanagement', function() use($api, $mysqli, $oauth, $render, $langpack) {  
        $request = 'usermanagement';
        $response = 'userManagementResponse';
        $operations = array('create', 'update', 'delete');
        $fields = array('id', 'username', 'title', 'forename', 'surname', 'initials', 'email', 'password',
            'course', 'gender', 'year', 'role', 'studentid', 'modules');
        $xsd = 'managementrequest';
        process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli);
    });
    // Assessment management request
    $app->post('/assessmentmanagement', function() use($api, $mysqli, $oauth, $render, $langpack) {  
        $request = 'assessmentmanagement';
        $response = 'assessmentManagementResponse';
        $operations = array('create', 'schedule', 'delete', 'update');
        $fields = array('id', 'owner', 'type', 'title', 'startdatetime', 'enddatetime', 'modules', 'session', 'labs', 'month',
            'cohort_size', 'sittings', 'barriers', 'campus', 'notes', 'timezone', 'duration', 'externalid', 'externalsys', 'extmodules');
        $xsd = 'managementrequest';
        process($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli);    
    });
    /**
     * Gradebook consumption request
     * 
     * @param mysqli $mysqli - db connection
     * @param object $oauth - oauth object
     * @param object $api - api object
     * @param object $render - render object
     * @param object $langpack - language object
     */
    $app->get('/gradebook/:filtername/:filterid', function($filtername, $filterid) use($mysqli, $oauth, $api, $render, $langpack) {
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
            
                $response = array();
                $gradebook = new \api\gradebook($mysqli);
                // Map temnplate.
                if (in_array($filtername, array('paper', 'extpaper'))) {
                    $templatename = 'paper';
                } else {
                    $templatename = 'module';
                }
                // Process the request.
                $request = $gradebook->get($filtername, $filterid);
                $response = $request[1];
                if ($request[0] == 'OK') {
                    $template = 'api/' . $templatename . '_gradebook.xml';
                } else {
                    $template = 'api/error.xml';
                }
            
                // Render response.
                $response_xml = $render->render_xml($template, 'gradebookResponse', $response);
                $api->log_response($apiid, $response_xml);
                echo $response_xml;
            }
        }
    });
}
/**
 * 404 error handling.
 *
 * @param object $render - render object
 * @param object $api - api object
 * @param object $langpack - language object
 */
$app->notFound(function () use ($render, $api, $langpack) {
    
    // Log request.
    $apiid = $api->log_request();
    
    $response_xml = $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', '404')));
    $api->log_response($apiid, $response_xml);
    echo $response_xml;
});
/**
 * 500 error handling.
 *
 * @param object $render - render object
 * @param object $api - api object
 * @param object $langpack - language object
 */
$app->error(function (\Exception $e) use ($render, $api, $langpack) {
    
    // Log request.
    $apiid = $api->log_request();
    
    $response_xml = $render->render_xml('api/error.xml', 'rogo', array($langpack->get_string('api/commonapi', '500')));
    $api->log_response($apiid, $response_xml);
    echo $response_xml;
});

/**
 * Process the web wervice request.
 * 
 * All request are authenticated, validated and processed.
 * @param string $request - name of request
 * @param array $operations - operations available in request
 * @param array $fields - expected request fields
 * @param string $response - name of response
 * @param object $oauth - oauth object
 * @param object $api - api object
 * @param object $langpack - language object
 * @param object $render - render object
 * @param string $xsd - xsd filename
 * @param mysqli $mysqli - db connection 
 */
function process ($request, $operations, $fields, $response, $oauth, $api, $langpack, $render, $xsd, $mysqli) {
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
            $perm[$operation] = $oauth->check_permissions($request . '/' . $operation, $client_id);
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
            $classname = '\\api\\' . $request;
            $requestobject = new $classname($mysqli);

            // Process the request.
            $data = $api->process($request, $xsd);
            
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
            $response_xml = $render->render_xml($template, $response, $responsedata);
            $api->log_response($apiid, $response_xml);
            echo $response_xml;
        }
    }
}

$app->run();