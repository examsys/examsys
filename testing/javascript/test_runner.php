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

use testing\javascript\TestLoader;

require_once dirname(dirname(__DIR__)) . '/include/sysadmin_auth.inc';
require_once dirname(dirname(__DIR__)) . '/include/autoload.inc.php';

autoloader::init();
if (file_exists(dirname(dirname(__DIR__)) . '/node_modules/qunitjs/qunit/qunit.js')) {
    $suite = param::optional('suite', '', param::ALPHANUM);
    
    // Find the test files.
    $loader = new TestLoader();
    $success = $loader->locate($suite);
    
    // Start generating the page
    $twigloader = new \Twig_Loader_Filesystem(__DIR__ . DIRECTORY_SEPARATOR . 'templates');
    $renderer = new \Twig_Environment($twigloader, array(
        'cache' => false
    ));
    $data = array(
      'scripts' => $loader,
      'webroot' => $configObject->get('cfg_root_path'),
    );
    
    if ($success) {
      $template = 'test_runner.html';
    } else {
      $template = 'suite_not_found.html';
    }
    
    // Output the page.
    echo $renderer->render($template, $data);
} else {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['accessdenied'], $msg, $string['accessdenied'], '/artwork/access_denied.png', '#C00000', true, true);
}
