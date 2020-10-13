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

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use testing\behat\rogo_test;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Exception;

/**
 * Basic core step definitions.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait basic
{
    /**
     * Helper function to click at specified locations.
     * @param array $coordinates coordinates to click on image to create ploygon
     */
    private function polygonClicks($coordinates): void
    {
        $last = count($coordinates);
        $count = 1;
        foreach ($coordinates as $coord) {
            $this->getSession()->getDriver()->getWebDriverSession()->moveto(
                array(
                    'xoffset' => (int) $coord['x'],
                    'yoffset' => (int) $coord['y']
                )
            );
            if ($count < $last) {
                $this->getSession()->getDriver()->getWebDriverSession()->click();
            } else {
                $this->getSession()->getDriver()->getWebDriverSession()->doubleclick();
            }
            $count++;
        }
    }

    /**
     * Helper function to drag mouse to a location
     * @param int $x the x coordinate
     * @param int $y the y coordinate
     */
    private function dragTo(int $x, int $y): void
    {
        $this->getSession()->getDriver()->getWebDriverSession()->buttondown();
        $this->getSession()->getDriver()->getWebDriverSession()->moveto(
            array(
                'xoffset' => $x,
                'yoffset' => $y
            )
        );
        $this->getSession()->getDriver()->getWebDriverSession()->buttonup();
    }

    /**
     * Click on an element on the page.
     *
     * @Given /^I click "([^"]*)" "([^"]*)"$/
     * @param string $name The value to be searched for
     * @param string $selector The type of selector
     * @throws Exception
     */
    public function i_click($name, $selector)
    {
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
    public function i_should_see($content, $selector)
    {
        $element = $this->find($selector, $content);
        if (is_null($element)) {
            throw new \Exception("The \"$selector\" with the value of \"$content\" could not be found");
        }
        if ($this->running_javascript() and !$element->isVisible()) {
            throw new \Exception("The \"$selector\" with the value of \"$content\" is hidden");
        }
    }

    /**
     * Checks that text is not visible to the user
     *
     * @Then /^I should not see "([^"]*)" "([^"]*)"$/
     * @param string $content
     * @param string $selector
     * @return void
     * @throws Exception
     */
    public function i_should_not_see($content, $selector)
    {
        $element = $this->find($selector, $content);
        if (is_null($element)) {
            // Element is not present at all so all is good.
            return;
        }
        if ($this->running_javascript() and !$element->isVisible()) {
            // The element is present but hidden from the user.
            return;
        }
        throw new \Exception("The \"$selector\" with the value of \"$content\" is visibile");
    }

    /**
     * Keep browser live, for debuging
     *
     * @Given /^I pause "(?P<seconds_number>\d+)" seconds$/
     * @param int $seconds
     */
    public function i_wait_seconds($seconds)
    {
        $this->getSession()->wait($seconds * 1000, false);
    }

    /**
     * Sets focus to the names popup window.
     *
     * @And I focus :name popup
     * @param string $name
     * @return void
     */
    public function i_focus_popup($name)
    {
        $session = $this->getSession();
        $windows = $session->getDriver()->getWindowNames();

        foreach ($windows as $window) {
            $session->switchToWindow($window);
            $title = $session->getDriver()->getWebDriverSession()->title();
            if (trim($title) === trim($name)) {
                return;
            }
        }
        throw new Exception("Popup '$name' not found");
    }

    /**
     * Sets the focus to the main Rogo screen away from any popups.
     *
     * @And I focus main window
     */
    public function i_focus_main_window()
    {
        $session = $this->getSession();
        if (is_null($this->mainwindow)) {
            throw new Exception('Main window not set');
        }
        $session->switchToWindow($this->mainwindow);
    }

    /**
     * Check there is a popup present.
     *
     * @Then I should see popup page with title :title
     * @param String $title The page title
     * @throws Exception
     */
    public function i_see_popup_page($title)
    {
        $session = $this->getSession();
        $this->spin(function (rogo_test $context) use ($session, $title) {
            $current = $session->getDriver()->getWebDriverSession()->window_handle();
            $windows = $session->getDriver()->getWindowNames();
            foreach ($windows as $window) {
                $session->switchToWindow($window);
                $name = $session->getDriver()->getWebDriverSession()->title();
                if (trim($title) === trim($name)) {
                    return true;
                }
            }
            $session->switchToWindow($current);
            return false;
        });
    }

    /**
     * Tests that only the main window is open.
     *
     * @And only main window should be open
     * @throws Exception
     */
    public function only_main_window()
    {
        $session = $this->getSession();
        $this->spin(function (rogo_test $context) use ($session) {
            $windows = $session->getDriver()->getWindowNames();
            if (count($windows) === 1 and $windows[0] === $context->mainwindow) {
                return true;
            }
            return false;
        });
    }

    /**
     * Checks a popup was not found.
     *
     * @And I should not see popup page with title :title
     * @param type $title
     */
    public function i_should_not_see_popup($title)
    {
        $session = $this->getSession();
        $this->spin(function (rogo_test $context) use ($session, $title) {
            $current = $session->getDriver()->getWebDriverSession()->window_handle();
            $windows = $session->getDriver()->getWindowNames();
            foreach ($windows as $window) {
                $session->switchToWindow($window);
                $name = $session->getDriver()->getWebDriverSession()->title();
                if (trim($title) === trim($name)) {
                    return false;
                }
            }
            $session->switchToWindow($current);
            return true;
        });
    }

    /**
     * Close popup window back to main window
     *
     * @Then I close popup window :title
     * @throws Exception
     */
    public function i_close_popup_window($title)
    {
        $session = $this->getSession();
        $windows = $session->getDriver()->getWindowNames();

        if (empty($windows)) {
            throw new Exception('No windows open');
        }
        if (count($windows) === 1) {
            throw new Exception('No popup windows open');
        }
        $closed = false;
        for ($i = 1; $i < count($windows); $i++) {
            $this->getSession()->switchToWindow($windows[$i]);
            $name = $session->getDriver()->getWebDriverSession()->title();
            if (trim($name) === trim($title)) {
                $this->getSession()->executeScript('window.close()');
                $closed = true;
            }
        }
        if (!$closed) {
            throw new Exception("Popup with title '$title' not found");
        }
        $this->getSession()->switchToWindow($windows[0]);
    }


    /**
     * Check the page
     *
     * @Then /^I should see page with title "([^"]*)"$/
     * @param String $title The page title
     * @throws Exception
     */
    public function i_see_page_title($title)
    {
        $pagetitle = $this->find('xpath', "//div[@class='page_title']")->getText();
        if (mb_strpos($pagetitle, $title) === false) {
            throw new Exception('The page could not be found');
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
    public function i_see_table_with(TableNode $table)
    {
        $rows = $table->getRows();
        $headers = array_shift($rows);
        $max = count($headers); //number of columns in table
        foreach ($rows as $row) {
            for ($i = 1; $i <= $max; $i++) {
                $text = array_shift($row);
                $foundRows = $this->get_table_row($text, $i, "table[@id='maindata']");
                if (!$foundRows) {
                    throw new Exception('the table row could not been found');
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
    public function get_table_row($text, $columnnumber, $tableXpath)
    {
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

        $dd = $this->find('xpath', "//$tableXpath/thead/tr/th$column");
        $ww = $this->find('xpath', "//$tableXpath/tbody/tr/td" . $column . "[text()='$text']");
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
    public function i_click_admin_tool($name)
    {
        $elements = $this->find_all('xpath', "//div[@class='container' and contains(text(), '$name')]");
        $elements[0]->click();
    }

    /**
     * Waits for the Rogo page in the focused window to load.
     *
     * @And I wait for page to load
     */
    public function i_wait_for_page_to_load()
    {
        $session = $this->getSession();
        $this->spin(function (rogo_test $context) use ($session) {
            try {
                // Now try testing via Javascript if the page is in a loaded state.
                $js = <<<JS
return document.readyState === 'complete'
JS;
                if ($session->evaluateScript($js)) {
                    // The status code indicates the page is fully loaded.
                    return true;
                }
            } catch (UnsupportedDriverActionException $ex) {
                // Javascript evaluation is not supported so try looking at the last http status code.
                try {
                    // Try testing for a response code of 200 (Any other code is not loaded)
                    if ($session->getStatusCode() === 200) {
                        // The status code indicates the page returned content successfully.
                        return true;
                    }
                } catch (UnsupportedDriverActionException $ex) {
                    // All methods of determining if the page is fully loaded are not supported,
                    //  so we must assume it is and hope for the best.
                    return true;
                }
            }
            return false;
        });
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
    public function assert_popup_message($message)
    {
        return $message == $this->getSession()->getDriver()->getWebDriverSession()->getAlert_text();
    }

    /**
     * Confirm a javascript popup window, click OK/Yes button
     *
     * @Then /^(?:|I )confirm the popup$/
     */
    public function confirm_popup()
    {
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    }

    /**
     * Cancel a javascript popup window, click No/Cancel button
     *
     * @Then /^(?:|I )cancel the popup$/
     */
    public function cancel_popup()
    {
        $this->getSession()->getDriver()->getWebDriverSession()->dismiss_alert();
    }

    /**
     * Fill a dropdown field
     * @param string $id dropdown id
     * @param array  $values options to select
     */
    public function fillDropDown(string $id, array $values): void
    {
        $select = $this->find('xpath', '//*[@id="' . $id . '"]');
        foreach ($values as $option) {
            $select->selectOption($option, true);
        }
    }

    /**
     * Select check points.
     * @param string $id checkpoint id
     * @param array $values checkpoints to select
     */
    public function selectCheckPoints(string $id, array $values): void
    {
        foreach ($values as $option) {
            $select = $this->find('xpath', '//*[@id="' . $id . $option . '"]');
            $select->click();
        }
    }

    /**
     * Select a radio field.
     * @param string $id radio id
     */
    public function selectRadio(string $id): void
    {
        $select = $this->find('xpath', '//*[@id="' . $id . '"]');
        $select->click();
    }

    /**
     * Scroll the browser window to the element.
     * Useful in question edit page where the bottom-bar div blocks elements.
     * @param string $id element id
     */
    public function scrollToElement(string $id): void
    {
        $this->getSession()->executeScript(
            "requirejs(['jquery'], function ($) {
                $('" . $id . "')[0].scrollIntoView();
            });"
        );
    }

    /**
     * Fill a tinymce editor field.
     * We need to wait for the editor to load.
     * Retry mechanism copied from https://github.com/MaharaProject/mahara/blob/master/htdocs/testing/frameworks/behat/classes/FormFields/BehatFormEditor.php
     * @param string $id editor id
     * @param string $value fill contents
     * @throws Exception
     */
    public function fillTinyMCE(string $id, string $value): void
    {
        $lastexception = null;

        for ($i = 0; $i < 10; $i++) {
            try {
                $this->getSession()->executeScript(
                    "requirejs(['tinyMCE'], function () {
                        tinyMCE.get('" . $id . "').setContent('" . $value . "');
                    });"
                );
            } catch (Exception $e) {
                // Catching any kind of exception and ignoring it until times out.
                $lastexception = $e;

                // Waiting 0.1 seconds.
                usleep(100000);
            }
        }

        // If it is not available we throw the last exception.
        if (is_a($lastexception, 'Exception')) {
            throw $lastexception;
        }
    }

    /**
     * Draw a polygon.
     * Will draw a shape based on the coordinates provided in the feauture.
     * @param string $id element id
     * @param array $coordinates coordinates to click on image to create ploygon
     */
    public function drawAPolygon(string $id, array $coordinates): void
    {
        // We click on the canvas to start drawing the polygon.
        $element = $this->find('id', $id);
        $element->click();
        // Positions of everthing below is related to the position of the above click.
        $this->polygonClicks($coordinates);
    }

    /**
     * Draw a hotspot on a canvas.
     * Will draw the shape provided in the feature.
     * @param string $shape shape of hotspot
     * @param array $coordinates coordinates to click on image to create hotspot
     */
    public function addAHotspot(
        string $shape,
        array $coordinates
    ): void {
        switch ($shape) {
            case 'ellipse':
                $shape_element = $this->find('id', 'question1-ellipse');
                break;
            case 'polygon':
                $shape_element = $this->find('id', 'question1-polygon');
                break;
            default:
                $shape_element = $this->find('id', 'question1-rectangle');
                break;
        }
        $shape_element->click();
        // Positions of everthing below is related to the shape button clicked.
        // Start position.
        $this->getSession()->getDriver()->getWebDriverSession()->moveto(
            array(
                'xoffset' => (int) $coordinates[0]['x'],
                'yoffset' => (int) $coordinates[0]['y']
            )
        );
        $this->getSession()->getDriver()->getWebDriverSession()->click();
        // Other coords.
        $coordinates = array_splice($coordinates, 1);
        if ($shape === 'polygon') {
            $this->polygonClicks($coordinates);
        } else {
            $this->dragTo((int) $coordinates[0]['x'], (int) $coordinates[0]['y']);
        }
    }

    /**
     * Draw labels.
     * Adds labels to the image for each pair of coordinates provided in the feature.
     * @param int $xlabel x position in pixels of a label
     * @param int $ylabel y position in pixels of a label
     * @param array $coordinates coordinates to drag label to on image
     */
    public function addALabel(
        array $coordinates,
        int $xlabel = 10,
        int $ylabel = 50
    ): void {
        $chars = 'abcdefghijklmnopqrst';
        $count = 0;
        foreach ($coordinates as $coords) {
            $element = $this->find(
                'xpath',
                '//*[@class="align-left" and contains(text(), "Image")]//*[@class="mandatory"]'
            );
            $element->click();
            // Positions of everthing below is related to the image label clicked above.
            // The click is requried so moveto knows where to start from.
            // Click on a label textarea in the topleft.
            $this->getSession()->getDriver()->getWebDriverSession()->moveto(
                array(
                    'xoffset' => $xlabel,
                    'yoffset' => $ylabel
                )
            );
            $this->getSession()->getDriver()->getWebDriverSession()->click();
            // Add Text.
            $element = $this->find('id', 'canvas1');
            $element->keyPress(substr($chars, $count, 1));
            // Move Label.
            $this->dragTo((int)$coords['x'], (int)$coords['y']);
            $count++;
        }
    }

    /**
     * Set the upload source path for the test
     * @Given The upload source path is :path
     * @param string $path location of test upload assets
     */
    public function theUploadSourcePathIs(string $path): void
    {
        $this->setFilesPath($path);
    }

    /**
     * Focus on the iframe so we can select elements from it
     * @param $name
     */
    public function iFocusOnIframe($name): void
    {
        $this->getSession()->getDriver()->switchToIFrame($name);
    }

    /**
     * Waits for the an element to load.
     * @param string $selector the selector type
     * @param string $content the content to find
     */
    public function iWaitForElement(string $selector, string $content): void
    {
        if ($this->running_javascript()) {
            $this->spin(
                function (rogo_test $context) use ($selector, $content) {
                    $element = $this->find($selector, $content);
                    if (!is_null($element) and $element->isVisible()) {
                        return true;
                    }
                    return false;
                }
            );
        } else {
            throw new \Exception("The \"$selector\" with the value of \"$content\" is hidden");
        }
    }

    /**
     * Waits for the screen number to change
     * @param string $current_screen the screen we have navigated from
     */
    public function iWaitForScreen(string $current_screen): void
    {
        if ($this->running_javascript()) {
            $this->spin(
                function (rogo_test $context) use ($current_screen) {
                    $screen = $this->find('id_or_name', 'current_screen');
                    if ($screen->getValue() !== $current_screen) {
                        return true;
                    }
                    return false;
                }
            );
        } else {
            throw new \Exception('The "id_or_name" with the value of "current_screen" could not be found');
        }
    }
}
