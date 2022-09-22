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
 * Display student settings
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @package core
 */

require_once '../include/staff_student_auth.inc';

// Only allow access to valid students i.e. not guest accounts.
if (
    !$userObject->has_role('Student') or
    !$configObject->get_setting('core', 'system_user_accessibility') or
    !UserUtils::username_is_valid($userObject->get_username())
) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '/artwork/page_not_found.png', '#C00000', true, true);
}

$updateaccess = param::optional('updateaccess', null, param::ALPHA, param::FETCH_POST);

if (!is_null($updateaccess)) {
    $colour_background = param::optional('bg_radio', false, param::BOOLEAN, param::FETCH_POST);
    $colour_forground = param::optional('fg_radio', false, param::BOOLEAN, param::FETCH_POST);
    $colour_marks = param::optional('marks_radio', false, param::BOOLEAN, param::FETCH_POST);
    $colour_theme = param::optional('theme_radio', false, param::BOOLEAN, param::FETCH_POST);
    $colour_labels = param::optional('labels_radio', false, param::BOOLEAN, param::FETCH_POST);
    $colour_unanswered = param::optional('unanswered_radio', false, param::BOOLEAN, param::FETCH_POST);
    $colour_dismiss = param::optional('dismiss_radio', false, param::BOOLEAN, param::FETCH_POST);
    $colour_globaltheme = param::optional('colour_globaltheme_radio', false, param::BOOLEAN, param::FETCH_POST);
    $colour_globalthemefont = param::optional('colour_globalthemefont_radio', false, param::BOOLEAN, param::FETCH_POST);
    $colour_highlight = param::optional('colour_highlight_radio', false, param::BOOLEAN, param::FETCH_POST);

    if ($colour_background) {
        $background = param::optional('background', null, param::TEXT, param::FETCH_POST);
    } else {
        $background = null;
    }
    if ($colour_forground) {
        $foreground = param::optional('foreground', null, param::TEXT, param::FETCH_POST);
    } else {
        $foreground = null;
    }
    $textsize = param::optional('textsize', 0, param::INT, param::FETCH_POST);
    $font = param::optional('font', null, param::ALPHA, param::FETCH_POST);
    if ($colour_marks) {
        $marks_color = param::optional('marks_color', null, param::TEXT, param::FETCH_POST);
    } else {
        $marks_color = null;
    }
    if ($colour_theme) {
        $themecolor = param::optional('themecolor', null, param::TEXT, param::FETCH_POST);
    } else {
        $themecolor = null;
    }
    if ($colour_labels) {
        $labelcolor = param::optional('labelcolor', null, param::TEXT, param::FETCH_POST);
    } else {
        $labelcolor = null;
    }
    if ($colour_unanswered) {
        $unansweredcolor = param::optional('unansweredcolor', null, param::TEXT, param::FETCH_POST);
    } else {
        $unansweredcolor = null;
    }
    if ($colour_dismiss) {
        $dismisscolor = param::optional('dismisscolor', null, param::TEXT, param::FETCH_POST);
    } else {
        $dismisscolor = null;
    }
    if ($colour_globaltheme) {
        $globalthemecolour = param::optional('globalthemecolour', null, param::TEXT, param::FETCH_POST);
    } else {
        $globalthemecolour = null;
    }
    if ($colour_globalthemefont) {
        $globalthemefontcolour = param::optional('globalthemefontcolour', null, param::TEXT, param::FETCH_POST);
    } else {
        $globalthemefontcolour = null;
    }
    if ($colour_highlight) {
        $highlightcolour = param::optional('highlightcolour', null, param::TEXT, param::FETCH_POST);
    } else {
        $highlightcolour = null;
    }

    $userObject->userSetAccessibility(
        $background,
        $foreground,
        $textsize,
        $marks_color,
        $themecolor,
        $labelcolor,
        $font,
        $unansweredcolor,
        $dismisscolor,
        $globalthemecolour,
        $globalthemefontcolour,
        $highlightcolour
    );
    header('location: settings.php');
    exit();
}

// Look up special_needs data
$textsize = $userObject->get_textsize(100);
$font = $userObject->get_font('Arial');

$render = new render($configObject);
$headerdata = array(
    'css' => array(
        '/css/body.css',
        '/css/rogo_logo.css',
        '/css/studentsettings.css',
        '/css/accessiblity.css',
    ),
    'scripts' => array(
        '/js/studentsettingsinit.min.js',
    ),
);
$additionalcss = '<style type="text/css">body {padding-left:0; font-size:' . $textsize  . '%; font-family:' . $font . '}</style>';
$render->render($headerdata, $string, 'header.html', '', $additionalcss);

require_once '../include/toprightmenu.inc';
$accessibilitydata['toprightmenu'] = draw_toprightmenu();
require '../tools/colour_picker/colour_picker.inc';

$fontsizes = array(90, 100, 110, 120, 130, 140, 150, 175, 200, 300, 400);
$accessibilitydata['fontsize'] = array();
foreach ($fontsizes as $individual_fontsize) {
    if ($individual_fontsize == $userObject->get_textsize()) {
        $accessibilitydata['fontsize'][$individual_fontsize] = true;
    } else {
        $accessibilitydata['fontsize'][$individual_fontsize] = false;
    }
}

$fontfamily = array('Arial', 'Arial Black', 'Calibri', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Tahoma', 'Times New Roman', 'Verdana');
$accessibilitydata['fontfamily'] = array();
foreach ($fontfamily as $individual_fontfamily) {
    if ($individual_fontfamily == $userObject->get_font()) {
        $accessibilitydata['fontfamily'][$individual_fontfamily] = true;
    } else {
        $accessibilitydata['fontfamily'][$individual_fontfamily] = false;
    }
}

$accessibilitydata['colours'] = array(
    array(
        'name' => $string['background'],
        'value' => $userObject->get_bgcolor(''),
        'radio_name' => 'bg',
        'input_name' => 'background'
    ),
    array(
        'name' => $string['foreground'],
        'value' => $userObject->get_fgcolor(''),
        'radio_name' => 'fg',
        'input_name' => 'foreground'
    ),
    array(
        'name' => $string['markscolour'],
        'value' => $userObject->get_marks_color(''),
        'radio_name' => 'marks',
        'input_name' => 'marks_color'
    ),
    array(
        'name' => $string['themecolour'],
        'value' => $userObject->get_themecolor(''),
        'radio_name' => 'theme',
        'input_name' => 'themecolor'
    ),
    array(
        'name' => $string['labelscolour'],
        'value' => $userObject->get_labelcolor(''),
        'radio_name' => 'labels',
        'input_name' => 'labelcolor'
    ),
    array(
        'name' => $string['unanswered'],
        'value' => $userObject->get_unanswered_color(''),
        'radio_name' => 'unanswered',
        'input_name' => 'unansweredcolor'
    ),
    array(
        'name' => $string['dismisscolor'],
        'value' => $userObject->get_dismiss_color(''),
        'radio_name' => 'dismiss',
        'input_name' => 'dismisscolor'
    ),
    array(
        'name' => $string['highlightcolour'],
        'value' => $userObject->getHighlightBackgroundColour(''),
        'radio_name' => 'colour_highlight',
        'input_name' => 'highlightcolour'
    ),
    array(
        'name' => $string['globalthemecolour'],
        'value' => $userObject->getPaperGlobalThemeColour(''),
        'radio_name' => 'colour_globaltheme',
        'input_name' => 'globalthemecolour'
    ),
    array(
        'name' => $string['globalthemefontcolour'],
        'value' => $userObject->getPaperGlobalThemeFontcolour(''),
        'radio_name' => 'colour_globalthemefont',
        'input_name' => 'globalthemefontcolour'
    ),
);
$accessibilitydata['userid'] = $userObject->get_user_ID();
$accessibilitydata['username'] = $userObject->get_username();
$render->render($accessibilitydata, $string, 'students/settings.html');

$render->render(array(), array(), 'footer.html');
