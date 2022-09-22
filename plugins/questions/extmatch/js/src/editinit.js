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
// Initialise exact match edit page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['jqueryextmatch', 'questioneditextmatch', 'jquery'], function (EXT, ADDEDIT, $) {
    var ext = new EXT();
    ext.init();
    var addedit = new ADDEDIT();
    $('.extmatch-option').blur(function () {
        addedit.updateExtMatchOptions(this);
    });
    // Bit of a hack to get the options section to fit in.
    var extraWidth = 0;
    var img = $('#media0 img:first');
    if (img.length == 1) {
        if (img.width() > 820) {
            extraWidth = img.width() - 820;
        }
    }
    var qh = $('#question-holder');
    qh.addClass('wide');
    if (extraWidth > 0) {
        qh.width(qh.width() + extraWidth);
    }
});