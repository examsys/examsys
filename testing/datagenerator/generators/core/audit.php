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
 * Generates ExamSys audit_access tables.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class audit extends generator
{
    /**
     * Create a audit entry
     *
     * @param array parameters
     *  string parameters[clientid]
     *  string parameters[userid]
     * @return array
     * @throws data_error If passed parameter is invalid
     */
    public function create($parameters)
    {
        if (!empty($parameters['username'])) {
            $parameters['userID'] = \UserUtils::username_exists($parameters['username'], $this->db);
        }
        if (empty($parameters['userID'])) {
            throw new data_error('userID must be provided');
        }
        if (empty($parameters['action'])) {
            throw new data_error('action must be provided');
        }
        $defaults = array(
            'time' => date('Y-m-d H:i:s'),
            'source' => 'vendor/bin/phpunit',
            'sourceID' =>  -1,
            'details' => 'test',
            'userID' => $parameters['userID'],
            'action' => $parameters['action'],
        );
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        $sql = 'INSERT INTO audit_log (userID, action, details, time, sourceID, source)
            VALUES (?, ?, ?, ?, ?, ?)';
        $query = $this->db->prepare($sql);
        $query->bind_param(
            'isssis',
            $settings['userID'],
            $settings['action'],
            $settings['details'],
            $settings['time'],
            $settings['sourceID'],
            $settings['source']
        );
        if (!$query->execute()) {
            throw new data_error(
                'Create new audit permissions log failed with parameters: '
                . $parameters['userID'] . '--'
                . $parameters['action'] . '--'
                . implode('--', $settings)
            );
        }
        $query->close();
        return $settings;
    }
}
