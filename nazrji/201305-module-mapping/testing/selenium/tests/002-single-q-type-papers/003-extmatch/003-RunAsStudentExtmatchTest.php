<?php
require_once 'shared.inc.php';

class RunAsStudentExtmatchTest extends PHPUnit_Extensions_SeleniumTestCase
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

    $this->open("/user_index.php?id=31357211657102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('Extended Matching Questions');
    $this->assertTextPresent('Note: Extended Matching notes for students');
    $this->assertTextPresent('Ext Match 1, display order, 1 mark, 3 scenarios');
    $this->assertTextPresent('Ext Match 2, alphabetic, 2 marks, 4 scenarios');
    $this->assertCssCount('css=select', 8); // Include page jump DDL
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(3 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(8 marks)');
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@name='q2_1']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_1']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q2_1']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_1']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_1']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_2']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q2_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_3']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q2_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_3']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_4']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_4']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q2_4']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_4']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_4']/option[6]", 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 3, random, 1 mark, 3 scenarios');
    $this->assertTextPresent('Negative Marking');
    $this->assertTextPresent('Ext Match 4, display order, 1 mark correct, -0.5 marks incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(3 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(3 marks, negative marking)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 5, alphabetic, 2 marks correct, -1 mark incorrect, 4 scenarios');
    $this->assertTextPresent('Ext Match 6, random, 1 mark correct, -1 mark incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 8); // Include page jump DDL
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(8 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(3 marks, negative marking)');
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@name='q1_1']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_1']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q1_1']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_1']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_1']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_2']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q1_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_3']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q1_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_3']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_4']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_4']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q1_4']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_4']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_4']/option[6]", 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Mark Per Question');
    $this->assertTextPresent('Ext Match 7, display order, mark per question, 1 mark, 3 scenarios');
    $this->assertTextPresent('Ext Match 8, alphabetic, mark per question, 2 marks, 4 scenarios');
    $this->assertCssCount('css=select', 8); // Include page jump DDL
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(1 mark)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(2 marks)');
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@name='q2_1']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_1']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q2_1']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_1']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_1']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_2']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q2_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_3']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q2_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_3']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_4']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_4']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q2_4']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_4']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_4']/option[6]", 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 9, random, mark per question, 3 marks, 3 scenarios');
    $this->assertTextPresent('Mark Per Question, Negative Marking');
    $this->assertTextPresent('Ext Match 10, display order, mark per question, 1 mark correct, -0.5 marks incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(3 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 11, alphabetic, mark per question, 2 marks correct, -1 mark incorrect, 4 scenarios');
    $this->assertTextPresent('Ext Match 12, random, mark per question, 1 mark correct, -1 mark incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 8); // Include page jump DDL
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@name='q1_1']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_1']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q1_1']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_1']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_1']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_2']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q1_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_3']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q1_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_3']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_4']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_4']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q1_4']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_4']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_4']/option[6]", 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Mixed Multi-Select Questions');
    $this->assertTextPresent('Ext Match 13, display order, 1 mark, 3 scenarios');
    $this->assertTextPresent('Ext Match 14, alphabetic, 2 marks, 4 scenarios');
    $this->assertCssCount('css=select', 8); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 5); // Multi-select boxes
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(5 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(14 marks)');
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@id='q2_1']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_1']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q2_1']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_1']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_1']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_2']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q2_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@id='q2_3']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_3']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q2_3']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_3']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_3']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q2_4']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_4']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q2_4']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_4']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_4']/option[5]", 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 15, random, 1 mark, 3 scenarios');
    $this->assertTextPresent('Mixed Multi-select, Negative Marking');
    $this->assertTextPresent('Ext Match 16, display order, 1 mark correct, -0.5 marks incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 4); // Multi-select boxes
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(5 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(5 marks, negative marking)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 17, alphabetic, 2 marks correct, -1 mark incorrect, 4 scenarios');
    $this->assertTextPresent('Ext Match 18, random, 1 mark correct, -1 mark incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 8); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 5); // Multi-select boxes
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(14 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(5 marks, negative marking)');
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@id='q1_1']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_1']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q1_1']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_1']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_1']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q1_2']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_2']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q1_2']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_2']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_2']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q1_3']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_3']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q1_3']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_3']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_3']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_4']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_4']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q1_4']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_4']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_4']/option[6]", 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Mixed Multi-select, Mark Per Question');
    $this->assertTextPresent('Ext Match 19, display order, mark per question, 1 mark, 3 scenarios');
    $this->assertTextPresent('Ext Match 20, alphabetic, mark per question, 2 marks, 4 scenarios');
    $this->assertCssCount('css=select', 8); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 3); // Multi-select boxes
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(1 mark)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(2 marks)');
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@name='q2_1']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_1']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q2_1']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_1']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_1']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@id='q2_2']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_2']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q2_2']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_2']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_2']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@name='q2_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q2_3']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q2_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q2_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q2_3']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@id='q2_4']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_4']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q2_4']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_4']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_4']/option[5]", 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 21, random, mark per question, 3 marks, 3 scenarios');
    $this->assertTextPresent('Mixed Multi-select, Mark Per Question, Negative Marking');
    $this->assertTextPresent('Ext Match 22, display order, mark per question, 1 mark correct, -0.5 marks incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 4); // Multi-select boxes
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(3 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 23, alphabetic, mark per question, 2 marks correct, -1 mark incorrect, 4 scenarios');
    $this->assertTextPresent('Ext Match 24, random, mark per question, 1 mark correct, -1 mark incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 8); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 4); // Multi-select boxes
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@id='q1_1']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_1']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q1_1']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_1']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_1']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_2']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_2']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q1_2']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_2']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_2']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@name='q1_3']/option[2]", 'Option B');
    $this->assertElementContainsText("//select[@name='q1_3']/option[3]", 'Option C');
    $this->assertElementContainsText("//select[@name='q1_3']/option[4]", 'Option M');
    $this->assertElementContainsText("//select[@name='q1_3']/option[5]", 'Option P');
    $this->assertElementContainsText("//select[@name='q1_3']/option[6]", 'Option X');
    $this->assertElementContainsText("//select[@id='q1_4']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_4']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q1_4']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_4']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_4']/option[5]", 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('All Multi-Select Questions');
    $this->assertTextPresent('Ext Match 25, display order, 1 mark, 3 scenarios');
    $this->assertTextPresent('Ext Match 26, alphabetic, 2 marks, 4 scenarios');
    $this->assertCssCount('css=select', 8); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 7); // Multi-select boxes
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(6 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(16 marks)');
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@id='q2_1']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_1']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q2_1']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_1']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_1']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q2_2']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_2']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q2_2']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_2']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_2']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q2_3']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_3']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q2_3']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_3']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_3']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q2_4']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_4']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q2_4']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_4']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_4']/option[5]", 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 27, random, 1 mark, 3 scenarios');
    $this->assertTextPresent('All Multi-select, Negative Marking');
    $this->assertTextPresent('Ext Match 28, display order, 1 mark correct, -0.5 marks incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 6); // Multi-select boxes
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(6 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(6 marks, negative marking)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 29, alphabetic, 2 marks correct, -1 mark incorrect, 4 scenarios');
    $this->assertTextPresent('Ext Match 30, random, 1 mark correct, -1 mark incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 8); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 7); // Multi-select boxes
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(16 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(6 marks, negative marking)');
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@id='q1_1']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_1']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q1_1']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_1']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_1']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q1_2']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_2']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q1_2']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_2']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_2']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q1_3']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_3']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q1_3']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_3']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_3']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q1_4']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_4']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q1_4']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_4']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_4']/option[5]", 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('All Multi-select, Mark Per Question');
    $this->assertTextPresent('Ext Match 31, display order, mark per question, 1 mark, 3 scenarios');
    $this->assertTextPresent('Ext Match 32, alphabetic, mark per question, 2 marks, 4 scenarios');
    $this->assertCssCount('css=select', 8); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 7); // Multi-select boxes
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(1 mark)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(2 marks)');
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@id='q2_1']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_1']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q2_1']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_1']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_1']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q2_2']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_2']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q2_2']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_2']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_2']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q2_3']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_3']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q2_3']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_3']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_3']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q2_4']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q2_4']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q2_4']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q2_4']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q2_4']/option[5]", 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 33, random, mark per question, 3 marks, 3 scenarios');
    $this->assertTextPresent('All Multi-select, Mark Per Question, Negative Marking');
    $this->assertTextPresent('Ext Match 34, display order, mark per question, 1 mark correct, -0.5 marks incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 7); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 6); // Multi-select boxes
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(3 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Ext Match 35, alphabetic, mark per question, 2 marks correct, -1 mark incorrect, 4 scenarios');
    $this->assertTextPresent('Ext Match 36, random, mark per question, 1 mark correct, -1 mark incorrect, 3 scenarios');
    $this->assertCssCount('css=select', 8); // Include page jump DDL
    $this->assertXpathCount("//select[@multiple='multiple']", 7); // Multi-select boxes
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');
    // Order of alphabetic questions
    $this->assertElementContainsText("//select[@id='q1_1']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_1']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q1_1']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_1']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_1']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q1_2']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_2']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q1_2']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_2']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_2']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q1_3']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_3']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q1_3']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_3']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_3']/option[5]", 'Option X');
    $this->assertElementContainsText("//select[@id='q1_4']/option[1]", 'Option B');
    $this->assertElementContainsText("//select[@id='q1_4']/option[2]", 'Option C');
    $this->assertElementContainsText("//select[@id='q1_4']/option[3]", 'Option M');
    $this->assertElementContainsText("//select[@id='q1_4']/option[4]", 'Option P');
    $this->assertElementContainsText("//select[@id='q1_4']/option[5]", 'Option X');

    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=31357211657102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 3');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[4]/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 8');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[6]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[8]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 5');
    $this->assertElementContainsText('//table[8]/tbody/tr[4]/td[2]/p/span', '0 out of 14');
    $this->assertElementContainsText('//table[9]/tbody/tr/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[9]/tbody/tr[4]/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[10]/tbody/tr/td[2]/p/span', '0 out of 14');
    $this->assertElementContainsText('//table[10]/tbody/tr[3]/td[2]/p/span', '0 out of 5');
    $this->assertElementContainsText('//table[11]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[11]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[12]/tbody/tr/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[12]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[13]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[13]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[14]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 6');
    $this->assertElementContainsText('//table[14]/tbody/tr[4]/td[2]/p/span', '0 out of 16');
    $this->assertElementContainsText('//table[15]/tbody/tr/td[2]/p/span', '0 out of 6');
    $this->assertElementContainsText('//table[15]/tbody/tr[4]/td[2]/p/span', '0 out of 6');
    $this->assertElementContainsText('//table[16]/tbody/tr/td[2]/p/span', '0 out of 16');
    $this->assertElementContainsText('//table[16]/tbody/tr[3]/td[2]/p/span', '0 out of 6');
    $this->assertElementContainsText('//table[17]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 1');
    $this->assertElementContainsText('//table[17]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[18]/tbody/tr/td[2]/p/span', '0 out of 3');
    $this->assertElementContainsText('//table[18]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[19]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[19]/tbody/tr[3]/td[2]/p/span', '0 out of 1');

    // Overall Marks
    $this->assertElementContainsText('//div[19]/table/tbody/tr[2]/td[2]', '0 out of 162');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[4]/td[2]', '0%');
  }

  public function testCompletePaperCorrect() {
    do_student_login($this, 'teststudent11', 'bkt_66Y4');

    $this->open("/user_index.php?id=31357211657102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    $this->select("name=q1_1", "label=A. Option One");
    $this->select("name=q1_2", "label=E. Option Five");
    $this->select("name=q1_3", "label=B. Option Two");
    $this->select("name=q2_1", "label=C. Option M");
    $this->select("name=q2_2", "label=B. Option C");
    $this->select("name=q2_3", "label=A. Option B");
    $this->select("name=q2_4", "label=D. Option P");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "value=3");
    $this->select("name=q1_2", "value=4");
    $this->select("name=q1_3", "value=2");
    $this->select("name=q2_1", "label=A. Option One");
    $this->select("name=q2_2", "label=E. Option Five");
    $this->select("name=q2_3", "label=B. Option Two");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "label=C. Option M");
    $this->select("name=q1_2", "label=B. Option C");
    $this->select("name=q1_3", "label=A. Option B");
    $this->select("name=q1_4", "label=D. Option P");
    $this->select("name=q2_1", "value=3");
    $this->select("name=q2_2", "value=4");
    $this->select("name=q2_3", "value=2");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "label=A. Option One");
    $this->select("name=q1_2", "label=E. Option Five");
    $this->select("name=q1_3", "label=B. Option Two");
    $this->select("name=q2_1", "label=C. Option M");
    $this->select("name=q2_2", "label=B. Option C");
    $this->select("name=q2_3", "label=A. Option B");
    $this->select("name=q2_4", "label=D. Option P");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "value=3");
    $this->select("name=q1_2", "value=4");
    $this->select("name=q1_3", "value=2");
    $this->select("name=q2_1", "label=A. Option One");
    $this->select("name=q2_2", "label=E. Option Five");
    $this->select("name=q2_3", "label=B. Option Two");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "label=C. Option M");
    $this->select("name=q1_2", "label=B. Option C");
    $this->select("name=q1_3", "label=A. Option B");
    $this->select("name=q1_4", "label=D. Option P");
    $this->select("name=q2_1", "value=3");
    $this->select("name=q2_2", "value=4");
    $this->select("name=q2_3", "value=2");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option One");
    $this->addSelection("id=q1_1", "label=C. Option Three");
    $this->addSelection("id=q1_2", "label=D. Option Four");
    $this->addSelection("id=q1_2", "label=E. Option Five");
    $this->select("name=q1_3", "label=B. Option Two");
    $this->addSelection("id=q2_1", "label=C. Option M");
    $this->addSelection("id=q2_1", "label=D. Option P");
    $this->select("name=q2_2", "label=B. Option C");
    $this->addSelection("id=q2_3", "label=A. Option B");
    $this->addSelection("id=q2_3", "label=E. Option X");
    $this->addSelection("id=q2_4", "label=D. Option P");
    $this->addSelection("id=q2_4", "label=E. Option X");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "value=2");
    $this->addSelection("id=q1_2", "value=1");
    $this->addSelection("id=q1_2", "value=4");
    $this->addSelection("id=q1_3", "value=2");
    $this->addSelection("id=q1_3", "value=5");
    $this->addSelection("id=q2_1", "label=A. Option One");
    $this->addSelection("id=q2_1", "label=C. Option Three");
    $this->addSelection("id=q2_2", "label=A. Option One");
    $this->addSelection("id=q2_2", "label=E. Option Five");
    $this->select("name=q2_3", "label=B. Option Two");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=C. Option M");
    $this->addSelection("id=q1_1", "label=E. Option X");
    $this->addSelection("id=q1_2", "label=A. Option B");
    $this->addSelection("id=q1_2", "label=B. Option C");
    $this->addSelection("id=q1_3", "label=A. Option B");
    $this->addSelection("id=q1_3", "label=D. Option P");
    $this->select("name=q1_4", "label=D. Option P");
    $this->addSelection("id=q2_1", "value=3");
    $this->addSelection("id=q2_1", "value=5");
    $this->select("name=q2_2", "value=4");
    $this->addSelection("id=q2_3", "value=2");
    $this->addSelection("id=q2_3", "value=3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option One");
    $this->addSelection("id=q1_1", "label=C. Option Three");
    $this->select("name=q1_2", "label=E. Option Five");
    $this->select("name=q1_3", "label=B. Option Two");
    $this->select("name=q2_1", "label=C. Option M");
    $this->addSelection("id=q2_2", "label=B. Option C");
    $this->addSelection("id=q2_2", "label=C. Option M");
    $this->select("name=q2_3", "label=A. Option B");
    $this->addSelection("id=q2_4", "label=D. Option P");
    $this->addSelection("id=q2_4", "label=E. Option X");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "value=2");
    $this->addSelection("id=q1_1", "value=3");
    $this->addSelection("id=q1_2", "value=4");
    $this->addSelection("id=q1_2", "value=5");
    $this->select("name=q1_3", "value=2");
    $this->addSelection("id=q2_1", "label=A. Option One");
    $this->addSelection("id=q2_1", "label=C. Option Three");
    $this->select("name=q2_2", "label=E. Option Five");
    $this->addSelection("id=q2_3", "label=A. Option One");
    $this->addSelection("id=q2_3", "label=B. Option Two");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=B. Option C");
    $this->addSelection("id=q1_1", "label=C. Option M");
    $this->select("name=q1_2", "label=B. Option C");
    $this->select("name=q1_3", "label=A. Option B");
    $this->addSelection("id=q1_4", "label=D. Option P");
    $this->addSelection("id=q1_4", "label=E. Option X");
    $this->addSelection("id=q2_1", "value=3");
    $this->addSelection("id=q2_1", "value=5");
    $this->addSelection("id=q2_2", "value=1");
    $this->addSelection("id=q2_2", "value=4");
    $this->select("name=q2_3", "value=2");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option One");
    $this->addSelection("id=q1_1", "label=C. Option Three");
    $this->addSelection("id=q1_2", "label=D. Option Four");
    $this->addSelection("id=q1_2", "label=E. Option Five");
    $this->addSelection("id=q1_3", "label=B. Option Two");
    $this->addSelection("id=q1_3", "label=E. Option Five");
    $this->addSelection("id=q2_1", "label=C. Option M");
    $this->addSelection("id=q2_1", "label=D. Option P");
    $this->addSelection("id=q2_2", "label=B. Option C");
    $this->addSelection("id=q2_2", "label=C. Option M");
    $this->addSelection("id=q2_3", "label=A. Option B");
    $this->addSelection("id=q2_3", "label=E. Option X");
    $this->addSelection("id=q2_4", "label=D. Option P");
    $this->addSelection("id=q2_4", "label=E. Option X");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "value=2");
    $this->addSelection("id=q1_1", "value=3");
    $this->addSelection("id=q1_2", "value=1");
    $this->addSelection("id=q1_2", "value=4");
    $this->addSelection("id=q1_3", "value=2");
    $this->addSelection("id=q1_3", "value=5");
    $this->addSelection("id=q2_1", "label=A. Option One");
    $this->addSelection("id=q2_1", "label=C. Option Three");
    $this->addSelection("id=q2_2", "label=A. Option One");
    $this->addSelection("id=q2_2", "label=E. Option Five");
    $this->addSelection("id=q2_3", "label=B. Option Two");
    $this->addSelection("id=q2_3", "label=D. Option Four");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=C. Option M");
    $this->addSelection("id=q1_1", "label=E. Option X");
    $this->addSelection("id=q1_2", "label=A. Option B");
    $this->addSelection("id=q1_2", "label=B. Option C");
    $this->addSelection("id=q1_3", "label=A. Option B");
    $this->addSelection("id=q1_3", "label=D. Option P");
    $this->addSelection("id=q1_4", "label=D. Option P");
    $this->addSelection("id=q1_4", "label=E. Option X");
    $this->addSelection("id=q2_1", "value=3");
    $this->addSelection("id=q2_1", "value=5");
    $this->addSelection("id=q2_2", "value=4");
    $this->addSelection("id=q2_2", "value=5");
    $this->addSelection("id=q2_3", "value=2");
    $this->addSelection("id=q2_3", "value=3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option One");
    $this->addSelection("id=q1_1", "label=C. Option Three");
    $this->addSelection("id=q1_2", "label=C. Option Three");
    $this->addSelection("id=q1_2", "label=E. Option Five");
    $this->addSelection("id=q1_3", "label=B. Option Two");
    $this->addSelection("id=q1_3", "label=D. Option Four");
    $this->addSelection("id=q2_1", "label=C. Option M");
    $this->addSelection("id=q2_1", "label=E. Option X");
    $this->addSelection("id=q2_2", "label=B. Option C");
    $this->addSelection("id=q2_2", "label=C. Option M");
    $this->addSelection("id=q2_3", "label=A. Option B");
    $this->addSelection("id=q2_3", "label=B. Option C");
    $this->addSelection("id=q2_4", "label=D. Option P");
    $this->addSelection("id=q2_4", "label=E. Option X");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "value=2");
    $this->addSelection("id=q1_1", "value=3");
    $this->addSelection("id=q1_2", "value=4");
    $this->addSelection("id=q1_2", "value=5");
    $this->addSelection("id=q1_3", "value=2");
    $this->addSelection("id=q1_3", "value=4");
    $this->addSelection("id=q2_1", "label=A. Option One");
    $this->addSelection("id=q2_1", "label=C. Option Three");
    $this->addSelection("id=q2_2", "label=D. Option Four");
    $this->addSelection("id=q2_2", "label=E. Option Five");
    $this->addSelection("id=q2_3", "label=A. Option One");
    $this->addSelection("id=q2_3", "label=B. Option Two");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=B. Option C");
    $this->addSelection("id=q1_1", "label=C. Option M");
    $this->addSelection("id=q1_2", "label=B. Option C");
    $this->addSelection("id=q1_2", "label=D. Option P");
    $this->addSelection("id=q1_3", "label=A. Option B");
    $this->addSelection("id=q1_3", "label=E. Option X");
    $this->addSelection("id=q1_4", "label=D. Option P");
    $this->addSelection("id=q1_4", "label=E. Option X");
    $this->addSelection("id=q2_1", "value=3");
    $this->addSelection("id=q2_1", "value=5");
    $this->addSelection("id=q2_2", "value=1");
    $this->addSelection("id=q2_2", "value=4");
    $this->addSelection("id=q2_3", "value=2");
    $this->addSelection("id=q2_3", "value=3");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=31357211657102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText("//table[2]/tbody/tr[2]/td[2]/p[2]/span", '3 out of 3');
    $this->assertElementContainsText("//table[2]/tbody/tr[4]/td[2]/p/span", '8 out of 8');
    $this->assertElementContainsText("//table[3]/tbody/tr/td[2]/p/span", '3 out of 3');
    $this->assertElementContainsText("//table[3]/tbody/tr[4]/td[2]/p/span", '3 out of 3');
    $this->assertElementContainsText("//table[4]/tbody/tr/td[2]/p/span", '8 out of 8');
    $this->assertElementContainsText("//table[4]/tbody/tr[3]/td[2]/p/span", '3 out of 3');
    $this->assertElementContainsText("//table[5]/tbody/tr[2]/td[2]/p[2]/span", '1 out of 1');
    $this->assertElementContainsText("//table[5]/tbody/tr[4]/td[2]/p/span", '2 out of 2');
    $this->assertElementContainsText("//table[6]/tbody/tr/td[2]/p/span", '3 out of 3');
    $this->assertElementContainsText("//table[6]/tbody/tr[4]/td[2]/p/span", '1 out of 1');
    $this->assertElementContainsText("//table[7]/tbody/tr/td[2]/p/span", '2 out of 2');
    $this->assertElementContainsText("//table[7]/tbody/tr[3]/td[2]/p/span", '1 out of 1');
    $this->assertElementContainsText("//table[8]/tbody/tr[2]/td[2]/p[2]/span", '5 out of 5');
    $this->assertElementContainsText("//table[8]/tbody/tr[4]/td[2]/p/span", '14 out of 14');
    $this->assertElementContainsText("//table[9]/tbody/tr/td[2]/p/span", '5 out of 5');
    $this->assertElementContainsText("//table[9]/tbody/tr[4]/td[2]/p/span", '5 out of 5');
    $this->assertElementContainsText("//table[10]/tbody/tr/td[2]/p/span", '14 out of 14');
    $this->assertElementContainsText("//table[10]/tbody/tr[3]/td[2]/p/span", '5 out of 5');
    $this->assertElementContainsText("//table[11]/tbody/tr[2]/td[2]/p[2]/span", '1 out of 1');
    $this->assertElementContainsText("//table[11]/tbody/tr[4]/td[2]/p/span", '2 out of 2');
    $this->assertElementContainsText("//table[12]/tbody/tr/td[2]/p/span", '3 out of 3');
    $this->assertElementContainsText("//table[12]/tbody/tr[4]/td[2]/p/span", '1 out of 1');
    $this->assertElementContainsText("//table[13]/tbody/tr/td[2]/p/span", '2 out of 2');
    $this->assertElementContainsText("//table[13]/tbody/tr[3]/td[2]/p/span", '1 out of 1');
    $this->assertElementContainsText("//table[14]/tbody/tr[2]/td[2]/p[2]/span", '6 out of 6');
    $this->assertElementContainsText("//table[14]/tbody/tr[4]/td[2]/p/span", '16 out of 16');
    $this->assertElementContainsText("//table[15]/tbody/tr/td[2]/p/span", '6 out of 6');
    $this->assertElementContainsText("//table[15]/tbody/tr[4]/td[2]/p/span", '6 out of 6');
    $this->assertElementContainsText("//table[16]/tbody/tr/td[2]/p/span", '16 out of 16');
    $this->assertElementContainsText("//table[16]/tbody/tr[3]/td[2]/p/span", '6 out of 6');
    $this->assertElementContainsText("//table[17]/tbody/tr[2]/td[2]/p[2]/span", '1 out of 1');
    $this->assertElementContainsText("//table[17]/tbody/tr[4]/td[2]/p/span", '2 out of 2');
    $this->assertElementContainsText("//table[18]/tbody/tr/td[2]/p/span", '3 out of 3');
    $this->assertElementContainsText("//table[18]/tbody/tr[4]/td[2]/p/span", '1 out of 1');
    $this->assertElementContainsText("//table[19]/tbody/tr/td[2]/p/span", '2 out of 2');
    $this->assertElementContainsText("//table[19]/tbody/tr[3]/td[2]/p/span", '1 out of 1');

    // Overall Marks
    $this->assertElementContainsText("//div[19]/table/tbody/tr[2]/td[2]", '162 out of 162');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText("//div[19]/table/tbody/tr[4]/td[2]", '100.00%');
  }

  public function testCompletePaperIncorrect() {
    do_student_login($this, 'teststudent12', 'rmu_74L4');

    $this->open("/user_index.php?id=31357211657102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->select("name=q1_1", "label=E. Option Five");
    $this->select("name=q1_2", "label=B. Option Two");
    $this->select("name=q1_3", "label=A. Option One");
    $this->select("name=q2_1", "label=B. Option C");
    $this->select("name=q2_2", "label=A. Option B");
    $this->select("name=q2_3", "label=D. Option P");
    $this->select("name=q2_4", "label=C. Option M");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "value=4");
    $this->select("name=q1_2", "value=2");
    $this->select("name=q1_3", "value=3");
    $this->select("name=q2_1", "label=E. Option Five");
    $this->select("name=q2_2", "label=B. Option Two");
    $this->select("name=q2_3", "label=A. Option One");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "label=B. Option C");
    $this->select("name=q1_2", "label=A. Option B");
    $this->select("name=q1_3", "label=D. Option P");
    $this->select("name=q1_4", "label=C. Option M");
    $this->select("name=q2_1", "value=4");
    $this->select("name=q2_2", "value=2");
    $this->select("name=q2_3", "value=3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "label=E. Option Five");
    $this->select("name=q1_2", "label=B. Option Two");
    $this->select("name=q1_3", "label=A. Option One");
    $this->select("name=q2_1", "label=B. Option C");
    $this->select("name=q2_2", "label=A. Option B");
    $this->select("name=q2_3", "label=D. Option P");
    $this->select("name=q2_4", "label=C. Option M");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "value=4");
    $this->select("name=q1_2", "value=2");
    $this->select("name=q1_3", "value=3");
    $this->select("name=q2_1", "label=E. Option Five");
    $this->select("name=q2_2", "label=B. Option Two");
    $this->select("name=q2_3", "label=A. Option One");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "label=B. Option C");
    $this->select("name=q1_2", "label=A. Option B");
    $this->select("name=q1_3", "label=D. Option P");
    $this->select("name=q1_4", "label=C. Option M");
    $this->select("name=q2_1", "value=4");
    $this->select("name=q2_2", "value=2");
    $this->select("name=q2_3", "value=3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=B. Option Two");
    $this->addSelection("id=q1_1", "label=D. Option Four");
    $this->addSelection("id=q1_2", "label=A. Option One");
    $this->addSelection("id=q1_2", "label=B. Option Two");
    $this->select("name=q1_3", "label=C. Option Three");
    $this->addSelection("id=q2_1", "label=A. Option B");
    $this->addSelection("id=q2_1", "label=B. Option C");
    $this->select("name=q2_2", "label=A. Option B");
    $this->addSelection("id=q2_3", "label=B. Option C");
    $this->addSelection("id=q2_3", "label=C. Option M");
    $this->addSelection("id=q2_4", "label=A. Option B");
    $this->addSelection("id=q2_4", "label=B. Option C");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "value=3");
    $this->addSelection("id=q1_2", "value=3");
    $this->addSelection("id=q1_2", "value=2");
    $this->addSelection("id=q1_3", "value=3");
    $this->addSelection("id=q1_3", "value=1");
    $this->addSelection("id=q2_1", "label=B. Option Two");
    $this->addSelection("id=q2_1", "label=D. Option Four");
    $this->addSelection("id=q2_2", "label=B. Option Two");
    $this->addSelection("id=q2_2", "label=C. Option Three");
    $this->select("name=q2_3", "label=A. Option One");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option B");
    $this->addSelection("id=q1_1", "label=B. Option C");
    $this->addSelection("id=q1_2", "label=C. Option M");
    $this->addSelection("id=q1_2", "label=E. Option X");
    $this->addSelection("id=q1_3", "label=B. Option C");
    $this->addSelection("id=q1_3", "label=E. Option X");
    $this->select("name=q1_4", "label=A. Option B");
    $this->addSelection("id=q2_1", "value=1");
    $this->addSelection("id=q2_1", "value=2");
    $this->select("name=q2_2", "value=1");
    $this->addSelection("id=q2_3", "value=1");
    $this->addSelection("id=q2_3", "value=5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=B. Option Two");
    $this->addSelection("id=q1_1", "label=D. Option Four");
    $this->select("name=q1_2", "label=A. Option One");
    $this->select("name=q1_3", "label=C. Option Three");
    $this->select("name=q2_1", "label=A. Option B");
    $this->addSelection("id=q2_2", "label=A. Option B");
    $this->addSelection("id=q2_2", "label=D. Option P");
    $this->select("name=q2_3", "label=B. Option C");
    $this->addSelection("id=q2_4", "label=A. Option B");
    $this->addSelection("id=q2_4", "label=B. Option C");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "value=4");
    $this->addSelection("id=q1_1", "value=5");
    $this->addSelection("id=q1_2", "value=1");
    $this->addSelection("id=q1_2", "value=2");
    $this->select("name=q1_3", "value=5");
    $this->addSelection("id=q2_1", "label=B. Option Two");
    $this->addSelection("id=q2_1", "label=D. Option Four");
    $this->select("name=q2_2", "label=A. Option One");
    $this->addSelection("id=q2_3", "label=C. Option Three");
    $this->addSelection("id=q2_3", "label=D. Option Four");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option B");
    $this->addSelection("id=q1_1", "label=D. Option P");
    $this->select("name=q1_2", "label=A. Option B");
    $this->select("name=q1_3", "label=B. Option C");
    $this->addSelection("id=q1_4", "label=A. Option B");
    $this->addSelection("id=q1_4", "label=C. Option M");
    $this->addSelection("id=q2_1", "value=1");
    $this->addSelection("id=q2_1", "value=2");
    $this->addSelection("id=q2_2", "value=2");
    $this->addSelection("id=q2_2", "value=3");
    $this->select("name=q2_3", "value=1");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=B. Option Two");
    $this->addSelection("id=q1_1", "label=E. Option Five");
    $this->addSelection("id=q1_2", "label=A. Option One");
    $this->addSelection("id=q1_2", "label=B. Option Two");
    $this->addSelection("id=q1_3", "label=C. Option Three");
    $this->addSelection("id=q1_3", "label=D. Option Four");
    $this->addSelection("id=q2_1", "label=A. Option B");
    $this->addSelection("id=q2_1", "label=B. Option C");
    $this->addSelection("id=q2_2", "label=D. Option P");
    $this->addSelection("id=q2_2", "label=E. Option X");
    $this->addSelection("id=q2_3", "label=B. Option C");
    $this->addSelection("id=q2_3", "label=C. Option M");
    $this->addSelection("id=q2_4", "label=A. Option B");
    $this->addSelection("id=q2_4", "label=B. Option C");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "value=4");
    $this->addSelection("id=q1_1", "value=5");
    $this->addSelection("id=q1_2", "value=3");
    $this->addSelection("id=q1_2", "value=5");
    $this->addSelection("id=q1_3", "value=1");
    $this->addSelection("id=q1_3", "value=3");
    $this->addSelection("id=q2_1", "label=B. Option Two");
    $this->addSelection("id=q2_1", "label=D. Option Four");
    $this->addSelection("id=q2_2", "label=B. Option Two");
    $this->addSelection("id=q2_2", "label=C. Option Three");
    $this->addSelection("id=q2_3", "label=A. Option One");
    $this->addSelection("id=q2_3", "label=E. Option Five");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option B");
    $this->addSelection("id=q1_1", "label=B. Option C");
    $this->addSelection("id=q1_2", "label=C. Option M");
    $this->addSelection("id=q1_2", "label=D. Option P");
    $this->addSelection("id=q1_3", "label=B. Option C");
    $this->addSelection("id=q1_3", "label=E. Option X");
    $this->addSelection("id=q1_4", "label=A. Option B");
    $this->addSelection("id=q1_4", "label=B. Option C");
    $this->addSelection("id=q2_1", "value=1");
    $this->addSelection("id=q2_1", "value=4");
    $this->addSelection("id=q2_2", "value=3");
    $this->addSelection("id=q2_2", "value=1");
    $this->addSelection("id=q2_3", "value=1");
    $this->addSelection("id=q2_3", "value=5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=B. Option Two");
    $this->addSelection("id=q1_1", "label=D. Option Four");
    $this->addSelection("id=q1_2", "label=A. Option One");
    $this->addSelection("id=q1_2", "label=B. Option Two");
    $this->addSelection("id=q1_3", "label=A. Option One");
    $this->addSelection("id=q1_3", "label=C. Option Three");
    $this->addSelection("id=q2_1", "label=A. Option B");
    $this->addSelection("id=q2_1", "label=B. Option C");
    $this->addSelection("id=q2_2", "label=A. Option B");
    $this->addSelection("id=q2_2", "label=D. Option P");
    $this->addSelection("id=q2_3", "label=C. Option M");
    $this->addSelection("id=q2_3", "label=E. Option X");
    $this->addSelection("id=q2_4", "label=A. Option B");
    $this->addSelection("id=q2_4", "label=C. Option M");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "value=4");
    $this->addSelection("id=q1_1", "value=5");
    $this->addSelection("id=q1_2", "value=1");
    $this->addSelection("id=q1_2", "value=2");
    $this->addSelection("id=q1_3", "value=3");
    $this->addSelection("id=q1_3", "value=5");
    $this->addSelection("id=q2_1", "label=D. Option Four");
    $this->addSelection("id=q2_1", "label=E. Option Five");
    $this->addSelection("id=q2_2", "label=A. Option One");
    $this->addSelection("id=q2_2", "label=B. Option Two");
    $this->addSelection("id=q2_3", "label=C. Option Three");
    $this->addSelection("id=q2_3", "label=D. Option Four");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=D. Option P");
    $this->addSelection("id=q1_1", "label=E. Option X");
    $this->addSelection("id=q1_2", "label=C. Option M");
    $this->addSelection("id=q1_2", "label=E. Option X");
    $this->addSelection("id=q1_3", "label=B. Option C");
    $this->addSelection("id=q1_3", "label=C. Option M");
    $this->addSelection("id=q1_4", "label=A. Option B");
    $this->addSelection("id=q1_4", "label=B. Option C");
    $this->addSelection("id=q2_1", "value=1");
    $this->addSelection("id=q2_1", "value=2");
    $this->addSelection("id=q2_2", "value=2");
    $this->addSelection("id=q2_2", "value=3");
    $this->addSelection("id=q2_3", "value=4");
    $this->addSelection("id=q2_3", "value=5");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=31357211657102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText("//table[2]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 3');
    $this->assertElementContainsText("//table[2]/tbody/tr[4]/td[2]/p/span", '0 out of 8');
    $this->assertElementContainsText("//table[3]/tbody/tr/td[2]/p/span", '0 out of 3');
    $this->assertElementContainsText("//table[3]/tbody/tr[4]/td[2]/p/span", '-1.5 out of 3');
    $this->assertElementContainsText("//table[4]/tbody/tr/td[2]/p/span", '-4 out of 8');
    $this->assertElementContainsText("//table[4]/tbody/tr[3]/td[2]/p/span", '-3 out of 3');
    $this->assertElementContainsText("//table[5]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 1');
    $this->assertElementContainsText("//table[5]/tbody/tr[4]/td[2]/p/span", '0 out of 2');
    $this->assertElementContainsText("//table[6]/tbody/tr/td[2]/p/span", '0 out of 3');
    $this->assertElementContainsText("//table[6]/tbody/tr[4]/td[2]/p/span", '-0.5 out of 1');
    $this->assertElementContainsText("//table[7]/tbody/tr/td[2]/p/span", '-1 out of 2');
    $this->assertElementContainsText("//table[7]/tbody/tr[3]/td[2]/p/span", '-1 out of 1');
    $this->assertElementContainsText("//table[8]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 5');
    $this->assertElementContainsText("//table[8]/tbody/tr[4]/td[2]/p/span", '0 out of 14');
    $this->assertElementContainsText("//table[9]/tbody/tr/td[2]/p/span", '0 out of 5');
    $this->assertElementContainsText("//table[9]/tbody/tr[4]/td[2]/p/span", '-2.5 out of 5');
    $this->assertElementContainsText("//table[10]/tbody/tr/td[2]/p/span", '-7 out of 14');
    $this->assertElementContainsText("//table[10]/tbody/tr[3]/td[2]/p/span", '-5 out of 5');
    $this->assertElementContainsText("//table[11]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 1');
    $this->assertElementContainsText("//table[11]/tbody/tr[4]/td[2]/p/span", '0 out of 2');
    $this->assertElementContainsText("//table[12]/tbody/tr/td[2]/p/span", '0 out of 3');
    $this->assertElementContainsText("//table[12]/tbody/tr[4]/td[2]/p/span", '-0.5 out of 1');
    $this->assertElementContainsText("//table[13]/tbody/tr/td[2]/p/span", '-1 out of 2');
    $this->assertElementContainsText("//table[13]/tbody/tr[3]/td[2]/p/span", '-1 out of 1');
    $this->assertElementContainsText("//table[14]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 6');
    $this->assertElementContainsText("//table[14]/tbody/tr[4]/td[2]/p/span", '0 out of 16');
    $this->assertElementContainsText("//table[15]/tbody/tr/td[2]/p/span", '0 out of 6');
    $this->assertElementContainsText("//table[15]/tbody/tr[4]/td[2]/p/span", '-3 out of 6');
    $this->assertElementContainsText("//table[16]/tbody/tr/td[2]/p/span", '-8 out of 16');
    $this->assertElementContainsText("//table[16]/tbody/tr[3]/td[2]/p/span", '-6 out of 6');
    $this->assertElementContainsText("//table[17]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 1');
    $this->assertElementContainsText("//table[17]/tbody/tr[4]/td[2]/p/span", '0 out of 2');
    $this->assertElementContainsText("//table[18]/tbody/tr/td[2]/p/span", '0 out of 3');
    $this->assertElementContainsText("//table[18]/tbody/tr[4]/td[2]/p/span", '-0.5 out of 1');
    $this->assertElementContainsText("//table[19]/tbody/tr/td[2]/p/span", '-1 out of 2');
    $this->assertElementContainsText("//table[19]/tbody/tr[3]/td[2]/p/span", '-1 out of 1');

    // Overall Marks
    $this->assertElementContainsText("//div[19]/table/tbody/tr[2]/td[2]", '-47.5 out of 162');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText("//div[19]/table/tbody/tr[4]/td[2]", '-29.32%');
  }

  public function testCompletePaperMixed() {
    do_student_login($this, 'teststudent13', 'hii.420R');

    $this->open("/user_index.php?id=31357211657102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->select("name=q1_1", "label=A. Option One");
    $this->select("name=q1_2", "label=A. Option One");
    $this->select("name=q1_3", "label=B. Option Two");
    $this->select("name=q2_1", "label=A. Option B");
    $this->select("name=q2_2", "label=B. Option C");
    $this->select("name=q2_3", "label=B. Option C");
    $this->select("name=q2_4", "label=D. Option P");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "value=4");
    $this->select("name=q1_2", "value=4");
    $this->select("name=q1_3", "value=3");
    $this->select("name=q2_1", "label=B. Option Two");
    $this->select("name=q2_2", "label=E. Option Five");
    $this->select("name=q2_3", "label=A. Option One");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "label=C. Option M");
    $this->select("name=q1_2", "label=A. Option B");
    $this->select("name=q1_3", "label=A. Option B");
    $this->select("name=q1_4", "label=A. Option B");
    $this->select("name=q2_1", "value=3");
    $this->select("name=q2_2", "value=1");
    $this->select("name=q2_3", "value=2");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "label=B. Option Two");
    $this->select("name=q1_2", "label=E. Option Five");
    $this->select("name=q1_3", "label=A. Option One");
    $this->select("name=q2_1", "label=C. Option M");
    $this->select("name=q2_2", "label=C. Option M");
    $this->select("name=q2_3", "label=A. Option B");
    $this->select("name=q2_4", "label=A. Option B");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "value=3");
    $this->select("name=q1_2", "value=4");
    $this->select("name=q1_3", "value=2");
    $this->select("name=q2_1", "label=A. Option One");
    $this->select("name=q2_2", "label=E. Option Five");
    $this->select("name=q2_3", "label=B. Option Two");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "label=A. Option B");
    $this->select("name=q1_2", "label=A. Option B");
    $this->select("name=q1_3", "label=B. Option C");
    $this->select("name=q1_4", "label=A. Option B");
    $this->select("name=q2_1", "value=1");
    $this->select("name=q2_2", "value=1");
    $this->select("name=q2_3", "value=1");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option One");
    $this->addSelection("id=q1_1", "label=C. Option Three");
    $this->addSelection("id=q1_2", "label=A. Option One");
    $this->addSelection("id=q1_2", "label=B. Option Two");
    $this->select("name=q1_3", "label=B. Option Two");
    $this->addSelection("id=q2_1", "label=C. Option M");
    $this->addSelection("id=q2_1", "label=B. Option C");
    $this->select("name=q2_2", "label=B. Option C");
    $this->addSelection("id=q2_3", "label=B. Option C");
    $this->addSelection("id=q2_3", "label=E. Option X");
    $this->addSelection("id=q2_4", "label=C. Option M");
    $this->addSelection("id=q2_4", "label=E. Option X");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "value=2");
    $this->addSelection("id=q1_2", "value=1");
    $this->addSelection("id=q1_2", "value=4");
    $this->addSelection("id=q1_3", "value=3");
    $this->addSelection("id=q1_3", "value=4");
    $this->addSelection("id=q2_1", "label=A. Option One");
    $this->addSelection("id=q2_1", "label=B. Option Two");
    $this->addSelection("id=q2_2", "label=A. Option One");
    $this->addSelection("id=q2_2", "label=B. Option Two");
    $this->select("name=q2_3", "label=B. Option Two");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option B");
    $this->addSelection("id=q1_1", "label=E. Option X");
    $this->addSelection("id=q1_2", "label=D. Option P");
    $this->addSelection("id=q1_2", "label=E. Option X");
    $this->addSelection("id=q1_3", "label=A. Option B");
    $this->addSelection("id=q1_3", "label=D. Option P");
    $this->select("name=q1_4", "label=A. Option B");
    $this->addSelection("id=q2_1", "value=3");
    $this->addSelection("id=q2_1", "value=4");
    $this->select("name=q2_2", "value=4");
    $this->addSelection("id=q2_3", "value=1");
    $this->addSelection("id=q2_3", "value=3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option One");
    $this->addSelection("id=q1_1", "label=C. Option Three");
    $this->select("name=q1_2", "label=E. Option Five");
    $this->select("name=q1_3", "label=A. Option One");
    $this->select("name=q2_1", "label=C. Option M");
    $this->addSelection("id=q2_2", "label=A. Option B");
    $this->addSelection("id=q2_2", "label=E. Option X");
    $this->select("name=q2_3", "label=A. Option B");
    $this->addSelection("id=q2_4", "label=D. Option P");
    $this->addSelection("id=q2_4", "label=E. Option X");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "value=2");
    $this->addSelection("id=q1_1", "value=1");
    $this->addSelection("id=q1_2", "value=4");
    $this->addSelection("id=q1_2", "value=1");
    $this->select("name=q1_3", "value=2");
    $this->addSelection("id=q2_1", "label=A. Option One");
    $this->addSelection("id=q2_1", "label=C. Option Three");
    $this->select("name=q2_2", "label=E. Option Five");
    $this->addSelection("id=q2_3", "label=A. Option One");
    $this->addSelection("id=q2_3", "label=B. Option Two");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=D. Option P");
    $this->addSelection("id=q1_1", "label=E. Option X");
    $this->select("name=q1_2", "label=A. Option B");
    $this->select("name=q1_3", "label=B. Option C");
    $this->addSelection("id=q1_4", "label=A. Option B");
    $this->addSelection("id=q1_4", "label=B. Option C");
    $this->addSelection("id=q2_1", "value=3");
    $this->addSelection("id=q2_1", "value=5");
    $this->addSelection("id=q2_2", "value=2");
    $this->addSelection("id=q2_2", "value=3");
    $this->select("name=q2_3", "value=2");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option One");
    $this->addSelection("id=q1_1", "label=B. Option Two");
    $this->addSelection("id=q1_2", "label=D. Option Four");
    $this->addSelection("id=q1_2", "label=C. Option Three");
    $this->addSelection("id=q1_3", "label=B. Option Two");
    $this->addSelection("id=q1_3", "label=C. Option Three");
    $this->addSelection("id=q2_1", "label=C. Option M");
    $this->addSelection("id=q2_1", "label=D. Option P");
    $this->addSelection("id=q2_2", "label=A. Option B");
    $this->addSelection("id=q2_2", "label=E. Option X");
    $this->addSelection("id=q2_3", "label=A. Option B");
    $this->addSelection("id=q2_3", "label=E. Option X");
    $this->addSelection("id=q2_4", "label=A. Option B");
    $this->addSelection("id=q2_4", "label=B. Option C");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "value=1");
    $this->addSelection("id=q1_1", "value=4");
    $this->addSelection("id=q1_2", "value=1");
    $this->addSelection("id=q1_2", "value=5");
    $this->addSelection("id=q1_3", "value=2");
    $this->addSelection("id=q1_3", "value=1");
    $this->addSelection("id=q2_1", "label=A. Option One");
    $this->addSelection("id=q2_1", "label=B. Option Two");
    $this->addSelection("id=q2_2", "label=A. Option One");
    $this->addSelection("id=q2_2", "label=B. Option Two");
    $this->addSelection("id=q2_3", "label=B. Option Two");
    $this->addSelection("id=q2_3", "label=C. Option Three");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=C. Option M");
    $this->addSelection("id=q1_1", "label=D. Option P");
    $this->addSelection("id=q1_2", "label=A. Option B");
    $this->addSelection("id=q1_2", "label=B. Option C");
    $this->addSelection("id=q1_3", "label=B. Option C");
    $this->addSelection("id=q1_3", "label=E. Option X");
    $this->addSelection("id=q1_4", "label=D. Option P");
    $this->addSelection("id=q1_4", "label=C. Option M");
    $this->addSelection("id=q2_1", "value=3");
    $this->addSelection("id=q2_1", "value=4");
    $this->addSelection("id=q2_2", "value=2");
    $this->addSelection("id=q2_2", "value=5");
    $this->addSelection("id=q2_3", "value=2");
    $this->addSelection("id=q2_3", "value=5");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option One");
    $this->addSelection("id=q1_1", "label=C. Option Three");
    $this->addSelection("id=q1_2", "label=A. Option One");
    $this->addSelection("id=q1_2", "label=B. Option Two");
    $this->addSelection("id=q1_3", "label=B. Option Two");
    $this->addSelection("id=q1_3", "label=D. Option Four");
    $this->addSelection("id=q2_1", "label=C. Option M");
    $this->addSelection("id=q2_1", "label=D. Option P");
    $this->addSelection("id=q2_2", "label=B. Option C");
    $this->addSelection("id=q2_2", "label=C. Option M");
    $this->addSelection("id=q2_3", "label=B. Option C");
    $this->addSelection("id=q2_3", "label=A. Option B");
    $this->addSelection("id=q2_4", "label=D. Option P");
    $this->addSelection("id=q2_4", "label=E. Option X");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "value=2");
    $this->addSelection("id=q1_1", "value=1");
    $this->addSelection("id=q1_2", "value=4");
    $this->addSelection("id=q1_2", "value=2");
    $this->addSelection("id=q1_3", "value=2");
    $this->addSelection("id=q1_3", "value=1");
    $this->addSelection("id=q2_1", "label=A. Option One");
    $this->addSelection("id=q2_1", "label=C. Option Three");
    $this->addSelection("id=q2_2", "label=D. Option Four");
    $this->addSelection("id=q2_2", "label=E. Option Five");
    $this->addSelection("id=q2_3", "label=A. Option One");
    $this->addSelection("id=q2_3", "label=B. Option Two");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option B");
    $this->addSelection("id=q1_1", "label=D. Option P");
    $this->addSelection("id=q1_2", "label=A. Option B");
    $this->addSelection("id=q1_2", "label=C. Option M");
    $this->addSelection("id=q1_3", "label=B. Option C");
    $this->addSelection("id=q1_3", "label=C. Option M");
    $this->addSelection("id=q1_4", "label=A. Option B");
    $this->addSelection("id=q1_4", "label=B. Option C");
    $this->addSelection("id=q2_1", "value=3");
    $this->addSelection("id=q2_1", "value=5");
    $this->addSelection("id=q2_2", "value=3");
    $this->addSelection("id=q2_2", "value=2");
    $this->addSelection("id=q2_3", "value=2");
    $this->addSelection("id=q2_3", "value=3");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=31357211657102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText("//table[2]/tbody/tr[2]/td[2]/p[2]/span", '2 out of 3');
    $this->assertElementContainsText("//table[2]/tbody/tr[4]/td[2]/p/span", '4 out of 8');
    $this->assertElementContainsText("//table[3]/tbody/tr/td[2]/p/span", '1 out of 3');
    $this->assertElementContainsText("//table[3]/tbody/tr[4]/td[2]/p/span", '0 out of 3');
    $this->assertElementContainsText("//table[4]/tbody/tr/td[2]/p/span", '2 out of 8');
    $this->assertElementContainsText("//table[4]/tbody/tr[3]/td[2]/p/span", '1 out of 3');
    $this->assertElementContainsText("//table[5]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 1');
    $this->assertElementContainsText("//table[5]/tbody/tr[4]/td[2]/p/span", '0 out of 2');
    $this->assertElementContainsText("//table[6]/tbody/tr/td[2]/p/span", '3 out of 3');
    $this->assertElementContainsText("//table[6]/tbody/tr[4]/td[2]/p/span", '1 out of 1');
    $this->assertElementContainsText("//table[7]/tbody/tr/td[2]/p/span", '-1 out of 2');
    $this->assertElementContainsText("//table[7]/tbody/tr[3]/td[2]/p/span", '-1 out of 1');
    $this->assertElementContainsText("//table[8]/tbody/tr[2]/td[2]/p[2]/span", '3 out of 5');
    $this->assertElementContainsText("//table[8]/tbody/tr[4]/td[2]/p/span", '8 out of 14');
    $this->assertElementContainsText("//table[9]/tbody/tr/td[2]/p/span", '3 out of 5');
    $this->assertElementContainsText("//table[9]/tbody/tr[4]/td[2]/p/span", '2 out of 5');
    $this->assertElementContainsText("//table[10]/tbody/tr/td[2]/p/span", '2 out of 14');
    $this->assertElementContainsText("//table[10]/tbody/tr[3]/td[2]/p/span", '1 out of 5');
    $this->assertElementContainsText("//table[11]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 1');
    $this->assertElementContainsText("//table[11]/tbody/tr[4]/td[2]/p/span", '0 out of 2');
    $this->assertElementContainsText("//table[12]/tbody/tr/td[2]/p/span", '0 out of 3');
    $this->assertElementContainsText("//table[12]/tbody/tr[4]/td[2]/p/span", '1 out of 1');
    $this->assertElementContainsText("//table[13]/tbody/tr/td[2]/p/span", '-1 out of 2');
    $this->assertElementContainsText("//table[13]/tbody/tr[3]/td[2]/p/span", '-1 out of 1');
    $this->assertElementContainsText("//table[14]/tbody/tr[2]/td[2]/p[2]/span", '3 out of 6');
    $this->assertElementContainsText("//table[14]/tbody/tr[4]/td[2]/p/span", '8 out of 16');
    $this->assertElementContainsText("//table[15]/tbody/tr/td[2]/p/span", '2 out of 6');
    $this->assertElementContainsText("//table[15]/tbody/tr[4]/td[2]/p/span", '1.5 out of 6');
    $this->assertElementContainsText("//table[16]/tbody/tr/td[2]/p/span", '4 out of 16');
    $this->assertElementContainsText("//table[16]/tbody/tr[3]/td[2]/p/span", '0 out of 6');
    $this->assertElementContainsText("//table[17]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 1');
    $this->assertElementContainsText("//table[17]/tbody/tr[4]/td[2]/p/span", '0 out of 2');
    $this->assertElementContainsText("//table[18]/tbody/tr/td[2]/p/span", '0 out of 3');
    $this->assertElementContainsText("//table[18]/tbody/tr[4]/td[2]/p/span", '1 out of 1');
    $this->assertElementContainsText("//table[19]/tbody/tr/td[2]/p/span", '-1 out of 2');
    $this->assertElementContainsText("//table[19]/tbody/tr[3]/td[2]/p/span", '-1 out of 1');

    // Overall Marks
    $this->assertElementContainsText("//div[19]/table/tbody/tr[2]/td[2]", '47.5 out of 162');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText("//div[19]/table/tbody/tr[4]/td[2]", '29.32%');
  }

  public function testCompletePaperPartialAnswers() {
    do_student_login($this, 'teststudent14', 'mon~61Qt');

    $this->open("/user_index.php?id=31357211657102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    $this->select("name=q1_2", "label=E. Option Five");
    $this->select("name=q1_3", "label=B. Option Two");
    $this->select("name=q2_1", "label=A. Option B");
    $this->select("name=q2_3", "label=E. Option X");
    $this->select("name=q2_4", "label=C. Option M");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "value=3");
    $this->select("name=q1_2", "value=3");
    $this->select("name=q2_1", "label=B. Option Two");
    $this->select("name=q2_3", "label=A. Option One");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "label=A. Option B");
    $this->select("name=q1_2", "label=E. Option X");
    $this->select("name=q1_3", "label=B. Option C");
    $this->select("name=q2_2", "value=4");
    $this->select("name=q2_3", "value=2");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "label=A. Option One");
    $this->select("name=q1_3", "label=E. Option Five");
    $this->select("name=q2_1", "label=C. Option M");
    $this->select("name=q2_2", "label=B. Option C");
    $this->select("name=q2_4", "label=D. Option P");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_2", "value=3");
    $this->select("name=q1_3", "value=3");
    $this->select("name=q2_1", "label=A. Option One");
    $this->select("name=q2_3", "label=B. Option Two");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_2", "label=B. Option C");
    $this->select("name=q1_3", "label=B. Option C");
    $this->select("name=q1_4", "label=B. Option C");
    $this->select("name=q2_1", "value=2");
    $this->select("name=q2_2", "value=2");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option One");
    $this->addSelection("id=q1_1", "label=C. Option Three");
    $this->select("name=q1_3", "label=B. Option Two");
    $this->select("name=q2_2", "label=A. Option B");
    $this->addSelection("id=q2_3", "label=C. Option M");
    $this->addSelection("id=q2_3", "label=D. Option P");
    $this->addSelection("id=q2_4", "label=A. Option B");
    $this->addSelection("id=q2_4", "label=B. Option C");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1_1", "value=5");
    $this->addSelection("id=q1_2", "value=1");
    $this->addSelection("id=q1_2", "value=4");
    $this->addSelection("id=q2_2", "label=B. Option Two");
    $this->addSelection("id=q2_2", "label=C. Option Three");
    $this->select("name=q2_3", "label=E. Option Five");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=C. Option M");
    $this->addSelection("id=q1_1", "label=E. Option X");
    $this->addSelection("id=q1_2", "label=D. Option P");
    $this->addSelection("id=q1_2", "label=E. Option X");
    $this->select("name=q1_4", "label=D. Option P");
    $this->select("name=q2_2", "value=4");
    $this->addSelection("id=q2_3", "value=2");
    $this->addSelection("id=q2_3", "value=3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option One");
    $this->addSelection("id=q1_1", "label=C. Option Three");
    $this->select("name=q1_3", "label=A. Option One");
    $this->select("name=q2_1", "label=C. Option M");
    $this->addSelection("id=q2_2", "label=B. Option C");
    $this->addSelection("id=q2_2", "label=C. Option M");
    $this->addSelection("id=q2_4", "label=D. Option P");
    $this->addSelection("id=q2_4", "label=E. Option X");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_2", "value=2");
    $this->addSelection("id=q1_2", "value=3");
    $this->select("name=q1_3", "value=3");
    $this->addSelection("id=q2_1", "label=A. Option One");
    $this->addSelection("id=q2_1", "label=C. Option Three");
    $this->select("name=q2_2", "label=E. Option Five");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=B. Option C");
    $this->addSelection("id=q1_1", "label=C. Option M");
    $this->select("name=q1_3", "label=A. Option B");
    $this->addSelection("id=q1_4", "label=D. Option P");
    $this->addSelection("id=q1_4", "label=E. Option X");
    $this->addSelection("id=q2_2", "value=2");
    $this->addSelection("id=q2_2", "value=5");
    $this->select("name=q2_3", "value=3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option One");
    $this->addSelection("id=q1_2", "label=E. Option Five");
    $this->addSelection("id=q1_3", "label=B. Option Two");
    $this->addSelection("id=q1_3", "label=E. Option Five");
    $this->addSelection("id=q2_1", "label=A. Option B");
    $this->addSelection("id=q2_1", "label=B. Option C");
    $this->addSelection("id=q2_2", "label=D. Option P");
    $this->addSelection("id=q2_3", "label=B. Option C");
    $this->addSelection("id=q2_3", "label=D. Option P");
    $this->addSelection("id=q2_4", "label=A. Option B");
    $this->addSelection("id=q2_4", "label=B. Option C");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "value=2");
    $this->addSelection("id=q1_2", "value=3");
    $this->addSelection("id=q1_3", "value=4");
    $this->addSelection("id=q1_3", "value=2");
    $this->addSelection("id=q2_1", "label=B. Option Two");
    $this->addSelection("id=q2_1", "label=E. Option Five");
    $this->addSelection("id=q2_2", "label=C. Option Three");
    $this->addSelection("id=q2_3", "label=C. Option Three");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=C. Option M");
    $this->addSelection("id=q1_2", "label=B. Option C");
    $this->addSelection("id=q1_3", "label=E. Option X");
    $this->addSelection("id=q1_4", "label=B. Option C");
    $this->addSelection("id=q2_1", "value=3");
    $this->addSelection("id=q2_1", "value=5");
    $this->addSelection("id=q2_2", "value=5");
    $this->addSelection("id=q2_3", "value=3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=A. Option One");
    $this->addSelection("id=q1_2", "label=A. Option One");
    $this->addSelection("id=q1_2", "label=B. Option Two");
    $this->addSelection("id=q1_3", "label=C. Option Three");
    $this->addSelection("id=q2_1", "label=E. Option X");
    $this->addSelection("id=q2_2", "label=B. Option C");
    $this->addSelection("id=q2_2", "label=C. Option M");
    $this->addSelection("id=q2_3", "label=A. Option B");
    $this->addSelection("id=q2_4", "label=D. Option P");
    $this->addSelection("id=q2_4", "label=E. Option X");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "value=1");
    $this->addSelection("id=q1_2", "value=1");
    $this->addSelection("id=q1_3", "value=1");
    $this->addSelection("id=q2_1", "label=A. Option One");
    $this->addSelection("id=q2_2", "label=D. Option Four");
    $this->addSelection("id=q2_3", "label=A. Option One");
    $this->addSelection("id=q2_3", "label=B. Option Two");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->addSelection("id=q1_1", "label=B. Option C");
    $this->addSelection("id=q1_2", "label=C. Option M");
    $this->addSelection("id=q1_3", "label=B. Option C");
    $this->addSelection("id=q1_3", "label=D. Option P");
    $this->addSelection("id=q1_4", "label=D. Option P");
    $this->addSelection("id=q2_1", "value=4");
    $this->addSelection("id=q2_2", "value=2");
    $this->addSelection("id=q2_2", "value=3");
    $this->addSelection("id=q2_3", "value=4");
    $this->addSelection("id=q2_3", "value=5");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=31357211657102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText("//table[2]/tbody/tr[2]/td[2]/p[2]/span", '2 out of 3');
    $this->assertElementContainsText("//table[2]/tbody/tr[4]/td[2]/p/span", '0 out of 8');
    $this->assertElementContainsText("//table[3]/tbody/tr/td[2]/p/span", '1 out of 3');
    $this->assertElementContainsText("//table[3]/tbody/tr[4]/td[2]/p/span", '-1 out of 3');
    $this->assertElementContainsText("//table[4]/tbody/tr/td[2]/p/span", '-3 out of 8');
    $this->assertElementContainsText("//table[4]/tbody/tr[3]/td[2]/p/span", '2 out of 3');
    $this->assertElementContainsText("//table[5]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 1');
    $this->assertElementContainsText("//table[5]/tbody/tr[4]/td[2]/p/span", '0 out of 2');
    $this->assertElementContainsText("//table[6]/tbody/tr/td[2]/p/span", '0 out of 3');
    $this->assertElementContainsText("//table[6]/tbody/tr[4]/td[2]/p/span", '-0.5 out of 1');
    $this->assertElementContainsText("//table[7]/tbody/tr/td[2]/p/span", '-1 out of 2');
    $this->assertElementContainsText("//table[7]/tbody/tr[3]/td[2]/p/span", '-1 out of 1');
    $this->assertElementContainsText("//table[8]/tbody/tr[2]/td[2]/p[2]/span", '3 out of 5');
    $this->assertElementContainsText("//table[8]/tbody/tr[4]/td[2]/p/span", '0 out of 14');
    $this->assertElementContainsText("//table[9]/tbody/tr/td[2]/p/span", '2 out of 5');
    $this->assertElementContainsText("//table[9]/tbody/tr[4]/td[2]/p/span", '-1.5 out of 5');
    $this->assertElementContainsText("//table[10]/tbody/tr/td[2]/p/span", '4 out of 14');
    $this->assertElementContainsText("//table[10]/tbody/tr[3]/td[2]/p/span", '3 out of 5');
    $this->assertElementContainsText("//table[11]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 1');
    $this->assertElementContainsText("//table[11]/tbody/tr[4]/td[2]/p/span", '0 out of 2');
    $this->assertElementContainsText("//table[12]/tbody/tr/td[2]/p/span", '0 out of 3');
    $this->assertElementContainsText("//table[12]/tbody/tr[4]/td[2]/p/span", '-0.5 out of 1');
    $this->assertElementContainsText("//table[13]/tbody/tr/td[2]/p/span", '-1 out of 2');
    $this->assertElementContainsText("//table[13]/tbody/tr[3]/td[2]/p/span", '-1 out of 1');
    $this->assertElementContainsText("//table[14]/tbody/tr[2]/td[2]/p[2]/span", '4 out of 6');
    $this->assertElementContainsText("//table[14]/tbody/tr[4]/td[2]/p/span", '0 out of 16');
    $this->assertElementContainsText("//table[15]/tbody/tr/td[2]/p/span", '2 out of 6');
    $this->assertElementContainsText("//table[15]/tbody/tr[4]/td[2]/p/span", '-2 out of 6');
    $this->assertElementContainsText("//table[16]/tbody/tr/td[2]/p/span", '2 out of 16');
    $this->assertElementContainsText("//table[16]/tbody/tr[3]/td[2]/p/span", '4 out of 6');
    $this->assertElementContainsText("//table[17]/tbody/tr[2]/td[2]/p[2]/span", '0 out of 1');
    $this->assertElementContainsText("//table[17]/tbody/tr[4]/td[2]/p/span", '0 out of 2');
    $this->assertElementContainsText("//table[18]/tbody/tr/td[2]/p/span", '0 out of 3');
    $this->assertElementContainsText("//table[18]/tbody/tr[4]/td[2]/p/span", '-0.5 out of 1');
    $this->assertElementContainsText("//table[19]/tbody/tr/td[2]/p/span", '-1 out of 2');
    $this->assertElementContainsText("//table[19]/tbody/tr[3]/td[2]/p/span", '-1 out of 1');

    // Overall Marks
    $this->assertElementContainsText("//div[19]/table/tbody/tr[2]/td[2]", '14 out of 162');
    $this->assertElementContainsText('//div[19]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText("//div[19]/table/tbody/tr[4]/td[2]", '8.64%');
  }

  public function testClassTotals() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=3&startdate=20130102000000&enddate=20230117150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");

    // Individuals
    $this->assertElementContainsText('//tr[@id="res6"]/td[5]', '0');
    $this->assertElementContainsText('//tr[@id="res6"]/td[6]', '0.00%');
    $this->assertElementContainsText('//tr[@id="res6"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res7"]/td[5]', '162');
    $this->assertElementContainsText('//tr[@id="res7"]/td[6]', '100.00%');
    $this->assertElementContainsText('//tr[@id="res7"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res8"]/td[5]', '-47.5');
    $this->assertElementContainsText('//tr[@id="res8"]/td[6]', '-29.32%');
    $this->assertElementContainsText('//tr[@id="res8"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res9"]/td[5]', '47.5');
    $this->assertElementContainsText('//tr[@id="res9"]/td[6]', '29.32%');
    $this->assertElementContainsText('//tr[@id="res9"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res10"]/td[5]', '14');
    $this->assertElementContainsText('//tr[@id="res10"]/td[6]', '8.64%');
    $this->assertElementContainsText('//tr[@id="res10"]/td[7]', 'Fail');
  }
}
?>