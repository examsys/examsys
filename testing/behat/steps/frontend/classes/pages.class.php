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

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use testing\behat\helpers\database\state;
use testing\behat\helpers\rogo\Url;

/**
 * Step definitions for navigating to pages in Rogo.
 *
 * @copyright Copyright (c) 2020 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait pages
{
    /**
     * Visit a Rogo page.
     *
     * For valid page types and data @see visit_rogo_page()
     *
     * @Given /^I am on "([^"]*)" page$/
     *
     * @param string $page The type of page to go to.
     * @throws PendingException
     */
    public function i_am_on_page($page)
    {
        $this->visit_rogo_page($page);
    }

    /**
     * Visit a Rogo page where the specific content is identified by a some data.
     *
     * For valid page types and data @see visit_rogo_page()
     *
     * @Given /^I am on "([^"]*)" page for "([^"]*)"$/
     *
     * @param string $page The type of page to go to.
     * @param string $data Data to go identify a specific page type.
     * @throws PendingException
     */
    public function i_am_on_page_for($page, $data)
    {
        $this->visit_rogo_page($page, $data);
    }

    /**
     * Visit a Rogo report where the specific filters are identified by a some data.
     *
     * @Given I run report :name for :page with filters:
     *
     * @param string $name The name of the report
     * @param string $instance The instance the report relates to e.g. a paper or a module
     * @param TableNode $data The report filters
     * @throws PendingException
     */
    public function iRunReportForWithFilters(string $name, string $instance, TableNode $data)
    {
        switch ($name) {
            case 'Class Totals':
                $filters = $data->getRowsHash();
                $this->visitClassTotals($instance, $filters['moduleid'], $data);
                break;
            default:
                // Unsupported page type.
                throw new PendingException("A handler for the report '$name' page has not been implemented.");
                break;
        }
    }

    /**
     * Visit a specific section of a Rogo page where the specific content is identified by a some data.
     *
     * For valid page types and data @see visit_rogo_page()
     *
     * @Given /^I am on "([^"]*)" page in "([^"]*)" section for "([^"]*)"$/
     *
     * @param string $page The type of page to go to.
     * @param string $section The name of a section that should be displayed.
     * @param string $data Data to go identify a specific page type.
     * @throws PendingException
     */
    public function i_am_on_page_section_for($page, $section, $data)
    {
        $this->visit_rogo_page($page, $data, $section);
    }

    /**
     * Visit a Rogo page
     *
     * Valid pages:
     *
     * || Page            || Section                                       || Data                  ||
     * | User profile     | Log, Teams, Admin, Roles, Modules, Notes, ect  | A username              |
     *
     * @param string $page
     * @param string $data
     * @param string $section
     * @throws PendingException
     * @throws \Exception
     */
    protected function visit_rogo_page(string $page, string $data = '', string $section = '')
    {
        switch ($page) {
            case 'User profile':
                $this->visit_user_profile($data, $section);
                break;
            case 'Paper Details':
                $this->visitPaperDetails($data, $section);
                break;
            default:
                // Unsupported page type.
                throw new PendingException("A handler for the '$page' page has not been implemented.");
                break;
        }
        $this->lookForErrors();
    }

    /**
     * Loads the profile page for a user.
     *
     * Valid tabs:
     * - Accessibility
     * - Admin
     * - Metadata
     * - Modules
     * - Notes
     * - Roles
     * - Teams
     *
     * @param string $username
     * @param string $tab
     * @throws \Exception
     * @throws PendingException
     */
    protected function visit_user_profile(string $username, string $tab = '')
    {
        $userid = \UserUtils::username_exists($username, state::get_db());
        if ($userid === false) {
            throw new \Exception('Invalid username');
        }
        $this->visit(Url::userProfile($userid, $tab));
    }

    /**
     * Loads the paper details page for a paper.
     *
     * @param string $paper the paper name
     * @throws \Exception
     * @throws PendingException
     */
    protected function visitPaperDetails(string $paper): void
    {
        $paperid = \PaperUtils::getPaperId($paper);
        if ($paperid === null) {
            throw new \Exception('Invalid paper title');
        }
        $this->visit(Url::paperDetails($paperid));
    }

    /**
     * Loads the class totals report for a paper.
     *
     * @param string $paper the paper name
     * @param string $module the module code
     * @param TableNode $filters the report filters
     * @throws \Exception
     * @throws PendingException
     */
    protected function visitClassTotals(string $paper, string $module, TableNode $filters): void
    {
        $paperid = \PaperUtils::getPaperId($paper);
        if ($paperid === null) {
            throw new \Exception('Invalid paper title');
        }
        $data = $filters->getRowsHash();
        $data['module'] = \module_utils::get_idMod($module, state::get_db());
        if ($data['module']  === false) {
            throw new \Exception('Invalid module code');
        }
        $this->visit(Url::classTotals($paperid, $data));
    }
}
