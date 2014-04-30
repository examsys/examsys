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

$string['company'] = 'Společnost';
$string['companyname'] = 'Jméno společnosti';
$string['databaseadminuser'] = 'Administrátor databáze';
$string['server'] = 'Server'; //cognate
$string['tempdirectory'] = 'Dočasný adresář';
$string['needusername'] = 'Pro vytvoření databáze a potřebné tabulky, potřebujete uživatelské jméno a heslo od administrátora MySQL . Toto uživatelské jméno je použito pouze v instalačním skriptu a není na serveru uloženo.';
$string['dbusername'] = 'Databázové uživatelské jméno';
$string['dbpassword'] = 'Databázové heslo';
$string['databasesetup'] = 'Nastavení databáze';
$string['databasehost'] = 'Hostitel databáze';
$string['databaseport'] = 'Port databáze';
$string['databasename'] = 'Název databáze';
$string['databasecharset'] = 'Znaková sada databáze';
$string['databaseuser'] = 'Uživatel Rogo databáze';
$string['pagecharset'] = 'Znaková sada stránek';
$string['rdbusername'] = 'Uživatelské jméno';
$string['rdbpassword'] = 'Heslo';
$string['timedateformats'] = 'Čas/Datum - formáty';

$string['date'] = 'Datum';
$string['datetime'] = 'Datum/Čas';
$string['longdatephp'] = 'Nezkrácené datum (%s)';
$string['shortdatephp'] = 'Zkrácené datum (%s)';
$string['longtimephp'] = 'Nezkrácený čas (%s)';
$string['shorttimephp'] = 'Zkrácený čas (%s)';
$string['currenttimezone'] = 'Aktuální časová zóna';
$string['authentication'] = 'Ověřování';

$string['allowlti'] = 'Povolit LTI (lineární časová neměnná';
$string['allowintdb'] = 'Vnitřní databáze';
$string['allowguest'] = 'Dočasné účty (pro sumativní zkoušky)';
$string['allowimpersonation'] = 'Impersonifikace uživatele (pouze SysAdmin)';

$string['lookup'] = 'Vyhledat zdroje dat';
$string['allowlookupXML'] = 'Použít XML (Značkovací jazyk, bude potřeba přizpůsobení v konfiguračním souboru)';
$string['rdbbasename'] = 'Základní část uživatelského jména';

$string['useldap'] = 'Použít LDAP';
$string['ldapserver'] = 'LDAP server'; //cognate
$string['searchdn'] = 'Hledat dn';
$string['bindusername'] = 'Svázat uživatelské jméno';
$string['bindpassword'] = 'Přiřadit heslo';
$string['userprefix'] = 'Prefix uživatelského jména';
$string['userprefixtip'] = 'Prefix uživatelského jména pro hledání v LDAP, příklad &quot;sAMAccountName=&quot;';
$string['sysadminuser'] = 'Rogō SysAdmin uživatel';
$string['initialsysadmin'] = 'K vytvoření běžných účtů zaměstnanců a obecné správě je potřeba se nejprve přihlásit k SysAdminovskému účtu.';
$string['title'] = 'Titul';
$string['title_types'] = "Pan,Paní,Slečna,Dr,Profesor";
$string['firstname'] = 'Jméno';
$string['surname'] = 'Příjmení';
$string['emailaddress'] = 'E-mail';
$string['username'] = 'Uživatelské jméno';
$string['password'] = 'Heslo';
$string['helpdb'] = 'Nápověda k Rogō';
$string['loadhelp'] = 'Otevřít nápovědu';
$string['supportemaila'] = 'E-mail podpory';
$string['supportemail'] = 'E-mail podpory';
$string['supportnumbers'] = 'Číslo Podpory';
$string['name'] = 'Jméno';
$string['number'] = 'Číslo';
$string['install'] = 'Instalovat Rogō';
$string['installed'] = 'Rogō je úspěšně nainstalováno.';
$string['deleteinstall'] = 'Prosíme, odstraňte z bezpečnostních důvodů instalační adresář.';
$string['staffhomepage'] = 'Přejít na domovskou stránku určenou zaměstnancům';

$string['logwarning1'] = 'Staff_help.sql nemohl být načten, nápovědu pro zaměstnance nelze nainstalovat';
$string['logwarning2'] = 'Nelze nalézt staff_help.sql, nápověda pro zaměstnance nemohla být naistalována';
$string['logwarning3'] = 'Student_help.sql nemohl být načten, nápovědu pro studenty nelze nainstalovat';
$string['logwarning4'] = 'Nelze nalézt student_help.sql, nápověda pro studenty nemohla být naistalována';
$string['displayerror1'] = 'Název databáze \'%s\' je již používán, vyberte, prosím, jiný.';
$string['displayerror2'] = 'Nelze vytvořit databázi \'%s\' zkontrolujte, zda máte povolení od administrátora  ';
$string['displayerror3'] = 'nelze vytvořit tabulku.';
$string['wdatabaseuser'] = 'Uživatel databáze ';
$string['wnotcreated'] = ' nemohl být vytvořen';
$string['wnotpermission'] = ' nemohl nastavit povolení';
$string['logwarning20'] = 'Nelze FLUSH PRIVILEGES';
$string['errors1'] = 'Rogo již bylo nainstalováno! Pro nové nastavení odstraňte/přejmenujte.';
$string['errors3'] = 'Rogo vyžaduje existenci %s a jejich zapisovatelnost na webserver';
$string['errors4'] = 'Rogo vyžaduje existenci %s/medií a jejich zapisovatelnost na webserver';
$string['errors5'] = 'Rogo vyžaduje existenci %s/qti/importů a a jejich zapisovatelnost na webserver';
$string['errors6'] = 'Rogo vyžaduje existenci %s/qti/exportů a jejich zapisovatelnost na webserver';
$string['errors7'] = 'Rogo vyžaduje existenci %s/dočasných adresářů a jejich zapisovatelnost na webserver';
$string['errors8'] = 'Rogo vyžaduje Apache verzi $apache_min_ver';
$string['errors9'] = 'Rogo vyžaduje Apache verzi $apache_min_ver nebo novější než máte';
$string['errors10'] = 'Rogo vyžaduje PHP verzi $php_min_ver nebo novější';
$string['errors11'] = 'Rogo k fungování vyžaduje PHP mysqli modul, prosím, nainstalujte jej a aktivujte.';
$string['errors12'] = 'Přístup do Rogō je možný pouze pomocí https. Aktualizujte prosím konfiguraci Apache.';
$string['errors13'] = 'Chyba';
$string['errors14'] = 'Byla generována následující varování"';
$string['errors15'] = 'Varování';
$string['errors16'] = 'Rogo vyžaduje schopnost tvořit jeho konfigurační soubor %s/config/config.inc.php. Jedním ze způsobů, jak toto zvládnout je, že dočasně povolíte přístup osobě, která toto ovládá, do %s/config a po spuštění aktualizace toto oprávnění zrušíte.';
$string['installscript'] = 'Instalační skript Rogō ';
$string['systeminstallation'] = 'Instalace systému';

$string['flash'] = "Adobe Flash"; //cognate
$string['hostname'] = "Machine hostname";
$string['webhost'] = 'WebServer host';
$string['html5'] = "HTML5"; //cognate

$string['labsecuritytype'] = "Zabezpečení učebny";
$string['IP'] = "IP addresa";
$string['interactivequestions'] = "Nastavení interaktivních úloh.";

?>
