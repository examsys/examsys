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
// Initialise MRQ exam page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['questionstartmrq', 'jquery'], function (MRQ, $) {
    var mrq = new MRQ();
    $('.online').click(function() {
        mrq.MRQ($(this).attr('data-qno'), $(this).attr('data-pos'), $(this).attr('data-ono'), $(this).attr('data-allowed'));
    });

    $('.abstain').click(function() {
        mrq.MRQabstain($(this).attr('data-qno'), $(this).attr('data-ono'));
    });
});