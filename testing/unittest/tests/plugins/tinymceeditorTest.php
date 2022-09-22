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
use plugins\texteditor\plugin_tinymce_texteditor\plugin_tinymce_texteditor;

/**
 * Test 'core' texteditor 'tinymce'
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class tinymceeditortest extends unittestdatabase
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
        $text = new plugin_tinymce_texteditor();
        $this->newversion = $text->get_file_version();
    }

    /**
     * Test install tinymce
     * @group texteditor
     */
    public function test_install_tinymce()
    {
        $text = new plugin_tinymce_texteditor();
        $this->assertEquals('OK', $text->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password')));
        // Check tables are correct.
        $queryTable = $this->query(array('columns' => array('component', 'type', 'version'), 'table' => 'plugins'));
        $expectedTable = array(
            0 => array (
                'component' => 'plugin_tinymce_texteditor',
                'type' => 'texteditor',
                'version' => $this->newversion
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
        $queryTable = $this->query(array('columns' => array('component', 'setting', 'value', 'type'), 'table' => 'config', 'orderby' => array(1, 2), 'where' => array(array('column' => 'component', 'value' => 'plugin_tinymce_texteditor'))));
        $expectedTable = array(
            0 => array (
                'component' => 'plugin_tinymce_texteditor',
                'setting' => 'installed',
                'value' => 1,
                'type' => 'boolean'
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test uninstall tinymce
     * @group texteditor
     */
    public function test_uninstall_tinymce()
    {
        $text = new plugin_tinymce_texteditor();
        $text->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
        $this->assertEquals('OK', $text->uninstall($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password')));
        // Check tables are correct.
        $this->assertEquals(0, $this->rowcount('plugins'));
        $queryTable = $this->query(array('columns' => array('component', 'setting', 'value', 'type'), 'table' => 'config', 'orderby' => array(1, 2), 'where' => array(array('column' => 'component', 'value' => 'plugin_tinymce_texteditor'))));
        $expectedTable = array(
            0 => array(
                'component' => 'plugin_tinymce_texteditor',
                'setting' => 'installed',
                'value' => 0,
                'type' => 'boolean'
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test enable tinymce
     * @group texteditor
     */
    public function test_enable_tinymce()
    {
        $text = new plugin_tinymce_texteditor();
        $text->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
        $text->enable_plugin();
        // Check tables are correct.
        $queryTable = $this->query(array('columns' => array('component', 'type', 'version'), 'table' => 'plugins'));
        $expectedTable = array(
            0 => array (
                'component' => 'plugin_tinymce_texteditor',
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
        $tinymce = new plugin_tinymce_texteditor();
        $this->assertEquals('tinymce.html', $tinymce->get_header_file());
    }

    /**
     * Test get editor etype
     * @group texteditor
     */
    public function test_get_type()
    {
        $tinymce = new plugin_tinymce_texteditor();
        $this->assertEquals('editorSimple', $tinymce->get_type(\plugins\plugins_texteditor::TYPE_SIMPLE));
        $this->assertEquals('editorBasic', $tinymce->get_type(\plugins\plugins_texteditor::TYPE_BASIC));
        $this->assertEquals('editorStandardUans', $tinymce->get_type(plugins\plugins_texteditor::TYPE_STANDARD_UANS));
        $this->assertEquals('editorStandard', $tinymce->get_type('meh'));
    }

    /**
     * Test replace <div class="mee"></div> tags with [tex][/tex]
     * @group texteditor
     */
    public function test_prepare_text_for_save()
    {
        $tinymce = new plugin_tinymce_texteditor();
        $this->assertEquals('[tex]\sigma[/tex]', $tinymce->prepare_text_for_save('<div class="mee">\sigma</div>'));
        $this->assertEquals('[texi]\sigma[/texi]', $tinymce->prepare_text_for_save('<span class="mee">\sigma</span>'));
    }

    /**
     * Test replace [tex][/tex] tags with <div class="mee"></div>
     * @group texteditor
     */
    public function test_get_text_for_display()
    {
        $tinymce = new plugin_tinymce_texteditor();
        $this->assertEquals('<div class="mee">\alpha</div>', $tinymce->get_text_for_display('[tex]\alpha[/tex]'));
        $this->assertEquals('<span class="mee">\alpha</span>', $tinymce->get_text_for_display('[texi]\alpha[/texi]'));
    }

    /**
     * Test clena leadin check
     * @group texteditor
     */
    public function clean_leadin()
    {
        $tinymce = new plugin_tinymce_texteditor();
        $this->assertFalse($tinymce->cleanleadin('test - <div class="mee">\alpha</div>'));
        $this->assertTrue($tinymce->cleanleadin('test - \alpha'));
    }
}
