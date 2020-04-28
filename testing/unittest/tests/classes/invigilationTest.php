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

use testing\unittest\unittestdatabase;

/**
 * Test invigilation class
 *
 * @author Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 * @package tests
 */
class InvigilationTest extends unittestdatabase
{
    /**
     * The test paper
     *
     * @var array $paper
     */
    protected $paper;

    /**
     * The test paper
     *
     * @var array $paper2
     */
    protected $paper2;

    /*
     * @var array Storage for user data in tests
     */
    private $user;

    /*
     * @var array Storage for pc data in tests
     */
    private $pc;

    /**
     * Generate common data for test.
     *
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2015, 'academic_year' => '2015/16'));
        $datagenerator = $this->get_datagenerator('labs', 'core');
        $datagenerator->create_campus(array('name' => 'Test Campus', 'isdefault' => 1));
        $lab = $datagenerator->create_lab(
            array(
                'name' => 'Test lab',
                'building' => 'Test building',
                'room' => 1
            )
        );
        $this->pc = $datagenerator->create_exam_pc(array('lab' => $lab['name']));
        $datagenerator = $this->get_datagenerator('users', 'core');
        $this->user = $datagenerator->create_user(
            array(
                'surname' => 'test3',
                'username' => 'unit3',
                'grade' => 'TEST2',
                'sid' => '141516171819',
                'special_needs' => array('breaks' => 'one an hour'),
            )
        );
        $datagenerator = $this->get_datagenerator('modules', 'core');
        $module3 = $datagenerator->create_module(
            array('fullname' => 'Test module 3', 'moduleid' => 'TEST3', 'timed_exams' => 1)
        );
        $datagenerator->create_enrolment(
            array('userid' => $this->user['id'], 'moduleid' => $module3['id'], 'calendar_year' => 2015)
        );
        $this->config->set_setting('cfg_summative_mgmt', false, \Config::BOOLEAN);
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $this->paper = $datagenerator->create_paper(
            array(
                'papertitle' => 'test summative',
                'bidirectional' => '1',
                'fullscreen' => '1',
                'paperowner' => 'admin',
                'papertype' => '2',
                'password' => 'EC1VbYJtOq8NsidA+q60rDEzjZZ8eHmHm6dEtfVBpeQ=',
                'modulename' => 'Test module 3',
                'remote' => 1,
                'calendaryear' => 2015,
                'duration' => 60
            )
        );
        $this->paper2 = $datagenerator->create_paper(
            array(
                'papertitle' => 'test summative 2',
                'bidirectional' => '1',
                'fullscreen' => '1',
                'paperowner' => 'admin',
                'papertype' => '2',
                'modulename' => 'Test module 3',
                'calendaryear' => 2015,
                'duration' => 60,
                'labs' => $lab['name']
            )
        );
        $datagenerator = $this->get_datagenerator('questions', 'core');
        $question = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'enhancedcalc',
                'leadin' => 'test'
            )
        );
        $datagenerator->add_question_to_paper(
            array(
                'paper' => $this->paper['id'],
                'question' => $question['id'],
                'screen' => 1,
                'displaypos' => 1
            )
        );
        $datagenerator->add_question_to_paper(
            array(
                'paper' => $this->paper2['id'],
                'question' => $question['id'],
                'screen' => 1,
                'displaypos' => 1
            )
        );
        $logdatagenerator = $this->get_datagenerator('log', 'core');
        $meta = $logdatagenerator->create_metadata(
            array(
                'userID' => $this->user['id'],
                'paperID' => $this->paper['id'],
                'year' => 1,
                'started' => '2015-01-01 09:00:00'
            )
        );
        $meta2 = $logdatagenerator->create_metadata(
            array(
                'userID' => $this->user['id'],
                'paperID' => $this->paper2['id'],
                'year' => 1,
                'started' => '2015-01-01 09:00:00'
            )
        );
        $logdatagenerator->create_summative(
            array(
                'q_id' => $question['id'],
                'metadataID' => $meta['id'],
                'screen' => 1,
                'user_answer' => 1
            )
        );
        $logdatagenerator->create_summative(
            array(
                'q_id' => $question['id'],
                'metadataID' => $meta2['id'],
                'screen' => 1,
                'user_answer' => 1
            )
        );
        $this->config->set_setting('cfg_summative_mgmt', true, \Config::BOOLEAN);
    }

    /**
     * Test getting emergency number.
     *
     * @group invigilation
     */
    public function testEmergencyNumbers(): void
    {
        $inv = new Invigilation();
        $contact1 = $this->config->get_setting('core', 'emergency_support_contact1');
        $contact2 = $this->config->get_setting('core', 'emergency_support_contact2');
        $contact3 = $this->config->get_setting('core', 'emergency_support_contact3');
        $expected = array(
            $contact1['name'] => $contact1['number'],
            $contact2['name'] => $contact2['number'],
            $contact3['name'] => $contact3['number']
        );
        $this->assertEquals($expected, $inv->emergencyNumbers());
    }

    /**
     * Test getting paper cohort - remote summative
     * @group invigilation
     */
    public function testGetStudents(): void
    {
        $inv = new Invigilation();
        $string = array();
        $paper = PaperProperties::get_paper_properties_by_id($this->paper['id'], $this->db, $string);
        $modules = array_keys($paper->get_modules());
        $labend = false;
        $timing = module_utils::modules_allow_timing($modules, $this->db);
        $data = array(
            'class' => 'student',
            'id' => $this->paper['id'] . '_' . $this->user['id'],
            'paperid' => $this->paper['id'],
            'userid' => $this->user['id'],
            'timing' => 'true',
            'completed' => false,
            'notes' => 0,
            'restbreak' => 0,
            'title' => $this->user['title'],
            'forname' => $this->user['first_names'],
            'surname' => $this->user['surname'],
            'endtime' => '',
            'special' => '',
            'specialextra' => '',
            'specialextratime' => '',
            'accessibility' => 1,
            'medical' => '',
            'breaks' => $this->user['breaks'],
        );
        $expected[] = $data;
        $this->assertEquals($expected, $inv->getStudents(implode(',', $modules), $paper, $labend, $timing));
    }

    /**
     * Test getting paper cohort - in lab summative
     * @group invigilation
     */
    public function testGetStudentsLab(): void
    {
        $inv = new Invigilation();
        $string = array();
        $paper = PaperProperties::get_paper_properties_by_id($this->paper2['id'], $this->db, $string);
        $modules = array_keys($paper->get_modules());
        $lab = new LabFactory($this->db);
        $lab_object = $lab->get_lab_based_on_client($this->pc['address']);
        $labend = new LogLabEndTime($lab_object->get_id(), $paper, $this->db);
        $timing = module_utils::modules_allow_timing($modules, $this->db);
        // Datetime in mysql is in UTC.
        $tz = new DateTimeZone('UTC');
        $enddatetime = new DateTime($this->paper2['end_date'], $tz);
        $enddatetime->setTimezone(new DateTimeZone($paper->get_timezone()));
        $data = array(
            'class' => 'student',
            'id' => $this->paper2['id'] . '_' . $this->user['id'],
            'paperid' => $this->paper2['id'],
            'userid' => $this->user['id'],
            'timing' => 'true',
            'completed' => false,
            'notes' => 0,
            'restbreak' => 0,
            'title' => $this->user['title'],
            'forname' => $this->user['first_names'],
            'surname' => $this->user['surname'],
            'endtime' => $enddatetime->format($this->config->get('cfg_short_time_php')),
            'special' => '',
            'specialextra' => '',
            'specialextratime' => '',
            'accessibility' => 1,
            'medical' => '',
            'breaks' => $this->user['breaks'],
        );
        $expected[] = $data;
        $this->assertEquals($expected, $inv->getStudents(implode(',', $modules), $paper, $labend, $timing));
    }
}
