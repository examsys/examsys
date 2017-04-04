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
   testing\behat\selectors;

/**
 * Basic core step definitions.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Yijun Xue <yijun.xue@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait menu {

  /**
   * Check for menu items.
   *
   * @Then /^I should see menu with following items:$/
   * @param TableNode $menuitems The menu's items
   * @throws Exception
   */
  public function i_should_see_menu_with_following_item(TableNode $menuitems) {

    if (empty($menuitems)) {
      throw new Exception("The menu element or its items list are empty");
    }
    foreach ($menuitems->getHash() as $menuitem) {
      $title = $menuitem["menu_items"];
      $element = $this->find('link', $title);
      if (empty($element)) {
        throw new Exception("$menuitem is not in the menu");
      }
    }
  }

   /**
   * Check for menu section items.
   *
   * @Then I should see :menu_section menu section with following items
   * @param string $menu_section section title
   * @param TableNode $menuitems The menu's items
   * @throws Exception
   */
  
  public function i_should_see_menu_section_with_following_item($menu_section, TableNode $menuitems) {
    if (empty($menuitems) || empty($menu_section)) {
      throw new Exception("The menu name or items is empty");
    }
    foreach ($menuitems->getHash() as $menuitem) {
      $title = $menuitem["item"];
      //$element = $this->find('sub_menu', $title);
      $menuitem = $this->find("xpath", "//div[contains(concat(' ', normalize-space(@class), ' '), ' submenuheading ') and contains(normalize-space(.) , '" . $menu_section . "')]/following-sibling::div/div[contains(concat(' ', normalize-space(@class), ' '), ' menuitem ') and contains(normalize-space(.) , '" . $title  . "')]");
      if (empty($menuitem)) {
        throw new Exception("menu section item is not exist in the submenu");
      }
    }
  }
   
   /**
   * Check for submenu items.
   *
   * @Then /^I should see submenu with following items:$/
   * @param TableNode $menuitems The menu's items
   * @throws Exception
   */
  public function i_should_see_submenu_with_following_item(TableNode $menuitems) {

    if (empty($menuitems)) {
      throw new Exception("The submenu items list is empty");
    }
    foreach ($menuitems->getHash() as $menuitem) {
      $title = $menuitem["menu_items"];
      $element = $this->find('sub_menu', $title);
      if (empty($element)) {
        throw new Exception("$menuitem is not in the submenu");
      }
    }
  }
  
  
  /**
   * Checks if topright menu is hiden.
   *
   * @Then /^(?:|I )should not see main menu$/
   */
  public function i_not_see_main_menu() {
    $node = null;
    if (!$node = $this->find("xpath", selectors::get_selectors('main_menu'))) {
      throw new Exception("Could not find main menu");
    }
    if ($node->isVisible()) {
      throw new Exception("Main menu should be not visible.");
    }
  }

  /**
   * Toggle the main menu.
   *
   * @Then /^(?:|I )toggle the main menu$/
   */
  public function toggle_main_menu() {
    $node = null;
    if (!$node = $this->find("xpath", selectors::get_selectors("main_menu_icon"))) {
      throw new Exception("Could not find main menu");
    }
    $node->click();
  }

  /**
   * Checks for main menu items.
   *
   * @Then /^I should see main menu with following items:$/
   * @param TableNode $menuitems The menu's items
   * @throws Exception
   */
  public function i_see_main_menu(TableNode $menuitems) {
    if (empty($menuitems)) {
      throw new Exception("The menu element or its items list are empty");
    }
    $toprightmenu = $this->find("xpath", "//div[contains(@id, 'toprightmenu') and contains(@style, 'display: block;')]");

    if (empty($toprightmenu)) {
      throw new Exception('Main menu is not found');
    }

    foreach ($menuitems->getHash() as $menuitem) {
      $item = $menuitem['Item'];
      switch ($item) {
        case "Administrative Tools":
          $element = $this->find("main_menu_item", 'Administrative Tools');
          if (empty($element)) {
            throw new \Exception('$item in main menu could not be found');
          }
          break;
        case "Help and Support":
          $element = $this->find("main_menu_item", 'Help');
          if (empty($element)) {
            throw new \Exception('$item in main menu could not be found');
          }
          break;
        case "Sign Out":
          $element = $this->find("main_menu_item", 'Sign Out');
          if (empty($element)) {
            throw new \Exception('$item in main menu could not be found');
          }
          break;
        case "About Rogo":
          $element = $this->find("main_menu_item", 'About Rog');
          if (empty($element)) {
            throw new \Exception('$item in main menu could not be found');
          }
          break;
        default:
          throw new \Exception('main menu could not be found');
      }
    }
  }

  /**
   * Checks for popup search menu items.
   *
   * @Then /^I should see popup search menu with following items:$/
   * @param TableNode $menuitems The menu's items
   * @throws Exception
   */
  public function i_see_search_menu(TableNode $menuitems) {
    if (empty($menuitems)) {
      throw new Exception("The search menu element or its items list are empty");
    }
    $searchmenu = $this->find("xpath", selectors::get_selectors("search_menu"));

    if (empty($searchmenu)) {
      throw new Exception('popup search menu is not found');
    }

    foreach ($menuitems->getHash() as $menuitem) {
      $item = $menuitem['Item'];
      $element = $this->find("sub_search_menu_item", $item);
      if (empty($element)) {
            throw new Exception("$item in sub menu could not be found");
      }
    }
  }
}
