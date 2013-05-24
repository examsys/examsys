<?php
require_once 'shared.inc.php';

class RunAsStudentDichotomousTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testQuestionPresenceAndOrderPlusUnanswered() {
    do_student_login($this, 'teststudent10', 'jgl!34Z^');

    $this->open("/user_index.php?id=41357635970102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('Dichotomous T/F Questions');
    $this->assertTextPresent('Note: Dichotomous 1 notes for students');
    $this->assertTextPresent('Dichotomous 1 scenario');
    $this->assertTextPresent('Dichotomous 1, True/False, display order, 1 mark, 4 stems, answers: T, F, F, T');
    $this->assertTextPresent('Dichotomous 2, True/False, alphabetic, 2 marks, 4 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 16);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(4 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(8 marks)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q2_opt1"]/td[3]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q2_opt2"]/td[3]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q2_opt3"]/td[3]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q2_opt4"]/td[3]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 3, True/False, random, 1 mark, 5 stems, answers');
    $this->assertTextPresent('T/F Mark Per Question');
    $this->assertTextPresent('Dichotomous 4, True/False, display order, mark per question, 1 mark, 4 stems, answers: T, F, F, T');
    $this->assertCssCount('css=input[type="radio"]', 18);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(5 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 5, True/False, alphabetic, mark per question, 2 marks, 4 stems, answers');
    $this->assertTextPresent('Dichotomous 6, True/False, random, mark per question, 1 mark, 5 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 18);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q1_opt1"]/td[3]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q1_opt2"]/td[3]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q1_opt3"]/td[3]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q1_opt4"]/td[3]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('T/F Negative Marking');
    $this->assertTextPresent('Dichotomous 7, True/False, display order, 1 mark correct, -0.5 marks incorrect, 4 stems, answers: T, F, F, T');
    $this->assertTextPresent('Dichotomous 8, True/False, alphabetic, 2 marks correct, -1 mark incorrect, 4 stems, answer');
    $this->assertCssCount('css=input[type="radio"]', 16);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(4 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(8 marks, negative marking)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q2_opt1"]/td[3]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q2_opt2"]/td[3]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q2_opt3"]/td[3]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q2_opt4"]/td[3]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 9, True/False, random, 1 mark correct, -1 mark incorrect, 5 stems, answers');
    $this->assertTextPresent('T/F Mark Per Question, Negative Marking');
    $this->assertTextPresent('Dichotomous 10, True/False, display order, mark per question, 1 mark correct, -0.5 marks incorrect, 4 stems, answers: T, F, F, T');
    $this->assertCssCount('css=input[type="radio"]', 18);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(5 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 11, True/False, alphabetic, mark per question, 2 marks correct, -1 mark incorrect, 4 stems, answers');
    $this->assertTextPresent('Dichotomous 12, True/False, random, mark per question, 1 mark correct, -1 mark incorrect, 5 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 18);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q1_opt1"]/td[3]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q1_opt2"]/td[3]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q1_opt3"]/td[3]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q1_opt4"]/td[3]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous Y/N Questions');
    $this->assertTextPresent('Note: Dichotomous 13 notes for students');
    $this->assertTextPresent('Dichotomous 13 scenario');
    $this->assertTextPresent('Dichotomous 13, Yes/No, display order, 1 mark, 4 stems, answers: T, F, F, T');
    $this->assertTextPresent('Dichotomous 14, Yes/No, alphabetic, 2 marks, 4 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 16);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(4 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(8 marks)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q2_opt1"]/td[3]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q2_opt2"]/td[3]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q2_opt3"]/td[3]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q2_opt4"]/td[3]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 15, Yes/No, random, 1 mark, 5 stems, answers');
    $this->assertTextPresent('Y/N Mark Per Question');
    $this->assertTextPresent('Dichotomous 16, Yes/No, display order, mark per question, 1 mark, 4 stems, answers: T, F, F, T');
    $this->assertCssCount('css=input[type="radio"]', 18);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(5 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 17, Yes/No, alphabetic, mark per question, 2 marks, 4 stems, answers');
    $this->assertTextPresent('Dichotomous 18, Yes/No, random, mark per question, 1 mark, 5 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 18);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q1_opt1"]/td[3]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q1_opt2"]/td[3]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q1_opt3"]/td[3]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q1_opt4"]/td[3]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Y/N Negative Marking');
    $this->assertTextPresent('Dichotomous 19, Yes/No, display order, 1 mark correct, -0.5 marks incorrect, 4 stems, answers: T, F, F, T');
    $this->assertTextPresent('Dichotomous 20, Yes/No, alphabetic, 2 marks correct, -1 mark incorrect, 4 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 16);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(4 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(8 marks, negative marking)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q2_opt1"]/td[3]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q2_opt2"]/td[3]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q2_opt3"]/td[3]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q2_opt4"]/td[3]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 21, Yes/No, random, 1 mark correct, -1 mark incorrect, 5 stems, answers');
    $this->assertTextPresent('Y/N, Mark Per Question, Negative Marking');
    $this->assertTextPresent('Dichotomous 22, Yes/No, display order, mark per question, 1 mark correct, -0.5 marks incorrect, 4 stems, answers: T, F, F, T');
    $this->assertCssCount('css=input[type="radio"]', 18);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(5 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 23, Yes/No, alphabetic, mark per question, 2 marks correct, -1 mark incorrect, 4 stems, answers');
    $this->assertTextPresent('Dichotomous 24, Yes/No, random, mark per question, 1 mark correct, -1 mark incorrect, 5 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 18);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q1_opt1"]/td[3]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q1_opt2"]/td[3]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q1_opt3"]/td[3]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q1_opt4"]/td[3]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous T/F/A Questions');
    $this->assertTextPresent('Note: Dichotomous 25 notes for students');
    $this->assertTextPresent('Dichotomous 25 scenario');
    $this->assertTextPresent('Dichotomous 25, True/False/Abstain, display order, 1 mark, 4 stems, answers: T, F, F, T');
    $this->assertTextPresent('Dichotomous 26, True/False/Abstain, alphabetic, 2 marks, 4 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 24);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(4 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(8 marks)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q2_opt1"]/td[4]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q2_opt2"]/td[4]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q2_opt3"]/td[4]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q2_opt4"]/td[4]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 27, True/False/Abstain, random, 1 mark, 5 stems, answers');
    $this->assertTextPresent('T/F/A Mark Per Question');
    $this->assertTextPresent('Dichotomous 28, True/False/Abstain, display order, mark per question, 1 mark, 4 stems, answers: T, F, F, T');
    $this->assertCssCount('css=input[type="radio"]', 27);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(5 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 29, True/False/Abstain, alphabetic, mark per question, 2 marks, 4 stems, answers');
    $this->assertTextPresent('Dichotomous 30, True/False/Abstain, random, mark per question, 1 mark, 5 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 27);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q1_opt1"]/td[4]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q1_opt2"]/td[4]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q1_opt3"]/td[4]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q1_opt4"]/td[4]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('T/F/A Negative Marking');
    $this->assertTextPresent('Dichotomous 31, True/False/Abstain, display order, 1 mark correct, -0.5 marks incorrect, 4 stems, answers: T, F, F, T');
    $this->assertTextPresent('Dichotomous 32, True/False/Abstain, alphabetic, 2 marks correct, -1 mark incorrect, 4 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 24);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(4 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(8 marks, negative marking)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q2_opt1"]/td[4]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q2_opt2"]/td[4]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q2_opt3"]/td[4]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q2_opt4"]/td[4]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 33, True/False/Abstain, random, 1 mark correct, -1 mark incorrect, 5 stems, answers');
    $this->assertTextPresent('T/F/A Mark Per Question, Negative Marking');
    $this->assertTextPresent('Dichotomous 34, True/False/Abstain, display order, mark per question, 1 mark correct, -0.5 marks incorrect, 4 stems, answers: T, F, F, T');
    $this->assertCssCount('css=input[type="radio"]', 27);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(5 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 35, True/False/Abstain, alphabetic, mark per question, 2 marks correct, -1 mark incorrect, 4 stems, answers');
    $this->assertTextPresent('Dichotomous 36, True/False/Abstain, random, mark per question, 1 mark correct, -1 mark incorrect, 5 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 27);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q1_opt1"]/td[4]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q1_opt2"]/td[4]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q1_opt3"]/td[4]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q1_opt4"]/td[4]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous Y/N/A Questions');
    $this->assertTextPresent('Note: Dichotomous 37 notes for students');
    $this->assertTextPresent('Dichotomous 37 scenario');
    $this->assertTextPresent('Dichotomous 37, Yes/No/Abstain, display order, 1 mark, 4 stems, answers: T, F, F, T');
    $this->assertTextPresent('Dichotomous 38, Yes/No/Abstain, alphabetic, 2 marks, 4 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 24);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(4 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(8 marks)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q2_opt1"]/td[4]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q2_opt2"]/td[4]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q2_opt3"]/td[4]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q2_opt4"]/td[4]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 39, Yes/No/Abstain, random, 1 mark, 5 stems, answers');
    $this->assertTextPresent('Y/N/A Mark Per Question');
    $this->assertTextPresent('Dichotomous 40, Yes/No/Abstain, display order, mark per question, 1 mark, 4 stems, answers: T, F, F, T');
    $this->assertCssCount('css=input[type="radio"]', 27);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(5 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 41, Yes/No/Abstain, alphabetic, mark per question, 2 marks, 4 stems, answers');
    $this->assertTextPresent('Dichotomous 42, Yes/No/Abstain, random, mark per question, 1 mark, 5 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 27);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q1_opt1"]/td[4]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q1_opt2"]/td[4]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q1_opt3"]/td[4]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q1_opt4"]/td[4]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Y/N/A Negative Marking');
    $this->assertTextPresent('Dichotomous 43, Yes/No/Abstain, display order, 1 mark correct, -0.5 marks incorrect, 4 stems, answers: T, F, F, T');
    $this->assertTextPresent('Dichotomous 44, Yes/No/Abstain, alphabetic, 2 marks correct, -1 mark incorrect, 4 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 24);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(4 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(8 marks, negative marking)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q2_opt1"]/td[4]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q2_opt2"]/td[4]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q2_opt3"]/td[4]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q2_opt4"]/td[4]', 'Stem X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 45, Yes/No/Abstain, random, 1 mark correct, -1 mark incorrect, 5 stems, answers');
    $this->assertTextPresent('Y/N/A, Mark Per Question, Negative Marking');
    $this->assertTextPresent('Dichotomous 46, Yes/No/Abstain, display order, mark per question, 1 mark correct, -0.5 marks incorrect, 4 stems, answers: T, F, F, T');
    $this->assertCssCount('css=input[type="radio"]', 27);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(5 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Dichotomous 47, Yes/No/Abstain, alphabetic, mark per question, 2 marks correct, -1 mark incorrect, 4 stems, answers');
    $this->assertTextPresent('Dichotomous 48, Yes/No/Abstain, random, mark per question, 1 mark correct, -1 mark incorrect, 5 stems, answers');
    $this->assertCssCount('css=input[type="radio"]', 27);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');
    // Order of alphabetic questions
    $this->assertElementContainsText('//tr[@id="q1_opt1"]/td[4]', 'Stem B');
    $this->assertElementContainsText('//tr[@id="q1_opt2"]/td[4]', 'Stem M');
    $this->assertElementContainsText('//tr[@id="q1_opt3"]/td[4]', 'Stem P');
    $this->assertElementContainsText('//tr[@id="q1_opt4"]/td[4]', 'Stem X');

    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());

    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=41357635970102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[20]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[20]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[21]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[21]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[22]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[22]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[23]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[23]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[24]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[24]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[25]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[25]/tbody/tr[3]/td[2]/p/span', '0 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[25]/table/tbody/tr[2]/td[2]', '0 out of 168');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[4]/td[2]', '0.00%');
  }

  public function testCompletePaperCorrect() {
    do_student_login($this, 'teststudent11', 'bkt_66Y4');

    $this->open("/user_index.php?id=41357635970102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q1_5'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q1_5'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q1_5'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q1_5'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q1_5'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q1_5'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q1_5'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q1_5'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());

    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=41357635970102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '4 out of 4');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '4 out of 4');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '4 out of 4');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[20]/tbody/tr[2]/td[2]/p[2]/span', '4 out of 4');
    $this->assertElementContainsText('//table[20]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[21]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[21]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[22]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[22]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[23]/tbody/tr[2]/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[23]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[24]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[24]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[25]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[25]/tbody/tr[3]/td[2]/p/span', '1 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[25]/table/tbody/tr[2]/td[2]', '168 out of 168');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[4]/td[2]', '100.00%');
  }

  public function testCompletePaperIncorrect() {
    do_student_login($this, 'teststudent12', 'rmu_74L4');

    $this->open("/user_index.php?id=41357635970102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_4");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_5");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_4");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_4");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_5");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_4");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_4");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_5");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_4");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_4");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_5");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_4");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_4");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_5");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_4");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_4");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_5");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_4");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_4");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_5");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_4");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_4");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_5");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_4");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_5");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=41357635970102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p/span', '-2 out of 4');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '-4 out of 8');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '-5 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p/span', '-2 out of 4');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '-4 out of 8');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '-5 out of 5');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p/span', '-2 out of 4');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '-4 out of 8');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '-5 out of 5');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[20]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[20]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[21]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[21]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[22]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[22]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[23]/tbody/tr[2]/td[2]/p/span', '-2 out of 4');
    $this->assertElementContainsText('//table[23]/tbody/tr[4]/td[2]/p/span', '-4 out of 8');
    $this->assertElementContainsText('//table[24]/tbody/tr/td[2]/p/span', '-5 out of 5');
    $this->assertElementContainsText('//table[24]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[25]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[25]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[25]/table/tbody/tr[2]/td[2]', '-54 out of 168');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[4]/td[2]', '-32.14%');
  }

  public function testCompletePaperMixed() {
    do_student_login($this, 'teststudent13', 'hii.420R');

    $this->open("/user_index.php?id=41357635970102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->click("name=q1_1");
    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_5'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_3");
    $this->click("name=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("name=q1_1");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_3");
    $this->click("name=q2_4");
    $this->click("name=q2_5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_4");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q1_5'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_4");
    $this->click("name=q1_3");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_5");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_3");
    $this->click("name=q1_4");
    $this->click("name=q2_2");
    $this->click("name=q2_1");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_3");
    $this->click("name=q1_4");
    $this->click("name=q1_5");
    $this->click("name=q2_1");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_3");
    $this->click("name=q2_1");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_5");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_4");
    $this->click("name=q1_3");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_4");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q1_5'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_5'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_3");
    $this->click("name=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("name=q1_1");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_3");
    $this->click("name=q2_4");
    $this->click("name=q2_5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_5'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_3");
    $this->click("name=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("name=q1_1");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_3");
    $this->click("name=q2_4");
    $this->click("name=q2_5");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=41357635970102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 4');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '4 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '3 out of 5');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '-4 out of 8');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p/span', '1 out of 4');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '2 out of 8');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '-1 out of 5');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '8 out of 8');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '-4 out of 8');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '5 out of 5');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[20]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 4');
    $this->assertElementContainsText('//table[20]/tbody/tr[4]/td[2]/p/span', '4 out of 8');
    $this->assertElementContainsText('//table[21]/tbody/tr/td[2]/p/span', '3 out of 5');
    $this->assertElementContainsText('//table[21]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[22]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[22]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[23]/tbody/tr[2]/td[2]/p/span', '1 out of 4');
    $this->assertElementContainsText('//table[23]/tbody/tr[4]/td[2]/p/span', '2 out of 8');
    $this->assertElementContainsText('//table[24]/tbody/tr/td[2]/p/span', '1 out of 5');
    $this->assertElementContainsText('//table[24]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[25]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[25]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[25]/table/tbody/tr[2]/td[2]', '50.5 out of 168');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[4]/td[2]', '30.06%');
  }

  public function testCompletePaperWithAbstentions() {
    do_student_login($this, 'teststudent14', 'mon~61Qt');

    $this->open("/user_index.php?id=41357635970102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[3]");
    $this->click("xpath=(//input[@name='q1_4'])[3]");
    $this->click("xpath=(//input[@name='q2_2'])[3]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[3]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("xpath=(//input[@name='q1_2'])[3]");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[3]");
    $this->click("name=q1_5");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[3]");
    $this->click("xpath=(//input[@name='q2_3'])[3]");
    $this->click("name=q2_4");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_4'])[3]");
    $this->click("xpath=(//input[@name='q1_3'])[3]");
    $this->click("xpath=(//input[@name='q2_3'])[3]");
    $this->click("xpath=(//input[@name='q2_5'])[3]");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_1");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[3]");
    $this->click("xpath=(//input[@name='q1_4'])[3]");
    $this->click("name=q2_2");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[3]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->click("xpath=(//input[@name='q1_1'])[3]");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[3]");
    $this->click("xpath=(//input[@name='q1_5'])[3]");
    $this->click("xpath=(//input[@name='q2_1'])[3]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("xpath=(//input[@name='q2_4'])[3]");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->click("xpath=(//input[@name='q1_2'])[3]");
    $this->click("name=q1_1");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_5'])[3]");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->click("xpath=(//input[@name='q1_1'])[3]");
    $this->click("xpath=(//input[@name='q1_2'])[3]");
    $this->click("xpath=(//input[@name='q1_3'])[3]");
    $this->click("xpath=(//input[@name='q1_4'])[3]");
    $this->click("xpath=(//input[@name='q2_2'])[3]");
    $this->click("xpath=(//input[@name='q2_1'])[3]");
    $this->click("xpath=(//input[@name='q2_4'])[3]");
    $this->click("xpath=(//input[@name='q2_3'])[3]");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->click("xpath=(//input[@name='q1_3'])[3]");
    $this->click("xpath=(//input[@name='q1_4'])[3]");
    $this->click("xpath=(//input[@name='q1_2'])[3]");
    $this->click("xpath=(//input[@name='q1_5'])[3]");
    $this->click("xpath=(//input[@name='q1_1'])[3]");
    $this->click("xpath=(//input[@name='q2_1'])[3]");
    $this->click("xpath=(//input[@name='q2_2'])[3]");
    $this->click("xpath=(//input[@name='q2_3'])[3]");
    $this->click("xpath=(//input[@name='q2_4'])[3]");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->click("xpath=(//input[@name='q1_2'])[3]");
    $this->click("xpath=(//input[@name='q1_1'])[3]");
    $this->click("xpath=(//input[@name='q1_4'])[3]");
    $this->click("xpath=(//input[@name='q1_3'])[3]");
    $this->click("xpath=(//input[@name='q2_2'])[3]");
    $this->click("xpath=(//input[@name='q2_5'])[3]");
    $this->click("xpath=(//input[@name='q2_1'])[3]");
    $this->click("xpath=(//input[@name='q2_3'])[3]");
    $this->click("xpath=(//input[@name='q2_4'])[3]");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->click("xpath=(//input[@name='q1_1'])[3]");
    $this->click("xpath=(//input[@name='q1_2'])[3]");
    $this->click("xpath=(//input[@name='q1_3'])[3]");
    $this->click("xpath=(//input[@name='q1_4'])[3]");
    $this->click("xpath=(//input[@name='q2_2'])[3]");
    $this->click("xpath=(//input[@name='q2_1'])[3]");
    $this->click("xpath=(//input[@name='q2_4'])[3]");
    $this->click("xpath=(//input[@name='q2_3'])[3]");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->click("xpath=(//input[@name='q1_5'])[3]");
    $this->click("xpath=(//input[@name='q1_2'])[3]");
    $this->click("xpath=(//input[@name='q1_3'])[3]");
    $this->click("xpath=(//input[@name='q1_4'])[3]");
    $this->click("xpath=(//input[@name='q1_1'])[3]");
    $this->click("xpath=(//input[@name='q2_1'])[3]");
    $this->click("xpath=(//input[@name='q2_2'])[3]");
    $this->click("xpath=(//input[@name='q2_3'])[3]");
    $this->click("xpath=(//input[@name='q2_4'])[3]");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->click("xpath=(//input[@name='q1_2'])[3]");
    $this->click("xpath=(//input[@name='q1_1'])[3]");
    $this->click("xpath=(//input[@name='q1_4'])[3]");
    $this->click("xpath=(//input[@name='q1_3'])[3]");
    $this->click("xpath=(//input[@name='q2_4'])[3]");
    $this->click("xpath=(//input[@name='q2_5'])[3]");
    $this->click("xpath=(//input[@name='q2_1'])[3]");
    $this->click("xpath=(//input[@name='q2_2'])[3]");
    $this->click("xpath=(//input[@name='q2_3'])[3]");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=41357635970102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 4');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '4 out of 8');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p/span', '0.5 out of 4');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '3 out of 8');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[20]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 4');
    $this->assertElementContainsText('//table[20]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[21]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[21]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[22]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[22]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[23]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[23]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[24]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[24]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[25]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[25]/tbody/tr[3]/td[2]/p/span', '0 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[25]/table/tbody/tr[2]/td[2]', '7 out of 168');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[4]/td[2]', '4.17%');
  }

  public function testCompletePaperPartialAnswers() {
    do_student_login($this, 'teststudent15', 'scd=50AH');

    $this->open("/user_index.php?id=41357635970102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_4");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("name=q1_3");
    $this->click("name=q1_4");
    $this->click("name=q1_5");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_3");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("name=q1_3");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_5");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_5");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_2");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("name=q1_3");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("name=q2_5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_4");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_3");
    $this->click("name=q1_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_2'])[2]");
    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("name=q1_2");
    $this->click("name=q1_4");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q1_5");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_4");
    $this->click("name=q2_5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q1_4");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_1'])[2]");
    $this->click("name=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("name=q1_3");
    $this->click("name=q1_4");
    $this->click("name=q2_2");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q2_2'])[2]");
    $this->click("xpath=(//input[@name='q2_3'])[2]");
    $this->click("name=q2_4");
    $this->click("xpath=(//input[@name='q2_5'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_2");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("xpath=(//input[@name='q1_4'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1_1'])[2]");
    $this->click("name=q1_3");
    $this->click("name=q1_5");
    $this->click("name=q2_2");
    $this->click("name=q2_3");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1_1");
    $this->click("xpath=(//input[@name='q1_3'])[2]");
    $this->click("name=q2_1");
    $this->click("xpath=(//input[@name='q2_4'])[2]");
    $this->click("name=q2_5");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=41357635970102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '3 out of 4');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '2 out of 5');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p/span', '1.5 out of 4');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '6 out of 8');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '-4 out of 5');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '3 out of 4');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '2 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p/span', '1.5 out of 4');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '6 out of 8');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '-4 out of 5');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '3 out of 4');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '1 out of 5');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p/span', '1.5 out of 4');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '4 out of 8');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '-4 out of 5');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[20]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 4');
    $this->assertElementContainsText('//table[20]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[21]/tbody/tr/td[2]/p/span', '2 out of 5');
    $this->assertElementContainsText('//table[21]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[22]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[22]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[23]/tbody/tr[2]/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[23]/tbody/tr[4]/td[2]/p/span', '4 out of 8');
    $this->assertElementContainsText('//table[24]/tbody/tr/td[2]/p/span', '-3 out of 5');
    $this->assertElementContainsText('//table[24]/tbody/tr[4]/td[2]/p/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[25]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[25]/tbody/tr[3]/td[2]/p/span', '-1 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[25]/table/tbody/tr[2]/td[2]', '17.5 out of 168');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[25]/table/tbody/tr[4]/td[2]', '10.42%');
  }

  public function testClassTotals() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=4&startdate=20130101000000&enddate=20230208110000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");

    // Individuals
    $this->assertElementContainsText('//tr[@id="res7"]/td[5]', '0');
    $this->assertElementContainsText('//tr[@id="res7"]/td[6]', '0.00%');
    $this->assertElementContainsText('//tr[@id="res7"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res8"]/td[5]', '168');
    $this->assertElementContainsText('//tr[@id="res8"]/td[6]', '100.00%');
    $this->assertElementContainsText('//tr[@id="res8"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res9"]/td[5]', '-54');
    $this->assertElementContainsText('//tr[@id="res9"]/td[6]', '-32.14%');
    $this->assertElementContainsText('//tr[@id="res9"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res10"]/td[5]', '50.5');
    $this->assertElementContainsText('//tr[@id="res10"]/td[6]', '30.06%');
    $this->assertElementContainsText('//tr[@id="res10"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res11"]/td[5]', '7');
    $this->assertElementContainsText('//tr[@id="res11"]/td[6]', '4.17%');
    $this->assertElementContainsText('//tr[@id="res11"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res12"]/td[5]', '17.5');
    $this->assertElementContainsText('//tr[@id="res12"]/td[6]', '10.42%');
    $this->assertElementContainsText('//tr[@id="res12"]/td[7]', 'Fail');
  }
}
?>