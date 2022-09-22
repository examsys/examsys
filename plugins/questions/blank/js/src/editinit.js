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
// Initialise fill in the blank edit page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['jqueryblank', 'questioneditblank', 'jquery'], function (BLANK, ADDEDIT, $) {
    var blank = new BLANK();
    blank.init();
    var addedit = new ADDEDIT();
    $('.blank-display').change(function() {
        addedit.updateBlankInstructions(this);
    });
});