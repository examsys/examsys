$.Class.extend("MEE.Parser",
{
    isTokenAlphaValidChars: "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ",
    isAlphaValidChars: "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ<=>~-#",
    isNumericValidChars: "0123456789",
    isSingleLetterCommandValidChars: ":,;.>\\/!*{}|",
 
    init: function () {
        this.customcommands = {};
        this.apply_to_parent = {};
        this.apply_to_thisset = {};
    },

    //
    // parse latex
    //
    parse: function (latex, parent) {
        this.par = parent;
        this.allowspaces = 0;
        if (this.par && this.par.eldata && this.par.eldata.allowspaces)
            this.allowspaces = 1;

        latex = this.removeNewLines(latex);
        latex = MEE.Tools.HTML.html_entity_decode(latex);

        var tokens = this.tokenize(latex);

        // need to take the list of tokens, and associate the sub script, super script, and arguments to each element so have a list of elements at the end
        var elements = this.buildelements(tokens);

        return elements;
    },

    //
    // split latex into tokens anything in {} or latex commands or ^ _ type stuff into a single chunk. Also anything in a /begin /end set as a 2 chunks, a begin elem and a set of data passed to it
    //
    tokenize: function (text, sepsize, _allowspaces) {
        var output = new Array();
        var type = "";
        var sizemod = 0;
        var sizemodscope = 0;
        var aftersize = true;

        var allowspaces = this.allowspaces;
        if (_allowspaces)
            allowspaces = true;
        var l = text.length;
        for (var i = 0; i < l; i++) {
            aftersize = true;
            if (text.charAt(i) == " ") {
                if (allowspaces) {
                    var cmd = {
                      latex : "\;",
                      type :"command",
                      valid : true
                    }
                    output.push(cmd);
                } else {
                    continue;
                }
            } else if (text.charAt(i) == "\\") {
                // command found, deal with it

                if (text.charAt(i + 1) == "\\") // we have a \\ character, so handle it as a line break
                {
                    var cmd = {
                      latex : "",
                      type : "newline",
                      valid : true
                    }
                    output.push(cmd);
                    i++;
                    type = "";

                } else { // normal command found

                    var isvalidcommand = false;
                    var validobj = {
                      valid : false
                    }
                    var end = this.getEndOfCommand(text, i + 1, validobj);
                    isvalidcommand = validobj.valid;
                    var inbrackets = text.substring(i + 1, end);


                    if (inbrackets == "begin") { // have we found a being command?
                        // find corresponding end, and create 2 cmd object, one for the being command and one for its contents
                        end = this.getEndMatchedBracketPosition(text, i + 6, "{", "}");
                        if (end > 0)
                            var command = text.substring(i + 7, end);

                        // add command
                        var cmd = {
                          latex : command,
                          type  : "begin",
                          valid :  true
                        }
                        if (end == 0)
                            cmd.valid = false;
                        output.push(cmd);

                        if (end > 0) {
                            i = end + 1;

                            // NOTE: Need to just look for end, and store the command that is associated with it, as this determines the closing bracket style
                            // find contents of the \begin \end set and add as a sub command
                            var fintext = "\\end{" + command + "}";
                            end = this.getEndPosition(text, i, command);
                            inbrackets = text.substring(i, end);
                            var cmd = {
                              latex : inbrackets,
                              type : "arg",
                              valid : true
                            }
                            output.push(cmd);

                            i = end + fintext.length - 1;
                        } else {
                            i = text.length;
                        }
                        type = "";

                    } else if (inbrackets == "end") {
                        end = this.getEndMatchedBracketPosition(text, i + 4, "{", "}");
                        if (end > 0)
                            var command = text.substring(i + 5, end);

                        // add command
                        var cmd = {
                          latex : command,
                          type : "end",
                          valid : true
                        }
                        if (end == 0)
                            cmd.valid = false;
                        output.push(cmd);
                        if (end > 0) {
                            i += 6 + command.length;
                        } else {
                            i = text.length;
                        }
                    } else if (this.isSizeCmd("\\" + inbrackets)) {
                        // is a size modifier command, if so store the size change for the next element that is extensible
                        sizemod = this.getSize("\\" + inbrackets);

                        if (sepsize) {
                            aftersize = false;
                            var cmd = {
                              latex : sizemod,
                              type : 'size',
                              valid : false
                            }
                            output.push(cmd);
                        } else {
                            sizemodscope = 2;
                        }

                        i = end - 1;

                    } else if (this.isPairedCmd("\\" + inbrackets)) {
                        // if the command is a pairable command, then try to find its pair and create
                        // NOTE: THIS DOESNT TAKE INTO ACCOUNT ANY SIZE MODIFIER THAT IS WITH THE CLOSING ENTRY
                        // elements based on this, and use any size modifer found
                        /*var pair = this.getPairCmd("\\"+inbrackets);
                        var pairend = this.findPairEnd(text, i + inbrackets.length + 1, pair);*/

                        var res = this.getEndBracketPosition(text, i);
                        var end = res.offset;
                        var pair = this.getPairCmd("\\" + inbrackets);

                        if (end == 0) {

                            // no closing pair found, create as a single element with size modifier
                            var cmd = {
                              latex: inbrackets,
                              type: "extsingle",
                              valid: isvalidcommand,
                              size: sizemod
                            }
                            output.push(cmd);

                            i = i + inbrackets.length;

                        } else {
                            // found an end eleemnt, so create as a paired element with contents as a parameter

                            // NOTE: Should be saving the end sizing as it can be used later on to size the right hand bracket differently to the left one. If no sizing operator for the end bracket is supplied, text sized should be used
                            var content = text.substring(i + inbrackets.length + 1, end);
                            var trimres = this.TrimAnyRSize(content);
                            content = trimres.text;

                            var cmd = {
                              latex: pair.pair,
                              type: "extpair",
                              size: sizemod,
                              sizer: trimres.size
                            }
                            if (res.match) cmd.closing = this.getClosingBracketText(res.match);

                            output.push(cmd);

                            var cmd = {
                              latex : content,
                              type : "arg"
                            }
                            output.push(cmd);

                            type = "";
                            i = end + res.match.length - 1;

                        }

                    } else if (this.isNonPairedCmd("\\" + inbrackets)) {
                        // if non pairable command, just apply the size modifier if available
                        var cmd = {
                          latex : inbrackets,
                          type : "extsingle",
                          size : sizemod,
                          valid : isvalidcommand
                        }
                        if (inbrackets == ")" || inbrackets == "}" || inbrackets == "]" || inbrackets.substr(0, 1) == "r")
                            cmd.isclosing = true;
                        output.push(cmd);

                        i = end - 1;

                    } else if (inbrackets == "") {
                        var cmd = {
                          latex : inbrackets,
                          type : "command",
                          size : sizemod,
                          valid : false
                        }
                        output.push(cmd);
                    } else {
                        // non special case command, just create an object for it
                        var cmd = {};


                        // type isnt blank, so we are in subscript or superscript, take the command and add it as the subscript or superscript latex
                        // this allows things like x_\pi to work properly
                        if (type != "") {
                            cmd.latex = "\\" + inbrackets;
                            cmd.type = type;
                            cmd.valid = isvalidcommand;
                            output.push(cmd);
                        } else {
                            // nothing special at all, just create a extra command on the output stack
                            cmd.latex = inbrackets;
                            cmd.valid = isvalidcommand;
                            cmd.type = "command";


                            var eldata = this.getElementData(cmd);
                            if (eldata.all_as_arg01) {
                                var before = text.substr(0, i);
                                var after = text.substr(i + inbrackets.length + 1);
                                output = new Array();
                                var isvalid = true;
                                if (after == "") {
                                    cmd.valid = false;
                                    isvalid = false;
                                }
                                output.push(cmd);

                                // prefix
                                cmd = {
                                  latex : before,
                                  type : "arg",
                                  valid : isvalid
                                }
                                output.push(cmd);

                                // postfix
                                cmd = {
                                  latex : after,
                                  type : "arg",
                                  valid : isvalid
                                }
                                output.push(cmd);

                                return output;
                            } else if (eldata.rest_as_arg0) {
                                output.push(cmd);

                                // postfix
                                cmd = {
                                  latex : text.substr(i + inbrackets.length + 1),
                                  type : "arg",
                                  valid : true
                                }
                                output.push(cmd);

                                return output;
                            } else {
                                output.push(cmd);
                            }
                        }
                        type = "";
                        i = end - 1;
                    }
                }

            } else if (text.charAt(i) == "{") {
                // are we entering a { bracket piece, if so find the end and use it as a single entity
                var end = this.getEndMatchedBracketPosition(text, i, "{", "}");
                if (end > 0) {
                    var inbrackets = text.substring(i + 1, end);
                    if (type == "")
                        type = "arg";
                    var cmd = {
                      latex : inbrackets,
                      type : type,
                      valid : true
                    }
                    output.push(cmd);
                    type = "";
                    i = end;
                } else {
                    var inbrackets = text.substring(i + 1);
                    if (type == "")
                        type = "arg";
                    var cmd = {
                      latex : inbrackets,
                      type : type,
                      incompletearg : 1,
                      valid : true
                    }
                    output.push(cmd);
                    type = "";
                    i = text.length;
                }

            } else if (text.charAt(i) == "(" || (text.charAt(i) == '.' && sizemod != 0)) {
                // are we entering a ( bracket piece, if so find the end and use it as a single entity
                // VALIDATE THE RESULT OF THE PAIRING IS OK, IF NOT HAVE SOME SORT OF ERROR
                var res = this.getEndBracketPosition(text, i);
                var end = res.offset;

                if (end > 0) {
                    var inbrackets = text.substring(i + 1, end);
                    var trimres = this.TrimAnyRSize(inbrackets); ;
                    inbrackets = trimres.text;

                    var cmd = {};
                    if (text.charAt(i) == '.') {
                        cmd.latex = "pdotbrackets";
                    } else {
                        cmd.latex = "pbrackets";
                    }
                    cmd.type = "extpair";
                    cmd.size = sizemod;
                    cmd.sizer = trimres.size;

                    if (res.match) cmd.closing = this.getClosingBracketText(res.match);
                    output.push(cmd);

                    var cmd = {
                      latex : inbrackets,
                      type : "arg"
                    }
                    output.push(cmd);

                    type = "";
                    i = end + res.match.length - 1;
                    //sizemod = 0;
                } else {
                    // unable to find ending ), add as single element
                    var cmd = {
                      latex : "(",
                      type : "extsingle",
                      size : sizemod,
                      valid : true
                    }
                    output.push(cmd);
                    //sizemod = 0;                        
                }

            } else if (text.charAt(i) == "[") {
                // have we found a [ set? add as a single entity if paired ] is found
                // this is a special case of the bracketed sets earlier, but needs to 
                // be still separate due to [] sometimes being used for arguments to commands
                // NOTE: MAKE SURE IF NO PAIRED ] IS FOUND, THEN OUTPUT AS A SINGLE [

                var end = this.getEndMatchedBracketPosition(text, i, "[", "]");
                if (end > 0) {
                    var inbrackets = text.substring(i + 1, end);
                    var trimres = this.TrimAnyRSize(inbrackets);
                    inbrackets = trimres.text;

                    var cmd = {
                      latex : "psqbrackets",
                      type : "extpair", // sometimes this will be taken as an argument for a command
                      size : sizemod,
                      sizer : trimres.size
                    }
                    output.push(cmd);

                    var cmd = {
                      latex : inbrackets,
                      type : "arg"
                    }
                    output.push(cmd);

                    type = "";
                    i = end;
                    //sizemod = 0;
                } else {
                    var res = this.getEndBracketPosition(text, i);
                    var end = res.offset;

                    if (end > 0) {
                        var inbrackets = text.substring(i + 1, end);
                        var trimres = this.TrimAnyRSize(inbrackets); ;
                        inbrackets = trimres.text;

                        var cmd = {
                          latex : "psqbrackets",
                          type : "extpair",
                          size : sizemod,
                          sizer : trimres.size
                        }

                        if (res.match) cmd.closing = this.getClosingBracketText(res.match);
                        output.push(cmd);

                        var cmd = {
                          latex : inbrackets,
                          type : "arg"
                        }
                        output.push(cmd);

                        type = "";
                        i = end + res.match.length - 1;
                        //sizemod = 0;
                    } else {
                        // No ] found, add as single extendable element
                        var cmd = {
                          latex : "[",
                          type : "extsingle",
                          size : sizemod,
                          incompletearg : 1,
                          valid : true
                        };
                        output.push(cmd);
                        //sizemod = 0;      
                    }
                }

            } else if (text.charAt(i) == "}" || text.charAt(i) == ")" || text.charAt(i) == "]") {
                // CHECK: } was commented out, WHY!?
                // have we found a stray ending bracket, } ] ), add it as a single element
                var cmd = {
                  latex : text.charAt(i),
                  type : "extsingle",
                  size : sizemod,
                  valid : true,
                  isclosing : true
                }
                output.push(cmd);

            } else if (text.charAt(i) == "_") {
                // have we found a subscript character? Note this so it can be used for the 
                // next element or {} elements found
                type = "subscript";

            } else if (text.charAt(i) == "}") {
                // found a stray } so show an error
                //alert("Missing {");

            } else if (text.charAt(i) == "^") {
                // have we found a superscript character? Note this so it can be used for the 
                // next element or {} elements found
                type = "superscript";

            } else if (text.charAt(i) == "/") {
                // bracket char

                var cmd = {
                  latex: text.charAt(i),
                  type : "extsingle",
                  size : sizemod,
                  valid : true
                };
                output.push(cmd);

            } else {
                // just have a normal single character to deal with

                if (text.charAt(i) == "&") {
                    // Handler the tab when aligning stuff
                    var cmd = {
                      latex : "",
                      type : "tab",
                      valid : true
                    }
                    output.push(cmd);
                    type = "";
                } else {
                    var ch = text.charAt(i);

                    // we just have a single character to handle
                    var cmd = {
                      latex : ch,
                      type : type,
                      valid : true
                    };
                    output.push(cmd);
                    type = "";
                }
            }

            // reset size modifier
            sizemodscope--;
            if (sizemodscope == 0)
                sizemod = 0;
        }

        if (type != "") {
            var cmd = {
              latex : '',
              type : type,
              valid : true
            };
            output.push(cmd);
        }

        // set a size with something after it as valid
        if (output.length > 0) {
            for (var i = 0; i < output.length; i++) {
                var lastcmd = output[i];
                if (lastcmd.type == 'size' && aftersize)
                    lastcmd.valid = true;
            }
        }

        return output;
        //alert(output);
    },

    buildelements: function (tokens) {
        var i;
        var elems = new Array();
        this.removeprev = false;
        for (i = 0; i < tokens.length; i++) {
            var token = tokens[i];
            var eldata = this.getElementData(token);

            if (token.type == "" || token.type == "command" || token.type == "extpair") {
                // token.type == "" : do we just have a normal letter or number
                // token.type == "command": we have a command, look it up, check how many arguments allowed and add em to the
                // token.type == "extpair": pair of brackets, create a element for the brackets, and add the contents as an argument

                if (token.type == "command" && token.latex == "end") {
                    //alert("BAR");
                    i++;
                    continue;
                }

                // is the element a parent modifier, then store its eldata to be applied to the parent element later on
                if (eldata.apply_to_parent) {
                    this.apply_to_parent = jQuery.extend(this.apply_to_parent, eldata.apply_to_parent);
                    continue;
                } else if (eldata.apply_to_thisset) {
                    this.apply_to_thisset = jQuery.extend(this.apply_to_thisset, eldata.apply_to_thisset);
                    continue;
                }

                // define custom command
                if (token.latex == "newcommand") {
                    var nametoken = tokens[++i];
                    i++;
                    var counttoken = tokens[++i];
                    var valuetoken = tokens[++i];
                    if (!nametoken || !counttoken || !valuetoken)
                        continue;

                    var cc = {
                      count : counttoken.latex,
                      value : valuetoken.latex
                    };
                    this.customcommands[nametoken.latex] = cc;

                    continue;


                } else if ('\\' + token.latex in this.customcommands) {
                    // process custom command
                    var cc = this.customcommands['\\' + token.latex];
                    var text = cc.value;

                    for (var arg = 0; arg < cc.count; arg++) {
                        var toreplace = '#' + (arg + 1);
                        var argtoken = tokens[++i];
                        var replacewith = argtoken.latex;
                        text = text.replace(new RegExp(toreplace, 'g'), replacewith);
                    }

                    var newtoken = {
                      type : "arg",
                      value : true,
                      latex : text
                    };
                    elem = new MEE.Elem(newtoken, this.getElementData('base'));
                    elem.SetMain(newtoken);
                    elems.push(elem);
                    continue;
                }

                var elem = null;
                if (eldata.object) {
                    var name = "Elem" + eldata.object;
                    elem = new MEE[name](token, eldata);
                } else {
                    elem = new MEE.Elem(token, eldata);
                }

                // this is probably in the wrong place, but its easiest to write here. Used to parse the $$ stuff in \text 
                if (eldata.parseinner) {
                    var token2 = tokens[i + 1];
                    if (token2 && token2.type == "arg") {
                        if (token2.latex.indexOf('$') > -1) {
                            var parts = token2.latex.split('$');

                            // first part should be the current element
                            var newtoken = {
                              type : 'arg',
                              valid : true,
                              latex : parts[0]
                             };
                            elem.SetMain(newtoken);


                            for (var k = 1; k < parts.length; k++) {
                                elems.push(elem);

                                if (k % 2 == 1) { // math part
                                    newtoken = {
                                      valid : true,
                                      latex : parts[k],
                                      type : 'arg'
                                    };

                                    elem = new MEE.Elem(newtoken, this.getElementData('base'));

                                    elem.SetMain(newtoken);
                                    //elems.push(elem);

                                } else { // text part
                                    newtoken = {
                                      valid : true,
                                      latex : token.latex,
                                      type : token.type
                                    };
                                    elem = new MEE.Elem(newtoken, eldata);

                                    newtoken = {
                                      valid : true,
                                      latex : parts[k],
                                      type : 'arg'
                                    };
                                    var argelem = new MEE.Elem(newtoken, this.getElementData('base'));
                                    elem.SetMain(argelem);

                                }
                            }
                            i++;
                        }
                    }
                }

                i = this.addToElement(tokens, i, elem);

                if (this.removeprev) {
                    elems.pop();
                }

                elems.push(elem);

            } else if (token.type == "superscript" || token.type == "subscript") {
                // add a blank space element when we have a script that has no preceding
                // element available
                if (elems.length == 0) {
                    var blanktoken = {
                      latex : MEE.Data.blankspace,
                      type : ''
                    }
                    var eldata = jQuery.extend({}, this.getElementData('base'));
                    eldata.blank = 1;

                    var elem = new MEE.Elem(blanktoken, eldata);
                    elems.push(elem);
                } else {
                    var elem = elems[elems.length - 1];
                }
                elem.SetScript(token);


            } else if (token.type == "arg") {
                // stray argument or { } text, need to parse it
                var elem = new MEE.Elem(token, this.getElementData('base'));

                elem.SetMain(token);
                elems.push(elem);

            } else if (token.type == "begin") {
                // begin set, add the contents as latex, and the elemarray class should parse into a table
                var elem = new MEE.Elem(token, eldata);

                // check the content of tokens[i+1] for a [] part at the start, this will
                // be used for column alignment information
                var eldata = this.getElementData(token);
                var alignment = "";
                if (tokens.length > i + 1) {
                    if (eldata.custalign) {
                        var text = $.trim(tokens[i + 1].latex);
                        if (text.charAt(0) == "[") {
                            var end = text.indexOf(']');
                            if (end != -1) {
                                alignment = text.substr(1, end - 1);
                                tokens[i + 1].latex = text.substr(end + 1);
                            }
                        } else if (text.charAt(0) == "{") {
                            var end = text.indexOf('}');
                            if (end != -1) {
                                alignment = text.substr(1, end - 1);
                                tokens[i + 1].latex = text.substr(end + 1);
                            }
                        } else {
                            if (token.latex.substr(token.latex.length - 1) == "*") {
                                alignment = text.substr(0, 1)
                                tokens[i + 1].latex = text.substr(1);
                            }
                        }
                    }
                    elem.AddArray(tokens[i + 1], alignment);
                    i++;
                }
                elems.push(elem);

            } else if (token.type == "extsingle") {
                // single bracket, just add as a single element
                elems.push(new MEE.Elem(token, eldata));
            }
        }

        return elems;
    },

    addToElement: function (tokens, i, elem) {
        var elemdata = this.getElementData(tokens[i]);
        elemdata = jQuery.extend(true, {}, elemdata);
        var argno = 0;
        i = i + 1;

        /*if (elemdata.pn_as_upperlower && i > 1 && i < tokens.length)
        {
        elem.AddUpperLower(tokens[i-2],tokens[i]);
        this.removeprev = true;
        return i;
        } else*/
        if (elemdata.next_as_arg0 && i < tokens.length) {
            elem.AddArg(tokens[i]);
            return i;
        }

        for (; i < tokens.length; i++) {
            var token = tokens[i];
            if (token.type == "superscript") {
                elem.SetScript(token);
            } else if (token.type == "subscript") {
                elem.SetScript(token);
            } else if (token.type == "command") {
                var eldatac = this.getElementData(token);
                if (eldatac.apply_to_previous) {
                    elem.eldata = $.extend(elem.eldata, eldatac.apply_to_previous);
                } else {
                    break;
                }
            } else if (token.type == "arg") {
                if (elemdata.arg01_as_upperlower && argno == 0) {
                    // should first 2 arguments be used as upper and lower
                    elem.AddUpperLower(token, tokens[i + 1]);
                    elemdata.args--;
                    i++;
                } else if (elemdata.arg0_as_array && argno == 0) {
                    // should first 2 arguments be used as upper and lower
                    elem.AddArray(token, tokens[i + 1]);
                    elemdata.args -= 2;
                    i++;
                } else if (elemdata.arg0_as_upper && argno == 0) {
                    // should the 1st argument be the main text of the element
                    elem.AddUpperLower(token, null);
                    elemdata.args--;
                } else if (elemdata.arg0_as_main && argno == 0) {
                    // should the 1st argument be the main text of the element
                    elem.SetMain(token);
                    elemdata.args--;
                } else if (elemdata.arg0_as_super && argno == 0) {
                    // should the 1st argument be the main text of the element
                    elem.SetScript(token, "superscript");
                    elemdata.args--;
                } else if (elemdata.arg0_as_sub && argno == 0) {
                    // should the 1st argument be the main text of the element
                    elem.SetScript(token, "subscript");
                    elemdata.args--;
                } else if (elemdata.arg1_as_main && argno == 1) {
                    // should the 1st argument be the main text of the element
                    elem.SetMain(token);
                    elemdata.args--;
                } else if (elemdata.args > 0) {

                    elem.AddArg(token);
                    elemdata.args--;
                } else {

                    break;
                }
                argno++;

            } else if (token.type == "extpair") {

                if (token.latex == "psqbrackets") {

                    if (elemdata.sarg_as_sup) {
                        // should the sarg be set as superscript
                        elem.SetScript(tokens[i + 1], "superscript");
                        i++;
                        elemdata.sarg = 0;
                    } else if (elemdata.sarg_as_lower) {
                        // should the sarg be set as superscript
                        elem.AddUpperLower(null, tokens[i + 1]);
                        i++;
                        elemdata.sarg = 0;
                    } else if (elemdata.sarg > 0) {

                        elem.SetSArg(tokens[i + 1]);
                        i++;
                        elemdata.sarg = 0;
                    } else {
                        break;
                    }

                } else {
                    break;
                }
            } else {
                break;
            }
        }
        return i - 1;
    },

    getElementData: function (token) {
        // special case types
        if (token.type == "") {
            // case for single letters or numbers
            if (this.isTokenAlpha(token.latex)) {
                var eldata = MEE.Data.commands.variable;
                eldata._name = 'variable';
                return eldata;
            } else if (this.isNumeric(token.latex)) {
                var eldata = MEE.Data.commands.digit;
                eldata._name = 'digit';
                return eldata;
            }
        }

        var latex = token.latex;
        if (token.type != "")
            latex = "\\" + latex;
        var el = MEE.Data.commands[latex];

        if (el) {
            el._name = latex;
            return el;
        }
        
        if (token.type == "command") {
            var eldata = MEE.Data.commands['invalidcommand'];
            eldata._name = 'invalidcommand';
            return eldata;
        }

        el = {
          args : 0,
          sarg : 0,
          _name : latex
        }

        return el;
    },

    // is the character a valid alpha numeric char
    isAlpha: function (sText) {
        //var isAlphaValidChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ<=>~-#";
        var IsNumber = true;
        //var Ch;
        for (i = sText.length; i--; ) {
            if (this.isAlphaValidChars.indexOf(sText.charAt(i)) == -1) {
                IsNumber = false;
                break;
            }
        }
        return IsNumber;

    },

    // is the character a valid alpha numeric char
    isTokenAlpha: function (sText) {
        //var isTokenAlphaValidChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        var IsNumber = true;
        var Ch;

        for (i = sText.length; i--;) {
            if (this.isTokenAlphaValidChars.indexOf(sText.charAt(i)) == -1) {
                IsNumber = false;
                break;
            }
        }
        return IsNumber;

    },

    // is the character a valid alpha numeric char
    isNumeric: function (sText) {
        //var isNumericValidChars = "0123456789";
        var IsNumber = true;
        //var Ch;
        for (i = sText.length; i--; ) {
            if (this.isNumericValidChars.indexOf(sText.charAt(i)) == -1) {
                IsNumber = false;
                break;
            }
        }
        return IsNumber;

    },

    // is the character a valid alpha numeric char
    isSingleLetterCommand: function (sText) {
        //var isSingleLetterCommandValidChars = ":,;.>\\/!*{}|";

        if (this.isSingleLetterCommandValidChars.indexOf(sText) != -1)
            return true;

        return false;

    },

    // for a corresponding \being{command}, find the position of the \end{command}
    getEndPosition: function (text, initial, command) {
        var tofind = "\\end{" + command + "}";
        var newopen = "\\begin{" + command + "}";
        var depth = 1;
        for (var i = initial; i < text.length; i++) {
            var match1 = text.substr(i, tofind.length);
            var match2 = text.substr(i, newopen.length);

            if (match1 == tofind) {
                depth--;
            } else if (match2 == newopen) {
                depth++;
            }

            if (depth == 0)
                return i;
        }
        return text.length;
    },

    // find the end of the latex command
    getEndOfCommand: function (text, initial, isvalidcommand) {
        for (var i = initial; i < text.length; i++) {
            if (i == initial) {
                if (this.isSingleLetterCommand(text.charAt(i))) {
                    isvalidcommand.valid = true;
                    return i + 1;
                }
            }
            if (!this.isAlpha(text.charAt(i))) {
                isvalidcommand.valid = true;
                return i;
            }
            if (i != initial) {
                if (text.charAt(i) == "\\") {
                    isvalidcommand.valid = true;
                    return i;
                }
            }

        }
        return text.length;
    },

    // find the position of the closing bracket for the argument
    getEndMatchedBracketPosition: function (text, initial, open, close) {
        var bkcount = 0;
        for (var i = initial; i < text.length; i++) {
            if (text.charAt(i) == open && text.charAt(i - 1) != "\\")
                bkcount++;
            if (text.charAt(i) == close && text.charAt(i - 1) != "\\")
                bkcount--;

            if (bkcount == 0)
                return i;
        }
        return 0;
    },

    // find the position of the closing bracket
    getEndBracketPosition: function (text, initial, open, close) {
        var bkcount = 0;

        var result = {
          offset : 0,
          match : ""
        }

        // match any opening and closing brackets
        for (var k = 0; k < MEE.Data.pairs.length; k++) {
            if (MEE.Data.pairs[k].left == MEE.Data.pairs[k].right) {
                MEE.Data.pairs[k].matched = 1;
            } else {
                MEE.Data.pairs[k].matched = 0;
            }
            MEE.Data.pairs[k].count = 0;

        }
        for (var i = initial; i < text.length; i++) {
            for (var k = 0; k < MEE.Data.pairs.length; k++) {
                var llen = MEE.Data.pairs[k].left.length;
                var matchok = true;
                if (MEE.Data.pairs[k].matched && bkcount % 2 == 1)
                    matchok = false;
                if (text.substr(i, llen) == MEE.Data.pairs[k].left && matchok) {
                    bkcount++;
                    if (MEE.Data.pairs[k].matched)
                        MEE.Data.pairs[k].count = 1;
                } else {
                    var llen = MEE.Data.pairs[k].right.length;
                    if (text.substr(i, llen) == MEE.Data.pairs[k].right) {
                        if (MEE.Data.pairs[k].onlysized) {
                            var tocheck = text.substr(initial, i - initial);
                            var dotok = false;
                            for (var m = 0; m < MEE.Data.sizemodifiers.length; m++) {

                                var rightmod = MEE.Data.sizemodifiers[m].right;
                                if (tocheck.substr(tocheck.length - rightmod.length) == rightmod) {
                                    dotok = true;
                                    break;
                                }
                            }
                            if (!dotok)
                                continue;
                        }
                        result.match = MEE.Data.pairs[k].right;
                        bkcount--;
                        MEE.Data.pairs[k].count = 0;
                    }
                }
            }

            if (bkcount == 0) {
                result.offset = i;
                return result;
            }
        }
        result.match = "";
        result.offset = 0;
        return result;
    },

    // takes some text, and removes all new line characters and tab characters
    removeNewLines: function (latex) {
        latex = this.replaceAll(latex, /\n/, "");
        latex = this.replaceAll(latex, /\t/, "");
        return latex;
    },

    // do a replace all on a string
    replaceAll: function (text, search, replace) {
        var base;
        do {
            base = text;
            text = text.replace(search, replace);
        } while (base != text)

        return text;
    },

    // look up a command and find if it is a sizing modifer command
    isSizeCmd: function (text) {
        var i = 0;
        for (i = 0; i < MEE.Data.sizemodifiers.length; i++) {
            var left = MEE.Data.sizemodifiers[i].left;
            var right = MEE.Data.sizemodifiers[i].right;

            if (text == left)
                return true;

            if (text == right)
                return true;
        }

        if (text in MEE.Data.sizemodifiers_single) {
            return true;
        }
        return false;
    },

    // for a given sizing modifer command, return the size specified
    getSize: function (text) {
        var i = 0;
        for (i = 0; i < MEE.Data.sizemodifiers.length; i++) {
            var left = MEE.Data.sizemodifiers[i].left;
            var right = MEE.Data.sizemodifiers[i].right;

            if (text == left)
                return MEE.Data.sizemodifiers[i].size;

            if (text == right)
                return MEE.Data.sizemodifiers[i].size;
        }

        if (text in MEE.Data.sizemodifiers_single) {
            return MEE.Data.sizemodifiers_single[text];
        }

        return 0;
    },

    // if the last part of the text is a right sizing command then remove it
    TrimAnyRSize: function (text) {
        text = $.trim(text);
        var result = {
          text : text,
          size : 0
        }

        for (var i = 0; i < MEE.Data.sizemodifiers.length; i++) {
            var len = MEE.Data.sizemodifiers[i].right.length;
            if (text.substr(text.length - len, len) == MEE.Data.sizemodifiers[i].right) {
                result.text = text.substr(0, text.length - len);
                result.size = this.getSize(text.substr(text.length - len, len));
                return result;
            }
        }
        return result;
    },

    // if this a non pairing command
    isPairedCmd: function (text, leftonly) {
        var i = 0;

        if (leftonly == undefined)
            leftonly = true;

        for (i = 0; i < MEE.Data.pairs.length; i++) {
            var left = MEE.Data.pairs[i].left;
            var right = MEE.Data.pairs[i].right;

            if (text == left)
                return true;

            if (!leftonly && text == right)
                return true;
        }
        return false;
    },

    // return the pair command object
    getPairCmd: function (text) {
        var i = 0;

        for (i = 0; i < MEE.Data.pairs.length; i++) {
            var left = MEE.Data.pairs[i].left;
            var right = MEE.Data.pairs[i].right;

            if (text == left)
                return MEE.Data.pairs[i];

            if (text == right)
                return MEE.Data.pairs[i];
        }
        return null;

    },

    // find the end element of a paired command
    findPairEnd: function (text, initial, pair) {

        var tofind = pair.right;
        var newopen = pair.left;
        var depth = 1;
        for (var i = initial; i < text.length; i++) {
            var match1 = text.substr(i, tofind.length);
            var match2 = text.substr(i, newopen.length);

            if (match1 == tofind) {
                depth--;
            } else if (match2 == newopen) {
                depth++;
            }

            if (depth == 0)
                return i;
        }
        return 0;
    },

    // is this a etensible element that can have a size applied
    isNonPairedCmd: function (text) {
        var i = 0;

        for (i = 0; i < MEE.Data.nonpairs.length; i++) {
            var left = MEE.Data.nonpairs[i];

            if (text == left)
                return true;
        }
        return false;

    },

    getClosingBracketText: function (match) {

        var newtoken = {};
        newtoken.type = "command";
        if (match.length == 1) {
            newtoken.latex = match;
        } else {
            newtoken.latex = match.substr(1);
        }
        var closingdata = this.getElementData(newtoken);
        if (closingdata.text) {
            return closingdata.text;
        } else {
            return match;
        }
    }
});
