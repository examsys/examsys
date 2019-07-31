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

/**
* Text editor plugin
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2017 onwards The University of Nottingham
*/

namespace plugins;

/**
 * Abstract mapping class.
 * 
 * This class should be extend by classes used define text editor plugins.
 */
abstract class plugins_texteditor extends \plugins\plugins {
    /**
     * Type of the editor.
     * @var string
     */
    const TYPE_STANDARD_UANS = 'standarduans';

    /**
     * Type of the editor.
     * @var string
     */
    const TYPE_STANDARD = 'standard';

    /**
     * Type of the editor.
     * @var string
     */
    const TYPE_SIMPLE = 'simple';

    /**
     * Type of the editor.
     * @var string
     */
    const TYPE_BASIC = 'basic';

    /**
     * Type of the editor.
     * @var string
     */
    const TYPE_MATHJAX = 'mathjax';

    /**
     * Editor for general screens
     * @var string
     */
    const CONFIG = 'config';

    /**
     * Editor for staff help screens
     * @var string
     */
    const HELP_STAFF = 'config_help_staff';

    /**
     * Editor for student help screens
     * @var string
     */
    const HELP_STUDENT = 'config_help_student';

    /**
     * Editor for announements screens
     * @var string
     */
    const ANNOUNCEMENTS = 'config_announcements';

    /**
     * Editor for paper properties screen
     * @var string
     */
    const PROPERTIES = 'config_properties';

    /**
     * Editor for unanswered questions
     * @var string
     */
    const UNANSWERED = 'config_unanswered';

    /**
     * Editor for answered questions
     * @var string
     */
    const ANSWERED = 'config_answered';

    /**
     * Editor for external email screens
     * @var string
     */
    const EXTERNAL = 'config_externals_email';

    /**
     * Editor for email screens
     * @var string
     */
    const EMAIL = 'config_email';

    /**
     * Editor for question editing screens
     * @var string
     */
    const QUESTION = 'config_question_editor';

    /**
     * Type of the plugin.
     * @var string
     */
    protected $plugin_type = 'texteditor';

    /**
     * Get text editor header file
     */
    abstract public function get_header_file();

    /**
     * Get text editor javascript.
     * @param array $data
     */
    abstract public function get_javascript_config($data = '');

    /**
     * Get text editor textarea.
     * @param string $name
     * @param string $id
     * @param string $content
     * @param string $type
     * @param string $styleoverwrite overwrite base styling
     */
    abstract public function get_textarea($name, $id, $content, $type, $styleoverwrite = '');

    /**
     * Leadin clean function check
     * @param $leadin
     * @return boolean
     */
    public function clean_leadin($leadin) {
        if (strpos($leadin, 'class="mee"') === false AND strpos($leadin, 'class=mee') === false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return editor specific type class
     * @param string $type generic type
     * @return string
     */
    abstract public function get_type($type);

    /**
     * Parse text before saving to the database
     * @param string $text the text to be processed
     * @return string
     */
    public function prepare_text_for_save($text) {
        // Replace deprecated mee maths
        preg_match_all("#<div class=\"mee\">(.*?)\</div>#si",$text,$tex_matches);
        if (count($tex_matches[0]) > 0) {
            foreach($tex_matches[0] as $m) {
                $new = str_replace(array('<div class="mee">','</div>'),array('[tex]','[/tex]'),$m);
                $text = str_replace($m, $new, $text);
            }
        }
        preg_match_all("#<span class=\"mee\">(.*?)\</span>#si",$text,$tex_matches);
        if (count($tex_matches[0]) > 0) {
            foreach($tex_matches[0] as $m) {
                $new = str_replace(array('<span class="mee">','</span>'),array('[texi]','[/texi]'),$m);
                $text = str_replace($m, $new, $text);
            }
        }
        return $text;
    }

    /**
     * Parse text before displaying in the editor
     * @param string $text the text to be processed
     * @return string
     */
    public function get_text_for_display($text) {
        // Support deprecated mee maths
        preg_match_all("#\[tex\](.*?)\[/tex\]#si",$text,$tex_matches);
        if (count($tex_matches[0]) > 0) {
            foreach($tex_matches[0] as $m) {
                $new = str_replace(array('[tex]','[/tex]'),array('<div class="mee">','</div>'),$m);
                $text = str_replace($m, $new, $text);
            }
        }
        preg_match_all("#\[texi\](.*?)\[/texi\]#si",$text,$tex_matches);
        if (count($tex_matches[0]) > 0) {
            foreach($tex_matches[0] as $m) {
                $new = str_replace(array('[texi]','[/texi]'),array('<span class="mee">','</span>'),$m);
                $text = str_replace($m, $new, $text);
            }
        }
        return $text;
    }

    /**
     * Get data to render in header.
     * @return array
     */
    public function get_header_data() {
        // Enable/Disable deprecated mee maths.
        $data['mee'] = $this->config->get_setting('core', 'paper_mee');
        return $data;
    }

    /**
     * Enable this plugin
     * Only one module text editor plugin should be enabled at anyone time
     */
    public function enable_plugin() {
        $enabled = array($this->plugin);
        $this->config->set_setting('enabled_plugin', $enabled, \Config::JSON, 'plugin_texteditor');
    }

    /**
     * Disable this plugin
     *
     */
    public function disable_plugin() {
      // Nothing to do as only one module text editor plugin is enable at a time enable_plugin handles everything.
    }

    /**
     * Get plugin name
     * @return string
     */
    public function get_name() {
      return $this->plugin;
    }

    /**
     * Render text editor header
     */
    public function display_header() {
      $render = new \render($this->config, $this->get_render_paths());
      $render->render($this->get_header_data(), null, $this->get_header_file());
    }

    /**
     * Get text editor base path
     * @return string
     */
    public function get_header_path() {
      return $this->get_path() . DIRECTORY_SEPARATOR . 'templates';
    }

    /**
     * Get the enabled text editor
     * @return object
     */
    public static function get_editor() {
      $texteditorplugin_name = \plugin_manager::get_plugin_type_enabled('plugin_texteditor');
      $texteditorpluginns = 'plugins\texteditor\\' . $texteditorplugin_name[0] . '\\' . $texteditorplugin_name[0];
      return new $texteditorpluginns();
    }

    /**
     * Get path to render templates
     * @return array
     */
    public function get_render_paths() {
      $renderpath = array($this->get_header_path());
      // Always get plain text editor.
      $renderpath[] = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'templates'. DIRECTORY_SEPARATOR . 'texteditor';
      return $renderpath;
    }

    /**
     * Get texteditor langpack
     * @return array
     */
    public function get_strings() {
      $langpack = new \langpack();
      $strings = $langpack->get_all_strings($this->langcomponent);
      // Always get plain text editor.
      $strings = array_merge($strings, $langpack->get_all_strings('/texteditor'));
      return $strings;
    }
}