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

namespace testing\datagenerator;

use LogExtraTime;
use PaperProperties;

/**
 * Generates ExamSys Campuses and Labs.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class labs extends generator
{
    /** @var int Stores how many campuses have been created. */
    protected static $campusescreated = 0;

    /** @var int Stores how many labs have been created. */
    protected static $labscreated = 0;

    /** @var int Stores how many exam pcs have been created. */
    protected static $pcscreated = 0;

    /**
     * Creates a campus.
     *
     * @param array|stdClass $parameters
     * @return array
     * @throws data_error
     */
    public function create_campus($parameters)
    {
        // If an object is passed convert it into an array.
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }
        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }
        $number = ++self::$campusescreated;
        $defaults = array(
            'name' => "Campus $number",
            'isdefault' => 0,
        );
        $values = $this->set_defaults_and_clean($defaults, $parameters);
        $values['id'] = $this->insert_campus($values);
        return $values;
    }

    /**
     * Creates an entry for an exam PC.
     *
     * @param type $parameters
     * @return array
     * @throws data_error
     */
    public function create_exam_pc($parameters)
    {
        // If an object is passed convert it into an array.
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }
        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }
        $number = ++self::$labscreated;
        $subnumber = (int)($number / 256);
        $lastnumber = ($number % 255) + 1; // Must not be 0, or we have a broadcast address.
        $defaults = array(
            'lab' => '',
            'address' => "192.168.$subnumber.$lastnumber", // We will be able to generate 65280 IP addresses before we get a duplicate.
            'hostname' => "test$number.example.com",
            'low_bandwidth' => 0,
        );
        $values = $this->set_defaults_and_clean($defaults, $parameters);
        $lab = $values['lab'];
        $values['lab'] = $this->get_lab_id($values['lab']);
        if ($values['lab'] === false) {
            throw new data_error("Lab '$lab' does not exist.");
        }
        $values['id'] = $this->insert_exam_pc($values);
        return $values;
    }

    /**
     * Creates a lab.
     *
     * @param array|stdClass $parameters
     * @return array
     * @throws data_error
     */
    public function create_lab($parameters)
    {
        // If an object is passed convert it into an array.
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }
        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }
        $number = ++self::$labscreated;
        $defaults = array(
            'name' => "Lab $number",
            'campus' => $this->get_default_campus(),
            'building' => 'My building',
            'room' => random_int(1, 50),
            'timetabling' => '',
            'support' => '',
            'plagarism' => '',
        );
        $values = $this->set_defaults_and_clean($defaults, $parameters);
        $campus = $values['campus'];
        $values['campus'] = $this->get_campus_id($values['campus']);
        if (empty($values['campus'])) {
            throw new data_error("Campus '$campus' does not exist.");
        }
        $values['id'] = $this->insert_lab($values);
        return $values;
    }

    /**
     * Create an entry for a user having extra time on a paper in a lab.
     *
     * Required parameters:
     * - lab: The id of the lab the extra time is for.
     * - paper: The id of the paper.
     * - student: The id of the student getting extra time
     * - invigilator: The id of the invigilator assigning extra time
     *
     * Optional parameters:
     * - extra_time: The amount of extra time granted to the user in minutes (default: 10)
     *
     * @param array|stdClass $parameters
     * @return array
     * @throws data_error
     */
    public function createExtraTime($parameters): array
    {
        // If an object is passed convert it into an array.
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }

        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }

        // Check that the required fields are present.
        if (!isset($parameters['invigilator'])) {
            throw new data_error('The invigilator must be passed.');
        }

        if (!isset($parameters['lab'])) {
            throw new data_error('The lab must be passed.');
        }

        if (!isset($parameters['paper'])) {
            throw new data_error('The paper must be passed.');
        }

        if (!isset($parameters['student'])) {
            throw new data_error('The student must be passed.');
        }

        // Clean the values.
        $defaults = array(
            'extra_time' => 10,
            'invigilator' => null,
            'lab' => null,
            'paper' => null,
            'student' => null,
        );
        $values = $this->set_defaults_and_clean($defaults, $parameters);

        // Save the entry.
        $propertyObj = PaperProperties::get_paper_properties_by_id($values['paper'], $this->db, []);
        $log_lab_end_time = $propertyObj->getLogLabEndTime($values['lab']);
        $log_extra_time = new LogExtraTime($log_lab_end_time, ['user_ID' => $values['student']], $this->db);
        $log_extra_time->save($values['invigilator'], $values['extra_time']);
        return $values;
    }

    /**
     * Sets up a record for an exam lab setting the start and end time.
     *
     * @param array|stdClass $parameters
     * @return array
     */
    public function createLabTime($parameters): array
    {
        // If an object is passed convert it into an array.
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }
        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }

        // Check that required values have been passed.
        if (!isset($parameters['labID'])) {
            throw new data_error('Must pass a labID');
        }

        if (!isset($parameters['paperID'])) {
            throw new data_error('Must pass a paperID');
        }

        // Ensure we have all the required data.
        $defaults = [
            'labID' => 0,
            'invigilatorID' => 0,
            'paperID' => 0,
            'start_time' => 'now',
            'end_time' => '1 hour',
        ];
        $values = $this->set_defaults_and_clean($defaults, $parameters);

        // Ensure that the dates are a Timestamp.
        if (!is_numeric($values['start_time'])) {
            $start = new \DateTime($values['start_time']);
            $values['start_time'] = $start->getTimestamp();
        }

        if (!is_numeric($values['end_time'])) {
            $end = new \DateTime($values['end_time']);
            $values['end_time'] = $end->getTimestamp();
        }

        // Create the record.
        $sql = 'INSERT INTO log_lab_end_time (labID, invigilatorID, paperID, start_time, end_time) VALUES (?, ?, ?, ?, ?)';
        $query = $this->db->prepare($sql);
        $query->bind_param('iiiii', $values['labID'], $values['invigilatorID'], $values['paperID'], $values['start_date'], $values['end_time']);

        if (!$query->execute()) {
            // The end time was not successfully inserted.
            throw new data_error('Failed to insert lab time into database');
        }

        // Return the data.
        $values['id'] = $query->insert_id;
        return $values;
    }

    /**
     * Gets the database id of a Campus from it's name.
     *
     * @param string $name
     * @return int The id of the campus record, or 0 if none is found.
     */
    protected function get_campus_id($name)
    {
        $campuses = $this->get_campuses();
        foreach ($campuses as $id => $campus) {
            if (trim($campus['campusname']) === trim($name)) {
                return $id;
            }
        }
        return 0;
    }

    /**
     * Gets details of all the campuses in ExamSys.
     *
     * @return array
     */
    protected function get_campuses()
    {
        $helper = new \campus($this->db);
        return $helper->get_all_campus_details();
    }

    /**
     * Gets the default campus.
     *
     * @return string
     */
    protected function get_default_campus()
    {
        $campuses = $this->get_campuses();
        foreach ($campuses as $campus) {
            if (!empty($campus['isdefault'])) {
                // We found the default campus.
                return $campus['campusname'];
            }
        }
        return '';
    }

    /**
     * Gets the id of a lab from it's name.
     *
     * @param string $name
     * @return int|false The database id of the lab, or false if it could not be found.
     */
    protected function get_lab_id($name)
    {
        $helper = new \LabFactory($this->db);
        return $helper->get_lab_id($name);
    }

    /**
     * Inserts the exam pc into the database.
     *
     * @param array $values
     * @throws data_error If passed parameter is invalid
     * @return int The database id of the new lab record.
     */
    protected function insert_campus($values)
    {
        $query = $this->db->prepare('INSERT INTO campus (name, isdefault) VALUES (?, ?)');
        $query->bind_param('si', $values['name'], $values['isdefault']);
        if (!$query->execute()) {
            // The campus was not successfully inserted.
            throw new data_error("Campus {$values['name']} not inserted into database");
        }
        return $query->insert_id;
    }

    /**
     * Inserts the campus into the database.
     *
     * @param array $values
     * @return int The database id of the new lab record.
     * @throws data_error If passed parameter is invalid
     */
    protected function insert_exam_pc($values)
    {
        $query = $this->db->prepare('INSERT INTO client_identifiers (lab, address, hostname, low_bandwidth) VALUES (?, ?, ?, ?)');
        $query->bind_param('issi', $values['lab'], $values['address'], $values['hostname'], $values['low_bandwidth']);
        if (!$query->execute()) {
            // The exam pc was not successfully inserted.
            throw new data_error("PC {$values['hostname']} ({$values['address']}) not inserted into database");
        }
        return $query->insert_id;
    }

    /**
     * Inserts a lab into the database.
     *
     * @param array $values
     * @return int The database id of the new lab record.
     * @throws data_error If passed parameter is invalid
     */
    protected function insert_lab($values)
    {
        $sql = 'INSERT INTO labs (name, campus, building, room_no, timetabling, it_support, plagarism) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $query = $this->db->prepare($sql);
        $query->bind_param('sisssss', $values['name'], $values['campus'], $building, $room_no, $timetabling, $it_support, $plagarism);
        if (!$query->execute()) {
            // The lab was not successfully inserted.
            throw new data_error("Lab {$values['name']} not inserted into database");
        }
        return $query->insert_id;
    }
}
