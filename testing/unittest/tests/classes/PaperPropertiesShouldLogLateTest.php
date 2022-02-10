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
 * Tests for the PaperProperties::shouldLogLate() method.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2021 onwards The University of Nottingham
 * @package tests
 * @covers PaperProperties
 * @group paper
 */
class PaperPropertiesShouldLogLateTest extends unittestdatabase
{
    /** @var array Details of a lab in ExamSys. */
    protected $lab;

    /** @var array Details of a module. */
    protected $testmodule;

    /** @var array Details of a module with timing enabled. */
    protected $timedmodule;

    /** @var array details of the user registered on the module. */
    protected $user;

    /**
     * Generate the base data used by all the tests.
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('labs');
        $datagenerator->create_campus(['isdefault' => true]);
        $this->lab = $datagenerator->create_lab([]);

        $datagenerator = $this->get_datagenerator('modules');
        $this->testmodule = $datagenerator->create_module(['moduleid' => 'ABCD1234', 'fullname' => 'Is late testing']);
        $timed_properties = [
            'moduleid' => 'ABCD4321',
            'fullname' => 'Is late timed testing',
            'timed_exams' => 1
        ];
        $this->timedmodule = $datagenerator->create_module($timed_properties);

        $datagenerator = $this->get_datagenerator('users');
        $this->user = $datagenerator->create_user(['sid' => '987654321']);
    }

    /**
     * Generates a paper and returns the PaperProperties object for it.
     *
     * @param int $type The type of paper to generate.
     * @param string $start The start time of the paper
     * @param string $end The end time of the paper
     * @param string $modulename The full name of the module.
     * @param string|null $labs The labs the paper is attached to
     * @param bool $remote Should a remote exam be generated.
     * @return PaperProperties
     */
    protected function generatePaperProperties(
        int $type,
        string $start,
        string $end,
        string $modulename,
        ?string $labs = null,
        bool $remote = false
    ): PaperProperties {
        $datagenerator = $this->get_datagenerator('papers');
        $papaer_details = [
            'papertitle' => 'Test paper',
            'papertype' => $type,
            'paperowner' => $this->admin['username'],
            'modulename' => $modulename,
            'duration' => 60,
            'startdate' => $start,
            'enddate' => $end,
            'labs' => $labs,
            'remote' => $remote,
        ];
        $paper = $datagenerator->create_paper($papaer_details);

        if (!$remote and !is_null($labs)) {
            // Also generate the lab start and end time.
            $labgenerator = $this->get_datagenerator('labs');

            // The exams is hardcoded for an hour.
            $end = new DateTime($start);
            $end->add(DateInterval::createFromDateString('1 hour'));

            $lab_details = [
                'paperID' => $paper['id'],
                'labID' => $labs,
                'start_time' => $start,
                'end_time' => $end->getTimestamp(),
            ];
            $labgenerator->createLabTime($lab_details);
        }

        $paper_property = new PaperProperties(Config::get_instance()->db);
        $paper_property->set_property_id($paper['id']);
        $paper_property->load();

        return $paper_property;
    }

    /**
     * Configure the system level remote summative exam settings.
     *
     * @param bool $use_minutes Sets if the user extra time is in minutes (default: false)
     * @param bool $breaks Sets if a remote summative can be paused by users (default: false)
     */
    protected function configureSummatives(bool $use_minutes = false, bool $breaks = false)
    {
        $config = Config::get_instance();

        $type = $config->get_setting_type('core', 'paper_pause_exam');
        $config->set_setting('paper_pause_exam', $breaks, $type);

        $type = $config->get_setting_type('core', 'paper_breaktime_mins');
        $config->set_setting('paper_breaktime_mins', $use_minutes, $type);
    }

    /**
     * Generates and returns the log metadata for a user on the paper.
     *
     * @param int $paper_id
     * @param int $user_id
     * @param string $start
     * @return \LogMetadata
     */
    protected function generateMetaDataForPaper(int $paper_id, int $user_id, string $start): LogMetadata
    {
        $datagenerator = $this->get_datagenerator('log');
        $started = new DateTime($start);
        $params = [
            'userID' => $user_id,
            'paperID' => $paper_id,
            'started' => $started->format('Y-m-d H:i:s'),
        ];
        $datagenerator->create_metadata($params);
        $log = new LogMetadata($user_id, $paper_id, Config::get_instance()->db);
        $log->get_record();
        return $log;
    }

    /**
     * Tests that we will correctly put results into the late log if submitted now.
     *
     * @param string $start
     * @param string $end
     * @param bool $expected
     * @dataProvider dataProgress
     */
    public function testProgress(string $start, string $end, bool $expected)
    {
        $properties = $this->generatePaperProperties(assessment::TYPE_PROGRESS, $start, $end, $this->testmodule['fullname']);
        $metadata = $this->generateMetaDataForPaper($properties->get_property_id(), $this->user['id'], $start);
        $this->set_active_user($this->user['id']);
        $this->assertEquals($expected, $properties->shouldLogLate(null, $metadata));
    }

    /**
     * Data for testProgress.
     *
     * @return array
     */
    public function dataProgress(): array
    {
        return [
            'during time' => ['30 minutes ago', '30 minutes', false],
            'after time' => ['61 minutes ago', '61 seconds ago', true],
        ];
    }

    /**
     * Test that formative exams never go to the late log.
     *
     * @param string $start
     * @param string $end
     * @dataProvider dataFormative
     */
    public function testFormative(string $start, string $end)
    {
        $properties = $this->generatePaperProperties(assessment::TYPE_FORMATIVE, $start, $end, $this->testmodule['fullname']);
        $metadata = $this->generateMetaDataForPaper($properties->get_property_id(), $this->user['id'], $start);
        $this->set_active_user($this->user['id']);
        $this->assertFalse($properties->shouldLogLate(null, $metadata));
    }

    /**
     * Data for testProgress.
     *
     * @return array
     */
    public function dataFormative(): array
    {
        return [
            'during time' => ['30 minutes ago', '30 minutes'],
            'after time' => ['61 minutes ago', '61 seconds ago'],
        ];
    }

    /**
     * Tests that summative exams that are not times work correctly.
     *
     * @param string $start
     * @param string $end
     * @param bool $expected
     * @dataProvider dataSummativeUnTimed
     */
    public function testSummativeUnTimed(string $start, string $end, bool $expected)
    {
        // Create the paper and get the property.
        $properties = $this->generatePaperProperties(assessment::TYPE_SUMMATIVE, $start, $end, $this->testmodule['fullname'], $this->lab['id']);
        $metadata = $this->generateMetaDataForPaper($properties->get_property_id(), $this->user['id'], $start);

        // Test that the late log is used correctly.
        $this->set_active_user($this->user['id']);
        $this->assertEquals($expected, $properties->shouldLogLate($this->lab['id'], $metadata));
    }

    /**
     * Data for estSummativeUnTimed
     * @return array[]
     */
    public function dataSummativeUnTimed(): array
    {
        return [
            'during exam' => ['30 minutes ago', '30 minutes',false],
            'after exam' => ['61 minutes ago', '61 seconds ago', true],
        ];
    }

    /**
     * Tests that when in a lab summative exams detect if they should put answers in the late log.
     *
     * @param string $paper_start
     * @param string $paper_end
     * @param string $lab_start
     * @param string $lab_end
     * @param bool $expected
     * @dataProvider dataSummativeTimed
     */
    public function testSummativeTimed(string $paper_start, string $paper_end, string $lab_start, string $lab_end, bool $expected)
    {
        // Create the paper and get the property.
        $properties = $this->generatePaperProperties(assessment::TYPE_SUMMATIVE, $paper_start, $paper_end, $this->timedmodule['fullname'], $this->lab['id']);
        $metadata = $this->generateMetaDataForPaper($properties->get_property_id(), $this->user['id'], $lab_start);

        // Create the lab end time.
        $datagenerator = $this->get_datagenerator('labs');
        $lab_time = [
            'labID' => $this->lab['id'],
            'invigilatorID' => $this->admin['id'],
            'paperID' => $properties->get_property_id(),
            'start_time' => $lab_start,
            'end_time' => $lab_end,
        ];
        $datagenerator->createLabTime($lab_time);

        // Test that the late log is used correctly.
        $this->set_active_user($this->user['id']);
        $this->assertEquals($expected, $properties->shouldLogLate($this->lab['id'], $metadata));
    }

    /**
     * Data for testSummativeTimed
     *
     * @return array[]
     */
    public function dataSummativeTimed(): array
    {
        return [
            'during exam' => ['30 minutes ago', '30 minutes', '30 minutes ago', '30 minutes', false],
            'grace period' => ['30 minutes ago', '30 minutes', '61 minutes ago', '10 seconds ago', false],
            'during, lab ends after exam end' => ['61 minutes ago', '61 seconds ago', '50 minutes ago', '10 minutes', false],
            'grace period, lab ends after exam end' => ['61 minutes ago', '61 seconds ago', '50 minutes ago', '10 seconds ago', false],
            'after exam' => ['61 minutes ago', '61 seconds ago', '61 minutes ago', '61 seconds ago', true],
            'after lab end, before exam end' => ['62 minutes ago', '30 minutes', '61 minutes ago', '61 seconds ago', true],
        ];
    }

    /**
     * Tests that remote summative answers will be sent to the late log.
     *
     * @param string $paper_start
     * @param string $paper_end
     * @param string $user_start
     * @param bool $expected
     * @dataProvider dataSummativeRemote
     */
    public function testSummativeRemote(string $paper_start, string $paper_end, string $user_start, bool $expected)
    {
        $this->configureSummatives();

        // Create the paper and get the property.
        $properties = $this->generatePaperProperties(assessment::TYPE_SUMMATIVE, $paper_start, $paper_end, $this->testmodule['fullname'], null, true);
        $metadata = $this->generateMetaDataForPaper($properties->get_property_id(), $this->user['id'], $user_start);

        // Test that the late log is used correctly.
        $this->set_active_user($this->user['id']);
        $this->assertEquals($expected, $properties->shouldLogLate(null, $metadata));
    }

    /**
     * Data for testSummativeRemote.
     *
     * @return array
     */
    public function dataSummativeRemote(): array
    {
        return [
            'during exam' => ['4 hours ago', '4 hours', '30 minutes ago', false],
            'grace period' => ['4 hours ago', '4 hours', '60 minutes 10 second ago', false],
            'user out of time' => ['4 hours ago', '4 hours', '61 minutes 1 second ago', true],
            'exam over, user has time remaining' => ['8 hours ago', '61 seconds ago', '30 minutes ago', true],
            'exam over, user out of time' => ['8 hours ago', '1 minute ago', '61 minutes 1 second ago', true],
        ];
    }

    /**
     * Tests that a student with extra time via their accessibility settings is late logged correctly.
     *
     * @param string $start The start of the paper and user start time.
     * @param string $paper_end The paper end time.
     * @param int $extra Extra time as a percentage of the paper time.
     * @param bool $remote Set if the exam should be remote or not.
     * @param bool $expected The expected outcome.
     * @dataProvider dataSummativeStudentExtraTime
     */
    public function testSummativeStudentExtraTime(string $start, string $paper_end, int $extra, bool $remote, bool $expected)
    {
        $this->configureSummatives();

        // Create the paper and get the property.
        $properties = $this->generatePaperProperties(assessment::TYPE_SUMMATIVE, $start, $paper_end, $this->timedmodule['fullname'], $this->lab['id'], $remote);

        // Create a user with extra time.
        $datagenerator = $this->get_datagenerator('users');
        $userdata = [
            'sid' => '5265727',
            'special_needs' => [
                'extra_time' => $extra,
            ],
        ];
        $user = $datagenerator->create_user($userdata);

        $metadata = $this->generateMetaDataForPaper($properties->get_property_id(), $user['id'], $start);

        // Use the lab when not a remote exam.
        $lab = ($remote) ? null : $this->lab['id'];

        // Test that the late log is used correctly.
        $this->set_active_user($user['id']);
        $this->assertEquals($expected, $properties->shouldLogLate($lab, $metadata));
    }

    /**
     * Data for testSummativeStudentExtraTime
     *
     * @return array
     */
    public function dataSummativeStudentExtraTime(): array
    {
        return [
            // 25% extra time on a 1 hour exam is 15 minutes.
            'in extended period, in lab' => ['74 minutes ago', '30 minutes', 25, false, false],
            'extended passed, in lab' => ['76 minutes 1 second ago', '30 minutes', 25, false, true],
            'in extended period, remote' => ['74 minutes ago', '30 minutes', 25, true, false],
            'extended passed, remote' => ['76 minutes 1 second ago', '30 minutes', 25, true, true],
        ];
    }

    /**
     * Checks that the late log is used correctly when an invigilator gives a user extra time.
     *
     * @param string $start The paper and lab start time
     * @param string $paper_end The paper end time.
     * @param string $lab_end the lab end time
     * @param int $extra The amount of extra time available to the user.
     * @param bool $expected The expected outcome.
     * @dataProvider dataSummativeLabUserExtraTime
     */
    public function testSummativeLabUserExtraTime(
        string $start,
        string $paper_end,
        string $lab_end,
        int $extra,
        bool $expected
    ) {
        // Set the paper break setting.
        $this->configureSummatives();

        // Create the paper and get the property.
        $properties = $this->generatePaperProperties(assessment::TYPE_SUMMATIVE, $start, $paper_end, $this->timedmodule['fullname'], $this->lab['id']);
        $metadata = $this->generateMetaDataForPaper($properties->get_property_id(), $this->user['id'], $start);

        // Create the lab end time.
        $datagenerator = $this->get_datagenerator('labs');
        $lab_time = [
            'labID' => $this->lab['id'],
            'invigilatorID' => $this->admin['id'],
            'paperID' => $properties->get_property_id(),
            'start_time' => $start,
            'end_time' => $lab_end,
        ];
        $datagenerator->createLabTime($lab_time);

        // Add invigilator awarded extra time.
        $extra_time = [
            'extra_time' => $extra,
            'invigilator' => $this->admin['id'],
            'lab' => $this->lab['id'],
            'paper' => $properties->get_property_id(),
            'student' => $this->user['id'],
        ];
        $datagenerator->createExtraTime($extra_time);

        // Test that the late log is used correctly.
        $this->set_active_user($this->user['id']);
        $this->assertEquals($expected, $properties->shouldLogLate($this->lab['id'], $metadata));
    }

    /**
     * Data for testSummativeLabUserExtraTime
     *
     * @return array
     */
    public function dataSummativeLabUserExtraTime(): array
    {
        return [
            'in extended period' => ['74 minutes ago', '30 minutes', '14 minutes ago', 15, false],
            'extended passed' => ['76 minutes ago', '30 minutes', '16 minutes 1 second ago', 15, true],
        ];
    }

    /**
     * Tests that remote summative exams with breaks enabled work as expected.
     *
     * @param string $start The start of the paper and the user attempt
     * @param string $end The end of the paper
     * @param bool $use_minutes If the break time is in minutes or a percentage of the paper duration.
     * @param int $break_time The amount of break time the user has.
     * @param ?int $break_remaining The amount of break time remaining to the user, or null if the exam has not been paused.
     * @param int $extra_time The amount of special needs time the user has added to papers.
     * @param bool $expected The expected result
     * @dataProvider dataSummativeRemoteBreaks
     */
    public function testSummativeRemoteBreaks(
        string $start,
        string $end,
        bool $use_minutes,
        int $break_time,
        ?int $break_remaining,
        int $extra_time,
        bool $expected
    ) {
        $this->configureSummatives($use_minutes, true);

        // Create the paper and get the property.
        $properties = $this->generatePaperProperties(assessment::TYPE_SUMMATIVE, $start, $end, $this->testmodule['fullname'], null, true);

        // Create the user.
        $datagenerator = $this->get_datagenerator('users');
        $userdata = [
            'sid' => '5265727',
        ];
        $special_needs = [];

        if ($extra_time > 0) {
            // Create a user with extra time.
            $special_needs['extra_time'] = $extra_time;
        }

        if ($break_time > 0) {
            // Create break time for the user.
            $special_needs['break_time'] = $break_time;
        }

        if (!empty($special_needs)) {
            // The user has special needs.
            $userdata['special_needs'] = $special_needs;
        }

        $user = $datagenerator->create_user($userdata);

        $metadata = $this->generateMetaDataForPaper($properties->get_property_id(), $user['id'], $start);

        if (!is_null($break_remaining)) {
            // Set the remaining time for the exam.
            $datagenerator = $this->get_datagenerator('breaks');
            $break = [
                'paperID' => $properties->get_property_id(),
                'userID' => $user['id'],
                'time' => ($break_remaining * 60),
            ];
            $datagenerator->createExamBreak($break);
        }

        // Test that the late log is used correctly.
        $this->set_active_user($user['id']);
        $this->assertEquals($expected, $properties->shouldLogLate(null, $metadata));
    }

    /**
     * Data for testSummativeRemoteBreaks.
     *
     * @return array
     */
    public function dataSummativeRemoteBreaks(): array
    {
        return [
            // No special needs time. 15 minutes of breaks will be given.
            'during' => ['59 minutes ago', '1 hour', true, 15, null, 0, false],
            'after' => ['61 minutes 1 second ago', '1 hour', true, 15, null, 0, true],
            'during, some break used' => ['69 minutes ago', '1 hour', true, 15, 5, 0, false],
            'after, some break used' => ['71 minutes 1 second ago', '1 hour', true, 15, 5, 0, true],
            'during, all break used' => ['74 minutes ago', '1 hour', true, 15, 0, 0, false],
            'after, all break used' => ['76 minutes 1 second ago', '1 hour', true, 15, 0, 0, true],
            'during, some break used, percentage' => ['69 minutes ago', '1 hour', false, 25, 5, 0, false],
            'after, some break used, percentage' => ['71 minutes 1 second ago', '1 hour',  false, 25, 5, 0, true],
            'during, all break used, percentage' => ['74 minutes ago', '1 hour',  false, 25, 0, 0, false],
            'after, all break used, percentage' => ['76 minutes 1 second ago', '1 hour',  false, 25, 0, 0, true],

            // 15 minutes of special needs time, i.e. 25% of 1 hour.
            // Minute per hour will give 30 minutes of break time, percentage break time will give 19 minutes.
            'during, special needs' => ['74 minutes ago', '1 hour', true, 15, null, 25, false],
            'after, special needs' => ['76 minutes 1 second ago', '1 hour', true, 15, null, 25, true],
            'during, some break used, special needs' => ['99 minutes ago', '1 hour', true, 15, 5, 25, false],
            'after, some break used, special needs' => ['101 minutes 1 second ago', '1 hour', true, 15, 5, 25, true],
            'during, all break used, special needs' => ['104 minutes ago', '1 hour', true, 15, 0, 25, false],
            'after, all break used, special needs' => ['106 minutes 1 second ago', '1 hour', true, 15, 0, 25, true],
            'during, some break used, percentage, special needs' => ['88 minutes ago', '1 hour', false, 25, 5, 25, false],
            'after, some break used, percentage, special needs' => ['90 minutes 1 second ago', '1 hour',  false, 25, 5, 25, true],
            'during, all break used, percentage, special needs' => ['93 minutes ago', '1 hour',  false, 25, 0, 25, false],
            'after, all break used, percentage, special needs' => ['95 minutes 1 second ago', '1 hour',  false, 25, 0, 25, true],
        ];
    }
}
