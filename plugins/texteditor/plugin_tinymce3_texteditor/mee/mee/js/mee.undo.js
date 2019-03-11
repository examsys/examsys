$.Class.extend("MEE.Undo",
{
    init: function (initial) {
        MEE_Undo = this;
        this.items = new Array();
        if (initial) {
            this.items.push(initial);
            this.position = 0;
        } else {
            this.position = -1;
        }
    },

    Add: function (item) {
        if (this.position != this.items.length - 1) {
            this.items = this.items.splice(0, this.position + 1);
        }
        this.items.push(item);
        this.position = this.items.length - 1;
    },

    CurrentUndo: function () {
        if (!this.canUndo())
            return null;

        var item = this.items[this.position];

        return item;

    },

    Undo: function () {
        if (!this.canUndo())
            return null;

        var item = this.items[this.position - 1];
        this.position--;

        return item;
    },

    Redo: function () {
        if (!this.canRedo())
            return null;

        var item = this.items[this.position + 1];
        this.position++;

        return item;
    },

    canUndo: function () {
        if (this.items.length < 2)
            return false;

        if (this.position == 0)
            return false;

        return true;
    },

    canRedo: function () {
        if (this.items.length < 2)
            return false;

        if (this.position == this.items.length - 1)
            return false;

        return true;
    },

    dump: function () {
        var res = "<pre>";
        res += "Position : " + this.position + "\n";
        for (var i = 0; i < this.items.length; i++) {
            res += i + ": " + this.items[i].latex + "\n";
        }
        res += "</pre>";
        return res;
    }
});
