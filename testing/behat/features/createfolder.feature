@core
Feature: Create folder
   In order to use folder
   As a admin
   I should be able to create new folder

   Scenario: Admin user create folder
      Given I login as "admin"
      Then I should see "Create folder" "menu_item"

   Scenario: Admin user login
      Given I login as "admin"
      Then I should see menu with following items:
         | menu_items |
         | Administrative Tools |
         | Create folder |
         | My Personal Keywords |
         | Search |
      And I should see "My Folders" "content_section"
      And I should see "Recycle Bin" "link"
      And I should see "My Modules" "content_section"
      And I should see "All Modules..." "link"
      Then I follow "Create folder"
      Then I fill in "folder_name" with "my_folder"
      Then I press "submit"
      Then I should see "my_folder" "folder"
