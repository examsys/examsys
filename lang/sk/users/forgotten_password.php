<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

$string['forgottenpassword'] = 'Zabudnuté heslo';
$string['emailaddress'] = 'E-mailová adresa';
$string['emailaddressinvalid'] = 'Zadajte, prosím, platnú e-mailovú adresu';
$string['emailaddressnotfound'] = 'E-mailová adresa nenájdená';
$string['emailaddressininstitutionaldomains'] = 'Váš účet je vedený v inštitúcii, ktorá je riadena pomocou centrálnej autentizačnej služby. Kontaktujte, prosím, IT podporu pre postup pri obnovení hesla.';
$string['passwordreset'] = 'Obnovenie hesla';
$string['emailhtml'] = <<< EMAIL_HTML
<p>Vážený/á %s %s,</p>
<p>Obdržali sme žiadosť o obnovenie hesla na  Rog&#333;. Ak chcete požiadavku dokončiť, kliknite na odkaz uvedený nižšie:</p>
<p><a href="https://%s/users/reset_password.php?token=%s">Obnovenie hesla</a></p>
<p>Ak ste o obnovenie hesla nežiadali: <a href="mailto:%s">napíšte nám</a>. Vaše existujúce používateľské meno a heslo Vám aj napriek tomu umožní prihlásenie do Rog&#333;.</p>

EMAIL_HTML;
$string['couldntsendemail'] = 'Nie je možné odoslať e-mail <strong>%s</strong>';
$string['emailsentmsg'] = 'Bol odoslaný e-mail na <em>%s</em> obsahujúci odkaz, ktorý Vám umožní heslo obnoviť. Tento odkaz bude platný len <strong>24 hodín</strong>.';
$string['intromsg'] = 'Zadajte svoju e-mailovú adresu a my Vám zašleme e-mail, ktorý Vám umožní heslo obnoviť.';
$string['send'] = 'Odoslať';
?>