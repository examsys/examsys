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

namespace testing\behat\steps\frontend;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

/**
 * Basic core step definitions.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait basic {
  /**
   * Click on an element on the page.
   * 
   * @Given /^I click "([^"]*)" "([^"]*)"$/
   * @param string $name The value to be searched for
   * @param string $selector The type of selector
   * @throws Exception
   */
  public function i_click($name, $selector) {
    $element = $this->find($selector, $name);
    if (is_null($element)) {
      throw new \Exception("The \"$selector\" with the value of \"$name\" could not be found");
    }
    $element->click();
  }

  /**
   * Checks for the presense of text.
   * 
   * @Then /^I should see "([^"]*)" "([^"]*)"$/
   * @param string $content
   * @param string $selector
   * @throws Exception
   */
  public function i_should_see($content, $selector) {
    $element = $this->find($selector, $content);
    if (is_null($element)) {
      throw new \Exception("The \"$selector\" with the value of \"$content\" could not be found");
    }
    if ($this->running_javascript() and !$element->isVisible()) {
      throw new \Exception("The \"$selector\" with the value of \"$content\" is hidden");
    }
  }
}

