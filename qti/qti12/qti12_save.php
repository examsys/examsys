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

// TODO: Status should be from struct
// TODO: Heading and notes should be stuck in the question materia

class IE_qti12_Save extends IE_Main
{
    public $data;
    public $params;

    // main save function
    public function Save($params, &$data)
    {
        global $string;

        echo "<h4>{$string['params']}</h4>";
        print_p($params);
        echo "<h4>{$string['generaldebuginfo']}</h4>";

        global $REPLACEMEuserIDold;
        $userObj = UserObject::get_instance();
        $userID = $userObj->get_user_ID();
        $data->ownerID = $userID;

        $this->data =& $data;
        $this->params =& $params;

        $this->ll = array();
        for ($i = 1; $i < 27; $i++) {
            $varletter = chr(ord('A') + $i - 1);
            $this->ll[$i] = $varletter;
        }

        // paper mode
        if (count($data->papers) > 0) {
            foreach ($data->papers as & $paper) {
                //print_p($paper);

                $this->output = $this->DoHeader();
                $this->output .= "\t<assessment title='" . $paper->paper_title . "' ident='" . $paper->load_id . "'>\n";
                if ($paper->rubric) {
                    $this->output .= "\t\t<rubric><![CDATA[" . $paper->rubric . "]]></rubric>\n";
                }
                foreach ($paper->screens as $id => & $screen) {
                    $this->output .= "\t\t<section title='Screen $id' ident='$id'>\n";
                    foreach ($screen->question_ids as $q_id) {
                        $question = FindQuestion($data->questions, $q_id);
                        if ($question) {
                            $this->OutputQuestion($question);
                        } else {
                            $this->AddError("Screen $id references questions $q_id which doesnt exist");
                        }
                    }
                    $this->output .= "\t\t</section>\n";
                }

                $this->output .= "\t</assessment>\n";
                $this->output .= sprintf("</questestinterop>\n");

                $filename = $params->base_dir . $params->dir . '/paper-' . $paper->load_id . '.xml';
                file_put_contents($filename, $this->output);
                //$data->files[$paper->paper_title] = $filename;

                $data->files[] = new ST_File('paper-' . $paper->load_id . '.xml', $paper->paper_title, $params->dir);
            }
        } else { // question mode
            $this->output = $this->DoHeader();

            // this needs a lot more work on this function
            foreach ($data->questions as $question) {
                $this->OutputQuestion($question);
            }

            $this->output .= sprintf("</questestinterop>\n");

            $filename = $params->base_dir . $params->dir . '/questions.xml';
            file_put_contents($filename, $this->output);
            $data->files[] = new ST_File('questions.xml', 'Questions', $params->dir);
        }

        echo '<h4>QTI Output</h4>';
        echo '<pre>';
        echo htmlentities($this->output);
        echo '</pre>';
    }

    public function OutputQuestion(&$question)
    {
        if ($question->media) {
            $this->data->files[] = new ST_File($question->media, $question->media, $this->params->dir, 'image');
        }

        if ($question->type == 'blank') {
            $this->SaveBlank($question);
        } elseif ($question->type == 'enhancedcalc') {
            $this->SaveEnhancedCalc($question);
        } elseif ($question->type == 'dichotomous') {
            $this->SaveDichotomous($question);
        } elseif ($question->type == 'extmatch') {
            $this->SaveExtMatch($question);
        } elseif ($question->type == 'hotspot') {
            $this->SaveHotspot($question);
        } elseif ($question->type == 'info') {
            $this->SaveInfo($question);
        } elseif ($question->type == 'labelling') {
            $this->SaveLabelling($question);
        } elseif ($question->type == 'likert') {
            $this->SaveLikert($question);
        } elseif ($question->type == 'matrix') {
            $this->SaveMatrix($question);
        } elseif ($question->type == 'mcq') {
            $this->SaveMcq($question);
        } elseif ($question->type == 'mrq') {
            $this->SaveMrq($question);
        } elseif ($question->type == 'rank') {
            $this->SaveRank($question);
        } elseif ($question->type == 'textbox') {
            $this->SaveTextbox($question);
        } elseif ($question->type == 'true_false') {
            $this->SaveTrueFalse($question);
        } else {
            $this->AddError('Question type ' . $question->type . ' not yet supported', $question->load_id);
        }
    }

    public function MakeQuestionHeader(&$question, $scenario = true, $image = true)
    {
        $configObject = Config::get_instance();
        $cfg_web_root = $configObject->get('cfg_web_root');
        $output = '';

        if (trim($question->theme)) {
            $output .= "
			<material label='theme'>
				<mattext texttype='text/html'><![CDATA[<font size='+2' color='#316ac5'>" . $question->theme . '</font>]]></mattext>
			</material>';
        }

        if (trim($question->notes)) {
            $output .= "
			<material label='notes'>
				<matimage imagtype='image/gif' uri='notes_icon.gif'/>
				<mattext texttype='text/html'><![CDATA[<font color='#c00000'> <b>NOTE:</b> " . $question->notes . '</font>]]></mattext>
			</material>';
            if (!file_exists($this->params->base_dir . '/' . $this->params->dir . '/' . 'notes_icon.gif')) {
                copy($cfg_web_root . 'artwork/notes_icon.gif', $this->params->base_dir . '/' . $this->params->dir . '/' . 'notes_icon.gif');
            }
            $this->data->files[] = new ST_File('notes_icon.gif', 'notes_icon.gif', $this->params->dir, 'image');
        }

        if ($scenario && !empty($question->scenario) && mb_strlen(trim($question->scenario)) > 0) {
            $output .= "
			<material label='scenario'>
				<mattext texttype='text/html'><![CDATA[" . $question->scenario . ']]></mattext>
			</material>';
        }

        if ($image && !empty($question->media)) {
            $output .= "
			<material label='media'>
				<matimage imagtype='" . $question->media_type . "' uri='" . $question->media . "' label='" . $question->media_alt . "'/>
			</material>";
        }

        if (trim($question->leadin)) {
            $output .= "
			<material label='leadin'>
				<mattext texttype='text/html'><![CDATA[" . $question->leadin . ']]></mattext>
			</material>';
        }

        $title = $question->leadin;

        if ($output == '') {
            $output = '<p></p>';
        }

        $title = StripForTitle($title);

        return array($output, $title);
    }

    public function SaveBlank(&$question)
    {
        // format the text for the question
        list($headertext, $title) = $this->MakeQuestionHeader($question, false);

        //echo "Doing SaveBlank - " . $question->displaymode . "<br>";
        $ob = new OB();
        $ob->ClearAndSave();
        if (mb_strtolower($question->displaymode) == 'dropdown') {
            // export as a Select a Blank so QMP imports correctly
            $type = 'Select a Blank';
            include 'qti12/tmpl/blank-dropdown.php';
        } else {
            // export as a Fill in Blanks so QMP imports correctly
            $type = 'Fill in Blanks';
            if (mb_strtolower($question->score_method) == 'mark per question') {
                include 'qti12/tmpl/blank-textentry-mark-per-question.php';
            } else {
                include 'qti12/tmpl/blank-textentry.php';
            }
        }
        $this->output .= $ob->GetContent();
        $ob->Restore();
    }

    /**
     * Save 'calculation' question type to QTI XML
     * @param ST_Question_Calculation $question Reference to the question object
     * @author Adam Clarke, Rob Ingram, Simon Atack
     */
    public function SaveEnhancedCalc(&$question)
    {
        if (!isset($configObject)) {
            $configObject = Config::get_instance();
        }
        $cfg_web_root = $configObject->get('cfg_web_root');
        require_once($cfg_web_root . '/plugins/questions/enhancedcalc/enhancedcalc.class.php');

        $enhancedcalc = new EnhancedCalc($configObject);
        $enhancedcalc->load($question);


        // replace all variables in leadin with randomly generated values
        $enhancedcalc->generate_variables();
        $real_answer = $enhancedcalc->get_real_answer();
        $enhancedcalc->add_to_useranswer('uans', $real_answer);
        $uansdata = $enhancedcalc->get_uans_data();


        $question->origleadin = $question->leadin;
        // format the text for the question
        $q_text = $enhancedcalc->replace_leadin(false);

        $question->feedback = $enhancedcalc->replace_vars($question->feedback);


        //echo $q_text."<BR>";

        $question->leadin = $q_text;

        list($headertext, $title) = $this->MakeQuestionHeader($question);
        $type = 'Calculation';



        $ob = new OB();
        $ob->ClearAndSave();
        include 'qti12/tmpl/enhancedcalc.php';

        $this->output .= $ob->GetContent();
        $ob->Restore();
    }

    public function SaveDichotomous(&$question)
    {
        // list of score methods:
        /*
        TF_NegativeAbstain - True/False/Abstain (Negative Marking -1)
        TF_NegativeAbstainHalf - True/False/Abstain (Negative Marking -0.5)
        TF_Positive - True/False
        YN_NegativeAbstain - Yes/No/Abstain (Negative Marking -1)
        YN_Positive - Yes/No
        */
        // generate the marking and text to be used
        $hasab = false;
        $true = 'True';
        $false = 'False';

        if ($question->display_method == 'TF_NegativeAbstain') {
            $hasab = 1;
        } elseif ($question->display_method == 'TF_Positive') {
            // default
        } elseif ($question->display_method == 'YN_NegativeAbstain') {
            $true = 'Yes';
            $false = 'No';
            $hasab = 1;
        } elseif ($question->display_method == 'YN_Positive') {
            $true = 'Yes';
            $false = 'No';
        }

        // header stuff
        list($headertext, $title) = $this->MakeQuestionHeader($question);
        $type = 'Dichotomous';

        $ob = new OB();
        $ob->ClearAndSave();
        if (mb_strtolower($question->score_method) == 'mark per question') {
            $type = 'Dichotomous - All options must be correct';
            include 'qti12/tmpl/dichotomous-mark-per-question.php';
        } else {
            include 'qti12/tmpl/dichotomous.php';
        }
        $this->output .= $ob->GetContent();
        $ob->Restore();

        foreach ($question->options as $option) {
            if ($option->media) {
                $this->data->files[] = new ST_File($option->media, $option->media, $option->params->dir, 'image');
            }
        }
    }

    public function SaveExtMatch(&$question)
    {
        // format the text for the question
        $this->AddWarning('QMP Cannot import extended matching questions correctly, it cannot handle the multiple select options', $question->load_id);

        list($headertext, $title) = $this->MakeQuestionHeader($question);

        $type = 'Extended Matching';
        $ob = new OB();
        $ob->ClearAndSave();
        if (mb_strtolower($question->score_method) == 'mark per question') {
            $type = 'Ext Match - All options must be correct';
            include 'qti12/tmpl/extmatch-mark-per-question.php';
        } else {
            include 'qti12/tmpl/extmatch.php';
        }
        $this->output .= $ob->GetContent();
        $ob->Restore();

        if ($question->media) {
            $this->data->files[] = new ST_File($question->media, $question->media, $this->params->dir, 'image');
        }

        foreach ($question->scenarios as $scenarios) {
            if ($scenarios->media) {
                $this->data->files[] = new ST_File($scenarios->media, $scenarios->media, $scenarios->params->dir, 'image');
            }
        }
    }

    // TODO
    public function SaveHotspot(&$question)
    {
        // format the text for the question
        list($headertext, $title) = $this->MakeQuestionHeader($question, true, false);

        $type = 'Hotspot';
        $ob = new OB();
        $ob->ClearAndSave();
        include 'qti12/tmpl/hotspot.php';
        $this->output .= $ob->GetContent();
        $ob->Restore();
    }

    public function SaveInfo(&$question)
    {
        // format the text for the question
        list($headertext, $title) = $this->MakeQuestionHeader($question);

        $type = 'Explanation';
        $ob = new OB();
        $ob->ClearAndSave();
        include 'qti12/tmpl/info.php';
        $this->output .= $ob->GetContent();
        $ob->Restore();
    }

    // TODO
    public function SaveLabelling(&$question)
    {
        // format the text for the question
        list($headertext, $title) = $this->MakeQuestionHeader($question, true, false);

        $type = 'Labelling';
        $ob = new OB();
        $ob->ClearAndSave();
        include 'qti12/tmpl/labelling.php';
        $this->output .= $ob->GetContent();
        $ob->Restore();
    }

    public function SaveLikert(&$question)
    {
        // format the text for the question
        list($headertext, $title) = $this->MakeQuestionHeader($question);

        $type = 'Likert Scale';
        $ob = new OB();
        $ob->ClearAndSave();
        include 'qti12/tmpl/likert.php';
        $this->output .= $ob->GetContent();
        $ob->Restore();
    }

    public function SaveMatrix(&$question)
    {
        // NO FEEDBACK ON MATRIX!!!

        // format the text for the question
        list($headertext, $title) = $this->MakeQuestionHeader($question);
        $max_score = count($question->scenarios);

        $type = 'Matrix';
        $ob = new OB();
        $ob->ClearAndSave();
        if (mb_strtolower($question->score_method) == 'mark per question') {
            $type = 'Matrix - Marks per Question';
            include 'qti12/tmpl/matrix-mark-per-question.php';
        } else {
            include 'qti12/tmpl/matrix.php';
        }
        $this->output .= $ob->GetContent();
        $ob->Restore();
    }

    public function SaveMcq(&$question)
    {
        // fairly sure this is exporting correctly, feedback for pos + neg ok,
        // all options listed ok

        // format the text for the question
        list($headertext, $title) = $this->MakeQuestionHeader($question);

        $type = 'Multiple Choice';
        $ob = new OB();
        $ob->ClearAndSave();
        include 'qti12/tmpl/mcq.php';
        $this->output .= $ob->GetContent();
        $ob->Restore();

        foreach ($question->options as $option) {
            if ($option->media) {
                $this->data->files[] = new ST_File($option->media, $option->media, $this->params->dir, 'image');
            }
        }
    }

    public function SaveTrueFalse(&$question)
    {
        // fairly sure this is exporting correctly, feedback for pos + neg ok,
        // all options listed ok

        // format the text for the question
        list($headertext, $title) = $this->MakeQuestionHeader($question);

        $type = 'True False';
        $ob = new OB();
        $ob->ClearAndSave();
        include 'qti12/tmpl/true_false.php';
        $this->output .= $ob->GetContent();
        $ob->Restore();

        foreach ($question->options as $option) {
            if ($option->media) {
                $this->data->files[] = new ST_File($option->media, $option->media, $this->params->dir, 'image');
            }
        }
    }

    public function SaveMrq(&$question)
    {
        // QMP doesnt pay attention
        // to the maxnumber field in render_choice so allows all options to be checked
        // spits out valid QTI format, but QMP doesnt read it correctly (or export it
        // correctly for that matter)

        $this->AddWarning('QMP doesnt correctly import the maximum number of options from QTI', $question->load_id);

        list($headertext, $title) = $this->MakeQuestionHeader($question);

        // work out how many correct answers we have
        $maxanswers = 0;
        $negmarking = false; //is it negativly marked
        foreach ($question->options as $option) {
            if ($option->is_correct) {
                $maxanswers++;
            }
            if ($option->marks_incorrect < 0) {
                $negmarking = true;
            }
        }

        // use different template depending on marking type
        // current marking types - allnegative, selectedpositive, allitemscorrect
        // marking type other not currently supported

        $ob = new OB();
        $ob->ClearAndSave();

        // 1 mark per correct answer, negative for wrong ones. 4 item question
        // with 2 correct answers will result in following
        // 2 correct answers - 4 marks
        // 2 incorrect answers - -4 marks
        if (mb_strtolower($question->score_method) == 'mark per option' and $negmarking == true) {
            $type = 'Multiple Response - N Mark per Option (with Negative Marking)';
            include 'qti12/tmpl/mrq-mark-per-option-negative.php';
        }

        // multiple marks for question - 1 mark per positive, should only be able to
        // select same no of options as correct answers but not in QMP as its broken
        if (mb_strtolower($question->score_method) == 'mark per option' and $negmarking == false) {
            $type = 'Multiple Response - N Mark per Option (with Negative Marking)';
            include 'qti12/tmpl/mrq-mark-per-option.php';
        }
        // results and feedback for 1 mark for all items correcte, should only be able to
        // select same no of options as correct answers but not in QMP as its broken
        if (mb_strtolower($question->score_method) == 'mark per question') {
            $type = 'Multiple Response - All options must be correct';
            include 'qti12/tmpl/mrq-mark-per-question.php';
        }

        // other - 1 mark per correct, no maximum number of items, and other box.
        // NOT WORKING
        if (mb_strtolower($question->score_method) == 'other') {
            $type = 'Multiple Response - 1 mark per True Option with Other';
            include 'qti12/tmpl/mrq-other.php';
        }
        $this->output .= $ob->GetContent();
        $ob->Restore();

        foreach ($question->options as $option) {
            if ($option->media) {
                $this->data->files[] = new ST_File($option->media, $option->media, $this->params->dir, 'image');
            }
        }
    }

    // DONE
    public function SaveRank(&$question)
    {
        // format the text for the question
        list($headertext, $title) = $this->MakeQuestionHeader($question);

        //build list of options
        $optlist = array();

        foreach ($question->options as $option) {
            if ($option->order == '') {
                $option->order = 0;
            }
            $option->order = intVal($option->order);
            $optlist[$option->order] = OrderToStr($option->order);
        }
        $optlist[9990] = 'N/A';

        $question->optlist = $optlist;

        // different template for each type of marking
        $ob = new OB();
        $ob->ClearAndSave();

        if (mb_strtolower($question->score_method) == 'mark per option') {
            $type = 'Ranking - Strict Order';
            include 'qti12/tmpl/rank-strictorder.php';
        }
        if (mb_strtolower($question->score_method) == 'mark per question') {
            $type = 'Ranking - All items correct';
            include 'qti12/tmpl/rank-allitemscorrect.php';
        }
        if (mb_strtolower($question->score_method) == 'allow partial marks') {
            $type = 'Ranking - Strict Order';
            $this->AddWarning("'Partial marks for neighbours' is not a supported marking type, using 'Strict order (mark per option)' instead", $question->load_id);
            include 'qti12/tmpl/rank-strictorder.php';
        }
        if (mb_strtolower($question->score_method) == 'bonusmark') {
            $this->AddWarning("'Correct items with bonus for overall order' is not a supported marking type, using 'Strict order (mark per option)' instead", $question->load_id);
            $type = 'Ranking - Strict Order';
            include 'qti12/tmpl/rank-strictorder.php';
        }
        $this->output .= $ob->GetContent();

        $ob->Restore();
    }

    // DONE
    public function SaveTextBox(&$question)
    {
        // format the text for the question
        list($headertext, $title) = $this->MakeQuestionHeader($question);

        $this->AddWarning('Terms are not exported to QTI with this question type', $question->load_id);

        $type = 'Text Box';
        $ob = new OB();
        $ob->ClearAndSave();
        include 'qti12/tmpl/textbox.php';
        $this->output .= $ob->GetContent();
        $ob->Restore();
    }

    public function GetTDSet($type, $correct, &$question = '')
    {
        $res = new ST_Question_Timedate_set();
        $res->correct = $correct;
        if ($type == 'dd') {
            for (
                $i = 1; $i <= 31; $i++
            ) {
                $res->values[] = sprintf('%02d', $i);
            }
        } elseif ($type == 'MM') {
            for (
                $i = 1; $i <= 12; $i++
            ) {
                $res->values[] = sprintf('%02d', $i);
            }
        } elseif ($type == 'yyyy') {
            for (
                $i = $question->startyear; $i <= $question->endyear; $i++
            ) {
                $res->values[] = sprintf('%02d', $i);
            }
        } elseif ($type == 'hh') {
            for (
                $i = 0; $i < 24; $i++
            ) {
                $res->values[] = sprintf('%02d', $i);
            }
        } elseif ($type == 'mm') {
            for (
                $i = 0; $i < 60; $i++
            ) {
                $res->values[] = sprintf('%02d', $i);
            }
        } elseif ($type == 'ss') {
            for (
                $i = 0; $i < 60; $i++
            ) {
                $res->values[] = sprintf('%02d', $i);
            }
        } elseif ($type == 'MMMM') {
            $res->values[1] = 'Jan';
            $res->values[2] = 'Feb';
            $res->values[3] = 'Mar';
            $res->values[4] = 'Apr';
            $res->values[5] = 'May';
            $res->values[6] = 'Jun';
            $res->values[7] = 'Jul';
            $res->values[8] = 'Aug';
            $res->values[9] = 'Sep';
            $res->values[10] = 'Oct';
            $res->values[11] = 'Nov';
            $res->values[12] = 'Dec';

            $res->correct = $res->values[(int) $res->correct];
        }

        return $res;
    }

    public function DoHeader()
    {
        $output = "<?xml version='1.0' standalone='no'?>\n";
        $output .= "<!DOCTYPE questestinterop SYSTEM 'ims_qtiasiv1p2.dtd'>\n\n";
        $output .= "<questestinterop>\n";

        return $output;
    }
}
