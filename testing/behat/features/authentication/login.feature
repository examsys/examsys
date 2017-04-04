@core
Feature: Login
  In order to allow access to Rogo
  As a user
  I should be able to login

  Scenario: Admin user login
    Given I login as "admin"
    Then I should see "Administrative Tools" "menu_item"

  @javascript
  Scenario: Admin user login using JavaScript
    Given I login as "admin"
    Then I should see "Administrative Tools" "menu_item"

  Scenario: Student user login
    Given the following "users" exist:
      | username |
      | student1 |
    When I login as "student1"
    Then I should see "No exams found"

  @javascript
  Scenario: Student user login with JavaScript
    Given the following "users" exist:
      | username |
      | student1 |
    When I login as "student1"
    Then I should see "No exams found"
