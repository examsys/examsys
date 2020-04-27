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

namespace testing\datagenerator;

use Config;
use yearutils;
use assessment;
use UserUtils;

/**
 * Generates Rogo paper.
 *
 * @author Yijun Xue <yijun.xue@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class papers extends generator
{
    /**
     * Creates metadata restrictions for a paper.
     *
     * Parameters:
     * - name string The type of metadata
     * - value string The value of the metadata a student must have
     *
     * @param int $paperid The database id of the paper.
     * @param array|stdClass $parameters
     * @return array
     * @throws data_error
     */
    public function create_metadata(int $paperid, $parameters): array
    {
        // If an object is passed convert it into an array.
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }
        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }
        $defaults = array(
            'paperID' => $paperid,
            'name' => 'SomeType',
            'value' => 'Default',
        );
        $values = $this->set_defaults_and_clean($defaults, $parameters);
        $valued['id'] = $this->insert_mertadata($values);
        return $values;
    }

    /**
     * Create a new paper with 4 mandatory or optional parametera
     *
     * @param array $parameters
     * Mandatories param in $parameters are
     *  string parameters[papertitle]
     *  string parameters[papertype], any of
     *    assessment::TYPE_PROGRESS,
     *    assessment::TYPE_SUMMATIVE,
     *    assessment::TYPE_SURVEY,
     *    assessment::TYPE_OSCE,
     *    assessment::TYPE_OFFLINE,
     *    assessment::TYPE_PEERREVIEW
     *  string parameters[paperowner]
     *  string parameters[modulename]
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_paper($parameters)
    {

        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }
        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }
        if (empty($parameters['papertitle']) or !is_numeric($parameters['papertype']) or empty($parameters['paperowner']) or empty($parameters['modulename'])) {
            throw new data_error('Error in | papertitle | papertype | paperowner | modulename |');
        } else {
            $papertitle = $parameters['papertitle'];
            $papertype = (int)$parameters['papertype'];
            $paperowner = UserUtils::username_exists($parameters['paperowner'], $this->db);
            $modulename = $parameters['modulename'];
        }
        $default = array(
            'startdate' => null,
            'enddate' => null,
            'labs' => null,
            'duration' => null,
            'session' => null,
            'timezone' => 'Europe/London',
            'externalid' => null,
            'externalsys' => null,
            'calendaryear' => null,
            'remote' => 0,
            'password' => null,
        );
        $settings = $this->set_defaults_and_clean($default, $parameters);

        if (!empty($settings['startdate'])) {
            $startdate = new \DateTime($settings['startdate']);
            if (empty($startdate)) { // If user's date is not vialid
                throw new data_error("Paper's startdate is wrong");
            }
        } else {
            // We need to be specific with start and end date as some paper types cannot run over into another day.
            $startdate = new \DateTime('tomorrow');
            $startdate->setTime(12, 00);
        }
        if (!empty($settings['enddate'])) {
            $enddate = new \DateTime($settings['enddate']);
            if (empty($enddate)) { // If user's date is not vialid
                throw new data_error("Paper's enddate is wrong");
            }
        } else {
            $enddate = new \DateTime('tomorrow');
            $enddate->setTime(13, 00);
        }

        $conf = Config::get_instance();

        $paper = new assessment($this->db, $conf);
        if (!empty($settings['calendaryear'])) {
            $settings['session'] = $settings['calendaryear'];
        } else {
            $settings['session'] = date('Y');

            $yearutils = new yearutils($this->db);
            $supported = $yearutils->get_supported_years();

            if (!array_key_exists($settings['session'], $supported)) {
                $generator = new academic_year();
                $parameters['calendar_year'] = $settings['session'];
                $parameters['academic_year'] = $settings['session'] . '/' . (date('y') + 1);
                $generator->create_academic_year($parameters);
            }
        }

        $settings['moduleids'] = array();
        if (is_array($modulename)) {
            foreach ($modulename as $m) {
                $settings['moduleids'][] = self::test_get_moduleidbyname($m, $this->db);
            }
        } else {
            $settings['moduleids'][] = self::test_get_moduleidbyname($modulename, $this->db);
        }

        $settings['papertitle'] = $papertitle;
        $settings['papertype'] = $papertype;
        $settings['paperowner'] = $paperowner;
        $settings['start_date'] = $startdate->format('Y-m-d H:i:s');
        $settings['end_date'] = $enddate->format('Y-m-d H:i:s');

        try {
            $pid = $paper->create(
                $settings['papertitle'],
                $settings['papertype'],
                $settings['paperowner'],
                $settings['start_date'],
                $settings['end_date'],
                $settings['labs'],
                $settings['duration'],
                $settings['session'],
                $settings['moduleids'],
                $settings['timezone'],
                $settings['externalid'],
                $settings['externalsys'],
                $settings['remote'],
            );
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            throw new data_error('Error: ' . $message);
        }
        $settings['id'] = $pid;
        return $settings;
    }

    /**
     * Inserts meta data restrictions for the paper.
     *
     * @param array $data
     * @return int The id of the record inserted.
     * @throws data_error
     */
    protected function insert_mertadata(array $data): int
    {
        $sql = 'INSERT INTO paper_metadata_security (paperID, name, value) VALUES (?, ?, ?)';
        $query = $this->db->prepare($sql);
        $query->bind_param('iss', $data['paperID'], $data['name'], $data['value']);
        if (!$query->execute()) {
            // The metadata was not successfully inserted.
            throw new data_error("Metadata {$data['value']} for paper {$data['paperID']} was not inserted into database");
        }
        return $query->insert_id;
    }

    /**
     * Set the paper properties after creation
     * @param integer $pid property id
     * @param array $parameters
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function set_post_creation_settings(int $pid, array $parameters): array
    {
        $default = array('paper_prologue' => null, 'paper_postscript' => null, 'bgcolor' => 'white',
            'fgcolor' => 'black', 'themecolor' => '#316AC5', 'labelcolor' => '#C00000',
            'fullscreen' => 0, 'marking' => 0, 'bidirectional' => 1,
            'pass_mark' => 40, 'distinction_mark' => 70, 'folder' => null,
            'rubric' => null, 'calculator' => 1, 'display_correct_answer' => 1,
            'display_question_mark' => 1, 'display_students_response' => 1, 'display_feedback' => 1,
            'hide_if_unanswered' => 0, 'external_review_deadline' => null, 'internal_review_deadline' => null,
            'sound_demo' => 0);

        $settings = $this->set_defaults_and_clean($default, $parameters);

        foreach ($settings as $setting => $value) {
            if (!is_null($setting)) {
                $sql = $this->db->prepare('UPDATE properties SET ' . $setting . ' = ? WHERE property_id = ?');
                $sql->bind_param('si', $value, $pid);
                $sql->execute();
                $sql->close();
            }
        }
        return $settings;
    }

    /**
     * Get module id by name
     *
     * @param string $modulename
     * @param obj $db
     * @return int moduleid
     */
    public static function test_get_moduleidbyname($modulename, $db)
    {
        $result = $db->prepare('SELECT id FROM modules where fullname = ?');
        $result->bind_param('s', $modulename);
        $result->execute();
        $result->bind_result($moduleid);
        $result->store_result();
        $result->fetch();
        if ($result->num_rows == 0) {
            $result->close();
            return false;
        }
        $result->close();
        return $moduleid;
    }
}
