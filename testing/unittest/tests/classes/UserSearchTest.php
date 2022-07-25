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
 * Tests the SQLFragment class.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 * @package tests
 * @group core
 * @group search
 */
class UserSearchTest extends testing\unittest\unittestdatabase
{
    /** @var array Admin user. */
    protected $admin2;

    /** @var array External examiner user. */
    protected $external;

    /** @var array Graduate user. */
    protected $graduate;

    /** @var array Inactive staff user. */
    protected $inactive;

    /** @var array Internal examiner user. */
    protected $internal;

    /** @var array Invigilator user. */
    protected $invigilator;

    /** @var array User who has left. */
    protected $leaver;

    /** @var array Locked user. */
    protected $locked;

    /** @var array A second module with users enrolled on it. */
    protected $othermodule;

    /** @var array Staff user. */
    protected $staff;

    /** @var array Standard setter user. */
    protected $standard;

    /** @var array Student user, enrolled on a module in 2012. */
    protected $student1;

    /** @var array Student user, enrolled on a module in 2013.. */
    protected $student2;

    /** @var array Suspended user. */
    protected $suspended;

    /**
     * @see testing\unittest\unittestdatabase::datageneration()
     */
    public function datageneration(): void
    {
        // Create some academic years.
        $yeargen = $this->get_datagenerator('academic_year', 'core');
        $yeargen->create_academic_year(['calendar_year' => 2012, 'academic_year' => '2012/13']);
        $yeargen->create_academic_year(['calendar_year' => 2013, 'academic_year' => '2013/14']);

        // Create some students.
        $usergen = $this->get_datagenerator('users', 'core');
        $student1 = [
            'first_names' => 'Amanda',
            'surname' => 'Cox',
            'initials' => 'A',
            'title' => 'Miss',
            'username' => 'student1',
            'roles' => 'Student',
            'sid' => '12345678901',
        ];
        $this->student1 = $usergen->create_user($student1);
        $student2 = [
                'first_names' => 'Bob',
                'surname' => 'Coxley',
                'initials' => 'B',
                'title' => 'Mr',
                'username' => 'student2',
                'roles' => 'Student',
                'sid' => '0987654321',
        ];
        $this->student2 = $usergen->create_user($student2);

        $this->graduate = $usergen->create_user(['roles' => 'graduate', 'sid' => '26572525725']);
        $this->leaver = $usergen->create_user(['roles' => 'left', 'grade' => 'left', 'sid' => '827414657']);
        $this->locked = $usergen->create_user(['roles' => 'Locked', 'sid' => '1157276176']);
        $this->suspended = $usergen->create_user(['roles' => 'Suspended', 'sid' => '155727256']);

        $this->admin2 = $usergen->create_user(['roles' => 'Staff,Admin']);
        $this->external = $usergen->create_user(['roles' => 'External Examiner']);
        $this->inactive = $usergen->create_user(['roles' => 'Inactive Staff']);
        $this->internal = $usergen->create_user(['roles' => 'Internal Reviewer']);
        $this->invigilator = $usergen->create_user(['roles' => 'Invigilator']);
        $this->staff = $usergen->create_user(
            [
                'first_names' => 'Kelly',
                'surname' => 'Shelly',
                'initials' => 'K',
                'title' => 'Mx',
                'username' => 'staff1',
                'roles' => 'Staff'
            ]
        );
        $this->standard = $usergen->create_user(['roles' => 'Staff,Standards Setter']);

        // Enrol the students on a module.
        $modgen = $this->get_datagenerator('modules', 'core');
        $modgen->create_enrolment(['userid' => $this->student1['id'], 'moduleid' => $this->module, 'calendar_year' => 2012]);
        $modgen->create_enrolment(['userid' => $this->student2['id'], 'moduleid' => $this->module, 'calendar_year' => 2013]);
        $this->othermodule = $modgen->create_module(['moduleid' => 'ROLETEST', 'fullname' => 'Other module']);
        $modgen->create_enrolment(['userid' => $this->student1['id'], 'moduleid' => $this->othermodule['id'], 'calendar_year' => 2012]);

        // Staff in a team.
        $modgen->create_module_team(['moduleid' => $this->othermodule['moduleid'], 'username' => $this->staff['username']]);
        $othermodule2 = $modgen->create_module(['moduleid' => 'ROLETEST2', 'fullname' => 'Yet Another module']);
        $modgen->create_module_team(['moduleid' => $othermodule2['moduleid'], 'username' => $this->staff['username']]);
    }

    /**
     * Tests that searches that should return Amanda Cox.
     *
     * @param string $name
     * @dataProvider dataNameSearch
     */
    public function testNameSearch(string $name)
    {
        $search = new UserSearch();
        $search->setSearchStudents();
        $search->setName($name);

        $result = $search->execute();
        self::assertInstanceOf(SearchResult::class, $result);
        self::assertEquals(1, $result->total);

        $result->query->bind_result(
            $id,
            $roles,
            $sid,
            $surname,
            $initials,
            $first_names,
            $title,
            $username,
            $grade,
            $yearofstudy,
            $email,
            $special_id
        );
        $result->query->fetch();

        self::assertEquals('student1', $username);
        self::assertEquals('Amanda', $first_names);
        self::assertEquals('Cox', $surname);
    }

    /**
     * Data for the name search test.
     *
     * @return array
     */
    public function dataNameSearch(): array
    {
        return [
            'forename' => ['Amanda'],
            'surname' => ['Cox'],
            'surname initial' => ['Cox, A'],
            'title surname' => ['Miss Cox'],
            'wildcard' => ['Amand*'],
            'title surname initial' => ['Miss Cox, A'],
            'inital surname' => ['A, Cox'],
        ];
    }

    /**
     * Test that search finds people by student ID.
     */
    public function testSIDSearch()
    {
        $search = new UserSearch();
        $search->setSearchStudents();
        $search->setIDNumber('12345678901');

        $result = $search->execute();
        self::assertInstanceOf(SearchResult::class, $result);
        self::assertEquals(1, $result->total);

        $result->query->bind_result(
            $id,
            $roles,
            $sid,
            $surname,
            $initials,
            $first_names,
            $title,
            $username,
            $grade,
            $yearofstudy,
            $email,
            $special_id
        );
        $result->query->fetch();

        self::assertEquals('student1', $username);
        self::assertEquals('Amanda', $first_names);
        self::assertEquals('Cox', $surname);
    }

    /**
     * Tests that a username search returns the correct person.
     */
    public function testUsernameSearch()
    {
        $search = new UserSearch();
        $search->setSearchStudents();
        $search->setUsername('student1');

        $result = $search->execute();
        self::assertInstanceOf(SearchResult::class, $result);
        self::assertEquals(1, $result->total);

        $result->query->bind_result(
            $id,
            $roles,
            $sid,
            $surname,
            $initials,
            $first_names,
            $title,
            $username,
            $grade,
            $yearofstudy,
            $email,
            $special_id
        );
        $result->query->fetch();

        self::assertEquals('student1', $username);
        self::assertEquals('Amanda', $first_names);
        self::assertEquals('Cox', $surname);
    }

    /**
     * Tests that users on a module are found.
     */
    public function testModuleSearch()
    {
        $search = new UserSearch();
        $search->setSearchStudents();
        $search->setSearchSysAdmin();
        $search->setModule($this->module);

        $result = $search->execute();
        self::assertInstanceOf(SearchResult::class, $result);
        self::assertEquals(2, $result->total);

        $result->query->bind_result(
            $id,
            $roles,
            $sid,
            $surname,
            $initials,
            $first_names,
            $title,
            $username,
            $grade,
            $yearofstudy,
            $email,
            $special_id
        );

        $result->query->fetch();
        self::assertEquals('student1', $username);
        self::assertEquals('Amanda', $first_names);
        self::assertEquals('Cox', $surname);

        $result->query->fetch();
        self::assertEquals('student2', $username);
        self::assertEquals('Bob', $first_names);
        self::assertEquals('Coxley', $surname);
    }

    /**
     * @return void
     */
    public function testModuleSearchIncludingStaff()
    {
        $search = new UserSearch();
        $search->setSearchStudents();
        $search->setSearchStaff();
        $search->setModule($this->othermodule['id']);

        $result = $search->execute();
        self::assertInstanceOf(SearchResult::class, $result);
        self::assertEquals(2, $result->total);

        $result->query->bind_result(
            $id,
            $roles,
            $sid,
            $surname,
            $initials,
            $first_names,
            $title,
            $username,
            $grade,
            $yearofstudy,
            $email,
            $special_id
        );

        $result->query->fetch();
        self::assertEquals('student1', $username);
        self::assertEquals('Amanda', $first_names);
        self::assertEquals('Cox', $surname);

        $result->query->fetch();
        self::assertEquals('staff1', $username);
        self::assertEquals('Kelly', $first_names);
        self::assertEquals('Shelly', $surname);
    }

    /**
     * Tests that a year search returns students enrolled on that year.
     */
    public function testYearSearch()
    {
        $search = new UserSearch();
        $search->setSearchStudents();
        $search->setYear(2012);

        $result = $search->execute();
        self::assertInstanceOf(SearchResult::class, $result);
        self::assertEquals(1, $result->total);

        $result->query->bind_result(
            $id,
            $roles,
            $sid,
            $surname,
            $initials,
            $first_names,
            $title,
            $username,
            $grade,
            $yearofstudy,
            $email,
            $special_id
        );
        $result->query->fetch();

        self::assertEquals('student1', $username);
        self::assertEquals('Amanda', $first_names);
        self::assertEquals('Cox', $surname);
    }

    /**
     * Tests that year and module interact correctly.
     */
    public function testModuleYearSearch()
    {
        $search = new UserSearch();
        $search->setSearchStudents();
        $search->setYear(2013);
        $search->setModule($this->module);

        $result = $search->execute();
        self::assertInstanceOf(SearchResult::class, $result);
        self::assertEquals(1, $result->total);

        $result->query->bind_result(
            $id,
            $roles,
            $sid,
            $surname,
            $initials,
            $first_names,
            $title,
            $username,
            $grade,
            $yearofstudy,
            $email,
            $special_id
        );
        $result->query->fetch();

        self::assertEquals('student2', $username);
        self::assertEquals('Bob', $first_names);
        self::assertEquals('Coxley', $surname);
    }

    /**
     * Tests the role search.
     *
     * @param array $role_methods The role filter method to trigger.
     * @param array $expected The property names storing the expected users.
     *
     * @dataProvider dataRoleSearch
     */
    public function testRoleSearch(array $role_methods, array $expected)
    {
        $search = new UserSearch();
        foreach ($role_methods as $role_method) {
            $search->$role_method();
        }
        // We need to fix the order that users are returned in
        // which will not happen with the default ordering and the way we are creating users.
        $search->setOrder('users.id', 'ASC');

        $result = $search->execute();
        self::assertInstanceOf(SearchResult::class, $result);
        self::assertEquals(count($expected), $result->total);

        $result->query->bind_result(
            $id,
            $roles,
            $sid,
            $surname,
            $initials,
            $first_names,
            $title,
            $username,
            $grade,
            $yearofstudy,
            $email,
            $special_id
        );

        foreach ($expected as $user) {
            $result->query->fetch();
            self::assertEquals($this->$user['username'], $username);
            self::assertEquals($this->$user['first_names'], $first_names);
            self::assertEquals($this->$user['surname'], $surname);
        }
    }

    /**
     * Data for testRoleSearch.
     *
     * @return array
     */
    public function dataRoleSearch(): array
    {
        return [
            'Admin' => [['setSearchAdmin'], ['admin2']],
            'External' => [['setSearchExternal'], ['external']],
            'Graduates' => [['setSearchGraduates'], ['graduate']],
            'Inactive' => [['setSearchInactive'], ['inactive']],
            'Internal' => [['setSearchInternal'], ['internal']],
            'Invigilators' => [['setSearchInvigilators'], ['invigilator']],
            'Leavers' => [['setSearchLeavers'], ['leaver']],
            'Locked' => [['setSearchLocked'], ['locked']],
            'Staff' => [['setSearchStaff'], ['admin', 'admin2', 'staff', 'standard']],
            'Standard' => [['setSearchStandard'], ['standard']],
            'Students' => [['setSearchStudents'], ['student', 'studentneeds', 'student1', 'student2']],
            'Suspended' => [['setSearchSuspended'], ['suspended']],
            'SysAdmin' => [['setSearchSysadmin'], ['admin']],
            'SysAdmin, Invigilators' => [['setSearchSysadmin', 'setSearchInvigilators'], ['admin', 'invigilator']],
        ];
    }

    /**
     * Tests that title search works correctly.
     *
     * @param string $name
     *
     * @dataProvider dataTitle
     */
    public function testTitle(string $name)
    {
        $usergen = $this->get_datagenerator('users', 'core');
        $student = [
            'first_names' => 'Profiterole',
            'surname' => 'TakeMiss',
            'initials' => 'P',
            'title' => 'Miss',
            'roles' => 'Student',
            'sid' => '567945614',
        ];
        $student = $usergen->create_user($student);

        $search = new UserSearch();
        $search->setSearchStudents();
        $search->setName($name);

        $result = $search->execute();
        self::assertInstanceOf(SearchResult::class, $result);
        self::assertEquals(1, $result->total);

        $result->query->bind_result(
            $id,
            $roles,
            $sid,
            $surname,
            $initials,
            $first_names,
            $title,
            $username,
            $grade,
            $yearofstudy,
            $email,
            $special_id
        );
        $result->query->fetch();

        self::assertEquals($student['username'], $username);
        self::assertEquals($student['first_names'], $first_names);
        self::assertEquals($student['surname'], $surname);
    }

    /**
     * Data for the Title test.
     *
     * @return array
     */
    public function dataTitle(): array
    {
        return [
            'Start' => ['Miss TakeMiss Profiterole'],
            'End' => ['TakeMiss Profiterole Miss'],
            'Middle' => ['TakeMiss Miss Profiterole'],
            'With initial' => ['P, Miss TakeMiss Profiterole'],
        ];
    }
}
