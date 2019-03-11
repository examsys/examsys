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

namespace plugins\texteditor\plugin_plain_texteditor;
/**
* Text editor plugin helper file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2017 onwards The University of Nottingham
*/

/**
 * SMS import plugin.
 */
class plugin_plain_texteditor extends \plugins\plugins_texteditor {
  /**
   * Name of the plugin;
   * @var string
   */
  protected $plugin = 'plugin_plain_texteditor';

  /**
   * Language pack component.
   * @var string
   */
  public $langcomponent = 'plugins/texteditor/plugin_plain_texteditor/plugin_plain_texteditor';

  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * Get text editor base file
   * @return string
   */
  public function get_header_file() {
    return 'plain.html';
  }

  /**
   * Get text editor javascript
   * @param array $configfile config file
   */
  public function get_javascript_config($configfile = '') {
    $render = new \render($this->config, $this->get_render_paths());
    $plaindata['file'] = 'plain_' . $configfile;
    if ($plaindata['file'] != 'plain_') {
      $render->render($plaindata, null, 'plain_config.html');
    }
  }

  /**
   * Get text editor textarea.
   * @param string $name editor name
   * @param string $id editor id
   * @param string $content editor content
   * @param string $type type of editor i.e. i.e. standard, simple, etc
   * @param string $styleoverwrite overwrite base styling
   */
  public function get_textarea($name, $id, $content, $type, $styleoverwrite = '') {
    // Reneder mathjax utils.
    $data['editormathjax'] = false;
    if ($this->get_type($type) === 'mathjax') {
      $data['editormathjax'] = true;
    }
    // Render textarea.
    $render = new \render($this->config, $this->get_render_paths());
    $data['id'] = $id;
    $data['questionno'] = $id;
    $data['content'] = $content;
    $data['style'] = $styleoverwrite;
    $render->render($data, $this->get_strings(), 'plain_admin_textarea.html');
  }

  /**
   * Return editor specific type class
   * @param string $type generic type
   * @return string
   */
  public function get_type($type) {
    if ($type == \plugins\plugins_texteditor::TYPE_MATHJAX and $this->config->get_setting($this->plugin, 'supports_mathjax')) {
      return 'mathjax';
    }
    return 'plain';
  }

  /**
   * Leadin clean function check
   * @param $leadin
   * @return boolean
   */
  public function clean_leadin($leadin) {
    return true;
  }

  /**
   * Plain text editor applies no conversion to stored text
   * @param string $text from database
   * @return string
   */
  public function get_text_for_display($text) {
    return $text;
  }

  /**
   * Plain text editor applies no conversion to db text
   * @param string $text from database
   * @return string
   */
  public function prepare_text_for_save($text) {
    return $text;
  }

  /**
   * Get header data.
   * @return array
   */
  public function get_header_data() {
    // Intentionally empty.
    return array();
  }

  /**
   * Get text editor base path
   * @return string
   */
  public function get_header_path() {
    // Intentionally blank as plain path loaded by core.
    return '';
  }
}