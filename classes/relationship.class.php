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
 * Class for objective mapping relationships
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class Relationship
{
    private $id = -1;
    private $idMod;
    private $paper_id;
    private $question_id;
    private $objective_id;
    private $calendar_year;
    private $vle_api;
    private $map_level;

    private $_db;
    private $_db_error;

    public function __construct($mysqli, $data = null)
    {
        $this->_db = $mysqli;

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
            if (!$this->get_relationship()) {
                throw new DatabaseException('Error loading relationship from databse');
            }
        } elseif ($data !== null) {
            throw new DataTypeException('Unknown initialisation data type');
        }
    }

    /**
     * Load a relationship from the database
     * @return boolean  True if the relationship was loaded successfully
     */
    private function get_relationship()
    {
        $success = false;

        $q_query = <<< QUERY
SELECT idMod, paper_id, question_id, obj_id, calendar_year, vle_api, map_level
FROM relationships
WHERE rel_id = ?
QUERY;
        $result = $this->_db->prepare($q_query);
        $result->bind_param('i', $this->id);
        $result->execute();
        $result->store_result();
        $result->bind_result($this->idMod, $this->paper_id, $this->question_id, $this->objective_id, $this->calendar_year, $this->vle_api, $this->map_level);
        if ($result->fetch()) {
            $success = true;
        }
        $result->close();

        return $success;
    }

    public function save()
    {
        if ($this->id != -1) {
            throw new MethodNotImplementedException('UPDATE not yet implemented');
        }

        $sql = <<< QUERY
INSERT INTO relationships (idMod, paper_id, question_id, obj_id, calendar_year, vle_api, map_level)
VALUES (?, ?, ?, ?, ?, ?, ?)
QUERY;
        if ($stmt = $this->_db->prepare($sql)) {
            $stmt->bind_param('iiiissi', $this->idMod, $this->paper_id, $this->question_id, $this->objective_id, $this->calendar_year, $this->vle_api, $this->map_level);
            if (!$stmt->execute()) {
                throw new DatabaseException($stmt->error . "<br/> $sql <br/>");
            }
            $stmt->close();

            $this->id = $this->_db->insert_id;
        } else {
            throw new DatabaseException($this->_db->error . "<br/> $sql <br/>");
        }

        return true;
    }

    /**
     * @return int
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function get_idMod()
    {
        return $this->idMod;
    }

    /**
     * @return integer
     */
    public function get_paper_id()
    {
        return $this->paper_id;
    }

    /**
     * @return integer
     */
    public function get_question_id()
    {
        return $this->question_id;
    }

    /**
     * @return integer
     */
    public function get_objective_id()
    {
        return $this->objective_id;
    }

    /**
     * @return string
     */
    public function get_calendar_year()
    {
        return $this->calendar_year;
    }

    /**
     * @return string
     */
    public function get_vle_api()
    {
        return $this->vle_api;
    }

    /**
     * @return integer
     */
    public function get_map_level()
    {
        return $this->map_level;
    }

    /**
     * @return string
     */
    public function get_db_error()
    {
        return $this->_db_error;
    }

    /**
     * Get an array of Relationship objects matching the given search criteria
     * @param  mysqli   $db             Database link
     * @param  mixed    $idMod          Module idMod or array of module idMods to search for
     * @param  string   $calendar_year  Academic year string in the form YYYY/YY (e.g. '2012/13')
     * @param  integer  $paper_id       ID of paper to search for
     * @param  mixed    $question_id    Question ID or array of question IDs to search for
     * @param  string   $limit          Maximum number of records to return. 0 = infinite
     * @return array                    Array of Relationship objects
     */
    public static function find($db, $idMod = '', $calendar_year = '', $paper_id = -1, $question_id = '', $limit = 0)
    {
        $sql = 'SELECT rel_id, idMod, paper_id, question_id, obj_id, calendar_year, vle_api, map_level FROM relationships';
        $params = array();
        $types = '';
        $relationships = array();
        $and = '';

        if ($idMod != '' or $calendar_year != '' or $paper_id != '') {
            $sql .= ' WHERE';
        }

        if ($idMod != '') {
            if (is_array($idMod)) {
                $mod_in = implode(',', $idMod);
                $sql .= " idMod IN ($mod_in)";
            } else {
                $sql .= ' idMod = ?';
                $params[] = &$idMod;
                $types .= 'i';
            }
            $and = ' AND';
        }

        if ($calendar_year != '') {
            $sql .= $and . ' calendar_year = ?';
            $params[] = &$calendar_year;
            $types .= 's';
            $and = ' AND';
        }

        if ($paper_id != -1) {
            $sql .= $and . ' paper_id = ?';
            $params[] = &$paper_id;
            $types .= 'i';
        }

        if ($question_id != '') {
            if (is_array($question_id)) {
                $q_in = implode(',', $question_id);
                $sql .= $and . " question_id IN ($q_in)";
            } else {
                $sql .= ' question_id = ?';
                $params[] = &$question_id;
                $types .= 'i';
            }
        }

        if ($limit != 0) {
            $sql .= ' LIMIT ' . $limit;
        }

        if ($result = $db->prepare($sql)) {
            array_unshift($params, $types);
            array_unshift($params, $result);
            call_user_func_array('mysqli_stmt_bind_param', $params);
            $result->execute();
            $result->store_result();
            $result->bind_result($id, $idMod, $paper_id, $question_id, $objective_id, $calendar_year, $vle_api, $map_level);
            while ($result->fetch()) {
                $data = array(
                    'id' => $id,
                    'idMod' => $idMod,
                    'paper_id' => $paper_id,
                    'question_id' => $question_id,
                    'objective_id' => $objective_id,
                    'calendar_year' => $calendar_year,
                    'vle_api' => $vle_api,
                    'map_level' => $map_level
                );
                $relationships[] = new Relationship($db, $data);
            }
            $result->close();
        } else {
            $relationships = false;
        }

        return $relationships;
    }
}
