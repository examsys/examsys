<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 26/11/12
 * Time: 14:55
 * To change this template use File | Settings | File Templates.
 */
/**
 *
 * Creates and loads Config Object.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

$root                  = str_replace( '/include', '/', str_replace('\\', '/', dirname(__FILE__) ) );

require_once $root . 'classes/configobject.class.php';

$configObject          = Config::get_instance();

$cfg_web_root          = $configObject->get('cfg_web_root');
$cfg_editor_javascript = $configObject->get('cfg_editor_javascript');
$cfg_editor_name       = $configObject->get('cfg_editor_name');
