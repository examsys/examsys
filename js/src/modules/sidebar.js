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
// Sidebar
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jsxls', 'rogoconfig', 'jquery', 'jqueryui'], function(jsxls, config, $) {
    return function() {
        /**
         * Initialise sidebar.
         */
        this.init = function() {
            this.scrollLine = 0;
            this.myUpInterval = 0;
            this.myDownInterval = 0;
            var scope = this;
            $('.scrollup').mouseover(function() {
                var id = $(this).attr('data-menuno');
                var options = JSON.parse($("#popupmenu" + id).attr('data-myOptions'));
                var urls = JSON.parse($("#popupmenu" + id).attr('data-myURLs'));
                scope.scrollUpStart('popup' + id, options, urls);
            });

            $('.scrollup').mouseout(function() {
                scope.scrollUpEnd();
            });

            $('.scrolldown').mouseover(function() {
                var id = $(this).attr('data-menuno');
                var options = JSON.parse($("#popupmenu" + id).attr('data-myOptions'));
                var urls = JSON.parse($("#popupmenu" + id).attr('data-myURLs'));
                scope.scrollDownStart('popup' + id, options, urls);
            });

            $('.scrolldown').mouseout(function() {
                scope.scrollDownEnd();
            });

            $('.showmenu').click(function(e) {
                var options = JSON.parse($("#popupmenu" + $(this).attr('data-popupid')).attr('data-myOptions'));
                var urls = JSON.parse($("#popupmenu" + $(this).attr('data-popupid')).attr('data-myURLs'));
                var id = 'popup' + $(this).attr('data-popupid');
                var type = $(this).attr('data-popuptype');
                var name = $(this).attr('data-popupname');
                scope.showMenu(id, type, name, options, urls, e);
            });

            $('.popup').mouseleave(function(e) {
                // FF/IE trigger mouseleave incorrectly on dropdown use so ignore.
                if (e.target.tagName !== 'SELECT') {
                    scope.hideMenus();
                }
            });
        };

        /**
         * Scroll up the menu
         * menus have a hardcoded display number of 20
         * @param integer submenuID submenu id
         * @param array arrayID array of submenu items
         * @param array urlID array of submenu urls
         */
        this.scrollUpStart = function (submenuID, arrayID, urlID) {
            this.myUpInterval = window.setInterval(function () {
                if (this.scrollLine > 0) {
                    this.scrollLine--;
                    var limit = (this.scrollLine + 19);
                    if (limit >= arrayID.length) {
                        limit = arrayID.length-1;
                    }
                    var line = 0;
                    for (var i = this.scrollLine; i <= limit; i++) {
                        var submenuItemID = submenuID.substr(5,1) + '_' + line;
                        if (urlID[i].substr(0,1) == '-') {
                            $('#' + submenuItemID).html('<hr nonshade="nonshade" style="height:1px; border:none; background-color:#C0C0C0; color:#C0C0C0" />');
                            $('#' + submenuItemID).attr('onclick', "window.location=''");
                        } else if (urlID[i].substr(0,1) == '#') {
                            $('#' + submenuItemID).html(urlID[i].substr(1));
                        } else {
                            $('#' + submenuItemID).html(arrayID[i]);
                            $('#' + submenuItemID).attr('onclick', "window.location='" + urlID[i] + "'");
                        }
                        line++;
                    }
                    var downID = submenuID.substr(5,1) + '_down';
                    $('#' + downID).html('<img src="' + config.cfgrootpath + '/artwork/submenu_down_on.png" width="9" height="5" alt="'+ jsxls.lang_string["down"] + '" />&nbsp;');
                } else {
                    var upID = submenuID.substr(5,1) + '_up';
                    $('#' + upID).html('<img src="' + config.cfgrootpath + '/artwork/submenu_up_off.png" width="9" height="5" alt="'+ jsxls.lang_string["up"] + '" />&nbsp;');
                    clearInterval(this.myDownInterval);
                }
            }.bind(this),50);
        };

        /**
         * Stop scrolling up.
         */
        this.scrollUpEnd = function() {
            clearInterval(this.myUpInterval);
        };

        /**
         * Scroll down the menu
         * menus have a hardcoded display number of 20
         * @param integer submenuID submenu id
         * @param array arrayID array of submenu items
         * @param array urlID array of submenu urls
         */
        this.scrollDownStart = function(submenuID, arrayID, urlID) {
            this.myDownInterval = window.setInterval(function () {
            if (this.scrollLine < (arrayID.length-20)) {
                if (this.scrollLine == 0) {
                    var upID = submenuID.substr(5,1) + '_up';
                    $('#' + upID).html('<img src="' + config.cfgrootpath + '/artwork/submenu_up_on.png" width="9" height="5" alt="'+ jsxls.lang_string["up"] + '" />&nbsp;');
                }
                this.scrollLine++;
                var limit = (this.scrollLine + 19);
                if (limit >= arrayID.length) {
                    limit = arrayID.length-1;
                }
                var line = 0;
                for (var i = this.scrollLine; i <= limit; i++) {
                    var submenuItemID = submenuID.substr(5,1) + '_' + line;
                    if (urlID[i].substr(0,1) == '-') {
                        $('#' + submenuItemID).html('<hr nonshade="nonshade" style="height:1px; border:none; background-color:#C0C0C0; color:#C0C0C0" />');
                        $('#' + submenuItemID).attr('onclick', "window.location=''");
                    } else if (urlID[i].substr(0,1) == '#') {
                        $('#' + submenuItemID).html(urlID[i].substr(1));
                    } else {
                        $('#' + submenuItemID).html(arrayID[i]);
                        $('#' + submenuItemID).attr('onclick', "window.location='" + urlID[i] + "'");
                    }
                    line++;
                }
            } else {
                var downID = submenuID.substr(5,1) + '_down';
                $('#' + downID).html('<img src="' + config.cfgrootpath + '/artwork/submenu_down_off.png" width="9" height="5" alt="'+ jsxls.lang_string["down"] + '" />&nbsp;');
                clearInterval(this.myDownInterval);
            }
            }.bind(this),50);
        };

        /**
         * Stop scrolling down.
         */
        this.scrollDownEnd = function() {
            clearInterval(this.myDownInterval);
        };

        /**
         * Display menu overlay.
         * @param integer submenuID submenu id
         * @param integer menuID menu id
         * @param integer callingID id of calling item
         * @param array arrayID array of item ids
         * @param array urlID array of item urls
         * @param object e event
         * @returns bool
         */
        this.showMenu = function(submenuID, menuID, callingID, arrayID, urlID, e) {
            var scope = this;
            this.scrollLine = 0;

            var limit = (this.scrollLine + 19);
            if (limit >= arrayID.length) {
                limit = arrayID.length-1;
            }
            if (arrayID.length > 20) {
                var upID = submenuID.substr(5,1) + '_up';
                $('#' + upID).html('<img src="' + config.cfgrootpath + '/artwork/submenu_up_off.png" width="9" height="5" alt="'+ jsxls.lang_string["up"] + '" />&nbsp;');
                var downID = submenuID.substr(5,1) + '_down';
                $('#' + downID).html('<img src="' + config.cfgrootpath + '/artwork/submenu_down_on.png" width="9" height="5" alt="'+ jsxls.lang_string["down"] + '" />&nbsp;');
            }
            var line = 0;
            for (var i = this.scrollLine; i <= limit; i++) {
                var submenuItemID = submenuID.substr(5,1) + '_' + line;
                if (urlID[i].substr(0,1) == '-') {
                    $('#' + submenuItemID).html('<hr nonshade="nonshade" style="height:1px; border:none; background-color:#C0C0C0; color:#C0C0C0" />');
                    $('#' + submenuItemID).attr('onclick', "window.location=''");
                } else if (urlID[i].substr(0,1) == '#') {
                    $('#' + submenuItemID).html(urlID[i].substr(1));
                } else {
                    $('#' + submenuItemID).html(arrayID[i]);
                    $('#' + submenuItemID).attr('onclick', "window.location='" + urlID[i] + "'");
                }
                line++;
            }

            if (!e) e = window.event;
            if ($('#' + submenuID).css('display') != 'block') {
                scope.hideMenus(e);
                $('#' + submenuID).show();
            } else {
                scope.hideMenus(e);
            }
            var popupHeight = $('#' + submenuID).height();

            var sidebarHeight = $('#left-sidebar').height();

            var mytop = $('#' + callingID).offset().top - $(document).scrollTop();
            if ((mytop + popupHeight) > sidebarHeight) {
                mytop = sidebarHeight - popupHeight - 6;
            }
            $('#' + submenuID).css('top', mytop + 'px');

            e.cancelBubble = true;

            return false;
        };

        /**
         * Hide menu overlay.
         */
        this.hideMenus = function() {
            $(".popup").each(function() {
                $(this).hide();
            });
        }
    }
});

