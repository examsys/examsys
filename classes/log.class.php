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
 * Log package
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 */

use users\PaperList;

/**
 * Log helper class.
 */
abstract class log
{
    /**
     *  DB connection
     * @var mysqli
     */
    protected $db;

    /**
     * Unique identifier of paper/user entry in log
     * @var integer
     */
    protected $metadataid;

    /**
     * Does this paper type allow multiple attempts
     * @var boolean
     */
    protected $dorestart;

    /**
     * What screen is the user on
     * @var integer
     */
    protected $currentscreen;

    /**
     * Paper type
     * @var string
     */
    protected $papertype;

    /**
     * Screen duration
     * @var integer
     */
    protected $previousduration;

    /**
     * Screen previously submitted
     * @var boolean
     */
    protected $screenpresubmitted;

    /**
     * Flag to indicate if paper type uses log late table
     * @var boolean
     */
    protected $late;

    /**
     * Called when the object is unserialised.
     */
    public function __wakeup()
    {
        // The serialised database object will be invalid,
        // this object should only be serialised during an error report,
        // so adding the current database connect seems like a waste of time.
        $this->db = null;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $configObj = Config::get_instance();
        $this->db = $configObj->db;
        $this->late = false;
    }

    /**
     * Get previous answers for a paper/user in log - used to load exam script
     * @param integer $metadataID unique identifier of paper/user entry in log
     * @param boolean $do_restart does this paper type allow multiple attempts
     * @param integer $current_screen what screen is the user on
     * @param boolean so we need to check the log late table
     * @return array
     */
    public function get_previous_answers($metadataID, $do_restart, $current_screen, $check_log_late = false)
    {
        $this->previousduration = 0;
        $this->screenpresubmitted = 0;
        $this->dorestart = $do_restart;
        $this->currentscreen = $current_screen;
        $this->metadataid = $metadataID;
        if ($check_log_late and $this->late) {
            // If we are after the deadline check for answers in original_paper_type_log - these will be over written below by new answers in log_late below.
            return $this->get_log_late();
        } else {
            // Get user answers from whichever log is pointed to by log$paper_type
            return $this->get_log();
        }
    }

    /**
     * Get entries from paper type log table and ovewrite with the log late table
     * @return array
     */
    public function get_log_late()
    {
        $log = $this->get_log();
        $user_answers = $log['user_answers'];
        $user_dismiss = $log['user_dismiss'];
        $user_order = $log['user_order'];
        $used_questions = $log['used_questions'];
        $log_data = $this->db->prepare('SELECT id, q_id, user_answer, duration, screen, dismiss, option_order FROM log_late WHERE metadataID = ? ORDER BY id');
        $log_data->bind_param('i', $this->metadataid);
        $log_data->execute();
        $log_data->store_result();
        $log_data->bind_result($log_id, $log_q_id, $log_user_answer, $log_duration, $log_screen, $current_dismiss, $option_order);
        while ($log_data->fetch()) {
            $user_answers[$log_screen][$log_q_id] = $log_user_answer;
            $user_dismiss[$log_screen][$log_q_id] = $current_dismiss;
            $user_order[$log_screen][$log_q_id] = $option_order;
            $used_questions[$log_q_id] = $log_q_id;
            // Bump up the current screen if restarting
            if ($this->dorestart and $log_screen > $this->currentscreen) {
                $this->currentscreen = $log_screen;
            }
            if ($log_screen == $this->currentscreen) {
                $this->previousduration = $log_duration;
                $this->screenpresubmitted = 1;
            }
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
     * Update screen variables to keep track of user journey
     * @param integer $log_screen screen identifier
     * @param integer $log_duration time in seconds spent on screen
     */
    public function process_screen_variables($log_screen, $log_duration)
    {
        // Bump up the current screen if restarting
        if ($this->dorestart and $log_screen > $this->currentscreen) {
            $this->currentscreen = $log_screen;
        }
        if ($log_screen == $this->currentscreen) {
            $this->previousduration = $log_duration;
            $this->screenpresubmitted = 1;
        }
    }

    /**
     * Get list of users that have taken the exam order by total mark ascending.
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
            $from = 'log' . $this->papertype . ', log_metadata, users ' . self::get_student_only();
        } else {
            $from = 'log' . $this->papertype . ', log_metadata, users';
        }
        $userfilter = self::get_user_filter($userlist);
        $time_int = self::getStartInterval($this->papertype);
        $sql = "SELECT
            log_metadata.userID,
            SUM(mark) AS total_mark 
          FROM
            $from
          WHERE
            log$this->papertype.metadataID = log_metadata.id AND
            paperID = ? AND
            DATE_ADD(started, INTERVAL $time_int MINUTE) >= ? AND
            started <= ? $userfilter AND
            log_metadata.userID = users.id
          GROUP BY
            log_metadata.userID,
            paperID,
            started
          ORDER BY
            total_mark, log_metadata.userID";
        $result = $this->db->prepare($sql);
        $result->bind_param('iss', $paperid, $startdate, $enddate);
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
            $from = 'log' . $this->papertype . ', log_metadata, questions, users ' . self::get_student_only();
        } else {
            $from = 'log' . $this->papertype . ', log_metadata, questions, users';
        }

        $time_int = self::getStartInterval($this->papertype);

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
        log$this->papertype.q_id,
        user_answer,
        screen,
        log_metadata.id as metaid
      FROM 
        $from
      WHERE
        log$this->papertype.metadataID = log_metadata.id AND
        log$this->papertype.q_id = questions.q_id AND
        log_metadata.userID IN ($userlist) AND
        paperID = ? AND
        users.id = log_metadata.userID AND
        grade LIKE ? AND
        DATE_ADD(started, INTERVAL $time_int MINUTE) >= ? AND 
        started <= ?
      ORDER BY 
        surname,
        first_names,
        started,
        userID,
        metaid DESC";
        $result = $this->db->prepare($sql);
        $result->bind_param('isss', $paperid, $course, $startdate, $enddate);
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
     * Paper type accessor method
     * @return string
     */
    public function get_papertype()
    {
        return $this->papertype;
    }

    /**
     * Get paper logs
     */
    abstract public function get_log();

    /**
     * Get paper log class
     * @param string $papertype paper type
     * @return \log class
     */
    public static function get_paperlog($papertype)
    {
        switch ($papertype) {
            case '0':
                $papertype = 'formative';
                break;
            case '1':
                $papertype = 'progressive';
                break;
            case '2':
                $papertype = 'summative';
                break;
            case '3':
                $papertype = 'survey';
                break;
            case '4':
                $papertype = 'osce';
                break;
            case '5':
                $papertype = 'offline';
                break;
            case '6':
                $papertype = 'peer_review';
                break;
            default:
                throw new \Exception('Unsupported paper type.');
        }
        $paperpluginns = 'plugins\\papers\\' . $papertype . '\\log';
        return new $paperpluginns();
    }

    /**
     * Retrieve student only sql filter.
     *
     * @param string $userfield The userid field that the userroles table should be joined to
     * @param string $userrolesalias The alias for the user
     * @param string $rolesalias
     * @return string
     */
    public static function get_student_only(
        string $userfield = 'users.id',
        string $userrolesalias = 'ur',
        string $rolesalias = 'r'
    ) {
        return "JOIN user_roles $userrolesalias ON $userfield = $userrolesalias.userid JOIN roles $rolesalias
                    ON $userrolesalias.roleid = $rolesalias.id AND $rolesalias.name IN ('Student', 'graduate')";
    }

    /**
     * Retrieve user sql filter.
     * @param array $userlist list of users
     * @return string
     */
    public static function get_user_filter($userlist)
    {
        if (!is_array($userlist)) {
            $userfilter = '';
        } elseif (count($userlist) === 1) {
            $userfilter = 'AND log_metadata.userID = ' . $userlist[0];
        } elseif (count($userlist) > 0) {
            $userfilter = 'AND log_metadata.userID IN (' . implode(',', $userlist) . ')';
        } else {
            // No users found? So ensure query returns 0 rows.
            $userfilter = 'AND log_metadata.userID = 0';
        }
        return $userfilter;
    }

    /**
     * Check if user has previous attempts on ths paper.
     *
     * An attempt should be completed for it to be a previous attempt.
     *
     * @param int $paperid the paper identifier
     * @param int $userid the user identifier
     * @return bool
     */
    public static function hasPreviousAttempts(int $paperid, int $userid): bool
    {
        $configObj = Config::get_instance();
        $found = false;
        $sql = 'SELECT NULL FROM log_metadata WHERE paperID = ? AND userID = ? AND completed IS NOT NULL LIMIT 1';
        $result = $configObj->db->prepare($sql);
        $result->bind_param('ii', $paperid, $userid);
        $result->execute();
        $result->store_result();
        if ($result->num_rows > 0) {
            $found = true;
        }
        $result->close();
        return $found;
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
        $prev_attempts = new PaperList();

        $result = $this->db->prepare('SELECT lm.id, MAX(l.screen) AS screen, SUM(l.mark) AS mark,'
            . ' DATE_FORMAT(lm.started,"%Y%m%d%H%i%s") AS started, ? AS paper_type,'
            . ' DATE_FORMAT(lm.started,"%d/%m/%Y %H:%i") AS temp_date'
            . ' FROM log_metadata lm LEFT JOIN log' . $this->papertype . ' l ON l.metadataID = lm.id'
            . ' WHERE started IS NOT NULL AND lm.paperID = ? AND lm.userID = ? AND screen IS NOT NULL'
            . ' GROUP BY started, lm.id ORDER BY lm.id');
        $result->bind_param('iii', $this->papertype, $paperID, $userID);
        $result->execute();
        $result->bind_result($metadataID, $log_max_screen, $log_mark, $log_started, $log_paper_type, $log_temp_date);
        while ($result->fetch()) {
            $paper = new \users\Paper();
            $paper->log_started = $log_started;
            $paper->metadataID = $metadataID;
            $paper->max_screen = $log_max_screen;
            $paper->max_mark = $log_mark;
            $paper->paper_type = $log_paper_type;
            $paper->original_paper_type  = $log_paper_type;
            $paper->human_log_started = $log_temp_date;
            if ($log_paper_type == '0') {
                if ($total_marks > 0) {
                    if ($marking_style == 1) {
                        if ($total_marks == $total_random_mark) {
                            // This should never happen, but we have had errors that seem that suggest it does.
                            // This is here to stop future divide by zero errors from happening.
                            $adjPercent = 0;
                        } else {
                            $adjPercent = number_format((($log_mark - $total_random_mark) / ($total_marks - $total_random_mark)) * 100, 1, '.', ',');
                        }
                        if ($adjPercent < 0) {
                            $adjPercent = 0;
                        }
                        $paper->percent  = $adjPercent . '%';
                    } else {
                        $paper->percent  =  number_format(($log_mark / $total_marks) * 100, 1, '.', ',') . '%';
                    }
                }
            } else {
                $paper->percent = '';
            }
            $prev_attempts->add($paper);
        }
        $result->close();

        return $prev_attempts;
    }

    /**
     * Gets the amount of time we consider attempts before the start of a paper to be current.
     *
     * Paper types that allow an invigilator to start them may start shortly before the actual start time
     * since their clocks and the server time may not align exactly.
     *
     * @param int $papertype The type of paper
     * @return int Number of minutes
     */
    public static function getStartInterval(int $papertype): int
    {
        return match ($papertype) {
            \assessment::TYPE_SUMMATIVE, \assessment::TYPE_PROGRESS => 2,
            default => 0,
        };
    }
}
