## Documentation/Bird's-Eye View

### Basic Configuration

Only very basic database configuration is required. Open the `phrames/Config_phrames.php` file and include your database connection information:

```php
<?php

  class Config_phrames {
  
    const DB_DRIVER = "mysql"; // flavor of database, curently only MySQL driver exists
    const DB_HOST = "localhost"; // server address
    const DB_NAME = "my_database"; // database name
    const DB_USER = "username";
    const DB_PASS = "password";
    
    /* ... */
  
  }
  
```

### Defining Models

In order to perform queries, you first need to define the models that will be used to represent the tables in your database.

Because phrames is intended to be flexible, you can define your object models as basic as you wish:

```php
<?php

  use phrames\model\Model as Model;

  require_once("phrames/phrames.php");
  
  class User extends Model {
  }
  
```

That's it! You can now perform queries (detailed below) on your User objects/table. You redefine your existing table schema field-by-field, or simply not redefine anything and let PHP/phrames handle the rest.

#### Explicit database table naming

By default, phrames will automatically determine what your database table name is based on the name of the model. For example, if your model is named `User`, it will assume that the database table you have your `User` data stored in is called `users`. If this is not the case, and you store your `User` data in a table called `thesearemyusers`, you can explicity define this using the `table_name` constant:

```php
<?php

  use phrames\model\Model as Model;

  class User extends Model {
  
    const table_name = "thesearemyusers";
  
  }
  
```

phrames doesn't know all of the intricicies of the English language, so it only makes a few basic assumptions about pluralizing `Model` names:

	Funny => funnies // 'y' ending is replaced with 'ies'
	Class => classes // 's' ending is replaced with 'es'
	User => users // everything else just adds an 's'

This will probably work out just fine for you 90% of the time.

### Explicit field definitions

Some of the power of phrames comes from pre-/re-defining your database table columns as fields of your models. This is done by defining a `protected static function fields()`, which should return an array of your fields:

```php
<?php

  use phrames\model\Model as Model;

  class User extends Model {
  
    protected static function fields() {
      return array(
        "id" => array(
          // if no IDField is defined, phrames assumes that you have
          // a field in your table called 'id' that represents a unique
          // identifier of your objects
          "type" => new IDField(),
        ),
        "username" => array(
          "type" => new CharField(30),
          "required" => true,
        ),
        "options" => array(
          "type" => new JSONField(),
        ),
      );
    }
  
    public function __toString() {
      // notice that the referencing of fields that aren't defined above. phrames assumes you know
      // the schema of your own database tables. You can define as little or as much as you like.
      return "{$this->first_name} {$this->last_name}";
    }
  
  }
  
```

There are a few pre-defined model field types defined in the `phrames/model/ModelField.class.php` file, such as IDField, BooleanField, TextField, CharField, DateField, etc. Each can handle data in a special way. For example, the JSONField class will save your data to the table as a JSON string, but will return an array when the value is retrieve from an active object.

Now you can start creating and saving objects:

```php
<?php

  $new_user = new User();
  $new_user->username = "some_user";

  // again, these weren't defined, phrames assumes I know what I'm doing
  // if I want to rapidly develop my project
  $new_user->first_name = "Jon";
  $new_user->last_name = "Doe";

  // JSONField will automatically json_encode before saving to the database
  $new_user->options = array( "some_option" => "some value"; );

  $new_user->save(); //saved!

  var_dump($new_user->id); // now that it's been saved, it has an id
  
```

#### More field options

There are a few more field definition options that you can define to make life a little bit simpler:

```php
<?php

  use phrames\model\Model as Model;

  class Foo extends Model {

    protected static function fields() {
      return array(
        "type" => /* a specific ModelField type */,
        "required" => /* true/false, whether this field requires a value prior to saving */,
        "default" => /* a default value that is provided when a new object is created */,
        "__get" => function($v) {
          // a closure that can modify the stored database value of this field before
          // returning it from the object's storage, e.g. if your actual database stored
          // value is 5, and your function is:
          $v = $v * 100;
          // then $obj->this_field will return 500, not 5. This is great for otherwise
          // repetitive custom data handling/formatting
          return $v;
        },
        "__set" => function($v) {
          // Essentially the opposite of "__get". This will modify the stored value of a
          // field, e.g.
          $v = strtoupper($v);
          // means that $obj->this_field = "This is my Fancy String.";
          // will store to the database, "THIS IS MY FANCY STRING"
          return $v;
          },
        "unique" => /* true/false, whether this field needs to be unique in the table */
      );
    }

  }
  
```
	
#### ForeignKey fields

You can also define foreign key references from one object/table to another. For example:

```php
<?php

  use phrames\model\Model as Model;

  class Order extends Model {

    protected static function fields() {
      return array(
        "placed_by" => array(
          "type" => new \phrames\model\ForeignKey("User"),
        ),
      );
    }

  }

  class User extends Model {
  }

  /* some query to get an order */
  $user = $order->placed_by; // will return a User object that placed this Order
  print $user->username;

  // and it works as expected when assigning a value:
  $user = new User();
  $user->username = "mrfoobar";
  $user->save();
  $order->placed_by = $user;
  
```

Each ForeignKey object can also have an on_delete flag to specify how to protect relationships when an object is deleted. For example, you might want to protect an `Order` in case it's `placed_by` user gets deleted. This very closely replicates [the same functionality from django](https://docs.djangoproject.com/en/dev/ref/models/fields/#django.db.models.ForeignKey.on_delete). Basic options include: CASCADE, PROTECT, SET_NULL, SET_DEFAULT, or a closure which defines what new value to assign:

```php
<?php

  /* ... */
  new \phrames\model\ForeignKey("User", function() {
    $user = new User();
    $user->username = "DELETED_USER";
    $user->save();
    return $user;
  });
  
```

Now when a `User` is deleted (i.e. `$some_user->delete()`), the `User` is deleted from the database and all `Order`s that have been placed by that him/her have been replaced by a new one named `DELETED_USER`.

#### One-to-many Fields

One-to-many fields are pseudo fields (i.e. not defined in the database table) that can return a set (QuerySet, outlined below) of objects. So, building on the example above:

```php
<?php

  /* .. */

  class User extends Model {

    protected static function fields() {
      return array(
        "orders" => array(
          "type" => new \phrames\model\OneToManyField("Order"),
        ),
      );
    }

  }
  
```

Now when you call `$some_user->orders`, it will return a set of Order objects placed by this user. It will automatically search the defined fields of `Order` to find which field (`placed_by`) references `User`. Of course, you can also easily iterate over this result set:

```php
<?php

  foreach($user->orders as $order) {
    print $order->order_num;
  }
  
```

#### Many-to-many Fields

Many-to-many fields are, like one-to-many fields, pseudo fields which can return a series of objects whose relationship is defined through the definition of a tertiary class:

```php
<?php

  class Club extends Model {
    protected static function fields() {
      return array(
        "members" => array(
          "type" => new \phrames\model\ManyToManyField("User", "Membership"), // get Users in this club through Membership
        ),
      );
    }
  }

  class User extends Model {
    protected static function fields() {
      return array(
        "clubs" => array(
          "type" => new \phrames\model\ManyToManyField("Club", "Membership"),
        ),
      );
    }
  }

  class Membership extends Model {

    protected static function fields() {
      return array(
        "user" => array(
          "type" => new \phrames\model\ForeignKey("User"),
        ),
        "club" => array(
          "type" => new \phrames\model\ForeignKey("Club"),
        ),
      );
    }

  }
  
```

Now you can quickly gather a list of a clubs members (or clubs that a user belongs to) by referencing its ManyToManyField. As with OneToManyField relationships, phrames will automatically determine what fields reference what. An example:

```php
<?php

	/* some query for a club */
	print "{$club->name} members list:\n";
	foreach($club->members as $user)
	  print "{$user->username}\n";
    
```

#### MySQL 'CREATE TABLE' definitions

Currently the MySQL database driver includes the ability to generate/dump a CREATE TABLE SQL statement that you can use to build your tables in your database. This would allow you to quickly plot out your `Model` definitions in code and create the tables after.

```php
<?php

  // single model CREATE TABLE
  print Membership::db_create_table();

  // all models CREATE TABLE
  print \phrames\Config_phrames::db_create_tables();
  /* (it is recommended to define models in a logical order, where Models
      with ForeignKey fields are placed after the Models they reference) */

```

### Querying Models

Now of course, what good is defining all of these Models without being able to query them willy nilly from the database? Each model can be queried using its query manager, obtained statically via `SomeClass::objects()`. Every query (except `get()`) can be iterated over easily:

```php
<?php

  foreach($somequery as $single_object) {
    /* ... */
  }
  
```

#### All objects query

You can return a set of every record/object in database by performing an `all()` query:

	$all_users = User::objects()->all();

#### Basic 'get' queries

Performing a 'get' query will either return a single object or throw an exception where more than one object exists in that query. For example, if you want to get a single user whose name is 'Andrew' (and you know there should be only one):

	$andrew = User::objects()->get(Field::first_name__exact("Andrew"));

You can also quickly retrieve an object by it's specific unique identifier:

	$user123 = User::objects()->get(123);

#### get\_or_create()

Using the `get\_or_create()` method does more or less what you expect it to. By providing an array of query arguments, the object manager will attempt to perform a `get()` operation, or create a new object using those arguments if none (or more than one) was found:

```php
<?php

  $user = User::objects()->get_or_create(array("name" => "Andrew", "age" => 25));
  // SELECT ... FROM users WHERE name = 'Andrew' AND age = 25
  // if none was found, it basically does this for you:
  $user = new User();
  $user->name = "Andrew";
  $user->age = 25;
  $user->save();
    
```

Either way, you're getting an object that you need.

#### 'Filter' queries

You can filter an set/subset of objects using the equiviliant of a WHERE clause by using the `filter()` method:

```php
<?php

  $young_users = User::objects()->filter(Field::age__lt(25));
  // SELECT ... FROM users WHERE age < 25
  
```

and you can modify such a query by using the `_AND_()` or `_OR_()` functions:

```php
<?php

  $young_males = User::objects()->filter(_AND_(Field::age__lt(25), Field::gender__exact('male')));
  // SELECT ... FROM users WHERE age < 25 AND gender = 'male'
  
```

#### 'Exclude' queries

Somewhat like `filter()`, `exclude()` limits a query using a set of parameters:

```php
<?php

  $female_users = User::objects()->exclude(Field::gender__exact('male'));
  // SELECT ... FROM users WHERE NOT gender = 'male'
  
```

Exclude queries can also use `_AND_()` and `_OR_()` modifiers.

#### Field Lookups

Currently there exists a number of field_lookups contained with the MySQL database driver. These are closely based on [django's field lookups](https://docs.djangoproject.com/en/dev/ref/models/querysets/#field-lookups):

`EXACT, IEXACT, CONTAINS, ICONTAINS, IN, GT, GTE, LT, LTE, STARTSWITH, ISTARTSWITH, ENDSWITH, IENDSWITH, RANGE, YEAR, MONTH, DAY, WEEKDAY, ISNULL, REGEX, IREGEX`

Customized query parameters/field lookups can be added in the `phrames/db/drivers/DB_MYSQL.class.php` file.

#### Negating query parameters

You can negate individual query parameters by using the `_NOT_()` function, such as:

```php
<?php

  $female_users = User::objects()->filter(_NOT_(Field::gender__exact('male')));
  // SELECT ... FROM users WHERE NOT gender = 'male'
  
```

#### Chaining queries

Any query can be filtered or excluded any number of times to continually reduce its subset of results:

```php
<?php

  $males = User::objects()->filter(Field::gender__exact('male'));
  // SELECT ... FROM users WHERE gender = 'male'
  $young_males = $males->exclude(Field::age__gt(25))
  // SELECT ... FROM users WHERE gender = 'male' AND NOT age > 25
  
```

#### Querying ForeignKey fields

Some of the greatest power of explicitly defining `Model` field types with phrames is the ability to query through `ForeignKey` relationships. Using our `User`-`Order` relationship from before, it is possible to query orders given a particular user field:

```php
<?php

  // get orders by female users
  $orders = Order::objects()->filter(Field::placed_by__gender__exact('female'));

  // alternatively, without defining a ForeignKey relationship:
  $female_users = User::objects()->filter(Field::gender__exact('female'));
  $orders = Order::objects()->filter(Field::placed_by__in($female_users));
  
```

#### Sorting/splicing queries

Queries can easily be sorted and spliced like arrays. Here are a few basic examples:

```php
<?php

  $users = User::objects()->all();

  sizeof($users); // returns a count of resulting objects
  // BACKEND NOTE: To improve performance, if you haven't yet iterated over the query (i.e. loaded
  // all resulting objects into it) then it simply performs a SELECT COUNT(id_field) ... on the database,
  // thereby saving memory

  $users = $users->order_by("name", "-age");
  // returns a new query: SELECT ... FROM users ORDER BY name ASC, age DESC
  $users = $users->reverse();
  // inverts the previous sort: SELECT ... FROM users ORDER BY name DESC, age ASC

  $first_user = $users[0]; // returns the first user in this result
  $last_user = $users[sizeof($users) - 1]; // last user in the result

  $first_five_users = $users->limit(0, 4);
  // also the same as:
  $first_five_users = $users["0:4"];
  
  // gets every object after the 4th result
  $last_users = $users["4:"];
    
```

#### Splicing resulting object fields

You can easily obtain an array of values from any set of fields. This is a cleaner alternative to returning a set of objects and iterating through to retrieve a list of field values.

```php
<?php

  $users = User::objects()->all();

  $usernames = $users->value_list("username");
  // get a one dimensional array of usernames from this query
  // i.e. array("foo", "bar", "anotheruser", ...);

  $usernames_and_ids = $users->values_list(array("username", "id"));
  // multidimensional key/value array of specified fields, i.e.
  /**
   * array(
   *   array(
   *     "username" => "foo",
   *     "id" => 1,
   *   ),
   *   array(
   *     "username" => "bar",
   *     "id" => 2,
   *   ),
   *   // etc
   * )
   */
  
```

#### Updating/deleting objects from a query

Rather than iterating through each object, you can quickly update or delete all of the objects in a query using their obviously named methods:

```php
<?php

  $users = User::objects()->all();

  // update all users to be aged 25
  $users->update(array("age" => 25));
  // equivilant to:
  foreach($users as $user) {
    $user->age = 25;
    $user->save();
  }

  // delete all users
  $users->delete();
  
```
