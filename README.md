# cookingwithgas
version 1.0.1

cookingwithgas is a web-based recipe management system.  It uses JQuery 
Mobile to generate a UI that is designed for mobile devices like Android 
phones and iPhones.

## Features

- arbitrary tagging system so that you can organize and browse your
  recipes in a variety of different ways.
  
- search by title, tag, etc.

- Import and export from other systems using XML

- Generate PDF recipe cards (4"x6" index cards)

- Share recipes via e-mail (also sends the recipient a PDF copy for
  convenient printing)

- reasonably sophisticated user account management, including the concept
  of "account groups".  Within an account group, all users have read/write
  access to the recipes.  Outside the account group, users cannot see the
  recipes at all.  So you could host this and let different unrelated users
  manage their recipes independently.

## Requirements

- php 5 (tested with php 5.2 and 5.4, but I think it *should* work with other
  point releases of php 5)

- ZendFramework 1.11 or above (it *may* work with earlier versions, but
  no guarantees); if you're using a hosting provider, make sure you know
  how to access ZF from your PHP code; I know for my hosting provider, it's
  fairly complicated -- lots of apache and php config files to muck with.

- MySQL 5

- JQuery Mobile (currently at version 1 alpha 4)

## Installing

- unpack the tarball

- create a new database in MySQL

- use cookingwithgas.ddl to build the schema

- edit application/configs/application.ini; be sure to leave
  open_registration = 1 for now.

- go to the main page of the application (probably something like
  http://example.com/cookingwithgas/public/)

- register for an account; confirm the account via the confirmation
  e-mail

- you can now edit application.ini and set open_registration to 0
  (unless you want to run a public server)

- you should now be able to log in and manage recipes

- if you want to import recipes, you'll need them to be in this XML
  format:

```
<?xml version="1.0" encoding="utf-8"?>
<recipes>
  <recipe>
    <title>TITLE</title>
    <ingredients>INGREDIENTS
WITH
LINE
BREAKS</ingredients>
    <directions>DIRECTIONS
WITH LINE BREAKS BETWEEN PARAGRAPHS.

LIKE THIS.</directions>
    <yield>YIELD (free text)</yield>
    <source>SOURCE (free text)</source>
    <tags>
      <tag>TAG1</tag>
      <tag>TAG2</tag>
        ...
      <tag>TAGn</tag>
    </tags>
  </recipe>
</recipes>
```
