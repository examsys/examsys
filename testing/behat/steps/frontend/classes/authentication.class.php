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

namespace testing\behat\steps\frontend;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Definition\Call\Given as Given;
use Behat\Gherkin\Node\TableNode;

/**
 * Authentication step definitions.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait authentication
{
    /**
     * Log into ExamSys.
     *
     * @Given /^I login as "([^"]*)"$/
     * @param $username The username to be logged in.
     */
    public function i_login_as($username)
    {
        // Goto the base ExamSys path.
        $this->getSession()->visit($this->locatePath('/'));
        $this->lookForErrors();
        $this->i_set_field('ROGO_USER', $username);
        $this->i_set_field('ROGO_PW', $username);
        $this->i_click('rogo-login-form-std', 'button');
        $this->i_wait_for_page_to_load();
        try {
            $this->i_should_not_see('rogo-login-form-std', 'button');
        } catch (\Exception $e) {
            // We are still on the login page, so lets give a reasonable message.
            throw new \Exception("Login failed for $username");
        }
    }

    /**
     * Log out ExamSys.
     *
     * @Then /^I log out$/
     * @param $username The username to be logged in.
     */
    public function i_log_out()
    {
        $this->toggle_main_menu();
        $this->i_click('signout', 'id');
    }

    /**
     * Log out ExamSys transparently without changing page
     *
     * @Then /^I destroy the session$/
     */
    public function i_destroy_session()
    {
        $config = \Config::get_instance();
        $this->getSession()->setCookie($config->get('cfg_session_name'), null);
    }

    /**
     * Re-log into ExamSys from the current page.
     *
     * @Given /^I relogin as "([^"]*)"$/
     * @param $username The username to be logged in.
     */
    public function i_relogin_as($username)
    {
        $this->i_set_field('ROGO_USER', $username);
        $this->i_set_field('ROGO_PW', $username);
        $this->i_click('rogo-login-form-std', 'button');
        $this->i_wait_for_page_to_load();
        try {
            $this->i_should_not_see('rogo-login-form-std', 'button');
        } catch (\Exception $e) {
            // We are still on the login page, so lets give a reasonable message.
            throw new \Exception("Re-login failed for $username");
        }
    }

}
