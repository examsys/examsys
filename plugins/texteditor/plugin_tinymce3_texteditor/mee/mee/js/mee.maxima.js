$.Class.extend("MEE.Maxima",
{
    Convert: function (elementset) {
        /*

        xy => x * y
        2x + y => 2 * x + y
        sin xy => sin(x) * y ??
        sin (xy) => sin(xy)

        (2x+3)(3x+4) => (2 * x + 3) * (3 * x + 4)

        */
        var maxima_parser = new MEE.Maxima();
        var maxima = maxima_parser.ProcessSet(elementset);

        //if (maxima.charAt(0) == "(" && maxima.charAt(maxima.length - 1) == ")")
        //    maxima = maxima.substr(1, maxima.length - 2);

        return maxima;
    }
},
{
    init: function (token, eldata) {
        this.parser = new MEE.Parser();
    },


    ProcessSet: function (elementset) {

        var m_elems;

        var settype = elementset._name;

        if (settype == "MEE.ElemSetNormal") {
            m_elems = this.ProcessSet_Normal(elementset);
        } else if (settype == "MEE.ElemSetArray") {
            m_elems = this.ProcessSet_Array(elementset);
        }


        // this code is wrong and needs writing properly, this 
        // is just so i can see whats going on
        // need to process for * insertion
        var output = "";
        var lastel = {};
        lastel.type = "";
        lastel.maxima = "";
        m_elems.push(lastel);


        m_elems2 = new Array();


        // go through all elements and merge the ones that need mergeing, such as digits and functions
        for (var i = 0; i < m_elems.length - 1; i++) {
            var elm = m_elems[i];
            var nextel = m_elems[i + 1];

            if (elm.type == "")
                continue;

            if (elm.type == "digit") {
                // go through until type != digit
                for (var k = i + 1; k < m_elems.length - 1; k++) {
                    var elm2 = m_elems[k];
                    if (elm2.type == "digit") {
                        elm.maxima += elm2.maxima;
                    } else {
                        break;
                    }
                }
                i = k - 1;
                m_elems2.push(elm);
            } else if (elm.type == "function") {
                if (elm.args == 1) {
                    elm.maxima = " " + elm.maxima + "(" + nextel.maxima + ") ";
                    i++;
                } else {
                    output += " " + elm.maxima + " ";
                }
                m_elems2.push(elm);
            } else {
                m_elems2.push(elm);
            }
        }
        m_elems2.push(lastel);

        // stick a * between all elements that arent operators

        for (var i = 0; i < m_elems2.length - 1; i++) {
            var elm = m_elems2[i];
            var nextel = m_elems2[i + 1];

            if (elm.type == "operator") {
                output += " " + elm.maxima + " ";
            } else {
                output += elm.maxima;
            }

            if (nextel.type != "" && elm.type != "operator" && nextel.type != "operator") {
                output += " * ";
            }
        }

        if (m_elems2.length > 2)
            output = "(" + output + ")";

        return output;
    },

    ProcessSet_Normal: function (elementset) {
        var m_elems = new Array();

        for (var i = 0; i < elementset.elements.length; i++) {
            var elem = elementset.elements[i];
            if (elem._name == "MEE.ElemInput")
                continue;

            if (elem.latex == "")
                continue;

            var el_maxima = this.ProcessElem(elem);
            m_elems.push(el_maxima);
        }

        return m_elems;
    },

    ProcessSet_Basic: function (elementset) {
        var m_elems = new Array();

        return m_elems;
    },

    ProcessSet_Array: function (elementset) {
        var m_elems = new Array();

        if (elementset.eldata.base == "base_matrix") { // matrix
            var m = "matrix (";
            for (var r = 0; r < elementset.rows; r++) {
                var row = elementset['row' + r];
                m += "[";

                for (var c = 0; c < row.cols; c++) {
                    var col = row['col' + c];

                    m += this.ProcessSet(col);

                    if (c + 1 < row.cols)
                        m += ", ";
                }

                m += "]";

                if (r + 1 < elementset.rows)
                    m += ", ";
            }

            m += ")";

            var elm = {
              type : "matrix",
              maxima : m
            };

        } else { // fraction
            // should return a single element with the maxima in
            var top = elementset.row0.col0;
            var bottom = elementset.row1.col0;

            var topm = this.ProcessSet(top);
            var bottomm = this.ProcessSet(bottom);

            var elm = {
              type : "fraction",
              maxima : "(" + topm + ")/(" + bottomm + ")"
            };
        }

        m_elems.push(elm);

        return m_elems;
    },

    ProcessElem: function (elem) {
        // turn an element into some maxima
        var el_maxima = {
          elem : elem,
          type : this.GetElementType(elem)
        };

        // get the maxima of the element
        this.GetElementMaxima(elem, el_maxima);

        return el_maxima;
    },

    GetElementType: function (elem) {
        var eldata = this.parser.getElementData(elem);

        // variables
        if (eldata._name == "variable")
            return "variable";

        // numeric (1.23)
        if (eldata._name == "digit")
            return "digit";
        if (eldata._name == ".")
            return "digit";

        // binary operators, + - etc
        if (eldata.base == "base_binary")
            return "operator";

        if (eldata.base == "base_greek")
            return "greek";

        if (eldata.base == "base_matrix")
            return "matrix";

        // function, sin cos etc
        if (eldata.base == "base_names")
            return "function";

        if (elem.latex == "pbrackets")
            return "brackets";

        if (elem.latex == "frac")
            return "fraction";

        if (elem.latex == "sqrt")
            return "root";

        return "";
    },

    GetElementMaxima: function (elem, el_maxima) {
        var do_sup = true;

        // need to add things like superscripts here. also parse out things like main and args if available
        if (el_maxima.type == "digit") {
            // easy, return the content
            el_maxima.maxima = elem.latex;

        } else if (el_maxima.type == "variable") {
            el_maxima.maxima = elem.latex;

        } else if (el_maxima.type == "greek") {
            el_maxima.maxima = "%" + elem.latex;

        } else if (el_maxima.type == "operator" || el_maxima.type == "function") {
            var op = elem.latex;
            var mapping = MEE.Maxima.Mappings[op];
            if (!mapping)
                return;

            el_maxima.maxima = mapping.maxima;
            el_maxima.args = mapping.args;

        } else if (el_maxima.type == "brackets") {
            el_maxima.maxima = "(" + this.ProcessSet(elem.main) + ")";

        } else if (el_maxima.type == "fraction" || el_maxima.type == "matrix") {
            el_maxima.maxima = this.ProcessSet(elem.main);

        } else if (el_maxima.type == "root") {
            do_sup = false;
            var ssmaxima;
            if (elem.superscript)
                ssmaxima = this.ProcessSet(elem.superscript);

            if (ssmaxima) {
                el_maxima.maxima = "(" + this.ProcessSet(elem.main) + ")^(1/" + ssmaxima + ")";
            } else {
                el_maxima.maxima = "sqrt(" + this.ProcessSet(elem.main) + ")";
            }
        }

        if (do_sup && elem.superscript) {
            var ssmaxima = this.ProcessSet(elem.superscript);
            if (ssmaxima != "")
                el_maxima.maxima = el_maxima.maxima + "^" + ssmaxima;
        }
    }
});

MEE.Maxima.Mappings = {
    // function
    'sin': { maxima: 'sin', args: 1 },
    'cos': { maxima: 'cos', args: 1 },
    'tan': { maxima: 'tan', args: 1 },
    'sinh': { maxima: 'sinh', args: 1 },
    'cosh': { maxima: 'cosh', args: 1 },
    'tanh': { maxima: 'tanh', args: 1 },
    'arcsin': { maxima: 'asin', args: 1 },
    'arccos': { maxima: 'acos', args: 1 },
    'arctan': { maxima: 'atan', args: 1 },

    'log': { maxima: 'log', args: 1 },
    'sqrt': { maxima: 'sqrt', args: 1 },
    'exp': { maxima: 'exp', args: 1 },

    // operators
    '+': { maxima: '+' },
    '-': { maxima: '-' },
    'times': { maxima: '*' }
};