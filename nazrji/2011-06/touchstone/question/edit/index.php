<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

// TODO: handle errors (i.e. question not found)
// TODO: handle keyword based and random
// TODO: validation in JS
// TODO: replace comment OK etc. icons with CSS BG image?

require '../../include/staff_auth.inc';
require_once '../../classes/question.class.php';

$question = null;

$critical_error = '';

$q_no = (empty($_GET['qNo'])) ? '' : $_GET['qNo'];
$q_type = '';
$q_type_full = '';
 
if(empty($_REQUEST['q_id'])) {
  // We're adding a new question
  $question = new Question($mysqli);
  
  if (empty($_GET['type'])) {
    $critical_error = 'No question type defined.';
  } elseif (!in_array($_GET['type'], array_keys(Question::$types))) {
    $critical_error = 'Unknown question type <em>' . htmlentities($_GET['type']) . '</em>.';
  } else {
    $question->type = $_GET['type'];
  }
} else {
  // We're editing an existion question
  
  $question = new Question($mysqli, $_REQUEST['q_id']);
}

$mode = (empty($_REQUEST['q_id'])) ? 'Add' : 'Edit';

$q_type_display = (!empty($_REQUEST['q_no'])) ? ' ' . $_REQUEST['q_no'] : '';
if (!empty($question->type)) {
  $q_type_full = Question::$types[$question->type];
  $q_type_display .= " &ndash; $q_type_full";
}

?>
<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>Edit MCQ Question (new)</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />

<link rel="stylesheet" href="../../css/screen.css" type="text/css" />
<link rel="stylesheet" href="../../css/add_edit_new.css" type="text/css" />

<script type="text/javascript" src="../../javascript/tiny_mce.js"></script>
<script type="text/javascript" src="../../javascript/tiny_config.js"></script>
<script type="text/javascript" src="../../javascript/staff_help.js"></script>
<script type="text/javascript" src="../../javascript/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="../../javascript/jquery.addedit.js"></script>
</head>
<body>
	<div id="page-header">
		<div id="page-help">
			<a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" alt="Help" border="0" height="16" width="16" />
			</a>
		</div>
		<div id="page-header-inner">
			<h1><?php echo $mode . ' Question' . $q_type_display ?></h1>
		</div>

<?php 
if ($critical_error == '') {
?>
    <div class="tab-bar">
      <div class="tab-holder">
        <p class="question-stats">
          <strong>Created:</strong>&nbsp;12/04/2011&nbsp;&nbsp;&nbsp;&nbsp;<strong>Modified:</strong>&nbsp;02/08/2011
        </p>
        <ol class="tabs">
          <li class="on"><a href="#">Editor</a></li>
          <li><a href="#">Changes</a></li>
          <li><a href="#">Comments</a></li>
          <li><a href="#">Mapping</a></li>
        </ol>
      </div>
    </div>
<?php
}
?>
	</div>

<?php
if($critical_error != '') {
  // We have a major error so won't even display a form
?>
  <div id="major-error" class="edit-spacer">
    <div id="major-error-inner">
      <h1>Error</h1>
      <p><?php echo $critical_error ?></p>
    </div>
  </div>
<?php
} else {
?>

	<form name="edit_form" method="post" onsubmit="return checkForm()" action="/touchstone/question/edit/mcq.php?q_id=53669" enctype="multipart/form-data">
    <div id="tabbed-content">
			<div id="editor" class="tab-area">
        
				<div class="message">
					<p>
						<span class="mandatory">*</span> Indicates a <strong>mandatory</strong> field that must be completed.
					</p>
				</div>

				<table id="q-details" class="form" summary="Edit question details">
					<tbody>
            <tr>
              <th><label for="theme">Theme/Heading</label></th>
              <td>
                <textarea id="theme" name="theme" cols="100" rows="5" class="form-large"></textarea>
                <textarea name="old_theme" cols="100" rows="5" class="form-old"></textarea>
                <input name="checkout_author" value="17276" type="hidden" class="form-old" />
              </td>
            </tr>
            <tr>
              <th><label for="notes">Notes</label><br /><span class="note">(visible to students)</span></th>
              <td>
                <textarea id="notes" name="notes" cols="100" rows="2" class="form-large"></textarea>
                <textarea name="old_notes" class="form-old" rows="2" cols="40"></textarea>
              </td>
            </tr>
            <tr>
              <th class="field"><label for="scenario">Scenario</label><br /><span class="note">(background info)</span></th>
              <td colspan="2">
                <textarea id="scenario" name="scenario" cols="100" rows="5" class="mceEditor form-large">&lt;p&gt;&lt;br _mce_bogus="1"&gt;&lt;/p&gt;</textarea>
                <textarea id="old_scenario" name="old_scenario" cols="100" rows="5" class="form-old"></textarea>
              </td>
            </tr>
            <tr>
              <th><label for="q_media">Change Media</label></th>
              <td>
                <input id="q_media" name="q_media" size="65" type="file" />
                <input name="old_q_media" value="" type="hidden" />
                <input name="old_q_media_width" value="0" type="hidden" />
                <input name="old_q_media_height" value="0" type="hidden" />
              </td>
            </tr>
            <tr>
              <th><span class="mandatory">*</span> <label for="leadin">Lead-in</label><br /><span class="note">(the question)</span></th>
              <td>
                <textarea id="leadin" name="leadin" cols="100" rows="5" class="mceEditor form-large">&lt;p&gt;xxxxxxxAn
                  otherwise healthy 33-year-old man has mild weakness and occasional 
                  episodes of steady, severe abdominal pain with some cramping but no 
                  diarrhea. One aunt and a cousin have had similar episodes. During an 
                  episode, his abdomen is distended, and bowel sounds are decreased. 
                  Neurologic examination shows mild weakness in the upper arms. These 
                  findings suggest a defect in the biosynthetic pathway for:&lt;/p&gt;
                </textarea>
                <textarea name="old_leadin" id="old_leadin" cols="100" rows="5" class="form-old">
                  xxxxxxxAn
                  otherwise healthy 33-year-old man has mild weakness and occasional 
                  episodes of steady, severe abdominal pain with some cramping but no 
                  diarrhea. One aunt and a cousin have had similar episodes. During an 
                  episode, his abdomen is distended, and bowel sounds are decreased. 
                  Neurologic examination shows mild weakness in the upper arms. These 
                  findings suggest a defect in the biosynthetic pathway for:</textarea>
              </td>
            </tr>
            <tr>
              <th><label for="score_method">Presentation</label></th>
              <td>
                <select id="score_method" name="score_method">
                  <option value="vertical" selected="selected">Vertical Option Button</option>
                  <option value="vertical_other">Vertical Option Buttons (with 'other' textbox)</option>
                  <option value="horizontal">Horizontal Option Button</option>
                  <option value="dropdown">Dropdown List</option>
                </select>
                <input name="old_score_method" value="vertical" type="hidden" />
              </td>
            </tr>
            <tr>
              <th><label for="option_order">Option Order</label></th>
              <td>
                <select id="option_order" name="option_order">
                  <option value="display order" selected="selected">Display Order</option>
                  <option value="alphabetic">Alphabetic</option>
                  <option value="random">Random</option>
                </select>
                <input name="old_option_order" value="display order" type="hidden" />
              </td>
            </tr>
					</tbody>
				</table>
        
        <table id="q-options" class="form" summary="Edit question options">
          <tbody>
            <tr>
              <th colspan="3">&nbsp;</th>
              <th class="small"><strong>Correct Answer</strong></th>
            </tr>
          </tbody>
          <tbody class="option">
            <tr>
              <th><span class="mandatory">*</span><label for="new_option_text1"><strong>1.</strong></label></th>
              <td colspan="2">
                <textarea name="new_option_text1" id="new_option_text1" cols="90" rows="2" class="form-med-large">collagen</textarea>
                <textarea name="old_option_text1" cols="90" rows="2" class="form-old">collagen</textarea>
                <input name="optionid1" value="280288" type="hidden" />
              </td>
              <td class="small"><input id="correct1" name="correct" value="1" type="radio" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="feedback_right1">Feedback:</label></th>
              <td>
                <textarea cols="85" rows="2" id="feedback_right1" name="feedback_right1" class="form-med"></textarea>
                <textarea name="old_feedback_right1" class="form-old" rows="2" cols="40"></textarea>
              </td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="new_option_media1">Change Media:</label></th>
              <td>
                <input id="new_option_media1" name="new_option_media1" type="file" size="50" />
                <input name="old_option_media1" id="old_option_media1" value="" type="hidden" />
                <input name="old_option_media_width1" value="0" type="hidden" />
                <input name="old_option_media_height1" value="0" type="hidden" />
              </td>
              <td>&nbsp;</td>
            </tr>
          </tbody>
          <tbody class="option">
            <tr>
              <th><span class="mandatory">*</span><label for="new_option_text2"><strong>2.</strong></label></th>
              <td colspan="2">
                <textarea name="new_option_text2" id="new_option_text2" cols="90" rows="2" class="form-med-large">corticosteroid</textarea>
                <textarea name="old_option_text2" cols="90" rows="2" class="form-old">corticosteroid</textarea>
                <input name="optionid1" value="280288" type="hidden" />
              </td>
              <td class="small"><input id="correct2" name="correct" value="2" type="radio" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="feedback_right2">Feedback:</label></th>
              <td>
                <textarea cols="85" rows="2" id="feedback_right2" name="feedback_right2" class="form-med"></textarea>
                <textarea name="old_feedback_right2" class="form-old" rows="2" cols="40"></textarea>
              </td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="new_option_media2">Change Media:</label></th>
              <td>
                <input id="new_option_media2" name="new_option_media2" type="file" size="50" />
                <input name="old_option_media2" id="old_option_media2" value="" type="hidden" />
                <input name="old_option_media_width2" value="0" type="hidden" />
                <input name="old_option_media_height2" value="0" type="hidden" />
              </td>
              <td>&nbsp;</td>
            </tr>
          </tbody>
          <tbody class="option">
            <tr>
              <th><span class="mandatory">*</span><label for="new_option_text3"><strong>3.</strong></label></th>
              <td colspan="2">
                <textarea name="new_option_text3" id="new_option_text3" cols="90" rows="2" class="form-med-large">fatty acid</textarea>
                <textarea name="old_option_text3" cols="90" rows="2" class="form-old">fatty acid</textarea>
                <input name="optionid1" value="280288" type="hidden" />
              </td>
              <td class="small"><input id="correct3" name="correct" value="3" type="radio" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="feedback_right3">Feedback:</label></th>
              <td>
                <textarea cols="85" rows="2" id="feedback_right3" name="feedback_right3" class="form-med"></textarea>
                <textarea name="old_feedback_right3" class="form-old" rows="2" cols="40"></textarea>
              </td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="new_option_media3">Change Media:</label></th>
              <td>
                <input id="new_option_media3" name="new_option_media3" type="file" size="50" />
                <input name="old_option_media3" id="old_option_media3" value="" type="hidden" />
                <input name="old_option_media_width3" value="0" type="hidden" />
                <input name="old_option_media_height3" value="0" type="hidden" />
              </td>
              <td>&nbsp;</td>
            </tr>
          </tbody>
          <tbody class="option hide">
            <tr>
              <th><label for="new_option_text4"><strong>4.</strong></label></th>
              <td colspan="2">
                <textarea name="new_option_text4" id="new_option_text4" cols="90" rows="2" class="form-med-large">glucose</textarea>
                <textarea name="old_option_text4" cols="90" rows="2" class="form-old">glucose</textarea>
                <input name="optionid1" value="280288" type="hidden" />
              </td>
              <td class="small"><input id="correct4" name="correct" value="4" type="radio" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="feedback_right4">Feedback:</label></th>
              <td>
                <textarea cols="85" rows="2" id="feedback_right4" name="feedback_right4" class="form-med"></textarea>
                <textarea name="old_feedback_right4" class="form-old" rows="2" cols="40"></textarea>
              </td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="new_option_media4">Change Media:</label></th>
              <td>
                <input id="new_option_media4" name="new_option_media4" type="file" size="50" />
                <input name="old_option_media4" id="old_option_media4" value="" type="hidden" />
                <input name="old_option_media_width4" value="0" type="hidden" />
                <input name="old_option_media_height4" value="0" type="hidden" />
              </td>
              <td>&nbsp;</td>
            </tr>
          </tbody>
          <tbody class="option hide">
            <tr>
              <th><label for="new_option_text5"><strong>5.</strong></label></th>
              <td colspan="2">
                <textarea name="new_option_text5" id="new_option_text5" cols="90" rows="2" class="form-med-large">heme</textarea>
                <textarea name="old_option_text5" cols="90" rows="2" class="form-old">heme</textarea>
                <input name="optionid1" value="280288" type="hidden" />
              </td>
              <td class="small"><input id="correct5" name="correct" value="5" type="radio" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="feedback_right5">Feedback:</label></th>
              <td>
                <textarea cols="85" rows="2" id="feedback_right5" name="feedback_right5" class="form-med"></textarea>
                <textarea name="old_feedback_right5" class="form-old" rows="2" cols="40"></textarea>
              </td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <th><label for="new_option_media5">Change Media:</label></th>
              <td>
                <input id="new_option_media5" name="new_option_media5" type="file" size="50" />
                <input name="old_option_media5" id="old_option_media5" value="" type="hidden" />
                <input name="old_option_media_width5" value="0" type="hidden" />
                <input name="old_option_media_height5" value="0" type="hidden" />
              </td>
              <td>&nbsp;</td>
            </tr>
          </tbody>
          <tbody id="next-option-holder">
            <tr>
              <th>&nbsp;</th>
              <td colspan="3">
                <input id="next-option" value="Add More Options..." type="button" />
              </td>
            </tr>
          </tbody>
        </table>

        <table id="q-feedback" class="form" summary="Edit question feedback">
          <tbody>
            <tr>
              <th><label for="correct_fback">General Feedback</label></th>
              <td>
                <textarea id="correct_fback" name="correct_fback" cols="100" rows="3" class="form-large">right</textarea>
                <input name="old_correct_fback" value="right" type="hidden" />
              </td>
            </tr>
          </tbody>
        </table>

        <div class="form">
          <h2>Metadata</h2>
        </div>
        
        <table id="q-metadata-basic" class="form" summary="Edit basic question metadata">
          <tbody>
            <tr>
              <th>Status</th>
              <td>
                <ul class="radio-list horizontal">
                  <li><input id="status-normal" name="status" value="Normal" checked="checked" type="radio" /> <label for="status-normal">Normal</label></li>
                  <li><input id="status-retired" name="status" value="Retired" type="radio" /> <label for="status-retired">Retired</label></li>
                  <li><input id="status-inc" name="status" value="Incomplete" type="radio" /> <label for="status-inc">Incomplete</label></li>
                  <li><input id="status-exp" name="status" value="Experimental" type="radio" /> <label for="status-exp">Experimental</label></li>
                  <li><input id="status-beta" name="status" value="Beta" type="radio" /> <label for="status-beta">Beta</label></li>
                </ul>
                <input name="old_status" value="Normal" type="hidden" />
              </td>
            </tr>
            <tr>
              <th>Bloom's Taxonomy</th>
              <td>
                <select name="bloom">
                  <option selected="selected" value=""></option>
                  <option value="Knowledge">Knowledge</option>
                  <option value="Comprehension">Comprehension</option>
                  <option value="Application">Application</option>
                  <option value="Analysis">Analysis</option>
                  <option value="Synthesis">Synthesis</option>
                  <option value="Evaluation">Evaluation</option>
                </select>
                <input name="old_bloom" value="" type="hidden" />
              </td>
            </tr>
          </tbody>
        </table>
        
        <div class="form">
          <div id="keyword-select" class="select-area">
            <h3>Keywords</h3>
            <!-- TODO: check what the old UpdateList function did -->
            <div id="q-keywords" class="select-group">
              <h4>SYSTEM</h4>
              <ul class="radio-list clearfix">
                <li><label for="keyword0" class="fullwidth"><input id="keyword0" name="keyword0" value="1268" type="checkbox" /> Cat</label></li>
              </ul>
  
              <h4>Personal Keywords</h4>
              <ul class="radio-list clearfix">
                <li><label for="keyword1" class="fullwidth"><input id="keyword1" name="keyword1" value="1094" type="checkbox" /> Training</label></li>
              </ul>
            </div>
          </div>
          
          <div id="team-select" class="select-area">
            <h3>Teams</h3>
            <div id="q-teams" class="select-group">
              <h4>Department of Mechanical, Materials and Manufacturing Engineering</h4>
              <ul class="radio-list clearfix">
                <li><label for="team0" class="fullwidth"><input id="team0" name="team0" value="MM1EM1" type="checkbox" /> MM1EM1: Electromechanical Systems 1</label></li>
                <li><label for="team1" class="fullwidth"><input id="team1" name="team1" value="MM1EM1_UNMC" type="checkbox" /> MM1EM1_UNMC: Electromechanical Systems 1</label></li>
                <li><label for="team2" class="fullwidth"><input id="team2" name="team2" value="MM1MS1" type="checkbox" /> MM1MS1: Mechanics of Solids 1</label></li>
                <li><label for="team3" class="fullwidth"><input id="team3" name="team3" value="MM1MSY" type="checkbox" /> MM1MSY: Mechanical Systems</label></li>
                <li><label for="team4" class="fullwidth"><input id="team4" name="team4" value="MM1TF1" type="checkbox" /> MM1TF1: Thermodynamics and Fluid Mechanics</label></li>
                <li><label for="team5" class="fullwidth"><input id="team5" name="team5" value="MM2TF2" type="checkbox" /> MM2TF2: Thermodynamics &amp; Fluid Mechanics 2</label></li>
                <li><label for="team6" class="fullwidth"><input id="team6" name="team6" value="MM3HTR" type="checkbox" /> MM3HTR: Heat Transfer</label></li>
                <li><label for="team7" class="fullwidth"><input id="team7" name="team7" value="MM3HTR_UNMC" type="checkbox" /> MM3HTR_UNMC: Heat Transfer</label></li>
              </ul>
              <h4>Faculty of Medicine</h4>
              <ul class="radio-list clearfix">
                <li><label for="team8" class="fullwidth"><input id="team8" name="team8" value="A11BHS" type="checkbox" /> A11BHS: Behavioural Sciences</label></li>
                <li><label for="team9" class="fullwidth"><input id="team9" name="team9" value="A11CLS" type="checkbox" /> A11CLS: Clinical Laboratory Sciences</label></li>
                <li><label for="team10" class="fullwidth"><input id="team10" name="team10" value="A11CRH" type="checkbox" /> A11CRH: Cardiovascular, Respiratory and Haematology</label></li>
                <li><label for="team11" class="fullwidth"><input id="team11" name="team11" value="A11CS1" type="checkbox" /> A11CS1: Communication Skills (1)</label></li>
                <li><label for="team12" class="fullwidth"><input id="team12" name="team12" value="A11EXT" type="checkbox" /> A11EXT: Structure, function and pharmacology of excitable tissues</label></li>
                <li><label for="team13" class="fullwidth"><input id="team13" name="team13" value="A11HDT" type="checkbox" /> A11HDT: Human Development and Tissue Differentiation</label></li>
                <li><label for="team14" class="fullwidth"><input id="team14" name="team14" value="A11MBM" type="checkbox" /> A11MBM: Molecular Basis of Medicine</label></li>
                <li><label for="team15" class="fullwidth"><input id="team15" name="team15" value="A11PD1" type="checkbox" /> A11PD1: Early Clinical and Professional Development</label></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div id="changes" class="tab-area">
        <table class="data">
          <thead>
            <tr>
              <th class="data-small">Date</th>
              <th>Section</th>
              <th class="data-small">Old</th>
              <th class="data-small">New</th>
              <th class="data-small">Editor</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>03/08/2011</td>
              <td>Frigged more test data</td>
              <td>53657</td>
              <td>53669</td>
              <td>Dr R ingram</td>
            </tr>
            <tr class="alt">
              <td>03/08/2011</td>
              <td>Frigged some test data</td>
              <td>53657</td>
              <td>53669</td>
              <td>Dr R ingram</td>
            </tr>
            <tr>
              <td>12/04/2011</td>
              <td>Copied Question</td>
              <td>53657</td>
              <td>53669</td>
              <td>Miss A Rockcliffe</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div id="comments" class="tab-area">
        <table class="data">
          <thead>
            <tr>
              <th style="width:16px"></th>
              <th style="width:14%">Reviewer</th>
              <th style="width:39%">Comments</th>
              <th style="width:5%">Action&nbsp;Taken</th>
              <th style="width:39%">Internal Response</th>
            </tr>
        </thead>
        <tbody>
            <tr>
              <td><img src="../../artwork/ok_comment.png" width="16" height="16" alt="OK" /></td>
              <td>Dr T Boswell<br /><span class="note">Internal</span></td>
              <td><span class="note">No Comments</span></td>
              <td>
                <select name="action0">
                  <option value="Not actioned" selected="selected">Not actioned</option>
                  <option value="Read - disagree">Read - disagree</option>
                  <option value="Read - actioned">Read - actioned</option>
                </select>
                <input type="hidden" name="commentid0" value="34725" />
              </td>
              <td>
                <textarea cols="60" rows="3" name="response0"></textarea>
              </td>
            </tr>
            <tr class="alt">
              <td><img src="../../artwork/ok_comment.png" width="16" height="16" alt="OK" /></td>
              <td>Dr S Crusz<br /><span class="note">Internal</span></td>
              <td><span class="note">No Comments</span></td>
              <td>
                <select name="action1">
                  <option value="Not actioned" selected="selected">Not actioned</option>
                  <option value="Read - disagree">Read - disagree</option>
                  <option value="Read - actioned">Read - actioned</option>
                </select>
                <input type="hidden" name="commentid1" value="34887" />
              </td>
              <td>
                <textarea cols="60" rows="3" name="response1"></textarea>
              </td>
            </tr>
            <tr>
              <td><img src="../../artwork/ok_comment.png" width="16" height="16" alt="OK" /></td>
              <td>Professor W Irving<br /><span class="note">Internal</span></td>
              <td><span class="note">No Comments</span></td>
              <td>
                <select name="action2">
                  <option value="Not actioned" selected="selected">Not actioned</option>
                  <option value="Read - disagree">Read - disagree</option>
                  <option value="Read - actioned">Read - actioned</option>
                </select>
                <input type="hidden" name="commentid2" value="34782" />
              </td>
              <td>
                <textarea cols="60" rows="3" name="response2"></textarea>
              </td>
            </tr>
            <tr class="alt">
              <td><img src="../../artwork/ok_comment.png" width="16" height="16" alt="OK" /></td>
              <td>Professor R James<br /><span class="note">Internal</span></td>
              <td><span class="note">No Comments</span></td>
              <td>
                <select name="action3">
                  <option value="Not actioned" selected="selected">Not actioned</option>
                  <option value="Read - disagree">Read - disagree</option>
                  <option value="Read - actioned">Read - actioned</option>
                </select>
                <input type="hidden" name="commentid3" value="36913" />
              </td>
              <td>
                <textarea cols="60" rows="3" name="response3"></textarea>
              </td>
            </tr>
            <tr>
              <td><img src="../../artwork/ok_comment.png" width="16" height="16" alt="OK" /></td>
              <td>Professor D Mack<br /><span class="note">External</span></td>
              <td><span class="note">No Comments</span></td>
              <td>
                <input type="hidden" name="commentid4" value="37078" />
                <select name="action4">
                  <option value="Not actioned" selected="selected">Not actioned</option>
                  <option value="Read - disagree">Read - disagree</option>
                  <option value="Read - actioned">Read - actioned</option>
                </select>
              </td>
              <td>
                <textarea cols="60" rows="3" name="response4"></textarea>
              </td>
            </tr>
            <tr class="alt">
              <td><img src="../../artwork/ok_comment.png" width="16" height="16" alt="OK" /></td>
              <td>Dr V Weston<br /><span class="note">Internal</span></td>
              <td><span class="note">No Comments</span></td>
              <td>
                <input type="hidden" name="commentid5" value="34719" />
                <select name="action5">
                  <option value="Not actioned" selected="selected">Not actioned</option>
                  <option value="Read - disagree">Read - disagree</option>
                  <option value="Read - actioned">Read - actioned</option>
                </select>
              </td>
              <td>
                <textarea rows="3" cols="60" name="response5"></textarea>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div id="mapping" class="tab-area">
        <h2>SYSTEM Objectives</h2>
        
        <ul class="objectives">
          <li class="top">
            <a href="#">Set internally held objectives <span class="note">10/03/11</span></a>
            <ul class="hide">
              <li><input id="SYSTEMobj0" name="SYSTEMobj0" value="861" type="checkbox" /> <label for="SYSTEMobj0">Objective1</label></li>
              <li><input id="SYSTEMobj1" name="SYSTEMobj1" value="862" type="checkbox" /> <label for="SYSTEMobj1">Objective2</label></li>
              <li><input id="SYSTEMobj2" name="SYSTEMobj2" value="863" type="checkbox" /> <label for="SYSTEMobj2">Objective3</label></li>
              <li><input id="SYSTEMobj3" name="SYSTEMobj3" value="864" type="checkbox" /> <label for="SYSTEMobj3">Objective4</label></li>
            </ul>
          </li>
          <li class="top">
            <a href="#">Session2 Set internally held objectives <span class="note">11/03/11</span></a>
            <ul class="hide">
              <li><input id="SYSTEMobj4" name="SYSTEMobj4" value="865" type="checkbox" /> <label for="SYSTEMobj4">Session2 Objective1</label></li>
              <li><input id="SYSTEMobj5" name="SYSTEMobj5" value="866" type="checkbox" /> <label for="SYSTEMobj5">Session2 Objective2</label></li>
              <li><input id="SYSTEMobj6" name="SYSTEMobj6" value="867" type="checkbox" /> <label for="SYSTEMobj6">Session2 Objective3</label></li>
              <li><input id="SYSTEMobj7" name="SYSTEMobj7" value="868" type="checkbox" /> <label for="SYSTEMobj7">Session2 Objective4</label></li>
              <li><input id="SYSTEMobj8" name="SYSTEMobj8" value="869" type="checkbox" /> <label for="SYSTEMobj8">Session2 Objective5</label></li>
            </ul>
          </li>
        </ul>
        
        <p class="warning"><input value="-1" id="none_of_the_above" name="none_of_the_above" type="checkbox" /><label for="none_of_the_above"><strong>None of the Above</strong></label><br />Check here if the current question does not match any of the above objectives from SYSTEM.</p>
        
        <input id="SYSTEM_objectiveCount" name="SYSTEM_objectiveCount" value="9" type="hidden" />
        <input name="SYSTEM_session" value="2010/11" type="hidden" />
        <input name="SYSTEM_old_mappings" value="" type="hidden" />
        <input id="paperID" name="paperID" value="3515" type="hidden" />
        <input id="questionID" name="questionID" value="53669" type="hidden" />
        <input id="modules" name="modules" value="SYSTEM" type="hidden" />
      </div>
    </div>

    <div id="button-bar">
      <input id="submit-save" name="submit-save" value="Save Changes" type="submit" class="submit" />
      <input id="submit-cancel" name="submit-cancel" value="Cancel" onclick="formCancel();" type="submit" class="submit" />
    </div>
  </form>
<?php
}
?>
</body>
</html>