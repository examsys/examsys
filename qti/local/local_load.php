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
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

/* ExamSys Load params list
 *
 * q_ids = list of question ids if type is batch_q or question
 * p_ids = list of paper types if type is batch_q or paper
 *
 */

// TODO : if neg feedback is empty use positive one for both question and options
// TODO : Status is a field in the database, stick it in the struct

require_once '../include/load_config.php';

class IE_Local_Load extends IE_Main
{
    public $type = '';
    public $q_ids = array();
    public $p_ids = array();
    public $params;
    public $statuses = array();

    public function Load($params)
    {
        global $string;

        echo "<h4>{$string['params']}</h4>";
        print_p($params);
        echo "<h4>{$string['othherdebug']}</h4>";

        $this->params = $params;
        // store params in class
        $this->type = $params->type;
        $this->ids = $params->ids;

        // create storage to put loaded data
        $result = new ST_Main();
        if ($this->type == 'question') {
            // for each q in list, load the question
            foreach ($this->ids as $q_id) {
                $question = $this->LoadQuestion($q_id);
                $result->questions[] = $question;
            }
        } else {
            $questions = array();
            foreach ($this->ids as $p_id) {
                $paper = $this->LoadPaper($p_id);

                foreach ($paper->screens as & $screen) {
                    foreach ($screen->question_ids as $q_id) {
                        $questions[$q_id] = $q_id;
                    }
                }

                $result->papers[$paper->load_id] = $paper;
            }

            foreach ($questions as $q_id) {
                $question = $this->LoadQuestion($q_id);
                $result->questions[] = $question;
            }
        }

        return $result;
    }

    public function LoadPaper($p_id)
    {
        $paper_row = array();
        $prop_rows = array();

        // retrieve question row from database
        $db = new Database();
        $db->SetTable('properties');
        $db->AddField('*');
        $db->AddWhere('property_id', $p_id, 'i');
        $paper_row = $db->GetSingleRow();

        // retrieve array of options from database
        $db = new Database();
        $db->SetTable('papers');
        $db->AddField('*');
        $db->AddWhere('paper', $p_id, 'i');
        $db->AddOrder('display_pos');
        $prop_rows = $db->GetMultiRow();

        $paper = new ST_Paper();
        $paper->load_id = $p_id;
        $paper->paper_title = $paper_row['paper_title'];
        $paper->rubric = $paper_row['rubric'];

        foreach ($prop_rows as $row) {
            $screen = $row['screen'];
            $q_id = $row['question'];
            $displypos = $row['display_pos'];

            if (!array_key_exists($screen, $paper->screens)) {
                $paper->screens[$screen] = new ST_Paper_Screen();
            }

            $paper->screens[$screen]->question_ids[$displypos] = $q_id;
        }

        return $paper;
    }

    public function LoadQuestion($q_id)
    {
        global $REPLACEMEuserIDold, $show_debug;

        $userObj = UserObject::get_instance();

        // storage for question data
        $q_row = array();
        $o_rows = array();

        // retrieve question row from database
        $db = new Database();
        $db->SetTable('questions');
        $db->AddField('*');
        $db->AddWhere('q_id', $q_id, 'i');
        $q_row = $db->GetSingleRow();

        // retrieve array of options from database
        $db = new Database();
        $db->SetTable('options');
        $db->AddField('*');
        $db->AddWhere('o_id', $q_id, 'i');
        $db->AddOrder('id_num');
        $o_rows = $db->GetMultiRow();

        // determine q type and create a storage class for correct type
        $q_type = $q_row['q_type'];
        $q_storage = 'ST_Question_' . $q_type;

        $store = new $q_storage();
        $store->type = $q_type;

        // populate base storage fields
        $this->LoadQuestionBase($store, $q_row, $o_rows);

        // populate class specific storage fields
        $funcname = 'LoadQuestion' . $q_type;
        call_user_func(array($this, $funcname), $store, $q_row, $o_rows);

        // display some debug data
        print_p($q_row);
        print_p($o_rows, true, 100);

        // insert track changes record
        if ($show_debug != true) {
            $track = array();
            $track['type'] = 'QTI Export';
            $track['typeID'] = $q_row['q_id'];
            $track['editor'] = $userObj->get_user_ID();
            $track['new'] = 'Exported to QTI file';
            $track['part'] = 'all';
            $track['changed'] = date('Y-m-d H:i:s');

            $db->InsertRow('track_changes', 'id', $track);
        }
        // return question
        return $store;
    }

    public function LoadQuestionBase($store, $q_row, $o_rows)
    {
        // store id that we loaded as so can be referenced later
        $store->load_id = $q_row['q_id'];

        // load in things common to all questions
        $store->leadin = $q_row['leadin'];
        $store->theme = $q_row['theme'];
        $store->notes = $q_row['notes'];
        $store->q_group = isset($q_row['q_group']) ? $q_row['q_group'] : '';
        $store->bloom = $q_row['bloom'];
        $store->score_method = $q_row['score_method'];

        if ($q_row['ownerID'] > 0) {
            $store->author = GetAuthorName($q_row['ownerID']);
        }

        // Get keywords for question
        $db = new Database();
        $db->SetTable('keywords_user', 'ku');
        $db->AddField('keyword');
        $db->AddInnerJoin('keywords_question', 'kq', 'id', 'keywordID');
        $db->AddWhere('kq.q_id', $q_row['q_id'], 'i');
        $o_rows = $db->GetMultiRow();

        $keywords = array();
        if (count($o_rows) > 0) {
            $keywords[] = $o_rows[0]['keyword'];
            for ($i = 1; $i < count($o_rows); $i++) {
                $keywords[] = $o_rows[$i]['keyword'];
            }
        }

        $store->keywords = $keywords;

        // Standard media
        $db = new Database();
        $db->SetTable('media', 'm');
        $db->AddField('source');
        $db->AddField('width');
        $db->AddField('height');
        $db->AddField('alt');
        $db->AddInnerJoin('questions_media', 'qm', 'id', 'mediaid');
        $db->AddWhere('qm.qid', $q_row['q_id'], 'i');
        $m_rows = $db->GetSingleRow();
        $this->AddMedia($store, $m_rows['source'], $m_rows['width'], $m_rows['height'], $m_rows['alt']);

        $store->status = $this->statuses[$q_row['status']];
    }

    public function LoadQuestionBlank($store, $q_row, $o_rows)
    {
        // basic things
        $store->displaymode = $q_row['score_method'];
        $store->feedback = $q_row['correct_fback'];

        // load option text
        $q = $o_rows[0]['option_text'];

        $question = array();

        // parse question into some more meaningful format
        $blankno = 1;
        while (mb_stripos($q, '[blank]') > 0) {
            // create new indentifier for the blank option
            $blankid = '%BLANK_' . $blankno . '%';

            // locate [blank][/blank] segment
            $offset = mb_stripos($q, '[blank]');
            $endoffset = mb_stripos($q, '[/blank]');

            // pull out list of options and replace segment with blankid created arlier
            $midpart = mb_substr($q, $offset + 7, $endoffset - $offset - 7);
            $question[] = mb_substr($q, 0, $offset);
            $question[] = $blankid;
            $q = mb_substr($q, $endoffset + 8);

            // process the options
            $optlist = explode(',', $midpart);

            // array to store resulting STQ_Blank_Option classes
            $options = array();

            $optionno = 1;
            foreach ($optlist as $opt) {
                $opt = trim($opt);
                if ($opt) {
                    // create new option
                    $curopt = new STQ_Blank_Option();
                    $curopt->display = $opt;

                    $curopt->marks_correct = $o_rows[0]['marks_correct'];
                    $curopt->marks_incorrect = $o_rows[0]['marks_incorrect'];
                    $curopt->marks_partial = $o_rows[0]['marks_partial'];


                    // if display mode is dropdown, only first is correct answer
                    if ($store->displaymode == 'dropdown') {
                        $curopt->correct = $optionno == 1 ? 1 : 0;

                        // if textboxes, each item is a correct answer
                    } elseif ($store->displaymode == 'textboxes') {
                        $curopt->correct = 1;
                    }
                    $options[] = $curopt;
                    $optionno++;
                }
            }
            $store->options[$blankid] = array();
            foreach ($options as $option) {
                $store->options[$blankid][] = $option;
            }

            $blankno++;
        }

        if ($q) {
            $question[] = $q;
        }

        $store->question = $question;
    }

    public function LoadQuestionenhancedcalc($store, $q_row, $o_rows)
    {
        // fiarly sure this is ok
        $store->scenario = $q_row['scenario'];
        $store->feedback = $q_row['correct_fback'];
        $store->q_type = 'enhancedcalc';

        $settingsdecoded = json_decode($q_row['settings'], true);

        $store->marks_correct = $settingsdecoded['marks_correct'];
        $store->marks_incorrect = $settingsdecoded['marks_incorrect'];
        $store->marks_partial = $settingsdecoded['marks_partial'];
        $store->settings = $q_row['settings'];
    }

    public function LoadQuestionCalculation($store, $q_row, $o_rows)
    {
        // fiarly sure this is ok
        $store->scenario = $q_row['scenario'];
        $store->feedback = $q_row['correct_fback'];
        $store->formula = $o_rows[0]['correct'];

        list($store->decimals, $store->tolerance, $store->units) = explode(',', $q_row['display_method']);

        $calcvar = 0;
        foreach ($o_rows as $o_row) {
            $calcvarletter = chr(ord('A') + $calcvar);
            $var = new STQ_Calc_Vars();
            list($var->min, $var->max, $var->inc, $var->dec) = explode(',', $o_row['option_text']);
            $store->variables[$calcvarletter] = $var;

            $store->marks_correct = $o_row['marks_correct'];
            $store->marks_incorrect = $o_row['marks_incorrect'];
            $store->marks_partial = $o_row['marks_partial'];


            $calcvar++;
        }
    }

    public function LoadQuestionDichotomous($store, $q_row, $o_rows)
    {
        // basic stuff
        $store->scenario = $q_row['scenario'];
        $store->feedback = $q_row['correct_fback'];
        $store->score_method = $q_row['score_method'];
        $store->display_method = $q_row['display_method'];

        // for each options, create a STQ_Dic_Options
        $optionno = 1;
        foreach ($o_rows as $o_row) {
            $options = new STQ_Dic_Options();
            $options->text = $o_row['option_text'];
            $options->iscorrect = mb_strtolower($o_row['correct']) == 't' ? 1 : 0;
            $options->fb_correct = $o_row['feedback_right'];
            $options->fb_incorrect = $o_row['feedback_wrong'];
            if (!$options->fb_incorrect) {
                $options->fb_incorrect = $options->fb_correct;
            }

            $db = new Database();
            $db->SetTable('media', 'm');
            $db->AddField('source');
            $db->AddField('width');
            $db->AddField('height');
            $db->AddField('alt');
            $db->AddInnerJoin('options_media', 'om', 'id', 'mediaid');
            $db->AddWhere('om.oid', $o_row['id_num'], 'i');
            $m_rows = $db->GetSingleRow();
            $this->AddMedia($options, $m_rows['source'], $m_rows['width'], $m_rows['height'], $m_rows['alt']);

            $options->marks_correct = $o_row['marks_correct'];
            $options->marks_incorrect = $o_row['marks_incorrect'];

            $store->options[$optionno] = $options;
            $optionno++;
        }
    }

    public function LoadQuestionExtmatch($store, $q_row, $o_rows)
    {
        // get list of possible answers
        $optno = 1;
        foreach ($o_rows as $o_row) {
            $store->optionlist[$optno] = $o_row['option_text'];
            $optno++;
        }

        // split all stuff from q_row into arrays for processing
        $feedbacks = explode('|', $q_row['correct_fback']);
        $medias = array();
        $media_widths = array();
        $medias_heights = array();
        $medias_alts = array();
        $medias_nums = array();
        $tmp_scenarios = explode('|', $q_row['scenario']);

        // Get media for question
        $db = new Database();
        $db->SetTable('media', 'm');
        $db->AddField('source');
        $db->AddField('width');
        $db->AddField('height');
        $db->AddField('alt');
        $db->AddField('num');
        $db->AddInnerJoin('questions_media', 'qm', 'id', 'mediaid');
        $db->AddWhere('qm.qid', $q_row['q_id'], 'i');
        $m_rows = $db->GetMultiRow();

        foreach ($m_rows as $row) {
            $medias[$row['num']] = $row['source'];
            $medias_widths[$row['num']] = $row['width'];
            $medias_heights[$row['num']] = $row['height'];
            $medias_alts[$row['num']] = $row['alt'];
        }

        $scenarios = array();
        $i = 0;
        foreach ($tmp_scenarios as $s) {
            if ($s != '' or isset($medias[$i + 1])) {
                $scenarios[] = $s;
            }
            $i++;
        }

        $correct = explode('|', $o_rows[0]['correct']);

        // for all the arrays made, create scenarios
        $scenariono = 1;
        for ($i = 0; $i < count($scenarios); $i++) {
            $ems = new STQ_Extm_Scenario();
            $ems->stem = $scenarios[$i];

            if (isset($medias[$i + 1])) {
                $this->AddMedia($ems, $medias[$i + 1], $media_widths[$i + 1], $medias_heights[$i + 1], $medias_alts[$i + 1]);
            }

            $ems->marks_correct = $o_rows[0]['marks_correct'];
            $ems->marks_incorrect = $o_rows[0]['marks_incorrect'];
            $ems->marks_partial = $o_rows[0]['marks_partial'];

            $ems->feedback = (empty($feedbacks[$i])) ? '' : $feedbacks[$i];
            $ems->correctans = explode('$', $correct[$i]);

            $store->scenarios[$scenariono] = $ems;
            $scenariono++;
        }
    }

    // TODO - Does this deal with multi-layered hotspot questions?
    public function LoadQuestionHotspot($store, $q_row, $o_rows)
    {

        $store->scenario = $q_row['scenario'];
        $store->feedback = $q_row['correct_fback'];

        $hotspots = $o_rows[0]['correct'];

        $store->raw_option = $hotspots;
        $hotspots = explode('|', $hotspots);
        $spotcount = 0;
        foreach ($hotspots as $hotspot) {
            $parts = explode('~', $hotspot);
            array_shift($parts);
            array_shift($parts);
            array_pop($parts);
            $num = 0;
            for ($i = 0; $i < count($parts) - 1; $i += 3) {
                $type = $parts[$i];
                $coords = $parts[$i + 1];
                $coords = explode(',', $coords);
                $hotspot = new STQ_Hotspot_Spot();
                $hotspot->type = $type;
                foreach ($coords as $coord) {
                    $coord = hexdec($coord);
                    $hotspot->coords[] = $coord;
                }
                $hotspot->marks_correct = $o_rows[0]['marks_correct'];
                $hotspot->marks_incorrect = $o_rows[0]['marks_incorrect'];
                $hotspot->marks_partial = $o_rows[0]['marks_partial'];
                if ($hotspot->type != '') {
                    $store->hotspots[$spotcount][$num] = $hotspot;
                }
                $num++;
                $spotcount++;
            }
        }
    }

    public function LoadQuestionInfo($store, $q_row, $o_rows)
    {
        // Info type question has no notes!
        // main info stored in leadin
    }


    public function LoadQuestionLabelling($store, $q_row, $o_rows)
    {
        // 1 - 3/4 pt
        // 2 - 1 pt
        // 3 - 1 1/4 pt
        // 4 - 2 1/4 pt
        // 5 - 3 pt
        // 6 - 4 1/2 pt
        // 7 - 6 pt
        $line_thicknesses = array();
        $line_thicknesses[1] = 0.75;
        $line_thicknesses[2] = 1;
        $line_thicknesses[3] = 1.25;
        $line_thicknesses[4] = 2.25;
        $line_thicknesses[5] = 3;
        $line_thicknesses[6] = 4.5;
        $line_thicknesses[7] = 6;

        $store->scenario = $q_row['scenario'];
        $store->feedback = $q_row['correct_fback'];

        $store->marks_correct = $o_rows[0]['marks_correct'];
        $store->marks_incorrect = $o_rows[0]['marks_incorrect'];
        $store->marks_partial = $o_rows[0]['marks_partial'];

        $data = $o_rows[0]['correct'];
        $store->raw_option = $data;

        $data = explode(';', $data);

        $store->line_color = $data[0];
        $store->line_thickness = $line_thicknesses[$data[1]];
        $store->box_color = $data[2];
        $store->font_size = $data[3];
        $store->font_color = $data[4];
        $store->width = $data[5];
        $store->height = $data[6];
        $store->label_type = $data[7];

        for ($i = 11; $i < count($data); $i++) {
            if (empty($data[$i])) {
                continue;
            }

            $chunk = $data[$i];
            $data3 = explode('$', $data[$i]);
            $arrow = new STQ_Labelling_Arrow();
            $arrow->type = $data3[1];
            $arrow->coords[] = $data3[2];
            $arrow->coords[] = $data3[3];
            $arrow->coords[] = $data3[4];
            $arrow->coords[] = $data3[5];

            $store->arrows[] = $arrow;
        }

        $data2 = explode('|', $data[11]);

        foreach ($data2 as $label) {
            $label = explode('$', $label);

            if (empty($label[4])) {
                continue;
            }
            $lc = new STQ_Labelling_Label();

            $tag = explode('~', $label[4]);

            if (count($tag) == 1) {
                $lc->tag = $tag[0];
                $lc->type = 'text';
            } else {
                $lc->tag = $tag[0];
                $lc->width = $tag[1];
                $lc->height = $tag[2];
                $lc->type = 'img';
            }
            $lc->left = $label[2] - 220;
            $lc->top = $label[3] - 25;

            if ($lc->left < 0) {
                $lc->left = -1;
                $lc->top = -1;
            }

            $store->labels[$label[0]] = $lc;
        }
    }

    public function LoadQuestionLikert($store, $q_row, $o_rows)
    {
        $store->scenario = $q_row['scenario'];

        // options for likert in score method, along with has n/a
        $sm = $q_row['display_method'];

        // extract the last part of the score method and if true has n/a
        $store->hasna = mb_strtolower(mb_substr($sm, mb_strrpos($sm, '|') + 1)) == 'true' ? 1 : 0;

        // trim off the last scoremethod as this stored has n/a
        $sm = mb_substr($sm, 0, mb_strrpos($sm, '|'));

        // store rest of the options in scale
        $opts = explode('|', $sm);
        $i = 1;
        $store->scale = array();
        foreach ($opts as $opt) {
            $store->scale[$i++] = $opt;
        }
    }

    public function LoadQuestionMatrix($store, $q_row, $o_rows)
    {
        // get list of correct values for each of the questions
        $correctvalues = explode('|', $o_rows[0]['correct']);

        // build a list of the top row options
        $topvalueno = 1;
        foreach ($o_rows as $o_row) {
            $option = new STQ_Mcq_Option();
            $option->stem = $o_row['option_text'];
            $option->marks_correct = $o_row['marks_correct'];
            $option->marks_incorrect = $o_row['marks_incorrect'];
            $option->marks_partial = $o_row['marks_partial'];
            $store->options[$topvalueno] = $option;
            $topvalueno++;
        }

        // for all questions down left, create a STQ_Matrix_Scenario
        $scenno = 1;
        $leftvalue = explode('|', $q_row['scenario']);
        foreach ($leftvalue as $left) {
            if ($left != '') {
                $scenario = new STQ_Matrix_Scenario();
                $scenario->scenario = $left;
                // lookup the correct top row option id and store it as the answer
                $scenario->answer = $correctvalues[$scenno - 1];

                $store->scenarios[$scenno] = $scenario;
                $scenno++;
            }
        }
    }

    public function LoadQuestionMcq($store, $q_row, $o_rows)
    {
        // basic stuff
        $store->scenario = $q_row['scenario'];
        $store->fb_correct = $q_row['correct_fback'];
        $store->fb_incorrect = $q_row['incorrect_fback'];
        if (!$store->fb_incorrect) {
            $store->fb_incorrect = $store->fb_correct;
        }
        $store->correct = $o_rows[0]['correct'];

        // for each of the options create an STQ_Mcq_Option
        $optionno = 1;
        foreach ($o_rows as $o_row) {
            $option = new STQ_Mcq_Option();
            $option->stem = $o_row['option_text'];
            $option->marks_correct = $o_row['marks_correct'];
            $option->marks_incorrect = $o_row['marks_incorrect'];
            $option->marks_partial = $o_row['marks_partial'];
            $db = new Database();
            $db->SetTable('media', 'm');
            $db->AddField('source');
            $db->AddField('width');
            $db->AddField('height');
            $db->AddField('alt');
            $db->AddInnerJoin('options_media', 'om', 'id', 'mediaid');
            $db->AddWhere('om.oid', $o_row['id_num'], 'i');
            $m_rows = $db->GetSingleRow();
            $this->AddMedia($option, $m_rows['source'], $m_rows['width'], $m_rows['height']);

            $store->options[$optionno] = $option;
            $optionno++;
        }
    }

    public function LoadQuestiontrue_false($store, $q_row, $o_rows)
    {
        // basic stuff
        $store->scenario = $q_row['scenario'];
        $store->fb_correct = $q_row['correct_fback'];
        $store->fb_incorrect = $q_row['incorrect_fback'];
        if (!$store->fb_incorrect) {
            $store->fb_incorrect = $store->fb_correct;
        }
        $store->correct = $o_rows[0]['correct'];

        // for each of the options create an STQ_Mcq_Option
        $optionno = 1;
        foreach ($o_rows as $o_row) {
            $option = new STQ_Mcq_Option();
            $option->stem = $o_row['option_text'];
            $option->marks_correct = $o_row['marks_correct'];
            $option->marks_incorrect = $o_row['marks_incorrect'];
            $option->marks_partial = $o_row['marks_partial'];
            $db = new Database();
            $db->SetTable('media', 'm');
            $db->AddField('source');
            $db->AddField('width');
            $db->AddField('height');
            $db->AddField('alt');
            $db->AddInnerJoin('options_media', 'om', 'id', 'mediaid');
            $db->AddWhere('om.oid', $o_row['id_num'], 'i');
            $m_rows = $db->GetSingleRow();
            $this->AddMedia($option, $m_rows['source'], $m_rows['width'], $m_rows['height'], $m_rows['alt']);

            $store->options[$optionno] = $option;
            $optionno++;
        }
    }

    public function LoadQuestionMrq($store, $q_row, $o_rows)
    {
        // basic stuff
        $store->scenario = $q_row['scenario'];
        // score method oddness, if type is other, the include other then
        // score method gets set to "1 Mark per True Option"
        $store->score_method = $q_row['score_method'];
        if ($store->score_method == 'other') {
            $store->include_other = true;
        }
        $store->feedback = $q_row['correct_fback'];

        // get a list of all the options
        $optionno = 1;

        foreach ($o_rows as $o_row) {
            $option = new STQ_Mrq_Option();
            $option->stem = $o_row['option_text'];
            // check correct and map to 1/0
            $option->is_correct = mb_strtolower($o_row['correct']) == 'y' ? 1 : 0;
            $option->fb_correct = $o_row['feedback_right'];
            $option->fb_incorrect = $o_row['feedback_wrong'];
            if ($option->fb_incorrect == '') {
                $option->fb_incorrect = $option->fb_correct;
            }

            $option->marks_correct = $o_row['marks_correct'];
            $option->marks_incorrect = $o_row['marks_incorrect'];
            $option->marks_partial = $o_row['marks_partial'];

            $db = new Database();
            $db->SetTable('media', 'm');
            $db->AddField('source');
            $db->AddField('width');
            $db->AddField('height');
            $db->AddField('alt');
            $db->AddInnerJoin('options_media', 'om', 'id', 'mediaid');
            $db->AddWhere('om.oid', $o_row['id_num'], 'i');
            $m_rows = $db->GetSingleRow();
            $this->AddMedia($option, $m_rows['source'], $m_rows['width'], $m_rows['height'], $m_rows['alt']);
            $store->options[$optionno] = $option;
            $optionno++;
        }
    }

    public function LoadQuestionRank($store, $q_row, $o_rows)
    {
        // basic stuff
        $store->scenario = $q_row['scenario'];
        $store->score_method = $q_row['score_method'];
        $store->fb_correct = $q_row['correct_fback'];
        $store->fb_incorrect = $q_row['incorrect_fback'];
        if (!$store->fb_incorrect) {
            $store->fb_incorrect = $store->fb_correct;
        }

        // get a list of options and create a STQ_Rank_Options for em
        $optionno = 1;
        foreach ($o_rows as $o_row) {
            $ranking = new STQ_Rank_Options();
            $ranking->order = $o_row['correct'];
            $ranking->stem = $o_row['option_text'];
            $ranking->marks_correct = $o_row['marks_correct'];
            $ranking->marks_incorrect = $o_row['marks_incorrect'];
            $ranking->marks_partial = $o_row['marks_partial'];
            $store->options[$optionno] = $ranking;
            $optionno++;
        }
    }

    public function LoadQuestionTextbox($store, $q_row, $o_rows)
    {
        // basic stuff
        $store->scenario = $q_row['scenario'];

        // size of text box stored as 100x30 in sm
        $settings = json_decode($q_row['settings']);
        $store->columns = $settings->columns;
        $store->rows = $settings->rows;

        $store->editor = $o_rows[0]['option_text'];
        $store->marks_correct = $o_rows[0]['marks_correct'];
        $store->marks_incorrect = $o_rows[0]['marks_incorrect'];
        $store->feedback = $q_row['correct_fback'];

        // create a list of ; separated terms, stripping out any empty ones?
        // TODO: Should this happen? maybe they want to leave blank ones in?
        $store->terms = explode_no_empty(';', $o_rows[0]['correct']);
    }

    public function LoadQuestionRandom($store, $q_row, $o_rows)
    {
        return "Error: Random questions can't be exported.";
    }

    public function LoadQuestionKeyword_based($store, $q_row, $o_rows)
    {
        return "Error: Keyword-based questions can't be exported.";
    }

    public function LoadQuestionSct($store, $q_row, $o_rows)
    {
        return "Error: SCT questions can't be exported.";
    }

    public function AddMedia(&$question, $media, $width = 0, $height = 0, $alt = '')
    {
        if ($media == '') {
            return;
        }

        $question->media = $media;
        $question->media_width = $width;
        $question->media_height = $height;
        $question->media_alt = $alt;
        $question->media_type = GenerateMediaType($question->media);
        $this->GetMedia($question->media);
    }

    public function GetMedia($filename)
    {
        $mediadirectory = rogo_directory::get_directory('media');
        $fullpath = $mediadirectory->fullpath($filename);
        if (file_exists($fullpath)) {
            copy($fullpath, $this->params->base_dir . $this->params->dir . '/' . $filename);
        }
    }

    public function setStatuses($statuses)
    {
        $this->statuses = $statuses;
    }
}
