cookingwithgas
version 1.0

cookingwithgas is a web-based recipe management system.  It uses JQuery 
Mobile to generate a UI that is designed for mobile devices like Android 
phones and iPhones.

Features:

- arbitrary tagging system so that you can organize and browse your
  recipes in a variety of different ways.

- Import and export from other systems using XML

- Generate PDF recipe cards (4"x6" index cards)

- Share recipes via e-mail (also sends the recipient a PDF copy for
  convenient printing)

- reasonably sophisticated user account management, including the concept
  of "account groups".  Within an account group, all users have read/write
  access to the recipes.  Outside the account group, users cannot see the
  recipes at all.  So you could host this and let different unrelated users
  manage their recipes independently.

Requirements:

- php 5 (tested with php 5.2, but I think it *should* work with other
  point releases of php 5)

- ZendFramework 1.11 or above (it *may* work with earlier versions, but
  no guarantees)

- MySQL 5

- JQuery Mobile (currently at version 1 alpha 4)

Installing:

- unpack the tarball

- create a new database in MySQL

- use cookingwithgas.ddl to build the schema

- edit application/configs/application.ini

