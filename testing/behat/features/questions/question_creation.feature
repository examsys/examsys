@questions @javascript
Feature: Question creation
  In order to run exams
  As a teacher
  I need to be able to create questions from the module page

  Background:
    Given the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
    And the following "users" exist:
      | username | roles |
      | teacher | Staff |
    And the following "module team members" exist:
      | moduleid | username |
      | TEST1001 | teacher |
    And the following "module keywords" exist:
      | moduleid | keyword |
      | TEST1001 | test |
      | TEST1001 | key |
      | TEST1001 | words |
    And the following "questions" exist:
      | user | type | leadin | scenario | keywords | options |
      | teacher | true_false | tf 1 leadin | tf 1 scenario | ["test"] | {"correct":"t","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"} |
      | teacher | true_false | tf 2 leadin | tf 2 scenario | ["key","words"] | {"correct":"f","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"} |
      | teacher | true_false | tf 3 leadin | tf 3 scenario | | {"correct":"t","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"} |

  @question_area @mousemanipulation @attachfile
  Scenario: Create a area question
    Given I login as "teacher"
    And I follow "TEST1001"
    And The upload source path is "questions/html5"
    And I select a "area" question type
    And I create a new "area" question:
      | theme | area theme |
      | notes | area notes |
      | scenario | area scenario |
      | leadin | area leadin |
      | file | plants.jpg |
      | coordinates | 100,0,0,100,-100,0,0,-100 |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_enhancedcalc
  Scenario: Create a enhancedcalc question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "enhancedcalc" question type
    And I create a new "enhancedcalc" question:
      | theme | enhancedcalc theme |
      | notes | enhancedcalc notes |
      | scenario | enhancedcalc scenario |
      | leadin | enhancedcalc leadin $A+$B|
      | variable_min_1 | 1 |
      | variable_max_1 | 10 |
      | variable_decimal_1 | 2 |
      | variable_increment_1 | 0.2 |
      | variable_min_2 | 100 |
      | variable_max_2 | 1000 |
      | variable_decimal_2 | 0 |
      | variable_increment_2 | 100 |
      | formula_1 | $A+$B |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_dichotomous
  Scenario: Create a dichotomous question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "dichotomous" question type
    And I create a new "dichotomous" question:
      | theme | dichotomous theme |
      | notes | dichotomous notes |
      | scenario | dichotomous scenario |
      | leadin | dichotomous leadin|
      | stem_1 | this is stem 1 |
      | stem_true_1 | 1 |
      | stem_2 | this is stem 2 |
      | stem_true_2 | 0 |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_extmatch
  Scenario: Create a extmatch question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "extmatch" question type
    And I create a new "extmatch" question:
      | theme | extmatch theme |
      | notes | extmatch notes |
      | leadin | extmatch leadin|
      | stem_1 | this is stem 1 |
      | stem_2 | this is stem 2 |
      | option_1 | this is option A |
      | option_2 | this is option B |
      | option_3 | this is option C |
      | stem_select_1 | ["this is option A", "this is option C"] |
      | stem_select_2 | ["this is option B"] |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_blank
  Scenario: Create a blank question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "blank" question type
    And I create a new "blank" question:
      | theme | blank theme |
      | notes | blank notes |
      | leadin | blank leadin |
      | question | fill in [blank]the,a,their[/blank] blank |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_info
  Scenario: Create a info block
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "info" question type
    And I create a new "info" question:
      | theme | info theme |
      | text | info text |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_matrix
  Scenario: Create a matrix question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "matrix" question type
    And I create a new "matrix" question:
      | theme | matrix theme |
      | notes | matrix notes |
      | leadin | matrix leadin |
      | column_1 | this is column 1 |
      | column_2 | this is column 2 |
      | row_1 | this is row A |
      | row_2 | this is row B |
      | select_1 | 1 |
      | select_2 | 2 |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_hotspot @mousemanipulation @attachfile
  Scenario: Create a hotspot question
    Given I login as "teacher"
    And I follow "TEST1001"
    And The upload source path is "questions/html5"
    And I select a "hotspot" question type
    And I create a new "hotspot" question:
      | theme | hotspot theme |
      | notes | hotspot notes |
      | scenario | hotspot scenario |
      | file | plants.jpg |
      | hotspot_1 | rectangle,0,200,100,100 |
      | hotspot_2 | ellipse,100,100,100,100 |
      | hotspot_3 | polygon,100,50,100,0,0,100,-100,-100 |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_labelling @mousemanipulation @attachfile
  Scenario: Create a labelling question
    Given I login as "teacher"
    And I follow "TEST1001"
    And The upload source path is "questions/html5"
    And I select a "labelling" question type
    And I create a new "labelling" question:
      | theme | labelling theme |
      | notes | labelling notes |
      | scenario | labelling scenario |
      | leadin | labelling leadin |
      | file | plants.jpg |
      | coordinates | 500,100,500,200 |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_likert
  Scenario: Create a likert question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "likert" question type
    And I create a new "likert" question:
      | theme | likert theme |
      | notes | likert notes |
      | scenario | likert scenario |
      | leadin | likert leadin |
      | scale | 0\|1 |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_mcq
  Scenario: Create a mcq question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "mcq" question type
    And I create a new "mcq" question:
      | theme | mcq theme |
      | notes | mcq notes |
      | scenario | mcq scenario |
      | leadin | mcq leadin |
      | option_1 | mcq option 1 |
      | option_2 | mcq option 2 |
      | option_3 | mcq option 3 |
      | correct | 2 |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_mrq
  Scenario: Create a mrq question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "mrq" question type
    And I create a new "mrq" question:
      | theme | mrq theme |
      | notes | mrq notes |
      | scenario | mrq scenario |
      | leadin | mrq leadin |
      | option_1 | mrq option 1 |
      | option_2 | mrq option 2 |
      | option_3 | mrq option 3 |
      | correct | ["2","3"] |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_keyword_based
  Scenario: Create a keyword_based question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "keyword_based" question type
    And  I create a new "keyword_based" question:
      | keyword | key |
      | description | keyword description |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_random @iframe
  Scenario: Create a random question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "random" question type
    And I create a new "random" question:
      | description | random block description |
      | questions | ["tf 1 leadin","tf 3 leadin"] |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_rank
  Scenario: Create a rank question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "rank" question type
    And I create a new "rank" question:
      | theme | rank theme |
      | notes | rank notes |
      | scenario | rank scenario|
      | leadin | rank leadin|
      | option_1 | this is option A |
      | option_2 | this is option B |
      | option_3 | this is option C |
      | rank_1 | 3 |
      | rank_2 | 1 |
      | rank_3 | 2 |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_sct
  Scenario: Create a sct question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "sct" question type
    And I create a new "sct" question:
      | theme | sct theme |
      | notes | sct notes |
      | clinical vignette | sct clinical vignette|
      | hypothesis | sct hypothesis |
      | experts_1 | 10 |
      | experts_2 | 30 |
      | experts_3 | 5 |
      | experts_4 | 15 |
      | experts_5 | 1 |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_textbox @attachfile
  Scenario: Create a text question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "textbox" question type
    And The upload source path is "questions/media"
    And I create a new "textbox" question:
      | theme | textbox theme |
      | notes | textbox notes |
      | scenario | textbox scenario |
      | leadin | textbox leadin |
      | file | {"filename":"frog.jpg"} |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"

  @question_true_false @attachfile
  Scenario: Create a true_false question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I select a "true_false" question type
    And The upload source path is "questions/media"
    And I create a new "true_false" question:
      | theme | textbox theme |
      | notes | textbox notes |
      | scenario | textbox scenario |
      | leadin | textbox leadin |
      | true | 0 |
      | file | {"filename":"frog.jpg","alt":"alternate text"} |
    When I click "Add to Bank" "button"
    Then I should see "Module: TEST1001" "paper_title"
