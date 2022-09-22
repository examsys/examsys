@core
Feature: Home page
   In order to allow access to ExamSys
   As a user
   I should be able to see home page after login

   @javascript
   Scenario: Admin user has functional menu
      Given I login as "admin"
      Then I should see menu with following items:
         | menu_items |
         | Administrative Tools |
         | Create folder |
         | My Personal Keywords |
         | Search |
      When I toggle the main menu
      Then I should see main menu with following items:
         | Item | 
         | Administrative Tools |
         | Help & Support |
         | Sign Out |
         | About ExamSys |
      When I click "Help & Support" "main_menu_item"
      Then I should see popup page with title "ExamSys: Help"
      When I close popup window "ExamSys: Help"
      And I toggle the main menu
      And I click "Administrative Tools" "main_menu_item"
      Then I should see page with title "Administrative Tools"
      When I toggle the main menu
      And I click "Sign Out" "main_menu_item"
      Then I should be on the homepage

   @javascript
   Scenario: Admin user homepage
      Given the following "academic year" exist:
         | calendar_year | academic_year |
         | 2015 | 2015/16 |
         | 2016 | 2016/17 |
         | 2017 | 2017/18 |
      When I login as "admin"
      And I should see "My Folders" "content_section"
      And I should see "Recycle Bin" "link"
      And I should see "My Modules" "content_section"
      And I should see "All Modules..." "link"
      When I follow "Search"
      Then I should see popup search menu with following items:
         | Item |
         | Questions |
         | Papers |
         | People |
      And I should see "Administrative Tools" "menu_item"
      When I follow "Administrative Tools"
      And I click admin tool "Academic Sessions"
      Then I should see table with:
         | Calendar Year | Academic Year |
         | 2017 | 2017/18 |
         | 2016 | 2016/17 |
         | 2015 | 2015/16 |

   @javascript
   Scenario: Staff user homepage
      Given the following "users" exist:
         | username | roles |
         | teacher1 | Staff |
      And the following "modules" exist:
         | moduleid | fullname |
         | m1 | m1 |
         | m2 | m2 |
         | m3 | m3 |
      And the following "module team members" exist:
         | moduleid | username |
         | m1 | teacher1 |
         | m2 | teacher1 |
         | m3 | teacher1 |
      When I login as "teacher1"
      And I go to the homepage
      Then I should see "m1" "folder"
      And I should see "m2" "folder"
      And I should see "m3" "folder"