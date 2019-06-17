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
 * Test 'core' texteditor 'tinymce3'
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class tinymce3editortest extends unittestdatabase {
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
   * Test install tinymce3
   * @group texteditor
   */
  public function test_install_tinymce3() {
    $text = new \plugins\texteditor\plugin_tinymce3_texteditor\plugin_tinymce3_texteditor();
    $this->assertEquals('OK', $text->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password')));
    // Check tables are correct.
    $queryTable = $this->getConnection()->createQueryTable('plugins', 'SELECT component, type, version FROM plugins');
    $expectedTable = $this->get_expected_data_set('install_tinymce3')->getTable("plugins");
    $this->assertTablesEqual($expectedTable, $queryTable);
    $queryTable = $this->getConnection()->createQueryTable('config', 'SELECT component, setting, value, type FROM config order by 1, 2');
    $expectedTable = $this->get_expected_data_set('install_tinymce3')->getTable("config");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test uninstall tinymce3
   * @group texteditor
   */
  public function test_uninstall_tinymce3() {
    $text = new \plugins\texteditor\plugin_tinymce3_texteditor\plugin_tinymce3_texteditor();
    $text->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
    $this->assertEquals('OK', $text->uninstall($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password')));
    // Check tables are correct.
    $this->assertTableRowCount('plugins' ,0);
    $queryTable = $this->getConnection()->createQueryTable('config', 'SELECT component, setting, value, type FROM config  order by 1, 2');
    $expectedTable = $this->get_expected_data_set('uninstall_tinymce3')->getTable("config");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test enable tinymce3
   * @group texteditor
   */
  public function test_enable_tinymce3() {
    $text = new \plugins\texteditor\plugin_tinymce3_texteditor\plugin_tinymce3_texteditor();
    $text->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
    $text->enable_plugin();
    // Check tables are correct.
    $queryTable = $this->getConnection()->createQueryTable('plugins', 'SELECT component, type, version FROM plugins');
    $expectedTable = $this->get_expected_data_set('tinymce3_enabled')->getTable("plugins");
    $this->assertTablesEqual($expectedTable, $queryTable);
  }

  /**
   * Test get header file
   * @group texteditor
   */
  public function test_get_header_file() {
    $tinymce = new \plugins\texteditor\plugin_tinymce3_texteditor\plugin_tinymce3_texteditor();
    $this->assertEquals('tinymce3.html', $tinymce->get_header_file());
  }

  /**
   * Test get editor etype
   * @group texteditor
   */
  public function test_get_type() {
    $tinymce = new \plugins\texteditor\plugin_tinymce3_texteditor\plugin_tinymce3_texteditor();
    $this->assertEquals('editorSimple', $tinymce->get_type(\plugins\plugins_texteditor::TYPE_SIMPLE));
    $this->assertEquals('editorBasic', $tinymce->get_type(\plugins\plugins_texteditor::TYPE_BASIC));
    $this->assertEquals('editorStandardUans', $tinymce->get_type(plugins\plugins_texteditor::TYPE_STANDARD_UANS));
    $this->assertEquals('editorStandard', $tinymce->get_type('meh'));
  }

  /**
   * Test replace <div class="mee"></div> tags with [tex][/tex]
   * @group texteditor
   */
  public function test_prepare_text_for_save() {
    $tinymce = new \plugins\texteditor\plugin_tinymce3_texteditor\plugin_tinymce3_texteditor();
    $this->assertEquals("[tex]\sigma[/tex]", $tinymce->prepare_text_for_save("<div class=\"mee\">\sigma</div>"));
    $this->assertEquals("[texi]\sigma[/texi]", $tinymce->prepare_text_for_save("<span class=\"mee\">\sigma</span>"));
  }

  /**
   * Test replace [tex][/tex] tags with <div class="mee"></div>
   * @group texteditor
   */
  public function test_get_text_for_display() {
    $tinymce = new \plugins\texteditor\plugin_tinymce3_texteditor\plugin_tinymce3_texteditor();
    $this->assertEquals("<div class=\"mee\">\alpha</div>", $tinymce->get_text_for_display("[tex]\alpha[/tex]"));
    $this->assertEquals("<span class=\"mee\">\alpha</span>", $tinymce->get_text_for_display("[texi]\alpha[/texi]"));
  }

  /**
   * Test clena leadin check
   * @group texteditor
   */
  public function clean_leadin() {
    $tinymce = new \plugins\texteditor\plugin_tinymce3_texteditor\plugin_tinymce3_texteditor();
    $this->assertFalse($tinymce->cleanleadin("test - <div class=\"mee\">\alpha</div>"));
    $this->assertTrue($tinymce->cleanleadin("test - \alpha"));
  }
}