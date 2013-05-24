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

require '../lang/' . $language . '/include/months.inc';
require '../lang/' . $language . '/include/paper_types.inc';

$string['propertiestitle'] = 'Właściwości';
$string['edittitle'] = 'Edytuj';
$string['warning'] = 'Uwaga: nazwa arkusza jest już wykorzystywana w innym teście!';
$string['availablefromyear'] = 'Zaplanowany rok \'od\' jest późniejszy niż rok \'do\' co jest nielogiczne!';
$string['availablefrommonth'] = 'Zaplanowany miesiąc \'od\' jest późniejszy niż miesiąc \'do\' co jest nielogiczne!';
$string['availablefromday'] = 'Zaplanowany dzień \'od\' jest późniejszy niż dzień \'do\' co jest nielogiczne!';
$string['msg1'] = 'Nie wybrano modułu. Arkusz musi być przypisany do co najmniej jednego modułu.';
$string['msg2'] = 'Data rozpoczęcia i zakończenia egzaminu końcowego musi być ta sama (zakładka \'Prawa dostępu\').';
$string['msg3'] = 'Czas trwania egzaminu końcowego musi być określony.\nPowinien to być normalny czas trwania z wyłączeniem czasu dodatkowego dla osób studentów z dysleksją.';
$string['msg4'] = 'Rok akademicki dla egzaminu końcowego musi być określony (zakładka \'Prawa dostępu\').';
$string['msg5'] = 'OSCE muszą być przypisane do co najmniej jednego modułu.';
$string['msg6a'] = 'Wybrano egzaminatorów wewnętrznych, jednak nie określono terminu finalizacji.'; 
$string['msg6'] = 'Wybrano egzaminatorów zewnętrznych, jednak nie określono terminu finalizacji.';
$string['msg7'] = 'Proszę podać nazwę arkusza.';
$string['msg8'] = 'To jest egzamin typu \'closed-book\', w czasie którego <em>niedozwolone jest</em> korzystanie ze środków i źródeł pomocniczych (także słowników) ani pomocy osób drugich. Niedozwolone jest też używanie urządzeń elektronicznych z wyjątkiem komputera egzaminacyjnego.';

// General tab
$string['generaltab'] = 'Ogólne';
$string['generalheading'] = 'Nazwa arkusza, punktacja i opcje prezentacji';
$string['paperdetails'] = 'Szczegóły arkusza';
$string['onlyonexamday'] = '(tylko w dniu egzaminu)';
$string['url'] = 'URL'; //cognate
$string['name'] = 'Nazwa';
$string['type'] = 'Typ';
$string['folder'] = 'Folder'; //cognate
$string['feedback'] = 'Odzew';
$string['displayoptions'] = 'Opcje prezentacji';
$string['display'] = 'Prezentacja';
$string['windowed'] = 'w oknie';
$string['fullscreen'] = 'Pełnoekranowa (tylko Internet Explorer)';
$string['navigation'] = 'Nawigacja';
$string['bidirectional'] = 'dwukierunkowa';
$string['unidirectional'] = 'jednokierunkowa (liniowa)';
$string['background'] = 'Tło';
$string['foreground'] = 'Pierwszy plan';
$string['theme'] = 'Motyw';
$string['labelsnotes'] = 'Etykiety/Notatki';
$string['calculator'] = 'Kalkulator';
$string['displaycalculator'] = 'Kalkulator ekranowy';
$string['audio'] = 'Audio'; //cognate
$string['demosoundclip'] = 'testowy plik dźwiękowy';
$string['marking'] = 'Punktacja';
$string['overallclassification'] = 'Klasyfikacja całościowa';
$string['markingguidance'] = 'Examiner Marking Guidance';
$string['overallclass1'] = '&lt;Automatyczna&gt;';
$string['overallclass2'] = 'Jednoznacznie niezdany | Na granicy | Jednoznacznie zdany';
$string['overallclass3'] = 'Niezdany | Na granicy niezdania | Na granicy zdania | Zdany | Zdecydowanie zdany';
$string['overallclass4'] = 'Jednoznacznie niezdany | Na granicy | Jednoznacznie zdany | Wyróżniająco zdany';
$string['passmark'] = 'Liczba punktów na zaliczenie';
$string['distinction'] = 'Wyróżnienie';
$string['method'] = 'Metoda';
$string['noadjustment'] = 'Brak wzoru';
$string['calculatrrandommark'] = "Oszacuj punkty na 'chybił-trafił'";
$string['stdset'] = 'Wyznaczony standard';
$string['ticks_crosses'] = 'Haczyki/Krzyżyki';
$string['question_marks'] = 'Punktacja pytania';
$string['hideallfeedback'] = 'Ukryj odzew<br />jeśli nie odpowiedziano';
$string['correctanswerhighlight'] = 'Wyróżnienie poprawnej odpowiedzi';
$string['textfeedback'] = 'Odzew tekstowy';
$string['photos'] = 'Zdjęcia';  
$string['ifavailable'] = 'jeśli dostępne';  
$string['review'] = 'Recenzja';
$string['allpeerspergroup'] = 'wszystkich członków grupy'; 
$string['singlereview'] = 'recenzja indywidualna';
$string['numberfrom'] = 'Numeruj od';
$string['groupdetails'] = 'Szczegóły dot. grupy';

// Security tab
$string['securitytab'] = 'Bezpieczeństwo';
$string['securityheading'] = 'Kontrola praw dostępu studentów do arkuszy.';
$string['session'] = 'Sesja';
$string['password'] = 'Hasło';
$string['timezone'] = 'Strefa czasowa';
$string['modules'] = 'Moduł(y)';
$string['duration'] = 'Czas trwania';
$string['mins'] = 'min.';
$string['availablefrom'] = 'Dostępne od';
$string['to'] = 'do';
$string['restricttolabs'] = 'Ogranicz do pracowni';
$string['restricttometadata'] = 'Ogranicz do metadanych';
$string['na'] = 'Brak';//data

// Reviewers tab
$string['reviewerstab'] = 'Recenzenci';
$string['reviewersheading'] = 'Lista recenzentów wewnętrznych i zewnętrznych z terminami finalizacji.';
$string['internalreviewers'] = 'Wewnętrzni recenzenci';
$string['externalexaminers'] = 'Zewnętrzni egzaminatorzy';
$string['deadline'] = 'Termin finalizacji:';

// Exam Rubric tab
$string['rubrictab'] = 'Rubryka egzaminu';
$string['rubricheading'] = 'Rubryka egzaminu prezentowana studentowi przed rozpoczęciem egzaminu końcowego';

// Prologue tab
$string['prologuetab'] = 'Wstęp';
$string['prologueheading'] = 'Tekst wyświetlany u góry ekranu 1 po rozpoczęciu pracy z arkuszem.';

// Postscript tab
$string['postscripttab'] = 'Zakończenie';
$string['postscriptheading'] = "Tekst wyświetlany po tym jak student kliknie 'Zakończ'.";

// Reference Material tab
$string['referencematerial'] = 'Materiał pomocniczy';  
$string['referenceheading'] = 'Kontrola, które materiały pomocnicze są dostępne dla arkusza.'; 
$string['nomaterials'] = 'Do tego arkusza nie przypisano żadnych materiałów pomocniczych dostępnych w tym module.<br /><br />Materiały pomocnicze mogą być dodane przez kliknięcie na opcję  \'Materiał pomocniczy\' na ekranie  modułu (<a href="" style="color:blue" onclick="launchHelp(296); return false;">zobacz pomoc</a>).';

// Feedback tab
$string['feedbackheading'] = 'Odzew dostępny dla studentów'; 
$string['feedbackwarning'] = '<strong>Uwaga:</strong> To uwolni pytania włączając w to poprawne odpowiedzi i oceny dla studentów.'; 
$string['on'] = 'Wł.';
$string['off'] = 'Wył.';
$string['objectivesreport'] = 'Odzew dot. celów';
$string['questionfeedback'] = 'Odzew dot. pytań';
$string['textualfeedback'] = 'Odzew tekstowy';
$string['above'] = 'Powyżej';
$string['message'] = 'Wiadomość';  
$string['answerscreensettings'] = 'Ustawienia ekranu odpowiedzi';

// Changes tab
$string['changes'] = 'Zmiany';
$string['changesheading'] = 'Lista zmian dokonanych w aktualnym arkuszu.'; 
$string['part'] = 'Część';
$string['old'] = 'Poprzednio';
$string['new'] = 'Teraz';
$string['date'] = 'Data';
$string['author'] = 'Autor';
$string['startdate'] = 'Data rozpoczęcia';
$string['enddate'] = 'Data zakończenia';
$string['retired'] = 'Wycofany'; 
$string['externalreviewdeadline'] = 'Termin finalizacji zewnętrznej recenzji';
$string['internalreviewdeadline'] = 'Termin finalizacji wewnętrznej recenzji';  

//Colour picker
$string['colour'] = 'Kolor';
$string['themecolours'] = 'Kolory motywu';
$string['standardcolours'] = 'Kolory standardowe';
$string['more'] = 'Więcej...';
$string['cancel'] = 'Anuluj';
$string['OK'] = 'OK'; //cognate
?>