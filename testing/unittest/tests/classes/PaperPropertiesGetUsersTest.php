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
 * Test paperproperties class get_users method
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 onwards The University of Nottingham
 * @package tests
 */
class PaperPropertiesGetUsersTest extends testing\unittest\unittestdatabase
{
    /** @var array A student used the each test. */
    protected $student1;

    /** @var array A student used the each test. */
    protected $student2;

    /** @var array A student used the each test. */
    protected $student3;

    /** @var array A modules used in tests. */
    protected $module1;

    /** @var array A modules used in tests. */
    protected $module2;

    /**
     * The current academic session
     * @var integer $currentsession
     */
    protected $currentsession;

    /**
     * Generate common data for test.
     *
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $yearutils = new \yearutils($this->db);
        $this->currentsession = $yearutils->get_current_session();
        $usergen = $this->get_datagenerator('users', 'core');
        $this->student1 = $usergen->create_user(['roles' => 'Student', 'sid' => '24680', 'surname' => 'Smith']);
        $this->student2 = $usergen->create_user(['roles' => 'Student', 'sid' => '13579', 'surname' => 'Johnson']);
        $this->student3 = $usergen->create_user(['roles' => 'Student', 'sid' => '65478', 'surname' => 'Peterson']);
        $modgen = $this->get_datagenerator('modules', 'core');
        $this->module1 = $modgen->create_module(['fullname' => 'Module 1', 'moduleid' => 'MOD1']);
        $this->module2 = $modgen->create_module(['fullname' => 'Module 2', 'moduleid' => 'MOD2']);
        $modgen->create_enrolment(['moduleid' => $this->module1['id'], 'userid' => $this->student1['id']]);
        $modgen->create_enrolment(['moduleid' => $this->module1['id'], 'userid' => $this->student2['id']]);
        $modgen->create_enrolment(['moduleid' => $this->module2['id'], 'userid' => $this->student2['id']]);
        $modgen->create_enrolment(['moduleid' => $this->module2['id'], 'userid' => $this->student3['id']]);
    }

    /**
     * Tests users on a single module with a defined year.
     *
     * @group paper
     */
    public function test_basic()
    {
        $admin = $this->get_base_admin();
        $paperparams = [
        'papertitle' => 'Paper 1',
        'papertype' => \assessment::TYPE_FORMATIVE,
        'paperowner' => $admin['username'],
        'modulename' => [$this->module1['fullname']],
        'calendaryear' => $this->currentsession,
        ];
        $paper = $this->get_datagenerator('papers', 'core')->create_paper($paperparams);

        $property = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, '', false);
        $userlist = $property->get_users();
        self::assertInstanceOf(\users\UserList::class, $userlist);
        self::assertEquals(2, $userlist->length());

        $users = $userlist->get_all();
        // Students should be returned alphabetically by surname.
        self::assertEquals($this->student2['id'], $users[0]->id);
        self::assertEquals($this->student1['id'], $users[1]->id);
    }

    /**
     * Tests a paper on multiple courses with a defined year.
     *
     * Some students will be in more than one of the modules, they should only be returned a single time.
     *
     * @group paper
     */
    public function test_multiple_modules()
    {
        $admin = $this->get_base_admin();
        $paperparams = [
        'papertitle' => 'Paper 1',
        'papertype' => \assessment::TYPE_FORMATIVE,
        'paperowner' => $admin['username'],
        'modulename' => [$this->module1['fullname'], $this->module2['fullname']],
        'calendaryear' => $this->currentsession,
        ];
        $paper = $this->get_datagenerator('papers', 'core')->create_paper($paperparams);

        $property = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, '', false);
        $userlist = $property->get_users();
        self::assertInstanceOf(\users\UserList::class, $userlist);
        self::assertEquals(3, $userlist->length());

        $users = $userlist->get_all();
        // Students should be returned alphabetically by surname without duplication.
        self::assertEquals($this->student2['id'], $users[0]->id);
        self::assertEquals($this->student3['id'], $users[1]->id);
        self::assertEquals($this->student1['id'], $users[2]->id);
    }

    /**
     * Tests a paper that requires students with specific meta data.
     *
     * @group paper
     */
    public function test_metadata()
    {
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $oldyear = $datagenerator->create_academic_year(array('calendar_year' => 2017, 'academic_year' => '2017/18'));
        // Create a student with metadata.
        $usergen = $this->get_datagenerator('users', 'core');
        $student = $usergen->create_user(['roles' => 'Student', 'sid' => '64537', 'surname' => 'Appleton']);
        $modgen = $this->get_datagenerator('modules', 'core');
        $modgen->create_enrolment(['moduleid' => $this->module1['id'], 'userid' => $student['id']]);
        // Create the paper with meta data.
        $admin = $this->get_base_admin();
        $paperparams = [
        'papertitle' => 'Paper 1',
        'papertype' => \assessment::TYPE_FORMATIVE,
        'paperowner' => $admin['username'],
        'modulename' => [$this->module1['fullname']],
        'calendaryear' => $this->currentsession,
        ];
        $papergen = $this->get_datagenerator('papers', 'core');
        $paper = $papergen->create_paper($paperparams);
        $papergen->createSecurityMetadata($paper['id'], ['name' => 'Group', 'value' => 'Theta']);
        // Add metadata to a student on another module.
        $metadata = ['type' => 'Group', 'value' => 'Theta', 'calendar_year' => $paper['session']];
        $usergen->create_metadata($student['id'], $this->module1['id'], $metadata);
        // Student will have matching meta data on another module.
        $usergen->create_metadata($this->student2['id'], $this->module2['id'], $metadata);
        // Student will have matching meta data in another year.
        $othermetadata = ['type' => 'Group', 'value' => 'Theta', 'calendar_year' => $oldyear['calendar_year']];
        $usergen->create_metadata($this->student1['id'], $this->module1['id'], $othermetadata);

        $property = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, '', false);
        $userlist = $property->get_users();
        self::assertInstanceOf(\users\UserList::class, $userlist);
        self::assertEquals(1, $userlist->length());

        $users = $userlist->get_all();
        self::assertEquals($student['id'], $users[0]->id);
    }
}
