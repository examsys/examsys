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
    Behat\Behat\Definition\Call\Given as Given,
    Behat\Gherkin\Node\TableNode;

/**
 * Authentication step definitions.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait authentication {
  /**
   * Log into Rogo.
   *
   * @Given /^I login as "([^"]*)"$/
   * @param $username The username to be logged in.
   */
  public function i_login_as($username) {
    // Goto the base Rogo path.
    $this->getSession()->visit($this->locatePath('/'));
    $this->i_set_field("ROGO_USER", $username);
    $this->i_set_field("ROGO_PW", $username);
    $this->i_click("rogo-login-form-std", "button");
  }
  
  /**
   * Log out Rogo.
   *
   * @Then /^I log out$/
   * @param $username The username to be logged in.
   */
  public function i_log_out() {
    $this->toggle_main_menu();
    $this->i_click("signout", "id");
  }
}
