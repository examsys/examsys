// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.
//
// Mathjax preview module
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
// A modularised version of the example from https://www.mathjax.org/

define(['mathjax'], function (MathJax) {
    /**
     * Preview mathajx entered into a textarea
     * @param id textarea identifier
     */
    return function(id) {
        this.id = id;
        this.delay = 150;        // delay after keystroke before updating
        this.preview = null;     // filled in by Init below
        this.buffer = null;      // filled in by Init below
        this.timeout = null;     // store setTimout id
        this.mjRunning = false;  // true when MathJax is processing
        this.mjPending = false;  // true when a typeset has been queued
        this.oldText = null;     // used to check if an update is needed
        //
        //  Get the preview and buffer DIV's
        //
        this.Init = function () {
            this.preview = document.getElementById("MathPreview" + this.id);
            this.buffer = document.getElementById("MathBuffer" + this.id);
        }
        //
        //  Switch the buffer and preview, and display the right one.
        //  (We use visibility:hidden rather than display:none since
        //  the results of running MathJax are more accurate that way.)
        //
        this.SwapBuffers = function () {
            var buffer = this.preview, preview = this.buffer;
            this.buffer = buffer; this.preview = preview;
            buffer.style.visibility = "hidden"; buffer.style.position = "absolute";
            preview.style.position = ""; preview.style.visibility = "";
        }
        //
        //  This gets called when a key is pressed in the textarea.
        //  We check if there is already a pending update and clear it if so.
        //  Then set up an update to occur after a small delay (so if more keys
        //    are pressed, the update won't occur until after there has been
        //    a pause in the typing).
        //  The callback function is set up below, after the Preview object is set up.
        //
        this.Update = function () {
            if (this.timeout) {clearTimeout(this.timeout)}
            this.timeout = setTimeout(this.callback,this.delay);
        }
        //
        //  Creates the preview and runs MathJax on it.
        //  If MathJax is already trying to render the code, return
        //  If the text hasn't changed, return
        //  Otherwise, indicate that MathJax is running, and start the
        //    typesetting.  After it is done, call PreviewDone.
        //
        this.CreatePreview = function () {
            this.timeout = null;
            if (this.mjPending) return;
            var text = document.getElementById("q" + this.id).value;
            if (text === this.oldtext) return;
            if (this.mjRunning) {
                this.mjPending = true;
                MathJax.Hub.Queue(["CreatePreview",this]);
            } else {
                this.buffer.innerHTML = this.oldtext = text;
                this.mjRunning = true;
                MathJax.Hub.Queue(
                    ["Typeset",MathJax.Hub,this.buffer],
                    ["PreviewDone",this]
                );
            }
        }
        //
        //  Indicate that MathJax is no longer running,
        //  and swap the buffers to show the results.
        //
        this.PreviewDone = function () {
            this.mjRunning = this.mjPending = false;
            this.SwapBuffers();
        }
        //
        //  Cache a callback to the CreatePreview action
        //
        this.callback = MathJax.Callback(["CreatePreview",this]);
        this.callback.autoReset = true;  // make sure it can run more than once
    }
});


