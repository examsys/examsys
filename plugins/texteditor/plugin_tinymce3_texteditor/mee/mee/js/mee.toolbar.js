$.Class.extend("MEE.Toolbar",
{
    //#region static stuff, keep track of all instances of the main classes here
    defs: {},
    activetab: null,
    popupmenuid: 1,
    curpopupmenu: null,
    nomouseover: false,
    leavecount: 0,
    toolbarelem: false,
    toolbaralwayson: false,

    images: {
        '22x22': 1,
        '102x22': 1,
        '220x58': 1,
        '220x32': 1,
        '220x35': 1,
        '60x69': 1,
        '75x22': 1,
        '110x22': 1,
        '53x24': 1,
        '50x24': 1,
        '100x24': 1,
        '100x36': 1,
        '32x32': 1,
        '96x96': 1,
        '160x36': 1,
        '120x36': 1,
        '64x64': 1,
        '100x22': 1,
        '96x24': 1,
        '130x24': 1,
        '64x24': 1
    },

    appendElement: function (base, element, classlist, content) {
        var newelem = $('<' + element + '>');
        if (classlist) {
            $(newelem).addClass(classlist);
        }
        if (content) {
            $(newelem).html(content);
        }
        $(base).append(newelem);
        return newelem;
    },

    AddImage: function (parelem, image) {
        var div = $('<span>');
        parelem.append(div);

        var imgdata = MEE.Toolbar.ApplyImage(div, image);
        if (!imgdata)
            return div;

        $(div).css('width', imgdata.width + 'px');
        $(div).css('height', imgdata.height + 'px');
        $(div).css('display', 'block');

        return div;
    },

    ApplyImage: function (element, image, size, repeat) {
        if (image in MEE.Data.images) {
            var imgdata = MEE.Data.images[image];
            $(element).css('background-image', 'url(' + mee_baseurl + 'images/combined.png)');
            $(element).css('background-position', (-imgdata.left) + 'px ' + (-imgdata.top) + 'px');

            if (size)
                $(element).css('background-size', (imgdata.width) + 'px ' + (imgdata.height) + 'px');

            if (repeat)
                $(element).css('background-repeat', repeat);
            else
                $(element).css('background-repeat', 'no-repeat');

            return imgdata;
        } else {
            $(element).css('background-image', 'url(' + mee_baseurl + 'images/' + image + ')');
            $(element).css('background-position', '');
            $(element).css('background-repeat', '');
        }
    }
},
{
    // main class for dealing with a equation editor element
    tbbase: null,
    inputelem: null,
    eventsinit: false,

    init: function (element) {
        this.inputelem = element;
        //this.tbbase = MEE.Toolbar.getToolbarFromClass(element);
        this.currentEdit = null;
        this.orbmenu = true;
        this.showtabs = true;
        
        this.type = 'default';
        
        if($(element).hasClass('sci')) {
          this.type = 'sci';
        }
        
        if($(element).hasClass('units')) {
          this.type = 'units';
        }
    },

    loadToolBar: function (tbtype) {
        
        //default editor
        var xmlurl = 'toolbar/toolbar.xml';   
        var callbackfn = "buildToolBar";
        
        //scientific notation editor
        if(tbtype != 'default') {
          xmlurl = 'toolbar/toolbar_' + tbtype + '.xml';   
          callbackfn = "buildToolBarSingleFunction";
          this.toolbaralwayson = true;
        }
        
        $.ajax({
            type: "GET",
            url: mee_baseurl + xmlurl,
            dataType: "xml",
            success: this.callback(callbackfn)
        });
    },
    
    buildToolBarSingleFunction: function (temp, xml) {
        var toolbar = $.xml2json(xml); // this.xmlToToolbar(xml); // MEE.Toolbar.defs[this.tbbase];
        //MEE.Toolbar.defs["xml"] = toolbar;
        var main = $('<div>');
        this.html_elem = main;
        main.addClass('mee_toolbar_single');
        this.toolbarelem = main;
        for(var k = 0; k < toolbar.items.length; k++) {
          var paneitem = toolbar.items[k];
        
          if (!paneitem.sections) {
            
            if(!paneitem.latex)
                continue
              
            temp = MEE.Toolbar.appendElement(main, "div", "tb_symbol");
            temp.data('latex', paneitem.latex);
            if (paneitem.command)
                temp.data('command', paneitem.command);

            MEE.Toolbar.appendElement(temp, "a", "", paneitem.display);

          } else {
            var menuclass = "tb_menu_small";
            var menuimg = "toolbar/arrow_down.png";
            var menuitemconta = MEE.Toolbar.appendElement(main, "div", menuclass);
            menuitemconta.attr('id', "popupmenubutton" + MEE.Toolbar.popupmenuid);
            menuitemconta.attr('popuptype', 'menus');
            menuitemconta.attr('popupmenu', MEE.Toolbar.popupmenuid);
            menuitemconta.addClass('tb_menu_button');

            var menuitemcont = MEE.Toolbar.appendElement(menuitemconta, "a");
            menuitemcont.attr('popupmenu', MEE.Toolbar.popupmenuid);

            temp = MEE.Toolbar.appendElement(menuitemcont, "div", "icon");

            if (paneitem.image) {
                var img = MEE.Toolbar.AddImage(temp, 'tbicons/' + paneitem.image);
                img.css('margin-left', '14px');
                img.css('margin-right', '14px');
            }
            temp.attr("alt", paneitem.display);

            temp = MEE.Toolbar.appendElement(menuitemcont, "div", "text", paneitem.display);

            temp = MEE.Toolbar.appendElement(menuitemcont, "div", "arrow");
            var img = MEE.Toolbar.AddImage(temp, menuimg);
            
            img.css('margin-left', '12px');
            img.css('margin-right', '12px');
            
                        
            var menupopup = MEE.Toolbar.appendElement(main, "div", "tb_popupmenu");
           
            menupopup.addClass("tb_popupmenu_vert");

            menupopup.css("display", "none");
            menupopup.attr('id', "popupmenu" + MEE.Toolbar.popupmenuid);
            var menuinner = MEE.Toolbar.appendElement(menupopup, "div", "tb_popupmenu_inner");
            var menufooter = MEE.Toolbar.appendElement(menupopup, "div", "tb_popupmenu_footer");

            if (paneitem.popupwidth)
                menuinner.css('width', paneitem.popupwidth + 'px');
                          
            // add panes to popup menu
            for (var p = 0; p < paneitem.sections.length; p++) {
              var section = paneitem.sections[p];
              if (!section) continue;

              MEE.Toolbar.appendElement(menuinner, "div", "tbpm_header", section.heading);
              MEE.Toolbar.appendElement(menuinner, "div", "clear");

              // add items to popup menu
              if (section.items) {
                  for (var q = 0; q < section.items.length; q++) {
                      var item = section.items[q];
                      if (!item) continue;

                      var _class = "tbpm_item";
                      if (section._class)
                          _class = section._class;
                      var item_elem = MEE.Toolbar.appendElement(menuinner, "div", _class);
                      if (item._class) {
                          item_elem.addClass(item._class);
                          item_elem.data('class', item._class);
                      }

                      if (section.listwidth)
                          item_elem.css('width', section.listwidth + 'px');

                      item_elem.data('latex', item.latex);

                      if (item.wlatex)
                          item_elem.data('wlatex', item.wlatex);
                      if (item.mlatex)
                          item_elem.data('mlatex', item.mlatex);

                      item_elem.data('text', item.display);

                      if (item.command)
                          item_elem.data('command', item.command);

                      item_elem = MEE.Toolbar.appendElement(item_elem, "a");
                      var div2 = MEE.Toolbar.appendElement(item_elem, "div", "icon");
                      if (item.image) {
                          //var img = MEE.Toolbar.appendElement(div2, "img");
                          //img.attr('src', mee_baseurl + 'images/tbicons/' + item.image);
                          MEE.Toolbar.AddImage(div2, 'tbicons/' + item.image);
                      }
                      MEE.Toolbar.appendElement(item_elem, "div", "label", item.display);
                  }
              }

          }
          MEE.Toolbar.popupmenuid++;
        }
      }
    },
    
    //#region Build Toolbar html
    buildToolBar: function (temp, xml) {
        var toolbar = $.xml2json(xml); // this.xmlToToolbar(xml); // MEE.Toolbar.defs[this.tbbase];
        //MEE.Toolbar.defs["xml"] = toolbar;
        var main = $('<div>');
        this.html_elem = main;
        main.addClass('mee_toolbar');
        var main_list = MEE.Toolbar.appendElement(main, "div", "mt_main");
        var tabs_list = MEE.Toolbar.appendElement(main, "div", "mt_tabs");
        this.toolbarelem = main;
        
        // add orb menu to tree
        if( this.orbmenu == true) {
          var home = MEE.Toolbar.appendElement(main_list, "div", "mt_home");
          var home_link = MEE.Toolbar.appendElement(home, "a", "", "");
          home_link.attr('href', '#home');

          var homemenu = MEE.Toolbar.appendElement(main, "div", "mt_home_popup");
          var homeinner = MEE.Toolbar.appendElement(homemenu, "div", "mt_home_inner");

          for (var k = 0; k < toolbar.home.items.length; k++) {
              var data = toolbar.home.items[k];
              if (data.spacer) {
                  var homeitem = MEE.Toolbar.appendElement(homeinner, "div", "mt_home_popup_spacer");
              } else {
                  var homeitem = MEE.Toolbar.appendElement(homeinner, "div", data._class);
                  var image = MEE.Toolbar.AddImage(homeitem, 'toolbar/' + data.image);


                  var text = MEE.Toolbar.appendElement(homeitem, "span", "mt_home_popup_item_label");
                  if (data.checked == 1 || data.checked == -1) {

                      var check = null; // MEE.Toolbar.appendElement(homeitem, "img", "mt_home_popup_check");

                      if (data.checked == 1) {
                          check = MEE.Toolbar.AddImage(homeitem, 'toolbar/home_tick.png');
                          //check.attr("src", mee_baseurl + 'images/toolbar/home_tick.png');
                      } else {
                          check = MEE.Toolbar.AddImage(homeitem, 'toolbar/home_tick_blank.png');
                          //check.attr("src", mee_baseurl + 'images/toolbar/home_tick_blank.png');
                      }
                      check.addClass('mt_home_popup_check');
                      if (data.id) {
                          check.attr('id', data.id + '_check');
                      }
                  }
                  text.html(data.name);
                  if (data.command) {
                      homeitem.data('command', data.command);
                      homeitem.click(this.callback('itemClick'));
                  }

                  if (data.id) {
                      homeitem.attr('id', 'tb_home_' + data.id);
                      image.attr('id', 'tb_home_' + data.id + '_img');
                  }
              }
          }
        }
        // create tabs
        for (var r = 0; r < toolbar.tabs.length; r++) {
            var tab = toolbar.tabs[r];
            if (!tab) continue;

            // create tab and header link
            var tab_elem = MEE.Toolbar.appendElement(main_list, "div", "mt_tab");

            if (tab.visibility) {
                tab_elem.data('visibility', tab.visibility);
            }

            tab_elem.data('id', 'mee_tab_' + tab.id);
            tab_elem.attr('id', 'mee_tab_link_' + tab.id);

            var link = MEE.Toolbar.appendElement(tab_elem, "a", "", tab.name);
            link.attr('href', '#' + 'mee_tab_' + tab.id);


            // create panes container
            var panes = MEE.Toolbar.appendElement(tabs_list, "div", "mt_tabblock");
            panes.attr('id', 'mee_tab_' + tab.id);

            if (!tab.panes) continue;

            // for all panes, add em to panes container
            for (var s = 0; s < tab.panes.length; s++) {
                var pane = tab.panes[s];
                if (!pane) continue;

                var pane_cont = MEE.Toolbar.appendElement(panes, "div", "mt_tabpanecont");
                pane_cont.attr('id', 'mt_pane_' + pane.id);
                var pane_elem = MEE.Toolbar.appendElement(pane_cont, "div", "mt_tabpane");
                pane_elem.attr('id', 'mt_pane_elem_' + pane.id);
                var pane_label = MEE.Toolbar.appendElement(pane_cont, "div", "mt_tabpanelabel", pane.name);

                //var divider = MEE.Toolbar.appendElement(panes, "div", "mt_tabdivider");
                var divider = MEE.Toolbar.AddImage(panes, 'toolbar/divider.png');
                divider.addClass('mt_tabdivider');
                // add pane header name

                if (!pane.items) continue;

                var itemcount = pane.items.length;

                // if panes type is icons, then add all icons to it
                if (pane.type == "icons") {
                    for (var u = 0; u < pane.items.length; u++) {
                        var paneitem = pane.items[u];
                        if (!paneitem || !paneitem.display) {
                            itemcount--;
                            continue;
                        }

                        temp = MEE.Toolbar.appendElement(pane_elem, "div", "tb_symbol");
                        temp.data('latex', paneitem.latex);
                        if (paneitem.command)
                            temp.data('command', paneitem.command);
                        MEE.Toolbar.appendElement(temp, "a", "", paneitem.display);
                    }

                    // if pane type is menu then add all menu items
                } else if (pane.type == "bigicons") {
                    for (var u = 0; u < pane.items.length; u++) {
                        var paneitem = pane.items[u];
                        if (!paneitem || !paneitem.display) {
                            itemcount--;
                            continue;
                        }

                        temp = MEE.Toolbar.appendElement(pane_elem, "div", "tb_bigicons");

                        temp.data('latex', paneitem.latex);
                        if (paneitem.command)
                            temp.data('command', paneitem.command);
                        if (paneitem.id)
                            temp.attr('id', 'tbpm_item_' + paneitem.id);
                        if (paneitem.wlatex)
                            temp.data('wlatex', paneitem.wlatex);
                        if (paneitem.mlatex)
                            temp.data('mlatex', paneitem.mlatex);

                        var div = MEE.Toolbar.appendElement(temp, "div", "icon");
                        var img = MEE.Toolbar.AddImage(div, 'tbicons/' + paneitem.image);
                        img.css('float', 'left');
                        MEE.Toolbar.appendElement(temp, "div", "label", paneitem.display);

                    }

                    // if pane type is menu then add all menu items
                } else if (pane.type == "list") {
                    for (var u = 0; u < pane.items.length; u++) {
                        var paneitem = pane.items[u];
                        if (!paneitem) continue;

                        temp = MEE.Toolbar.appendElement(pane_elem, "div", "tb_" + pane.type);

                        temp.data('latex', paneitem.latex);
                        if (paneitem.command)
                            temp.data('command', paneitem.command);
                        if (paneitem.id)
                            temp.attr('id', 'tbpm_item_' + paneitem.id);
                        if (paneitem.wlatex)
                            temp.data('wlatex', paneitem.wlatex);
                        if (paneitem.mlatex)
                            temp.data('mlatex', paneitem.mlatex);

                        if (!paneitem.image)
                            paneitem.image = 'blank_1x16.png';

                        var div = MEE.Toolbar.appendElement(temp, "span", "icon");
                        var img = MEE.Toolbar.AddImage(div, 'tbicons/' + paneitem.image);
                        img.css('float', 'left');
                        //var img = MEE.Toolbar.appendElement(div, "img", "");
                        //img.attr("src", mee_baseurl + 'images/tbicons/' + paneitem.image);

                        var label = MEE.Toolbar.appendElement(temp, "span", "label", paneitem.display);

                        if (paneitem._class)
                            label.addClass(paneitem._class);

                        if (pane.itemwidth)
                            temp.css('width', pane.itemwidth + 'px');
                    }

                    if (!pane.itemwidth)
                        pane.itemwidth = 103;
                    itemcount = Math.ceil(pane.items.length / 3);
                    pane.width = itemcount * pane.itemwidth;
                    // if pane type is menu then add all menu items
                } else if (pane.type == "menus" || pane.type == "hmenus") {

                    var menuclass = "tb_menu";
                    var menuimg = "toolbar/arrow_down.png";
                    //var menuimg = mee_baseurl + "images/toolbar/arrow_down.png";
                    if (pane.type == "hmenus") {
                        menuclass = "tb_menu_horiz";
                        //menuimg = mee_baseurl + "images/toolbar/arrow_down-16.png";
                        menuimg = "toolbar/arrow_down-16.png";
                    }

                    // add menus to pane
                    for (var t = 0; t < pane.items.length; t++) {
                        var paneitem = pane.items[t];

                        if (!paneitem || !paneitem.display) {
                            itemcount--;
                            continue;
                        }

                        var menuitemconta = MEE.Toolbar.appendElement(pane_elem, "div", menuclass);
                        menuitemconta.attr('id', "popupmenubutton" + MEE.Toolbar.popupmenuid);
                        menuitemconta.attr('popuptype', pane.type);
                        menuitemconta.attr('popupmenu', MEE.Toolbar.popupmenuid);
                        menuitemconta.addClass('tb_menu_button');

                        var menuitemcont = MEE.Toolbar.appendElement(menuitemconta, "a");
                        menuitemcont.attr('popupmenu', MEE.Toolbar.popupmenuid);

                        temp = MEE.Toolbar.appendElement(menuitemcont, "div", "icon");
                        
                        if (paneitem.image) {
                            var img = MEE.Toolbar.AddImage(temp, 'tbicons/' + paneitem.image);
                            if (pane.type == "menus") {
                                img.css('margin-left', '14px');
                                img.css('margin-right', '14px');
                            }

                        }
                        temp.attr("alt", paneitem.display);

                        temp = MEE.Toolbar.appendElement(menuitemcont, "div", "text", paneitem.display);

                        temp = MEE.Toolbar.appendElement(menuitemcont, "div", "arrow");
                        var img = MEE.Toolbar.AddImage(temp, menuimg);
                        if (pane.type == "menus") {
                            img.css('margin-left', '27px');
                            img.css('margin-right', '27px');
                        }
                      
                        if (!paneitem.sections) continue;

                        // add menu popup div to document
                        var menupopup = MEE.Toolbar.appendElement(main, "div", "tb_popupmenu");
                        //var menupopup = MEE.Toolbar.appendElement(pane_elem, "div", "tb_popupmenu");
                        if (pane.type == "hmenus")
                            menupopup.addClass("tb_popupmenu_horiz");
                        else
                            menupopup.addClass("tb_popupmenu_vert");

                        menupopup.css("display", "none");
                        menupopup.attr('id', "popupmenu" + MEE.Toolbar.popupmenuid);
                        var menuinner = MEE.Toolbar.appendElement(menupopup, "div", "tb_popupmenu_inner");
                        var menufooter = MEE.Toolbar.appendElement(menupopup, "div", "tb_popupmenu_footer");

                        if (paneitem.popupwidth)
                            menuinner.css('width', paneitem.popupwidth + 'px');

                        // add panes to popup menu
                        for (var p = 0; p < paneitem.sections.length; p++) {
                            var section = paneitem.sections[p];
                            if (!section) continue;

                            MEE.Toolbar.appendElement(menuinner, "div", "tbpm_header", section.heading);
                            MEE.Toolbar.appendElement(menuinner, "div", "clear");

                            // add items to popup menu
                            if (section.items) {
                                for (var q = 0; q < section.items.length; q++) {
                                    var item = section.items[q];
                                    if (!item) continue;

                                    var _class = "tbpm_item";
                                    if (section._class)
                                        _class = section._class;
                                    var item_elem = MEE.Toolbar.appendElement(menuinner, "div", _class);
                                    if (item._class) {
                                        item_elem.addClass(item._class);
                                        item_elem.data('class', item._class);
                                    }

                                    if (section.listwidth)
                                        item_elem.css('width', section.listwidth + 'px');

                                    item_elem.data('latex', item.latex);

                                    if (item.wlatex)
                                        item_elem.data('wlatex', item.wlatex);
                                    if (item.mlatex)
                                        item_elem.data('mlatex', item.mlatex);

                                    item_elem.data('text', item.display);

                                    if (item.command)
                                        item_elem.data('command', item.command);

                                    if (section.history || pane.history)
                                        item_elem.data('history', 1);

                                    item_elem = MEE.Toolbar.appendElement(item_elem, "a");
                                    var div2 = MEE.Toolbar.appendElement(item_elem, "div", "icon");
                                    if (item.image) {
                                        //var img = MEE.Toolbar.appendElement(div2, "img");
                                        //img.attr('src', mee_baseurl + 'images/tbicons/' + item.image);
                                        MEE.Toolbar.AddImage(div2, 'tbicons/' + item.image);
                                    }
                                    MEE.Toolbar.appendElement(item_elem, "div", "label", item.display);
                                }
                            }

                        }
                        MEE.Toolbar.popupmenuid++;
                    }
                }

                if (itemcount) {
                    if (pane.type == "menus") {
                        pane.width = itemcount * 61;
                    } else if (pane.type == "bigicons") {
                        pane.width = itemcount * 60;
                    } else if (pane.type == "icons") {
                        itemcount = Math.ceil(itemcount / 3);
                        pane.width = itemcount * 23;
                    } else if (pane.type == "hmenus") {
                        itemcount = Math.ceil(itemcount / 3);
                        pane.width = itemcount * 103;
                    }
                }

                // check for a width specified
                if (pane.width) {
                    pane_cont.css("width", pane.width);
                    pane_elem.css("width", pane.width);
                    pane_label.css("width", pane.width);
                }

            }
        }

        this.toolbarelem = main;

        if (MEE.Edit.toolbar.activate)
            MEE.Edit.toolbar.activate.activate();
        
        return main;
    },

    //#endregion

    //#region Set up events
    initEvents: function () {

        if (this.eventsinit)
            return;

        this.eventsinit = true;

        this.hideTabs();

        // remove right hand tabblock border
        $('.mt_tabblock').each(function () {
            $(this).children('div:last').css('border', 'none');
        });

        // sort mouse over images
        // home stuff
        this.setMouseImages('.mt_home_popup_item_big');
        this.setMouseImages('.mt_home_popup_item');

        // toolbar stuff
        this.setMouseImages('.tb_menu_horiz');
        this.setMouseImages('.tb_menu');
        //this.setMouseImages('.tb_menu_small');

        this.setMouseImages('.tb_symbol');
        this.setMouseImages('.tb_bigicons');
        this.setMouseImages('.tb_list');

        // popup menu stuff
        this.setMouseImages('.tbpm_item');
        this.setMouseImages('.tbpm_list');
        this.setMouseImages('.tbpm_listbig');
        this.setMouseImages('.tbpm_grid');
        this.setMouseImages('.tbpm_gridbig');
        this.setMouseImages('.tbpm_gridxbig');



        // set up click handlers
        $(document).click(this.callback('docClick'));
        $('.mt_home',this.html_elem).click(this.callback('homeClick'));

        $('.mt_tab',this.html_elem).children('a').click(this.callback('tabClick'));
        
        $('.tb_menu_button',this.html_elem).click(this.callback('menuClick'));
        
        //$('.tb_menu_button').click(this.callback('menuClick'));

        $('.tb_symbol',this.html_elem).click(this.callback('itemClick'));
        $('.tb_bigicons',this.html_elem).click(this.callback('itemClick'));
        $('.tb_list',this.html_elem).click(this.callback('itemClick'));

        $('.tbpm_item',this.html_elem).click(this.callback('itemClick'));
        $('.tbpm_list',this.html_elem).click(this.callback('itemClick'));
        $('.tbpm_listbig',this.html_elem).click(this.callback('itemClick'));
        $('.tbpm_grid',this.html_elem).click(this.callback('itemClick'));
        $('.tbpm_gridbig',this.html_elem).click(this.callback('itemClick'));
        $('.tbpm_gridxbig',this.html_elem).click(this.callback('itemClick'));

        // scale background on home panel
        $('.mt_home_popup').scale9Grid({ top: 4, bottom: 15, left: 4, right: 15 });

        // Add images from combined.png instead of indivual images that used to be specified in stylesheet
        MEE.Toolbar.ApplyImage('.mt_home a', 'toolbar/orb.png');
        //MEE.Toolbar.ApplyImage('.mt_home a:hover', 'toolbar/orb_hover.png');
        $('.mt_home a').mouseenter(function () { MEE.Toolbar.ApplyImage($(this), 'toolbar/orb_hover.png'); });
        $('.mt_home a').mouseleave(function () { MEE.Toolbar.ApplyImage($(this), 'toolbar/orb.png'); });
        //MEE.Toolbar.ApplyImage('.mt_home_popup_spacer', 'toolbar/home_spacer.png');
        //MEE.Toolbar.ApplyImage('.mt_tabblock', 'toolbar/background.png', 1, 'repeat-x');


        this.hidePopups();
        $('.mt_tabblock').hide();
    },
    
    hide: function () {
      if(this.toolbaralwayson == false) {
        this.toolbarelem.hide();
      }
    },
    
    hideTabs: function () {
        this.visopt = {};
        var tabonly = false;
        if (this.currentEdit) {
            var baseelem = this.currentEdit.inputelement;
            var classList = $(baseelem).attr('class').split(/\s+/);
            for (var i = 0; i < classList.length; i++) {
                var class_name = classList[i];
                if (class_name.indexOf(':') > -1) {
                    var type = class_name.substr(0, class_name.indexOf(':'));
                    var tabs = class_name.substr(class_name.indexOf(':') + 1);
                    tabs = tabs.split(/,/);
                    var show = "";
                    if (type == "tabshow")
                        show = "show";
                    if (type == "tabhide")
                        show = "hide";
                    if (type == "tabonly") {
                        tabonly = true;
                        show = "show";
                    }

                    for (var k = 0; k < tabs.length; k++) {
                        var tab = tabs[k];
                        this.visopt[tab] = show;
                    }
                }
            }
        }
        var visopt = this.visopt;

        $('.mt_tab').each(function () {
            var visibility = $(this).data('visibility');
            var id = $(this).data('id');
            id = id.replace("mee_tab_", '');

            if (tabonly)
                visibility = "hide";

            if (id in visopt)
                visibility = visopt[id];

            visopt[id] = visibility;

            if (visibility == "hide")
                $(this).css('display', 'none');
        });
    },

    enableTab: function (tabname) {
        if (!this.visopt)
            return;

        if (this.visopt[tabname] == "hide")
            return;

        $('#mee_tab_link_' + tabname).css('display', 'block');
    },

    disableTab: function (tabname) {
        if (!this.visopt)
            return;

        if (this.visopt[tabname] == "hide")
            return;

        $('#mee_tab_link_' + tabname).css('display', 'none');

        if (MEE.Toolbar.activetab) {
            var tabid = $(MEE.Toolbar.activetab).attr('href');
            if (tabid == '#mee_tab_' + tabname)
                this.closeTabs();
        }
    },
    //#endregion

    ///////////////////////
    //#region Global events
    /////////////////////////
    docClick: function (e) {
        this.hidePopups();

        return true;
    },
    //#endregion

    ///////////////////////
    //#region Tab events
    /////////////////////////

    // tab clicked event
    tabClick: function (e) {
        this.hidePopups();

        if (MEE.Toolbar.activetab == e) {
            this.closeTabs()
        } else {
            if (MEE.Toolbar.activetab != null) {
                this.closeTabs();
            }
            this.openTab(e);
        }

        if (MEE.Toolbar.activetab == null) {
            $(e).parent().parent().parent().parent().parent().css('height', '25px');
        } else {
            $(e).parent().parent().parent().parent().parent().css('height', '116px');
        }

        if (MEE.Edit.toolbar.currentEdit)
            MEE.Edit.toolbar.currentEdit.changed();
        return false;
    },

    // close any open tabs
    closeTabs: function () {

        if (MEE.Toolbar.activetab) {
            // if we have an active tab then close it
            var tabid = $(MEE.Toolbar.activetab).attr('href');
            $(tabid).hide();
            $(MEE.Toolbar.activetab).parent().removeClass('mt_tab_active');
            MEE.Toolbar.activetab = null;
            this.hideMenus();
        }
    },

    // open a new tab
    openTab: function (e) {
        var tabid = $(e).attr('href');
        $(tabid).show();
        $(e).parent().addClass('mt_tab_active');
        MEE.Toolbar.activetab = e;
    },
    //#endregion

    /////////////////////////////
    //#region menu events
    /////////////////////////////

    // menu dropdown clicked
    menuClick: function (element) {
        var menuid = $(element).attr('popupmenu');
        this.hideHome();
        if (menuid == MEE.Toolbar.curpopupmenu)
            return this.hideMenus();

        if (MEE.Toolbar.curpopupmenu)
            this.hideMenus();

        this.showMenu(menuid);
        return false;
    },

    // show a menu
    showMenu: function (menuid) {
        var menu = $('#popupmenu' + menuid);
        if (menu) {
            menu.css('display', 'block');

            // if we havent already sorted the menus background then do it
            if (!$(menu).attr('scaled')) {
                $(menu).children('.tb_popupmenu_footer').scale9Grid({ top: 0, bottom: 0, left: 2, right: 11 });
                $(menu).scale9Grid({ top: 4, bottom: 15, left: 4, right: 15 });
                $(menu).attr('scaled', 1);
            }

            // get button
            var menubutton = $('#popupmenubutton' + menuid);

            // position the menu relative to its button
            /*$(menu).css('left', menubutton.position().left);
            var top = menubutton.position().top + menubutton.outerHeight() - 1;
            if (top > 68) top = 68;
            $(menu).css('top', top);*/

            $(menu).css('left', menubutton.offset().left + 'px');
            $(menu).css('top', menubutton.offset().top + menubutton.innerHeight() + 'px');

            $(menubutton).trigger('mousedown');
            $(menubutton).data('showingmenu', 1);

            if (this.currentEdit)
                this.currentEdit.focusInput();
        }
        MEE.Toolbar.curpopupmenu = menuid;
    },

    // hide any open menus
    hideMenus: function () {
        if (MEE.Toolbar.curpopupmenu) {
            var menu = $('#popupmenu' + MEE.Toolbar.curpopupmenu);
            $(menu).css('display', 'none');
            var menubutton = $('#popupmenubutton' + MEE.Toolbar.curpopupmenu);

            // Change this to a function that works on the item
            $(menubutton).data('showingmenu', 0);
            $(menubutton).trigger('mouseout');

            MEE.Toolbar.curpopupmenu = null;
        }
    },
    //#endregion

    ///////////////////////////
    //#region Home events
    ///////////////////////////
    homeClick: function (element) {
        if (this.home_showing) {
            this.hideHome();
        } else {
            this.showHome(element);
        }

        return false;
    },

    showHome: function (element) {
        this.home_showing = 1;
        var pos = $(element).position();
        $('.mt_home_popup').css('left', pos.left + 4 + 'px');
        $('.mt_home_popup').css('top', pos.top + 21 + 'px');
        $('.mt_home_popup').css('display', 'block');
    },

    hideHome: function () {
        /*if (this.home_showing == 2) {
        this.home_showing = 1;
        return;
        }*/
        //if (!this.home_showing)
        //    return;
        this.home_showing = 0;
        $('.mt_home_popup').hide();
    },
    //#endregion

    ///////////////////////
    //#region item events
    /////////////////////
    itemClick: function (item) {
        if (this.currentEdit == null)
            return true;

        this.hidePopups();

        var command = $(item).data('command');
        if (command) {
            if (command.indexOf("|") != -1) {
                var parts = command.split("|");
                var command = parts.shift();
                if (parts.length == 1) {
                    this.currentEdit[command](parts[0]);
                } else if (parts.length == 2) {
                    this.currentEdit[command](parts[0], parts[1]);
                } else if (parts.length == 3) {
                    this.currentEdit[command](parts[0], parts[1], parts[2]);
                } else if (parts.length == 4) {
                    this.currentEdit[command](parts[0], parts[1], parts[2], parts[3]);
                }
            } else {
                this.currentEdit[command]();
            }
        }
        var latex = $(item).data('latex');
        var wlatex = $(item).data('wlatex');
        var mlatex = $(item).data('mlatex');
        if (latex) {
            this.currentEdit.toolbarCommand(latex, item, wlatex, mlatex);
        }

        return false;
    },
    //#endregion

    SetHighlighted: function (id) {
        $('#' + id).trigger('mousedown');
        $('#' + id).data('showingmenu', 1);
    },

    SetNormal: function (id) {
        $('#' + id).data('showingmenu', 0);
        $('#' + id).trigger('mouseout');
    },

    hidePopups: function () {
        this.hideHome();
        this.hideMenus();
    },

    ///////////////////////
    //#region random stuff
    ///////////////////////
    setMouseImages: function (selector) {
        $(selector, this.html_elem).each(function () {
            var width = $(this).outerWidth();
            var height = $(this).outerHeight();

            var size = width + 'x' + height;

            if (size in MEE.Toolbar.images) {
                if (MEE.Toolbar.images[size] == -1)
                    return;

                $(this).mouseover(function () {
                    if ($(this).data('showingmenu') == 1)
                        return;
                    MEE.Toolbar.ApplyImage(this, 'toolbar/sizes/' + size + '-over.png');
                });
                $(this).mouseout(function () {
                    if ($(this).data('showingmenu') == 1)
                        return;
                    $(this).css('background-image', '');
                });
                $(this).mousedown(function () {
                    if ($(this).data('showingmenu') == 1)
                        return;
                    MEE.Toolbar.ApplyImage(this, 'toolbar/sizes/' + size + '-click.png');
                });
                $(this).mouseup(function () {
                    if ($(this).data('showingmenu') == 1)
                        return;
                    MEE.Toolbar.ApplyImage(this, 'toolbar/sizes/' + size + '-over.png');
                });
            } else {
                icon = 'tall';

                MEE.Toolbar.images[size] = -1;

                // dont scale with ie
                if ($.browser.msie)
                    return;

                var overlaydiv = $('<div>');
                //overlaydiv.css('border', '1px solid orange');
                overlaydiv.css('position', 'absolute');
                overlaydiv.css('z-index', '2');
                overlaydiv.css('left', '0px');
                overlaydiv.css('top', '0px');
                overlaydiv.css('width', width + 'px');
                overlaydiv.css('height', height + 'px');
                overlaydiv.data('elem', this);
                overlaydiv.html('&nbsp;');


                $(this).css('position', 'relative');
                $(overlaydiv).mouseover(function () {
                    var elem = $(this).data('elem');
                    if ($(elem).data('showingmenu') == 1)
                        return;
                    $(elem).remove9Grid();
                    $(elem).css('background-image', 'url(' + mee_baseurl + 'images/toolbar/base/' + icon + '-over.png)');
                    $(elem).scale9Grid({ top: 4, bottom: 4, left: 4, right: 4 });
                });
                $(overlaydiv).mouseout(function () {
                    var elem = $(this).data('elem');
                    if ($(elem).data('showingmenu') == 1)
                        return;
                    $(elem).remove9Grid();
                });
                $(overlaydiv).mousedown(function () {
                    var elem = $(this).data('elem');
                    if ($(elem).data('showingmenu') == 1)
                        return;
                    $(elem).remove9Grid();
                    $(elem).css('background-image', 'url(' + mee_baseurl + 'images/toolbar/base/' + icon + '-click.png)');
                    $(elem).scale9Grid({ top: 4, bottom: 4, left: 4, right: 4 });
                });
                $(overlaydiv).mouseup(function () {
                    var elem = $(this).data('elem');
                    if ($(elem).data('showingmenu') == 1)
                        return;
                    $(elem).remove9Grid();
                    $(elem).css('background-image', 'url(' + mee_baseurl + 'images/toolbar/base/' + icon + '-over.png)');
                    $(elem).scale9Grid({ top: 4, bottom: 4, left: 4, right: 4 });
                });
                $(overlaydiv).click(function () {
                    //var elem = $(this).data('elem');
                    //$(elem).trigger('click');
                });
                $(this).prepend(overlaydiv);
            }
        });
    }
    //#endregion
});
