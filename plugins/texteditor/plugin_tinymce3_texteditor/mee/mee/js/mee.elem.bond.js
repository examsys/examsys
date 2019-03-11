
// handles boxed stuff
MEE.Elem.extend("MEE.ElemBond",
{
    toHTML: function (depth) {
        var res = this._super(depth);
        var bond = this.html_main.html();

        var bondtext = "";

        if (this.main) {
            var latex = this.main.latex;
            while (latex.indexOf(' ') != -1)
                latex = latex.replace(' ', '');

            bondtext = MEE.Data.bonds[latex];
            if (!bondtext) {
                bondtext = this.main.latex;
            }
        }

        this.html_main.html(bondtext);
        return res;
    },

    sortAlign: function () {
        var res = this._super();
        return res;
    },

    SortBondInner: function () {
        var latex = this.html_main.text();
        while (latex.indexOf(' ') != -1)
            latex = latex.replace(' ', '');
        while (latex.indexOf("\u2212") != -1)
            latex = latex.replace("\u2212", '-');


        if (latex.charCodeAt(latex.length - 1) == 8203) {
            latex = latex.substr(0, latex.length - 1);
        }

        var bondtext = MEE.Data.bonds[latex];
        if (bondtext) {
            this.html_main.html(bondtext);
        }
        /*
        for (bond in MEE.Data.bonds) {
            if (bond.length != latex.length)
                continue;

            var found = true;

            for (var i = 0; i < bond.length; i++) {
                if (bond.charCodeAt(i) != latex.charCodeAt(i)) {
                    found = false;
                    break;
                }
            }

            if (found) {
                bondtext = MEE.Data.bonds[latex];
                if (bondtext) {
                    this.html_main.html(bondtext);
                }
            }
        }*/
    }
});
