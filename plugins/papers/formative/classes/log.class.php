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
 * Formative package
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 */

namespace plugins\papers\formative;

use users\PaperList;

/**
 * Formative helper class.
 */
class log extends \log
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->papertype = '0';
    }

    /**
     * Get formative logs
     * @return array
     */
    public function get_log()
    {
        $user_answers = array();
        $user_dismiss = array();
        $user_order = array();
        $used_questions = array();
        $log_data = $this->db->prepare('SELECT id, q_id, user_answer, duration, screen, dismiss, option_order FROM log0 WHERE metadataID = ? ORDER BY id');
        $log_data->bind_param('i', $this->metadataid);
        $log_data->execute();
        $log_data->store_result();
        $log_data->bind_result($log_id, $log_q_id, $log_user_answer, $log_duration, $log_screen, $current_dismiss, $option_order);
        while ($log_data->fetch()) {
            $user_answers[$log_screen][$log_q_id] = $log_user_answer;
            $user_dismiss[$log_screen][$log_q_id] = $current_dismiss;
            $user_order[$log_screen][$log_q_id] = $option_order;
            $used_questions[$log_q_id] = $log_q_id;
            $this->process_screen_variables($log_screen, $log_duration);
        }
        $log_data->close();
        return array('used_questions' => $used_questions,
        'user_answers' => $user_answers,
        'user_dismiss' => $user_dismiss,
        'user_order' => $user_order,
        'previous_duration' => $this->previousduration,
        'screen_pre_submitted' => $this->screenpresubmitted,
        'current_screen' => $this->currentscreen);
    }

    /**
     * Get list of users that have taken the exam order by total mark ascending.
     * Formative results inclused progressive results, as progressive papers can be converted into a formative
     * @param integer $paperid paper id
     * @param string $startdate start datetime for filter
     * @param string $enddate end datetime for filter
     * @param string $userlist user filter
     * @param boolean $studentonly flag to set student only filter
     * @return array
     */
    public function get_log_users($paperid, $startdate, $enddate, $userlist, $studentonly = false)
    {
        $user_list = array();
        if ($studentonly) {
            $rolefilter = self::get_student_only();
            $from = 'log0, users, log_metadata, user_roles ur JOIN roles r ON ur.roleid = r.id';
            $from1 = 'log1, users, log_metadata, user_roles ur JOIN roles r ON ur.roleid = r.id';
        } else {
            $rolefilter = '';
            $from = 'log0, log_metadata, users';
            $from1 = 'log1, log_metadata, users';
        }
        $userfilter = self::get_user_filter($userlist);
        $sql = "SELECT
              log_metadata.userID,
              SUM(mark) AS total_mark
            FROM
              $from
            WHERE
              log0.metadataID = log_metadata.id AND
              log_metadata.userID = users.id
              AND paperID = ?
              AND started >= ?
              AND started <= ? $userfilter $rolefilter
            GROUP BY
              log_metadata.userID,
              paperID,
              started
            UNION ALL
            SELECT
              log_metadata.userID,
              sum(mark) AS total_mark
            FROM
              $from1
            WHERE
              log1.metadataID = log_metadata.id AND
              log_metadata.userID = users.id AND
              paperID = ? AND
              started >= ? AND
              started <= ? $userfilter $rolefilter
            GROUP BY
              log_metadata.userID,
              paperID,
              started
            ORDER BY
              2, 1";
        $result = $this->db->prepare($sql);
        $result->bind_param('ississ', $paperid, $startdate, $enddate, $paperid, $startdate, $enddate);
        $result->execute();
        $result->bind_result($tmp_userID, $total_mark);
        $i = 0;
        while ($result->fetch()) {
            $user_list[$i]['userid'] = $tmp_userID;
            $user_list[$i]['totalmark'] = $total_mark;
            $i++;
        }
        $result->free_result();
        $result->close();
        return $user_list;
    }

    /**
     * Get list of users that have taken the exam order by total mark ascending.
     * Formative results inclused progressive results, as progressive papers can be converted into a formative
     * @param integer $paperid paper id
     * @param string $startdate start datetime for filter
     * @param string $enddate end datetime for filter
     * @param string $userlist list of users to filter
     * @param string $course course filter
     * @param boolean $studentonly flag to set student only filter
     * @return array
     */
    public function get_assessment_data($paperid, $startdate, $enddate, $userlist, $course = '%', $studentonly = false)
    {
        $data = array();
        if ($studentonly) {
            $rolefilter = self::get_student_only();
            $from = 'log0, users, questions, log_metadata, user_roles ur JOIN roles r ON ur.roleid = r.id';
            $from1 = 'log1, users, questions, log_metadata, user_roles ur JOIN roles r ON ur.roleid = r.id';
        } else {
            $rolefilter = '';
            $from = 'log0, log_metadata, questions,users';
            $from1 = 'log1, log_metadata, questions, users';
        }
        $sql = "SELECT DISTINCT 
              username, 
              log_metadata.userID, 
              title, 
              surname, 
              first_names, 
              grade, 
              gender, 
              year, 
              started, 
              log0.q_id, 
              user_answer, 
              screen,
              log_metadata.id as metaid
            FROM 
              $from
            WHERE 
              log0.metadataID = log_metadata.id AND 
              log0.q_id = questions.q_id AND 
              log_metadata.userID IN ($userlist) AND 
              paperID = ? AND 
              users.id = log_metadata.userID 
              $rolefilter AND 
              grade LIKE ? 
              AND started >= ? AND 
              started <= ?
            UNION ALL
            SELECT DISTINCT 
                username, 
                log_metadata.userID, 
                title, 
                surname, 
                first_names, 
                grade, 
                gender, 
                year, 
                started, 
                log1.q_id, 
                user_answer,
                screen,
                log_metadata.id as metaid
             FROM 
                $from1
             WHERE log1.metadataID = log_metadata.id AND 
                log1.q_id = questions.q_id AND 
                log_metadata.userID IN ($userlist) AND 
                paperID = ? AND 
                users.id = log_metadata.userID
                $rolefilter AND 
                grade LIKE ? 
                AND started >= ? 
                AND started <= ?
              ORDER BY 
                surname, 
                first_names, 
                started, 
                userID,
                metaid DESC";
        $result = $this->db->prepare($sql);
        $result->bind_param('isssisss', $paperid, $course, $startdate, $enddate, $paperid, $course, $startdate, $enddate);
        $result->execute();
        $result->bind_result($username, $uID, $title, $surname, $first_names, $grade, $gender, $year, $started, $question_ID, $user_answer, $screen, $metaid);
        $i = 0;
        while ($result->fetch()) {
            $data[$i]['username'] = $username;
            $data[$i]['uID'] = $uID;
            $data[$i]['title'] = $title;
            $data[$i]['surname'] = $surname;
            $data[$i]['first_names'] = $first_names;
            $data[$i]['grade'] = $grade;
            $data[$i]['gender'] = $gender;
            $data[$i]['year'] = $year;
            $data[$i]['started'] = $started;
            $data[$i]['question_ID'] = $question_ID;
            $data[$i]['user_answer'] = $user_answer;
            $data[$i]['screen'] = $screen;
            $i++;
        }
        $result->close();
        return $data;
    }

    /**
     * Load previous attempts for user on this assessment
     *
     * @param integer $paperID the test idendifier
     * @param integer $userID the user identifier
     * @param int $marking_style the marking style of the paper
     * @param float $total_marks the total marks of the paper
     * @param float $total_random_mark the total monkey mark for the paper
     * @return PaperList
     */
    public function loadAttempts($paperID, $userID, $marking_style, $total_marks, $total_random_mark): PaperList
    {
        $prev_attempts = parent::loadAttempts($paperID, $userID, $marking_style, $total_marks, $total_random_mark);

        // If type is Formative query the Progress Test log table as well and add into array if max screen is not blank.
        $result = $this->db->prepare('SELECT lm.id, MAX(l.screen) AS screen, SUM(l.mark) AS mark,'
            . ' DATE_FORMAT(lm.started,"%Y%m%d%H%i%s") AS started, 1 AS paper_type,'
            . ' DATE_FORMAT(lm.started,"%d/%m/%Y %H:%i") AS temp_date'
            . ' FROM log_metadata lm LEFT JOIN log1 l ON l.metadataID = lm.id'
            . ' WHERE started IS NOT NULL AND lm.paperID = ? AND lm.userID = ? AND screen IS NOT NULL'
            . ' GROUP BY started, lm.id ORDER BY lm.id');
        $result->bind_param('ii', $paperID, $userID);
        $result->execute();
        $result->bind_result($metadataID, $log_max_screen, $log_mark, $log_started, $log_paper_type, $log_temp_date);
        while ($result->fetch()) {
            if ($log_max_screen > 0) {
                $paper = new \users\Paper();
                $paper->log_started = $log_started;
                $paper->metadataID = $metadataID;
                $paper->max_screen = $log_max_screen;
                $paper->max_mark = $log_mark;
                $paper->paper_type = $log_paper_type;
                $paper->human_log_started = $log_temp_date;

                if ($total_marks > 0) {
                    if ($marking_style == 1) {
                        $adjPercent = number_format((($log_mark - $total_random_mark) / ($total_marks - $total_random_mark)) * 100, 1, '.', ',');
                        if ($adjPercent < 0) {
                            $adjPercent = 0;
                        }
                        $paper->percent  = $adjPercent . '%';
                    } else {
                        $paper->percent  =  number_format(($log_mark / $total_marks) * 100, 1, '.', ',') . '%';
                    }
                }

                $prev_attempts->add($paper);
            }
        }
        $result->close();

        return $prev_attempts;
    }
}
