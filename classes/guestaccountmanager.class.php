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
 * Class for managing guest accounts.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 * @package core
 */
class GuestAccountManager
{
    /** The reservation time in minutes. */
    protected const RESERVATION_TIME = '30';

    /**
     * Checks that a reservartion is still valid to have new details assigned to it.
     *
     * @param int $reservationid
     * @return bool
     */
    public static function isReservationValid(int $reservationid): bool
    {
        // Check the reservation is still valid.
        $minutes = static::RESERVATION_TIME;
        $sql = "SELECT id FROM temp_users WHERE id = ? AND reserved > DATE_SUB(NOW(), INTERVAL {$minutes} MINUTE) AND surname IS NULL";
        $stmt = Config::get_instance()->db->prepare($sql);
        $stmt->bind_param('s', $reservationid);
        $stmt->execute();
        $stmt->bind_result($has_record);
        $stmt->fetch();
        $stmt->close();
        return !is_null($has_record);
    }

    /**
     * Sets the details on a guest account reservation to finalise it.
     *
     * @param string $firstnames
     * @param string $surname
     * @param string $idnumber
     * @param string $title
     * @param int $reservationid
     * @return void
     */
    public static function setDetails(string $firstnames, string $surname, string $idnumber, string $title, int $reservationid)
    {
        $sql = 'UPDATE temp_users SET first_names = ?, surname = ?, title = ?, student_id = ? WHERE id = ?';
        $stmt = Config::get_instance()->db->prepare($sql);
        $stmt->bind_param('ssssi', $firstnames, $surname, $title, $idnumber, $reservationid);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Gets all the records for reserved guest accounts.
     *
     * The key of the return array is the username of the guest account.
     *
     * The value of the array is 0 if it is in use or reserved, if the reservation has expired
     * it will be the id of the reservation record.
     *
     * @return array
     */
    protected static function getUsedAccounts(): array
    {
        $used_accounts = [];
        $minutes = static::RESERVATION_TIME;
        $sql = "SELECT assigned_account, IF((reserved > DATE_SUB(NOW(), INTERVAL {$minutes} MINUTE) OR surname IS NOT NULL), 0, id) 
                FROM temp_users";
        $results = Config::get_instance()->db->prepare($sql);
        $results->execute();
        $results->bind_result($assigned_account, $reserved);
        while ($results->fetch()) {
            $used_accounts[$assigned_account] = $reserved;
        }
        $results->close();

        return $used_accounts;
    }

    /**
     * Creates a guest account reservation, that will last for 30 minutes.
     *
     * @return GuestAccount
     * @throws NoFreeGuestAccounts
     */
    public static function reserve(): GuestAccount
    {
        $account = new GuestAccount();
        $used_accounts = static::getUsedAccounts();

        for ($i = 1; $i <= 100; $i++) {
            if (!isset($used_accounts['user' . $i]) or $used_accounts['user' . $i] != 0) {
                $account->username = 'user' . $i;
                break;
            }
        }

        if (empty($account->username)) {
            throw new NoFreeGuestAccounts();
        }

        // Reserve this free account first.
        if (!isset($used_accounts[$account->username])) {
            $sql = 'INSERT INTO temp_users VALUES (NULL, NULL, NULL, NULL, NULL, ?, NOW())';
            $stmt = Config::get_instance()->db->prepare($sql);
            $stmt->bind_param('s', $account->username);
            $stmt->execute();
            $stmt->close();
            $account->reservationid = Config::get_instance()->db->insert_id;
        } else {
            // Reuse an expired reservation.
            $sql = 'UPDATE temp_users SET reserved = NOW() WHERE id = ?';
            $stmt = Config::get_instance()->db->prepare($sql);
            $stmt->bind_param('s', $used_accounts[$account->username]);
            $stmt->execute();
            $stmt->close();
            $account->reservationid = $used_accounts[$account->username];
        }

        // Get the user ID.
        $stmt = Config::get_instance()->db->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->bind_param('s', $account->username);
        $stmt->execute();
        $stmt->bind_result($userid);
        $stmt->fetch();
        $stmt->close();

        // Reset password on the chosen guest account.
        $color = array('blue', 'green', 'orange', 'gold', 'silver', 'purple', 'white', 'black', 'yellow');
        $max = count($color) - 1;
        $account->password = $color[rand(0, $max)] . rand(10, 99);
        UserUtils::update_password($account->username, $account->password, $userid, Config::get_instance()->db);

        return $account;
    }
}
