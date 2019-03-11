
MEE.Data.commands = {

    'base': {},
    // generic command for variables
    'variable': { args: 0, sarg: 0, mainclass: 'mee_variable' },
    // generic command for numbers
    'digit': { args: 0, sarg: 0, mainclass: 'mee_digit' },

    '\\input': { object: 'Answer' },

    // flag an invalid command for the editor
    'invalidcommand': { invalid: 1 },

    '\\XXREPLACEXX': { text: '', replaceme: 1 },

    //#region greek characters, lowercase
    'base_greek': { mainclass: 'mee_greek' },
    '\\alpha': { base: 'base_greek', text: '&#x03B1;' },
    '\\beta': { base: 'base_greek', text: '&#x03B2;' },
    '\\gamma': { base: 'base_greek', text: '&#x03B3;' },
    '\\delta': { base: 'base_greek', text: '&#x03B4;' },
    '\\epsilon': { base: 'base_greek', text: '&#x03F5;' },
    '\\zeta': { base: 'base_greek', text: '&#x03B6;' },
    '\\eta': { base: 'base_greek', text: '&#x03B7;' },
    '\\theta': { base: 'base_greek', text: '&#x03B8;' },
    '\\iota': { base: 'base_greek', text: '&#x03B9;' },
    '\\kappa': { base: 'base_greek', text: '&#x03BA;' },
    '\\lambda': { base: 'base_greek', text: '&#x03BB;' },
    '\\mu': { base: 'base_greek', text: '&#x03BC;' },
    '\\nu': { base: 'base_greek', text: '&#x03BD;' },
    '\\xi': { base: 'base_greek', text: '&#x03BE;' },
    '\\omicron': { base: 'base_greek', text: '&#x03BF;' },
    '\\pi': { base: 'base_greek', text: '&#x03C0;' },
    '\\rho': { base: 'base_greek', text: '&#x03C1;' },
    '\\sigma': { base: 'base_greek', text: '&#x03C3;' },
    '\\tau': { base: 'base_greek', text: '&#x03C4;' },
    '\\upsilon': { base: 'base_greek', text: '&#x03C5;' },
    '\\phi': { base: 'base_greek', text: '&#x03D5;' },
    '\\chi': { base: 'base_greek', text: '&#x03C7;' },
    '\\psi': { base: 'base_greek', text: '&#x03C8;' },
    '\\omega': { base: 'base_greek', text: '&#x03C9;' },
    '\\varepsilon': { base: 'base_greek', text: '&#x03B5;' },
    '\\vartheta': { base: 'base_greek', text: '&#x03D1;' },
    '\\varpi': { base: 'base_greek', text: '&#x03D6;' },
    '\\varrho': { base: 'base_greek', text: '&#x03F1;' },
    '\\varsigma': { base: 'base_greek', text: '&#x03C2;' },
    '\\varphi': { base: 'base_greek', text: '&#x03C6;' },
    '\\digamma': { base: 'base_greek', text: '&#x03DC;' },
    '\\varkappa': { base: 'base_greek', text: '&#x03F0;' },

    'base_greek_upper': {},
    '\\Gamma': { base: 'base_greek_upper', text: '&#x0393;' },
    '\\Delta': { base: 'base_greek_upper', text: '&#x0394;' },
    '\\Theta': { base: 'base_greek_upper', text: '&#x0398;' },
    '\\Lambda': { base: 'base_greek_upper', text: '&#x039B;' },
    '\\Xi': { base: 'base_greek_upper', text: '&#x039E;' },
    '\\Pi': { base: 'base_greek_upper', text: '&#x03A0;' },
    '\\Sigma': { base: 'base_greek_upper', text: '&#x03A3;' },
    '\\Upsilon': { base: 'base_greek_upper', text: '&#x03A5;' },
    '\\Phi': { base: 'base_greek_upper', text: '&#x03A6;' },
    '\\Psi': { base: 'base_greek_upper', text: '&#x03A8;' },
    '\\Omega': { base: 'base_greek_upper', text: '&#x03A9;' },
    //#endregion

    //#region Ord symbold
    'base_ordsymbol': {},
    '\\S': { base: 'base_ordsymbol', text: '&#x00A7;' },
    '\\hbar': { base: 'base_ordsymbol', text: '&#x210F;' },
    '\\imath': { base: 'base_ordsymbol', text: '&#x0131;' },
    '\\jmath': { base: 'base_ordsymbol', text: '&#x0237;' },
    '\\ell': { base: 'base_ordsymbol', text: '&#x2113;' },

    'base_ordsymbol_norm': { text: '' },
    '\\degree': { base: 'base_ordsymbol_norm', text: '&#x00B0;' },
    '\\aleph': { base: 'base_ordsymbol_norm', text: '&#x2135;' },
    '\\wp': { base: 'base_ordsymbol_norm', text: '&#x2118;' },
    '\\Re': { base: 'base_ordsymbol_norm', text: '&#x211C;' },
    '\\Im': { base: 'base_ordsymbol_norm', text: '&#x2111;' },
    '\\partial': { base: 'base_ordsymbol_norm', text: '&#x2202;' },
    '\\infty': { base: 'base_ordsymbol_norm', text: '&#x221E;' },
    '\\prime': { base: 'base_ordsymbol_norm', text: '&#x2032;' },
    '\\emptyset': { base: 'base_ordsymbol_norm', text: '&#x2205;' },
    '\\nabla': { base: 'base_ordsymbol_norm', text: '&#x2207;' },
    '\\top': { base: 'base_ordsymbol_norm', text: '&#x22A4;' },
    '\\bot': { base: 'base_ordsymbol_norm', text: '&#x22A5;' },
    '\\angle': { base: 'base_ordsymbol_norm', text: '&#x2220;' },
    '\\triangle': { base: 'base_ordsymbol_norm', text: '&#x25B3;' },
    '\\backslash': { base: 'base_ordsymbol_norm', text: '&#x2216;' },
    '\\forall': { base: 'base_ordsymbol_norm', text: '&#x2200;' },
    '\\exists': { base: 'base_ordsymbol_norm', text: '&#x2203;' },
    '\\neg': { base: 'base_ordsymbol_norm', text: '&#x00AC;' },
    '\\lnot': { base: 'base_ordsymbol_norm', text: '&#x00AC;' },
    '\\flat': { base: 'base_ordsymbol_norm', text: '&#x266D;' },
    '\\natural': { base: 'base_ordsymbol_norm', text: '&#x266E;' },
    '\\sharp': { base: 'base_ordsymbol_norm', text: '&#x266F;' },
    '\\clubsuit': { base: 'base_ordsymbol_norm', text: '&#x2663;' },
    '\\diamondsuit': { base: 'base_ordsymbol_norm', text: '&#x2662;' },
    '\\heartsuit': { base: 'base_ordsymbol_norm', text: '&#x2661;' },
    '\\spadesuit': { base: 'base_ordsymbol_norm', text: '&#x2660;' },
    '\\beth': { base: 'base_ordsymbol_norm', text: '&#x2136;' },
    '\\daleth': { base: 'base_ordsymbol_norm', text: '&#x2138;' },
    '\\gimel': { base: 'base_ordsymbol_norm', text: '&#x2137;' },
    '\\complement': { base: 'base_ordsymbol_norm', text: '&#x2201;' },
    '\\eth': { base: 'base_ordsymbol_norm', text: '&eth;' },
    '\\hslash': { base: 'base_ordsymbol_norm', text: '&#x0210F;', elemclass: 'mee_font_bb' },
    '\\mho': { base: 'base_ordsymbol_norm', text: '&#x2127;' },
    '\\circledS': { base: 'base_ordsymbol_norm', text: '&#x024C8;' },
    '\\Bbbk': { base: 'base_ordsymbol_norm', text: 'k', elemclass: 'mee_font_bb' },
    '\\Finv': { base: 'base_ordsymbol_norm', text: '&#x2132;' },
    '\\Game': { base: 'base_ordsymbol_norm', text: '&#x2141;', elemclass: 'mee_font_bb' },
    '\\surd': { base: 'base_ordsymbol_norm', text: '&#x221A;' },
    /* Missing ords: 
    \backprime
    \bigstar
    \blacklozenge
    \blacksquare
    \blacktriangle
    \blacktriangledown
    \diagdown
    \diagup
    \lozenge
    \measuredangle
    \nexists
    \sphericalangle
    \square
    \triangledown
    \varnothing
    */
    //#endregion

    //#region binary operations
    'base_binary': { mainclass: 'mee_binary', noautofrac: 1 },
    '\\triangleleft': { base: 'base_binary', text: '&#x25C3;' },
    '\\triangleright': { base: 'base_binary', text: '&#x25B9;' },
    '\\bigtriangleup': { base: 'base_binary', text: '&#x25B3;' },
    '\\bigtriangledown': { base: 'base_binary', text: '&#x25BD;' },
    '\\wedge': { base: 'base_binary', text: '&#x2227;' },
    '\\land': { base: 'base_binary', text: '&#x2227;' },
    '\\vee': { base: 'base_binary', text: '&#x2228;' },
    '\\lor': { base: 'base_binary', text: '&#x2228;' },
    '\\cap': { base: 'base_binary', text: '&#x2229;' },
    '\\cup': { base: 'base_binary', text: '&#x222A;' },
    '\\ddagger': { base: 'base_binary', text: '&#x2021;' },
    '\\dagger': { base: 'base_binary', text: '&#x2020;' },
    '\\sqcap': { base: 'base_binary', text: '&#x2293;' },
    '\\sqcup': { base: 'base_binary', text: '&#x2294;' },
    '\\uplus': { base: 'base_binary', text: '&#x228E;' },
    '\\amalg': { base: 'base_binary', text: '&#x2A3F;' },
    '\\diamond': { base: 'base_binary', text: '&#x22C4;' },
    '\\bullet': { base: 'base_binary', text: '&#x2219;' },
    '\\wr': { base: 'base_binary', text: '&#x2240;' },
    '\\div': { base: 'base_binary', text: '&#x00F7;' },
    '\\odot': { base: 'base_binary', text: '&#x2299;' },
    '\\oslash': { base: 'base_binary', text: '&#x2298;' },
    '\\otimes': { base: 'base_binary', text: '&#x2297;' },
    '\\ominus': { base: 'base_binary', text: '&#x2296;' },
    '\\oplus': { base: 'base_binary', text: '&#x2295;' },
    '\\mp': { base: 'base_binary', text: '&#x2213;' },
    '\\pm': { base: 'base_binary', text: '&#x00B1;' },
    '\\circ': { base: 'base_binary', text: '&#x2218;' },
    '\\bigcirc': { base: 'base_binary', text: '&#x25EF;' },
    '\\setminus': { base: 'base_binary', text: '&#x2216;' },
    '\\cdot': { base: 'base_binary', text: '&#x22C5;' },
    '\\ast': { base: 'base_binary', text: '&#x2217;' },
    '\\times': { base: 'base_binary', text: '&#x00D7;' },
    '\\star': { base: 'base_binary', text: '&#x22C6;' },
    '\\&': { base: 'base_binary', text: '&amp;' },
    '=': { base: 'base_binary' },
    '+': { base: 'base_binary' },
    '-': { base: 'base_binary' },
    '&#x2212;': { base: 'base_binary' },
    '*': { base: 'base_binary' },
    '&#x2217;': { base: 'base_binary' },
    '\\hyphen': { base: 'base_binary', text: '&#x002D;' },
    /* More missing
    \barwedge
    \boxdot
    \boxminus
    \boxplus
    \boxtimes
    \Cap
    \centerdot
    \circledast
    \circledcirc
    \circleddash
    \Cup
    \curlyvee
    \curlywedge
    \divideontimes
    \dotplus
    \doublebarwedge
    \gtrdot
    \intercal
    \leftthreetimes
    \lessdot
    \ltimes
    \rightthreetimes
    \rtimes
    \smallsetminus
    \veebar

    */
    //#endregion

    //#region Relations
    'base_relation': { mainclass: 'mee_operator' },
    '\\propto': { base: 'base_relation', text: '&#x221D;' },
    '\\sqsubseteq': { base: 'base_relation', text: '&#x2291;' },
    '\\sqsupseteq': { base: 'base_relation', text: '&#x2292;' },
    '\\parallel': { base: 'base_relation', text: '&#x2225;' },
    '\\mid': { base: 'base_relation', text: '&#x2223;' },
    '\\dashv': { base: 'base_relation', text: '&#x22A3;' },
    '\\vdash': { base: 'base_relation', text: '&#x22A2;' },
    '\\leq': { base: 'base_relation', text: '&#x2264;' },
    '\\le': { base: 'base_relation', text: '&#x2264;' },
    '\\geq': { base: 'base_relation', text: '&#x2265;' },
    '\\ge': { base: 'base_relation', text: '&#x2265;' },
    '\\lt': { base: 'base_relation', text: '&#x003C;' },
    '\\gt': { base: 'base_relation', text: '&#x003E;' },
    '\\succ': { base: 'base_relation', text: '&#x227B;' },
    '\\prec': { base: 'base_relation', text: '&#x227A;' },
    '\\approx': { base: 'base_relation', text: '&#x2248;' },
    '\\succeq': { base: 'base_relation', text: '&#x2AB0;' },
    '\\preceq': { base: 'base_relation', text: '&#x2AAF;' },
    '\\supset': { base: 'base_relation', text: '&#x2273;' },
    '\\subset': { base: 'base_relation', text: '&#x2282;' },
    '\\supseteq': { base: 'base_relation', text: '&#x2287;' },
    '\\subseteq': { base: 'base_relation', text: '&#x2286;' },
    '\\in': { base: 'base_relation', text: '&#x2208;' },
    '\\ni': { base: 'base_relation', text: '&#x220B;' },
    '\\notin': { base: 'base_relation', text: '&#x2209;' },
    '\\owns': { base: 'base_relation', text: '&#x220B;' },
    '\\gg': { base: 'base_relation', text: '&#x226B;' },
    '\\ll': { base: 'base_relation', text: '&#x226A;' },
    '\\sim': { base: 'base_relation', text: '&#x223C;' },
    '\\simeq': { base: 'base_relation', text: '&#x2243;' },
    '\\perp': { base: 'base_relation', text: '&#x22A5;' },
    '\\equiv': { base: 'base_relation', text: '&#x2261;' },
    '\\asymp': { base: 'base_relation', text: '&#x224D;' },
    '\\smile': { base: 'base_relation', text: '&#x2323;' },
    '\\frown': { base: 'base_relation', text: '&#x2322;' },
    '\\ne': { base: 'base_relation', text: '&#x2260;' },
    '\\neq': { base: 'base_relation', text: '&#x2260;' },
    '\\cong': { base: 'base_relation', text: '&#x2245;' },
    '\\doteq': { base: 'base_relation', text: '&#x2250;' },
    '\\bowtie': { base: 'base_relation', text: '&#x22C8;' },
    '\\models': { base: 'base_relation', text: '&#x22A8;' },
    '\\notChar': { base: 'base_relation', text: '&#x0338;' },
    /* More missing
    \approxeq
    \backsim
    \backsimeq
    \bumpeq
    \Bumpeq
    \circeq
    \curlyeqprec
    \curlyeqsucc
    \doteqdot
    \eqcirc
    \eqsim
    \eqslantgtr
    \eqslantless
    \fallingdotseq
    \geqq
    \geqslant
    \ggg
    \gnapprox
    \gneq
    \gneqq
    \gnsim
    \gtrapprox
    \gtreqless
    \gtreqqless
    \gtrless
    \gtrsim
    \gvertneqq
    \leqq
    \leqslant
    \lessapprox
    \lesseqgtr
    \lesseqqgtr
    \lessgtr
    \lesssim
    \lll
    \lnapprox
    \lneq
    \lneqq
    \lnsim
    \lvertneqq
    \ncong
    \ngeq
    \ngeqq
    \ngeqslant
    \ngtr
    \nleq
    \nleqq
    \nleqslant
    \nless
    \nprec
    \npreceq
    \nsim
    \nsucc
    \nsucceq
    \precapprox
    \preccurlyeq
    \precnapprox
    \precneqq
    \precnsim
    \precsim
    \risingdotseq
    \succapprox
    \succcurlyeq
    \succnapprox
    \succneqq
    \succnsim
    \succsim
    \thickapprox
    \thicksim
    \triangleq

    */

    /* another set of missing
    \backepsilon
    \because
    \between
    \blacktriangleleft
    \blacktriangleright
    \nmid
    \nparallel
    \nshortmid
    \nshortparallel
    \nsubseteq
    \nsubseteqq
    \nsupseteq
    \nsupseteqq
    \ntriangleleft
    \ntrianglelefteq
    \ntriangleright
    \ntrianglerighteq
    \nvdash
    \nVdash
    \pitchfork
    \shortmid
    \shortparallel
    \smallfrown
    \smallsmile
    \sqsubset
    \sqsupset
    \Subset
    \subseteqq
    \subsetneq
    \subsetneqq
    \Supset
    \supseteqq
    \supsetneq
    \supsetneqq
    \therefore
    \trianglelefteq
    \trianglerighteq
    \varpropto
    \varsubsetneq
    \varsubsetneqq
    \varsupsetneq
    \varsupsetneqq
    \vartriangle
    \vartriangleleft
    \vartriangleright
    \Vdash
    \vDash
    \Vvdash
    */
    //#endregion

    //#region arrows
    'base_arrows': { mainclass: 'mee_arrows' },
    '\\Leftrightarrow': { base: 'base_arrows', text: '&#x21D4;' },
    '\\Leftarrow': { base: 'base_arrows', text: '&#x21D0;' },
    '\\Rightarrow': { base: 'base_arrows', text: '&#x21D2;' },
    '\\leftrightarrow': { base: 'base_arrows', text: '&#x2194;' },
    '\\leftarrow': { base: 'base_arrows', text: '&#x2190;' },
    '\\gets': { base: 'base_arrows', text: '&#x2190;' },
    '\\rightarrow': { base: 'base_arrows', text: '&#x2192;' },
    '\\to': { base: 'base_arrows', text: '&#x2192;' },
    '\\mapsto': { base: 'base_arrows', text: '&#x21A6;' },
    '\\leftharpoonup': { base: 'base_arrows', text: '&#x21BC;' },
    '\\leftharpoondown': { base: 'base_arrows', text: '&#x21BD;' },
    '\\rightharpoonup': { base: 'base_arrows', text: '&#x21C0;' },
    '\\rightharpoondown': { base: 'base_arrows', text: '&#x21C1;' },
    '\\nearrow': { base: 'base_arrows', text: '&#x2197;' },
    '\\searrow': { base: 'base_arrows', text: '&#x2198;' },
    '\\nwarrow': { base: 'base_arrows', text: '&#x2196;' },
    '\\swarrow': { base: 'base_arrows', text: '&#x2199;' },
    '\\rightleftharpoons': { base: 'base_arrows', text: '&#x21CC;' },
    '\\hookrightarrow': { base: 'base_arrows', text: '&#x21AA;' },
    '\\hookleftarrow': { base: 'base_arrows', text: '&#x21A9;' },
    '\\longleftarrow': { base: 'base_arrows', text: '&#x27F5;' },
    '\\Longleftarrow': { base: 'base_arrows', text: '&#x27F8;' },
    '\\longrightarrow': { base: 'base_arrows', text: '&#x27F6;' },
    '\\Longrightarrow': { base: 'base_arrows', text: '&#x27F9;' },
    '\\Longleftrightarrow': { base: 'base_arrows', text: '&#x27FA;' },
    '\\longleftrightarrow': { base: 'base_arrows', text: '&#x27F7;' },
    '\\longmapsto': { base: 'base_arrows', text: '&#x27FC;' },

    '\\circlearrowleft': { base: 'base_arrows', text: '&#x21BA;' },
    '\\circlearrowright': { base: 'base_arrows', text: '&#x21BB;' },
    '\\curvearrowleft': { base: 'base_arrows', text: '&#x21B6;' },
    '\\curvearrowright': { base: 'base_arrows', text: '&#x21B7;' },
    '\\downdownarrows': { base: 'base_arrows', text: '&#x21CA;' },
    '\\downharpoonleft': { base: 'base_arrows', text: '&#x21C3;' },
    '\\downharpoonright': { base: 'base_arrows', text: '&#x21C2;' },
    '\\leftarrowtail': { base: 'base_arrows', text: '&#x21A2;' },
    '\\leftleftarrows': { base: 'base_arrows', text: '&#x21C7;' },
    '\\leftrightarrows': { base: 'base_arrows', text: '&#x21C6;' },
    '\\leftrightharpoons': { base: 'base_arrows', text: '&#x21CB;' },
    '\\leftrightsquigarrow': { base: 'base_arrows', text: '&#x21AD;' },

    '\\Lleftarrow': { base: 'base_arrows', text: '&#x21DA;' },
    '\\looparrowleft': { base: 'base_arrows', text: '&#x21AB;' },
    '\\looparrowright': { base: 'base_arrows', text: '&#x21AC;' },
    '\\Lsh': { base: 'base_arrows', text: '&#x21B0;' },
    '\\multimap': { base: 'base_arrows', text: '&#x22B8;' },
    '\\nLeftarrow': { base: 'base_arrows', text: '&#x21CD;' },
    '\\nLeftrightarrow': { base: 'base_arrows', text: '&#x21CE;' },
    '\\nRightarrow': { base: 'base_arrows', text: '&#x21CF;' },
    '\\nleftarrow': { base: 'base_arrows', text: '&#x219A;' },
    '\\nleftrightarrow': { base: 'base_arrows', text: '&#x21AE;' },
    '\\nrightarrow': { base: 'base_arrows', text: '&#x219B;' },

    '\\rightarrowtail': { base: 'base_arrows', text: '&#x21A3;' },
    '\\rightleftarrows': { base: 'base_arrows', text: '&#x21C4;' },
    '\\rightrightarrows': { base: 'base_arrows', text: '&#x21C9;' },
    '\\rightsquigarrow': { base: 'base_arrows', text: '&#x21DD;' },
    '\\Rrightarrow': { base: 'base_arrows', text: '&#x21DB;' },
    '\\Rsh': { base: 'base_arrows', text: '&#x21B1;' },
    '\\twoheadleftarrow': { base: 'base_arrows', text: '&#x219E;' },
    '\\twoheadrightarrow': { base: 'base_arrows', text: '&#x21A0;' },
    '\\upharpoonleft': { base: 'base_arrows', text: '&#x21BF;' },
    '\\upharpoonright': { base: 'base_arrows', text: '&#x21BE;' },
    '\\upuparrows': { base: 'base_arrows', text: '&#x21C8;' },
    //#endregion

    //#region dots
    'base_misc': {},
    '\\ldots': { base: 'base_misc', text: '&#x2026;' },
    '\\cdots': { base: 'base_misc', text: '&#x22EF;' },
    '\\vdots': { base: 'base_misc', text: '&#x22EE;' },
    '\\ddots': { base: 'base_misc', text: '&#x22F1;' },
    '\\dots': { base: 'base_misc', text: '&#x2026;' },
    '\\dotsc': { base: 'base_misc', text: '&#x2026;' },
    '\\dotsb': { base: 'base_misc', text: '&#x22EF;' },
    '\\dotsm': { base: 'base_misc', text: '&#x22EF;' },
    '\\dotsi': { base: 'base_misc', text: '&#x22EF;' },
    '\\dotso': { base: 'base_misc', text: '&#x2026;' },
    '\\ldotp': { base: 'base_misc', text: '&#x002E;' },
    '\\cdotp': { base: 'base_misc', text: '&#x22C5;' },
    '\\colon': { base: 'base_misc', text: '&#x003A;' },
    //#endregion

    //#region Named operators
    'base_names': { elemclass: 'mee_command_elem', cantype: 1 },
    '\\arcsin': { base: 'base_names' },
    '\\arccos': { base: 'base_names' },
    '\\arctan': { base: 'base_names' },
    '\\arg': { base: 'base_names' },
    '\\cos': { base: 'base_names' },
    '\\cosh': { base: 'base_names' },
    '\\cot': { base: 'base_names' },
    '\\coth': { base: 'base_names' },
    '\\csc': { base: 'base_names' },
    '\\deg': { base: 'base_names' },
    '\\det': { base: 'base_names', limits: 'above', limits_h: 1 },
    '\\dim': { base: 'base_names' },
    '\\exp': { base: 'base_names' },
    '\\gcd': { base: 'base_names', limits: 'above', limits_l: 1 },
    '\\hom': { base: 'base_names' },
    '\\inf': { base: 'base_names', limits: 'above', limits_h: 1 },
    '\\injlim': { base: 'base_names', limits: 'above', limits_h: 1, limits_l: 1, text: 'inj&thinsp;lim' },
    '\\ker': { base: 'base_names' },
    '\\lg': { base: 'base_names' },
    '\\lim': { base: 'base_names', limits: 'above', limits_h: 1 },
    '\\liminf': { base: 'base_names', limits: 'above', limits_h: 1, text: 'lim&thinsp;inf' },
    '\\limsup': { base: 'base_names', limits: 'above', limits_h: 1, limits_l: 1, text: 'lim&thinsp;sup' },
    '\\ln': { base: 'base_names' },
    '\\log': { base: 'base_names' },
    '\\max': { base: 'base_names', limits: 'above' },
    '\\min': { base: 'base_names', limits: 'above', limits_h: 1 },
    '\\Pr': { base: 'base_names', limits: 'above', limits_h: 1 },
    '\\sec': { base: 'base_names' },
    '\\sin': { base: 'base_names' },
    '\\sinh': { base: 'base_names' },
    '\\sup': { base: 'base_names', limits: 'above', limits_l: 1 },
    '\\tan': { base: 'base_names' },
    '\\tanh': { base: 'base_names' },

    // MISSING NAMED OPERATORS
    /*
    \varinjlim
    \varprojlim
    \varliminf
    \varlimsup
    */
    //#endregion

    //#region large operators
    'base_largeop': { mainclass: 'mee_largeop_main', limits_lx: 1, limits_hx: 1, large: 1, size1_font_if_depth: 1 },
    '\\coprod': { base: 'base_largeop', text: '&#x2210;', limits: 'above' },
    '\\bigvee': { base: 'base_largeop', text: '&#x22C1;', limits: 'above' },
    '\\bigwedge': { base: 'base_largeop', text: '&#x22C0;', limits: 'above' },
    '\\biguplus': { base: 'base_largeop', text: '&#x2A04;', limits: 'above' },
    '\\bigcap': { base: 'base_largeop', text: '&#x22C2;', limits: 'above' },
    '\\bigcup': { base: 'base_largeop', text: '&#x22C3;', limits: 'above' },
    '\\int': { base: 'base_largeop', text: '&#x222B;', offsetlimits: 1, ssoffsets:
        {
            '1': { sub: -0.5, sup: 0.1 },
            '2': { sub: -0.1, sup: 0.1 }
        }
    },
    '\\intop': { base: 'base_largeop', text: '&#x222B;', limits: 'above', offsetlimits: 1, ssoffsets:
        {
            '1': { sub: -0.5, sup: 0.1 },
            '2': { sub: -0.1, sup: 0.1 }
        }
    },
    '\\iint': { base: 'base_largeop', text: '&#x222C;', offsetlimits: 1, ssoffsets:
        {
            '1': { sub: -0.5, sup: 0.1 },
            '2': { sub: -0.1, sup: 0.1 }
        }
    },
    '\\iiint': { base: 'base_largeop', text: '&#x222D;', offsetlimits: 1, ssoffsets:
        {
            '1': { sub: -0.4, sup: 0.2 },
            '2': { sub: 0.1, sup: 0.3 }
        }
    },
    '\\idotsint': { base: 'base_largeop', text: '&idotsint;', limits: '', offsetlimits: 1, mainclass: 'mee_largeop_idotsint', ssoffsets:
        {
            '1': { sub: -0.4, sup: 0.2 },
            '2': { sub: 0.1, sup: 0.3 }
        }
    },
    '\\prod': { base: 'base_largeop', text: '&#x220F;', limits: 'above' },
    '\\sum': { base: 'base_largeop', text: '&#x2211;', limits: 'above' },
    '\\bigotimes': { base: 'base_largeop', text: '&#x2A02;', limits: 'above' },
    '\\bigoplus': { base: 'base_largeop', text: '&#x2A01;', limits: 'above' },
    '\\bigodot': { base: 'base_largeop', text: '&#x2A00;', limits: 'above' },
    '\\oint': { base: 'base_largeop', text: '&#x222E;', offsetlimits: 1, ssoffsets:
        {
            '1': { sub: -0.5, sup: 0.1 },
            '2': { sub: -0.1, sup: 0.1 }
        }
    },
    '\\bigsqcup': { base: 'base_largeop', text: '&#x2A06;', limits: 'above' },

    // modifiers
    '\\limits': { apply_to_previous: { foreclimits: 'above'} },
    '\\nolimits': { apply_to_previous: { foreclimits: ''} },

    // not a large operator
    '\\smallint': { text: '&#x222B;', limits: 'above', limits_lx: 1, limits_hx: 1 },
    //#endregion

    //#region accents
    'base_accents': { object: 'Accent', args: 1, mainclass: 'mee_accent_main' },
    '\\acute': { base: 'base_accents', text: '&#x02CA;' },
    '\\grave': { base: 'base_accents', text: '&#x02CB;' },
    '\\ddot': { base: 'base_accents', text: '&#x00A8;' },
    '\\dddot': { base: 'base_accents', text: '...', mainclass: 'mee_dots', handledots: 1 },
    '\\ddddot': { base: 'base_accents', text: '....', mainclass: 'mee_dots', handledots: 1 },
    '\\tilde': { base: 'base_accents', text: '&#x02DC;' },
    '\\bar': { base: 'base_accents', text: '&#x02C9;' },
    '\\breve': { base: 'base_accents', text: '&#x02D8;' },
    '\\check': { base: 'base_accents', text: '&#x02C7;' },
    '\\hat': { base: 'base_accents', text: '&#x02C6;' },
    '\\vec': { base: 'base_accents', text: '&#x20D7;', mainclass: 'mee_vector', nopadleft: 1 },
    '\\dot': { base: 'base_accents', text: '&#x02D9;' },

    // wide accents
    '\\widetilde': { base: 'base_accents', text: '&#x02DC;', accent_wide: 1 },
    '\\widehat': { base: 'base_accents', text: '&#x02C6;', accent_wide: 1 },
    /* Missing accents 
    \overline{xxx}\;
    \underline{xxx}\;
    \overbrace{xxx}\;
    \underbrace{xxx}\;
    \overleftarrow{xxx}\;
    \underleftarrow{xxx}\;
    \overrightarrow{xxx}\;
    \underrightarrow{xxx}\;
    \overleftrightarrow{xxx}\;
    \underleftrightarrow{xxx}\;
    */
    //#endregion

    //#region spaces
    'base_spaces': { object: 'Space' },
    '\\,': { base: 'base_spaces', space: '0.17em' },
    '\\:': { base: 'base_spaces', space: '0.17em' },
    '\\>': { base: 'base_spaces', space: '0.22em' },
    '\\;': { base: 'base_spaces', space: '0.27em' },
    '\\!': { base: 'base_spaces', space: '0em' }, // negative thin (-0.17em)
    '\\enspace': { base: 'base_spaces', space: '0.5em' },
    '\\quad': { base: 'base_spaces', space: '1em' },
    '\\qquad': { base: 'base_spaces', space: '2em' },
    '\\thinspace': { base: 'base_spaces', space: '0.17em' },
    '\\negthinspace': { base: 'base_spaces', space: '0em' }, // negative thin (-0.17em)
    '\\negthickspace': { base: 'base_spaces', space: '0em' },

    // phantom spaces
    '\\vphantom': { args: 1, arg0_as_main: 1, text: '', mainclass: 'mee_vphantom' },
    '\\hphantom': { args: 1, arg0_as_main: 1, text: '', mainclass: 'mee_phantom', noheight: 1 },
    '\\phantom': { args: 1, arg0_as_main: 1, text: '', mainclass: 'mee_phantom' },
    //#endregion

    //#region special commands - fractions and binomials (stuff with upper and lower)
    '\\frac': { args: 2, text: '', frac: 1, arg01_as_upperlower: 1, bar: 1, evenpos: 1, upperclass: 'mee_frac_upper', lowerclass: 'mee_frac_lower', noautofrac: 1, bartype: 'single' },
    '\\cfrac': { base: '\\frac', displaystyle: 1 },
    '\\dfrac': { base: '\\frac', displaystyle: 1 },
    '\\tfrac': { base: '\\frac', textstyle: 1 }, // should be much smaller
    '\\binom': { base: '\\frac', lb: '(', rb: ')', bar: 0, size: -1 }, // this should have ( ) around it
    '\\over': { all_as_arg01: 1, frac: 1, base: '\\frac' },
    '\\choose': { all_as_arg01: 1, frac: 1, base: '\\frac', lb: '(', rb: ')', bar: 0, size: -1 },

    //#endregion

    //#region Chemistry
    '\\ce': { args: 1, arg0_as_main: 1, simplemain: 1, elemclass: 'mee_font_rm' },

    'base_ce_relation': { elemclass: 'mee_ce_relation', noautofrac: 1 },
    '\\<=>': { base: 'base_ce_relation', text: '&nbsp;&#xE907;&nbsp;' },
    '\\<=>>': { base: 'base_ce_relation', text: '&nbsp;&#xE900;&nbsp;' },
    '\\<->': { base: 'base_ce_relation', text: '&nbsp;&#x27F7;&nbsp;' },
    '\\->': { base: 'base_ce_relation', text: '&nbsp;&#x27F6;&nbsp;' },
    '\\<-': { base: 'base_ce_relation', text: '&nbsp;&#x27F5;&nbsp;' },

    // bonds
    '\\sbond': { text: '&#x2212;', noautofrac: 1 },
    '\\dbond': { text: '=' },
    '\\tbond': { text: '&#x2261;' },

    '\\isotope': { text: '&#x200b;' },

    '\\bond': { object: 'Bond', text: '', args: 1, arg0_as_main: 1, noautofrac: 1 },
    //#endregion

    //#region mods
    '\\bmod': { text: '&nbsp;&nbsp;mod&nbsp;&nbsp;' },
    '\\pmod': { text: 'mod&nbsp;&nbsp;', lb: '(', rb: ')', args: 1, next_as_arg0: 1, elemclass: 'mee_pmod' },
    '\\mod': { text: 'mod&nbsp;', elemclass: 'mee_pmod' },
    '\\pod': { text: '', lb: '(', rb: ')', args: 1, next_as_arg0: 1, elemclass: 'mee_pmod', size: -1 },
    //#endregion

    //#region roots
    '\\sqrt': { args: 1, sarg: 1, sarg_as_sup: 1, arg0_as_main: 1, lb: '&#x221A;', limits: 'sqrt', superscriptclass: 'mee_sqrt_super', size: -1 },
    //#endregion

    //#region text stuff
    '\\text': { args: 1, elemclass: 'mee_font_rm', arg0_as_main: 1, simplemain: 1, parseinner: 1, allowspaces: 1 },
    '\\mbox': { base: '\\text', elemclass: 'mee_font_rm' },
    '\\textrm': { base: '\\text', elemclass: 'mee_font_rm' },
    '\\textit': { base: '\\text', elemclass: 'mee_font_it' },
    '\\textbf': { base: '\\text', elemclass: 'mee_font_bf_txt' },


    // formatting
    '\\boxed': { object: 'Boxed', args: 1, text: '', arg0_as_main: 1 },


    // fonts
    'base_font': { args: 1, arg0_as_main: 1, simplemain: 1 },
    '\\mathcal': { base: 'base_font', elemclass: 'mee_font_cal' },
    '\\cal': { base: 'base_font', elemclass: 'mee_font_cal', rest_as_arg0: 1 },
    '\\mathscr': { base: 'base_font', elemclass: 'mee_font_scr' },
    '\\scr': { base: 'base_font', elemclass: 'mee_font_scr', rest_as_arg0: 1 },
    '\\mathrm': { base: 'base_font', elemclass: 'mee_font_rm' },
    '\\rm': { base: 'base_font', elemclass: 'mee_font_rm', rest_as_arg0: 1 },
    '\\mathbf': { base: 'base_font', elemclass: 'mee_font_bf' },
    '\\bf': { base: 'base_font', elemclass: 'mee_font_bf', rest_as_arg0: 1 },
    '\\mathbb': { base: 'base_font', elemclass: 'mee_font_bb' },
    '\\Bbb': { base: 'base_font', elemclass: 'mee_font_bb' },
    '\\bbFont': { base: 'base_font', elemclass: 'mee_font_bb', rest_as_arg0: 1 },
    '\\mathit': { base: 'base_font', elemclass: 'mee_font_it' },
    '\\mit': { base: 'base_font', elemclass: 'mee_font_it' },
    '\\it': { base: 'base_font', elemclass: 'mee_font_it', rest_as_arg0: 1 },
    '\\mathfrak': { base: 'base_font', elemclass: 'mee_font_frak' },
    '\\frak': { base: 'base_font', elemclass: 'mee_font_frak', rest_as_arg0: 1 },
    '\\mathsf': { base: 'base_font', elemclass: 'mee_font_sf' },
    '\\sf': { base: 'base_font', elemclass: 'mee_font_sf', rest_as_arg0: 1 },
    '\\mathtt': { base: 'base_font', elemclass: 'mee_font_tt' },
    '\\tt': { base: 'base_font', elemclass: 'mee_font_tt', rest_as_arg0: 1 },
    '\\oldstyle': { base: 'base_font', elemclass: 'mee_font_oldstyle', rest_as_arg0: 1 },
    '\\boldsymbol': { base: 'base_font', elemclass: 'mee_font_bold' },
    '\\pmb': { base: 'base_font', elemclass: 'mee_font_bold' },
    //#endregion

    //#region brackets (pairs)
    'base_brackets': { args: 1, arg0_as_main: 1, changetype: 'extpair' /*, noautofrac: 1*/ },
    '\\pbrackets': { base: 'base_brackets', lb: '(', rb: ')' },
    '\\pdotbrackets': { base: 'base_brackets', lb: '', rb: '' }, // for when there are matching brackets that are sized but only want to display one of em
    '\\psqbrackets': { base: 'base_brackets', lb: '[', rb: ']' },
    '\\pbrace': { base: 'base_brackets', lb: '{', rb: '}' },
    '\\pvert': { base: 'base_brackets', lb: '|', rb: '|' },
    '\\pVert': { base: 'base_brackets', lb: '&#x2225;', rb: '&#x2225;' },
    '\\pangle': { base: 'base_brackets', lb: '&#x27E8;', rb: '&#x27E9;' },
    '\\pceil': { base: 'base_brackets', lb: '&#x2308;', rb: '&#x2309;' },
    '\\pfloor': { base: 'base_brackets', lb: '&#x230A;', rb: '&#x230B;' },
    '\\pgroup': { base: 'base_brackets', lb: '&#x27EE;', rb: '&#x27EF;' },
    '\\pmoustache': { base: 'base_brackets', lb: '&#x23B0;', rb: '&#x23B1;' },


    // brackets (left and right)
    'base_brackets_single': { noautofrac: 1 },
    '(': { base: 'base_brackets_single', text: '(' },
    ')': { base: 'base_brackets_single', text: ')' },
    '{': { base: 'base_brackets_single', text: '{' },
    '}': { base: 'base_brackets_single', text: '}' },
    '\\lbrace': { base: 'base_brackets_single', text: '{' },
    '\\rbrace': { base: 'base_brackets_single', text: '}' },
    '\\lvert': { base: 'base_brackets_single', text: '|' },
    '\\rvert': { base: 'base_brackets_single', text: '|' },
    '\\lVert': { base: 'base_brackets_single', text: '&#x2225;' },
    '\\rVert': { base: 'base_brackets_single', text: '&#x2225;' },
    '\\|': { base: 'base_brackets_single', text: '&#x2225;' },
    '\\langle': { base: 'base_brackets_single', text: '&#x27E8;' },
    '\\rangle': { base: 'base_brackets_single', text: '&#x27E9;' },
    '\\lceil': { base: 'base_brackets_single', text: '&#x2308;' },
    '\\rceil': { base: 'base_brackets_single', text: '&#x2309;' },
    '\\lfloor': { base: 'base_brackets_single', text: '&#x230A;' },
    '\\rfloor': { base: 'base_brackets_single', text: '&#x230B;' },
    '\\lgroup': { base: 'base_brackets_single', text: '&#x27EE;' },
    '\\rgroup': { base: 'base_brackets_single', text: '&#x27EF;' },
    '\\lmoustache': { base: 'base_brackets_single', text: '&#x23B0;' },
    '\\rmoustache': { base: 'base_brackets_single', text: '&#x23B1;' },


    // brackets (non pairing)
    'base_brackets_nonpair': { noautofrac: 1 },
    '\\vert': { base: 'base_brackets_nonpair', text: '|', scale: 1 },
    '\\Vert': { base: 'base_brackets_nonpair', text: '&#x2225;', scale: 1 },
    '/': { base: 'base_brackets_nonpair', text: '/' },
    '\\backslash': { base: 'base_brackets_nonpair', text: '\\' },
    '\\arrowvert': { base: 'base_brackets_nonpair', text: '|' },
    '\\Arrowvert': { base: 'base_brackets_nonpair', text: '&#x2225;' },
    '\\bracevert': { base: 'base_brackets_nonpair', text: '&#x23AA;' }, // WRONG
    //#endregion


    //#region arrows (extensible)
    'base_arrows_ext': {},
    '\\uparrow': { base: 'base_arrows_ext', text: '&#x2191;' },
    '\\Uparrow': { base: 'base_arrows_ext', text: '&#x21D1;' },
    '\\downarrow': { base: 'base_arrows_ext', text: '&#x2193;' },
    '\\Downarrow': { base: 'base_arrows_ext', text: '&#x21D3;' },
    '\\updownarrow': { base: 'base_arrows_ext', text: '&#x2195;' },
    '\\Updownarrow': { base: 'base_arrows_ext', text: '&#x21D5;' },

    // horizontal arrows
    'base_arrow': { args: 1, sarg: 1, text: '', frac: 1, arg0_as_upper: 1, sarg_as_lower: 1, bar: 1, evenpos: 1, noautofrac: 1, bartype: 'single', extradepth: 1 },
    '\\xleftarrow': { base: 'base_arrow', lend: '&#x2190;' },
    '\\xrightarrow': { base: 'base_arrow', rend: '&#x2192;' },
    '\\xleftrightarrow': { base: 'base_arrow', lend: '&#x2190;', rend: '&#x2192;' },
    '\\xLeftarrow': { base: 'base_arrow', lend: '&#x21D0;', bartype: 'doubleL' },
    '\\xRightarrow': { base: 'base_arrow', rend: '&#x21D2;', bartype: 'doubleL' },
    '\\xLeftrightarrow': { base: 'base_arrow', lend: '&#x21D0;', rend: '&#x21D2;', bartype: 'doubleL' },
    '\\xhookleftarrow': { base: 'base_arrow', lend: '&#x2190;', rend: '&#xE150;' },
    '\\xhookrightarrow': { base: 'base_arrow', lend: '&#xE151;', rend: '&#x2192;' },
    '\\xmapsto': { base: 'base_arrow', lend: '&#xE152;', rend: '&#x2192;' },

    '\\xrightharpoondown': { base: 'base_arrow', rend: '&#x21C1;' },
    '\\xrightharpoonup': { base: 'base_arrow', rend: '&#x21C0;' },
    '\\xleftharpoondown': { base: 'base_arrow', lend: '&#x21BD;' },
    '\\xleftharpoonup': { base: 'base_arrow', lend: '&#x21BC;' },
    '\\xrightleftharpoons': { base: 'base_arrow', lend: '&#xE153;', rend: '&#xE155;', bartype: 'double' },
    '\\xleftrightharpoons': { base: 'base_arrow', lend: '&#xE154;', rend: '&#xE156;', bartype: 'double' },
    //#endregion

    //#region matrix
    'base_matrix': { align: 'c', rowclass: 'mee_matrix_row', colclass: 'mee_matrix_col', size: -1, padding: 0.25, inmatrix: 1, noautofrac: 1 },
    '\\matrix': { base: 'base_matrix', nodepth: 1 },
    '\\pmatrix': { base: 'base_matrix', lb: '(', rb: ')' },
    '\\bmatrix': { base: 'base_matrix', lb: '[', rb: ']' },
    '\\Bmatrix': { base: 'base_matrix', lb: '{', rb: '}' },
    '\\vmatrix': { base: 'base_matrix', lb: '|', rb: '|' },
    '\\Vmatrix': { base: 'base_matrix', lb: '&#x2225;', rb: '&#x2225;' },
    '\\matrix*': { base: 'base_matrix', custalign: 1 },

    // substack acts like a matrix with no brackets
    '\\substack': { args: 1, arg0_as_array: 1, text: '', nodepth: 1, inmatrix: 1 },

    // matrix lines
    '\\hline': { apply_to_parent: { hline: 1} },
    //#endregion

    //#region alignments (similar to matrix)
    'base_align': { nodepth: 1, padding: 0.25, inmatrix: 1 },
    '\\aligned': { base: 'base_align', align: 'rlrlrlrlrl' },
    '\\align': { base: 'base_align', align: 'rlrlrlrlrl' },
    '\\align*': { base: 'base_align', align: 'rlrlrlrlrl' },
    '\\split': { base: 'base_align', align: 'rl' },
    '\\split*': { base: 'base_align', align: 'rl', custalign: 1 },
    '\\gather': { base: 'base_align', align: 'c' },
    '\\gather*': { base: 'base_align', align: 'c', custalign: 1 },
    '\\multline': { base: 'base_align', align: 'c' },
    '\\multline*': { base: 'base_align', align: 'c', custalign: 1 },
    '\\cases': { base: 'base_align', align: 'lr', lb: '{', size: -1 },
    '\\cases*': { base: 'base_align', align: 'lr', lb: '{', custalign: 1 },
    '\\equation': { base: 'base_align', nosplit: 1 },
    '\\equation*': { base: 'base_align', nosplit: 1 },
    '\\array': { base: 'base_align', custalign: 1, align: 'l' },
    '\\array*': { base: 'base_align', custalign: 1 },
    '\\tabular': { base: 'base_align', custalign: 1 },
    '\\eqnarray*': { base: 'base_align', align: 'rcl*' },
    //#endregion

    //#region Random stuff
    '\\overset': { args: 2, arg0_as_super: 1, arg1_as_main: 1, text: '', limits: 'above' },
    '\\underset': { args: 2, arg0_as_sub: 1, arg1_as_main: 1, text: '', limits: 'above' },
    '\\stackrel': { args: 2, arg0_as_super: 1, arg1_as_main: 1, text: '', limits: 'above' },
    //#endregion

    //#region sizing operators
    '\\displaystyle': { apply_to_thisset: { displaystyle: 1} },
    '\\textstyle': { apply_to_thisset: { textstyle: 1} },
    //#endregion

    //#region BROKEN FROM HERE ONWARDS
    '\\mathop': { text: '', limits: 'above' },
    '\\nonumber': { text: '' },
    '\\sideset': { text: '' },
    '\\hfill': { text: '' },
    '\\underbrace': { text: '' },
    '\\overbrace': { text: '' },
    '\\underbracket': { text: '' },
    '\\overbracket': { text: '' },
    '\\displaybreak': { text: '' },
    '\\intertext': { text: '' },
    '\\overline': { text: '' },
    '\\kern': { text: '' }
    //#endregion
};

MEE.Data.buildDefs();