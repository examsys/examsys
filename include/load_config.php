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
 * Creates and loads Config Object.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

$root                  = str_replace( '/include', '/', str_replace('\\', '/', dirname(__FILE__) ) );

require_once 'defines.inc.php';
require_once __DIR__ . '/autoload.inc.php';
autoloader::init();

$configObject          = Config::get_instance();

$cfg_web_root          = $configObject->get('cfg_web_root');
$cfg_editor_javascript = $configObject->get('cfg_editor_javascript');
$cfg_editor_name       = $configObject->get('cfg_editor_name');

