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
* API XML functionality
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * API XML class
 */
class apixml extends \api\apiabstract {
    /**
     * Language pack component.
     */
    private $langcomponent = 'api/apixml';
    /**
     * XML data string
     */
    private $xml;
    /**
     * Status codes
     */
    private $statuscodes = array(
        'API_NO_PERMISSION' => 000
    );
    /**
     * Constructor
     * @param string $request - the xml request 
     */
    public function __construct($request) {
        $this->xml = $request;
    }
    
    /**
     * Validate the xml request againt an XSD
     * @param string $folder - sub dir where xsd is located
     * @param string $type - the filename
     * @return array - errors
     */
    public function validate($folder, $type) {
        // Enable user error handling.
        libxml_use_internal_errors(true);
        // Load dom object.
        $this->data = new \DOMDocument();
        $this->data->loadXML($this->xml);
        $schema = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . $folder
        . DIRECTORY_SEPARATOR . $type . '.xsd';
        $errorresp = array();
        if (!$this->data->schemaValidate($schema)) {
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                $errorresp[] = sprintf("[%s] Line %s - %s", $error->line, $error->code, trim($error->message));
            }
            libxml_clear_errors();
        }
        // Disable user error handling.
        libxml_use_internal_errors(false);
        return $errorresp;
    }
    
    /**
     * Parse the request and process it.
     * @param object $tasktype task object
     * @param array $fields expected fields
     * @param array $actions possible actions
     * @param array $task user permissions
     * @param integer $userid rogo user id linked to web service client
     * @return string - successful operation response or error response
     */
    public function parse($tasktype, $fields, $actions, $perms, $userid) {
        $langpack = new \langpack();
        $response = array();
        $xpath = new \DOMXPath($this->data); 
        foreach ($actions as $action) {
            $parentnode = $xpath->query($action);
            $error = false;
            foreach ($parentnode as $node) {
                if ($perms[$action]) {
                    foreach ($fields as $field) {
                        $item = $node->getElementsByTagName($field);
                        $item0 = $item->item(0);
                        if (!empty($item0)) {
                            if ($item0->childNodes->length > 1) {
                                $childarray = array();
                                foreach ($item0->childNodes as $childnode) {
                                    if ($childnode->nodeType != XML_TEXT_NODE) {
                                        $nodevalue = trim($childnode->nodeValue);
                                        if ($childnode->hasAttribute('id')) {
                                            $childarray[] = array('id' => $childnode->getAttribute('id'),
                                                'name' => $childnode->nodeName, 'value' => $nodevalue);
                                        } else {
                                            $childarray[] = array('name' => $childnode->nodeName,
                                                'value' => $nodevalue);
                                        }
                                    }
                                }
                                $params[$field] = $childarray;
                            } elseif (!is_null($item0->nodeValue)) {
                                $params[$field] = trim($item0->nodeValue);
                            }
                        }
                    }
                    if ($node->hasAttribute('id')) { 
                        $params['nodeid'] = $node->getAttribute('id');
                    }
                } else {
                    $error = true;
                    $data = array('statuscode' => $this->statuscodes['API_NO_PERMISSION'], 'status' => $langpack->get_string($this->langcomponent, 'nopermission'), 'id' => null);
                }
                if ($error) {
                    if ($node->hasAttribute('id')) { 
                        $response[] = $tasktype->get_response($data, $action, $node->getAttribute('id'));
                    } else {
                        $response[] = $tasktype->get_response($data, $action);
                    }
                } else {
                    $response[] = $tasktype->$action($params, $userid);
                }
            }
        }
        return $response;
    }
}