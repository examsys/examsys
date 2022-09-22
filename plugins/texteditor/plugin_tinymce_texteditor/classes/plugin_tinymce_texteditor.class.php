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

namespace plugins\texteditor\plugin_tinymce_texteditor;

use plugins\plugins_texteditor;

/**
 * Text editor plugin helper file
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 onwards The University of Nottingham
 */

/**
 * SMS import plugin.
 */
class plugin_tinymce_texteditor extends plugins_texteditor
{
    /**
     * Name of the plugin;
     * @var string
     */
    protected $plugin = 'plugin_tinymce_texteditor';

    /**
     * Language pack component.
     * @var string
     */
    public $langcomponent = 'plugins/texteditor/plugin_tinymce_texteditor/plugin_tinymce_texteditor';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get text editor base file
     * @return string
     */
    public function get_header_file()
    {
        return 'tinymce.html';
    }

    /**
     * Get text editor javascript
     * @param array $configfile config file
     */
    public function get_javascript_config($configfile = '')
    {
        $render = new \render($this->config, $this->get_render_paths());
        $tinmymcedata = [
            'file' => 'tinymce_' . $configfile,
        ];
        if ($tinmymcedata['file'] != 'tinymce_') {
            $render->render($tinmymcedata, null, 'tinymce_config.html');
        }
    }

    /**
     * Get text editor textarea.
     * @param string $name editor name
     * @param string $id editor id
     * @param string $content editor content
     * @param string $type type of editor i.e. standard, simple, etc
     * @param string $styleoverwrite overwrite base styling
     * @raturn string
     */
    public function get_textarea($name, $id, $content, $type, $styleoverwrite = '')
    {
        $type = $this->get_type($type);
        $render = new \render($this->config, $this->get_render_paths());
        $tinmymcedata = array(
            'type' => $type,
            'id' => $id,
            'name' => $id,
            'content' => $content,
            'style' => $styleoverwrite);
        $render->render($tinmymcedata, null, 'tinymce_admin_textarea.html');
    }

    /**
     * Return editor specific type class
     * @param string $type generic type
     * @return string
     */
    public function get_type($type)
    {
        switch ($type) {
            case \plugins\plugins_texteditor::TYPE_SIMPLE:
                $type = 'editorSimple';
                break;
            case \plugins\plugins_texteditor::TYPE_BASIC:
                $type = 'editorBasic';
                break;
            case \plugins\plugins_texteditor::TYPE_STANDARD_UANS:
                $type = 'editorStandardUans';
                break;
            default:
                $type = 'editorStandard';
                break;
        }
        return $type;
    }
}
