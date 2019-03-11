// Character data

//#region blank spacing
//MEE.Data.blankspace = "<span class='mee_blankspace'>!</span>";
MEE.Data.blankspace = "<span class='mee_blankspace'>&#x200b;</span>";
MEE.Data.blankspacesize = function (elem) {
    return 0;

    var fs = parseInt(elem.css('font-size').replace('px', ''));
    return Math.floor(fs / 3.6);
}
MEE.Data.emptywidth = 0.4;
//#endregion

//#region brackets that can grow past size 4, made up of 3 or 4 separate characters
MEE.Data.extbrackets = {
    '{': { top: '&#x23A7;', mid: '&#x23AA;', bottom: '&#x23A9;', angle: '&#x23A8;' },
    '}': { top: '&#x23AB;', mid: '&#x23AA;', bottom: '&#x23AD;', angle: '&#x23AC;' },

    '(': { top: '&#x239B;', mid: '&#x239C;', bottom: '&#x239D;' },
    ')': { top: '&#x239E;', mid: '&#x239F;', bottom: '&#x23A0;' },

    '[': { top: '&#x23A1;', mid: '&#x23A2;', bottom: '&#x23A3;' },
    ']': { top: '&#x23A4;', mid: '&#x23A5;', bottom: '&#x23A6;' },

    
    '&#x2308;': { top: '&#x23A1;', mid: '&#x23A2;', bottom: '&#x23A2;' }, // lceil
    '&#x2309;': { top: '&#x23A4;', mid: '&#x23A5;', bottom: '&#x23A5;' }, // rceil

    '&#x230A;': { top: '&#x23A2;', mid: '&#x23A2;', bottom: '&#x23A3;' }, // lfloor
    '&#x230B;': { top: '&#x23A5;', mid: '&#x23A5;', bottom: '&#x23A6;' }, // rfloor

    '&#x27EE;': { top: '&#x23A7;', mid: '&#x23AA;', bottom: '&#x23A9;' }, // lgroup
    '&#x27EF;': { top: '&#x23AB;', mid: '&#x23AA;', bottom: '&#x23AD;' }, // rgroup

    '&#x23B0;': { top: '&#x23A7;', mid: '&#x23AA;', bottom: '&#x23AD;' }, // lmoustache
    '&#x23B1;': { top: '&#x23AB;', mid: '&#x23AA;', bottom: '&#x23A9;' }, // rmoustache

    '|': { top: '|', mid: '|', bottom: '|' }, // lvert // rvert

    '&#x2225;': { top: '&#x2225;', mid: '&#x2225;', bottom: '&#x2225;' }, // lVert //rVert
    

    '&#x221A;': { top: '&#xE001;', mid: '&#x23B8;', bottom: '&#x23B7;' }, // sqrt

    '&#x23D0;': { top: '|', mid: '|', bottom: '|' }, // arrowvert 
    '&#x2016;': { top: '&#x2225;', mid: '&#x2225;', bottom: '&#x2225;' }, // Arrowvert 
    '&#x23AA;': { top: '&#x23AA;', mid: '&#x23AA;', bottom: '&#x23AA;' }, // bracevert 

    '&#x2191;': { top: '&#x2191;', mid: '&#x23D0;', bottom: '&#x23D0;', font: 'MathJax_Size1' }, // uparrow 
    '&#x21D1;': { top: '&#x21D1;', mid: '&#x2016;', bottom: '&#x2016;', font: 'MathJax_Size1' }, // Uparrow 
    '&#x2193;': { top: '&#x23D0;', mid: '&#x23D0;', bottom: '&#x2193;', font: 'MathJax_Size1' }, // downarrow 
    '&#x21D3;': { top: '&#x2016;', mid: '&#x2016;', bottom: '&#x21D3;', font: 'MathJax_Size1' }, // Downarrow 
    '&#x2195;': { top: '&#x2191;', mid: '&#x23D0;', bottom: '&#x2193;', font: 'MathJax_Size1' }, // updownarrow 
    '&#x21D5;': { top: '&#x21D1;', mid: '&#x2016;', bottom: '&#x21D3;', font: 'MathJax_Size1' } // Updownarrow 

};
//#endregion

//#region sizes of various characters used in extensible brackets
MEE.Data.charsizes = {
    // {
    '&#x23A7;': { top: 0.86, height: 0.88 }, 
    '&#x23AA;': { top: 1.45, height: 0.31 }, 
    '&#x23A9;': { top: 1.80, height: 0.88 }, 
    '&#x23A8;': { top: 0.58, height: 1.80 }, 

    // }
    '&#x23AB;': { top: 0.86, height: 0.88 }, 
    '&#x23AD;': { top: 1.80, height: 0.88 }, 
    '&#x23AC;': { top: 0.58, height: 1.80 }, 

    // (
    '&#x239B;': { top: 0.60, height: 1.80 }, 
    '&#x239C;': { top: 1.15, height: 0.60 }, 
    '&#x239D;': { top: 0.60, height: 1.80 },

    // )
    '&#x239E;': { top: 0.60, height: 1.80 }, 
    '&#x239F;': { top: 1.15, height: 0.60 }, 
    '&#x23A0;': { top: 0.60, height: 1.80 }, 

    // [
    '&#x23A1;': { top: 0.59, height: 1.79 }, 
    '&#x23A2;': { top: 1.15, height: 0.59 }, 
    '&#x23A3;': { top: 0.62, height: 1.78 },

    // ]
    '&#x23A4;': { top: 0.59, height: 1.79 }, 
    '&#x23A5;': { top: 1.15, height: 0.59 }, 
    '&#x23A6;': { top: 0.62, height: 1.78 }, 

    // |
    '|': { top: 1, height: 0.9 }, 

    // ||
    '&#x2225;': { top: 1.15, height: 0.62 }, 

    // updownarrow 
    '&#x2191;': { top: 0.25, height: 0.6 }, 
    '&#x23D0;': { top: 0.25, height: 0.6 }, 
    '&#x2193;': { top: 0.25, height: 0.6 }, 

    // Updownarrow 
    '&#x21D1;': { top: 0.25, height: 0.6 }, 
    '&#x2016;': { top: 0.25, height: 0.6 }, 
    '&#x21D3;': { top: 0.25, height: 0.6 }, 

    // sqrt 
    '&#xE001;': { top: 1, height: 0.6 }, 
    '&#x23B8;': { top: 1.15, height: 0.62 }, 
    '&#x23B7;': { top: 0.83, height: 1.815 } 
};
//#endregion

//#region characters to replace when outputting html
MEE.Data.replace =  {
        '-':   '&#x2212;',
        '*':   '&#x2217;',
	    "'":   '&#x02B9;',
	    '<': '&lt;',
	    '>': '&gt;',
	    // idotsint needs odd styling and spans etc, so change it when outputting. makes the code neater
        '&idotsint;': '&#x222B;<span class="mee_idotsint_dots">&#x22EF;</span><span class="mee_idotsint_rint">&#x222B;</span>',
        '&#x222D;': '<span class="mee_iiint">&#x222B;&#x222B;&#x222B;</span>'
      };
    //#endregion

//#region list of size modifiers available, paired
MEE.Data.sizemodifiers =  [
        // size modifier 0 is same size as the text
        // size modifier -1 is auto size to the content of the line
        // size modifiers 1-4 are larger than the text sizing
        {left:'\\left',right:'\\right',size:-1},
        {left:'\\bigl',right:'\\bigr',size:1},
        {left:'\\Bigl',right:'\\Bigr',size:2},
        {left:'\\biggl',right:'\\biggr',size:3},
        {left:'\\Biggl',right:'\\Biggr',size:4}
        ];
//#endregion

//#region list of single size modifiers
MEE.Data.sizemodifiers_single =  {
        // size modifier 0 is same size as the text
        // size modifier -1 is auto size to the content of the line
        // size modifiers 1-4 are larger than the text sizing
        '\\big': 1,
        '\\Big': 2,
        '\\bigg': 3,
        '\\Bigg': 4
        };
    //#endregion

//#region the following can be affected by the size modifiers, will attempt to find pairs, if not use single only
MEE.Data.pairs = [ 
        {left:'(',right:')',pair:'pbrackets'},
        {left:'.',right:'.',pair:'pdotbrackets',onlysized:1},
        {left:'[',right:']',pair:'psqbrackets'},
        {left:'\\{',right:'\\}',pair:'pbrace'},
        {left:'\\lbrace',right:'\\rbrace',pair:'pbrace'},
        {left:'\\lvert',right:'\\rvert',pair:'pvert'},
        {left:'\\lVert',right:'\\rVert',pair:'pVert'},
        {left:'\\langle',right:'\\rangle',pair:'pangle'},
        {left:'\\lceil',right:'\\rceil',pair:'pceil'},
        {left:'\\lfloor',right:'\\rfloor',pair:'pfloor'},
        {left:'\\lgroup',right:'\\rgroup',pair:'pgroup'},
        {left:'\\lmoustache',right:'\\rmoustache',pair:'pmoustache'}
        ];
//#endregion

//#region the following can be affected by the size modifiers - single item only
MEE.Data.nonpairs = [
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
        '\\Updownarrow',
        '\\{',
        '\\}',
        '\\lbrace',
        '\\rbrace',
        '\\lvert',
        '\\rvert',
        '\\lVert',
        '\\rVert',
        '\\langle',
        '\\rangle',
        '\\lceil',
        '\\rceil',
        '\\lfloor',
        '\\rfloor',
        '\\lgroup',
        '\\rgroup',
        '\\lmoustache',
        '\\rmoustache',

        ];
//#endregion

//#region size and spacing of large characters such as sum
MEE.Data.largechars = {
    '&#x2211;': { top: 0.15, bottom: 0.2, width: 1.5, offset: -0.53 }, // sum
    '&#x222B;': { top: 0.53, bottom: 0.6, width: 0.9, offset: -0.53 }, // int
    '&#x2210;': { top: 0.15, bottom: 0.2, width: 1.25, offset: -0.53 }, // coprod
    '&#x22C1;': { top: 0.15, bottom: 0.2, width: 1.05, offset: -0.53 }, // bigvee
    '&#x22C0;': { top: 0.15, bottom: 0.2, width: 1.05, offset: -0.53 }, // bigwedge
    '&#x2A04;': { top: 0.15, bottom: 0.2, width: 1.05, offset: -0.53 }, // biguplus
    '&#x22C2;': { top: 0.15, bottom: 0.2, width: 1.05, offset: -0.53 }, // bigcap
    '&#x22C3;': { top: 0.15, bottom: 0.2, width: 1.05, offset: -0.53 }, // bigcup
    '&#x222C;': { top: 0.53, bottom: 0.6, width: 1.5, offset: -0.53 }, // iint
    '&#x222D;': { top: 0.53, bottom: 0.6, width: 1.9, offset: -0.53 }, // iiint
    '&idotsint;': { top: 0.53, bottom: 0.6, width: 2.05, offset: -0.53 }, // idotsint
    '&#x220F;': { top: 0.15, bottom: 0.2, width: 1.25, offset: -0.53 }, // prod
    '&#x2A02;': { top: 0.15, bottom: 0.2, width: 1.45, offset: -0.53 }, // bigotimes
    '&#x2A01;': { top: 0.15, bottom: 0.2, width: 1.45, offset: -0.53 }, // bigoplus
    '&#x2A00;': { top: 0.15, bottom: 0.2, width: 1.45, offset: -0.53 }, // bigodot
    '&#x222E;': { top: 0.53, bottom: 0.6, width: 0.9, offset: -0.53 }, // oint
    '&#x2A06;': { top: 0.15, bottom: 0.2, width: 1.05, offset: -0.53 }, // bigsqcup
    '&#x02DC;': { width: 1.01 } // widetilde
};
//#endregion

//#region width of large characters when used in textstyle mode (display > 1). full size not needed as they fit on a normal line
MEE.Data.largechars_size1 = {
    '&#x2211;': { width: 1 }, // sum
    '&#x222B;': { width: 0.6 }, // int
    '&#x2210;': { width: 0.93 }, // coprod
    '&#x22C1;': { width: 0.8 }, // bigvee
    '&#x22C0;': { width: 0.8 }, // bigwedge
    '&#x2A04;': { width: 0.8 }, // biguplus
    '&#x22C2;': { width: 0.8 }, // bigcap
    '&#x22C3;': { width: 0.8 }, // bigcup
    '&#x222C;': { width: 1 }, // iint
    '&#x222D;': { width: 1.3 }, // iiint
    '&idotsint;': { width: 1.55 }, // idotsint
    '&#x220F;': { width: 0.93 }, // prod
    '&#x2A02;': { width: 1.07 }, // bigotimes
    '&#x2A01;': { width: 1.07 }, // bigoplus
    '&#x2A00;': { width: 1.07 }, // bigodot
    '&#x222E;': { width: 0.6 }, // oint
    '&#x2A06;': { width: 0.8 }, // bigsqcup
    '&#x02DC;': { width: 0.57 } // widetilde
};

MEE.Data.largechars_size3 = {
    '&#x02DC;': { width: 1.45 } // widetilde
};

MEE.Data.largechars_size4 = {
    '&#x02DC;': { width: 1.90 } // widetilde
};
//#endregion

//#region font sizes to use at various depths
// 1 is base depth, and increase for each level we nest.
// subscripts jump to 4 
// arrays and the like dont increase thi
MEE.Data.fontsizes = {
    '1': '100%',
    '2': '100%',
    '3': '75%',
    '4': '75%',
    '5': '100%',
    '6': '100%',
    '7': '100%'
};
/*MEE.Data.fontsizes = {
    '1': '100%',
    '2': '100%',
    '3': '100%',
    '4': '100%',
    '5': '100%',
    '6': '100%',
    '7': '100%'
};*/
//#endregion

//#region widths of all the brackets used at different sizes. needed as outerWidth() doesnt always work
MEE.Data.bracketwidths = [
    { 
        brackets: ['(',')'],
        '0': 0.39,
        '1': 0.46,
        '2': 0.595,
        '3': 0.735,
        '4': 0.79,
        's': 0.875,
        'haslarge': 1,
        'canscale' : 1
    },
    { 
        brackets: ['[',']'],
        '0': 0.28,
        '1': 0.415,
        '2': 0.47,
        '3': 0.53,
        '4': 0.585,
        's': 0.665,
        'haslarge': 1,
        'canscale' : 1
    },
    { 
        brackets: ['{','}'],
        '0': 0.5,
        '1': 0.585,
        '2': 0.665,
        '3': 0.75,
        '4': 0.805,
        's': 0.89,
        'haslarge': 1,
        'canscale' : 1
    },
    { 
        brackets: ['&#x2308;','&#x2309;','&#x230A;','&#x230B;'], // ceil floor
        '0': 0.445,
        '1': 0.47,
        '2': 0.53,
        '3': 0.583,
        '4': 0.64,
        's': 0.665,
        'haslarge': 1,
        'canscale' : 1
    },
    { 
        brackets: ['|'],
        '0': 0.28,
        '1': 0.375,
        '2': 0.375,
        '3': 0.375,
        '4': 0.375,
        's': 0.375,
        'haslarge': 0,
        'canscale' : 1
    },
    { 
        brackets: ['&#x2225;'], // Vert
        '0': 0.5,
        '1': 0.555,
        '2': 0.58,
        '3': 0.58,
        '4': 0.58,
        's': 0.58,
        'haslarge': 0,
        'canscale' : 1
    },
    { 
        brackets: ['&#x27EE;', '&#x27EF;'], // group
        '0': 0.41,
        '1': 0.89,
        '2': 0.89,
        '3': 0.89,
        '4': 0.89,
        's': 0.89,
        'haslarge': 0,
        'canscale' : 1
    },
    { 
        brackets: ['&#x23B0;', '&#x23B1;'], // moustache
        '0': 0.41,
        '1': 0.89,
        '2': 0.89,
        '3': 0.89,
        '4': 0.89,
        's': 0.89,
        'haslarge': 0,
        'canscale' : 1
    },
    { 
        brackets: ['&#x27E8;', '&#x27E9;'], // angle
        '0': 0.39,
        '1': 0.47,
        '2': 0.61,
        '3': 0.75,
        '4': 0.805,
        's': 0,
        'haslarge': 1,
        'canscale' : 0
    },
    { 
        brackets: ['&#x221A;'], // sqrt
        '0': 0.835,
        '1': 1,
        '2': 1,
        '3': 1,
        '4': 1,
        's': 1.05,
        'haslarge': 1,
        'canscale' : 1
    },
    // non scalable
    /*'/',
    '\\backslash',*/
    { 
        brackets: ['/','\\'], // uparrow downarrow updownarrow
        '0': 0.5,
        '1': 0.58,
        '2': 0.81,
        '3': 1.045,
        '4': 1.28,
        's': 0,
        'haslarge': 1,
        'canscale' : 0
    },
  
    // scalable, no large
    { 
        brackets: ['&#x2191;','&#x2193;','&#x2195;'], // uparrow downarrow updownarrow
        '0': 0.5,
        '1': 0.665,
        '2': 0.665,
        '3': 0.665,
        '4': 0.665,
        's': 0.53,
        'haslarge': 0,
        'canscale' : 1
    },
    { 
        brackets: ['&#x21D1;','&#x21D3;','&#x21D5;'], // Uparrow Downarrow Updownarrow
        '0': 0.61,
        '1': 0.78,
        '2': 0.78,
        '3': 0.78,
        '4': 0.78,
        's': 0.77,
        'haslarge': 0,
        'canscale' : 1
    }

    /*
    '&#x23AA;': { top: '&#x23AA;', mid: '&#x23AA;', bottom: '&#x23AA;' }, // bracevert 

    */
];
    //#endregion

//#region heights of brackets at different font sizes. all brackets have the same height
MEE.Data.bracketheights = {
    '0': 0.9,
    '1': 1.2,
    '2': 1.8,
    '3': 2.4,
    '4': 3
};
//#endregion

//#region sizes of end arrows for things like xoverarrow
MEE.Data.arrowendwidths = {
    '&#x2190;': 0.4,
    '&#x2192;': 0.4,
    '&#x21D0;': 0.4,
    '&#x21D2;': 0.4,
    '&#x21BD;': 0.4,
    '&#x21BC;': 0.4,
    '&#x21C1;': 0.4,
    '&#x21C0;': 0.4,
    '&#xE150;': 0.4,
    '&#xE151;': 0.4,
    '&#xE152;': 0.4,
    '&#xE153;': 0.4,
    '&#xE154;': 0.4,
    '&#xE155;': 0.4,
    '&#xE156;': 0.4
};
//#endregion

//#region fraction and over arrow bars that are available.
MEE.Data.bars = {
    'single': {
        'chars': {
            1: '&#xE100;',
            0.5: '&#xE101;',
            0.25: '&#xE102;',
            0.1: '&#xE103;',
            0.05: '&#xE104;'
        }
    },
    'double': {
        'chars': {
            1: '&#xE110;',
            0.5: '&#xE111;',
            0.25: '&#xE112;',
            0.1: '&#xE113;',
            0.05: '&#xE114;'
        }
    },
    'doubleL': {
        'chars': {
            1: '&#xE120;',
            0.05: '&#xE124;'
        }
    },
    'sqrts': {
        'chars': {
            1: '&#xE160;',
            0.5: '&#xE161;',
            0.25: '&#xE162;',
            0.1: '&#xE163;',
            0.05: '&#xE164;'
        }
    },
   'sqrt': {
        'chars': {
            1: '&#xE170;',
            0.5: '&#xE171;',
            0.25: '&#xE172;',
            0.1: '&#xE173;',
            0.05: '&#xE174;'
        }
    }
};
//#endregion

//#region ce bond mappings
MEE.Data.bonds = {
    '-': '&#x2212;',
    '=': '=',
    '#': '&#x2261;',
    '~': '&#xE905;',
    '~-': '&#xE906;',
    '~=': '&#xE903;',
    '~--': '&#xE903;',
    '&#xE906;-': '&#xE903;',
    '-~-': '&#xE904;',
    '...': '&#xE901;',
    '....': '&#xE902;',
    '->': '&#x2190;',
    '<-': '&#x2192;'
}
//#endregion

// build table of replacable named ops (ie someone types sin without the \ will turn it to a proper sin
MEE.Data.buildNamedOps();

// build a table containing all the data required by each bracket (uses bracketheights and bracketwidths). 
MEE.Data.buildBracketSizes();