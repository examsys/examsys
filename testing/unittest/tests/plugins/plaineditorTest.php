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

use testing\unittest\unittestdatabase;
use plugins\texteditor\plugin_plain_texteditor\plugin_plain_texteditor;

/**
 * Test 'core' texteditor plugin 'plain'
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class plaineditortest extends unittestdatabase
{
    /**
     * @var integer new version of plugin being installed
     */
    private $newversion;

    /**
     * Generate data for test.
     */
    public function datageneration(): void
    {
        $text = new plugin_plain_texteditor();
        $this->newversion = $text->get_file_version();
    }

    /**
     * Test install plain
     * @group texteditor
     */
    public function test_install_plain()
    {
        $text = new plugin_plain_texteditor();
        $this->assertEquals('OK', $text->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password')));
        // Check tables are correct.
        $queryTable = $this->query(array('columns' => array('component', 'type', 'version'), 'table' => 'plugins'));
        $expectedTable = array(
            0 => array (
                'component' => 'plugin_plain_texteditor',
                'type' => 'texteditor',
                'version' => $this->newversion
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
        $queryTable = $this->query(array('columns' => array('component', 'setting', 'value', 'type'), 'table' => 'config', 'orderby' => array(1, 2), 'where' => array(array('column' => 'component', 'value' => 'plugin_plain_texteditor'))));
        $expectedTable = array(
            0 => array (
                'component' => 'plugin_plain_texteditor',
                'setting' => 'installed',
                'value' => 1,
                'type' => 'boolean'
            ),
            1 => array(
                'component' => 'plugin_plain_texteditor',
                'setting' => 'supports_mathjax',
                'value' => 1,
                'type' => 'boolean'
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test uninstall plain
     * @group texteditor
     */
    public function test_uninstall_plain()
    {
        $text = new plugin_plain_texteditor();
        $text->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
        $this->assertEquals('OK', $text->uninstall($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password')));
        // Check tables are correct.
        $this->assertEquals(0, $this->rowcount('plugins'));
        $queryTable = $this->query(array('columns' => array('component', 'setting', 'value', 'type'), 'table' => 'config', 'orderby' => array(1, 2), 'where' => array(array('column' => 'component', 'value' => 'plugin_plain_texteditor'))));
        $expectedTable = array(
            0 => array (
                'component' => 'plugin_plain_texteditor',
                'setting' => 'installed',
                'value' => 0,
                'type' => 'boolean'
            ),
            1 => array(
                'component' => 'plugin_plain_texteditor',
                'setting' => 'supports_mathjax',
                'value' => 1,
                'type' => 'boolean'
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test enable plain
     * @group texteditor
     */
    public function test_enable_plain()
    {
        $text = new plugin_plain_texteditor();
        $text->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
        $text->enable_plugin();
        // Check tables are correct.
        $queryTable = $this->query(array('columns' => array('component', 'type', 'version'), 'table' => 'plugins'));
        $expectedTable = array(
            0 => array (
                'component' => 'plugin_plain_texteditor',
                'type' => 'texteditor',
                'version' => $this->newversion
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test get header file
     * @group texteditor
     */
    public function test_get_header_file()
    {
        $plain = new plugin_plain_texteditor();
        $this->assertEquals('plain.html', $plain->get_header_file());
    }

    /**
     * Test get editor etype
     * @group texteditor
     */
    public function test_get_type()
    {
        $plain = new plugin_plain_texteditor();
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
    public function clean_leadin()
    {
        $plain = new plugin_plain_texteditor();
        $this->assertTrue($plain->cleanleadin('test - <div class="mee">\alpha</div>'));
        $this->assertTrue($plain->cleanleadin('test - \alpha'));
    }

    /**
     * Test repalce text for save
     * @group texteditor
     */
    public function test_prepare_text_for_save()
    {
        $plain = new plugin_plain_texteditor();
        $this->assertEquals('[tex]\sigma[/tex]', $plain->prepare_text_for_save('[tex]\sigma[/tex]'));
        $this->assertEquals('[texi]\sigma[/texi]', $plain->prepare_text_for_save('[texi]\sigma[/texi]'));
    }

    /**
     * Test text for display
     * @group texteditor
     */
    public function test_get_text_for_display()
    {
        $plain = new plugin_plain_texteditor();
        $this->assertEquals('<div class="mee">\alpha</div>', $plain->get_text_for_display('<div class="mee">\alpha</div>'));
    }
}
