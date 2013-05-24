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
 * The xml lookup class
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once 'outline_lookup.class.php';

include_once $configObject->get('cfg_web_root') . 'lang/en/include/common.inc';


class XML_lookup extends outline_lookup {
  public $impliments_api_lookup_version = 1;
  public $version = 0.9;

  function register_callback_routines() {
    $callbackarray[] = array(array($this, 'userlookup'), 'userlookup', $this->number, $this->name);


    return $callbackarray;
  }

  //need function to register the extracallback
  function register_callback_sections() {
    //this is blank so that classes that dont register anything dont break
    return array('userlookupxmltranslate');
  }

  function userlookup($lookupobj) {
    $searchsuccess = false;
    $usefile = false;


    $this->savetodebug('The UoNSaturn userlookup function has been called');


//    $this->savetodebug('Received data:' . var_export($lookupobj, true));


    if (isset($this->settings['userlookup']['mandatoryurlfields'])) {
      // mandatory fields required!
      foreach ($this->settings['userlookup']['mandatoryurlfields'] as $index) {
        if (!isset($lookupobj->lookupdata->$index)) {
          //mandatory field not found
          $this->savetodebug("Mandatory field of $index required for this search but not found");

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
          throw new Exception('SimpleXMLElemnt creation has thrown', 0, $e);
        }
      }
    }
    if ($xml == false) {
      $this->savetodebug('No valid XML received');

      return $lookupobj;
    }
    //do translate lookup
    list($callbacklist, $callbackregisterdatalist) = $this->get_callback('userlookupxmltranslate'); //  run this when needing to store auth data to session

    if (is_array(($callbacklist))) {
      //foreach ($this->calling_object->callbackregister['lookupuser'] as $number => $callback) {
      foreach ($callbacklist as $number => $callback) {

        $xml = call_user_func_array($callback, array($xml));
        $objid = key($callbackregisterdatalist[$number]);
        $new_messages = $this->get_new_debug_messages($objid);
        foreach ($new_messages as $key => $value) {
          $info1 = $this->get_module_authinfo($objid);
          $info = key($info1) . ':' . current($info1);
          $this->savetodebug("User Lookup XML Translate:authObj($info)[$number:$key]: $value");
        }
      }
    }

    $this->savetodebug('XML is: ' . var_export($xml, true));

    $lookupobj = $this->xmlsearch($xml, $lookupobj, 'userlookup');

    if ($overrideallset == true) {
      unset($lookupobj->settings->overrideall);
    }
    if ($overrideset == true) {
      unset($lookupobj->settings->override);
    }


    return $lookupobj;
  }

  function xmlsearch($xml, $lookupobj, $section) {
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
      $attributes = $this->get_setting('xmlfields', 'userlookup');

      $this->savetodebug("Found $count records");
      if (isset($lookupobj->settings->firstentry) and $lookupobj->settings->firstentry == true) {
        //only
        $this->savetodebug('Saving First Entry Only');
        $datablock = $xmlsearched[0];
        $lookupobj = $this->store_in_data($datablock, $attributes, $lookupobj, 'userlookup');

      } elseif (isset($lookupobj->settings->lastentry) and $lookupobj->settings->lastentry == true) {
        //
        $this->savetodebug('Saving Last Entry Only');
        $datablock = $xmlsearched[$count - 1];
        $lookupobj = $this->store_in_data($datablock, $attributes, $lookupobj, 'userlookup');
      } else {


        foreach ($xmlsearched as $numb => $datablock) {
          $this->savetodebug("Saving Entry #$numb");
          $lookupobj = $this->store_in_data($datablock, $attributes, $lookupobj, 'userlookup');

        }
      }

      return $lookupobj;
    } else {
      //no records found!

      $this->savetodebug('No Records Match');

      return $lookupobj;
    }

  }

  function store_in_data($datablock, $attributes, $lookupobj, $section) {
    $prepend = '';
    if ((isset($this->settings['lowercasecompare']) and $this->settings['lowercasecompare'] == true) or (isset($this->settings[$section]['lowercasecompare']) and $this->settings[$section]['lowercasecompare'] == true)) {
      $this->savetodebug('Setting ldap_attributes to lowercase');
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
        $key = mb_strtolower($key); //think this actually needs to change the datablock without changing the original datablock
      }
      $reverse_attribute = $value;
      if (isset($datablock->$key) and (((isset($lookupobj->lookupdata->$reverse_attribute)) and ((isset($lookupobj->settings->overrideall) and $lookupobj->settings->overrideall == true) or ((isset($lookupobj->settings->override[$key]) and $lookupobj->settings->override[$key] == true) or (isset($lookupobj->settings->override[$reverse_attribute]) and $lookupobj->settings->override[$reverse_attribute] == true)))) or (!isset($lookupobj->lookupdata->$reverse_attribute)))) {
        // store data to lookup if ldap_attribute listed and ( not set or if set and ( overrideall or override value or override inverse ldap se+t))

        $lookupobj->lookupdata->$reverse_attribute = (string)$datablock->$key;
        $this->savetodebug("saving value for $reverse_attribute using ldap_attribute: $key");

      }
      if (isset($datablock[$key][0]) and !isset($lookupdatas->$reverse_attribute)) {
        $lookupdatas->$reverse_attribute = $datablock[$key][0];
      }
    }
    $lookupobj->lookupdatas[] = $lookupdatas;

    $datablockstore = array();
    foreach ($datablock as $key => $value) {

      if (!is_int($key)) {
        //


        if (isset($this->settings['lowercasecompare']) and $this->settings['lowercasecompare'] == true) {
          $key = mb_strtolower($key);
        }

        if ((isset($attributes[$key]))) {
          $gdgdfgdsgds = 1;
        }


        if (((isset($lookupobj->datablockstore[$prepend . $key])) and ((isset($lookupobj->settings->overrideall) and $lookupobj->settings->overrideall == true) or ((isset($lookupobj->settings->override[$key]) and $lookupobj->settings->override[$key] == true)))) or (!isset($lookupobj->datablockstore[$prepend . $key]))) {
          // store data to datablock store if not set or if set and ( overrideall or override value set)
          $lookupobj->datablockstore[$prepend . $key] = (string)$value;
        }

        $datablockstore[$prepend . $key] = (string)$value;
      }


    }
    $lookupobj->datablockstores[] = $datablockstore;

    return $lookupobj;
  }

  function get_setting($item, $section) {
    unset($data);
    if (isset($this->settings[$section][$item])) {
      $data = $this->settings[$section][$item];

      return $data;
    } elseif (isset($this->settings[$item])) {
      $data = $this->settings[$item];

      return $data;
    } else {
      return NULL;
    }


  }

}
