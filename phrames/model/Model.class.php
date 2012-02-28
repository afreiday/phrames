<?php

  namespace phrames\model;
  use phrames\model\Model as Model;
  use phrames\db\Database as Database;

  abstract class Model extends \phrames\model\Object {

    /**
     * Database table name
     *
     * @var string
     */
    const table_name = "";

    public static function fields() {
      return array();
    }

    /**
     * Get the table name for this particular model,
     * either manually defined or automatically
     * pluralized using the models name
     *
     * @return string
     */
    public static function table_name() {
      if (static::table_name) {
        return static::table_name;
      } else {
        $table = strtolower(get_called_class());
        switch(substr($table, -1)) {
          case "y":
            // pluralized like bunny => bunnies
            $table = substr_replace($table, "ies", -1);
            break;
          case "s":
            // pluralized like class => classes
            $table = substr_replace($table, "es", -1);
            break;
          default:
            // simply append an as, like sport => sports
            $table .= "s";
        }
        return $table;
      }
    }
    
    /**
     * Create a new object of this models type
     *
     * @return Object
     */
    public static function create($vals) {
      $obj = new Model(get_class($this)); 
      foreach($vals as $field => $value)
        $obj->field = $value;
      return $obj;
    }

    /**
     * Get the object manager for performing/generated
     * queries
     *
     * @return Manager
     */
    public static function objects() {
      /**
       * EXPERIMENTAL:
       * Create shortcut functions for all of the fields (and table relational fields) and possible
       * query types so that, for example
       * Field::some_field_name__contains($some_value)
       * could be written as
       * some_field_name__contains($some_value)
       */
      $database = static::get_db();
      $operators = $database->get_operators();
      $math_operators = $database->get_math_operators();
      $functions = array();

      foreach(static::fields() as $field => $opts) {
        $type = @$opts["type"];

        if ($type instanceof \phrames\model\ForeignKey) {
          // build list of foreign key connects (double-double underscore searches, like
          // Field::some_foreign_key__ext_field__exact($some_val)
          $to = $type->get_connects_to();
          foreach($to::get_fields() as $fk_field => $fk_opts) {
            $fk_type = @$fk_opts["type"];
            if (!($fk_type instanceof \phrames\model\ManyToManyField || $fk_type instanceof \phrames\model\OneToManyField)) 
              foreach($operators as $op)
                $functions[] = strtolower("{$field}__{$fk_field}__{$op}");
          }
        } else {
          // build basic list of functions by this Model's direct fields
          // (i.e. non-ForeignKeys, etc)
          foreach($operators as $op)
            $functions[] = strtolower("{$field}__{$op}");
          foreach($math_operators as $op)
            $functions[] = strtolower("{$field}__{$op}");
        }
      }

      foreach($functions as $func) {
        if (!function_exists($func)) {
          eval("
              function {$func}(\$v) {
                return \\phrames\\query\\Field::{$func}(\$v);
              }
          ");
        }
      }

      return new Manager(get_called_class());
    }

    /**
     * Using the defined $fields property, determine and
     * return the name of the ID field for this table/model.
     * Defaults to "id" if none can be found
     *
     * @return string
     */
    public static function get_id_field() {
      foreach(static::fields() as $field => $options)
        if (strtoupper(@$options["type"] == "ID"))
          return $field;
      // no manual id specified, assume "id"
      return "id";
    }

    /**
     * Returns an array of all defined fields
     *
     * @return array
     */
    public static function get_fields() {
      return static::fields();
    }

    /**
     * Returns an array of database query joins needed
     * to perform expanded queries
     *
     * Array of the form: array(
     *   "fieldname" => "Class_to_be_joined" 
     * )
     *
     * @return array
     */
    public static function get_joins() {
      $joins = array();
      foreach(static::get_fields() as $field => $properties) {
        if (isset($properties["type"]) 
          && ($properties["type"] instanceof \phrames\model\ForeignKey
            || $properties["type"] instanceof \phrames\model\OneToOneField))
          $joins[$field] = $properties["type"]->get_connects_to();
      }
      return $joins;
    }

    /**
     * Returns an array of required fields based on a Model $fields definition
     *
     * @return array
     */
    public static function get_required_fields() {
      $required = array();
      foreach(static::get_fields() as $field => $properties)
        if (isset($properties["required"]) && $properties["required"] == true
            // ignore id fields, required or generated by default
            && $field != static::get_id_field() && !(@$properties["type"] instanceof \phrames\model\OneToManyField))
          $required[] = $field;
      return $required;
    }

		/**
		 * Use the database driver to create a statement initializing
		 * the Model's database table
		 *
		 * @return string
		 */
    public static function db_create_table() {
      return $this->get_db()->create_table(get_called_class());
    }

    /**
     * Returns the Database object/driver wrapper to be used
     * for this specific Model/Object instance. It can be overloaded
     * to return any set of database connection details (ideally found
     * in Config_phrames. So that, if you desire, you can use different
     * databases/servers to handle different object-types/tables.
     *
     * By default it will search Config_phrames::$dbs and use either
     * the array item with key "primary", or the first one available.
     *
     * @return Database
     */
    public static function get_db() {
      return new Database(Database::get_default_conn_info());
    }

  }

