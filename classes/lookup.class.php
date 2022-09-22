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
 * User lookup as well as user data lookup.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class Lookup extends RogoStaticSingleton
{
    public static $inst = null;
    public static $class_name = 'Lookup';

    private $db;
    private $configObj;
    private $config;
    public $returndata;
    public $debug;
    public $lookupPluginObj;

    public $lookupinfo;

    private $callbackregister;
    private $callbackregisterdata;

    public $impliments_api_lookup_version = 1;

    public $callbacktypes = array( 'init', 'preuserlookup', 'userlookup', 'userlookup', 'postuserlookup', 'usertranslatelookup', 'premodulelookup', 'modulelookup', 'modulelookup', 'postmodulelookup', 'moduletranslatelookup' );


    public $initobj;
    public $lookupuserobj;
    public $preauthobj;
    public $userlookupobj;
    public $postauthobj;
    public $postauthsuccesobj;
    public $postauthfailobj;
    public $displaystdformobj;
    public $displayerrformobj;
    public $getauthobj;
    public $sessionstoreobj;

    /**
     * Called when the object is unserialised.
     */
    public function __wakeup()
    {
        // The serialised database object will be invalid,
        // this object should only be serialised during an error report,
        // so adding the current database connect seems like a waste of time.
        $this->db = null;
    }

    public static function get_instance($config = null, $db = null)
    {
        //some objects are global and need parameters these are constructed using
        //a stranded constructor and need parameters passing. if they have not been
        //built and get_instance is call it should return null
        if (isset(static::$dont_construct) and static::$dont_construct == true) {
            if (is_object(static::$inst)) {
                return static::$inst;
            } else {
                return null;
            }
        }

        //normal behaviour create on demand
        if (!is_object(static::$inst) and $config != null and $db != null) {
            static::$inst = new static::$class_name($config, $db);
        }
        if ($config == null or $db == null) {
            return null;
        }

        return static::$inst;
    }

    // constructor
    public function __construct(&$configObj, &$db)
    {
        $this->db = & $db;
        $this->configObj = & $configObj;

        $this->load_config();


        foreach ($this->config as $number => $lookup) {
            $lookuptype = $lookup[0];
            $lookuptype1 = $lookuptype . '_lookup';
            $settings = $lookup[1];
            $name = $lookup[2];
            $this->debug[] = "Loading lookup #$number with Type:$lookuptype Settings:" . str_replace("\n", "\n", var_export($settings, true));

            $this->lookupinfo[$number] = array($name => $lookuptype);

            $object = new stdClass();
            $object->db =& $this->db;
            $object->calling_object =& $this;
            $object->settings = $settings;

            if (!isset($settings['mockclass'])) {
                require_once $this->configObj->get('cfg_web_root') . 'plugins/lookup/' . $lookuptype . '.class.php';
                $this->lookupPluginObj[$number] = new $lookuptype1($number, $name, $this->impliments_api_lookup_version);
            } else {
                $this->lookupPluginObj[$number] = & $settings['mockclass'];
            }
            $error = $this->lookupPluginObj[$number]->apicheck();

            if ($error !== false) {
                $this->debug[] = '********* Disabled module #' . $number . ':' . $name . ' as it implements an old a version of the api.  The returned error #: ' . $error . ' *********';
            } else {
                $this->lookupPluginObj[$number]->init($object);

                $this->debug[] = "Running Registering callback routines for #$number";

                $callbacksections = $this->lookupPluginObj[$number]->register_callback_sections();
                $this->register_callback_section($callbacksections);

                $callbacks = $this->lookupPluginObj[$number]->register_callback_routines();
                foreach ($callbacks as $callbackitem) {
                    if (!isset($callbackitem[4])) {
                        $callbackitem[4] = false;
                    }
                    $this->register_callback($callbackitem[0], $callbackitem[1], $callbackitem[2], $callbackitem[3], $callbackitem[4]);
                }
                $this->append_lookup_object_debug($number);
            }
        }

        $initobj = new stdClass();

        if (isset($this->callbackregister['init'])) {
            foreach ($this->callbackregister['init'] as $number => $callback) {
                $initobj = call_user_func_array($callback, array($initobj));
                $objid = key($this->callbackregisterdata['init'][$number]);
                $this->append_lookup_object_debug($objid);
            }
        }
    }

    /**
     * error handling function
     */
    public function error_handling($context = null)
    {
        $context1 = array();
        $context1 = error_handling($this);
        if (isset($context1['config'])) {
            $context1['config'] = 'Settings hidden';
        }
        return $context1;
    }

    /**
     * CLoads the config
     */
    public function load_config()
    {
        $notice = UserNotices::get_instance();

        $this->config = $this->configObj->getbyref('lookup');

        if (!isset($this->config)) {
            $notice->display_notice($string['NoLookupConfigured'], $string['NoLookupConfiguredmessage'], '../artwork/software_64.png', $title_color = '#C00000');
            exit();
        }

        $this->debug[] = 'Loaded Config for lookup';
    }

    /**looks up module info Checks if a faculty ID already exists.
     * @param object  $data - an object containing the elements searchorder for searching order, lookupdata the data to lookup and where the data is stored and settings that stores certain settings for this search
     * @return object             - the data object
     */
    public function modulelookup($data)
    {
        if (!isset($data->searchorder)) {
            if (isset($this->settings->searchorder)) {
                $data->searchorder = $this->settings->searchorder;
            } else {
                $this->debug[] = 'Setting default search order as none supplied';
                $data->searchorder = array('modulecode');
            }
        }

        if (!isset($data->lookupdata)) {
            return new stdClass();
        }

        $premodulelookupobj = new stdClass();
        $premodulelookupobj->lookupdata = $data->lookupdata;
        $premodulelookupobj->searchorder = $data->searchorder;
        if (isset($this->callbackregister['premodulelookup'])) {
            foreach ($this->callbackregister['premodulelookup'] as $number => $callback) {
                $premodulelookupobj = call_user_func_array($callback, array($premodulelookupobj));
                $objid = key($this->callbackregisterdata['premodulelookup'][$number]);
                $this->append_lookup_object_debug($objid);
            }
        }


        $modulelookupobj = new stdClass();
        $modulelookupobj->lookupdata = $premodulelookupobj->lookupdata;
        $modulelookupobj->searchorder = $premodulelookupobj->searchorder;

        if (isset($this->callbackregister['modulelookup'])) {
            foreach ($this->callbackregister['modulelookup'] as $number => $callback) {
                $modulelookupobj = call_user_func_array($callback, array($modulelookupobj));
                $objid = key($this->callbackregisterdata['modulelookup'][$number]);
                $this->append_lookup_object_debug($objid);

                if (isset($this->callbackregister['moduletranslatelookup'])) {
                    foreach ($this->callbackregister['moduletranslatelookup'] as $number => $callback) {
                        $modulelookupobj = call_user_func_array($callback, array($modulelookupobj));
                        $objid = key($this->callbackregisterdata['moduletranslatelookup'][$number]);
                        $this->append_lookup_object_debug($objid);
                    }
                }
            }
        }

        if (isset($data->settings->recursive) and $data->settings->recursive == true) {
            $modulelookupobj->lookupdatasrec = array();
            foreach ($modulelookupobj->lookupdatas as $key => $lkdsvalue) {
                $block = new stdClass();
                $block->lookupdata =& $modulelookupobj->lookupdatas[$key];
                if (isset($data->settings->recursive_searchorder)) {
                    $block->searchorder = $data->settings->recursive_searchorder;
                }
                if (isset($data->settings->recursive_overrideall)) {
                    $block->overrideall = $data->settings->recursive_overrideall;
                }
                if (isset($data->settings->recursive_override)) {
                    $block->override = $data->settings->recursive_override;
                }
                $modulelookupobj->lookupdatasrec[$key] = clone $block;
                $this->debug[] = 'Now running recursively on data search results';

                $modulelookupobj->lookupdatasrec[$key] = $this->modulelookup($modulelookupobj->lookupdatasrec[$key]);

                if (isset($data->settings->recursive_max) and $key > $data->settings->recursive_max) {
                    $this->debug[] = 'max recursive number for search exceeded ' . $key > $data->settings->recursive_max . ' Total # records: ' . count($modulelookupobj->lookupdatas);
                    break;
                }
            }
        }

        $postmodulelookupobj = new stdClass();
        $postmodulelookupobj->lookupobj = $modulelookupobj;
        if (isset($this->callbackregister['postmodulelookup'])) {
            foreach ($this->callbackregister['postmodulelookup'] as $number => $callback) {
                $postmodulelookupobj = call_user_func_array($callback, array($postmodulelookupobj));
                $objid = key($this->callbackregisterdata['postmodulelookup'][$number]);
                $this->append_lookup_object_debug($objid);
            }
        }
        $modulelookupobj = $postmodulelookupobj->lookupobj;

        if (!isset($modulelookupobj->lookupdatas)) {
            $modulelookupobj->failed = true;
            $modulelookupobj->success = false;
        } else {
            $modulelookupobj->failed = false;
            $modulelookupobj->success = true;
        }


        if (isset($modulelookupobj->multiple) and $modulelookupobj->multiple == true) {
            $modulelookupobj->lookupdata->unreliable = true;
        }

        return $modulelookupobj;
    }

    /**looks up user info Checks if a faculty ID already exists.
     * @param object  $data - an object containing the elements searchorder for searching order, lookupdata the data to lookup and where the data is stored and settings that stores certain settings for this search
     * @return object             - the data object
     */
    public function userlookup($data)
    {
        if (!isset($data->searchorder)) {
            if (isset($this->settings->searchorder)) {
                $data->searchorder = $this->settings->searchorder;
            } else {
                $this->debug[] = 'Setting default search order as none supplied';
                $data->searchorder = array('username', 'studentID', 'staffID', 'email', array('surname', 'firstname'));
            }
        }

        if (!isset($data->lookupdata)) {
            return new stdClass();
        }

        $preuserlookupobj = new stdClass();
        $preuserlookupobj->lookupdata = $data->lookupdata;
        $preuserlookupobj->searchorder = $data->searchorder;
        if (isset($this->callbackregister['preuserlookup'])) {
            foreach ($this->callbackregister['preuserlookup'] as $number => $callback) {
                $preuserlookupobj = call_user_func_array($callback, array($preuserlookupobj));
                $objid = key($this->callbackregisterdata['preuserlookup'][$number]);
                $this->append_lookup_object_debug($objid);
            }
        }

        $userlookupobj = new stdClass();
        $userlookupobj->lookupdata = $preuserlookupobj->lookupdata;
        $userlookupobj->searchorder = $preuserlookupobj->searchorder;

        if (isset($this->callbackregister['userlookup'])) {
            foreach ($this->callbackregister['userlookup'] as $number => $callback) {
                $userlookupobj = call_user_func_array($callback, array($userlookupobj));
                $objid = key($this->callbackregisterdata['userlookup'][$number]);
                $this->append_lookup_object_debug($objid);

                if (isset($this->callbackregister['usertranslatelookup'])) {
                    foreach ($this->callbackregister['usertranslatelookup'] as $number => $callback) {
                        $userlookupobj = call_user_func_array($callback, array($userlookupobj));
                        $objid = key($this->callbackregisterdata['usertranslatelookup'][$number]);
                        $this->append_lookup_object_debug($objid);
                    }
                }
            }
        }

        if (isset($data->settings->recursive) and $data->settings->recursive == true) {
            $userlookupobj->lookupdatasrec = array();
            foreach ($userlookupobj->lookupdatas as $key => $lkdsvalue) {
                $block = new stdClass();
                $block->lookupdata =& $userlookupobj->lookupdatas[$key];
                if (isset($data->settings->recursive_searchorder)) {
                    $block->searchorder = $data->settings->recursive_searchorder;
                }
                if (isset($data->settings->recursive_overrideall)) {
                    $block->overrideall = $data->settings->recursive_overrideall;
                }
                if (isset($data->settings->recursive_override)) {
                    $block->override = $data->settings->recursive_override;
                }
                $userlookupobj->lookupdatasrec[$key] = clone $block;
                $this->debug[] = 'Now running recursively on data search results';

                $userlookupobj->lookupdatasrec[$key] = $this->userlookup($userlookupobj->lookupdatasrec[$key]);

                if (isset($data->settings->recursive_max) and $key > $data->settings->recursive_max) {
                    $this->debug[] = 'max recursive number for search exceeded ' . $key > $data->settings->recursive_max . ' Total # records: ' . count($userlookupobj->lookupdatas);
                    break;
                }
            }
        }

        $postuserlookupobj = new stdClass();
        $postuserlookupobj->lookupobj = $userlookupobj;
        if (isset($this->callbackregister['postuserlookup'])) {
            foreach ($this->callbackregister['postuserlookup'] as $number => $callback) {
                $postuserlookupobj = call_user_func_array($callback, array($postuserlookupobj));
                $objid = key($this->callbackregisterdata['postuserlookup'][$number]);
                $this->append_lookup_object_debug($objid);
            }
        }
        $userlookupobj = $postuserlookupobj->lookupobj;

        if (!isset($userlookupobj->lookupdatas)) {
            $userlookupobj->failed = true;
            $userlookupobj->success = false;
        } else {
            $userlookupobj->failed = false;
            $userlookupobj->success = true;
        }


        if (isset($userlookupobj->multiple) and $userlookupobj->multiple == true) {
            $userlookupobj->lookupdata->unreliable = true;
        }

        return $userlookupobj;
    }

    public function register_callback_section($section)
    {
        foreach ($section as $addition) {
            if (!in_array($addition, $this->callbacktypes)) {
                $this->callbacktypes[] = $addition;
            }
        }
    }

    /**registers callbacks from plugins.
     * @param object  $callback the callback object/array
     * @param string $section  the section it is associated with
     * @param int $number the id number for the plugin
     * @param string $name the  name of the plugin
     * @param bool $insert optional if set insert at beginning of list
     *    * @return bool             - false if failed
     */
    public function register_callback($callback, $section, $number, $name, $insert = false)
    {
        if (!in_array($section, $this->callbacktypes) or !is_callable($callback)) {
            //attempting to register callback to invalid section
            //maybe log name of function as well?
            $this->debug[] = 'register_callback FAILED ' . $section . ' from ' . get_class($callback[0]) . ' id:' . $number . ' with name:' . $name; // . var_export($callback,true);
            $this->lookupPluginObj[$number]->set_error("Failed to register callback for section ($section) with function ($callback[1])");

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

    public function get_callback($section)
    {
        return array(&$this->callbackregister[$section], &$this->callbackregisterdata[$section]);
    }

    public function append_lookup_object_debug($number, $desc = '')
    {
        $new_messages = $this->lookupPluginObj[$number]->get_new_debug_messages();
        foreach ($new_messages as $key => $value) {
            $info1 = $this->lookupinfo[$number];
            $info = key($info1) . ':' . current($info1);
            $this->debug[] = "lookupObj($info)[$number:$key]:$desc: $value";
        }
    }

    public function display_debug()
    {
        var_dump($this->debug);
    }

    public function debug_to_string()
    {
        return implode('<br />', $this->debug);
    }

    public function debug_as_array()
    {
        return $this->debug;
    }

    public function clear_debug()
    {
        $this->debug = array();
    }

    public function version_info($formatted = false, $advanced = false)
    {
        $data = new stdClass();
        $data->plugins = array();
        foreach ($this->lookupPluginObj as $lookupobj) {
            $data->plugins[] = $lookupobj->get_info();
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
                if (count($item->error) > 0) {
                    $error = true;
                }
                if ($number != 0) {
                    $return_data .= ',  <b>' . $number . '</b> ' . $item->name . ' <i>(' . $item->classname . ')</i>';
                }
            }
            $return_data = mb_substr($return_data, 3);
            if ($error) {
                $return_data = '<div style="background-color: #cc0000;">' . $return_data . '</div>';
            }
        } else {
            //advanced view
        }

        return $return_data;
    }
}
