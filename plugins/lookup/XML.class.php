<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * The xml lookup class
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'outline_lookup.class.php';
include_once $configObject->get('cfg_web_root') . 'lang/en/include/common.php';

class XML_lookup extends outline_lookup
{
    public $impliments_api_lookup_version = 1;
    public $version = 0.9;

    public function register_callback_routines()
    {
        $callbackarray[] = array(array($this, 'userlookup'), 'userlookup', $this->number, $this->name);
        $callbackarray[] = array(array($this, 'modulelookup'), 'modulelookup', $this->number, $this->name);
        return $callbackarray;
    }

    //need function to register the extracallback
    public function register_callback_sections()
    {
        //this is blank so that classes that dont register anything dont break
        return array('userlookupxmltranslate','modulelookupxmltranslate');
    }

    public function modulelookup($lookupobj)
    {
        $this->savetodebug('The XML modulelookup function has been called');
        if (!isset($this->settings['modulelookup'])) {
            $this->savetodebug('There is no config for module lookup');
            return $lookupobj;
        }

        if (isset($this->settings['modulelookup']['mandatoryurlfields'])) {
            // mandatory fields required!
            foreach ($this->settings['modulelookup']['mandatoryurlfields'] as $index) {
                $fieldname = $this->settings['modulelookup']['urlfields'][$index];
                if (!isset($lookupobj->lookupdata->$fieldname)) {
                          //mandatory field not found
                            $this->savetodebug("Mandatory field of $fieldname for $index required for this search but not found");
                          return $lookupobj;
                }
            }
        }

        //check if restrict is set
        $restrict = $this->get_setting('restrict', 'modulelookup');
        $restrictstop = false;
        if (!is_null($restrict)) {
            foreach ($restrict as $key => $value) {
                if (!isset($lookupobj->lookupdata->$key)) {
                    $this->savetodebug("Restriction stopped debug key: $key not found");
                    $restrictstop = true;
                } else {
                    if (mb_strpos($value, '|') === false) {
                        //condition
                        if (!isset($lookupobj->lookupdata->$key) or (isset($lookupobj->lookupdata->$key) and $lookupobj->lookupdata->$key !== $value)) {
                            $this->savetodebug("Restriction stopped debug key: $key !== $value");
                            $restrictstop = true;
                        }
                    } else {
                        // OR condition
                          $restrictstop1 = 0;
                        $exp = explode('|', $value);
                        foreach ($exp as $value1) {
                            if ($lookupobj->lookupdata->$key === $value1) {
                                        $restrictstop1++;
                            }
                        }
                        if ($restrictstop1 == 0) {
                            $this->savetodebug("Restriction stopped debug one of the ORs of $value in the key $key");
                            $restrictstop = true;
                        }
                    }
                }
            }
        }

        if ($restrictstop !== false) {
            $this->savetodebug('Restriction stopped function running');
            return $lookupobj;
        }

        // if the lookup doesnt have these set and the default for the module configuration exist use them
        if (!isset($lookupobj->settings->override)) {
            if (isset($this->settings['modulelookup']['override'])) {
                    $overrideset = true;
                foreach ($this->settings['modulelookup']['override'] as $key => $value) {
                    $lookupobj->settings->override[$key] = $value;
                }
                $this->savetodebug('Overriding settings from modulelookup as none supplied');
            } elseif (isset($this->settings['override'])) {
                  $overrideset = true;
                foreach ($this->settings['override'] as $key => $value) {
                    $lookupobj->settings->override[$key] = $this->settings['override'][$value];
                }
                $this->savetodebug('Overriding settings from xml plugin as none supplied');
            }
        }
        if (!isset($lookupobj->settings->overrideall)) {
            if (isset($this->settings['modulelookup']['overrideall'])) {
                    $overrideallset = true;
                    $lookupobj->settings->overrideall = $this->settings['modulelookup']['overrideall'];
                    $this->savetodebug('Overriding all settings from modulelookup as none supplied');
            } elseif (isset($this->settings['overrideall'])) {
                  $overrideallset = true;
                  $lookupobj->settings->overrideall = $this->settings['overrideall'];
                  $this->savetodebug('Overriding all settings  from xml plugin as none supplied');
            }
        }



        $url = $this->settings['baseurl'];
        if (isset($this->settings['modulelookup']['url'])) {
            $url .= $this->settings['modulelookup']['url'];
        }
        if (isset($this->settings['modulelookup']['urlfields'])) {
            foreach ($this->settings['modulelookup']['urlfields'] as $urlparam => $index) {
                    //$this->savetodebug('appending url ' . 'urlparam' . ' :: ' . $index);
                if (isset($lookupobj->lookupdata->$index)) {
                    //a field that can be supplied as argument
                    $url .= '&' . $urlparam . '=' . $lookupobj->lookupdata->$index;
                }
            }
        }

        $this->savetodebug('URL is: ' . $url);
        //setting options for curl retrieval eg username/password or form submission

        $usefile = true;
        if ($usefile == true) {
            $returned_data = @file_get_contents($url);
            $xml = false;
            if ($returned_data !== false) {
                try {
                    $xml = new SimpleXMLElement($returned_data);
                } catch (Exception $e) {
                    throw new Exception('SimpleXMLElement creation has thrown', 0, $e);
                }
            }
        }
        if ($xml == false) {
            $this->savetodebug('No valid XML received');
            return $lookupobj;
        }


        list($callbacklist, $callbackregisterdatalist) = $this->get_callback('modulelookupxmltranslate');
        //  run any appropriate translation callbacks

        if (is_array(($callbacklist))) {
            //foreach ($this->calling_object->callbackregister['lookupuser'] as $number => $callback) {
            foreach ($callbacklist as $number => $callback) {
                $xml = call_user_func_array($callback, array($xml));
                $objid = key($callbackregisterdatalist[$number]);
                $new_messages = $this->get_new_debug_messages($objid);
                foreach ($new_messages as $key => $value) {
                          $info1 = $this->get_module_lookupinfo($objid);
                          $info = key($info1) . ':' . current($info1);
                          $this->savetodebug("Module Lookup XML Translate:authObj($info)[$number:$key]: $value");
                }
            }
        }


        $this->savetodebug('XML is: ' . var_export($xml, true));
        $lookupobj = $this->xmlsearch($xml, $lookupobj, 'modulelookup');
        if (isset($overrideallset) and $overrideallset == true) {
            unset($lookupobj->settings->overrideall);
        }
        if (isset($overrideset) and $overrideset == true) {
            unset($lookupobj->settings->override);
        }


        return $lookupobj;
    }

    public function userlookup($lookupobj)
    {
        $searchsuccess = false;
        $usefile = false;
        $this->savetodebug('The XML userlookup function has been called');
        if (!isset($this->settings['userlookup'])) {
            $this->savetodebug('There is no config for module lookup');
            return $lookupobj;
        }

        if (isset($this->settings['userlookup']['disabled']) and $this->settings['userlookup']['disabled'] === true) {
            $this->savetodebug('disabled userlookup in this context');
            return $lookupobj;
        }

        if (isset($this->settings['userlookup']['mandatoryurlfields'])) {
            // mandatory fields required!
            foreach ($this->settings['userlookup']['mandatoryurlfields'] as $index) {
                $fieldname = $this->settings['userlookup']['urlfields'][$index];
                if (!isset($lookupobj->lookupdata->$fieldname)) {
                          //mandatory field not found
                            $this->savetodebug("Mandatory field of $fieldname for $index required for this search but not found");
                          return $lookupobj;
                }
            }
        }

        // if the lookup doesnt have these set and the default for the module configuration exist use them
        if (!isset($lookupobj->settings->override)) {
            if (isset($this->settings['userlookup']['override'])) {
                    $overrideset = true;
                foreach ($this->settings['userlookup']['override'] as $key => $value) {
                    $lookupobj->settings->override[$key] = $value;
                }
                $this->savetodebug('Overriding settings from userlookup as none supplied');
            } elseif (isset($this->settings['override'])) {
                  $overrideset = true;
                foreach ($this->settings['override'] as $key => $value) {
                    $lookupobj->settings->override[$key] = $this->settings['override'][$value];
                }
                $this->savetodebug('Overriding settings from xml plugin as none supplied');
            }
        }
        if (!isset($lookupobj->settings->overrideall)) {
            if (isset($this->settings['userlookup']['overrideall'])) {
                    $overrideallset = true;
                    $lookupobj->settings->overrideall = $this->settings['userlookup']['overrideall'];
                    $this->savetodebug('Overriding all settings from userlookup as none supplied');
            } elseif (isset($this->settings['overrideall'])) {
                  $overrideallset = true;
                  $lookupobj->settings->overrideall = $this->settings['overrideall'];
                  $this->savetodebug('Overriding all settings  from xml plugin as none supplied');
            }
        }

        $url = $this->settings['baseurl'];
        if (isset($this->settings['userlookup']['url'])) {
            $url .= $this->settings['userlookup']['url'];
        }
        if (isset($this->settings['userlookup']['urlfields'])) {
            foreach ($this->settings['userlookup']['urlfields'] as $urlparam => $index) {
                if (isset($lookupobj->lookupdata->$index)) {
                    //a field that can be supplied as argument
                    $url .= '&' . $urlparam . '=' . $lookupobj->lookupdata->$index;
                }
            }
        }

        $this->savetodebug('URL is: ' . $url);
        //setting options for curl retrieval eg username/password or form submission

        $usefile = true;
        if ($usefile == true) {
            $returned_data = @file_get_contents($url);
            $xml = false;
            if ($returned_data !== false) {
                try {
                    $xml = new SimpleXMLElement($returned_data);
                } catch (Exception $e) {
                    throw new Exception('SimpleXMLElemnt creation has thrown', 0, $e);
                }
            }
        }
        if ($xml == false) {
            $this->savetodebug('No valid XML received');
            return $lookupobj;
        }
        //do translate lookup
        list($callbacklist, $callbackregisterdatalist) = $this->get_callback('userlookupxmltranslate');
        //  run this when needing to store auth data to session

        if (is_array(($callbacklist))) {
            foreach ($callbacklist as $number => $callback) {
                $xml = call_user_func_array($callback, array($xml));
                $objid = key($callbackregisterdatalist[$number]);
                $new_messages = $this->get_new_debug_messages($objid);
                foreach ($new_messages as $key => $value) {
                          $info1 = $this->get_module_lookupinfo($objid);
                          $info = key($info1) . ':' . current($info1);
                          $this->savetodebug("User Lookup XML Translate:authObj($info)[$number:$key]: $value");
                }
            }
        }

        $this->savetodebug('XML is: ' . var_export($xml, true));
        $lookupobj = $this->xmlsearch($xml, $lookupobj, 'userlookup');
        if (isset($overrideallset) and $overrideallset == true) {
            unset($lookupobj->settings->overrideall);
        }
        if (isset($overrideset) and $overrideset == true) {
            unset($lookupobj->settings->override);
        }

        return $lookupobj;
    }

    public function xmlsearch($xml, $lookupobj, $section)
    {
        $searchsuccess = false;
        $oneitemreturned = false;
        $oneitemreturned = $this->get_setting('oneitemreturned', $section);
        if ($oneitemreturned !== true) {
            $searchsuccess = false;
            foreach ($lookupobj->searchorder as $keyno => $orderitem) {
                $filter = '';
                if (is_array($orderitem)) {
                    $countcheck = 0;
                    $countcheck2 = 0;
                    $count = count($orderitem);
                    $filter = '(&';
                    foreach ($orderitem as $item) {
                        if (count(array_keys($ldap_attributes, $item)) > 0) {
                            //searching item exists in ldap attribute so we can search
                            $countcheck++;
                            //check if we have any data for this item to actually search for
                            if (isset($lookupobj->lookupdata->{$item})) {
                                $countcheck2++;
                                $filter .= $this->create_filter($ldap_attributes, $item, $lookupobj->lookupdata->{$item});
                            }
                        }
                    }
                      $filter .= ')';
                    if (!(($countcheck == $countcheck2) and ($countcheck == $count))) {
                        $filter = '';
                    }

                      //multiple filter option use and
                } else {
                    //single filter option
                    if (count(array_keys($ldap_attributes, $orderitem)) > 0) {
                        //searching item exists in ldap attribute so we can search
                        //check if we have any data for this item to actually search for
                        if (isset($lookupobj->lookupdata->{$orderitem})) {
                            $filter = $this->create_filter($ldap_attributes, $orderitem, $lookupobj->lookupdata->{$orderitem});
                        }
                    } //else skip as cant search for this as we dont have corresponding attribute
                }

                if ($searchsuccess == true) {
                    break;
                }
                //end of searchorder loop
            }

            //TODO ABOVE IS SUSPECT AS STRAIGHT FROM LDAP

            //need to lookup up the xpath info
            //above block is for creating xpath filter for xml
        } else {
            $filter = '//*/parent::*';
        }
        $filter1 = $this->get_setting('filter', $section);
        if (!is_null($filter1)) {
            $filter = $filter1;
        }
        $this->savetodebug("Using search filter: $filter");
        $xmlsearched = $xml->xpath($filter);
        $this->savetodebug('XML IS NOW: ' . var_export($xmlsearched, true));
        //have just the number of simplexmlobjects we are interested in.

        $count = count($xmlsearched);
        if ($count > 0) {
            //check items in the record
            if ($count > 1) {
                $lookupobj->multiple = true;
            }

            $attributes = $this->get_setting('xmlfields', $section);
            $rawattributes = $this->get_setting('rawxmlfields', $section);
            $failattributes = $this->get_setting('failfields', $section);
            //check for fail attributes
            $fail = false;
            if (is_array($failattributes) and count($failattributes) > 0) {
                foreach ($xmlsearched as $xmlbits) {
                    foreach ($failattributes as $key => $value) {
                        if (isset($xmlbits->$key)) {
                                $fail = $key;
                                $this->savetodebug("Failure key $key found");
                        }
                    }
                    $lookupobj = $this->store_in_data($xmlbits, $failattributes, $lookupobj, $section);
                }
            }

            //if failure attribute found stop at state why
            if ($fail !== false) {
                return $lookupobj;
            }


            $this->savetodebug("Found $count records");
            if (isset($lookupobj->settings->firstentry) and $lookupobj->settings->firstentry == true) {
                //only
                  $this->savetodebug('Saving First Entry Only');
                $datablock = $xmlsearched[0];
                $lookupobj = $this->store_in_data($datablock, $attributes, $lookupobj, $section);
            } elseif (isset($lookupobj->settings->lastentry) and $lookupobj->settings->lastentry == true) {
                //
                  $this->savetodebug('Saving Last Entry Only');
                $datablock = $xmlsearched[$count - 1];
                $lookupobj = $this->store_in_data($datablock, $attributes, $lookupobj, $section);
            } else {
                foreach ($xmlsearched as $numb => $datablock) {
                    $this->savetodebug("Saving Entry #$numb");
                    $this->savetodebug('Datablock IS NOW: ' . var_export($datablock, true));
                    if (is_array($attributes) and count($attributes) > 0) {
                        $lookupobj = $this->store_in_data($datablock, $attributes, $lookupobj, $section);
                    }
                    if (is_array($rawattributes) and count($rawattributes) > 0) {
                            $lookupobj = $this->store_in_data($datablock, $rawattributes, $lookupobj, $section, true);
                    }
                }
            }

            return $lookupobj;
        } else {
            //no records found!

                $this->savetodebug('No Records Match');
            return $lookupobj;
        }
    }

    public function store_in_data($datablock, $attributes, $lookupobj, $section, $raw = false)
    {
        $prepend = '';
        if ((isset($this->settings['lowercasecompare']) and $this->settings['lowercasecompare'] == true) or (isset($this->settings[$section]['lowercasecompare']) and $this->settings[$section]['lowercasecompare'] == true)) {
            $this->savetodebug('Setting attributes to lowercase');
            foreach ($attributes as $key => $value) {
                $attributes[mb_strtolower($key)] = $value;
            }
        }
        if (isset($this->settings['storeprepend'])) {
            $prepend = $this->settings['storeprepend'];
            $this->savetodebug("Setting prepend to $prepend");
        }
        if (isset($this->settings[$section]['storeprepend'])) {
            $prepend = $this->settings[$section]['storeprepend'];
            $this->savetodebug("Setting prepend to $prepend");
        }
        foreach ($attributes as $key => $value) {
            $keyorig = $key;
            if ((isset($this->settings['lowercasecompare']) and $this->settings['lowercasecompare'] == true) or (isset($this->settings[$section]['lowercasecompare']) and $this->settings[$section]['lowercasecompare'] == true)) {
                    $key = mb_strtolower($key);
                    //think this actually needs to change the datablock without changing the original datablock
            }
            $reverse_attribute = $value;
            if (isset($datablock->$key) and (((isset($lookupobj->lookupdata->$reverse_attribute)) and ((isset($lookupobj->settings->overrideall) and $lookupobj->settings->overrideall == true) or ((isset($lookupobj->settings->override[$key]) and $lookupobj->settings->override[$key] == true) or (isset($lookupobj->settings->override[$reverse_attribute]) and $lookupobj->settings->override[$reverse_attribute] == true)))) or (!isset($lookupobj->lookupdata->$reverse_attribute)))) {
                  // store data to lookup if XML attribute listed and ( not set or if set and ( overrideall or override value or override inverse ldap se+t))

                if ($raw === false) {
                    $lookupobj->lookupdata->$reverse_attribute = (string)$datablock->$key;
                } else {
                    $lookupobj->lookupdata->$reverse_attribute = $datablock->$key;
                }
                $this->savetodebug("saving value for $reverse_attribute using XML attribute: $key");
            }
            if (isset($datablock[$key][0]) and !isset($lookupdatas->$reverse_attribute)) {
                $lookupdatas->$reverse_attribute = $datablock[$key][0];
            }
        }
        if (!isset($lookupdatas)) {
            $lookupdatas = new stdClass();
        }
        $lookupobj->lookupdatas[] = $lookupdatas;
        $datablockstore = array();
        foreach ($datablock as $key => $value) {
            if (!is_int($key)) {
                if (isset($this->settings['lowercasecompare']) and $this->settings['lowercasecompare'] == true) {
                    $key = mb_strtolower($key);
                }

                if ((isset($attributes[$key]))) {
                    $gdgdfgdsgds = 1;
                }

                if (((isset($lookupobj->datablockstore[$prepend . $key])) and ((isset($lookupobj->settings->overrideall) and $lookupobj->settings->overrideall == true) or ((isset($lookupobj->settings->override[$key]) and $lookupobj->settings->override[$key] == true)))) or (!isset($lookupobj->datablockstore[$prepend . $key]))) {
                    // store data to datablock store if not set or if set and ( overrideall or override value set)
                    if ($raw === false) {
                        $lookupobj->datablockstore[$prepend . $key] = (string)$value;
                    } else {
                        $lookupobj->datablockstore[$prepend . $key] = $value;
                    }
                }

                if ($raw === false) {
                    $datablockstore[$prepend . $key] = (string)$value;
                } else {
                    $datablockstore[$prepend . $key] = $value;
                }
            }
        }
        $lookupobj->datablockstores[] = $datablockstore;
        return $lookupobj;
    }

    public function get_setting($item, $section)
    {
        unset($data);
        if (isset($this->settings[$section][$item])) {
            $data = $this->settings[$section][$item];
            return $data;
        } elseif (isset($this->settings[$item])) {
            $data = $this->settings[$item];
            return $data;
        } else {
            return null;
        }
    }
}
