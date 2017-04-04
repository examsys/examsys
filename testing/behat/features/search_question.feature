@core
Feature: Searching Papers,People and their related Academic Sessions
  In order to check searching on Papers,People
  As a Admin/ Teacher
  I want to do search

  @javascript
  Scenario: Test search Papers and People
    Given the following "users" exist:
      | username | roles |
      | teacher1 | Staff |
    Given the following "modules" exist:
      | moduleid | fullname |
      | m1       | m1       |
      | m2       | m2       |
      | m3       | m3       |
    Given the following "questions" exist:
      | type    | leadin           | user     |
      | textbox | textbox_question | teacher1 |
    Given the following "module_teams" exist:
      | modulename | username |
      | m1         | teacher1 |
      | m2         | teacher1 |
      | m3         | teacher1 |
    Given I login as "teacher1"
    When I follow "Search"
    Then I should see submenu with following items:
      | menu_items  |
      | Questions |
      | Papers    |
      | People    |
    When I click "Question" "sub_search_menu_item"
    Then I should be on "/question/search.php"
    Then I fill in the following:
      | searchterm | textbox_question |
    And I press "Search"
    Then I should see "textbox_question" in the "td.u" element