<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

$string['forgottenpassword'] = 'Utracone hasło';
$string['emailaddress'] = 'Adres Email';
$string['emailaddressinvalid'] = 'Podaj poprawny adres Email';
$string['emailaddressnotfound'] = 'Nie znaleziono tego adresu Email';
$string['emailaddressininstitutionaldomains'] = 'Twoje konto jest z instytucji, która zarządzana jest z użyciem centralnego systemu uwierzytelniania. Skontaktuj się z zespołem wsparcia IT w celu uzskania informacji o procedurach resetowania hasła.';
$string['passwordreset'] = 'Resetowanie hasła';
$string['emailhtml'] = <<< EMAIL_HTML
<p>Cześć %s %s,</p>
<p>Otrzymaliśmy polecenie zmiany hasła w Rog&#333;. Aby potwierdzić to polecenie kliknij na poniższy link:</p>
<p><a href="https://%s/users/reset_password.php?token=%s">Resetowanie hasła</a></p>
<p>Jeśli nie wnioskowałeś/łaś o resetowanie hasła prosimy abyś do nas o tym <a href="mailto:%s">napisał/a</a>. Twój dotychczasowy login i hasło będą w Rog&#333; nadal obowiązywały.</p>

EMAIL_HTML;
$string['couldntsendemail'] = 'Nie można było wysłać Emaila do <strong>%s</strong>';
$string['emailsentmsg'] = 'Wysłany został Email na <em>%s</em> zawierający link umożliwiający zresetowanie Twojego hasła. Link ten pozostanie aktywny przez <strong>24 godziny</strong>.';
$string['intromsg'] = 'Podaj swój adres Email, a my wyślemy tam link umożliwiający zresetowanie hasła.';
$string['send'] = 'Wyślij';
?>