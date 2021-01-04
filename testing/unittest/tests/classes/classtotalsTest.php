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
 * Unit tests for the class totals class.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 * @package tests
 */
class ClassTotalsTest extends \testing\unittest\unittestdatabase
{
    /** @var array Details of the module used in the tests. */
    protected $module1;

    /** @var array Details of the pre-generated paper. */
    protected $paper1;

    /** @var array Details of the question on the paper. */
    protected $question1;

    /** @var array Student who is enrolled on the module. */
    protected $student1;

    /** @var array Student who is enrolled on the module. */
    protected $student2;

    /** @var array Student who is enrolled on the module. */
    protected $student3;

    /** @var array Student who is not enrolled on the module. */
    protected $student4;

    /** @var array Staff member on the module. */
    protected $staff1;

    /**
     * Generate common data for test.
     *
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        // Create users for the tests.
        $user_generator = $this->get_datagenerator('users', 'core');
        $this->student1 = $user_generator->create_user(
            [
                'roles' => 'Student',
                'sid' => '24680',
                'surname' => 'Smith',
                'username' => 'student1',
                'grade' => 'A100',
            ]
        );
        $this->student2 = $user_generator->create_user(
            [
                'roles' => 'Student',
                'sid' => '13579',
                'surname' => 'Johnson',
                'username' => 'student2',
                'grade' => 'A100',
            ]
        );
        $this->student3 = $user_generator->create_user(
            [
                'roles' => 'Student',
                'sid' => '65478',
                'surname' => 'Peterson',
                'username' => 'student3',
                'grade' => 'A100',
            ]
        );
        $this->student4 = $user_generator->create_user(
            [
                'roles' => 'Student',
                'sid' => '65478',
                'surname' => 'Owen',
                'username' => 'student4',
                'grade' => 'A101',
            ]
        );
        $this->staff1 = $user_generator->create_user(['roles' => 'Staff', 'surname' => 'Xue', 'username' => 'teacher1']);

        // Create a module and enrollments for the tests.
        $module_generator = $this->get_datagenerator('modules', 'core');
        $this->module1 = $module_generator->create_module(['moduleid' => 'CTTEST', 'fullname' => 'Class totals testing']);

        // Three of the students are enrolled on the module.
        $module_generator->create_enrolment(['moduleid' => $this->module1['id'], 'userid' => $this->student1['id']]);
        $module_generator->create_enrolment(['moduleid' => $this->module1['id'], 'userid' => $this->student2['id']]);
        $module_generator->create_enrolment(['moduleid' => $this->module1['id'], 'userid' => $this->student3['id']]);

        // Enroll the member of staff.
        $module_generator->create_module_team(['moduleid' => 'CTTEST', 'username' => $this->staff1['username']]);
    }

    /**
     * Creates a paper with a true_false question.
     *
     * The paper will contain 1 true_false question.
     *
     * There will be answers for the following students:
     * - Student 1: Fully answered.
     * - Student 2: Started the paper no answer.
     * - Student 3: Not started the paper.
     * - Student 4: Fully answered.
     * - Staff user: Previewed and completed the paper.
     *
     * This should only be called one time per test.
     *
     * @param int $paper_type The type of paper to generate.
     * @throws \testing\datagenerator\no_database
     * @throws \testing\datagenerator\not_found
     */
    protected function createPaper(int $paper_type = assessment::TYPE_SUMMATIVE)
    {
        // Create the paper that will be used by the tests.
        $paper_generator = $this->get_datagenerator('papers', 'core');
        $this->paper1 = $paper_generator->create_paper(
            [
                'papertitle' => 'Class totals testing paper',
                'papertype' => $paper_type,
                'paperowner' => $this->staff1['username'],
                'modulename' => $this->module1['fullname'],
                'calendaryear' => '2020'
            ]
        );

        // Add a question to the paper.
        $question_generator = $this->get_datagenerator('questions', 'core');
        $this->question1 = $question_generator->create_question(
            [
                'type' => 'true_false',
                'user' => $this->staff1['username'],
                'leadin' => 'This is a true/false question?',
            ]
        );
        $question_generator->add_options_to_question(
            [
                'question' => $this->question1['id'],
                'correct' => 't',
                'marks_correct' => 1,
            ]
        );
        $question_generator->add_question_to_paper(
            [
                'paper' => $this->paper1['id'],
                'question' => $this->question1['id'],
                'screen' => 1,
                'displaypos' => 1,
            ]
        );

        $this->createUserAnswers();
    }

    /**
     * Creates the user answers for the paper.
     *
     * It should only be called by createPaper()
     */
    protected function createUserAnswers()
    {
        $log_generator = $this->get_datagenerator('log', 'core');
        // Started and completed the paper.
        $metadata1 = $log_generator->create_metadata(
            [
                'userID' => $this->student1['id'],
                'paperID' => $this->paper1['id'],
                'started' => '2020-10-01 10:00:00',
                'completed' => '2020-10-01 10:05:00',
            ]
        );
        $log_generator->create_exam(
            $this->paper1['papertype'],
            [
                'metadataID' => $metadata1['id'],
                'q_id' => $this->question1['id'],
                'mark' => 1,
                'adjmark' => 1,
                'totalpos' => 1,
                'user_answer' => 't',
                'screen' => 1,
                'duration' => 180,
                'updated' => '2020-10-01 10:03:00',
            ]
        );
        $metadata2 = $log_generator->create_metadata(
            [
                'userID' => $this->student4['id'],
                'paperID' => $this->paper1['id'],
                'started' => '2020-10-01 10:00:00',
                'completed' => '2020-10-01 10:05:00',
            ]
        );
        $log_generator->create_exam(
            $this->paper1['papertype'],
            [
                'metadataID' => $metadata2['id'],
                'q_id' => $this->question1['id'],
                'mark' => 0,
                'adjmark' => 0,
                'totalpos' => 1,
                'user_answer' => 'f',
                'screen' => 1,
                'duration' => 180,
                'updated' => '2020-10-01 10:03:00',
            ]
        );
        // Started the paper, but did not complete it.
        $log_generator->create_metadata(
            [
                'userID' => $this->student2['id'],
                'paperID' => $this->paper1['id'],
                'started' => '2020-10-01 10:00:00',
            ]
        );
        // The staff user previewed the paper.
        $metadata3 = $log_generator->create_metadata(
            [
                'userID' => $this->staff1['id'],
                'paperID' => $this->paper1['id'],
                'started' => '2020-09-30 10:00:00',
                'completed' => '2020-09-30 10:05:00',
            ]
        );
        $log_generator->create_exam(
            $this->paper1['papertype'],
            [
                'metadataID' => $metadata3['id'],
                'q_id' => $this->question1['id'],
                'mark' => 0,
                'adjmark' => 0,
                'totalpos' => 1,
                'user_answer' => 'f',
                'screen' => 1,
                'duration' => 180,
                'updated' => '2020-09-30 10:03:00',
            ]
        );
    }

    /**
     * Tests the get_user_results method.
     *
     * @dataProvider dataGetUserResults
     */
    public function testGetUserResults(int $papertype, bool $students, bool $absent, array $expected): void
    {
        $this->createPaper($papertype);

        $db = Config::get_instance()->db;
        $this->set_active_user($this->admin['id']);
        $paper_property = PaperProperties::get_paper_properties_by_id($this->paper1['id'], $db, []);
        $order = 'asc';
        $percentage = 100;
        $sort = 'name';
        $start = '20200930000000'; // Start of 3rd September 2020.
        $end = '20201002000000'; // Start of 2nd September 2020.
        $course = '%';
        $module = $this->module1['id'];

        $classtotals = new ClassTotals(
            $students,
            $percentage,
            $order,
            $absent,
            $sort,
            $this->userobject,
            $paper_property,
            $start,
            $end,
            $course,
            $module,
            $db,
            []
        );
        $classtotals->compile_report(true);
        $results = $classtotals->get_user_results();

        // We expect the results to have the same number of entries as the expected array.
        self::assertEquals(count($expected), count($results));

        // The expected array is a partial of the results.
        foreach ($expected as $key => $expectedrecord) {
            self::assertArrayHasKey($key, $results);
            foreach ($expectedrecord as $property => $value) {
                $failmessage = 'Array key: ' . $key;
                self::assertArrayHasKey($property, $results[$key], $failmessage);
                self::assertEquals($value, $results[$key][$property], $failmessage);
            }
        }
    }

    /**
     * Data provider for testGetUserResults.
     *
     * @return array
     */
    public function dataGetUserResults(): array
    {
        return [
            'Progress test: Active students' => [
                assessment::TYPE_PROGRESS,
                true,
                false,
                [
                    ['username' => 'student2', 'visible' => false, 'has_results' => false],
                    ['username' => 'student1', 'visible' => true, 'has_results' => true],
                ],
            ],
            'Progress test: Including absent students' => [
                assessment::TYPE_PROGRESS,
                true,
                true,
                [
                    ['username' => 'student2', 'visible' => true, 'has_results' => false],
                    ['username' => 'student3', 'visible' => true, 'has_results' => false],
                    ['username' => 'student1', 'visible' => true, 'has_results' => true],
                ],
            ],
            'Progress test: Active students and staff' => [
                assessment::TYPE_PROGRESS,
                false,
                false,
                [
                    ['username' => 'student2', 'visible' => false, 'has_results' => false],
                    ['username' => 'student1', 'visible' => true, 'has_results' => true],
                    ['username' => 'teacher1', 'visible' => true, 'has_results' => true],
                ],
            ],
            'Progress test: Including absent students and staff' => [
                assessment::TYPE_PROGRESS,
                false,
                true,
                [
                    ['username' => 'student2', 'visible' => true, 'has_results' => false],
                    ['username' => 'student3', 'visible' => true, 'has_results' => false],
                    ['username' => 'student1', 'visible' => true, 'has_results' => true],
                    ['username' => 'teacher1', 'visible' => true, 'has_results' => true],
                ],
            ],
        ];
    }
}
