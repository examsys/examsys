// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.
//
// Requirejs configuration for the backend i.e. admin pages.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//

/**
 * Gets the base path needed for RequireJS
 *
 * jQuery must not be used as it will not have been loaded yet.
 *
 * @return {String}
 */
function baseUrl() {
    var root = document.getElementById('rogoconfig').getAttribute('data-root');
    return root + '/';
}

requirejs.config({
    // By default load any module IDs from js.
    baseUrl: baseUrl(),
    // paths to modules.
    paths: {
        jquery: "node_modules/jquery/dist/jquery.min",
        jqueryvalidate: "js/jquery.validate.min",
        jqueryui: "js/jquery-ui-1.10.4.min",
        jquerytablesorter: "js/jquery.tablesorter.min",
        qunit: "node_modules/qunit/qunit/qunit",
        mathjax: "node_modules/mathjax/MathJax.js?config=TeX-MML-AM_HTMLorMML&amp;delayStartupUntil=configured",
        editor: "js/modules/editor.min",
        tinyMCE: "plugins/texteditor/plugin_tinymce3_texteditor/tinymce/jscripts/tiny_mce/tiny_mce",
        three: "node_modules/three/build/three.min",
        colourpicker: "tools/colour_picker/js/colour_picker.min",
        campuses: "admin/campus/js/campuses.min",
        campusesvalidate: "admin/campus/js/campuses_validate.min",
        extsys: "admin/external/js/extsys.min",
        extsysvalidate: "admin/external/js/extsys_validate.min",
        oauth: "admin/oauth/js/oauth.min",
        oauthclients: "admin/oauth/js/oauthclients.min",
        oauthvalidate: "admin/oauth/js/oauthclients_validate.min",
        plugins: "admin/plugins/js/plugins.min",
        pluginsvalidate: "admin/plugins/js/plugins_validate.min",
        ltisearchusers: "LTI/js/search_users.min",
        rogoconfig: "js/modules/requireconfig.min",
        jsxls: "js/modules/jsxls.min",
        lang: "js/modules/lang.min",
        textboxmarking: "js/modules/textboxmarking.min",
        start: "js/modules/start.min",
        systemtooltips:"js/modules/system_tooltips.min",
        helplauncher: "js/modules/helplauncher.min",
        classtotals: "js/modules/class_totals.min",
        toprightmenu: "js/modules/toprightmenu.min",
        help: "js/modules/help.min",
        hofstee: "js/modules/hofstee.min",
        questionedit: "js/modules/questionedit.min",
        questioneditcalc: "plugins/questions/enhancedcalc/js/modules/questionedit.min",
        questioneditextmatch: "plugins/questions/extmatch/js/modules/questionedit.min",
        questioneditsct: "plugins/questions/sct/js/modules/questionedit.min",
        questioneditlikert: "plugins/questions/likert/js/modules/questionedit.min",
        questioneditdich: "plugins/questions/dichotomous/js/modules/questionedit.min",
        questioneditblank: "plugins/questions/blank/js/modules/questionedit.min",
        questioneditrandom: "plugins/questions/random/js/modules/questionedit.min",
        questionstartmrq: "plugins/questions/mrq/js/modules/start.min",
        questionstartrank: "plugins/questions/rank/js/modules/start.min",
        questionstartextmatch: "plugins/questions/extmatch/js/modules/start.min",
        datecopy: "js/modules/datecopy.min",
        questionmapping: "js/modules/questionmapping.min",
        paperdetails: "js/modules/paperdetails.min",
        leadinpopup: "js/modules/questionleadinpopup.min",
        questionstatus: "js/modules/questionstatus.min",
        qualitative: "js/modules/qualitative.min",
        typecoursefilter: "js/modules/typecoursefilter.min",
        list: "js/modules/list.min",
        errorlist: "js/modules/errorlist.min",
        facultieslist: "js/modules/facultieslist.min",
        summativedetails: "js/modules/summativedetails.min",
        modulessidebar: "js/modules/modulessidebar.min",
        courseslist: "js/modules/courseslist.min",
        ebellist: "js/modules/ebellist.min",
        sessionslist: "js/modules/sessionslist.min",
        sessions: "js/modules/sessions.min",
        schoolslist: "js/modules/schoolslist.min",
        keywordlist: "js/modules/keywordlist.min",
        modules: "js/modules/modules.min",
        popupmenu: "js/modules/popup_menu.min",
        peerreview: "js/modules/peerreview.min",
        performance: "js/modules/performance.min",
        osce: "js/modules/osce.min",
        user: "js/modules/user.min",
        usersearch: "js/modules/usersearch.min",
        usermodules: "js/modules/usermodules.min",
        state: "js/modules/state.min",
        alert: "js/modules/alert.min",
        recyclelist: "js/modules/recyclelist.min",
        questionsearch: "js/modules/questionsearch.min",
        questionlist: "js/modules/questionlist.min",
        sidebar: "js/modules/sidebar.min",
        stdset: "js/modules/stdset.min",
        admin: "js/modules/admin.min",
        papertype: "js/modules/papertype.min",
        moduleoptions: "js/modules/moduleoptions.min",
        folder: "js/modules/folder.min",
        folderproperties: "js/modules/folderproperties.min",
        papersidebar: "js/modules/papersidebar.min",
        menu: "js/modules/menu.min",
        stdsetreview: "js/modules/stdsetreview.min",
        paperproperties: "js/modules/paperproperties.min",
        freqdisc: "js/modules/freqdisc.min",
        review: "js/modules/review.min",
        reviewcomment: "js/modules/reviewcomment.min",
        reference: "js/modules/reference.min",
        userindex: "js/modules/userindex.min",
        qti: "js/modules/qti.min",
        register: "js/modules/register.min",
        calcremark: "plugins/questions/enhancedcalc/js/modules/remark.min",
        student: "js/modules/student.min",
        reassignuser: "js/modules/reassignuser.min",
        addquestionsbuttons: "js/modules/addquestionsbuttons.min",
        addquestionscommon: "js/modules/addquestionscommon.min",
        keywordsquestionlist: "js/modules/keywordsquestionlist.min",
        lablist: "js/modules/lablist.min",
        form: "js/modules/form.min",
        moduleform: "js/modules/moduleform.min",
        newpaperform: "js/modules/newpaperform.min",
        studentnote: "js/modules/studentnote.min",
        calendar: "js/modules/calendar.min",
        referancelist: "js/modules/referancelist.min",
        mapping: "js/modules/mapping.min",
        mappingsessions: "js/modules/mappingsessions.min",
        mappingsessionlist: "js/modules/mappingsessionlist.min",
        mappingsidebar: "js/modules/mappingsidebar.min",
        textboxfinalise: "js/modules/textbox_finalise.min",
        ui: "js/modules/ui.min",
        questionlink: "js/modules/questionlink.min",
        invigilator: "js/modules/invigilator.min",
        announcement: "js/modules/announcement.min",
        lti: "js/modules/lti.min",
        tabs: "js/modules/tabs.min",
        answer_hotspot: "js/modules/html_questions/html5.answer.hotspot.min",
        html5helper: "js/modules/html_questions/html5.helper.min",
        html5images: "js/modules/html_questions/html5.image.min",
        html5: "js/modules/html_questions/html5.min",
        hotspot_listener: "js/modules/html_questions/html5.listener.hotspot.min",
        html5listener: "js/modules/html_questions/html5.listener.min",
        html5_button: "js/modules/html_questions/html5.menu.button.min",
        html5_chk: "js/modules/html_questions/html5.menu.checkbox.min",
        html5_filler: "js/modules/html_questions/html5.menu.filler.min",
        html5_group: "js/modules/html_questions/html5.menu.group.min",
        hotspot_layerzone: "js/modules/html_questions/html5.menu.hotspot.layerzone.min",
        html5_menuitem: "js/modules/html_questions/html5.menu.item.min",
        html5_menu: "js/modules/html_questions/html5.menu.min",
        hotspot_analysis: "js/modules/html_questions/html5.question.hotspot.analysis.min",
        hotspot_answer: "js/modules/html_questions/html5.question.hotspot.answer.min",
        hotspot_colourselector: "js/modules/html_questions/html5.question.hotspot.colourselector.min",
        hotspot_correction: "js/modules/html_questions/html5.question.hotspot.correction.min",
        hotspot_edit: "js/modules/html_questions/html5.question.hotspot.edit.min",
        hotspot: "js/modules/html_questions/html5.question.hotspot.min",
        hotspot_layer: "js/modules/html_questions/html5.question.hotspot.layer.min",
        hotspot_script: "js/modules/html_questions/html5.question.hotspot.script.min",
        hotspot_shape: "js/modules/html_questions/html5.question.hotspot.shape.min",
        hotspot_standardset: "js/modules/html_questions/html5.question.hotspot.standardset.min",
        html5_question: "js/modules/html_questions/html5.question.min",
        qsharedf: "js/modules/html5/qsharedf.min",
        qarea: "js/modules/html5/qarea.min",
        qlabelling: "js/modules/html5/qlabelling.min",
        mathjaxpreview: "js/modules/mathjax/preview.min",
        log: "js/modules/log.min",
        threeshared: "js/modules/media/three/threeshared.min",
        TrackballControls: "js/modules/media/three/controlers/TrackballControls.min",
        CSS2DRenderer: "js/modules/media/three/renderers/CSS2DRenderer.min",
        CSS2DObject: "js/modules/media/three/objects/CSS2DObject.min",
        PDBLoader: "js/modules/media/three/loaders/PDBLoader.min",
        PLYLoader: "js/modules/media/three/loaders/PLYLoader.min",
        DDSLoader: "js/modules/media/three/loaders/DDSLoader.min",
        OBJLoader: "js/modules/media/three/loaders/OBJLoader.min",
        MTLLoader: "js/modules/media/three/loaders/MTLLoader.min",
        MaterialCreator: "js/modules/media/three/objects/MaterialCreator.min",
        ParserState: "js/modules/media/three/objects/ParserState.min",
        obj: "js/modules/media/three/obj.min",
        ply: "js/modules/media/three/ply.min",
        pdb: "js/modules/media/three/pdb.min",
        jqueryvalidatecalc: "plugins/questions/enhancedcalc/js/modules/validateuser.min",
        jquerycalc: "plugins/questions/enhancedcalc/js/modules/validatequestion.min",
        jqueryleadinonly: "js/modules/validation/jquery.leadin-only.min",
        jqueryarea: "plugins/questions/area/js/modules/validatequestion.min",
        jqueryhotspot: "plugins/questions/hotspot/js/modules/validatequestion.min",
        jqueryblank: "plugins/questions/blank/js/modules/validatequestion.min",
        jquerydich: "plugins/questions/dichotomous/js/modules/validatequestion.min",
        jqueryextmatch: "plugins/questions/extmatch/js/modules/validatequestion.min",
        jqueryinfo: "plugins/questions/info/js/modules/validatequestion.min",
        jquerykeyword: "plugins/questions/keyword_based/js/modules/validatequestion.min",
        jquerylikert: "plugins/questions/likert/js/modules/validatequestion.min",
        jquerymatrix: "plugins/questions/matrix/js/modules/validatequestion.min",
        jquerymcq: "plugins/questions/mcq/js/modules/validatequestion.min",
        jquerymrq: "plugins/questions/mrq/js/modules/validatequestion.min",
        jqueryrandom: "plugins/questions/random/js/modules/validatequestion.min",
        jqueryrank: "plugins/questions/rank/js/modules/validatequestion.min",
        jquerysct: "plugins/questions/sct/js/modules/validatequestion.min",
        jquerytextbox: "plugins/questions/textbox/js/modules/validatequestion.min",
        mejs: "node_modules/mediaelement/build/mediaelement-and-player.min",
        mejs_cs: "node_modules/mediaelement/build/lang/cs",
        mejs_pl: "node_modules/mediaelement/build/lang/pl",
        mejs_sk: "node_modules/mediaelement/build/lang/sk",
        media: "js/modules/media.min",
        polyfill: "js/modules/polyfill.min",
        rolelist: "js/modules/rolelist.min",
    },
    shim: {
        // Mathjax configration.
        mathjax: {
            exports: "MathJax",
            init: function () {
                MathJax.Hub.Config({
                    messageStyle: "none",
                    showMathMenu: false,
                    showMathMenuMSIE: false,
                    tex2jax: {inlineMath: [['$$','$$'], ['[tex]', '[/tex]'], ['[texi]', '[/texi]']], displayMath: [['$$$','$$$']]}
                });
                MathJax.Hub.Startup.onload();
                return MathJax;
            }
        },
        tinyMCE: {
            exports: 'tinyMCE',
            init: function () {
                return this.tinyMCE;
            },
        },
        mejs: {
            deps: ['require'],
            exports: "mejs",
        },
        mejs_cs: {
            deps: ['mejs'],
        },
        mejs_pl: {
            deps: ['mejs'],
        },
        mejs_sk: {
            deps: ['mejs'],
        },
        // Non AMD libraries that need jquery.
        jqueryvalidate: ["jquery"],
        jqueryui: ["jquery"],
        jquerytablesorter: ["jquery"],
    },
});

// Read Rogo configuration and enable/disable functionality accordingly
requirejs(['rogoconfig'], function(config) {
    if (config.mathjax) {
        requirejs(['mathjax']);
    }
});

requirejs(['systemtooltips'], function (TOOLTIPS) {
    var tooltips = new TOOLTIPS();
    tooltips.init();
});

requirejs(['helplauncher', 'jquery'], function (HELPLAUNCHER, $) {
    $(function() {
        $('#helplink').click(function () {
            HELPLAUNCHER.launchHelp($(this).attr('data-id'), $(this).attr('data-role'));
        });
    });
});

requirejs(['toprightmenu'], function (MENU) {
    var menu = new MENU();
    menu.init();
});

requirejs(['ui'], function (UI) {
    var ui = new UI();
    ui.init();
});

requirejs(['polyfill'], function (Polyfill) {
    var polyfill = new Polyfill();
    polyfill.init();
});
