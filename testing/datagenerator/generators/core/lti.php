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

/**
 * Generates ExamSys lti.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class lti extends generator
{

    /**
     * Create a new lti key
     *
     * @param array parameters
     *  string parameters[name]
     * options are (
     *   oauth_consumer_key, secret, contextid)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_key($parameters)
    {
        if (empty($parameters['name'])) {
            throw new data_error('name must be provided');
        }
        $defaults = array(
            'oauth_consumer_key' => 'test', 'secret' => 'testsecret', 'contextid' => null, 'deleted' => false);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        $now = date('Y-m-d H:i:s');
        if ($settings['deleted']) {
            $settings['deleted'] = $now;
        } else {
            $settings['deleted'] = null;
        }
        $sql = $this->db->prepare('INSERT INTO lti_keys VALUES (NULL, ?, ?, ?, ?, ?, NOW())');

        $sql->bind_param('sssss', $settings['oauth_consumer_key'], $settings['secret'], $parameters['name'], $settings['contextid'], $settings['deleted']);

        if (!$sql->execute()) {
            throw new data_error('Create new lti key failed with parameters: ' . $parameters['name'] . '--' . implode('--', $settings));
        }
        $settings['id'] = $sql->insert_id;
        $settings['name'] = $parameters['name'];
        $sql->close();
        return $settings;
    }

    /**
     * Create a new lti context
     *
     * @param array parameters
     *  string parameters[oauth_consumer_key]
     *  string parameters[externalid]
     *  string parameters[internalid]
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_context($parameters)
    {
        if (empty($parameters['oauth_consumer_key'])) {
            throw new data_error('oauth_consumer_key must be provided');
        }
        if (empty($parameters['externalid'])) {
            throw new data_error('externalid must be provided');
        }
        if (empty($parameters['internalid'])) {
            throw new data_error('internalid must be provided');
        }
        $sql = $this->db->prepare('INSERT INTO lti_context VALUES (?, ?, NOW())');
        $key = $parameters['oauth_consumer_key'] . ':' . $parameters['externalid'];
        $internalid = $parameters['internalid'];
        $sql->bind_param('si', $key, $internalid);
        if (!$sql->execute()) {
            throw new data_error('Create new lti context failed with parameters: ' . $key . '--' . $internalid);
        }
        $values['lti_context_key'] = $key;
        $values['c_internal_id'] = $internalid;
        $sql->close();
        return $values;
    }

    /**
     * Create a new lti user
     *
     * @param array parameters
     *  string parameters[oauth_consumer_key]
     *  string parameters[externalid]
     *  string parameters[internalid]
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_user($parameters)
    {
        if (empty($parameters['oauth_consumer_key'])) {
            throw new data_error('oauth_consumer_key must be provided');
        }
        if (empty($parameters['externalid'])) {
            throw new data_error('externalid must be provided');
        }
        if (empty($parameters['internalid'])) {
            throw new data_error('internalid must be provided');
        }
        $sql = $this->db->prepare('INSERT INTO lti_user VALUES (?, ?, NOW())');
        $key = $parameters['oauth_consumer_key'] . ':' . $parameters['externalid'];
        $internalid = $parameters['internalid'];
        $sql->bind_param('si', $key, $internalid);
        if (!$sql->execute()) {
            throw new data_error('Create new lti context failed with parameters: ' . $key . '--' . $internalid);
        }
        $values['lti_user_key'] = $key;
        $values['lti_user_equ'] = $internalid;
        $sql->close();
        return $values;
    }

    /**
     * Create a new lti resource
     *
     * @param array parameters
     *  string parameters[oauth_consumer_key]
     *  string parameters[externalid]
     *  string parameters[internalid]
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_resource($parameters)
    {
        if (empty($parameters['oauth_consumer_key'])) {
            throw new data_error('oauth_consumer_key must be provided');
        }
        if (empty($parameters['externalid'])) {
            throw new data_error('externalid must be provided');
        }
        if (empty($parameters['internalid'])) {
            throw new data_error('internalid must be provided');
        }
        if (empty($parameters['internaltype'])) {
            throw new data_error('internaltype must be provided');
        }
        $sql = $this->db->prepare('INSERT INTO lti_resource VALUES (?, ?, ?, NOW())');
        $key = $parameters['oauth_consumer_key'] . ':' . $parameters['externalid'];
        $internalid = $parameters['internalid'];
        $internaltype = $parameters['internaltype'];
        $sql->bind_param('sss', $key, $internalid, $internaltype);
        if (!$sql->execute()) {
            throw new data_error('Create new lti context failed with parameters: ' . $key . '--' . $internalid . '--' . $internaltype);
        }
        $values['lti_resource_key'] = $key;
        $values['internal_id'] = $internalid;
        $values['internal_type'] = $internaltype;
        $sql->close();
        return $values;
    }
}
