// base class for edit and display
$.Class.extend("MEE.Main",
{
// static stuff, keep track of all instances of the main classes here

},
{
    // main class for dealing with a equation editor element
    init: function (element) {
        
    }
});

$.Class.extend("MEE.Latex",
{
    init: function () {
        this.count = 0;
        this.latex = "";
    },
    AddText: function (string) {
        this.latex += string + " ";
        this.count++;
    },
    AddMod: function (string) {
        this.latex += string;
    },
    AddSet: function (more) {
        if (more.count > 1) {
            this.latex += "{" + more.latex + "}";
        } else {
            this.latex += more.latex;
        }
        this.count += more.count;
    },
    AddArg: function (more) {
        this.latex += "{" + more.latex + "}";
        this.count++;
    },
    AddElem: function (more) {
        this.latex += more.latex + " ";
        this.count += more.count;
    },
    get: function () {
        return this.latex;
    }
});