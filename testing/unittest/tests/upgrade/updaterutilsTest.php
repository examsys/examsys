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

use testing\unittest\unittestdatabase;

/**
 * Test the UpdaterUtils class.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class updaterutilstest extends unittestdatabase {
  /** @var \UpdaterUtils Store an updater utils object for use in the tests. */
  protected $updateutil;
  
  public function getDataSet() {
    return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "upgrade" . DIRECTORY_SEPARATOR . "updaterutils.yml");
  }

  /**
   * Do some setup for the tests.
   */
  public function setUp() {
    parent::setUp();
    // Create the updater utils object.
    $this->updateutil = new UpdaterUtils($this->config->db, $this->config->get('cfg_db_database'));
  }

  /**
   * Clean up.
   */
  public function tearDown() {
    $this->updateutil = null;
    parent::tearDown();
  }

  /**
   * Test that the version check works when Rogo needs upgrading on a non-developer system.
   *
   * @group update
   */
  public function test_check_version_upgrade_non_dev() {
    $this->config->set('cfg_dev_system', false);
    $this->config->set('rogo_version', '6.1.0');
    $this->config->override_xml('6.2.0', 'version');
    // Version numbers are higher than the version set in the configuration file.
    $this->assertTrue($this->updateutil->check_version('6.1.1'));
    // Version number equal to the code version.
    $this->assertTrue($this->updateutil->check_version('6.2.0'));
    // Version number equal to the installed version.
    $this->assertFalse($this->updateutil->check_version('6.1.0'));
    // Version number lower than the installed version.
    $this->assertFalse($this->updateutil->check_version('6.0.0'));
    $this->assertFalse($this->updateutil->check_version('6.0.19'));
    // Version number higher than the code version.
    $this->assertFalse($this->updateutil->check_version('6.3.0'));
  }

  /**
   * Test that the version check works when Rogo does not need upgrading on a non-developer system.
   *
   * @group update
   */
  public function test_check_version_no_upgrade_non_dev() {
    $this->config->set('cfg_dev_system', false);
    $this->config->set('rogo_version', '6.2.0');
    $this->config->override_xml('6.2.0', 'version');
    // Version number equal to the code version.
    $this->assertFalse($this->updateutil->check_version('6.2.0'));
    // Version number equal to the installed version.
    $this->assertFalse($this->updateutil->check_version('6.1.0'));
    // Version number lower than the installed version.
    $this->assertFalse($this->updateutil->check_version('6.0.0'));
    $this->assertFalse($this->updateutil->check_version('6.0.19'));
    // Version number higher than the code version.
    $this->assertFalse($this->updateutil->check_version('6.3.0'));
  }

  /**
   * Test that the version check works when Rogo needs upgrading on a non-developer system.
   *
   * @group update
   */
  public function test_check_version_downgrade_non_dev() {
    $this->config->set('cfg_dev_system', false);
    $this->config->set('rogo_version', '6.3.0');
    $this->config->override_xml('6.2.0', 'version');
    // Version numbers are higher than the version set in the configuration file.
    $this->assertFalse($this->updateutil->check_version('6.3.1'));
    // Version number equal to the code version.
    $this->assertFalse($this->updateutil->check_version('6.2.0'));
    // Version number equal to the installed version.
    $this->assertFalse($this->updateutil->check_version('6.1.0'));
    // Version number lower than the installed version.
    $this->assertFalse($this->updateutil->check_version('6.0.0'));
    $this->assertFalse($this->updateutil->check_version('6.0.19'));
    // Version number higher than the code version.
    $this->assertFalse($this->updateutil->check_version('6.3.0'));
  }

  /**
   * Test that the version check works when Rogo needs upgrading on a developer system.
   *
   * @group update
   */
  public function test_check_version_upgrade_dev() {
    $this->config->set('cfg_dev_system', true);
    $this->config->set('rogo_version', '6.1.0');
    $this->config->override_xml('6.2.0', 'version');
    // Version numbers are higher than the version set in the configuration file.
    $this->assertTrue($this->updateutil->check_version('6.1.1'));
    // Version number equal to the code version.
    $this->assertTrue($this->updateutil->check_version('6.2.0'));
    // Version number equal to the installed version.
    $this->assertTrue($this->updateutil->check_version('6.1.0'));
    // Version number lower than the installed version.
    $this->assertFalse($this->updateutil->check_version('6.0.0'));
    $this->assertFalse($this->updateutil->check_version('6.0.19'));
    // Version number higher than the code version.
    $this->assertFalse($this->updateutil->check_version('6.3.0'));
  }

  /**
   * Test that the version check works when Rogo does not need upgrading on a developer system.
   *
   * @group update
   */
  public function test_check_version_no_upgrade_dev() {
    $this->config->set('cfg_dev_system', true);
    $this->config->set('rogo_version', '6.2.0');
    $this->config->override_xml('6.2.0', 'version');
    // Version number equal to the code version.
    $this->assertTrue($this->updateutil->check_version('6.2.0'));
    // Version number equal to the installed version.
    $this->assertFalse($this->updateutil->check_version('6.1.0'));
    // Version number lower than the installed version.
    $this->assertFalse($this->updateutil->check_version('6.0.0'));
    $this->assertFalse($this->updateutil->check_version('6.0.19'));
    // Version number higher than the code version.
    $this->assertFalse($this->updateutil->check_version('6.3.0'));
  }

  /**
   * Test that the version check works when Rogo needs upgrading on a developer system.
   *
   * @group update
   */
  public function test_check_version_downgrade_dev() {
    $this->config->set('cfg_dev_system', false);
    $this->config->set('rogo_version', '6.3.0');
    $this->config->override_xml('6.2.0', 'version');
    // Version numbers are higher than the version set in the configuration file.
    $this->assertFalse($this->updateutil->check_version('6.3.1'));
    // Version number equal to the code version.
    $this->assertFalse($this->updateutil->check_version('6.2.0'));
    // Version number equal to the installed version.
    $this->assertFalse($this->updateutil->check_version('6.1.0'));
    // Version number lower than the installed version.
    $this->assertFalse($this->updateutil->check_version('6.0.0'));
    $this->assertFalse($this->updateutil->check_version('6.0.19'));
    // Version number higher than the code version.
    $this->assertFalse($this->updateutil->check_version('6.3.0'));
  }
}
