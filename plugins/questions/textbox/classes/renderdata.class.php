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

namespace plugins\questions\textbox;

/**
 *
 * Class for textbox in the blank rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class renderdata extends \questiondata {
  use \defaultgetmarks;
  /**
   * List of textboxes viewed
   * @var array
   */
  public $textboxesseen;

  /**
   * Number of columns in editor
   * @var integer
   */
  public $editorcolumns;

  /**
   * Number of rows in editor
   * @var integer
   */
  public $editorrows;

  /**
   * Editor type
   * @var string
   */
  public $editor;

  /**
   * Editor config file
   * @var string
   */
  public $file;

  /**
   * Editor type
   * @var string
   */
  public $type;

  /**
   * Editor style
   * @var string
   */
  public $style;

  /**
   * Editor config file template
   * @var string
   */
  public $editorconfig;

  /**
   * Editor textarea file template
   * @var string
   */
  public $editortextarea;

  /**
   * User answer
   * @var string
   */
  public $useranswer;

  /**
   * Mathjax state
   * @var boolean
   */
  public $editormathjax;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype = 'textbox';
    $this->textboxesseen = array();
    $this->editormathjax = false;
    $this->editor  = '';
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
    $this->displaydefault = true;
    if ($this->notes != '') {
      $this->displaynotes = true;
    }
    if ($this->scenario != '') {
      $this->displayscenario = true;
    }
    $this->displayleadin = true;
    if ($this->qmedia != '') {
      $this->displaymedia = true;
    }
  }

  /**
   * Question level settings for template rendering
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @param mixed $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   */
  public function set_question($screen_pre_submitted, $useranswer, $userdismissed) {
    // Noting to do.
  }

  /**
   * Option level settings for template rendering
   * @param integer $part_id part loop id
   * @param mixed $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function set_option_answer($part_id, $useranswer, $userdismissed, $screen_pre_submitted) {
    global $string;
    $option = $this->get_opt($part_id);
    $textboxes_seen = $this->textboxesseen;
    if (!in_array($this->questionno, $textboxes_seen)) {
      $textboxes_seen[] = $this->questionno;
      $settings = json_decode($this->settings, true);
      $this->editorcolumns = $settings['columns'];
      $this->editorrows = $settings['rows'];
      $texteditorplugin = \plugins\plugins_texteditor::get_editor();
      $te = explode('_', $texteditorplugin->get_name());
      $this->editor = $te[1];
      // We can override the enabled texteditor with plain/mathjax at the question level.
      if (isset($settings['editor'])) {
        if ($settings['editor'] == 'mathjax') {
          $this->editormathjax = true;
          $this->editor = 'plain';
        } elseif ($settings['editor'] == 'plain') {
          $this->editormathjax = false;
          $this->editor = 'plain';
        }
      }

      if ($useranswer == '' and $screen_pre_submitted == 1) {
        $this->file = $this->editor . '_config_unanswered';
      } else {
        $this->file = $this->editor . '_config_answered';
      }

      $this->editorconfig = $this->editor . '_config.html';

      $textbox_width  = ( 40 + ( $settings['columns'] * 8 ) );
      $textbox_height = ( $settings['rows'] * 28 );

      $background_colour = '';

      if ($useranswer == '' and $screen_pre_submitted == 1) {
        $this->unanswered = true;
      } else {
        $this->unanswered = false;
      }
      $this->useranswer = $useranswer;

      $this->editortextarea = $this->editor . '_textarea.html';
      $this->type = $texteditorplugin->get_type(\plugins\plugins_texteditor::TYPE_STANDARD);
      $this->style = $background_colour . "width:" . $textbox_width . "px; height:" . $textbox_height . "px";
      $this->textboxesseen = $textboxes_seen;
      $marks = $this->marks;
      $marks += $option['markscorrect'];
      $this->marks = $marks;
    }
  }

  /**
   * Additional option level settings for template rendering
   * @param integer $part_id part loop id
   * @param mixed $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function process_options($part_id, $useranswer, $userdismissed, $screen_pre_submitted) {
    // Nothing to do.
  }
}