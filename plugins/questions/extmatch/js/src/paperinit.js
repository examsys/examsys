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
// Initialise exact match exam page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['questionstartextmatch', 'jquery'], function (EXT, $) {
    var ext = new EXT();
    $('.multicheck').change(function() {
        ext.multimatchingCheck($(this).attr('data-qno'), $(this).attr('data-opt'), $(this).attr('data-sub'));
    });
});