
// possible token types:
/*
command
arg
subscript
superscript

begin
newline
tab

extsingle
extpair
 
*/
$.Class.extend("MEE.Parser",
{
// static stuff

},
{
    // list of size modifiers available
    // instead of dealign with them normally append a modifer tag to the 
    // object output from the tokenizer
    sizemodifiers: [
        // size modifier 0 is same size as the text
        // size modifier -1 is auto size to the content of the line
        // size modifiers 1-4 are larger than the text sizing
        {left:'\\left',right:'\\right',size:-1},
        {left:'\\bigl',right:'\\bigr',size:1},
        {left:'\\Bigl',right:'\\Bigr',size:2},
        {left:'\\biggl',right:'\\biggr',size:3},
        {left:'\\Biggl',right:'\\Biggr',size:4}
        ],
    sizemodifiers_single: {
        // size modifier 0 is same size as the text
        // size modifier -1 is auto size to the content of the line
        // size modifiers 1-4 are larger than the text sizing
        '\\big': 1,
        '\\Big': 2,
        '\\bigg': 3,
        '\\Bigg': 4,
        },

    // the following can be affected by the size modifiers, will attempt to find pairs, if not use single only
    pairs: [ 
        {left:'(',right:')',pair:'pbrackets'},
        {left:'[',right:']',pair:'psqbrackets'},
        {left:'\\lbrace',right:'\\rbrace',pair:'pbrace'},
        {left:'\\lvert',right:'\\rvert',pair:'pvert'},
        {left:'\\langle',right:'\\rangle',pair:'pangle'},
        {left:'\\lceil',right:'\\rceil',pair:'pceil'},
        {left:'\\lfloor',right:'\\rfloor',pair:'pfloor'},
        {left:'\\lgroup',right:'\\rgroup',pair:'pgroup'},
        {left:'\\lmoustache',right:'\\rmoustache',pair:'pmoustache'}
        ],
    // the following can be affected by the size modifiers - single item only
    nonpairs: [
        '\\vert',
        '\\Vert',
        '/',
        '\\|',
        '\\backslash',
        '\\arrowvert',
        '\\Arrowvert',
        '\\bracevert',
        '\\uparrow',
        '\\Uparrow',
        '\\downarrow',
        '\\Downarrow',
        '\\updownarrow',
        '\\Updownarrow'
        ],

    replace: {
        '-':   '&#x2212;',
        '*':   '&#x2217;',
	"'":   '&#x02B9;',
      },

    init: function () {
    },

    //
    // parse latex
    //
    parse: function (latex) {

        latex = this.removeNewLines(latex);
        latex = MEE.Tools.HTML.html_entity_decode(latex);

        //alert("Parsing " + latex);
        var tokens = this.tokenize(latex);

        // need to take the list of tokens, and associate the sub script, super script, and arguments to each element so have a list of elements at the end
        var elements = this.buildelements(tokens);

        return elements;
    },

    //
    // split latex into tokens anything in {} or latex commands or ^ _ type stuff into a single chunk. Also anything in a /begin /end set as a 2 chunks, a begin elem and a set of data passed to it
    //
    tokenize: function (text) {
        var output = new Array();
        var type = "";
        var sizemod = 0;
        var sizemodscope = 0;

        for (var i = 0; i < text.length; i++) {
            if (text[i] == " ") {
                continue;
            } else if (text[i] == "\\") {
                // command found, deal with it

                if (text[i+1] == "\\") // we have a \\ character, so handle it as a line break
                {
                    var cmd = new Object();
                    cmd.latex = "";
                    cmd.type = "newline";
                    output.push(cmd);
                    i++;
                    type = "";

                } else { // normal command found
                        
                    var end = this.getEndOfCommand(text, i + 1);
                    var inbrackets = text.substring(i + 1, end);


                    if (inbrackets == "begin") { // have we found a being command?
                        // find corresponding end, and create 2 cmd object, one for the being command and one for its contents
                        end = this.getEndMatchedBracketPosition(text, i + 6, "{", "}");
                        var command = text.substring(i + 7, end);

                        // add command
                        var cmd = new Object();
                        cmd.latex = command;
                        cmd.type = "begin";
                        output.push(cmd);

                        i = end + 1;

                        // NOTE: Need to just look for end, and store the command that is associated with it, as this determines the closing bracket style
                        // find contents of the \begin \end set and add as a sub command
                        var fintext = "\\end{" + command + "}";
                        end = this.getEndPosition(text, i, command);
                        inbrackets = text.substring(i, end);
                        var cmd = new Object();
                        cmd.latex = inbrackets;
                        cmd.type = "arg";
                        output.push(cmd);

                        i = end + fintext.length - 1;
                        type = "";

                    } else if (this.isSizeCmd("\\"+inbrackets)) { 
                        // is a size modifier command, if so store the size change for the next element that is extensible
                        sizemod = this.getSize("\\"+inbrackets);
                        sizemodscope = 2;
                        i = end - 1;

                    } else if (this.isPairedCmd("\\"+inbrackets)) { 
                        // if the command is a pairable command, then try to find its pair and create
                        // NOTE: THIS DOESNT TAKE INTO ACCOUNT ANY SIZE MODIFIER THAT IS WITH THE CLOSING ENTRY
                        // elements based on this, and use any size modifer found
                        /*var pair = this.getPairCmd("\\"+inbrackets);
                        var pairend = this.findPairEnd(text, i + inbrackets.length + 1, pair);*/

                        var res = this.getEndBracketPosition(text, i);
                        var end = res.offset;
                        var pair = this.getPairCmd("\\"+inbrackets);

                        if (end == 0) {

                            // no closing pair found, create as a single element with size modifier
                            var cmd = new Object();
                            cmd.latex = inbrackets;
                            cmd.type = "extsingle";
                            cmd.size = sizemod;
                            output.push(cmd);                        

                            i = i + inbrackets.length;
                        
                        } else {
                            // found an end eleemnt, so create as a paired element with contents as a parameter

                            // NOTE: Should be saving the end sizing as it can be used later on to size the right hand bracket differently to the left one. If no sizing operator for the end bracket is supplied, text sized should be used
                            var content = text.substring(i + inbrackets.length + 1, end);
                            var trimres = this.TrimAnyRSize(content);
                            content = trimres.text;
                    
                            var cmd = new Object();
                            cmd.latex = pair.pair;
                            cmd.type = "extpair";
                            cmd.size = sizemod;
                            cmd.sizer = trimres.size;
                            if (res.match) cmd.closing = this.getClosingBracketText(res.match);
                            
                            output.push(cmd);

                            var cmd = new Object();
                            cmd.latex = content;
                            cmd.type = "arg";
                            output.push(cmd);

                            type = "";
                            i = end + res.match.length;

                        }

                    } else if (this.isNonPairedCmd("\\"+inbrackets)) { 
                        // if non pairable command, just apply the size modifier if available
                        var cmd = new Object();
                        cmd.latex = inbrackets;
                        cmd.type = "extsingle";
                        cmd.size = sizemod;
                        output.push(cmd);
                                                
                        i = end - 1;
                        
                    } else {
                        // non special case command, just create an object for it
                        var cmd = new Object();

                        // type isnt blank, so we are in subscript or superscript, take the command and add it as the subscript or superscript latex
                        // this allows things like x_\pi to work properly
                        if (type != "")
                        {
                            cmd.latex = "\\" + inbrackets;
                            cmd.type = type;
                            output.push(cmd);
                        } else {
                            // nothing special at all, just create a extra command on the output stack
                            cmd.latex = inbrackets;
                            cmd.type = "command";
                            output.push(cmd);
                        }
                        type = "";
                        i = end - 1;
                    }
                }

            } else if (text[i] == "{") { 
                // are we entering a { bracket piece, if so find the end and use it as a single entity
                var end = this.getEndMatchedBracketPosition(text, i, "{", "}");
                if (end > 0)
                {
                    var inbrackets = text.substring(i + 1, end);
                    var cmd = new Object();
                    cmd.latex = inbrackets;
                    if (type == "")
                        type = "arg";
                    cmd.type = type;
                    output.push(cmd);
                    type = "";
                    i = end;                  
                }

            } else if (text[i] == "(") { 
                // are we entering a ( bracket piece, if so find the end and use it as a single entity
                // VALIDATE THE RESULT OF THE PAIRING IS OK, IF NOT HAVE SOME SORT OF ERROR
                var res = this.getEndBracketPosition(text, i);
                var end = res.offset;
                
                if (end > 0)
                {
                    var inbrackets = text.substring(i + 1, end);
                    var trimres = this.TrimAnyRSize(inbrackets);;
                    inbrackets = trimres.text;
                    
                    var cmd = new Object();
                    cmd.latex = "pbrackets";
                    cmd.type = "extpair";
                    cmd.size = sizemod;
                    cmd.sizer = trimres.size;

                    if (res.match) cmd.closing = this.getClosingBracketText(res.match);
                    output.push(cmd);

                    var cmd = new Object();
                    cmd.latex = inbrackets;
                    cmd.type = "arg";
                    output.push(cmd);

                    type = "";
                    i = end + res.match.length - 1;
                    //sizemod = 0;
                } else {
                    // unable to find ending ), add as single element
                    var cmd = new Object();
                    cmd.latex = "(";
                    cmd.type = "extsingle";
                    cmd.size = sizemod;
                    output.push(cmd);
                    //sizemod = 0;                        
                }

            } else if (text[i] == "[") { 
                // have we found a [ set? add as a single entity if paired ] is found
                // this is a special case of the bracketed sets earlier, but needs to 
                // be still separate due to [] sometimes being used for arguments to commands
                // NOTE: MAKE SURE IF NO PAIRED ] IS FOUND, THEN OUTPUT AS A SINGLE [

                var end = this.getEndMatchedBracketPosition(text, i, "[", "]");
                if (end > 0)
                {
                    var inbrackets = text.substring(i + 1, end);
                    var trimres = this.TrimAnyRSize(inbrackets);
                    inbrackets = trimres.text;

                    var cmd = new Object();
                    cmd.latex = "psqbrackets";
                    cmd.type = "extpair"; // sometimes this will be taken as an argument for a command
                    cmd.size = sizemod;
                    cmd.sizer = trimres.size;
                    output.push(cmd);

                    var cmd = new Object();
                    cmd.latex = inbrackets;
                    cmd.type = "arg";
                    output.push(cmd);

                    type = "";
                    i = end;
                    //sizemod = 0;
                } else {
                    // No ] found, add as single extendable element
                    var cmd = new Object();
                    cmd.latex = "[";
                    cmd.type = "extsingle";
                    cmd.size = sizemod;
                    output.push(cmd);  
                    //sizemod = 0;                      
                }

            } else if ( /*text[i] == "}" ||*/ text[i] == ")" || text[i] == "]") {
                // have we found a stray ending bracket, } ] ), add it as a single element
                var cmd = new Object();
                cmd.latex = text[i];
                cmd.type = "extsingle";
                cmd.size = sizemod;
                output.push(cmd);                        

            } else if (text[i] == "_") {
                // have we found a subscript character? Note this so it can be used for the 
                // next element or {} elements found
                type = "subscript";

            } else if (text[i] == "}") {
                // found a stray } so show an error
                //alert("Missing {");

            } else if (text[i] == "^") {
                // have we found a superscript character? Note this so it can be used for the 
                // next element or {} elements found
                type = "superscript";

            } else {
                // just have a normal single character to deal with

                if (text[i] == "&") { 
                    // Handler the tab when aligning stuff
                    var cmd = new Object();
                    cmd.latex = "";
                    cmd.type = "tab";
                    output.push(cmd);
                    type = "";
                } else {
                    var char = text[i];
                    
                    // some characters are automatically replace, so check for this here
                    if (this.replace[char]) char = this.replace[char];

                    // we just have a single character to handle
                    var cmd = new Object();
                    cmd.latex = char;
                    cmd.type = type;
                    output.push(cmd);
                    type = "";
                }
            }

            // reset size modifier
            sizemodscope--;
            if (sizemodscope == 0)
                sizemod = 0;
        }
        return output;
        //alert(output);
    },

    buildelements: function (tokens) {
        var i;
        var elems = new Array();
        this.removeprev = false;
        for (i = 0 ; i < tokens.length ; i++)
        {
            var token = tokens[i];
            var eldata = this.getElementData(token);

            if (token.type == "" || token.type == "command" || token.type == "extpair") {
                // token.type == "" : do we just have a normal letter or number
                // token.type == "command": we have a command, look it up, check how many arguments allowed and add em to the
                // token.type == "extpair": pair of brackets, create a element for the brackets, and add the contents as an argument

                if (token.type == "command" && token.latex == "end")
                {
                    i++;
                    continue;
                }

                var elem = null;
                if (eldata.object)
                {
                    var name = "Elem" + eldata.object;
                    elem = new MEE[name](token, eldata);
                } else {
                    elem = new MEE.Elem(token, eldata);
                }
                i = this.addToElement(tokens, i, elem);

                if (this.removeprev)
                {
                    elems.pop();
                }

                elems.push(elem);

            } else if (token.type == "superscript" || token.type == "subscript") {
                // add a blank space element when we have a script that has no preceding
                // element available
                var blanktoken = new Object();
                blanktoken.latex = '&nbsp;';
                blanktoken.type = '';
                var elem = new MEE.Elem(blanktoken, this.getElementData('base'));
                elem.SetScript(token);
                elems.push(elem);

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
                if (eldata.custalign)
                {
                    var text = $.trim(tokens[i+1].latex);
                    if (text[0] == "[")
                    {
                        var end = text.indexOf(']');
                        if (end != -1)
                        {
                            alignment = text.substr(1,end-1);
                            tokens[i+1].latex = text.substr(end+1);
                        }
                    } else if (text[0] == "{")
                    {
                        var end = text.indexOf('}');
                        if (end != -1)
                        {
                            alignment = text.substr(1,end-1);
                            tokens[i+1].latex = text.substr(end+1);
                        }
                    } else {
                        alignment = text.substr(0,1)
                        tokens[i+1].latex = text.substr(1);
                    }
                }
                elem.AddArray(tokens[i+1],alignment);

                elems.push(elem);
                i++;              
                 
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
        
        if (elemdata.pn_as_upperlower && i > 1 && i < tokens.length)
        {
            elem.AddUpperLower(tokens[i-2],tokens[i]);
            this.removeprev = true;
            return i;
        } else if (elemdata.next_as_arg0 && i < tokens.length) {
            elem.AddArg(tokens[i]);
            return i;
        }

        for ( ; i < tokens.length ; i++)
        {
            var token = tokens[i];
            if (token.type == "superscript")
            {
                elem.SetScript(token);
            } else if (token.type == "subscript")
            {
                elem.SetScript(token);
            } else if (token.type == "command")
            {
                break;
            } else if (token.type == "arg")
            {
                if (elemdata.arg01_as_upperlower && argno == 0) {
                    // should first 2 arguments be used as upper and lower
                    elem.AddUpperLower(token,tokens[i+1]);
                    i++;
                } else if (elemdata.arg0_as_array && argno == 0) {
                    // should first 2 arguments be used as upper and lower
                    elem.AddArray(token,tokens[i+1]);
                    i++;
                } else if (elemdata.arg0_as_main && argno == 0) {
                    // should the 1st argument be the main text of the element
                    elem.SetMain(token);
                } else if (elemdata.arg0_as_super && argno == 0) {
                    // should the 1st argument be the main text of the element
                    elem.SetScript(token,"superscript");
                } else if (elemdata.arg0_as_sub && argno == 0) {
                    // should the 1st argument be the main text of the element
                    elem.SetScript(token,"subscript");
                } else if (elemdata.arg1_as_main && argno == 1) {
                    // should the 1st argument be the main text of the element
                    elem.SetMain(token);
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
                        elem.SetScript(tokens[i+1],"superscript");
                        i++;
                        elemdata.sarg = 0;
                    } else if (elemdata.sarg > 0) {   

                        elem.SetSArg(tokens[i+1]);
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
            if (this.isAlpha(token.latex))
            {
                return MEE.Parser.commands.variable;
            } else if (this.isNumeric(token.latex))
            {
                return MEE.Parser.commands.digit;
            }/* else {
                return MEE.Parser.commands.digit;
            }*/
        }
        var latex = token.latex;
        if (token.type != "")
            latex = "\\" + latex;
        var el = MEE.Parser.commands[latex];

        if (el)
        {
            return el;
        }

        el = new Object();
        el.args = 0;
        el.sarg = 0;

        return el;
    },

    // is the character a valid alpha numeric char
    isAlpha: function (sText) {
        var ValidChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        var IsNumber = true;
        var Char;


        for (i = 0; i < sText.length && IsNumber == true; i++) {
            Char = sText.charAt(i);
            if (ValidChars.indexOf(Char) == -1) {
                IsNumber = false;
            }
        }
        return IsNumber;

    },

    // is the character a valid alpha numeric char
    isNumeric: function (sText) {
        var ValidChars = "0123456789";
        var IsNumber = true;
        var Char;


        for (i = 0; i < sText.length && IsNumber == true; i++) {
            Char = sText.charAt(i);
            if (ValidChars.indexOf(Char) == -1) {
                IsNumber = false;
            }
        }
        return IsNumber;

    },

    // is the character a valid alpha numeric char
    isSingleLetterCommand: function (sText) {
        var ValidChars = ":,;.>\\/!*{}";

        if (ValidChars.indexOf(sText) != -1)
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
    getEndOfCommand: function (text, initial) {
        for (var i = initial; i < text.length; i++) {
            if (i == initial) {
                if (this.isSingleLetterCommand(text[i]))
                    return i+1;
            }
            if (!this.isAlpha(text[i]))
                return i;
        }
        return text.length;
    },

    // find the position of the closing bracket for the argument
    getEndMatchedBracketPosition: function (text, initial, open, close) {
        var bkcount = 0;
        for (var i = initial; i < text.length; i++) {
            if (text[i] == open)
                bkcount++;
            if (text[i] == close)
                bkcount--;

            if (bkcount == 0)
                return i;
        }
        return 0;
    },

    // find the position of the closing bracket
    getEndBracketPosition: function (text, initial, open, close) {
        var bkcount = 0;

        var result = new Object();
        result.offset = 0;
        result.match = "";

        // match any opening and closing brackets

        for (var i = initial; i < text.length; i++) {
            for (var k = 0 ; k < this.pairs.length ; k++) {
                var llen = this.pairs[k].left.length;
                if (text.substr(i,llen) == this.pairs[k].left)
                    bkcount++;

                var llen = this.pairs[k].right.length;
                if (text.substr(i,llen) == this.pairs[k].right)
                {
                    result.match = this.pairs[k].right;
                    bkcount--;
                }
            }

            if (bkcount == 0)
            {
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
        for (i = 0 ; i < this.sizemodifiers.length ; i++)
        {
            var left = this.sizemodifiers[i].left;
            var right = this.sizemodifiers[i].right;

            if (text == left)
                return true;

            if (text == right)
                return true;
        }

        if (text in this.sizemodifiers_single)
        {
            return true;
        }
        return false;
    },

    // for a given sizing modifer command, return the size specified
    getSize: function (text) {
        var i = 0;
        for (i = 0 ; i < this.sizemodifiers.length ; i++)
        {
            var left = this.sizemodifiers[i].left;
            var right = this.sizemodifiers[i].right;

            if (text == left)
                return this.sizemodifiers[i].size;

            if (text == right)
                return this.sizemodifiers[i].size;
        }

        if (text in this.sizemodifiers_single)
        {
            return this.sizemodifiers_single[text];
        }

        return 0;
    },

    // if the last part of the text is a right sizing command then remove it
    TrimAnyRSize: function (text) {
        var result = new Object();
        result.text = text;
        result.size = 0;

        for (var i = 0 ; i < this.sizemodifiers.length ; i++)
        {
            var len = this.sizemodifiers[i].right.length;
            if (text.substr(text.length - len,len) == this.sizemodifiers[i].right)
            {
                result.text = text.substr(0,text.length - len);
                result.size = this.getSize(text.substr(text.length - len,len));
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

        for (i = 0 ; i < this.pairs.length ; i++)
        {
            var left = this.pairs[i].left;
            var right = this.pairs[i].right;

            if (text == left)
                return true;
  
            if (!leftonly && text == right)
                return true;
      }
        return false;
    },

    // return the pair command object
    getPairCmd: function(text) {
        var i = 0;

        for (i = 0 ; i < this.pairs.length ; i++)
        {
            var left = this.pairs[i].left;
            var right = this.pairs[i].right;

            if (text == left)
                return this.pairs[i];
  
            if (text == right)
                return this.pairs[i];
      }
        return null;

    },

    // find the end element of a paired command
    findPairEnd: function(text, initial, pair) {

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

        for (i = 0 ; i < this.nonpairs.length ; i++)
        {
            var left = this.nonpairs[i];
        
            if (text == left)
                return true;
        }
        return false;

    },

    getClosingBracketText: function (match) {
                                
        var newtoken = new Object();
        newtoken.type = "command";
        if (match.length == 1)
        {
            newtoken.latex = match;
        } else {
            newtoken.latex = match.substr(1);
        }
        var closingdata = this.getElementData(newtoken);
        if (closingdata.text)
        {
            return closingdata.text;
        } else {
            return match;
        }
    },
});
