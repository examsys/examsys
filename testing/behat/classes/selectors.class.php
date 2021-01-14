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

use Behat\Mink\Session;

/**
 * Used to define things that behat can select in Rogo
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
class selectors
{
    /**
     * An array of selector types that can be used by behat tests,
     * unless built into behat directly they should also have an
     * entry in self::$rogoselectors.
     *
     * @var array
     */
    protected static $allowedrogoselectors = array(
        // Built in selectors.
        'id' => 'id',
        'id_or_name' => 'id_or_name',
        'link' => 'link',
        'button' => 'button',
        'link_or_button' => 'link_or_button',
        'content' => 'content',
        'field' => 'field',
        'select' => 'select',
        'checkbox' => 'checkbox',
        'radio' => 'radio',
        'file' => 'file',
        'optgroup' => 'optgroup',
        'option' => 'option',
        'fieldset' => 'fieldset',
        'table' => 'table',
        // Rogo selectors.
        'menu' => 'menu', //<div class="sidebar
        'sub_menu' => 'sub_menu', //<div id="popup3" class="popup"
        'menu_section' => 'menu_section', //<div class="submenuheading"
        'navigation' => 'navigation', //<div class="breadcrumb"
        'paper_title' => 'paper_title', // PAPER_title => <div class="PAGE_title"
        'main_content' => 'main_content',
        'admin_tool_link' => 'admin_tool_link',
        'pop_page_title' => 'pop_page_title',
        'menu_item' => 'menu_item',
        'page_title' => 'page_title',
        'content_section' => 'content_section',
        'folder' => 'folder',
        'search_menu' => 'search_menu',
        'main_menu' => 'main_menu',
        'main_menu_icon' => 'main_menu_icon',
        'main_menu_item' => 'main_menu_item', //<div id="toprightmenu"
        'sub_search_menu_item' => 'sub_search_menu_item',
        'paper_type' => 'paper_type',
        'question_type' => 'question_type',
        'paper_question_leadin' => 'paper_question_leadin',
        'bank_question_leadin' => 'bank_question_leadin',
        'summary_mark' => 'summary_mark',
        'classification_button' => 'classification_button',
        'class_totals_menu_item' => 'class_totals_menu_item',
        'class_totals_student' => 'class_totals_student',
        'question_bank_item' => 'question_bank_item',
    );

    /**
     * An array containing XPATH selectors for elements of Rogo that behat can select.
     * The key is the name of the selector, the value the XPATH string describing it.
     *
     * @var array
     */
    protected static $rogoselectors = array(
    'menu_item' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' menuitem ') and contains(normalize-space(.) , %locator%)]
XPATH
     ,'mainmenuicon' => <<<XPATH
//img[contains(@id,'toprightmenu_icon')]
XPATH
     ,'admin_tool_link' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' container ') and contains(normalize-space(.) , %locator%)]
XPATH
    ,'sub_menu' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' popup ') and contains(normalize-space(.) , %locator%)]      
XPATH
    ,'menu_section' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' submenuheading ') and contains(normalize-space(.) , %locator%)]      
XPATH

    ,'pagetitle ' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' submenuheading ') and contains(normalize-space(.) , %locator%)]      
XPATH

    ,'navigation' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' breadcrumb ') and contains(normalize-space(.) , %locator%)]      
XPATH
    ,'main_content' => <<<XPATH
//table[contains(concat(' ', normalize-space(@id), ' '), ' content ') and contains(normalize-space(.) , %locator%)]      
XPATH
    ,'paper_title' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' page_title ') and contains(normalize-space(.) , %locator%)]      
XPATH
     ,'pop_page_title' => <<<XPATH
//title[contains(concat(' ', normalize-space(.), ' '), %locator%)]
XPATH
     ,'menu' => <<<XPATH
//div[contains(@class, 'sidebar')]
XPATH
      ,'main_menu' => <<<XPATH
//div[contains(@id, 'toprightmenu')]
XPATH
      ,'main_menu_item' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' trm_div ') and contains(normalize-space(.) , %locator%)]      
XPATH
      ,'main_menu_icon' => <<<XPATH
//img[@id='toprightmenu_icon']
XPATH
    ,'search_menu' => <<<XPATH
//div[contains(@id, 'popup0') and contains(@style, 'display: block;')]
XPATH
    ,'sub_search_menu_item' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' popupitem ') and contains(normalize-space(.) , %locator%)]      
XPATH
    ,'content_section' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' subsect_title ')]/nobr
XPATH
    ,'folder' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' f_details ')]/a
XPATH
    ,'paper_type' => <<<XPATH
//td[contains(concat(' ', normalize-space(@class), ' '), ' icon ') and contains(normalize-space(.) , %locator%)]
XPATH
    ,'question_type' => <<<XPATH
//tr[contains(concat(' ', normalize-space(@class), ' '), 'q') and contains(@data-qtype , %locator%)]
XPATH
    ,'paper_question_leadin' => <<<XPATH
//td[contains(@class, 'l') and contains(normalize-space(.) , %locator%)]
XPATH
    ,'bank_question_leadin' => <<<XPATH
//*[contains(@class, 'viewq') and contains(normalize-space(.) , %locator%)]
XPATH
    ,'summary_mark' => <<<XPATH
//td[contains(text(),'Your mark')]/following-sibling::td[contains(normalize-space(.), %locator%)]
XPATH
    ,'classification_button' => <<<XPATH
//td[contains(@class, 'overall') and contains(normalize-space(.) , %locator%)]
XPATH
    ,'class_totals_menu_item' => <<<XPATH
//div[contains(concat(' ', normalize-space(@class), ' '), ' popup_title ') and contains(normalize-space(.) , %locator%)]      
XPATH
    ,'class_totals_student' => <<<XPATH
//td[contains(normalize-space(.) , %locator%)]      
XPATH
    ,'question_bank_item' => <<<XPATH
//tr[contains(@class, 'q') and contains(normalize-space(.) , %locator%)]
XPATH
    );

    /**
     * Get the custom Rogo selector list or a selector.
     *
     * @param string $selectorname
     * @return array
     */
    public static function get_selectors($selectorname = null)
    {
        if (empty($selectorname)) {
            return self::$rogoselectors;
        } else {
            return self::$rogoselectors[$selectorname];
        }
    }

    /**
     * Checks if the the named selector is allowed in Rogo behat tests.
     *
     * @param string $namesselector
     * @return boolean
     */
    public static function is_allowed_named($namesselector)
    {
        return isset(self::$allowedrogoselectors[$namesselector]);
    }

    /**
     * Adds the custom Rogo selectors to behat.
     *
     * @param \testing\behat\Behat\Mink\Session $session The mink session
     * @return void
     */
    public static function register_rogo_selectors(Session $session)
    {
        foreach (self::get_selectors() as $name => $xpath) {
            $session->getSelectorsHandler()->getSelector('named_exact')->registerNamedXpath($name, $xpath);
            $session->getSelectorsHandler()->getSelector('named_partial')->registerNamedXpath($name, $xpath);
        }
    }
}
