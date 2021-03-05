<?php

// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tests the Role class.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 * @package tests
 * @group core
 * @group users
 */
class RoleTest extends \testing\unittest\unittestdatabase
{
    /**
     * @inheritDoc
     */
    public function datageneration(): void
    {
        // Do nothing.
    }

    /**
     * Tests that the roles for the correct user are found.
     *
     * @param string $roles
     * @param array $expected
     *
     * @dataProvider dataGetUsersRoles
     */
    public function testGetUsersRoles(string $roles, array $expected): void
    {
        $usergen = $this->get_datagenerator('users', 'core');
        $user = $usergen->create_user(['roles' => $roles, 'sid' => '12345']);
        $usergen->create_user(['roles' => 'Staff,Student,Admin,SysCron', 'sid' => '54321']);

        self::assertEquals($expected, Role::getUsersRoles($user['id']));
    }

    /**
     * Data for testGetUsersRoles.
     *
     * @return array
     */
    public function dataGetUsersRoles(): array
    {
        return [
            'single role' => ['Student', [4 => 'Student']],
            'same grouping' => ['Staff,SysAdmin', [3 => 'Staff', 1 => 'SysAdmin']],
            'multiple groupings' => ['Student,Staff,Admin', [4 => 'Student', 3 => 'Staff', 2 => 'Admin']],
        ];
    }

    /**
     * Tests that roles are returned.
     */
    public function testList(): void
    {
        $roles = Role::list();
        self::assertNotEmpty($roles);

        // Pick the first role and check it is the correct class.
        $role = current($roles);
        self::assertInstanceOf(Role::class, $role);
    }

    /**
     * Tests that a user's roles are updated.
     *
     * @param string $initial The starting role of the user.
     * @param array $roles The roles that should be defined.
     *
     * @dataProvider dataUpdateRoles
     */
    public function testUpdateRoles(string $initial, array $roles): void
    {
        $usergen = $this->get_datagenerator('users', 'core');
        $user = $usergen->create_user(['roles' => $initial, 'sid' => '12345']);
        $otheruser = $usergen->create_user(['roles' => 'Staff,Student', 'sid' => '54321']);

        Role::updateRoles($user['id'], $roles);

        // Test that the user roles are correct.
        $details = Role::getUsersRoles($user['id']);
        self::assertCount(count($roles), $details);
        foreach ($roles as $role) {
            self::assertContains($role, $details, "Missing role: $role");
        }

        // Test that the other user has not been modified.
        $otherdetails = Role::getUsersRoles($otheruser['id']);
        self::assertCount(2, $otherdetails);
        self::assertContains('Staff', $otherdetails);
        self::assertContains('Student', $otherdetails);
    }

    /**
     * Data for testUpdateRoles.
     *
     * @return array
     */
    public function dataUpdateRoles(): array
    {
        return [
            'change all' => ['Staff', ['Student']],
            'add role' => ['Staff', ['Student', 'Staff']],
            'remove role' => ['Staff,SysAdmin', ['Staff']],
            'change some' => ['Staff,SysAdmin', ['Staff', 'Student']],
        ];
    }

    /**
     * Tests that invalid roles names are rejected.
     *
     * @throws \InvalidRole
     */
    public function testValidateRoleInvalid(): void
    {
        self::expectException(\InvalidRole::class);
        Role::validateRole('Made up role');
    }

    /**
     * Tests that invalid combinations are rejected.
     *
     * @param array $roles
     * @param string $message
     *
     * @dataProvider dataValidateCombinationInvalid
     */
    public function testValidateCombinationInvalid(array $roles, string $message): void
    {
        self::expectException(\InvalidRole::class);
        self::expectExceptionMessage($message);
        Role::validateCombination($roles);
    }

    /**
     * Data for testValidateCombinationInvalid.
     *
     * @return array
     */
    public function dataValidateCombinationInvalid(): array
    {
        return [
            'no roles' => [[], 'Users must have a role'],
            'invalid role' => [['Fake role'], "'Fake role' is an invalid role"],
            'different groups' => [['Student', 'graduate'], 'Incompatible role role combination'],
        ];
    }

    /**
     * Tests valid role combinations.
     *
     * @param array $roles
     * @dataProvider dataValidateCombinationValid
     */
    public function testValidateCombinationValid(array $roles): void
    {
        // Returns nothing when valid.
        self::assertNull(Role::validateCombination($roles));
    }

    /**
     * Data for testValidateCombinationValid.
     *
     * @return array
     */
    public function dataValidateCombinationValid(): array
    {
        return [
                'one role' => [['Student']],
                'roles in different groupings' => [['Student', 'Staff']],
                'roles in same group' => [['Staff', 'SysAdmin']],
                'mixed' => [['graduate', 'Staff', 'SysAdmin']],
        ];
    }

    /**
     * Tests that validation returns the correct id.
     *
     * @param string $role The role to validate
     * @param int $expected the id of the role
     *
     * @dataProvider dataValidateRoleValid
     */
    public function testValidateRoleValid(string $role, int $expected): void
    {
        self::assertEquals($expected, Role::validateRole($role));
    }

    /**
     * Data for testValidateRoleValid
     *
     * @return array
     */
    public function dataValidateRoleValid(): array
    {
        return [
                'sysadmin' => ['SysAdmin', 1],
                'student' => ['Student', 4],
                'external' => ['External Examiner', 11],
        ];
    }

    /**
     * Tests that the localised names of roles will be returned correctly.
     */
    public function testLocalName(): void
    {
        $role = new Role(5, 'graduate', 'student', 2);
        self::assertEquals('Graduate', $role->localName());
    }

    /**
     * Tests that the api roles are found.
     */
    public function testGetApiRoles(): void
    {
        $expected = array( 'graduate', 'Inactive Staff', 'left', 'Locked', 'Staff', 'Student', 'Suspended');
        self::assertEquals($expected, Role::getApiRoles());
    }
}
