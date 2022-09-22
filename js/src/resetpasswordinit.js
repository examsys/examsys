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
// Initialise reset password.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['jsxls', 'jquery', 'jqueryvalidate'], function (jsxls, $) {
    $('#forgotten_pw').validate({
        rules: {
            password_confirm: {
                required: true,
                equalTo: "#password"
            }
        },
        messages: {
            email: jsxls.lang_string['emailaddressinvalid'],
            password: jsxls.lang_string['pleaseenterpassword'],
            password_confirm: {
                required: jsxls.lang_string['pleaseconfirmpassword'],
                equalTo: jsxls.lang_string['passwordsnotmatch']
            }
        }
    });
});