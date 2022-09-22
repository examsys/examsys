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

/**
 * Generates ExamSys api tables.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class api extends generator
{

    /**
     * Create a new gradebook paper entry
     *
     * @param array parameters
     *  string parameters[clientid]
     *  string parameters[userid]
     * @return array
     * @throws data_error If passed parameter is invalid
     */
    public function create_client($parameters)
    {
        if (empty($parameters['clientid'])) {
            throw new data_error('clientid must be provided');
        }
        if (empty($parameters['userid'])) {
            throw new data_error('userid must be provided');
        }
        $defaults = array('secret' => 'ssshitsasecret', 'redirect' => 'https://www.example.com', 'clientid' =>  $parameters['clientid'], 'userid' => $parameters['userid']);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        $sql = $this->db->prepare('INSERT INTO oauth_clients (client_id, client_secret, redirect_uri, user_id) VALUES (?, ?, ?, ?)');
        $sql->bind_param('ssss', $parameters['clientid'], $settings['secret'], $settings['redirect'], $parameters['userid']);
        if (!$sql->execute()) {
            throw new data_error('Create new client failed with parameters: ' . $parameters['userid'] . '--' . $parameters['clientid'] . '--' . implode('--', $settings));
        }
        $sql->close();
        return $settings;
    }

    /**
     * Create a new external system
     * @param array parameters
     *  string parameters[type]
     *  string parameters[name]
     * @return array
     * @throws data_error If passed parameter is invalid
     */
    public function create_external($parameters)
    {
        if (empty($parameters['type'])) {
            throw new data_error('clientid must be provided');
        }
        if (empty($parameters['name'])) {
            throw new data_error('userid must be provided');
        }
        $type = $parameters['type'];
        $name = $parameters['name'];
        $sql = $this->db->prepare('INSERT INTO external_systems VALUES (NULL, ?, ?)');
        $sql->bind_param('ss', $name, $type);
        if (!$sql->execute()) {
            throw new data_error('Create new external system failed with parameters: ' . $type . '--' . $name);
        }
        $id = $sql->insert_id;
        if (!empty($parameters['clientid'])) {
            $clientid = $parameters['clientid'];
            $insert = $this->db->prepare('INSERT INTO external_systems_mapping VALUES (?, ?)');
            $insert->bind_param('si', $clientid, $id);
            if (!$insert->execute()) {
                throw new data_error('Create new external system mapping failed with parameters: ' . $clientid . '--' . $id);
            }
            $insert->close();
        }
        $sql->close();
        $parameters['id'] = $id;
        return $parameters;
    }
}
