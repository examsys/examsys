<?php
require_once 'shared.inc.php';

class RunAsStudentCalculationTest extends PHPUnit_Extensions_SeleniumTestCase
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

    $this->open("/user_index.php?id=61357920091102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('Calculation Questions');
    $this->assertTextPresent('Note: Calculation 1 notes for students');
    $this->assertTextPresent('Calculation 1 scenario');
    $this->assertTextPresent('Calculation 1, no tolerance, no units, 2 decimals, increment A - 0.02, 1 mark');
    $this->assertTextPresent('Calculation 2, tolerance full 1, units = cm, 1 decimal, 2 marks, increment A - 0.1, increment B - 0.2');
    $this->assertTextPresent('Negative marking');
    $this->assertTextPresent('Note: Calculation 3 notes for students');
    $this->assertTextPresent('Calculation 3, no tolerance, no units, 2 decimals, increment A - 0.02, 1 mark, -0.5 marks incorrect');
    $this->assertCssCount('css=input[type="text"]', 3);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(1 mark)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(2 marks)');
    $this->assertElementContainsText('//*[@id="q3_mk"]', '(1 mark, negative marking)');
    $this->assertElementPresent('id=calc1_q');
    $this->assertElementPresent('id=calc2_q');
    $this->assertElementPresent('id=calc3_q');
    // Check variables in correct range
    $matches = array();
    $qn_text = $this->getText('id=calc1_q');
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $this->assertTrue(($A >= 5 and $A <= 20));
    $this->check_decimals($A, 2, 0.02);
    $qn_text = $this->getText('id=calc2_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $this->assertTrue(($A >= 10 and $A <= 30));
    $this->assertTrue(($B >= 20 and $B <= 50));
    $this->check_decimals($A, 1, 0.1);
    $this->check_decimals($B, 1, 0.2);
    $qn_text = $this->getText('id=calc3_q');
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $this->assertTrue(($A >= 5 and $A <= 20));
    $this->check_decimals($A, 2, 0.02);

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Calculation 4, tolerance full 1, units = cm, 1 decimal, 2 marks correct, -1 mark incorrect, increment A - 0.1, increment B - 0.2');
    $this->assertTextPresent('Partial Marks');
    $this->assertTextPresent('Note: Calculation 5 notes for students');
    $this->assertTextPresent('Calculation 5 scenario');
    $this->assertTextPresent('Calculation 5, tolerance partial 1, no units, 2 decimals, increment A - 0.02, 1 mark correct, 0.5 marks partial');
    $this->assertTextPresent('Calculation 6, tolerance full 1, tolerance partial 1.5, units = cm, 1 decimal, 2 marks correct, 1 mark partial, increment A - 0.1, increment B - 0.2');
    $this->assertCssCount('css=input[type="text"]', 3);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark)');
    $this->assertElementContainsText('//*[@id="q3_mk"]', '(2 marks)');
    $this->assertElementPresent('id=calc4_q');
    $this->assertElementPresent('id=calc5_q');
    $this->assertElementPresent('id=calc6_q');
    // Check variables in correct range
    $matches = array();
    $qn_text = $this->getText('id=calc4_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $this->assertTrue(($A >= 10 and $A <= 30));
    $this->assertTrue(($B >= 20 and $B <= 50));
    $this->check_decimals($A, 1, 0.1);
    $this->check_decimals($B, 1, 0.2);
    $qn_text = $this->getText('id=calc5_q');
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $this->assertTrue(($A >= 5 and $A <= 20));
    $this->check_decimals($A, 2, 0.02);
    $qn_text = $this->getText('id=calc6_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $this->assertTrue(($A >= 10 and $A <= 30));
    $this->assertTrue(($B >= 20 and $B <= 50));
    $this->check_decimals($A, 1, 0.1);
    $this->check_decimals($B, 1, 0.2);

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Negative marking, Partial Marks');
    $this->assertTextPresent('Note: Calculation 7 notes for students');
    $this->assertTextPresent('Calculation 7 scenario');
    $this->assertTextPresent('Calculation 7, tolerance partial 1, no units, 2 decimals, increment A - 0.02, 1 mark, 0.5 marks partial, -0.5 marks incorrect');
    $this->assertTextPresent('Calculation 8, tolerance full 1, tolerance partial 1.5, units = cm, 1 decimal, 2 marks correct, 1 mark partial, -1 mark incorrect, increment A - 0.1, increment B - 0.2');
    $this->assertTextPresent('Partial Marks, % Based Tolerances');
    $this->assertTextPresent('Note: Calculation 9 notes for students');
    $this->assertTextPresent('Calculation 9 scenario');
    $this->assertTextPresent('Calculation 9, tolerance partial 5%, no units, 2 decimals, increment A - 0.02, 1 mark correct, 0.5 marks partial');
    $this->assertCssCount('css=input[type="text"]', 3);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(1 mark, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(2 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q3_mk"]', '(1 mark)');
    $this->assertElementPresent('id=calc7_q');
    $this->assertElementPresent('id=calc8_q');
    $this->assertElementPresent('id=calc9_q');
    // Check variables in correct range
    $matches = array();
    $qn_text = $this->getText('id=calc7_q');
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $this->assertTrue(($A >= 5 and $A <= 20));
    $this->check_decimals($A, 2, 0.02);
    $qn_text = $this->getText('id=calc8_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $this->assertTrue(($A >= 10 and $A <= 30));
    $this->assertTrue(($B >= 20 and $B <= 50));
    $this->check_decimals($A, 1, 0.1);
    $this->check_decimals($B, 1, 0.2);
    $qn_text = $this->getText('id=calc9_q');
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $this->assertTrue(($A >= 5 and $A <= 20));
    $this->check_decimals($A, 2, 0.02);

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Calculation 10, tolerance full 5%, tolerance partial 8%, units = cm, 1 decimal, 2 marks correct, 1 mark partial, increment A - 0.1, increment B - 0.2');
    $this->assertTextPresent('Negative marking, Partial Marks, % Based Tolerances');
    $this->assertTextPresent('Note: Calculation 11 notes for students');
    $this->assertTextPresent('Calculation 11 scenario');
    $this->assertTextPresent('Calculation 11, tolerance partial 5%, no units, 2 decimals, increment A - 0.02, 1 mark, 0.5 marks partial, -0.5 marks incorrect');
    $this->assertTextPresent('Calculation 12, tolerance full 5%, tolerance partial 8%, units = cm, 1 decimal, 2 marks correct, 1 mark partial, -1 mark incorrect, increment A - 0.1, increment B - 0.2');
    $this->assertCssCount('css=input[type="text"]', 3);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');
    $this->assertElementContainsText('//*[@id="q3_mk"]', '(2 marks, negative marking)');
    $this->assertElementPresent('id=calc10_q');
    $this->assertElementPresent('id=calc11_q');
    $this->assertElementPresent('id=calc12_q');
    // Check variables in correct range
    $matches = array();
    $qn_text = $this->getText('id=calc10_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $this->assertTrue(($A >= 10 and $A <= 30));
    $this->assertTrue(($B >= 20 and $B <= 50));
    $this->check_decimals($A, 1, 0.1);
    $this->check_decimals($B, 1, 0.2);
    $qn_text = $this->getText('id=calc11_q');
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $this->assertTrue(($A >= 5 and $A <= 20));
    $this->check_decimals($A, 2, 0.02);
    $qn_text = $this->getText('id=calc12_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $this->assertTrue(($A >= 10 and $A <= 30));
    $this->assertTrue(($B >= 20 and $B <= 50));
    $this->check_decimals($A, 1, 0.1);
    $this->check_decimals($B, 1, 0.2);

    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=61357920091102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[7]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr[6]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/p/span', '0 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '0 out of 18');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '0.00%');
  }

  public function testCompletePaperCorrect() {
    do_student_login($this, 'teststudent11', 'bkt_66Y4');

    $this->open("/user_index.php?id=61357920091102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc1_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q1", $answer);
    $qn_text = $this->getText('id=calc2_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q2", $answer);
    $qn_text = $this->getText('id=calc3_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q3", $answer);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc4_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q1", $answer);
    $qn_text = $this->getText('id=calc5_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q2", $answer);
    $qn_text = $this->getText('id=calc6_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q3", $answer);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc7_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q1", $answer);
    $qn_text = $this->getText('id=calc8_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q2", $answer);
    $qn_text = $this->getText('id=calc9_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q3", $answer);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc10_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q1", $answer);
    $qn_text = $this->getText('id=calc11_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q2", $answer);
    $qn_text = $this->getText('id=calc12_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q3", $answer);
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=61357920091102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[7]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr[6]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/p/span', '2 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '18 out of 18');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '100.00%');
  }

  public function testCompletePaperCorrectWithTolerance() {
    do_student_login($this, 'teststudent12', 'rmu_74L4');

    $this->open("/user_index.php?id=61357920091102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc1_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q1", $answer);
    $qn_text = $this->getText('id=calc2_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q2", $answer + 1);
    $qn_text = $this->getText('id=calc3_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q3", $answer);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc4_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q1", $answer - 1);
    $qn_text = $this->getText('id=calc5_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q2", $answer);
    $qn_text = $this->getText('id=calc6_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q3", $answer + 1);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc7_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q1", $answer);
    $qn_text = $this->getText('id=calc8_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q2", $answer - 1);
    $qn_text = $this->getText('id=calc9_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q3", $answer);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc10_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    // Store answer and tolerance for comparison later
    $t_vals = array('10' => array($answer, .05, 1));
    $answer = $this->get_answer_with_tolerance($answer, 5);
    $this->type("id=q1", $answer);
    $qn_text = $this->getText('id=calc11_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q2", $answer);
    $qn_text = $this->getText('id=calc12_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    // Store tolerance min/max for comparison later
    $t_vals['12'] = array($answer, .05, 1);
    $answer = $this->get_answer_with_tolerance($answer, 5, false);
    $this->type("id=q3", $answer);
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=61357920091102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[7]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '2 out of 2');
    $text = $this->getText('//table[2]/tbody/tr[4]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr[6]/td[2]/p/span', '2 out of 2');
    $text = $this->getText('//table[3]/tbody/tr[6]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $text = $this->getText('//table[4]/tbody/tr[4]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/table/tbody/tr/td[2]', $this->make_perc_tolerance_text('cm', $t_vals['10'][0], $t_vals['10'][1], $t_vals['10'][2]));
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/table/tbody/tr/td[2]', $this->make_perc_tolerance_text('cm', $t_vals['12'][0], $t_vals['12'][1], $t_vals['12'][2]));

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '18 out of 18');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '100.00%');
  }

  public function testCompletePaperPartialMarks() {
    do_student_login($this, 'teststudent13', 'hii.420R');

    $this->open("/user_index.php?id=61357920091102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc1_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q1", $answer);
    $qn_text = $this->getText('id=calc2_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q2", $answer);
    $qn_text = $this->getText('id=calc3_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q3", $answer);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc4_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q1", $answer);
    $qn_text = $this->getText('id=calc5_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q2", $answer + 1);
    $qn_text = $this->getText('id=calc6_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q3", $answer + 1.5);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc7_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q1", $answer - 1);
    $qn_text = $this->getText('id=calc8_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q2", $answer - 1.5);
    $qn_text = $this->getText('id=calc9_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    // Store answer and tolerance for comparison later
    $t_vals = array('9' => array($answer, .05, 2));
    $answer = $this->get_answer_with_tolerance($answer, 5);
    $this->type("id=q3", $answer);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc10_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    // Store answer and tolerance for comparison later
    $t_vals['10'] = array($answer, .08, 1);
    $answer = $this->get_answer_with_tolerance($answer, 8);
    $this->type("id=q1", $answer);
    $qn_text = $this->getText('id=calc11_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    // Store answer and tolerance for comparison later
    $t_vals['11'] = array($answer, .05, 2);
    $answer = $this->get_answer_with_tolerance($answer, 5, false);
    $this->type("id=q2", $answer);
    $qn_text = $this->getText('id=calc12_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    // Store answer and tolerance for comparison later
    $t_vals['12'] = array($answer, .08, 1);
    $answer = $this->get_answer_with_tolerance($answer, 8, false);
    $this->type("id=q3", $answer);
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=61357920091102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[7]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[4]/span', '0.5 out of 1');
    $text = $this->getText('//table[3]/tbody/tr[4]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[3]/tbody/tr[6]/td[2]/p/span', '1 out of 2');
    $text = $this->getText('//table[3]/tbody/tr[6]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertRegExp('/with a tolerance of 1.5$/', $text);
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[4]/span', '0.5 out of 1');
    $text = $this->getText('//table[4]/tbody/tr[2]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[4]/tbody/tr[4]/td[2]/p/span', '1 out of 2');
    $text = $this->getText('//table[4]/tbody/tr[4]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertRegExp('/with a tolerance of 1.5$/', $text);
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/p[4]/span', '0.5 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/table/tbody/tr/td[2]', $this->make_perc_tolerance_text('', $t_vals['9'][0], $t_vals['9'][1], $t_vals['9'][2]));
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '1 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/table/tbody/tr/td[2]', $this->make_perc_tolerance_text('cm', $t_vals['10'][0], $t_vals['10'][1], $t_vals['10'][2]));
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p[4]/span', '0.5 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/table/tbody/tr/td[2]', $this->make_perc_tolerance_text('', $t_vals['11'][0], $t_vals['11'][1], $t_vals['11'][2]));
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/p/span', '1 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/table/tbody/tr/td[2]', $this->make_perc_tolerance_text('cm', $t_vals['12'][0], $t_vals['12'][1], $t_vals['12'][2]));

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '12 out of 18');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '66.67%');
  }

  public function testCompletePaperIncorrect() {
    do_student_login($this, 'teststudent14', 'mon~61Qt');

    $this->open("/user_index.php?id=61357920091102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc1_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q1", $answer + 2);
    $qn_text = $this->getText('id=calc2_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q2", $answer - 2);
    $qn_text = $this->getText('id=calc3_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q3", $answer + 2);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc4_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q1", $answer - 2);
    $qn_text = $this->getText('id=calc5_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q2", $answer + 2);
    $qn_text = $this->getText('id=calc6_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q3", $answer - 2);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc7_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q1", $answer + 2);
    $qn_text = $this->getText('id=calc8_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q2", $answer - 2);
    $qn_text = $this->getText('id=calc9_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $answer = $answer * 1.06;
    $this->type("id=q3", $answer);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc10_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $answer = $answer * 1.09;
    $this->type("id=q1", $answer);
    $qn_text = $this->getText('id=calc11_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $answer = $answer * 0.94;
    $this->type("id=q2", $answer);
    $qn_text = $this->getText('id=calc12_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $answer = $answer * 0.91;
    $this->type("id=q3", $answer);
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=61357920091102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[7]/td[2]/p[4]/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr[6]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[4]/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[4]/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p[4]/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/p/span', '-1 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '-4.5 out of 18');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '-25.00%');
  }

  public function testCompletePaperMixed() {
    do_student_login($this, 'teststudent15', 'scd=50AH');

    $this->open("/user_index.php?id=61357920091102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc1_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q1", $answer);
    $qn_text = $this->getText('id=calc2_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q2", $answer - 2);
    $qn_text = $this->getText('id=calc3_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q3", $answer + 2);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc4_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q1", $answer - 1);
    $qn_text = $this->getText('id=calc5_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q2", $answer + 1);
    $qn_text = $this->getText('id=calc6_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q3", $answer - 2);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc7_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q1", $answer);
    $qn_text = $this->getText('id=calc8_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    $this->type("id=q2", $answer - 1);
    $qn_text = $this->getText('id=calc9_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $answer = $this->get_answer_with_tolerance($answer, 6);
    $this->type("id=q3", $answer);
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    // Calculate correct answers and input them
    $qn_text = $this->getText('id=calc10_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    // Store answer and tolerance for comparison later
    $t_vals = array('10' => array($answer, .08, 1));
    $answer = $this->get_answer_with_tolerance($answer, 8);
    $this->type("id=q1", $answer);
    $qn_text = $this->getText('id=calc11_q');
    $matches = array();
    preg_match('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1];
    $answer = pow($A, 2) * pi();
    $answer = round($answer, 2);
    $this->type("id=q2", $answer);
    $qn_text = $this->getText('id=calc12_q');
    preg_match_all('/\|\|([0-9\.]*?)\|\|/', $qn_text, $matches);
    $A = $matches[1][0];
    $B = $matches[1][1];
    $answer = sqrt(pow($A, 2) + pow($B, 2));
    $answer = round($answer, 1);
    // Store tolerance min/max for comparison later
    $t_vals['12'] = array($answer, .08, 1);
    $answer = $this->get_answer_with_tolerance($answer, 8, false);
    $this->type("id=q3", $answer);
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=61357920091102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[7]/td[2]/p[4]/span', '-0.5 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '2 out of 2');
    $text = $this->getText('//table[3]/tbody/tr/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p[4]/span', '0.5 out of 1');
    $text = $this->getText('//table[3]/tbody/tr[4]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[3]/tbody/tr[6]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $text = $this->getText('//table[4]/tbody/tr[4]/td[2]/table/tbody/tr/td[2]');
    $this->assertRegExp('/^cm/', $text);
    $this->assertRegExp('/with a tolerance of 1$/', $text);
    $this->assertElementContainsText('//table[4]/tbody/tr[7]/td[2]/p[4]/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '1 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/table/tbody/tr/td[2]', $this->make_perc_tolerance_text('cm', $t_vals['10'][0], $t_vals['10'][1], $t_vals['10'][2]));
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p[4]/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/p/span', '1 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[6]/td[2]/table/tbody/tr/td[2]', $this->make_perc_tolerance_text('cm', $t_vals['12'][0], $t_vals['12'][1], $t_vals['12'][2]));

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '9 out of 18');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '50.00%');
  }

  public function testClassTotals() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=6&startdate=20130101000000&enddate=20230215120000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");

    $this->assertElementContainsText('//tr[@id="res7"]/td[5]', '0');
    $this->assertElementContainsText('//tr[@id="res7"]/td[6]', '0.00%');
    $this->assertElementContainsText('//tr[@id="res7"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res8"]/td[5]', '18');
    $this->assertElementContainsText('//tr[@id="res8"]/td[6]', '100.00%');
    $this->assertElementContainsText('//tr[@id="res8"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res9"]/td[5]', '18');
    $this->assertElementContainsText('//tr[@id="res9"]/td[6]', '100.00%');
    $this->assertElementContainsText('//tr[@id="res9"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res10"]/td[5]', '12');
    $this->assertElementContainsText('//tr[@id="res10"]/td[6]', '66.67%');
    $this->assertElementContainsText('//tr[@id="res10"]/td[7]', 'Pass');

    $this->assertElementContainsText('//tr[@id="res11"]/td[5]', '-4.5');
    $this->assertElementContainsText('//tr[@id="res11"]/td[6]', '-25.00%');
    $this->assertElementContainsText('//tr[@id="res11"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res12"]/td[5]', '9');
    $this->assertElementContainsText('//tr[@id="res12"]/td[6]', '50.00%');
    $this->assertElementContainsText('//tr[@id="res12"]/td[7]', 'Pass');
  }

  private function check_decimals($subject, $count, $increment) {
    $var_parts = explode('.', $subject);
    if (isset($var_parts[1])) {
      $this->assertTrue(strlen($var_parts[1]) == $count);
      if (preg_match('/[2-9]/', $increment)) {  // No point in further testing increments that are basically 1
        // For these tests we're controling that the number of decimals in the increment = number in the variable
        if ($increment == 0.02 or $increment == 0.2) {
          $inc_int = $increment * pow(10, $count);
        } else {
          $inc_int = $increment;
        }
        $this->assertTrue(($var_parts[1] % 2) == 0);  // Increment is correct
      }
    } else {
      $this->assertTrue(false, 'Incorrect decimals');
    }
  }

  /**
   * Get modified version of answer with tolerance added or subtracted
   * @param  float   $answer    Answer as calculated
   * @param  int     $tolerance Tolerance percentage (as integer)
   * @param  boolean $add       Add or subtract tolerance from answer
   * @return float              Answer modified by tolerance percentage
   */
  private function get_answer_with_tolerance($answer, $tolerance, $add=true) {
    $tolerance_val = $answer * ($tolerance/100);

    $answer = ($add) ? $answer + $tolerance_val : $answer - $tolerance_val;
    return $answer;
  }

  private function make_perc_tolerance_text($units, $answer, $tolerance, $decimals) {
    $min = $answer * (1 - $tolerance);
    $max = $answer * (1 + $tolerance);
    $tol_displ = $tolerance * 100;
    $answer = number_format($answer, $decimals, '.', '');

    return trim("$units (" . trim($answer . ' ' . $units) . ") with a tolerance of $tol_displ% ($min - $max)");
  }
}
?>