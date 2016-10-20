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

require '../lang/' . $language . '/include/paper_options.inc';
require '../lang/' . $language . '/include/months.inc';
require '../lang/' . $language . '/paper/new_paper2.php';

$string['importoscemarks'] = 'Nahrať známky z OSCE stanice';
$string['marksloaded'] = 'Známky nahrané.';
$string['csvfile'] = 'CSV súbor:';
$string['topmsg'] = 'Toto pole je určené k hromadnému nahratiu známok papierových OSCE. Použitý CSV súbor by mal byť v následujúcom formáte:';
$string['headerrow'] = 'Súbor obsahuje riadok so záhlavím';
$string['import'] = 'Nahrať';
$string['usernotfound'] = 'Používateľ nenájdený!';
$string['saveerror'] = 'Chyba pri ukladaní používateľských údajov %s';
$string['gradedmsg'] = 'Import failed. This OSCE station has already been graded.';
?>