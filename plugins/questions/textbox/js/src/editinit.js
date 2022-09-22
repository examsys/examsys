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
// Initialise mcq edit page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['jquerytextbox', 'jsxls', 'jquery'], function (Text, Jsxls, $) {
    var text = new Text();
    text.init();
    $('#addterm').click(function () {
        var disclass = $('#termattrs').attr('data-disclass');
        var readonly = $('#termattrs').attr('data-readonly');
        var termscount = $('.terms').length + 1;
        var rowclass = "class='spaced-top";
        if (termscount % 2 == 0) {
            rowclass += " alt'";
        } else {
            rowclass += "'";
        }
        $('#termscontainer').append('<tr ' + rowclass + '>' +
            '<th><label id="terms' + termscount + 'label" for="terms' + termscount + '">' + Jsxls.lang_string['termno'] + termscount + '</label></th>' +
            '<td><input id="terms' + termscount + '" name="terms' + termscount + '" type="text" class="terms form-med-large' + disclass + readonly + '" value=""/></td>' +
            '</tr>');

        if ($('.terms').length === 20) {
            $('#addterm').prop("disabled", true);
        }
    });
    $('#edit_form').submit(function () {
        var termsarray = new Array();
        $('.terms').each(function () {
            if ($(this).val() !== '') {
                termsarray.push($(this).val());
            }
        });
        $('#terms').val(JSON.stringify(termsarray));
        return true;
    });
});
