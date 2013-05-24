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
* Rogō Test Harness.
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

if(!isset($_GET['test'])) {
	//display interface 
	?>
	<html>
	<head>
		<title>Rogō Test Harness</title>
		<style>
        	aside, figure, footer, header, hgroup, nav, section { display: block; clear: both; }
            article { width:50%; height: 80%; float: left }
        </style>
        <link rel="stylesheet" type="text/css" href="../css/body.css" />
  		<link rel="stylesheet" type="text/css" href="../css/header.css" />
 		<link rel="stylesheet" type="text/css" href="../css/screen.css" />
	</head>
	<body>
		<h1>Rogō Test Harness</h1>
		<p>Select the test you wish to run.</p>
		<ul>
			<?php
			echo '<li><a href="./unittest.php?test=all"><strong>All</strong></a></li>';
			$test = scandir('./unit_tests');
			foreach($test as $t) {
				if(!is_dir("./unit_tests/$t") OR $t == '' OR $t == '.' OR $t == '..') {
					continue;
				}
				echo "<li><a href=\"./unittest.php?test=$t\">$t</a></li>\n";
			} 
			?>
		</ul>
	</body>
	</html>
	<?php
} else {

	//setup the Rogo config object
	$root = str_replace( '/testing', '/', str_replace('\\', '/', dirname(__FILE__) ) );
	require_once $root . 'classes/configobject.class.php';
	$configObject = Config::get_instance();
	$cfg_web_root = $configObject->get('cfg_web_root');
	
	//load the mysqli mocking classes	
	require_once './include/mockmysqli.class.php';

	// Include the test framework
	require_once './include/EnhanceTestFramework.php';
	require_once './include/codespy.php';
	\codespy\Analyzer::$outputdir = $cfg_web_root . 'testing/coverage';
	\codespy\Analyzer::$outputformat = 'html';
	\codespy\Analyzer::$coveredcolor = '#c2ffc2';

	$run = false;

	switch($_GET['test']) {
		case 'all':
			\Enhance\Core::discoverTests('./unit_tests/');
			$run = TRUE;
			break;
		case 'one':
			$path = './unit_tests/' . $_GET['one'];
				if(is_file($path) AND stristr($path,'unit_tests') !== FALSE) {
					\Enhance\Core::discoverTests($path);
					$run = TRUE;
				}
			break;
		default:
			$path = realpath('./unit_tests/' . $_GET['test']);
			echo "<h2>Looking for test in $path</h2>";
			if($path !== FALSE) {
				if(is_dir($path) AND stristr($path,'unit_tests') !== FALSE) {
					\Enhance\Core::discoverTests($path);
					$run = TRUE;
				}
			}
			break;
	}

	if ($run == TRUE) {
		// Run the tests
		\Enhance\Core::runTests();
	}

}

?>