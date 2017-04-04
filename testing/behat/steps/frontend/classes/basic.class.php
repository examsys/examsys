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
    Behat\Gherkin\Node\TableNode,
    PHPUnit_Framework_Assert;

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
    
  /**
   * Keep browser live, for debuging
   * 
   * @Given /^I pause "(?P<seconds_number>\d+)"$/
   * @param int $seconds 
   */
  public function i_wait_seconds($seconds) {
    $this->getSession()->wait($seconds * 1000, false);
  }
  
  /**
   * Check help page 
   * 
   * @Then I should see popup page with title :title
   * @param String $title The page title
   * @throws Exception
   */
  public function i_see_popup_page($title) {

    $session = $this->getSession();
    $windows = $session->getDriver()->getWindowNames();

    if (empty($windows)) {
      throw new Exception("The page could not be found");
    }
    $this->getSession()->switchToWindow($windows[1]); // Set focus window
    $thistitle= $session->getDriver()->getWebDriverSession()->title(); // Get window title
    PHPUnit_Framework_Assert::assertEquals($thistitle, $title, "Windows title not find");
  }
  
  /**
   * Close popup window back to main window
   * 
   * @Then /^I close popup window$/
   * @throws Exception
   */
  public function i_close_popup_window() {
    
    $session = $this->getSession();
    $windows = $session->getDriver()->getWindowNames();

    if (empty($windows)) {
      throw new Exception("The page could not be found");
    }
    $this->getSession()->executeScript('window.close()');
    $this->getSession()->switchToWindow($windows[0]); 
  }
  
  
  /**
   * Check the page 
   * 
   * @Then /^I should see page with title "([^"]*)"$/
   * @param String $title The page title
   * @throws Exception
   */
  public function i_see_page_title($title) {
    $pagetitle = $this->find("xpath", "//div[@class='page_title']")->getText();
    if (strpos($pagetitle, $title) === false) {
      throw new Exception("The page could not be found");
    }
  }

  /**
   * Check table content
   * 
   * @Then /^I should see table with:$/
   *
   * Asserts that a table exists with specified values.
   * The table header needs to have the number of the column to which the values belong,
   * all the other text is optional, normaly using 'Column' for easier understanding:
   *
   *      | Column 1 | Column 2 | Column 4 |
   *      | Value A  | Value B  | Value D  |
   *      ...
   *      | Value I  | Value J  | Value L  |
   */
  public function i_see_table_with(TableNode $table) {
    $rows = $table->getRows();
    $headers = array_shift($rows);
    $max = count($headers); //number of columns in table 
    foreach ($rows as $row) {
      for ($i = 1; $i <= $max; $i++) {
        $text = array_shift($row);
        $foundRows = $this->get_table_row($text, $i, "table[@id='maindata']");
        if (!$foundRows) {
          throw new Exception("the table row could not been found");
        }
      }
    }
  }

  /**
   * Find a(all) table row(s) that match the column text
   *
   * @param string        $text       Text to be found
   * @param int           $columnnumber     In which column the text should be found
   * @param string        $tableXpath If there is a specific table
   *
   * @return \Behat\Mink\Element\NodeElement[]
   */
  public function get_table_row($text, $columnnumber, $tableXpath) {
    // check column
    if (!empty($columnnumber)) {
      if (is_integer($columnnumber)) {
        $column = "[$columnnumber]";
      } else {
        return false;
      }
    } else {
      return false;
    }

    $dd = $this->find("xpath", "//$tableXpath/thead/tr/th$column");
    $ww = $this->find("xpath", "//$tableXpath/tbody/tr/td" . $column . "[text()='$text']");
    if (!empty($dd) && !empty($ww)) {
      return true;
    }
    return false;
  }

  /**
   * Click on an admin tool.
   * 
   * @When /^I click admin tool "([^"]*)"$/
   * @param string $name The value to be searched for
   * @throws Exception
   */
  public function i_click_admin_tool($name) {
    $elements = $this->find_all("xpath", "//div[@class='container' and contains(text(), '$name')]");
    $elements[0]->click();
  }

  /**
   * Check javascript popup message
   * 
   * @Then /^(?:|I )should see "([^"]*)" in popup$/
   *
   * @param string $message The message.
   *
   * @return bool
   */
  public function assert_popup_message($message) {
    return $message == $this->getSession()->getDriver()->getWebDriverSession()->getAlert_text();
  }

  /**
   * Confirm a javascript popup window, click OK/Yes button
   * 
   * @Then /^(?:|I )confirm the popup$/
   */
  public function confirm_popup() {
    $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
  }

  /**
   * Cancel a javascript popup window, click No/Cancel button
   * 
   * @Then /^(?:|I )cancel the popup$/
   */
  public function cancel_popup() {
    $this->getSession()->getDriver()->getWebDriverSession()->dismiss_alert();
  }

}
