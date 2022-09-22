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

require '../lang/' . $language . '/std_setting/std_set_shared.php';
require '../lang/' . $language . '/paper/start.php';

$string['modifiedangoffmethod'] = 'Modified Angoff method';
$string['ebelmethod'] = 'Ebel method';
$string['modangoffstep1'] = 'For each question use the orange dropdown lists to indicate the percentage of <strong>borderline</strong> (minimally competent) candidates expected to get each question correct.';
$string['step1'] = '<strong>Step 1:</strong><br />For each question use the orange dropdown lists firstly to indicate the <strong>difficulty</strong> of the question (easy, medium, or hard) and then secondly to indicate the question\'s <strong>importance</strong> (essential, important, or nice to know).';
$string['step2'] = '<strong>Step 2: Pass Grid</strong><br />For each category (e.g. easy/essential, easy/important, etc) specify the percentage of <strong>borderline candidates</strong> expected to get questions in this category correct.';
$string['step3'] = '<strong>Step 3: Distinction Grid</strong><br />For each category (e.g. easy/essential, easy/important, etc) specify the percentage of <strong>distinction candidates</strong> expected to get questions in this category correct.';
$string['gridbelow'] = 'Use grid below';
$string['top20'] = 'Use top 20%';
$string['donotapply'] = 'Do not apply';
$string['easy'] = 'Easy';
$string['medium'] = 'Medium';
$string['hard'] = 'Hard';
$string['essential'] = 'Essential';
$string['important'] = 'Important';
$string['nicetoknow'] = 'Nice to Know';
$string['papermarks'] = 'paper marks';
$string['reviewmarks'] = 'review marks';
$string['cutscore'] = 'cut score';
$string['saveexit'] = 'Save &amp; Exit';
$string['savecontinue'] = 'Save &amp; Continue';
$string['savebank'] = 'Save ratings into question bank';
$string['cannotbeused'] = '<strong>Note:</strong> Ebel method cannot be used to standard set textbox questions.';
$string['na'] = 'N/A';
$string['screen'] = 'Screen';
$string['note'] = 'NOTE:';
$string['notpossibletostandard'] = 'It is not possible to standard set Script Concordance questions.';
$string['notvisible'] = '<strong>Information:</strong> (not visible to candidates)';
$string['reviewermsg'] = 'This is a Calculation type question. Variables are calculated at run-time and will vary for different candidates. The answer, however, is based on a single formula. Candidates will not see <strong>$A</strong>, etc they will only see the randomly generated figures.';
$string['variable'] = 'Variable';
$string['generated'] = 'Generated';
$string['max'] = 'Max';
$string['min'] = 'Min';
$string['formula'] = 'Formula';
$string['tolerancefull'] = 'Tolerance for full marks';
$string['tolerancepartial'] = 'Tolerance for partial marks';
$string['togglevariables'] = 'Toggle Variables';
$string['randomwarning'] = '<strong>Warning:</strong> Random question block questions cannot be analysed in this report.';
$string['keywordwarning'] = '<strong>Warning:</strong> Keyword-based questions cannot be analysed in this report.';
