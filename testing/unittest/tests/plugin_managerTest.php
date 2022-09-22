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

/**
 * Test plugin_manager class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class plugin_managertest extends unittestdatabase
{

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        // Do nothing.
    }

    /**
     * Test listing enabled plugin for type
     * @group plugins
     */
    public function test_get_plugin_type_enabled()
    {
        $editor = $this->config->get_setting('plugin_texteditor', 'enabled_plugin');
        $this->assertEquals($editor, plugin_manager::get_plugin_type_enabled('plugin_texteditor'));
    }

    /**
     * Test plugin installed
     * @group plugins
     */
    public function test_plugin_installed()
    {
        $datagenerator = $this->get_datagenerator('config', 'core');
        $datagenerator->change_setting(array('component' => 'plugin_plain_texteditor', 'setting' => 'installed', 'value' => 0));
        $this->assertTrue(plugin_manager::plugin_installed('plugin_tinymce_texteditor'));
        $this->assertFalse(plugin_manager::plugin_installed('plugin_plain_texteditor'));
        $this->assertFalse(plugin_manager::plugin_installed('unknowntestplugin'));
    }
}
