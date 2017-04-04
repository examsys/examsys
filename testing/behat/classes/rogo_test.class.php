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

namespace testing\behat;
use Behat\MinkExtension\Context\MinkContext,
    Behat\Behat\Exception\PendingException;
use testing\datagenerator\loader;
use coding_exception,
    Exception;

/**
 * All Rogo behat test definitions should extend this class if they wish to do browser based tests.
 *
 * It should contain only utility functions we wish all Rogo
 * behat tests to have access to.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
class rogo_test extends MinkContext {
  /**
   * Get a data generator for adding information into the Rogo database.
   *
   * @param string $name The name of the generator.
   * @param string $component The component the generator is from (optional).
   * @return \testing\datagenerator\datagenerator
   * @throws \testing\datagenerator\not_found
   */
  protected function get_datagenerator($name, $component = 'core') {
    return loader::get($name, $component);
  }

  /**
   * {@inheritdoc}
   */
  public function locatePath($path) {
    // Get the base url for the site, ensure it has a trailing slash.
    $baseurl = rtrim($this->getMinkParameter('base_url'), '/') . '/';
    if (strpos($path, 'http') !== 0) {
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
  protected function get_page() {
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
  protected function validate_selector($selector, $locator) {
    if ($selector == 'named' or $selector == 'named_exact' or $selector == 'named_partial') {
      // The locator must be an array.
      if (!is_array($locator) and count($locator) !== 2) {
        throw new coding_exception('The locator for a named selector must be an aray with two values');
      }
      $name = $locator[0];
      if (!selectors::is_allowed_named($name)) {
        throw new Exception("The named selector $name is not enabled in rogo behat tests");
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
   * @see \testing\behat\selectors for Rogo specific selectors
   * @see \Behat\Mink\Element\ElementInterface::findAll for the supported selectors
   */
  public function find($name, $value) {
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
   * Checks if an element with the specified selector 
   *
   * @param string $name selector name
   * @param string $value the value to search for
   * @return boolean
   *
   * @see \testing\behat\selectors for Rogo specific selectors
   * @see \Behat\Mink\Element\ElementInterface::findAll for the supported selectors
   */
  public function has($name, $value) {
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
   * @see \testing\behat\selectors for Rogo specific selectors
   * @see \Behat\Mink\Element\ElementInterface::findAll for the supported selectors
   */
  public function find_all($name, $value) {
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
   * Returns whether the scenario is running in a browser that can run Javascript or not.
   *
   * @return boolean
   */
  protected function running_javascript() {
    return get_class($this->getSession()->getDriver()) !== 'Behat\Mink\Driver\GoutteDriver';
  }
}
