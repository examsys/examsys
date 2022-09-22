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
 * Generates ExamSys guest account reservations.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class guest_account_reservation extends generator
{
    /**
     * Creates a reservation for a guest account.
     *
     * Parameters:
     * - first_names string
     * - surname string
     * - title string
     * - student_id string
     * - assigned_account string The username of the account that is reserved (required)
     * - reserved string|int The time of reservation as either a UNIX timestamp, or date n the form: 'Y-m-d H:i:s'
     *
     * @param array|stadClass $parameters
     * @return array
     */
    public function createReservation($parameters): array
    {
        // If an object is passed convert it into an array.
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }

        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }

        if (!isset($parameters['assigned_account'])) {
            throw new data_error('The assigned account must be set');
        }

        if (isset($parameters['reserved']) and !is_numeric($parameters['reserved'])) {
            // Convert the human readable date into a timestamp.
            $date = new \DateTime($parameters['reserved']);
            $parameters['reserved'] = $date->getTimestamp();
        }

        $defaults = array(
                'first_names' => null,
                'surname' => null,
                'title' => null,
                'student_id' => null,
                'assigned_account' => null,
                'reserved' => time(),
        );
        $values = $this->set_defaults_and_clean($defaults, $parameters);

        $values['id'] = $this->insertReservation($values);
        return $values;
    }

    protected function insertReservation($values)
    {
        $sql = 'INSERT INTO temp_users '
            . '(first_names, surname, title, student_id, assigned_account, reserved)'
            . ' VALUES (?, ?, ?, ?, ?, FROM_UNIXTIME(?))';
        $query = $this->db->prepare($sql);
        $query->bind_param(
            'ssssss',
            $values['first_names'],
            $values['surname'],
            $values['title'],
            $values['student_id'],
            $values['assigned_account'],
            $values['reserved']
        );
        if (!$query->execute()) {
            // The user was not successfully inserted.
            throw new data_error("Reservation for {$values['assigned_account']} not inserted into database");
        }
        return $query->insert_id;
    }
}
