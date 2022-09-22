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
 * Generates ExamSys logs.
 *
 * @author Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class log extends generator
{

    /**
     * Create metadata log
     *
     * @param array parameters
     * options are (userID, paperID, started, ipaddress, student_grade, year, attempt, completed, lab_name, highest_screen)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_metadata($parameters)
    {
        if (empty($parameters['userID'])) {
            throw new data_error('userID must be provided');
        }
        if (empty($parameters['paperID'])) {
            throw new data_error('paperID must be provided');
        }

        $user = \UserUtils::get_full_details_by_ID($parameters['userID'], \Config::get_instance()->db);
        $defaults = array(
            'started' => null,
            'ipaddress' => null,
            'student_grade' => $user['course'],
            'year' => null,
            'attempt' => null,
            'completed' => null,
            'lab_name' => null,
            'highest_screen' => null,
            'userID' => $parameters['userID'],
            'paperID' => $parameters['paperID']
        );
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        $sql = $this->db->prepare('INSERT INTO log_metadata VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $sql->bind_param(
            'iisssiissi',
            $settings['userID'],
            $settings['paperID'],
            $settings['started'],
            $settings['ipaddress'],
            $settings['student_grade'],
            $settings['year'],
            $settings['attempt'],
            $settings['completed'],
            $settings['lab_name'],
            $settings['highest_screen']
        );
        if (!$sql->execute()) {
            throw new data_error('Create new metadata log failed with parameters: ' . implode('--', $settings));
        }
        $settings['id'] = $sql->insert_id;
        $sql->close();
        return $settings;
    }

    /**
     * Create osce overall log
     *
     * @param array parameters
     * options are (userID, started, q_paper, overall_rating, numeric_score, feedback, student_grade, examinerID, osce_type, year)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_osceoverall($parameters)
    {
        if (empty($parameters['userID'])) {
            throw new data_error('userID must be provided');
        }
        if (empty($parameters['q_paper'])) {
            throw new data_error('q_paper must be provided');
        }
        $defaults = array('started' => null, 'overall_rating' => null, 'numeric_score' => null,
            'feedback' => null, 'student_grade' => null, 'examinerID' => null, 'osce_type' => null, 'year' => null,
            'userID' => $parameters['userID'], 'q_paper' => $parameters['q_paper']);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        $sql = $this->db->prepare('INSERT INTO log4_overall VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $sql->bind_param(
            'isisissisi',
            $settings['userID'],
            $settings['started'],
            $settings['q_paper'],
            $settings['overall_rating'],
            $settings['numeric_score'],
            $settings['feedback'],
            $settings['student_grade'],
            $settings['examinerID'],
            $settings['osce_type'],
            $settings['year']
        );
        if (!$sql->execute()) {
            throw new data_error('Create new osce overall log failed with parameters: ' . implode('--', $settings));
        }
        $settings['id'] = $sql->insert_id;
        $sql->close();
        return $settings;
    }

    /**
     * Create osce log
     * @param array parameters
     *  string parameters[q_id]
     *  string parameters[log4_overallID]
     * options are (rating, q_parts)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_osce($parameters)
    {
        if (empty($parameters['log4_overallID'])) {
            throw new data_error('log4_overallID must be provided');
        }
        if (empty($parameters['q_id'])) {
            throw new data_error('q_id must be provided');
        }
        $defaults = array('rating' => null, 'q_parts' => null, 'log4_overallID' => $parameters['log4_overallID'], 'q_id' => $parameters['q_id']);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        $sql = $this->db->prepare('INSERT INTO log4 VALUES (NULL, ?, ?, ?, ?)');
        $sql->bind_param('issi', $settings['q_id'], $settings['rating'], $settings['q_parts'], $settings['log4_overallID']);
        if (! $sql->execute()) {
            throw new data_error('Create new osce log failed with parameters: ' . implode('--', $settings));
        }
        $settings['id'] = $sql->insert_id;
        $sql->close();
        return $settings;
    }

    /**
     * Create offline log
     * @param array parameters
     *  string parameters[q_id]
     *  string parameters[metadataID]
     * options are (mark, adjmark, totalpos)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_offline($parameters)
    {
        if (empty($parameters['metadataID'])) {
            throw new data_error('metadataID must be provided');
        }
        if (empty($parameters['q_id'])) {
            throw new data_error('q_id must be provided');
        }
        $defaults = array('mark' => null, 'adjmark' => null, 'totalpos' => null, 'metadataID' => $parameters['metadataID'],
            'q_id' => $parameters['q_id']);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        $sql = $this->db->prepare('INSERT INTO log5 VALUES (NULL, ?, ?, ?, ?, ?)');
        $sql->bind_param('iddii', $settings['q_id'], $settings['mark'], $settings['adjmark'], $settings['totalpos'], $settings['metadataID']);
        if (!$sql->execute()) {
            throw new data_error('Create new offline log failed with parameters: ' . implode('--', $settings));
        }
        $settings['id'] = $sql->insert_id;
        $sql->close();
        return $settings;
    }

    /**
     * Create peer review log
     * @param array parameters
     *  string parameters[q_id]
     * options are (paperID, reviewerID, peerID, started, rating)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_peerreview($parameters)
    {
        if (empty($parameters['q_id'])) {
            throw new data_error('q_id must be provided');
        }
        $defaults = array('paperID' => null, 'reviewerID' => null, 'peerID' => null, 'started' => null, 'rating' => null,
            'q_id' => $parameters['q_id']);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        $sql = $this->db->prepare('INSERT INTO log6 VALUES (NULL, ?, ?, ?, ?, ?, ?)');
        $sql->bind_param('iiisii', $settings['paperID'], $settings['reviewerID'], $settings['peerID'], $settings['started'], $settings['q_id'], $settings['rating']);
        if (!$sql->execute()) {
            throw new data_error('Create new offline log failed with parameters: ' . implode('--', $settings));
        }
        $settings['id'] = $sql->insert_id;
        $sql->close();
        return $settings;
    }

    /**
     * Create late log
     *
     * @param array parameters
     *  string parameters[q_id]
     *  string parameters[metadataID]
     * options are (paperID, started, ipaddress, student_grade, year, attempt, completed, lab_name, highest_screen)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_late($parameters)
    {
        return $this->create_exam('_late', $parameters);
    }

    /**
     * Create sumamtive exam log
     *
     * @param array parameters
     *  string parameters[q_id]
     *  string parameters[metadataID]
     * options are (paperID, started, ipaddress, student_grade, year, attempt, completed, lab_name, highest_screen)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_summative($parameters)
    {
        return $this->create_exam(\assessment::TYPE_SUMMATIVE, $parameters);
    }

    /**
     * Create formative exam log
     *
     * @param array parameters
     *  string parameters[q_id]
     *  string parameters[metadataID]
     * options are (paperID, started, ipaddress, student_grade, year, attempt, completed, lab_name, highest_screen)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_formative($parameters)
    {
        return $this->create_exam(\assessment::TYPE_FORMATIVE, $parameters);
    }

    /**
     * Create progress exam log
     *
     * @param array parameters
     *  string parameters[q_id]
     *  string parameters[metadataID]
     * options are (paperID, started, ipaddress, student_grade, year, attempt, completed, lab_name, highest_screen)
     * @throws data_error If passed parameter is invalid
     * @return integer
     */
    public function create_progress($parameters)
    {
        return $this->create_exam(\assessment::TYPE_PROGRESS, $parameters);
    }

    /**
     * Create survey log
     *
     * @param array parameters
     *  string parameters[q_id]
     *  string parameters[metadataID]
     * options are (paperID, started, ipaddress, student_grade, year, attempt, completed, lab_name, highest_screen)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_survey($parameters)
    {
        return $this->create_exam(\assessment::TYPE_SURVEY, $parameters);
    }

    /**
     * Create exam log
     * @param integer type type of exam
     * @param array parameters
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_exam($type, $parameters)
    {
        if (!isset($type)) {
            throw new data_error('type must be provided');
        }
        if (empty($parameters['metadataID'])) {
            throw new data_error('metadataID must be provided');
        }
        if (empty($parameters['q_id'])) {
            throw new data_error('q_id must be provided');
        }
        $defaults = array(
            'mark' => null,
            'adjmark' => null,
            'totalpos' => null,
            'user_answer' => null,
            'errorstate' => 0,
            'screen' => null,
            'duration' => null,
            'updated' => null,
            'dismiss' => null,
            'option_order' => null
        );
        $settings = $this->set_defaults_and_clean($defaults, $parameters);

        $sql = $this->db->prepare('INSERT INTO log' . $type . ' VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $sql->bind_param(
            'iddisiiisssi',
            $parameters['q_id'],
            $settings['mark'],
            $settings['adjmark'],
            $settings['totalpos'],
            $settings['user_answer'],
            $settings['errorstate'],
            $settings['screen'],
            $settings['duration'],
            $settings['updated'],
            $settings['dismiss'],
            $parameters['option_order'],
            $parameters['metadataID']
        );
        if (!$sql->execute()) {
            throw new data_error('Create new paper log failed with parameters: ' . $type . '--' . $parameters['q_id'] . '--' . $parameters['metadataID'] . '--' . implode('--', $settings));
        }
        $settings['id'] = $sql->insert_id;
        $sql->close();
        return $settings;
    }
}
