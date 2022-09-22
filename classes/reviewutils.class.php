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
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class ReviewUtils
{
    public static function is_external_on_paper($externalID, $paperID, $db)
    {
        $on_paper = false;

        $result = $db->prepare("SELECT properties_reviewers.id FROM properties_reviewers, feedback_release WHERE properties_reviewers.paperID = feedback_release.paper_id AND feedback_release.type = 'external_examiner' AND paperID = ? AND properties_reviewers.reviewerID = ? AND properties_reviewers.type = 'external'");
        $result->bind_param('ii', $paperID, $externalID);
        $result->execute();
        $result->store_result();
        $result->bind_result($id);
        if ($result->num_rows() > 0) {
            $on_paper = true;
        } else {
            $on_paper = false;
        }
        $result->close();

        return $on_paper;
    }

    public static function get_past_papers($externalID, $db)
    {
        $config = Config::get_instance();
        $released_papers = array();

        $result = $db->prepare("SELECT properties.property_id, paper_title, crypt_name, DATE_FORMAT(start_date, '" . $config->get('cfg_long_date_time') . "') FROM properties, properties_reviewers, feedback_release WHERE properties.property_id = properties_reviewers.paperID AND end_date < NOW() AND properties_reviewers.paperID = feedback_release.paper_id AND feedback_release.type = 'external_examiner' AND properties_reviewers.reviewerID = ? AND properties_reviewers.type = 'external' ORDER BY end_date DESC");
        $result->bind_param('i', $externalID);
        $result->execute();
        $result->store_result();
        $result->bind_result($paperID, $paper_title, $crypt_name, $start_date);
        while ($result->fetch()) {
            $released_papers[$paperID] = array('paper_title' => $paper_title, 'crypt_name' => $crypt_name, 'start_date' => $start_date);
        }
        $result->close();

        return $released_papers;
    }
}
