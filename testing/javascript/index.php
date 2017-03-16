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
 * This page finds and runs JavaScript Uniti tests using QUnit.
 * https://qunitjs.com/
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @subpackage javascript
 */

use testing\javascript\SuiteLoader;

require_once dirname(dirname(__DIR__)) . '/include/sysadmin_auth.inc';
require_once dirname(dirname(__DIR__)) . '/include/autoload.inc.php';
autoloader::init();

if (file_exists(dirname(dirname(__DIR__)) . '/node_modules/qunitjs/qunit/qunit.js')) {
    // Find the test files.
    $loader = new SuiteLoader();
    $loader->locate_all();
    
    // Start generating the page
    $twigloader = new \Twig_Loader_Filesystem(__DIR__ . DIRECTORY_SEPARATOR . 'templates');
    $renderer = new \Twig_Environment($twigloader, array(
        'cache' => false
    ));
    $data = array(
      'scripts' => $loader,
      'webroot' => $configObject->get('cfg_root_path'),
    );
    
    // Output the page.
    echo $renderer->render('index.html', $data);
} else {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['accessdenied'], $msg, $string['accessdenied'], '/artwork/access_denied.png', '#C00000', true, true);
}
