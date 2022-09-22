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

use yearutils;
use mappingutils;

/**
 * Generates ExamSys objective.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class objective extends generator
{

    /**
     * Create a new module
     *
     * @param array parameters
     *  string parameters[idMod]  the module internal id
     *  string parameters[identifier]
     * options are (
     *   objective, calendar_year, sequence)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_objective($parameters)
    {
        if (empty($parameters['idMod'])) {
            throw new data_error('idMod must be provided');
        }
        if (empty($parameters['identifier'])) {
            throw new data_error('identifier must be provided');
        }
        $defaults = array(
            'objective' => 'test objective', 'calendar_year' => null, 'sequence' => 1, 'idMod' => $parameters['idMod'],
            'identifier' => $parameters['identifier']);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        if (is_null($settings['calendar_year'])) {
            $yearutils = new yearutils($this->db);
            $settings['calendar_year'] = $yearutils->get_current_session();
        }

        $obj_id = mappingutils::get_objectives_start();
        $sql = $this->db->prepare('INSERT INTO objectives VALUES (?, ?, ?, ?, ?, ?)');
        $sql->bind_param(
            'isiiii',
            $obj_id,
            $settings['objective'],
            $settings['idMod'],
            $settings['identifier'],
            $settings['calendar_year'],
            $settings['sequence']
        );
        if (!$sql->execute()) {
            throw new data_error('Create new objective failed with parameters: ' . implode('--', $settings));
        }
        $sql->close();
        $settings['obj_id'] = $obj_id;
        return $settings;
    }

    /**
     * Create a new module
     *
     * @param array parameters
     *  string parameters[idMod] the module internal id
     * options are (
     *   title, source_url, occurrence, calendar_year)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_session($parameters)
    {
        if (empty($parameters['idMod'])) {
            throw new data_error('idMod must be provided');
        }
        $identifier = mappingutils::get_sessions_start();
        $defaults = array(
            'title' => 'test', 'calendar_year' => null, 'occurrence' => null, 'source_url' => 'https://www.example.com',
            'idMod' => $parameters['idMod'], 'identifier' => $identifier);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        if (is_null($settings['calendar_year'])) {
            $yearutils = new yearutils($this->db);
            $settings['calendar_year'] = $yearutils->get_current_session();
        }

        $sql = $this->db->prepare('INSERT INTO sessions VALUES (NULL, ?, ?, ?, ?, ?, ?)');
        $sql->bind_param(
            'iissis',
            $settings['identifier'],
            $settings['idMod'],
            $settings['title'],
            $settings['source_url'],
            $settings['calendar_year'],
            $settings['occurrence']
        );
        if (!$sql->execute()) {
            throw new data_error('Create new session failed with parameters: ' . implode('--', $settings));
        }
        $settings['sess_id'] = $sql->insert_id;
        $settings['identifier'] = $identifier;
        $sql->close();
        return $settings;
    }
}
