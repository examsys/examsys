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
 * Generates ExamSys gradebook.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class gradebook extends generator
{

    /**
     * Create a new gradebook paper entry
     *
     * @param array parameters
     *  string parameters[paperid]
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_paper($parameters)
    {
        if (empty($parameters['paperid'])) {
            throw new data_error('paperid must be provided');
        }
        $defaults = array('timestamp' => date('Y-m-d H:i:s'), 'paperid' => $parameters['paperid']);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        $sql = $this->db->prepare('INSERT INTO gradebook_paper VALUES (?, ?)');
        $sql->bind_param('is', $parameters['paperid'], $settings['timestamp']);
        if (!$sql->execute()) {
            throw new data_error('Create new gradebook paper failed with parameters: ' . $parameters['paperid'] . '--' . implode('--', $settings));
        }
        $sql->close();
        return $settings;
    }

    /**
     * Create a new gradebook user entry
     *
     * @param array parameters
     *  string parameters[userid]
     *  string parameters[paperid]
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_user($parameters)
    {
        if (empty($parameters['userid'])) {
            throw new data_error('userid must be provided');
        }
        if (empty($parameters['paperid'])) {
            throw new data_error('paperid must be provided');
        }
        $defaults = array('grade' => 0, 'adjustedgrade' => 0, 'classification' => 'Fail', 'paperid' => $parameters['paperid']
        , 'userid' => $parameters['userid']);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        $sql = $this->db->prepare('INSERT INTO gradebook_user VALUES (?, ?, ?, ?, ?)');
        $sql->bind_param('iidds', $parameters['paperid'], $parameters['userid'], $settings['grade'], $settings['adjustedgrade'], $settings['classification']);
        if (!$sql->execute()) {
            throw new data_error('Create new gradebook user failed with parameters: ' . $parameters['userid'] . '--' . $parameters['paperid'] . '--' . implode('--', $settings));
        }
        $sql->close();
        return $settings;
    }
}
