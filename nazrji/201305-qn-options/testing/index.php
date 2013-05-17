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

require '../include/sysadmin_auth.inc';
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
	<ol>
		<li><a href="./unittest.php">Unit tests</a></li>
		<li><a href="./selenium/README.txt">Selenium tests</a></li>
		<li><a href="./lang_test.php">Check for missing strings in language files</a></li>
		<li><a href="./class_totals.php">Check classtotals between 2 different servers</a></li>
		<li><a href="./class_totals_with_script.php">Check classtotals agents finish.php (internal constancy)</a></li>
		<li><a href="./database_grants.php">Database grants</a></li>
		<li><a href="./database_indexes.php">Database indexes</a></li>
		<li><a href="./database_structure.php">Database structure</a></li>
	</ol>
</body>
</html>
