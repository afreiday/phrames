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

