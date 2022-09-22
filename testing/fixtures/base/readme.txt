ExamSys Behat data definitions
---------------------------

This directory SHOULD contain a file for each ExamSys database table that has data in it by default.

There SHOULD NOT not be any files for a table that does not need default data.

Each file SHOULD contain data for only one database table.

The files WILL be loaded in alphabetical order, foreign key constraints matter so files SHOULD be prefixed with a
3 digit number to ensure that data for the table is loaded after any tables that that contain data it relies on are loaded.

All files to be loaded MUST be in the yml format, and MUST have the .yml extension.
