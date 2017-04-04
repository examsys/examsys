@core
Feature: Home page
   In order to allow access to Rogo
   As a user
   I should be able to see home page after login

   @javascript
   Scenario: Admin user login
      Given I login as "admin"
      Then I should see menu with following items:
         | menu_items |
         | Administrative Tools |
         | Create folder |
         | My Personal Keywords |
         | Search |
      Then I toggle the main menu
      Then I should see main menu with following items:
         | Item | 
         | Administrative Tools |
         | Help and Support |
         | Sign Out |
         | About Rogo | 
      Then I click "Help & Support" "main_menu_item"
      Then I should see popup page with title "Rogō: Help"
      Then I close popup window
      Then I toggle the main menu
      Then I click "Administrative Tools" "main_menu_item"
      Then I should see popup page with title "Rogō: Admin"
      Then I close popup window
      Then I toggle the main menu
      Then I click "Sign Out" "main_menu_item"
      Then I should be on homepage

   @javascript
   Scenario: Admin user homepage
      Given the following "academic_year" exist:
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
      Then I should see "Administrative Tools" "menu_item"
      When I follow "Administrative Tools"
      When I click admin tool "Academic Sessions"
      Then I should see table with:
         | Calendar Year | Academic Year |
         | 2017 | 2017/18 |
         | 2016 | 2016/17 |
         | 2015 | 2015/16 |

   @javascript
   Scenario: Staff user homepage
      Given the following "users" exist:
         | username |roles |
         | teacher1 | Staff |
      Given the following "modules" exist:
         | moduleid | fullname |
         | m1 | m1 |
         | m2 | m2 |
         | m3 | m3 |
      Given the following "module_teams" exist:
         | modulename | username |
         | m1 | teacher1 |
         | m2 | teacher1 |
         | m3 | teacher1 |
      Given I login as "teacher1"
      When I go to the homepage
      Then I should see "m1" "folder"
      Then I should see "m2" "folder"
      Then I should see "m3" "folder"