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
 * Generates ExamSys save fail / access denied logs.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class incident extends generator
{

    /**
     * Create a new fail log
     *
     * @param array parameters
     *  string parameters[userid]
     *  string parameters[paperid]
     * options are (
     *   screen, ipaddress, status, request_url, response_data)
     * @return array
     * @throws data_error If passed parameter is invalid
     */
    public function create_fail($parameters)
    {
        if (empty($parameters['userid'])) {
            throw new data_error('userid must be provided');
        }
        if (empty($parameters['paperid'])) {
            throw new data_error('paperid must be provided');
        }
        $defaults = array('screen' => 1, 'ipaddress' => '127.0.0.1', 'status' => 'test',
            'request_url' => 'Test request', 'response_data' => 'This is response data',
            'userid' => $parameters['userid'], 'paperid' => $parameters['paperid']);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);

        $now = time();
        $sql = $this->db->prepare('INSERT INTO save_fail_log VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)');
        $sql->bind_param(
            'iiisssss',
            $parameters['userid'],
            $parameters['paperid'],
            $settings['screen'],
            $settings['ipaddress'],
            $now,
            $settings['status'],
            $settings['request_rul'],
            $settings['response_data']
        );

        if (!$sql->execute()) {
            throw new data_error('Create new save fail log failed with parameters: ' . $parameters['userid'] . '--' . $parameters['paperid'] . '--' . implode('--', $settings));
        }
        $settings['id'] = $sql->insert_id;
        $sql->close();
        return $settings;
    }

    /**
     * Create a new access denied log
     *
     * @param array parameters
     *  string parameters[userid]
     *  string parameters[page]
     * options are (
     *   title, ipaddress, msg)
     * @return array
     * @throws data_error If passed parameter is invalid
     */
    public function create_denied($parameters)
    {
        if (empty($parameters['userid'])) {
            throw new data_error('userid must be provided');
        }
        if (empty($parameters['page'])) {
            throw new data_error('paperid must be provided');
        }
        $defaults = array('ipaddress' => '127.0.0.1', 'title' => 'fatal error',
            'msg' => 'page not loading', 'created' => date('Y-m-d H:i:s'));
        $settings = $this->set_defaults_and_clean($defaults, $parameters);

        $sql = $this->db->prepare('INSERT INTO denied_log VALUES (NULL, ?, ?, ?, ?, ?, ?)');

        $userid = $parameters['userid'];
        $page = $parameters['page'];
        $sql->bind_param('isssss', $userid, $settings['created'], $settings['ipaddress'], $page, $settings['title'], $settings['msg']);

        if (!$sql->execute()) {
            throw new data_error('Create new access denied log failed with parameters: ' . $userid . '--' . $page . '--' . implode('--', $settings));
        }
        $settings['id'] = $sql->insert_id;
        $sql->close();
        return $settings;
    }
}
