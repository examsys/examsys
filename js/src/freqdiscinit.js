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
//
// Initialise frequency discrimination analysis page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['media', 'html5', 'qlabelling', 'helplauncher', 'freqdisc', 'jquery'], function (Media, Html5, Qlabelling, Helplauncher, Freq, $) {
    var media = new Media();
    media.init();
    var freqdisc = new Freq();
    var language = $('#dataset').attr('data-language');

    $('.blacklink').click(function() {
        Helplauncher.launchHelp(189, 'staff');
    });

    var interactive = new Html5();
    interactive.init($('#dataset').attr('data-rootpath'));
    $(".q_no").each(function() {
        switch ($(this).attr('data-qtype')) {
            case 'labelling':
                var label = new Qlabelling();
                label.setUpLabelling($(this).attr('data-qno'), "flash" + $(this).attr('data-qno'), language, $(this).attr('data-qmedia'), $(this).attr('data-qcorrect'), "", "", "#FFC0C0", "analysis");
                break;
            default:
                break;
        }
    });

    $('.calccorrect').click(function() {
        freqdisc.calcCorrect($(this).attr('data-qid'));
    });

    $('.blankcorrect').click(function() {
        freqdisc.blankCorrect($(this).attr('data-qid'), $(this).attr('data-i'));
    });

    $('.in-exclusion').click(function() {
        freqdisc.toggle($(this).attr('data-id'), $(this).attr('data-parts'), $(this).attr('data-marks'));
    });
});
