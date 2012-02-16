<?php 

  require_once("phrames/phrames.php");

  class User extends Model {

    protected static function fields() {
      return array(
        "id" => array(
          "type" => new IDField(),
        ),
        "username" => array(
          "__get" => function($v) {
            return strtolower($v);
          },
        ),
        "clubs" => array(
          "type" => new ManyToManyField("Club", "Membership"),
        ),
      );
    }

  }

  class Club extends Model {

    protected static function fields() {
      return array(
        "id" => array(
          "type" => new IDField(),
        ),
        "members" => array(
          "type" => new ManyToManyField("User", "Membership"),
        ),
      );
    }

  }

  class Membership extends Model {

    protected static function fields() {
      return array(
        "id" => array(
          "type" => new IDField(),
        ),
        "user" => array(
          "type" => new ForeignKey("User"),
        ),
        "club" => array(
          "type" => new ForeignKey("Club"),
        ),
        "is_admin" => array(
          "type" => new BooleanField(),
          "required" => true,
        ),
      );
    }

  }

  $users = User::objects()->all(); // get all users

  foreach($users as $user) {
    print "{$user->real_name} ({$user->username}) is a member of:\n";
    foreach($user->clubs as $club) // get clubs user is a member of
      print "{$club->name}\n";
    print "\n";
  }

