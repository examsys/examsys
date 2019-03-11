$.Class.extend("MEE.SymHist",
{
    init: function () {
        this.items = {};

        this.sets = 5;
        this.perset = 3;

        this.max = this.sets * this.perset;

        for (var i = 0; i < this.sets; i++)
            this.LoadFromCookie("symhist" + i);

        //this.SortItems();
    },

    LoadFromCookie: function (name) {
        var ser = $.cookie(name);
        var items = $.parseJSON(ser);

        if (!items)
            return;

        for (var i = 0; i < items.length; i++)
            this.items[items[i].latex] = items[i];
    },

    SaveToCookie: function (name, item) {
        var ser = JSON.stringify(item);
        $.cookie(name, ser, { expires: 30 });
    },

    Add: function (item) {
        var i = $(item);
        var hist = {
          text : i.data('text'),
          latex : i.data('latex'),
          _class : i.data('class'),
          when : new Date().getTime()
        };
        this.items[hist.latex] = hist;

        this.SortItems();
    },

    SortItems: function () {
        var a = new Array();

        $.each(this.items, function (key, item) {
            a.push(item);
        });

        a.sort(function (l, r) {
            return l.when < r.when;
        });

        this.items = {};

        var output = new Array();
        for (var i = 0; i < this.sets; i++)
            output[i] = new Array();

        var count = Math.min(this.max, a.length);
        for (var i = 0; i < count; i++) {
            if (!a[i])
                continue;

            this.items[a[i].latex] = a[i];

            var set = i % this.perset;

            output[set].push(a[i]);
        }

        for (var i = 0; i < this.sets; i++)
            this.SaveToCookie("symhist" + i, output[i]);

        this.SortToolbar();
        var k = 0;
    },

    SortToolbar: function () {
        var tbpane = $('#mt_pane_elem_recentsym');
        tbpane.children().remove();

        var count = 0;

        var a = new Array();

        $.each(this.items, function (key, item) {
            a.push(item);
        });

        a.sort(function (a, b) {
            return ((a.latex.toLowerCase() == b.latex.toLowerCase()) ? 0 : ((a.latex.toLowerCase() > b.latex.toLowerCase()) ? 1 : -1));
        });

        for (var i = 0; i < a.length; i++) {
            var obj = a[i];

            var div = $('<div>');
            div.addClass('tb_symbol');
            div.addClass('tb_recent');
            if (obj._class)
                div.addClass(obj._class);
            div.data('latex', obj.latex);

            var link = $('<a>');
            link.html(obj.text);

            div.append(link);

            tbpane.append(div);

            div.css('width', '22px');
            div.css('height', '22px');

            div.click(this.callback('itemClick'));

            count++;
        }
        MEE.Edit.toolbar.setMouseImages($('.tb_recent'));
        if (tbpane[0]) {
            var parent = tbpane[0].parentNode;

            if (count < 4) count = 4;
            var width = Math.ceil(count / 3) * 23;
            $(parent).css('width', width + 'px');
            $(parent).children().css('width', width + 'px');
        }
    },

    itemClick: function (item) {
        if (MEE.Edit.toolbar.currentEdit == null)
            return true;

        var latex = $(item).data('latex');
        if (latex) {
            MEE.Edit.toolbar.currentEdit.toolbarCommand(latex, item);
        }

        return true;
    }
});
