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

use testing\unittest\unittestdatabase;

/**
 * Tests the guest account manager class.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 * @package tests
 *
 * @group guest_account
 */
class guestaccountmanagertest extends unittestdatabase
{
    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
    }

    /**
     * Tests that guest accounts can be reserved and that accounts are correctly reserved.
     */
    public function testReserve()
    {
        $account = GuestAccountManager::reserve();
        $this->assertInstanceOf(GuestAccount::class, $account);
        $this->assertEquals('user1', $account->username);

        $account2 = GuestAccountManager::reserve();
        $this->assertInstanceOf(GuestAccount::class, $account2);
        $this->assertEquals('user2', $account2->username);

        $this->assertNotEquals($account->reservationid, $account2->reservationid);
    }

    /**
     * Tests that expired reservations are reused.
     */
    public function testReserveExpiredReservations()
    {
        $datagenerator = $this->get_datagenerator('guest_account_reservation');
        $hourago = time() - 3600;
        // Fully reserved user.
        $datagenerator->createReservation(['assigned_account' => 'user1', 'reserved' => $hourago, 'surname' => 'Bob']);
        // Reservation not complete.
        $datagenerator->createReservation(['assigned_account' => 'user2', 'reserved' => $hourago]);
        $account = GuestAccountManager::reserve();

        $this->assertInstanceOf(GuestAccount::class, $account);
        $this->assertEquals('user2', $account->username);
    }

    /**
     * Tests that we get an exception when all the accounts are reserved.
     */
    public function testReserveNoneFree()
    {
        $this->expectException(NoFreeGuestAccounts::class);
        for ($i = 0; $i < 1000; $i++) {
            // Loop through was more reservations than we currently have avaliable.
            // We need to limit the number of tries to ensure we do not get an infinite loop!
            GuestAccountManager::reserve();
        }
    }

    /**
     * Tests that valid reservations are detected correctly.
     *
     * @dataProvider dataIsReservationValid
     * @param array $reservation
     * @param $expected
     */
    public function testIsReservationValid(array $reservation, $expected)
    {
        $datagenerator = $this->get_datagenerator('guest_account_reservation');
        $record = $datagenerator->createReservation($reservation);
        $this->assertEquals($expected, GuestAccountManager::isReservationValid($record['id']));
    }

    /**
     * Data for testIsReservationValid.
     *
     * @return array
     */
    public function dataIsReservationValid(): array
    {
        $validtime = time() - 60;
        $expiredimte = time() - 3600;
        return [
            'valid' => [['assigned_account' => 'user1', 'reserved' => $validtime], true],
            'recent complete' => [['assigned_account' => 'user1', 'reserved' => $validtime, 'surname' => 'Jones'], false],
            'expired' => [['assigned_account' => 'user1', 'reserved' => $expiredimte], false],
            'older complete' => [['assigned_account' => 'user1', 'reserved' => $expiredimte, 'surname' => 'Jones'], false],
        ];
    }

    /**
     * Tests that the details of the reservation are saved correctly.
     */
    public function testSetDetails()
    {
        $datagenerator = $this->get_datagenerator('guest_account_reservation');
        $record = $datagenerator->createReservation(['assigned_account' => 'user1', 'reserved' => time()]);
        GuestAccountManager::setDetails('Jessica', 'Jones', '123456789', 'Ms', $record['id']);
        $query = [
            'table' => 'temp_users',
            'where' => [
                ['column' => 'id', 'value' => $record['id']],
            ],
        ];
        $result = $this->query($query);
        $this->assertCount(1, $result);
        $this->assertEquals('Jessica', $result[0]['first_names']);
        $this->assertEquals('Jones', $result[0]['surname']);
        $this->assertEquals('Ms', $result[0]['title']);
        $this->assertEquals('123456789', $result[0]['student_id']);
    }
}
