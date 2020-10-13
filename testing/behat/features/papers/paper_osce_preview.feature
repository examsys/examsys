@paper @javascript
Feature: Paper setup
  In order to run exams
  As a teacher
  I need to be able to preview an exam

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
    And the following "papers" exist:
      | papertitle | papertype | paperowner | modulename | settings |
      | an osce paper | 4 | teacher | Test module | {"marking":"7"} |
    And the following "questions" exist:
      | user | type | leadin | scenario | options | paper | settings | display_method |
      | teacher | likert | likert 1 | likert 1 | {"marks_correct":"1","marks_incorrect":"0","marks_partial":"0"} | {"paper":"an osce paper","screen":"1","displaypos":"1"} | | Low\|High\|false |
      | teacher | likert | likert 2 | likert 2 | {"marks_correct":"1","marks_incorrect":"0","marks_partial":"0"} | {"paper":"an osce paper","screen":"1","displaypos":"2"} | | Low\|High\|false |

  @paper_preview_osce @jsevaluation
  Scenario: Preview an existing exam
    Given I login as "teacher"
    And I am on "Paper Details" page for "an osce paper"
    And I open the osce preview
    And I answer the questions:
      | position | type | answer |
      | 1 | likert | 1 |
      | 2 | likert | 2 |
    And I answer overall with "Fail"
    And I enter the feedback "some feedback"
    And I close the osce preview
    Then I should see "an osce paper" "paper_title"
