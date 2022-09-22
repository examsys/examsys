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

/**
 *
 * Class for Textbox questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class QuestionTEXTBOX extends QuestionEdit
{
    protected $columns = 80;
    protected $rows = 4;
    protected $editor;
    protected $terms = '';
    public $max_options = 10;
    protected $_allow_change_marking_method = false;
    protected $_answer_negative = '';

    // Textbox is a rare example of a question type that will allow option editing
    protected $_allow_option_edit = true;


    protected $_fields_editable = array('theme', 'scenario', 'leadin', 'notes', 'correct_fback', 'incorrect_fback', 'rows', 'columns', 'editor', 'terms', 'correct', 'bloom', 'status');
    protected $_fields_settings = array('columns', 'rows', 'editor', 'terms');

    public function __construct($mysqli, $userObj, $lang_strings, $data = null)
    {
        // Get default text editor first, allow to be overridden
        $configObject = Config::get_instance();
        $this->editor = $configObject->get_setting('core', 'paper_textbox_editor_default');

        parent::__construct($mysqli, $userObj, $lang_strings, $data);
        $this->_fields_unified = array('marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect']);
    }

    // ACCESSORS

    /**
     * Get the columns for the question
     * @return integer
     */
    public function get_columns()
    {
        return $this->columns;
    }

    /**
     * Set the columns for the question
     * @param integer $value
     */
    public function set_columns($value)
    {
        if ($value != $this->columns) {
            $this->set_modified_field('columns', $this->columns);
            $this->columns = $value;
        }
    }

    /**
     * Get the rows for the question
     * @return integer
     */
    public function get_rows()
    {
        return $this->rows;
    }

    /**
     * Set the rows for the question
     * @param integer $value
     */
    public function set_rows($value)
    {
        if ($value != $this->rows) {
            $this->set_modified_field('rows', $this->rows);
            $this->rows = $value;
        }
    }

    /**
     * Get the editor for the question
     * @return string
     */
    public function get_editor()
    {
        return $this->editor;
    }

    /**
     * Set the editor for the question
     * @param string $value
     */
    public function set_editor($value)
    {
        if ($value != $this->editor) {
            $this->set_modified_field('editor', $this->editor);
            $this->editor = $value;
        }
    }

    /**
     * Get the terms for the question
     * @return string
     */
    public function get_terms()
    {
        return $this->terms;
    }

    /**
     * Set the terms for the question
     * @param string $value
     */
    public function set_terms($value)
    {
        if ($value != $this->terms) {
            $this->set_modified_field('terms', $this->terms);
            $this->terms = $value;
        }
    }
}
