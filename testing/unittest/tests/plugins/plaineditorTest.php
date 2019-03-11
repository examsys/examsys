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
use PHPUnit\DbUnit\DataSet\YamlDataSet;

/**
 * Test 'core' texteditor plugin 'plain'
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class plaineditortest extends unittestdatabase {
  /**
   * Get init data set from yml
   * @return dataset
   */
  public function getDataSet() {
    return new YamlDataSet($this->get_base_fixture_directory() . "plugins" . DIRECTORY_SEPARATOR . "texteditor" . DIRECTORY_SEPARATOR . "default.yml");
  }

  /**
   * Get expected data set from yml
   * @param string $name fixture file name
   * @return dataset
   */
  public function get_expected_data_set($name) {
    return new YamlDataSet($this->get_base_fixture_directory() . "plugins" . DIRECTORY_SEPARATOR . "texteditor" . DIRECTORY_SEPARATOR . $name . ".yml");
  }

  /**
   * Test install plain
   * @group texteditor
   */
  public function test_install_plain() {
    $text = new \plugins\texteditor\plugin_plain_texteditor\plugin_plain_texteditor();
    $this->assertEquals('OK', $text->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password')));
    // Check tables are correct.
    $queryTable = $this->getConnection()->createQueryTable('plugins', 'SELECT component, type, version FROM plugins');
    $expectedTable = $this->get_expected_data_set('install_plain')->getTable("plugins");
    $this->assertTablesEqual($expectedTable, $queryTable);
    $queryTable = $this->getConnection()->createQueryTable('config', 'SELECT component, setting, value, type FROM config order by 1, 2');
    $expectedTable = $this->get_expected_data_set('install_plain')->getTable("config");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test uninstall plain
   * @group texteditor
   */
  public function test_uninstall_plain() {
    $text = new \plugins\texteditor\plugin_plain_texteditor\plugin_plain_texteditor();
    $text->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
    $this->assertEquals('OK', $text->uninstall($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password')));
    // Check tables are correct.
    $this->assertTableRowCount('plugins' ,0);
    $queryTable = $this->getConnection()->createQueryTable('config', 'SELECT component, setting, value, type FROM config  order by 1, 2');
    $expectedTable = $this->get_expected_data_set('uninstall_plain')->getTable("config");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test enable plain
   * @group texteditor
   */
  public function test_enable_plain() {
    $text = new \plugins\texteditor\plugin_plain_texteditor\plugin_plain_texteditor();
    $text->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
    $text->enable_plugin();
    // Check tables are correct.
    $queryTable = $this->getConnection()->createQueryTable('plugins', 'SELECT component, type, version FROM plugins');
    $expectedTable = $this->get_expected_data_set('plain_enabled')->getTable("plugins");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test get header file
   * @group texteditor
   */
  public function test_get_header_file() {
    $plain = new \plugins\texteditor\plugin_plain_texteditor\plugin_plain_texteditor();
    $this->assertEquals('plain.html', $plain->get_header_file());
  }

  /**
   * Test get editor etype
   * @group texteditor
   */
  public function test_get_type() {
    $plain = new \plugins\texteditor\plugin_plain_texteditor\plugin_plain_texteditor();
    $this->assertEquals('plain', $plain->get_type(\plugins\plugins_texteditor::TYPE_STANDARD));
    $this->config->set_setting('supports_mathjax', 1, \Config::BOOLEAN, $plain->get_name());
    $this->assertEquals('mathjax', $plain->get_type(\plugins\plugins_texteditor::TYPE_MATHJAX));
    $this->config->set_setting('supports_mathjax', 0, \Config::BOOLEAN, $plain->get_name());
    $this->assertEquals('plain', $plain->get_type(\plugins\plugins_texteditor::TYPE_MATHJAX));
  }

  /**
   * Test clena leadin check
   * @group texteditor
   */
  public function clean_leadin() {
    $plain = new \plugins\texteditor\plugin_plain_texteditor\plugin_plain_texteditor();
    $this->assertTrue($plain->cleanleadin("test - <div class=\"mee\">\alpha</div>"));
    $this->assertTrue($plain->cleanleadin("test - \alpha"));
  }

  /**
   * Test repalce text for save
   * @group texteditor
   */
  public function test_prepare_text_for_save() {
    $plain = new \plugins\texteditor\plugin_plain_texteditor\plugin_plain_texteditor();
    $this->assertEquals("[tex]\sigma[/tex]", $plain->prepare_text_for_save("[tex]\sigma[/tex]"));
    $this->assertEquals("[texi]\sigma[/texi]", $plain->prepare_text_for_save("[texi]\sigma[/texi]"));
  }

  /**
   * Test text for display
   * @group texteditor
   */
  public function test_get_text_for_display() {
    $plain = new \plugins\texteditor\plugin_plain_texteditor\plugin_plain_texteditor();
    $this->assertEquals("<div class=\"mee\">\alpha</div>", $plain->get_text_for_display("<div class=\"mee\">\alpha</div>"));
  }
}