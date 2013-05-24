<?php
require_once 'shared.inc.php';

class ClassTotalsExtmatchTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testResults() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=3&startdate=20130102000000&enddate=20530217150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");

    // Individuals
    $this->assertElementContainsText('//tr[@id="res1"]/td[5]', '0');
    $this->assertElementContainsText('//tr[@id="res1"]/td[6]', '0.00%');
    $this->assertElementContainsText('//tr[@id="res1"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res2"]/td[5]', '162');
    $this->assertElementContainsText('//tr[@id="res2"]/td[6]', '100.00%');
    $this->assertElementContainsText('//tr[@id="res2"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res3"]/td[5]', '-47.5');
    $this->assertElementContainsText('//tr[@id="res3"]/td[6]', '-29.32%');
    $this->assertElementContainsText('//tr[@id="res3"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res4"]/td[5]', '47.5');
    $this->assertElementContainsText('//tr[@id="res4"]/td[6]', '29.32%');
    $this->assertElementContainsText('//tr[@id="res4"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res5"]/td[5]', '14');
    $this->assertElementContainsText('//tr[@id="res5"]/td[6]', '8.64%');
    $this->assertElementContainsText('//tr[@id="res5"]/td[7]', 'Fail');

    // Overall

    // Failures
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[3]/td[2]', '4');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[3]/td[3]', '(80% of cohort)');
    // Passes
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[4]/td[2]', '0');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[4]/td[3]', '(0% of cohort)');
    // Distinctions
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[5]/td[2]', '1');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[5]/td[3]', '(20% of cohort)');

    // Total marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[6]/td[2]', '162');
    // Mean
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[8]/td[2]', '35.2');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[8]/td[3]', '(21.73%)');
    // Median
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[9]/td[2]', '14');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[9]/td[3]', '(8.64%)');
    // Standard Deviation
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[10]/td[2]', '78.67');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[10]/td[3]', '(48.56%)');
    // Max
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[11]/td[2]', '162');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[11]/td[3]', '(100.00%)');
    // Min
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[12]/td[2]', '-47.5');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[12]/td[3]', '(-29.32%)');
    // Range
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[13]/td[2]', '209.5');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td/table/tbody/tr[13]/td[3]', '(129.32%)');

    // Deciles
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr/td[2]', '71.73%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[2]/td[2]', '43.46%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[3]/td[2]', '25.19%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[4]/td[2]', '16.91%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[5]/td[2]', '8.64%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[6]/td[2]', '5.19%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[7]/td[2]', '1.73%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[8]/td[2]', '-5.86%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[3]/table/tbody/tr[9]/td[2]', '-17.59%');

    // Quartiles
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[5]/table/tbody/tr/td[2]', '0.00%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[5]/table/tbody/tr[2]/td[2]', '8.64%');
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[5]/table/tbody/tr[3]/td[2]', '29.32%');
  }
}
?>