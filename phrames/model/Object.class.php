<?php

  require_once("Model.class.php");

  abstract class Object {

    /**
     * Database column values for this row/object
     *
     * @var array
     */
    private $field_values = array();

    /**
     * List of fields that have been modified for next save()
     *
     * @var array
     */
    private $modified_fields = array();

    /**
     * Creates a new Object
     *
     * @param string $model Type of object using Model class name
     * @param int $id Optional id of the row
     */
    public function __construct($id = null) {
      if ($id) $this->field_values[static::get_id_field()] = $id;
      foreach(static::get_fields() as $field => $opts) {
        if (isset($opts["default"])) {
          $default = $opts["default"];
          if (is_callable($default))
            $default = $default();
          $this->$field = $default;
        }
      }
    }

    /**
     * Return this Object as a string using its class name
     * 
     * @return string
     */
    public function __toString() {
      return get_called_class();
    }

    /**
     * GET/SET
     */

    /**
     * Get a single field value
     *
     * @param string $field
     * @return string
     */
    public function get($field) {
      if (!isset($this->field_values[$field]))
        $this->load();
      
      $fields = static::get_fields();
      $type = @$fields[$field]["type"];

      if ($type instanceof OneToOneField || $type instanceof ForeignKey) {
        // one-to-one field
        $class = $type->get_connects_to();
        $id = @$this->field_values[$field];
        if ($id)
          return $class::objects()->get(
              forward_static_call(array("Field", "{$class::get_id_field()}__exact"), $id)
              );
        else
          return new $class();
      } else {
        if ($type instanceof OneToManyField) {
          $return = $type->get_connects_to();
          if (class_exists($return) && is_subclass_of($return, "Model")) {
            // figure out which field from model $type[1] relates to this->model
            $through = get_class($this);
            foreach($return::get_fields() as $field => $opts) {
              $type = $opts["type"];
              if (($type instanceof OneToOneField || $type instanceof ForeignKey)
                  && $type->get_connects_to() == $through)
                return $return::objects()->filter(
                    forward_static_call(array("Field", "{$field}__exact"), $this->id)
                    );
            }
            throw new Exception("Could not find relational field in {$through} referencing {$return}.");
          } else {
            throw new Exception("Invalid qualifier: {$type[1]} not a valid Model class.");
          }
        } elseif ($type instanceof ManyToManyField) {
          $find = $type->get_connects_to();
          $through = $type->get_through();

          $find_field = $through_field = null;

          foreach($through::get_fields() as $field => $opts) {
            if (@$opts["type"] instanceof OneToOneField || @$opts["type"] instanceof ForeignKey) {
              if ($opts["type"]->get_connects_to() == $find)
                $find_field = $field;
              elseif ($opts["type"]->get_connects_to() == get_class($this))
                $through_field = $field;
            }
          }
          
          if (!$find_field)
            throw new Exception("Could not find matching {$find} reference in {$through}.");
          elseif (!$through_field)
            throw new Exception("Could not find matching " . get_class($this) . " reference in {$through}.");
          else {
            $search_ids = $through::objects()->filter(forward_static_call(array("Field", "{$through_field}__exact"), $this->id()))->value_list($find_field);
            return $find::objects()->filter(
                  forward_static_call(array("Field", "{$find::get_id_field()}__in"), $search_ids));
          }
        } else {
          $v = @$this->field_values[$field];

          // specially process the val before returning based on ModelField type
          if ($type instanceof ModelField)
            $v = $type->get($v);

          if (isset($fields[$field]["__get"]) && is_callable($fields[$field]["__get"]))
            return $fields[$field]["__get"]($v);
          else
            return $v;
        }
      }
    }

    /**
     * get() magic method shortcut
     * 
     * @param string $field
     * @return string
     */
    public function __get($field) {
      return $this->get($field);
    }

    /**
     * Set a single field value
     *
     * @param string $field
     * @param string $value
     * @return Object
     */
    public function set($field, $value) {
      // can't change id field
      $model = get_class($this);;
      $fields = $model::get_fields();
      $field_type = isset($fields[$field]["type"]) ? $fields[$field]["type"] : null;

      if ($field == static::get_id_field() || $field_type instanceof IDField) {
        throw new Exception("Cannot change ID field.");
      } elseif ($field_type instanceof OneToOneField || $field_type instanceof ForeignKey) {
        // trying to update a onetoone field reference
        if (is_int($value) || $value === null) {
          // do nothing... left this here purely for aesthetic/logical purposes
          $value = $value;
        } elseif (get_class($value) == $field_type->get_connects_to()) {
          $connects_to = $field_type->get_connects_to();
          $value = $value->id();
        } else {
          throw new Exception("Invalid value assigned to OneToOne/ForeignKey field type. Value assigned to " .
              "field '{$field}' must be of type '{$field_type->get_connects_to()}' or integer.");
        }
      } else {
        if (isset($fields[$field]["__set"]) && is_callable($fields[$field]["__set"]))
          $value = $fields[$field]["__set"]($value);

        if ($field_type instanceof ModelField)
          $value = $field_type->set($value);
      }

      $this->field_values[$field] = $value;
      if (!isset($this->modified_fields[$field]))
        $this->modified_fields[] = $field;

      return $this;
    }

    /**
     * set() magic method shortcut
     * 
     * @param string $field
     * @param string $value
     * @return Object
     */
    public function __set($field, $value) {
      return $this->set($field, $value);
    }

    /**
     * Quickly update a set of fields at once by passing along a basic field/value
     * array of fields to update with their new values, i.e.
     * array("some_field" => "new_value", "another_field" => "other value");
     *
     * @return Object
     */
    public function set_fields($fields) {
      foreach($fields as $field => $value)
        $this->$field = $value;
      return $this;
    }

    /**
     * DB OPERATIONS
     */

    /**
     * Return the database table id of this object
     *
     * @return int
     */
    public function id() {
      return @$this->field_values[static::get_id_field()];
    }

    /**
     * Load this objects table data from the database
     *
     * @return Object
     */
    public function load() {
      if ($this->id()) {
        $db = new Database();
        $fields = $db->get_row(static::table_name(), static::get_id_field(), $this->id());
        foreach($fields as $field => $value) 
          if ($field != static::get_id_field() && !in_array($field, $this->modified_fields))
            $this->field_values[$field] = $value;
      }
      return $this;
    }

    /**
     * Save any changes made to this object to the
     * database (either by UPDATE or INSERT)
     *
     * @return Object
     */
    public function save() {
      $db = new Database();
      
      // check required fields
      foreach(static::get_required_fields() as $field) {
        if ($this->$field === null || ($this->$field instanceof Model && !$this->$field->id())) {
          throw new Exception("Field '{$field}' is required by " . get_class($this));
          return $this;
        }
      }

      if ($this->id()) {
        /**
         * UPDATE
         */
        if (sizeof($this->modified_fields)) {
          $data = array();
          foreach($this->modified_fields as $field)
            $data[$field] = $this->field_values[$field];
          $db->update_row(static::table_name(), static::get_id_field(), $this->id(), $data);
        }
      } else {
        /**
         * INSERT
         */
        $data = array();
        foreach($this->modified_fields as $field)
          $data[$field] = $this->field_values[$field];
        $id = $db->insert_row(static::table_name(), static::get_id_field(), $data);
        $this->field_values[static::get_id_field()] = $id;
      }

      // clear modified fields
      $this->modified_fields = array();

      return $this;
    }

    /**
     * Delete this object from the database
     *
     * @return null
     */
    public function delete() {
      if ($this->id()) {
        // first check if there are any on_delete requirements from ForeignKeys of other models
        foreach(Config_phrames::get_declared_models() as $model) {
          if ($model != get_class($this)) {
            foreach($model::get_fields() as $field => $opts) {
              $type = @$opts["type"];
              if ($type instanceof ForeignKey && $type->get_connects_to() == get_class($this)) {
                /**
                 * ON DELETE FUNCTION CALL
                 */
                if (is_callable($type->get_on_delete())) {
                  $model::objects()->filter(
                      forward_static_call(array("Field", "{$field}__exact"), $this->id()))
                      ->update(array($field => $type->on_delete()));
                /**
                 * ON DELETE PROTECT
                 */
                } elseif ($type->get_on_delete() == ForeignKey::PROTECT) {
                  throw new Exception("Deletion of {$type->get_connects_to()} object protected by {$model} " .
                      "ForeignKey field '{$field}'");
                /**
                 * ON DELETE SET DEFAULT
                 */
                } elseif ($type->get_on_delete() == ForeignKey::SET_DEFAULT) {
                  $default = isset($opts["default"]) ? $opts["default"] : null;
                  $model::objects()->filter(
                      forward_static_call(array("Field", "{$field}__exact"), $this->id()))
                      ->update(array($field => $default));
                /**
                 * ON DELETE SET NULL
                 * theoretically this sould be handled by the database itself,
                 * but just in case let's execute a null update anyways
                 */
                } elseif ($type->get_on_delete() == ForeignKey::SET_NULL) {
                  $model::objects()->filter(
                      forward_static_call(array("Field", "{$field}__exact"), $this->id()))
                      ->update(array($field => null));
                }
              }
            }
          }
        }

        $db = new Database();
        $db->delete_row(static::table_name(), static::get_id_field(), $this->id());
      }
      // clear fields and return null
      $this->field_values = $this->modified_fields = array();
      return null;
    }

  }
