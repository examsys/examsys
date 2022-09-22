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

require_once '../include/load_config.php';

// main question object
class ST_QTI12_Question // <item
{
    public $raw_xml;
    public $title; // <item title=
    public $load_id; // <item ident=


    public $wct_questiontype;
    // itemmetadata stuff
    public $qmd_itemtype; // <itemmetadata><qmd_itemtype>
    public $qmd_status = 'Normal'; // <itemmetadata><qmd_status>
    public $qmd_toolvendor; // <itemmetadata><qmd_toolvendor>

    // counted stuff
    public $counts = array(
        'lid' => 0,
        'str' => 0,
        'num' => 0,
        'xy' => 0,
        'grp' => 0,
        'choice' => 0,
        'hotspot' => 0,
        'slider' => 0,
        'fib' => 0,
        'extension' => 0,
        'response' => 0,
        'material' => 0
    );

    // calculate cardinaltiy, if all of lid are ismulti then Multi, if some are ismulti then varies (for extmatch i think)
    public $cardinality = 'Single';

    // label sets - do all sets of labels for each of the responses have the same values in or not??
    // this needs to be 1 for most question type, the only question type that allows multiple sets of answers is blank
    public $labelsets = 1;

    // <presentation>
    // do we have flow data?
    public $hasflow = 0;

    // general question material
    // <material>
    // array of QT_QTI12_Material, as some questions have multiple material sections
    // $questionmats - OBSOLETE!!! REPLACED BY $material
    public $material;

    // <response_lid>
    // array of ST_QTI12_Response
    // key on <response_lid ident=
    public $responses = array();

    public $presentation;
    // </presentation>

    // <resprocessing>

    // <respcondition>
    // array of ST_QTI12_RespCondition
    // key by numbe
    public $respconditions = array();

    // <resprocessing>

    // <itemfeedback>
    // array of ST_QTI12_Itemfeedback
    // key by <itemfeedback ident=
    public $itemfeedback = array();

    public $comments = array();
    public $params = array();

    public function __construct($xml)
    {
        $this->params['KEYWORD'] = array();
        $this->params['VARIABLE'] = array();
        $this->raw_xml = $xml;

        $this->title = (string) $xml->attributes()->title;
        $this->load_id = (string) $xml->attributes()->ident;
        $this->material = new ST_QTI12_Material();

        // load item meta data
        if ($xml->itemmetadata) {
            foreach ($xml->itemmetadata as $th) {
                foreach ($th as $rg) {
                    foreach ($rg as $ef) {
                        if ($ef->fieldlabel == 'wct_questiontype') {
                            $this->wct_questiontype = (string) $ef->fieldentry;
                        }
                    }
                }
            }

            if ($xml->itemmetadata->qmd_itemtype) {
                $this->qmd_itemtype = (string) $xml->itemmetadata->qmd_itemtype;
            }

            if ($xml->itemmetadata->qmd_status) {
                $this->qmd_status = (string) $xml->itemmetadata->qmd_status;
            }

            if ($xml->itemmetadata->qmd_toolvendor) {
                $this->qmd_toolvendor = (string) $xml->itemmetadata->qmd_toolvendor;
            }

            $t = 0;
        }

        $this->LoadComments($xml);
        if ($this->wct_questiontype == 'WCT_JumbledSentence') {
            $ttt = $xml->presentation->flow->response_lid->render_extension->ims_render_object;

            $itemid = 1;

            foreach ($xml->presentation->flow->response_lid->render_extension->ims_render_object->children() as $child1) {
                $name = $child1->getName();
                if ($name == 'material') {
                    $mat = new ST_QTI12_Material();
                    $mat->notrim = 1;
                    $mat->add($child1);
                    $question[] = $mat->GetText(1);
                    $trtrtr = 1;
                    unset($mat);
                }
                if ($name == 'response_label') {
                    $mat = new ST_QTI12_Material();
                    $mat->notrim = 1;
                    $mat->add($child1->material);
                    $string = sprintf('%%BLANK_%d%%', $itemid);
                    $question[] = $string;
                    $optionslk[$itemid] = $mat->GetText(1);
                    $ident1 = (string) $child1->attributes()->ident;
                    $optionslk1[$itemid] = $ident1;
                    $optionslk2[$ident1] = $mat->GetText(1);
                    $trtrtr = 1;
                    unset($mat);
                    $itemid++;
                }
            }

            if ($xml->resprocessing->respcondition->conditionvar->and) {
                foreach ($xml->resprocessing->respcondition->conditionvar->and->children() as $child2) {
                    $ident2 = (string) $child2->attributes()->index;
                    $nm = (string) $child2;
                    $optionslk3[$ident2] = $nm;
                    $optionslk4[$nm] = $ident2;
                }
            }
            if (isset($optionslk3)) {
                foreach ($optionslk1 as $par3 => $child3) {
                    $string = sprintf('%%BLANK_%d%%', $par3);
                    $lk2 = $optionslk3[$par3];
                    $options[$string][] = $optionslk2[$lk2];
                    foreach ($optionslk2 as $par4 => $child4) {
                        if ($par4 != $lk2) {
                            $options[$string][] = $optionslk2[$par4];
                        }
                    }
                }
            }
            $this->optionslk1 = $optionslk1;
            $this->optionslk2 = $optionslk2;
            @$this->optionslk3 = $optionslk3;
            @$this->optionslk4 = $optionslk4;


            @$this->options2 = $options;
            $this->question2 = $question;
            $debpoint = 1;

            $this->LoadPresentation($xml->presentation->flow);
        } else {
            $this->LoadPresentation($xml->presentation);
        }

        if ($xml->resprocessing->respcondition) {
            foreach ($xml->resprocessing as $respch) {
                foreach ($respch->respcondition as $respcondition) {
                    $this->respconditions[] = new ST_QTI12_RespCondition($respcondition);
                }
            }
        }

        // load all the feedback options
        foreach ($xml->itemfeedback as $itemfeedback) {
            $fb = new ST_QTI12_Itemfeedback($itemfeedback);
            $this->itemfeedback[$fb->id] = $fb;
        }

        // QMP FIX:
        // need to validate all id in ST_QTI12_CondVar against the correct ST_QTI12_Response object,
        // if the id isnt found attempt to look it up from the value

        foreach ($this->respconditions as & $respcondition) {
            foreach ($respcondition->conditions as $condition) {
                $value = $condition->value;
                $respid = $condition->respident;

                if (!array_key_exists($respid, $this->responses)) {
                    continue;
                }
                $response = $this->responses[$respid];

                if (!array_key_exists($value, $response->labels)) {
                    foreach ($response->labels as $label) {
                        if (mb_strtolower($label->material->GetText()) == mb_strtolower($value)) {
                            $condition->value = $label->id;
                        }
                    }
                }
            }
        }
    }

    public function LoadComments($xml)
    {
        foreach ($xml->children() as $child) {
            $name = $child->getName();
            if ($name == 'qticomment') {
                $com = (string) $child;
                $this->comments[] = $com;
                if (mb_strpos($com, ':') > 0) {
                    $bits = explode(':', $com, 2);
                    $param = mb_strtoupper($bits[0]);

                    // parameter already exists
                    if (array_key_exists($param, $this->params)) {
                        // if its not an array, make it one
                        if (!is_array($this->params[$param])) {
                            $old = $this->params[$param];
                            $this->params[$param] = array();
                            $this->params[$param][] = $old;
                        }
                        $this->params[$param][] = $bits[1];
                    } else {
                        $this->params[$param] = $bits[1];
                    }
                }
            } else {
                $this->LoadComments($child);
            }
        }
    }

    // <presentation> only 1 per <item>
    public function LoadPresentation($xml)
    {
        if ($xml->flow) {
            $this->hasflow = 1;
            $this->LoadPresentation($xml->flow);
        }

        $this->presentation = $xml;
        $elementno = 1;

        foreach ($xml->children() as $child) {
            $name = $child->getName();

            // if there is question material, load it
            if ($name == 'material') {
                $this->material->add($child, $elementno);
            }

            // load all response_lid elements, followed by all other types
            if ($name == 'response_lid') {
                $resp = new ST_QTI12_Response('lid', $child);
                $resp->order = $elementno;
                $this->responses[$resp->id] = $resp;
            }

            if ($name == 'response_str') {
                $resp = new ST_QTI12_Response('str', $child);
                $resp->order = $elementno;
                $this->responses[$resp->id] = $resp;
            }

            if ($name == 'response_xy') {
                $resp = new ST_QTI12_Response('xy', $child);
                $resp->order = $elementno;
                $this->responses[$resp->id] = $resp;
            }

            if ($name == 'response_num') {
                $resp = new ST_QTI12_Response('num', $child);
                $resp->order = $elementno;
                $this->responses[$resp->id] = $resp;
            }

            if ($name == 'response_grp') {
                $resp = new ST_QTI12_Response('grp', $child);
                $resp->order = $elementno;
                $this->responses[$resp->id] = $resp;
            }

            $elementno++;
        }
    }

    public function CountStuff()
    {
        $count_response = count($this->responses);

        foreach ($this->responses as $response) {
            $this->counts[$response->type]++;
            @$this->counts[$response->render]++;
            $this->counts['response']++;
        }

        $this->counts['material'] = $this->material->count;

        // work out cardinality
        $count_multi = 0;
        foreach ($this->responses as $res) {
            if ($res->ismulti) {
                $count_multi++;
            }
        }

        if ($count_multi == $this->counts['response']) {
            $this->cardinality = 'Multi';
        } elseif ($count_multi > 0) {
            $this->cardinality = 'Varies';
        }

        // are all the sets of labels the same for stuff like matrixes etc
        $this->CompareLabels();
    }

    // verify all sets of lables for each lid are same or not
    public function CompareLabels()
    {
        if ($this->counts['response'] > 1) {
            $allsame = 1;
            $first = true;

            // for all responses compare against the first one
            foreach ($this->responses as $res) {
                // find first set of responses as a baseline, and dont compare as its gonna be the same
                if ($first) {
                    $baselabels = $res->labels;
                    $first = false;
                    continue;
                }

                // get new set of labels
                $itemlabels = $res->labels;

                // check that the count is the same
                if (count($itemlabels) != count($baselabels)) {
                    $allsame = 0;
                    break;
                }

                // if count is same, then all items must match or not all same

                foreach ($itemlabels as $id => $itemlabel) {
                    // array key missing means option aint there!
                    if (!array_key_exists($id, $baselabels)) {
                        $allsame = 0;
                        break;
                    }

                    // compare the data to see if the same
                    if ($baselabels[$id]->material->GetText() != $itemlabel->material->GetText()) {
                        $allsame = 0;
                        break;
                    }
                }

                // break out if not all same
                if (!$allsame) {
                    break;
                }
            }

            $this->labelsets = $allsame;
        }
    }
}

// object to store question parts
class ST_QTI12_Response // <response_
{
    public $id; // <response_lid ident=

    // type of the response_ object
    public $type = 'lid';

    public $material; // <material>
    public $ismulti = 0; // <response_lid rcardinality= // "Multiple" for 1

    public $render = 'choice';
    public $flow = 0;

    // attributes for rebder_choice
    public $shuffle = 0; // <render_choice shuffle="No"
    public $minnumber = 0; // <render_choice minnumber=
    public $maxnumber = 0; // <render_choice maxnumber=

    // attributes for render_fib
    public $fibtype = '';
    public $prompt = '';
    public $rows = 0;
    public $cols = 0;

    public $orderid = 0;
    // attributes for render_slider

    // attributes for render_hotspot

    // <render_choice><response_label
    // ST_QTI12_Label
    // key by <response_label ident=
    public $labels = array();

    public function __construct($type, $xml)
    {

        $this->id = (string) $xml->attributes()->ident;
        $this->type = $type;
        // should we allow multiple answers, ie check instead of radio
        $this->ismulti = mb_strtolower($xml->attributes()->rcardinality) == 'multiple' ? 1 : 0;

        $this->material = new ST_QTI12_Material();
        // get material if available
        if ($xml->material) {
            $this->material->add($xml->material);
        }

        // as far as i can tell only ever 1 choice for each response_lid
        if ($xml->render_choice) {
            $render = 'choice';
            $this->shuffle = mb_strtolower($xml->render_choice->attributes()->shuffle) == 'no' ? 0 : 1;
            $this->minnumber = (string) $xml->render_choice->attributes()->minnumber;
            $this->maxnumber = (string) $xml->render_choice->attributes()->maxnumber;

            $this->LoadRender($xml->render_choice);
        } elseif ($xml->render_hotspot) {
            $this->render = 'hotspot';
            $this->LoadRenderHotspot($xml->render_hotspot);
        } elseif ($xml->render_slider) {
            $this->render = 'slider';
        } elseif ($xml->render_extension) {
            $this->render = 'extension';

            $this->LoadRender($xml->render_extension->ims_render_object);
        } elseif ($xml->render_fib) {
            $this->render = 'fib';

            $this->fibtype = (string) $xml->render_fib->attributes()->fibtype;
            $this->prompt = (string) $xml->render_fib->attributes()->prompt;
            $this->rows = (int) $xml->render_fib->attributes()->rows;
            $this->cols = (int) $xml->render_fib->attributes()->columns;

            $this->LoadRender($xml->render_fib);
        }
    }

    // load <render_ segment, only ever 1 per <response_
    public function LoadRender($xml) // <render_choice etc
    {
        if ($xml->material) {
            $this->material->add($xml->material);
        }

        foreach ($xml->response_label as $response_label) {
            $label = new ST_QTI12_Label($response_label);
            $this->labels[$label->id] = $label;
        }

        if ($xml->flow_label) {
            $this->flow = 1;
            foreach ($xml->flow_label as $flow_label) {
                $this->LoadRender($flow_label);
            }
        }
    }

    public function LoadRenderHotspot($xml)
    {
        if ($xml->material) {
            $this->material->add($xml->material);
        }

        foreach ($xml->response_label as $response_label) {
            $label = new ST_QTI12_Label($response_label);
            $this->labels[$label->id] = $label;
        }

        if ($xml->flow_label) {
            $this->flow = 1;
            foreach ($xml->flow_label as $flow_label) {
                $this->LoadRender($flow_label);
            }
        }
    }
    public function __toString()
    {
        $labeltxt = array();
        foreach ($this->labels as $label) {
            $labeltxt[] = $label->__toString();
        }

        if (count($labeltxt) == 0) {
            $labeltxt[] = "<font color='green'>NONE</font>";
        }

        return '<i>ID</i>: ' . $this->id . ', <i>Type</i>: ' . $this->type . ', <i>Render</i>: ' . $this->render . ', <i>Values</i>: ' . implode(' | ', $labeltxt);
    }
}

// object to store labels
class ST_QTI12_Label // <response_label
{
    public $id; // <response_label ident=
    public $material; // <material>
    public $flow = 0;
    public $orderid = 0;

    public function __construct($xml)
    {
        $this->id = (string) $xml->attributes()->ident;
        $this->shuffle = mb_strtolower($xml->attributes()->shuffle) == 'no' ? 0 : 1;
        if ($xml->attributes()->orderid) {
            $this->orderid = (int)$xml->attributes()->orderid;
        }

        $this->material = new ST_QTI12_Material();
        // get material if available
        if ($xml->material) {
            $this->material->add($xml->material);
        }

        if ($xml->flow_mat) {
            $this->flow = 1;
            $this->material->add($xml->flow_mat->material);
        }
    }
    public function __toString()
    {
        return $this->id . '=' . $this->material->__toString();
    }
}

// store each response processing condition
class ST_QTI12_RespCondition // <respcondition>
{
    public $title; // <respcondition title=
    public $action = ''; // <setvar action=
    public $mark = 0; // <setvar>
    public $feedback = ''; // <displayfeedback linkrefid=
    public $continue = 0; // should further resp conditions be processed if this one is matched?
    public $other = 0; // if final match, matching any other tags
    public $used = 0;
    public $type = 'and';
    // not is in the individual condition as can have an and with some nots in it for mrq type questions

    // <conditionvar>
    // ST_QTI12_CondVar
    // no key
    public $conditions = array();

    public $sortedout;
    public $sortedoutR;

    public function __construct($xml)
    {
        $this->title = (string) $xml->attributes()->title;

        foreach ($xml->conditionvar->and as $andpart) {
            if ($andpart->varequal) {
                foreach ($andpart->varequal as $andpart1) {
                    $nm = (string) $andpart1;
                    $ind = (int)$andpart1->attributes()->index;

                    $this->sortedout[$ind] = $nm;
                    $this->sortedoutR[$nm] = $ind;
                }
            }
        }

        $this->continue = mb_strtolower($xml->attributes()->continue) == 'yes' ? 1 : 0;
        if ($xml->setvar) {
            $this->action = (string) $xml->setvar->attributes()->action;
            $this->mark = (string) $xml->setvar;
            if (mb_strtolower($this->action) == 'subtract') {
                $this->action = 'Add';
                $this->mark = -$this->mark;
            }
        }

        // add all OR

        if ($xml->conditionvar->or) {
            $this->type = 'or';
            $this->LoadConditionVar($xml->conditionvar->or);
        } else {
            $this->LoadConditionVar($xml->conditionvar);
        }

        // feedback?
        if ($xml->displayfeedback) {
            $this->feedback = (string) $xml->displayfeedback->attributes()->linkrefid;
        }
    }

    public function LoadConditionVar($xml)
    {
        if ($xml->other) {
            $this->type = 'other';
            return;
        }

        // add all conditions

        foreach ($xml->children() as $child) {
            if ($child->getName() == 'not') {
                continue;
            }
            $this->conditions[] = new ST_QTI12_CondVar($child);
        }

        // add all NOT stuff
        // add all var equals
        foreach ($xml->not as $condition) {
            if ($condition->varequal) {
                $cv = new ST_QTI12_CondVar($condition->varequal);
                $cv->not = 1;
                $this->conditions[] = $cv;
            }
        }
    }

    public function __toString()
    {
        $conditions = array();
        foreach ($this->conditions as $condition) {
            $conditions[] = $condition->__toString();
        }

        if (count($conditions) == 0) {
            $conditions[] = "<font color='green'>NONE</font>";
        }

        return '<i>Mark</i>: ' . $this->mark . ', <i>Conditions</i>: ' . implode(' | ', $conditions);
    }
}

// variable for each response proc condition
class ST_QTI12_CondVar // <conditionvar>
{
    public $not = 0; // <not>
    public $respident; // <varequal respident=
    public $value; // <varequal>
    public $index; // <varequal>
    public $type = 'varequal';
    public $areatype = '';

    // should be passed a varequal
    public function __construct($xml)
    {
        $this->respident = (string) $xml->attributes()->respident;
        $this->index = (string) $xml->attributes()->index;
        $this->areatype = (string) $xml->attributes()->areatype;
        $this->value = (string) $xml;
        $this->type = $xml->getName();
    }

    public function __toString()
    {
        if ($this->not) {
            return $this->respident . '!=' . $this->value;
        }

        return $this->respident . '=' . $this->value;
    }
}

// feedback storage
class ST_QTI12_Itemfeedback // <itemfeedback>
{

    public $id; // <itemfeedback ident=
    public $material; // <material>

    public function __construct($xml)
    {
        $this->id = (string) $xml->attributes()->ident;

        $this->material = new ST_QTI12_Material();
        // get material if available

        if ($xml->material) {
            $this->material->add($xml->material);
        }

        if ($xml->flow_mat && $xml->flow_mat->material) {
            $this->material->add($xml->flow_mat->material);
        }
    }

    public function __toString()
    {
        return '<i>ID</i>: ' . $this->id . ', <i>Text</i>: ' . $this->material->__toString();
    }
}

class ST_QTI12_Material_Inner
{
    public $data = array(); // <material><mattext>
    public $image = '';
    public $label = '';

    public function GetHTML()
    {
        $output = implode('', $this->data);
        return $output;
    }

    public function GetText()
    {
        $text = implode('', $this->data) . "\n";

        while (mb_strpos($text, '  ') > 0) {
            $text = str_replace('  ', ' ', $text);
        }

        return trim($text);
    }
}

class ST_QTI12_Material // <material>
{
    public $count = 0;
    public $chunks = array(); // array of ST_QTI12_Material_Inner
    public $image = '';
    public $x_scale = 1;
    public $y_scale = 1;
    public $media_width = 0;
    public $media_height = 0;
    public $media_alt = '';
    public $orderid = 0;

    public $notrim = 0;


    public function add($xml = '', $order = '')
    {
        if ($xml) {
            $this->count++;
            if ($xml->attributes()->objectid) {
                $this->orderid = $xml->attributes()->objectid;
            }
            $chunk = new ST_QTI12_Material_Inner();
            $chunk->label = (string) $xml->attributes()->label;

            foreach ($xml->children() as $child) {
                $name = $child->getName();
                if ($name == 'mattext') {
                    $chunk->data[] = MakeValidHTML($this->ParseImages((string) $child), $this->notrim);
                    if (isset($child->attributes()->label)) {
                        $chunk->label = (string) $child->attributes()->label;
                    }
                }

                if ($name == 'matemtext') {
                    $chunk->data[] = '<em>' . MakeValidHTML($this->ParseImages((string) $child)) . '</em>';
                }

                if ($name == 'matbreak') {
                    $chunk->data[] = '<br />';
                }

                if ($name == 'mat_extension') {
                    foreach ($child->children() as $child1) {
                        $name1 = $child1->getName();
                        if ($name1 == 'webct_localizable_mattext') {
                            $chunk->data[] = MakeValidHTML($this->ParseImages((string) $child1));
                        }
                    }
                }
            }

            if ($order) {
                $this->chunks[$order] = $chunk;
            } else {
                $this->chunks[] = $chunk;
            }
            // load any images here
            if ($xml->matimage) {
                $this->addImage((string) $xml->matimage->attributes()->uri, (string) $xml->matimage->attributes()->width, (string) $xml->matimage->attributes()->height, (string)$xml->matimage, (string) $xml->matimage->attributes()->label);
            }
        }
    }

    public function addImage($image, $width = '', $height = '', $imgnam = '', $imglabel = '')
    {
        global $import_directory;
        global $q_warnings;
        global $q_errors;
        global $file;
        global $wct;
        global $load_params;

        $mediadirectory = rogo_directory::get_directory('media');

        if (stripos(' ' . $image, 'notes_icon.gif') > 0) {
            return;
        }

        echo "Adding image $image<br>";
        // download any http images etc here and put location in $imagefile as a LOCAL file
        $basename = basename($image);
        $imagefile = FindFile($import_directory, $basename);
        echo "Converted \"$image\" to base name \"$imagefile\"<br>";
        if ($imagefile == '' and $wct == 1) {
            list($discard, $split) = explode('=', $image);
            $pathinfo = pathinfo((string)$load_params->sourcefile);

            $imagefile = FindFileSub2($pathinfo['dirname'], '', '*' . $split . '*.' . pathinfo($imgnam, PATHINFO_EXTENSION));
            $imagefile = $pathinfo['dirname'] . '/' . $imagefile;
        } else {
            $imagefile = $import_directory . $imagefile;
        }

        if (mb_strlen($imagefile) > mb_strlen($import_directory) and file_exists($imagefile)) {
            $identifier_size = GetImageSize($imagefile);
            $this->media_width = $identifier_size[0];
            $this->media_height = $identifier_size[1];
            if ($imglabel != '') {
                $this->media_alt = $imglabel;
            }

            // if size different, then resize the image
            if ($width > 0 && $height > 0 && ($width != $this->media_width || $height != $this->media_height)) {
                $image = new SimpleImage();
                $image->load($imagefile);
                $image->resize($width, $height);
                $image->save($imagefile);
                echo "Resized $imagefile to $width x $height<br>";
                $this->x_scale = $width / $this->media_width;
                $this->y_scale = $height / $this->media_height;
                $this->media_width = $width;
                $this->media_height = $height;
            }

            $basename = basename($imagefile);
            $uniqueFilename = media_handler::unique_filename($basename);
            $fullpath = $mediadirectory->fullpath($uniqueFilename);

            copy($imagefile, $fullpath);
            echo "Copied $imagefile to $fullpath<br />";
            $this->media = $uniqueFilename;
        } else {
            $this->media = basename($imagefile);
            $q_warnings[] = "Missing image $image";
        }
    }

    public function ParseImages($text)
    {
        global $import_directory;
        global $q_warnings;
        global $q_errors;

        $mediadirectory = rogo_directory::get_directory('media');

        if (mb_stripos(' ' . $text, '<img') > 0) {
            $output = '';
            while ($text) {
                if (mb_stripos(' ' . $text, '<img') > 0) {
                    $pre = mb_substr($text, 0, mb_stripos($text, '<img'));
                    $imgtag = mb_substr($text, mb_stripos($text, '<img'));
                    $imgtag = mb_substr($imgtag, 0, mb_stripos($imgtag, '>') + 1);
                    $rest = mb_substr($text, mb_stripos($text, '<img'));
                    $rest = mb_substr($rest, mb_stripos($rest, '>') + 1);

                    $output .= $pre;

                    // we have a src tag?
                    if (mb_stripos($imgtag, 'src') > 0) {
                        $data = parseHtml($imgtag);
                        $src = $data['IMG'][0]['src'];
                        $basename = basename($src);
                        $filename = FindFile($import_directory, $basename);

                        if ($filename) {
                            $basename = basename($filename);
                            $uniqueFilename = media_handler::unique_filename($basename);
                            $fullpath = $mediadirectory->fullpath($uniqueFilename);

                            copy($import_directory . '/' . $filename, $fullpath);

                            $data['IMG'][0]['src'] = '/media/' . $basename;
                            // recreate img tag
                            $imgtag = '<img ';
                            foreach ($src = $data['IMG'][0] as $tag => $value) {
                                  $imgtag .= "$tag=\"$value\" ";
                            }
                            $imgtag .= '/>';
                        } else {
                            $q_warnings[] = "Missing image $basename";
                        }
                    }

                    $output .= $imgtag;

                    $text = $rest;
                } else {
                    $output .= $text;
                    $text = '';
                }
            }

            $text = $output;
        }

        return $text;
    }

    public function getItemCount()
    {
        return count($this->data);
    }

    public function GetHTML()
    {
        $output = '';
        $usediv = 0;

        if (count($this->chunks) > 1) {
            $usediv = 1;
        }

        foreach ($this->chunks as $chunk) {
            if ($this->notrim == 0) {
                $text = trim(implode('', $chunk->data));
            } else {
                $text = implode('', $chunk->data);
            }

            if ($text) {
                if ($usediv) {
                    $output .= '<div>';
                }

                $output .= implode('', $chunk->data);

                if ($usediv) {
                    $output .= '</div>';
                }
            }
        }

        return $output;
    }

    public function GetText($notrim = 0)
    {
        $text = '';
        foreach ($this->chunks as $chunk) {
            $text .= implode('', $chunk->data) . "\n";
        }

        while (mb_strpos($text, '  ') > 0) {
            $text = str_replace('  ', ' ', $text);
        }
        if ($notrim == 1) {
            return ($text);
        } else {
            return trim($text);
        }
    }

    public function GetLabel()
    {
        foreach ($this->chunks as $chunk) {
            $label[] = $chunk->label;
        }
        $labels = implode(' ', $label);
        return $labels;
    }

    public function __toString()
    {
        $text = $this->GetText();
        if (trim($text)) {
            return $text;
        }

        return "<font color='green'>EMPTY</font>";
    }
}

function FindFile($basedir, $filename)
{
    return FindFileSub($basedir, '', $filename);
}

function FindFileSub2($basedir, $dir, $filename)
{
    $dir_s = scandir($basedir . '/' . $dir);
    foreach ($dir_s as $entry) {
        if ($entry == '.') {
            continue;
        }
        if ($entry == '..') {
            continue;
        }
        if (is_dir($basedir . '/' . $dir . '/' . $entry)) {
            if ($dir) {
                $res = FindFileSub2($basedir, $dir . '/' . $entry, $filename);
            } else {
                $res = FindFileSub2($basedir, $entry, $filename);
            }
            if ($res != '') {
                return $res;
            }
        } elseif (fnmatch(mb_strtolower($filename), mb_strtolower($entry))) {
            if ($dir) {
                return $dir . '/' . $entry;
            } else {
                return $entry;
            }
        }
    }
    return '';
}

function FindFileSub($basedir, $dir, $filename)
{
    $dir_s = scandir($basedir . '/' . $dir);
    foreach ($dir_s as $entry) {
        if ($entry == '.') {
            continue;
        }
        if ($entry == '..') {
            continue;
        }
        if (is_dir($basedir . '/' . $dir . '/' . $entry)) {
            if ($dir) {
                $res = FindFileSub($basedir, $dir . '/' . $entry, $filename);
            } else {
                $res = FindFileSub($basedir, $entry, $filename);
            }
            if ($res != '') {
                return $res;
            }
        } elseif (mb_strtolower($entry) == mb_strtolower($filename)) {
            if ($dir) {
                return $dir . '/' . $entry;
            } else {
                return $entry;
            }
        }
    }

    return '';
}

function parseHtml($s_str)
{
    $i_indicatorL = 0;
    $i_indicatorR = 0;
    $s_tagOption = '';
    $i_arrayCounter = 0;
    $a_html = array();
    // Search for a tag in string
    while (is_int(($i_indicatorL = mb_strpos($s_str, '<', $i_indicatorR)))) {
        // Get everything into tag...
        $i_indicatorL++;
        $i_indicatorR = mb_strpos($s_str, '>', $i_indicatorL);
        $s_temp = mb_substr($s_str, $i_indicatorL, ($i_indicatorR - $i_indicatorL));
        $a_tag = explode(' ', $s_temp);
        // Here we get the tag's name
        list(, $s_tagName, , ) = $a_tag[0];
        $s_tagName = mb_strtoupper($s_tagName);
        // Well, I am not interesting in <br>, </font> or anything else like that...
        // So, this is false for tags without options.
        $b_boolOptions = is_array(($s_tagOption = $a_tag[1])) && $s_tagOption[1];
        if ($b_boolOptions) {
            // Without this, we will mess up the array
            $i_arrayCounter = 0;
            if (isset($a_html[$s_tagName])) {
                $i_arrayCounter = (int) count($a_html[$s_tagName]);
            }
            // get the tag options, like src="htt://". Here, s_tagTokOption is 'src' and s_tagTokValue is '"http://"'
            $tagcount = 2;
            do {
                $s_tagTokOption = mb_strtolower(strtok($s_tagOption[1], '='));
                $s_tagTokValue = trim(strtok('='));
                if (mb_substr($s_tagTokValue, 0, 1) == '"' && mb_substr($s_tagTokValue, mb_strlen($s_tagTokValue) - 1, 1) == '"') {
                    $s_tagTokValue = mb_substr($s_tagTokValue, 1, mb_strlen($s_tagTokValue) - 2);
                }
                if (mb_substr($s_tagTokValue, 0, 1) == "'" && mb_substr($s_tagTokValue, mb_strlen($s_tagTokValue) - 1, 1) == "'") {
                    $s_tagTokValue = mb_substr($s_tagTokValue, 1, mb_strlen($s_tagTokValue) - 2);
                }
                $a_html[$s_tagName][$i_arrayCounter][$s_tagTokOption] = $s_tagTokValue;
                $b_boolOptions = is_array(($s_tagOption = $a_tag[$tagcount])) && $s_tagOption[1];
                $tagcount++;
            } while ($b_boolOptions);
        }
    }
    return $a_html;
}
