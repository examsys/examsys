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
 * Base object for questions
 *
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class Question
{
    public $id = -1;
    protected $excluded = '';
    protected $type = null;
    protected $theme = '';
    protected $scenario = '';
    protected $scenario_plain = '';
    protected $leadin = '';
    protected $leadin_plain = '';
    protected $notes = '';
    protected $correct_fback = '';
    protected $incorrect_fback = '';
    protected $score_method = 'Mark per Option';
    protected $display_method = '';
    protected $option_order = 'display order';
    protected $standards_setting = '';
    protected $bloom = null;
    protected $owner_id = null;
    protected $media = '';
    protected $media_width = 0;
    protected $media_height = 0;
    protected $teams = array();
    protected $checkout_time = null;
    protected $checkout_author_id = '';
    protected $created = null;
    protected $last_edited = null;
    protected $locked = null;
    protected $deleted = null;
    protected $status = -1;
    protected $settings = '';
    public $options = array();

    // Below are for support in question display etc

    public $error;
    public $useranswer = null;

    public $markinfo = null;
    public $qmark = null;
    public $totalpos = null;


    public $q_media = '';
    public $q_media_height = '';
    public $q_media_width = '';

    public $uniqid;

    public $std;

    public function set_settings($settings)
    {
        if (!is_array($settings)) {
            $this->settings = json_decode($settings, true);
        } else {
            $this->settings = $settings;
        }
    }

    public function set_useranswer($useranswer)
    {
        if (!is_array($useranswer)) {
            $this->useranswer = json_decode($useranswer, true);
        } else {
            $this->useranswer = $useranswer;
        }
    }

    public function add_to_useranswer($key, $value)
    {
        $this->useranswer[$key] = $value;
    }

    public function export_save(&$array)
    {
        $classvar = get_object_vars($this);
        foreach ($classvar as $key => $value) {
            if ($key == 'useranswer') {
                $key = 'user_answer';
            } elseif ($key == 'options') {
                $key = 'DONOTUSE';
            }
            if ($key != 'DONOTUSE') {
                $array[$key] = $value;
            }
            if ($key == 'qmark') {
                $array['mark'] = $value;
            }
        }
        if (isset($this->settings['m_correct'])) {
            $array['marks_correct'] = $this->settings['m_correct'];
        }
        if (isset($this->settings['m_partial'])) {
            $array['marks_partial'] = $this->settings['m_partial'];
        }
        if (isset($this->settings['m_incorrect'])) {
            $array['marks_incorrect'] = $this->settings['m_incorrect'];
        }
    }

    public function load($array)
    {
        foreach ($array as $key => $value) {
            if (property_exists($this, $key)) {
                $func_name = 'set_' . $key;
                if (method_exists($this, $func_name)) {
                    $this->$func_name($value);
                } else {
                    $this->$key = $value;
                }
            }
            if ($key == 'q_id') {
                $this->id = $value;
            } elseif ($key == 'user_answer') {
                $this->set_useranswer($value);
            }
        }

        if (!is_array($this->options)) {
            // Convert to objects!
        }
    }

    public function save($db)
    {
        if ($this->id > 0) {
            // Update the database.
            $query = $db->prepare('UPDATE questions SET 
                theme = ?, 
                scenario = ?, 
                leadin = ?, 
                correct_fback = ?, 
                incorrect_fback = ?, 
                display_method = ?, 
                notes = ?, 
                last_edited = ?, 
                bloom = ?, 
                scenario_plain = ?, 
                leadin_plain = ?, 
                std = ?,
                status = ?, 
                q_option_order = ?, 
                score_method = ?, 
                settings = ?  
            WHERE 
                q_id = ?
            ');

            if (is_array($this->settings)) {
                $settings = json_encode($this->settings);
            } else {
                $settings = $this->settings;
            }
            $this->last_edited = date('Y-m-d H:i:s');

            $query->bind_param(
                'ssssssssssssisssi',
                $this->theme,
                $this->scenario,
                $this->leadin,
                $this->correct_fback,
                $this->incorrect_fback,
                $this->display_method,
                $this->notes,
                $this->last_edited,
                $this->bloom,
                $this->scenario_plain,
                $this->leadin_plain,
                $this->standards_setting,
                $this->status,
                $this->option_order,
                $this->score_method,
                $settings,
                $this->id
            );
            $query->execute();
            $query->close();
        } else {
            //insert
            throw new Exception('Can not insert questions using this class (INSERT has not been implemented)');
        }
    }
}
