phrames: A django-Inspired PHP ORM/Framework
===
## Status Update

It's been about 2 years since I've done any development on this little side project. With my day job keeping me busy in C# I've been feeling a little rusty with PHP. I've decided to do a complete rewrite of my original code both because phrames is something I want to use and because I've learned a lot in the last two years. My rewrite includes large changes to the programmer API (creating objects/models, fields) from the example below.

My goals for the project remain the same, but I will be using the [rewrite branch](https://github.com/afreiday/phrames/tree/rewrite) as my open working space -- committing whatever work I might have accomplished as time and motivation permits.

- Andrew, Apr 18 2014

## About

phrames was created by [Andrew Freiday](http://www.andrewfreiday.com) originally in hopes of replicating Python's django framework object-relational mapping (ORM).

IMPORTANT NOTE: This project is very much in development. It is certainly _not_ suitable for production use. It needs a lot of (unit) testing. Users should very much expect the interface to change over time, rendering previous versions deprecated/broken. For now, the github project page will simply serve as a random dumping ground for new features/updates/development work until it is production-worthy and a proper git workflow is established.

## Project Goals

There are a few primary goals/objectives that phrames was initially developed to achieve:

- Have a modular, lightweight codebase
- Be conducive to rapid development in a variety of iteration styles (database-first/code-later or code-first/database-later)
- Follow and improve upon the django-style syntax/model where possible within the current language feature constraints of PHP (when compared with Python)
- Attempt to find a balance between the most fluid, feature-rich interface and overall performance

## Basic Example

The following is just a brief display of some of the features of phrames. For more detailed feature descriptions, please see the documentation.

```php
<?php

  use phrames\query\Field as Field;
  use phrames\model\Model as Model;

  require_once("phrames/phrames.php");

  class User extends Model {

    protected static function fields() {
      return array(
        "id" => array(
          "type" => new phrames\model\IDField(),
        ),
        "username" => array(
          "__get" => function($v) {
            return strtolower($v);
          },
        ),
        "clubs" => array(
          "type" => new phrames\model\ManyToManyField("Club", "Membership"),
        ),
      );
    }

  }

  class Club extends Model {

    protected static function fields() {
      return array(
        "id" => array(
          "type" => new phrames\model\IDField(),
        ),
        "members" => array(
          "type" => new phrames\model\ManyToManyField("User", "Membership"),
        ),
      );
    }

  }

  class Membership extends Model {

    protected static function fields() {
      return array(
        "id" => array(
          "type" => new phrames\model\IDField(),
        ),
        "user" => array(
          "type" => new phrames\model\ForeignKey("User"),
        ),
        "club" => array(
          "type" => new phrames\model\ForeignKey("Club"),
        ),
        "is_admin" => array(
          "type" => new phrames\model\BooleanField(),
          "required" => true,
        ),
      );
    }

  }

  $users = User::objects()->all(); // get all users

  $users = $users->order_by("-username");

  foreach($users as $user) {
    print "{$user->real_name} ({$user->username}) is a member of:\n";
    foreach($user->clubs as $club) // get clubs user is a member of
      print "{$club->name}\n";
    print "\n";
  }

```

## TODO List

Next things to implement, in no particular order:

- Proper exception handling
- Flexible cache integration
- Expanded list of ModelField types
- Database sharding support
- Query aggregation functions (avg, count, max, min, etc)
- Improve interface to reduce verbosity
- A lot later: templating layer, view layer, etc

## Documentation

Please see DOCS.md
