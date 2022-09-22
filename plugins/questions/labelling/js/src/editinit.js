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
// Initialise labelling edit page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['qlabelling', 'jqueryleadinonly', 'jquery'], function (qlabelling, LEADIN, $) {
    var leadin = new LEADIN();
    leadin.init();

    var language = $('#dataset').attr('data-language');
    $("canvas[id^=canvas]").each(function() {
       var label = new qlabelling();
       label.setUpLabelling($(this).attr('data-qno'),
            "flash" + $(this).attr('data-qno'),
            language, $(this).attr('data-qmedia'),
            $(this).attr('data-qcorrect'), $(this).attr('data-user'), $(this).attr('data-marking'), "#FFC0C0", "edit");

    });
});