@core 
Feature: Searching People
   In order to check searching on People
   As a Admin/ Teacher
   I want to do search

   @javascript
   Scenario: Test search People
      Given the following "users" exist:
         | username |roles |
         | teacher1 | Staff |
         | teacher2 | Staff |
         | teacher3 | Staff |
         | student1 | Student |

      Given I login as "admin"
      Then I should see menu with following items:
         | menu_items |
         | Administrative Tools |
         | Create folder |
         | My Personal Keywords |
         | Search |
      When I follow "Search"
      Then I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      When I click "People" "sub_search_menu_item"
      Then I should be on "/users/search.php"
      Then I fill in the following:
         | search_username | teacher1 |
      When I click "icon3" "id"
      Then I check "staff"
      And I press "Search"
      Then I should see "teacher1" in the "table#maindata" element
      Then I log out
      Given I login as "teacher1"
      When I go to the homepage
      Then I follow "Search"
      Then I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      When I click "People" "sub_search_menu_item"
      Then I should be on "/users/search.php"
      Then I fill in the following:
         | search_username | teacher2 |
      When I click "icon3" "id"
      Then I should see "Staff" in the "div#menu3" element
      Then I check "staff"
      And I press "Search"
      Then I should see "teacher2" in the "table#maindata" element
      Then I go to the homepage
      Then I follow "Search"
      Then I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      When I click "People" "sub_search_menu_item"
      Then I should be on "/users/search.php"
      Then I fill in the following:
         | search_username | student1 |
      Then I should see "Students" in the "div#menu3" element
      Then I check "students"
      And I press "Search"
      Then I should see "student1" in the "table#maindata" element