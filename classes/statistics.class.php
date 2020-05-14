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
 * Statistics accessor methods
 * Used by PaperProperties to access settings
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 */
class Statistics
{
    /**
     * @var mysqli The database object
     */
    private $db;

    /**
     * @var Config The config object
     */
    private $config;

    /**
     * @var string Language pack component
     */
    private $langcomponent = 'classes/statistics';

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
     * Constructor.
     */
    public function __construct()
    {
        $configObject = Config::get_instance();
        $this->db = $configObject->db;
        $this->config = $configObject;
    }

    /**
     * Get the month start datetime
     * @param integer $year the year
     * @param string $month the month - padded integer representation i.e. 05 = May
     * @return string
     */
    private function getMonthStart(int $year, string $month): string
    {
        $month_start['09'] = $year . '0901000000';
        $month_start['10'] = $year . '1001000000';
        $month_start['11'] = $year . '1101000000';
        $month_start['12'] = $year . '1201000000';
        $month_start['01'] = ($year + 1) . '0101000000';
        $month_start['02'] = ($year + 1) . '0201000000';
        $month_start['03'] = ($year + 1) . '0301000000';
        $month_start['04'] = ($year + 1) . '0401000000';
        $month_start['05'] = ($year + 1) . '0501000000';
        $month_start['06'] = ($year + 1) . '0601000000';
        $month_start['07'] = ($year + 1) . '0701000000';
        $month_start['08'] = ($year + 1) . '0801000000';
        return $month_start[$month];
    }

    /**
     * Get the month end datetime
     * @param integer $year the year
     * @param string $month the month - padded integer representation i.e. 05 = May
     * @return string
     */
    private function getMonthEnd(int $year, string $month): string
    {
        $month_end['09'] = $year . '1001000000';
        $month_end['10'] = $year . '1101000000';
        $month_end['11'] = $year . '1201000000';
        $month_end['12'] = ($year + 1) . '0101000000';
        $month_end['01'] = ($year + 1) . '0201000000';
        $month_end['02'] = ($year + 1) . '0301000000';
        $month_end['03'] = ($year + 1) . '0401000000';
        $month_end['04'] = ($year + 1) . '0501000000';
        $month_end['05'] = ($year + 1) . '0601000000';
        $month_end['06'] = ($year + 1) . '0701000000';
        $month_end['07'] = ($year + 1) . '0801000000';
        $month_end['08'] = ($year + 1) . '0901000000';
        return $month_end[$month];
    }

    /**
     * Get summative papers for an academic year
     * @param int $current_year the current year
     * @return array
     */
    public function getSummativePapers(int $current_year): array
    {
        $papers = array();
        $result = $this->db->prepare('
            SELECT
                property_id,
                paper_title,
                DATE_FORMAT(start_date,"%m"),
                start_date,
                end_date,
                labs
            FROM
                properties
            WHERE
                paper_type = "2" AND
                start_date >= ' . $current_year . '0901000000 AND
                end_date < ' . ($current_year + 1) . '0831235959 AND
                deleted IS NULL
                ORDER BY start_date
        ');
        $result->execute();
        $result->store_result();
        $result->bind_result($property_id, $paper_title, $month, $start_date, $end_date, $labs);
        while ($result->fetch()) {
            $papers[$property_id] = array(
                'title' => $paper_title,
                'month' => $month,
                'start' => $start_date,
                'end' => $end_date,
                'labs' => $labs
            );
        }
        $result->close();
        return $papers;
    }

    /**
     * Get summative paper details
     * @param string $month month exam taken in - padded integer representation i.e. 05 = May
     * @param integer $year year exam taken in
     * @return array
     */
    public function getSummativePapersDetails(string $month, int $year): array
    {
        $monthstart = $this->getMonthStart($year, $month);
        $monthend = $this->getMonthEnd($year, $month);
        $details = array();
        $result = $this->db->prepare('
            SELECT
                property_id,
                paper_title,
                DATE_FORMAT(start_date, "' . $this->config->get('cfg_long_date_time') . '") AS display_start_date,
                start_date,
                end_date
            FROM
                properties
            WHERE
                paper_type = "2" AND
                start_date >= ' . $monthstart . ' AND
                end_date < ' . $monthend . ' AND
                deleted IS NULL
                ORDER BY start_date
        ');
        $result->execute();
        $result->store_result();
        $result->bind_result($property_id, $paper_title, $display_start_date, $start_date, $end_date);
        while ($result->fetch()) {
            $details[$property_id] = array(
                'title' => $paper_title,
                'display_start' => $display_start_date,
                'start' => $start_date,
                'end' => $end_date,
            );
        }
        $result->close();
        return $details;
    }

    /**
     * Get number of distinct students that have taken an exam in a date range
     * @param int $pid the paper
     * @param string $start the paper start date
     * @param string $end the paper end date
     * @return array
     */
    public function getStudentCount(int $pid, string $start, string $end): array
    {
        $users = array();
        $paper_data = $this->db->prepare('
            SELECT
                DISTINCT userid
            FROM
                log_metadata,
                users
            WHERE
                log_metadata.userID = users.ID AND
                roles IN ("Student", "graduate") AND
                paperID = ? AND
                DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND
                started <= ?
        ');
        $paper_data->bind_param('iss', $pid, $start, $end);
        $paper_data->execute();
        $paper_data->store_result();
        $paper_data->bind_result($userid);
        while ($paper_data->fetch()) {
            $users[$userid] = 1;
        }
        $paper_data->close();
        return $users;
    }

    /**
     * Render stats page header
     * @param integer $year current academic year
     * @param string|null $month current month - padded integer representation i.e. 05 = May
     */
    public function renderStatsHeader(int $year, ?string $month = null): void
    {
        $render = new render($this->config);
        $yearutils = new yearutils($this->db);
        if (is_null($month)) {
            $extra = '';
        } else {
            $extra = '&month=' . $month;
        }
        $data = $yearutils->generateTabs($year, 'academic', $extra);
        $render->render($data, array(), 'admin/statistics/statsheader.html');
    }

    /**
     * Render summative exam summary page.
     * @param array $data data to render
     */
    public function renderSummativeSummary(array $data): void
    {
        $langpack = new langpack();
        $strings = $langpack->get_all_strings($this->langcomponent);
        $render = new render($this->config);
        $render->render($data, $strings, 'admin/statistics/summative_summary.html');
    }

    /**
     * Render summative exam detail page.
     * @param array $data data to render
     */
    public function renderSummativeDetail(array $data): void
    {
        $langpack = new langpack();
        $strings = $langpack->get_all_strings($this->langcomponent);
        $render = new render($this->config);
        $render->render($data, $strings, 'admin/statistics/summative_detail.html');
    }

    /**
     * Get month summary data
     * @param string $month the month - padded integer representation i.e. 05 = May
     * @param integer $month_paper_no number of papers in month
     * @param integer $month_papers_unused number of unused papers in month
     * @param integer $month_student_no number of students on papers in month
     * @param integer $month_min minimum number of students on a paper in a month
     * @param integer $month_max maximum number of students on a paper in a month
     * @param integer $current_year academic year
     * @return array
     */
    public function generateMonth(
        string $month,
        int $month_paper_no,
        int $month_papers_unused,
        int $month_student_no,
        int $month_min,
        int $month_max,
        int $current_year
    ): array {
        $studentsperpaper = 0;
        $numberstudents = 0;
        $link = '';
        $linklabel = '';
        if ($month_paper_no > 0) {
            $link = 'summative_stats_detail.php?calyear=' . $current_year . '&month=' . $month;
            $linklabel = $this->getDisplayMonth($month);
            $studentsperpaper = round($month_student_no / $month_paper_no, 1);
            $numberstudents = number_format($month_student_no);
        }
        return array(
            'link' => $link,
            'linklabel' => $linklabel,
            'paperno' => $month_paper_no,
            'paperunusedno' => $month_papers_unused,
            'studentsperpaper' => $studentsperpaper,
            'monthmin' => $month_min,
            'monthmax' => $month_max,
            'numberstudents' => $numberstudents,
        );
    }

    /**
     * Generate lab information to render.
     * @param array $labsarray the labs in use on all papers
     * @return array
     */
    public function generateLabStats(array $labsarray): array
    {
        $lab_count = array();
        foreach ($labsarray as $labs) {
            $lab_list = explode(',', $labs);
            foreach ($lab_list as $labID) {
                if ($labID != '') {
                    if (isset($lab_count[$labID])) {
                        $lab_count[$labID]++;
                    } else {
                        $lab_count[$labID] = 1;
                    }
                }
            }
        }
        $result = $this->db->prepare('SELECT id, name FROM labs ORDER BY name');
        $result->execute();
        $result->store_result();
        $result->bind_result($id, $name);
        $data = array();
        while ($result->fetch()) {
            if (isset($lab_count[$id])) {
                $data[$name] = $lab_count[$id];
            } else {
                $data[$name] = 0;
            }
        }
        $result->close();
        return $data;
    }

    /**
     * Get the month to display
     * @param string $month - padded integer representation i.e. 05 = May
     * @return mixed
     */
    public function getDisplayMonth(string $month): string
    {
        $langpack = new langpack();
        $string = $langpack->get_all_strings($this->langcomponent);
        $month_names = array(
            'january',
            'february',
            'march',
            'april',
            'may',
            'june',
            'july',
            'august',
            'september',
            'october',
            'november',
            'december'
        );
        return $string[$month_names[$month - 1]];
    }
}
