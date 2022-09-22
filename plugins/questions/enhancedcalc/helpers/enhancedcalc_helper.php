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
 * Marks all Calculation questions for a summative paper.
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once dirname(__DIR__) . '/enhancedcalc.class.php';

function enhancedcalc_remark($paper_type, $paper_id, $q_id, $settings, $db, $mode = 'unmarked')
{
    $status = array(-13 => 0, -12 => 0, -11 => 0, -10 => 0, -9 => 0, -8 => 0, -7 => 0, -6 => 0, -5 => 0, -4 => 0, -3 => 0, -2 => 0, -1 => 0, 0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0);
    $configObject = Config::get_instance();
    $enhancedcalc = new EnhancedCalc($configObject);
    $data['settings'] = $settings;
    $data['q_id'] = $q_id;
    $enhancedcalc->load($data);

    $mode_sql = ($mode == 'all') ? '' : ' AND mark IS NULL';

    $result = $db->prepare("SELECT log{$paper_type}.id, user_answer FROM log{$paper_type}, log_metadata WHERE log{$paper_type}.metadataID = log_metadata.id AND q_id = ? AND paperID = ? {$mode_sql}");
    $result->bind_param('ii', $q_id, $paper_id);
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $user_answer);
    while ($result->fetch()) {
        $enhancedcalc->set_useranswer($user_answer);
        $returnarray = $enhancedcalc->calculate_user_mark();
        $status[$returnarray]++;

        if ($returnarray !== Q_MARKING_UNMARKED and $returnarray !== Q_MARKING_ERROR) {
            // Save the extra data back into the log record.
            $sql = "UPDATE log{$paper_type} set mark = ?, adjmark = ?, totalpos = ?, user_answer = ? WHERE id = ? LIMIT 1";
            $storemark = $db->prepare($sql);
            $new_useranswerstring = $enhancedcalc->useranswer_to_string();
            $totalpos = $enhancedcalc->calculate_question_mark();
            $storemark->bind_param('dddsi', $enhancedcalc->qmark, $enhancedcalc->qmark, $totalpos, $new_useranswerstring, $id);
            $storemark->execute();
        }

        // Log returned state.
        $sql = "UPDATE log{$paper_type} set errorstate = ? WHERE id = ? LIMIT 1";
        $storeerror = $db->prepare($sql);
        $storeerror->bind_param('ii', $returnarray, $id);
        $storeerror->execute();
    }
    $result->close();

    return $status;
}
