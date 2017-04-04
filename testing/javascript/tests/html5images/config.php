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

// This is the name of the test suite to be loaded.
// It must match the name of the directory the file is in.
$setup->test = 'html5images';
// Define all the Javascript that should be loaded for the test.
// Should be the complete path relative to the root of Rogo.
// The files will be loaded in the order they are in the array.
$setup->required_js = array(
  'js/jquery-1.11.1.min.js',
  'js/html5.images.js',
);
// Should store any text wanted in the #qunit-fixture div for the suite.
$setup->fixture_html = <<<FIXTURE
FIXTURE;
