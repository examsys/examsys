JavaScript Unit Tests
=====================

Rogō uses QUnit (https://qunitjs.com/) for testing JavaScript

Each sub-directory is a separate suite of tests, they are run in isolation from each other.

All the js files inside the directory must be QUnit test files.

Suite configuration
===================

Each suite requires a config.php file that must define the following:

* $setup->test
* $setup->required_js
* $setup->fixture_html

$setup->test
------------

This must contain the name of the directory, this is to ensure that the suite has been
configured correctly and is in the correct location. The name must consist of letters
and numbers only.

$setup->required_js
-------------------

Contains an array of all the JavaScript files that are needed by the tests in the suite.
They should be described by the full path to them from the root of Rogō, without a leading slash.
The scripts will be loaded in the order they are in the array.

$setup->fixture_html
--------------------

Any html needed on the page should be added to this as a string. It will then be put into the
fixture are of the page. QUnit should then reset that area to this text after every test.
