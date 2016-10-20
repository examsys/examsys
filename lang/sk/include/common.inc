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

$string['administrativetools'] = 'Nastavenia';
$string['helpsupport'] = 'Nápoveda a podpora';
$string['aboutrogo'] = 'O programe Rog&#333; %s';
$string['furtherassistance'] = 'V prípade potreby ďalšej pomoci, prosím, kontaktujte: <a href="mailto:%s">%s</a>';
$string['pagenotfound'] = 'Stránka nebola nájdená';
$string['signinmsg'] = '<span style="color:#1f497d;font-weight:bold;">Vitajte v systéme elektronického testovania</span><br />
    V prípade záujmu o vytvorenie účtu kontaktujte <a href="mailto:jaroslav.majernik@upjs.sk?Subject=Rogo%20account">správcu</a>.<hr /><br />
    Stránka, na ktorú sa pokúšate vstúpiť, vyžaduje overenie. Prihláste sa, prosím, pomocou svojho používateľského mena a hesla:';
$string['username'] = 'Používateľské meno';
$string['password'] = 'Heslo';
$string['signin'] = 'Prihlásiť';
$string['signout'] = 'Odhlásiť';
$string['home'] = 'Domov';
$string['help'] = 'Pomoc';
$string['line'] = 'Riadok';
$string['cancel'] = 'Zrušiť';
$string['ok'] = 'OK';
$string['save'] = 'Uložiť';
$string['accessdenied'] = 'Prístup zamietnutý';
$string['accessdeniedadmin'] = '<strong>Prístup zamietnutý</strong><br />pokúšate sa vstúpiť na správcovskú stránku.';
$string['accessdeniedsysadmin'] = '<strong>Prístup zamietnutý</strong><br />pokúšate sa vstúpiť na správcovskú stránku.';
$string['denied_paper'] = 'Pokúšate sa o vstup <strong>do dokumentu, ku ktorému nemáte prístupové práva</strong>!';
$string['denied_folder'] = 'Pokúšate sa o vstup <strong>do priečinka, ku ktorému nemáte prístupové práva</strong>!';
$string['denied_question'] = 'Pokúšate sa o vstup <strong>do úlohy, ku ktorej nemáte prístupové práva</strong>!';
$string['denied_team'] = 'Nie ste členom <strong>%s</strong> tímu modulu';
$string['noldapusernamesupplied'] = 'Nebolo zadané používateľské LDAP meno a heslo';
$string['incorrectpassword'] = 'Zadané chybné heslo';
$string['ldapserverunavailable'] = 'LDAP server nie je dostupný';
$string['noldapaccount'] = 'Nebol nájdený žiaden LDAP účet!';
$string['ldapservernosearch'] = 'LDAP server nie je dostupný: nie je možné vyhľadávať';
$string['nodatabaseconnection'] = 'K databáze sa nie je možné pripojiť, obráťte sa, prosím, na správcu.';
$string['as'] = 'ako';
$string['localtsauthfailed'] = 'Miestna autentizácia Rogō sa nepodarila.';
$string['notsaccount'] = 'Účet nebol nájdený!';
$string['tempaccount'] = 'Dočasný účet';
$string['authenticationfailed'] = 'Autentizácia Rogō zlyhala, nezabudnite:';
$string['usernamecasesensitive'] = 'V prihlasovacom mene/hesle rozlišujte VEĽKÉ a malé písmena. ';
$string['pressf5'] = 'Stlačte na klávesnici kláves \'F5\' a skúste sa prihlásiť znova';
$string['tsonldap'] = 'Rogō využíva LDAP hesla';
$string['ifstuckinvigilator'] = 'Pokiaľ sa opakovane nedarí, požiadajte dozorujúcu osobu o dočasný účet';
$string['forgottenpassword'] = 'Zabudnuté heslo';
$string['warning'] = 'Varovanie';
$string['secureconnection'] = 'Požadované bezpečné pripojenie';
$string['secureconnectionmsg'] = 'Rogō je prístupné iba prostredníctvom zabezpečeného webového pripojenia. Namiesto toho použite:';
$string['errormsg'] = 'Ak sa táto chyba objavuje opakovane, kontaktujte, prosím:';
$string['ltifirstlogin'] = 'Rogō autorizácia';
$string['ltifirstlogindesc'] = 'Systém e-hodnotenia Rogō musí, k zaisteniu správneho oprávnenia, Vašu identitu potvrdiť. Potvrdenie probehne iba pri Vašom prvom prihlásení a následne, k zaisteniu bezpečnosti, v občasných intervaloch .<br /><br /><input type="button" value=" Prihlásiť " onclick="window.location=\'./\'" />';
$string['ltinotconfigured'] = 'Použitý odkaz nebol doteraz konfigurovaný.';
$string['LTIFAILURE'] = 'Chyba LTI ';
$string['expiredtimestamp'] = 'Časový limit vypršal, prihláste sa, prosím, znova (nepoužívajte tlačidlo aktualizácie v prehliadači!).';
$string['UserMissing']='Pridružený používateľ chýba';
$string['UserMissingDesc'] = 'Ak pridružený používateľ zmizne, znamená to vážnu chybu a je potrebné obrátiť sa na podporu';
$string['impersonatepriv'] = 'Nemáte dostatočné práva k imitácii používateľov.';
$string['NoAuthenticationConfigured'] = 'Overovanie nenastavené';
$string['NoAuthenticationConfiguredmessage'] = 'V config súbore nebolo nastavené žiadne overovanie. Kontaktujte, prosím, systémového správcu.';
$string['Authentication_callback_failure1'] = 'Nepodarilo sa zapísať sekčnú spätnú väzbu';
$string['Authentication_callback_failure2'] = ' s funkciou';
$string['Authentication_issue1'] = 'Problémy s overovaním';
$string['Authentication_issue2'] = 'Overovacie pluginy vás nemohli prihlásiť a neposkytli žiadny ďalší formulár ani presmerovanie. Aktualizujte pomocou stlačenia klávesu F5, ak sa stále nedarí, kontaktujte podporu : <a href="mailto:%s">%s</a>.<p>Do e-mailu, prosím uveďte následujúci debug:</p><div style="margin-left:100px;">%s</div>';
$string['Authentication_issue2nodebug'] = 'Overovacie pluginy Vás nemohli prihlásiť a neposkytli žiadny ďalší formulár ani presmerovanie. Aktualizujte pomocou stlačenia klávesu F5, ak sa stále nedarí, kontaktujte podporu: <a href="mailto:%s">%s</a>.';
$string['Authentication_notloggedin1'] = 'Problémy s overovaním';
$string['Authentication_notloggedin2'] = 'Nie ste prihlásený/á. Aktualizujte stránku pomocou stlačenia klávesu F5, ak sa stále nedarí, kontaktujte podporu: <a href="mailto:%s">%s</a><p>Do e-mailu, prosím uveďte následujúci debug:</p><div style="margin-left:100px;">%s</div>';
$string['Authentication_notloggedin2nodebug'] = 'Nie ste prihlásený/á. Aktualizujte stránku pomocou stlačenia klávesu F5, ak sa stále nedarí, kontaktujte podporu: <a href="mailto:%s">%s</a>';
$string['NoLookupConfigured'] = 'Vyhľadávanie nenastavené';
$string['NoLookupConfiguredmessage'] = 'V config súbore nebolo nastavené žiadne vyhľadávanie. Kontaktujte, prosím, systémového správcu.';
$string['guestbutton'] = 'Dočasný účet';
$string['authentication_lti_authmessage'] = 'Prihláste sa, prosím, k overeniu spojenia LTI.';
$string['authentication_lti_reauthmessage'] = 'Prihláste sa, prosím, znova k overeniu spojenia LTI.';
$string['lti_not_allow_add_selfreg'] = 'Automatické pridávanie študentov, do tohto typu modulu, nebolo v configu modulu povolené.';
$string['denied_role'] = 'Vaša používateľská rola %s Vám prístup neumožňuje.';
$string['Error'] = 'Chyba';
$string['NoAcademicSession'] = 'Nie je nastavený akademický rok.';
$string['NoFieldMetadata'] = 'Žiadne pole v metadátach skupín.';
$string['NoGroup'] = 'K aktuálnemu používateľovi nie je možné nájsť žiadnu skupinu.';
$string['new'] = 'Nový';
$string['old'] = 'Starý';
$string['note'] = 'Poznámka';
$string['studentname'] = 'Meno študenta';
$string['html5warn'] = 'Rog&#333; vyžaduje podporu HTML5, aktualizujte, prosím, Váš prehliadač.';
$string['colours'] = 'Farby';
$string['Objectives Feedback report'] = 'Komentár k vzdelávacím cieľom';
$string['Questions Feedback report'] = 'Komentár k úlohám';
$string['loggedinas'] = 'Prihlásený ako';
$string['crondisallowed'] = 'Proces sa pokusil spustiť nepovolený skript';
$string['register'] = 'Registrovať ako zamestnanca ';
$string['register1'] = 'Registrovať ako ';
$string['demomodule'] = 'Demo modul';
$string['name'] = 'Meno';
$string['eassessmentmanagementsystem'] = 'Systém elektronického testovania';
?>