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

namespace testing\behat;

use Behat\Mink\Element\NodeElement;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Session;
use testing\datagenerator\loader;
use coding_exception;
use Exception;
use Config;

/**
 * All ExamSys behat test definitions should extend this class if they wish to do browser based tests.
 *
 * It should contain only utility functions we wish all ExamSys
 * behat tests to have access to.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
class rogo_test extends MinkContext
{
    /**
     * Get a data generator for adding information into the ExamSys database.
     *
     * @param string $name The name of the generator.
     * @param string $component The component the generator is from (optional).
     * @return \testing\datagenerator\datagenerator
     * @throws \testing\datagenerator\not_found
     */
    protected function get_datagenerator($name, $component = 'core')
    {
        return loader::get($name, $component);
    }

    /**
     * {@inheritdoc}
     */
    public function locatePath($path)
    {
        // Get the base url for the site, ensure it has a trailing slash.
        $baseurl = rtrim($this->getMinkParameter('base_url'), '/') . '/';
        if (mb_strpos($path, 'http') !== 0) {
            // The path is not a fully qualified url.
            $path = $baseurl . ltrim($path, '/');
        }
        return $path;
    }

    /**
     * Gets the page that the Mink session is viewing.
     *
     * @return \Behat\Mink\Element\DocumentElement
     */
    protected function get_page()
    {
        // Get the Mink session.
        $session = $this->getSession();
        // Get the current page.
        return $session->getPage();
    }

    /**
     * Tests if the correct prarmeters have been passed.
     *
     * @param string $selector selector engine name
     * @param string|array $locator selector locator
     * @return void
     * @throws coding_exception if the details are invalid
     * @throws exception If the selector type is not allowed
     */
    protected function validate_selector($selector, $locator)
    {
        if ($selector == 'named' or $selector == 'named_exact' or $selector == 'named_partial') {
            // The locator must be an array.
            if (!is_array($locator) and count($locator) !== 2) {
                throw new coding_exception('The locator for a named selector must be an aray with two values');
            }
            $name = $locator[0];
            if (!selectors::is_allowed_named($name)) {
                throw new Exception("The named selector $name is not enabled in ExamSys behat tests");
            }
        }
    }

    /**
     * Finds first element with specified selector inside the current element.
     *
     * @param string $name selector name
     * @param string $value the value to search for
     * @return \Behat\Mink\Element\NodeElement|null
     * @throws coding_exception
     * @throws exception If the element cannot be found
     *
     * @see \testing\behat\selectors for ExamSys specific selectors
     * @see \Behat\Mink\Element\ElementInterface::findAll for the supported selectors
     */
    public function find($name, $value)
    {
        if (selectors::is_allowed_named($name)) {
            $selector = 'named';
            $locator = array($name, $value);
        } else {
            $selector = $name;
            $locator = $value;
        }
        $page = $this->get_page();
        return $page->find($selector, $locator);
    }

    /**
     * Gets the attribute from the element
     * @param NodeElement $element the element
     * @param string $attribute the attribute
     * @return string
     */
    public function getAttribute(NodeElement $element, string $attribute): string
    {
        return $element->getAttribute($attribute);
    }
    /**
     * Checks if an element with the specified selector
     *
     * @param string $name selector name
     * @param string $value the value to search for
     * @return boolean
     *
     * @see \testing\behat\selectors for ExamSys specific selectors
     * @see \Behat\Mink\Element\ElementInterface::findAll for the supported selectors
     */
    public function has($name, $value)
    {
        if (selectors::is_allowed_named($name)) {
            $selector = 'named';
            $locator = array($name, $value);
        } else {
            $selector = $name;
            $locator = $value;
        }
        $page = $this->get_page();
        return $page->has($selector, $locator);
    }

    /**
     * Find all elements that match the criteria.
     *
     * @param string $name selector name
     * @param string $value the value to search for
     * @return \Behat\Mink\Element\NodeElement[]
     *
     * @see \testing\behat\selectors for ExamSys specific selectors
     * @see \Behat\Mink\Element\ElementInterface::findAll for the supported selectors
     */
    public function find_all($name, $value)
    {
        if (selectors::is_allowed_named($name)) {
            $selector = 'named';
            $locator = array($name, $value);
        } else {
            $selector = $name;
            $locator = $value;
        }
        $page = $this->get_page();
        return $page->findAll($selector, $locator);
    }

    /**
     * Detects errors, notices and warnings on a page.
     *
     * @throws \Exception
     * @throws \Behat\Mink\Exception\DriverException
     */
    public function lookForErrors(): void
    {
        // Regular expressions for detecting errors, notices and the like.
        $error = "//*[contains(., 'Error: ') and contains(., ' on line ')]";
        $warning = "//*[contains(., 'Warning: ') and contains(., ' on line ')]";
        $notice = "//*[contains(., 'Notice: ') and contains(., ' on line ')]";
        try {
            if ($this->getSession()->getDriver()->find($error)) {
                throw new Exception('Error found on page.');
            }
            if ($this->getSession()->getDriver()->find($warning)) {
                throw new Exception('Warning found on page.');
            }
            if ($this->getSession()->getDriver()->find($notice)) {
                throw new Exception('Notice found on page.');
            }
        } catch (\Behat\Mink\Exception\UnsupportedDriverActionException $e) {
            // Nothing we can do about this.
        } catch (\WebDriver\Exception\NoSuchWindow $e) {
            // The action caused the window to close so we cannot see any errors.
        }
    }

    /**
     * Waits for an action to be true.
     *
     * @param function $lambda
     * @return boolean
     * @throws Exception
     */
    public function spin($lambda)
    {
        $timeout = self::getTimeout();
        $start = microtime(true);
        $end = $start + $timeout;

        do {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (Exception $e) {
                // do nothing
            }

            if (!$this->running_javascript()) {
                break;
            }

            usleep(100000);

        } while (microtime(true) < $end);

        $backtrace = debug_backtrace();
        $message = 'Timeout thrown by ' . $backtrace[1]['class'] . '::' . $backtrace[1]['function'] . '()';
        if (isset($backtrace[1]['file'])) {
            $message .= ' in ' . $backtrace[1]['file'] . ', line ' . $backtrace[1]['line'];
        }
        throw new Exception($message);
    }

    /**
     * Returns whether the scenario is running in a browser that can run Javascript or not.
     *
     * @return boolean
     */
    public function running_javascript()
    {
        return get_class($this->getSession()->getDriver()) !== 'Behat\Mink\Driver\GoutteDriver';
    }

    /**
     * Sets files_path for the test
     * @param string $path location of test upload assets
     */
    public function setFilesPath(string $path): void
    {
        $this->setMinkParameter(
            'files_path',
            environment::get_basedir() . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $path
        );
    }

    /**
     * Waits for all the JS to be loaded.
     * Wait for JS copied from https://github.com/moodle/moodle/blob/master/lib/behat/classes/behat_session_trait.php
     *
     * @return  bool Whether any JS is still pending completion.
     */
    public function waitForPendingJs()
    {
        return static::waitForPendingJsInSession($this->getSession());
    }

    /**
     * Waits for all the JS to be loaded.
     * @param   Session $session The Mink Session where JS can be run
     * @return  bool Whether any JS is still pending completion.
     */
    public static function waitForPendingJsInSession(Session $session)
    {
        if (!self::runningJavascriptInSession($session)) {
            // JS is not available therefore there is nothing to wait for.
            return false;
        }

        // We don't use rogo_test::spin() here as we don't want to end up with an exception
        // if the page & JSs don't finish loading properly.
        for ($i = 0; $i < self::getExtendedTimeout() * 10; $i++) {
            try {
                $jscode = trim(preg_replace('/\s+/', ' ', '
                    return (function() {
                        if (document.readyState !== "complete") {
                            return "incomplete";
                        }
                        return "";
                    })()'));
                $pending = self::evaluateScriptInSession($session, $jscode);
            } catch (Exception $e) {
                // We catch an exception here, in case we just closed the window we were interacting with.
                // No javascript is running if there is no window right?
                $pending = '';
            }

            // If there are no pending JS we stop waiting.
            if ($pending === '') {
                return true;
            }

            // 0.1 seconds.
            usleep(100000);
        }

        // Timeout waiting for JS to complete.
        // It is unlikely that Javascript code of a page or an AJAX request needs more than
        // getExtendedTimeout() seconds to be loaded.
        throw new \Exception('Javascript code and/or AJAX requests are not ready after ' .
                             self::getExtendedTimeout() .
                             ' seconds. There is a Javascript error or the code is extremely slow (' . $pending .
                             '). If you are using a slow machine, consider setting increasetimeout in behat config.');
    }

    /**
     * Whether Javascript is available in the specified Session.
     *
     * @param Session $session
     * @return boolean
     */
    protected static function runningJavascriptInSession(Session $session): bool
    {
        return get_class($session->getDriver()) !== 'Behat\Mink\Driver\GoutteDriver';
    }

    /**
     * Gets the extended timeout.
     *
     * A longer timeout for cases where the normal timeout is not enough.
     *
     * @return int Timeout in seconds
     */
    public static function getExtendedTimeout() : int
    {
        return self::getRealTimeout(30);
    }

    /**
     * Gets the default timeout.
     *
     * The timeout for each Behat step (load page, wait for an element to load...).
     *
     * @return int Timeout in seconds
     */
    public static function getTimeout() : int {
        return self::getRealTimeout(15);
    }

    /**
     * Gets the required timeout in seconds.
     *
     * @param int $timeout One of the TIMEOUT constants
     * @return int Actual timeout (in seconds)
     */
    protected static function getRealTimeout(int $timeout) : int
    {
        $cfg_behat_increasetimeout = Config::get_instance()->get('cfg_behat_increasetimeout');
        if (isset($cfg_behat_increasetimeout)) {
            return $timeout * $cfg_behat_increasetimeout;
        } else {
            return $timeout;
        }
    }

    /**
     * Evaluate the supplied script in the specified session, returning the result.
     *
     * @param Session $session
     * @param string $script
     * @return mixed
     */
    public static function evaluateScriptInSession(Session $session, string $script)
    {
        self::requireJavascriptInSession($session);

        return $session->evaluateScript($script);
    }

    /**
     * Require that javascript be available for the specified Session.
     *
     * @param Session $session
     * @param null|string $message An additional information message to show when JS is not available
     * @throws DriverException
     */
    protected static function requireJavascriptInSession(Session $session, ?string $message = null): void
    {
        if (self::runningJavascriptInSession($session)) {
            return;
        }

        $error = "Javascript is required for this step.";
        if ($message) {
            $error = "{$error} {$message}";
        }
        throw new DriverException($error);
    }
}
