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

include 'lti_common.php';

$string['NoPapers'] = 'K tomuto modulu nie sú dostupné žiadne dokumenty';
$string['NoPapersDesc'] = 'K tomuto modulu nie sú dostupné žiadne dokumenty. Pravdepodobne je to spôsobené tým, že ste vytvorili nový odkaz z VLE z nového modulu, a preto nemáte v súčasnosti nakonfigurované žiadne dokumety.<br /><br />Pre vytvorenie dokumentu vyberte, prosím <a href="../" target="_blank">spustiť Rogo</a>'; //zavřete prohlížeč (<strong>velice dùležité</strong>) následně přejděte na domovskou stránku Rogo a dokument vytvořte.

$string['NoModCreateTitle'] = 'Vytvorenie nového modulu nie je povolené';
$string['NoModCreate'] = 'Tvorba modulu z LTI nie je povolená v konfigurácii, preto nie je možné vytvoriť modul s kódom kurzu: ';
$string['NotAddedToModuleTitle'] = 'Pridanie do tímu modulu nebolo úspešné';
$string['NotAddedToModule'] = 'Pridávanie do tímu modulu nie je v LTI konfigurácii povolené, táto závada sa vyskytla v module: ';

$string['NoModCreateTitle2'] = 'Vytváranie modulu nebeží';
$string['NoModCreate2'] = 'Tvorba modulu z LTI nebeží, keďže používateľ nevlastní oprávnenie, a preto nie je možné vytvoriť modul s kódom kurzu: ';
$string['moduletranslateerror'] = 'Module code error';
$string['moduletranslatemessage'] = 'There is a problem with the module code as the translation code has resulted in an error. Please contact Learning Team Support <a href="mailto:%s">%s</a>';
$string['moduletranslatecode'] = '<p>Incoming Module Code: %s</p>';
$string['modulecreateerror'] = 'Module creation error';
$string['modulecreatemessage'] = 'Modules cannot be created that do not exist within the student management system. For further assistance contact: <a href="mailto:%s">%s</a>';
?>
