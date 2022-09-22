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
 * Class for question statuses
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class QuestionStatus
{
    public $id = -1;
    protected $name = '';
    protected $exclude_marking = false;
    protected $retired = false;
    protected $is_default = false;
    protected $change_locked = true;
    protected $validate = true;
    protected $display_warning = false;
    protected $colour = '#000000';

    private $was_default = false;

    private $_db;
    private $_lang_strings;

    public function __construct($db, $lang_strings, $data)
    {
        $this->_db = $db;
        $this->_lang_strings = $lang_strings;

        // Check the type of $data
        if (is_array($data)) {
            // If it is an array, assume an associative array of fields for creating a new object (but not
            // saving it to the database)
            foreach ($data as $field => $val) {
                $this->$field = $val;
            }
        } elseif (ctype_digit($data)) {
            // If it is an int use it as an ID for the database lookup
            $this->id = $data;
            if (!$this->get_question_status()) {
                throw new DatabaseException('Error loading question status');
            }
        } elseif ($data !== null) {
            throw new DataTypeException('Invalid question status data type');
        }
    }

    /**
     * Persist the object to the database
     * @return boolean True if object was saved
     */
    public function save()
    {
        $success = false;

        $this->_db->autocommit(false);

        if ($this->id == -1) {
            $sql = 'SELECT count(id) FROM question_statuses WHERE name = ?';
            $result = $this->_db->prepare($sql);
            $result->bind_param('s', $this->name);
            $result->execute();
            $result->bind_result($count);
            $result->fetch();
            $result->close();

            if ($count > 0) {
                $this->_db->autocommit(true);
                throw new ItemExistsException();
            }

            $sql = 'INSERT INTO question_statuses(name, exclude_marking, retired, is_default, change_locked, validate, display_warning, colour, display_order) SELECT ?, ?, ?, ?, ?, ?, ?, ?, COALESCE(max(display_order) + 1,1) FROM question_statuses';
            $result = $this->_db->prepare($sql);
            $result->bind_param('siiiiiis', $this->name, $this->exclude_marking, $this->retired, $this->is_default, $this->change_locked, $this->validate, $this->display_warning, $this->colour);
            if ($result->execute()) {
                $success = true;
                $this->id = $this->_db->insert_id;
            }
            $result->close();
        } else {
            $sql = 'UPDATE question_statuses SET name = ?, exclude_marking = ?, retired = ?, is_default = ?, change_locked = ?, validate = ?, display_warning = ?, colour = ? where id = ?';
            $result = $this->_db->prepare($sql);
            $result->bind_param('siiiiiisi', $this->name, $this->exclude_marking, $this->retired, $this->is_default, $this->change_locked, $this->validate, $this->display_warning, $this->colour, $this->id);
            if ($result->execute()) {
                $success = true;
            }
            $result->close();
        }

        if ($success and $this->is_default) {
            $success = false;

            $sql = 'UPDATE question_statuses SET is_default = false WHERE id != ?';
            $result = $this->_db->prepare($sql);
            $result->bind_param('i', $this->id);
            if ($result->execute()) {
                $success = true;
            }
            $result->close();
        }

        if (!$success) {
            $this->_db->rollback();
        } else {
            $this->_db->commit();
        }

        $this->_db->autocommit(true);

        // Ensure we have a default set
        $this->reset_default();

        return $success;
    }

    /**
     * Load question status data from the database
     * @return boolean True if status was loaded
     */
    private function get_question_status()
    {
        $success = false;

        $sql = 'SELECT name, exclude_marking, retired, is_default, change_locked, validate, display_warning, colour, display_order FROM question_statuses WHERE id = ?';

        $result = $this->_db->prepare($sql);
        $result->bind_param('i', $this->id);
        $result->execute();
        $result->store_result();
        $result->bind_result($this->name, $this->exclude_marking, $this->retired, $this->is_default, $this->change_locked, $this->validate, $this->display_warning, $this->colour, $this->display_order);
        if ($result->fetch()) {
            $success = true;
        }
        $result->close();

        return $success;
    }

    /**
     * Remove a status from the database
     * @return boolean True if status was successfully deleted
     */
    public function delete()
    {
        $success = false;

        // Close any gaps in the display order
        $sql = 'UPDATE question_statuses SET display_order = display_order - 1 WHERE display_order > ?';
        $result = $this->_db->prepare($sql);
        $result->bind_param('i', $this->display_order);
        if ($result->execute()) {
            $success = true;
        }
        $result->close();

        $sql = 'DELETE FROM question_statuses where id = ?';
        $result = $this->_db->prepare($sql);
        $result->bind_param('i', $this->id);
        if ($result->execute()) {
            $success = true;
        }
        $result->close();

        // Make the first status default if this object was previously
        if ($success and $this->is_default) {
            if (!$this->reset_default()) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @param string $name
     */
    public function set_name($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return $this->name;
    }

    /**
     * @param boolean $exclude_marking
     */
    public function set_exclude_marking($exclude_marking)
    {
        $this->exclude_marking = $exclude_marking;
    }

    /**
     * @return boolean
     */
    public function get_exclude_marking()
    {
        return $this->exclude_marking;
    }

    /**
     * @param boolean $retired
     */
    public function set_retired($retired)
    {
        $this->retired = $retired;
    }

    /**
     * @return boolean
     */
    public function get_retired()
    {
        return $this->retired;
    }

    /**
     * @param boolean $is_default
     */
    public function set_is_default($is_default)
    {
        $this->is_default = $is_default;
    }

    /**
     * @return boolean
     */
    public function get_is_default()
    {
        return $this->is_default;
    }

    /**
     * @param boolean $change_locked
     */
    public function set_change_locked($change_locked)
    {
        $this->change_locked = $change_locked;
    }

    /**
     * @return boolean
     */
    public function get_change_locked()
    {
        return $this->change_locked;
    }

    /**
     * @param boolean $validate
     */
    public function set_validate($validate)
    {
        $this->validate = $validate;
    }

    /**
     * @return boolean
     */
    public function get_validate()
    {
        return $this->validate;
    }

    /**
     * @param boolean $display_warning
     */
    public function set_display_warning($display_warning)
    {
        $this->display_warning = $display_warning;
    }

    /**
     * @return boolean
     */
    public function get_display_warning()
    {
        return $this->display_warning;
    }

    /**
     * @param string $colour
     */
    public function set_colour($colour)
    {
        $this->colour = $colour;
    }

    /**
     * @return string
     */
    public function get_colour()
    {
        return $this->colour;
    }

    /**
     * Reset the default status to be the first in the database if none set
     * @return bool True if update was successful
     */
    private function reset_default()
    {
        $success = false;

        $this->_db->autocommit(false);

        $sql = 'SELECT count(id) FROM question_statuses WHERE is_default = 1';
        $result = $this->_db->prepare($sql);
        $result->execute();
        $result->bind_result($count);
        $result->fetch();
        $do_reset = ($count == 0);
        $result->close();

        if ($do_reset) {
            $sql2 = 'UPDATE question_statuses SET is_default = true WHERE display_order = 0';
            $result = $this->_db->prepare($sql2);
            if ($result->execute()) {
                $success = true;
            }
            $result->close();
        }

        $this->_db->commit();
        $this->_db->autocommit(true);

        return $success;
    }

    /**
     * Get an array containing all existing statuses
     * @param  mysqli $db             Database link
     * @param  array $lang_strings    Language Strings
     * @return array[QuestionStatus]  Existing statuses
     */
    private static function get_all_statuses_by_type($db, $lang_strings, $type)
    {
        $statuses = array();

        $sql = 'SELECT id, name, exclude_marking, retired, is_default, change_locked, validate, display_warning, colour FROM question_statuses ORDER BY display_order';

        $result = $db->prepare($sql);
        $result->execute();
        $result->store_result();
        $result->bind_result($id, $name, $exclude_marking, $retired, $is_default, $change_locked, $validate, $display_warning, $colour);
        while ($result->fetch()) {
            $data = array('id' => $id, 'name' => $name, 'exclude_marking' => $exclude_marking, 'retired' => $retired, 'is_default' => $is_default, 'change_locked' => $change_locked, 'validate' => $validate, 'display_warning' => $display_warning, 'colour' => $colour);
            $qs = new QuestionStatus($db, $lang_strings, $data);
            if ($type == '') {
                $statuses[] = $qs;
            } else {
                $statuses[$qs->$type] = $qs;
            }
        }
        $result->close();

        return $statuses;
    }

    public static function get_all_statuses($db, $lang_strings, $with_index = false)
    {
        if ($with_index  == true) {
            return QuestionStatus::get_all_statuses_by_type($db, $lang_strings, 'id');
        } else {
            return QuestionStatus::get_all_statuses_by_type($db, $lang_strings, '');
        }
    }

    public static function get_all_statuses_by_name($db, $lang_strings)
    {
        return QuestionStatus::get_all_statuses_by_type($db, $lang_strings, 'name');
    }

    /**
     * Generate a CSS string for the colours for all status contained in array
     * @param  array[mixed] $statuses Statuses
     * @return string                 CSS for colour definitions for statuses
     */
    public static function generate_status_css($statuses)
    {
        $css = '';

        foreach ($statuses as $status) {
            if ($status->id != -1) {
                $css .= <<<CSS
  .status{$status->id}, .status{$status->id} td {
    color: {$status->get_colour()}!important;
  }

CSS;
            }
        }

        return $css;
    }

    /*
    public statis function name_to_id($name) {

      return $id;
    }
    */

    /**
     * Get the IDs of all statuses that are flagged as 'retired'
     * @param  array $statuses       An array of the existing statuses
     * @return array[integer]        Array of status IDs
     */
    public static function get_retired_status_ids($statuses)
    {
        $retired_in = array();
        foreach ($statuses as $status) {
            if ($status->get_retired()) {
                $retired_in[] = $status->id;
            }
        }

        return $retired_in;
    }
}
