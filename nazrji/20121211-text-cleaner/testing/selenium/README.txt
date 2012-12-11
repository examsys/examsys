Rogō Selenium Test Suite
------------------------

These tests depend on a working installation of PHPUnit (https://github.com/sebastianbergmann/phpunit/) with Selenium Server integration extensions (https://github.com/sebastianbergmann/phpunit-selenium) plus a running Selenium server (http://seleniumhq.org/download/).

For details of using Selenium with PHPUnit see http://www.phpunit.de/manual/current/en/selenium.html.

If you are running  over SSL you will need to include the -trustAllSSLCertificates flag when starting Selenium Server.

These tests should currently be run from the command line FROM THIS DIRECTORY. This is to allow the inclusion of shared functions for carrying out common actions such as logging in to the site.

