<?php

  namespace phrames\model;

  /**
   * Basic definition for a database Model field
   */
  abstract class ModelField {

    /**
     * This format properly formats/modifies the assigned value
     * from the database before it is retrieved by an active object
     *
     * i.e. the get() function is called whenever a value is requested
     * from an object's field whose type is a ModelField object:
     * print $some_object->some_field;
     * ^^ get() would be called just before the value is returned
     *
     * @param mixed $v
     * @return mixed
     */
    public function get($v) {
      return $v;
    }

    /**
     * Properly format an assigned value before it can be saved to the database
     * i.e. $some_object->some_field = $some_new_value;
     * ^^ set() would be called on $some_new_value before it is stored in the
     * objects' modified fields list for storage in the database
     *
     * @param mixed $v
     * @return mixed
     */
    public function set($v) {
      return $v;
    }

  }

  class IDField extends ModelField {

  }

  class BooleanField extends ModelField {

    function get($v) {
      return (boolean) $v;
    }

    function set($v) {
      return $this->get($v);
    }
    
  }

  class CharField extends ModelField {

    private $length = null;
 
    function __construct($length) {
      $this->length = $length;
    }

    function get($v) {
      if ($this->length)
        return substr($v, 0, $this->length);
      else
        return $v;
    }

    function set($v) {
      return $this->get($v);
    }

    public function get_length() {
      return $this->length;
    }

  }

  class TextField extends ModelField {
  }

  class DateField extends ModelField {

    private $auto_now = false;

    function __construct($auto_now = false) {
      $this->auto_now = $auto_now;
    }

  }

  class DateTimeField extends DateField {
  }

  class TimeField extends DateField {
  }

  class FloatField extends ModelField {

    public function get($v) {
      return (float) $v;
    }

    public function set($v) {
      return $this->get($v);
    }

  }

  class DecimalField extends FloatField {

    private $decimal_places = null;

    public function  __construct($decimal_places = null) {
      $this->decimal_places = (int) $decimal_places;
    }
    
    public function get($v) {
      $v = parent::get($v);
      if ($this->decimal_places)
        $v = number_format($v, $this->decimal_places);
      return $v;
    }

  }

  class IntegerField extends ModelField {
    
    public function get($v) {
      return (int) $v;
    }

    public function set($v) {
      return $this->get($v);
    }

  }

  class JSONField extends TextField {
    
    public function get($v) {
      return json_decode($v, true);
    }

    public function set($v) {
      return json_encode($v);
    }

  }

  abstract class ExternalField extends ModelField {

    protected $connects_to;

    function __construct($connects_to) {
      $this->connects_to = $connects_to;
    }

    function get_connects_to() {
      return $this->connects_to;
    }

  }

  abstract class ComplexExternalField extends ExternalField {

    protected $through;

    function __construct($connects_to, $through) {
      $this->through = $through;
      parent::__construct($connects_to);
    }

    function get_through() {
      return $this->through;
    }

  }

  class OneToOneField extends ExternalField {
  } 

  class ForeignKey extends ExternalField {

    const CASCADE = 0; // default
    const PROTECT = 1; // throw exception and disallow deletion
    const SET_NULL = 2; // will throw exception if field is set as required
    const SET_DEFAULT = 3; // update using field "default" value

    private $on_delete = null;

    public function __construct($connects_to, $on_delete = ForeignKey::CASCADE) {
      if (!is_callable($on_delete) && !in_array($on_delete,
            array(static::CASCADE, static::PROTECT, static::SET_NULL, static::SET_DEFAULT))) {
          throw new Exception("Invalid ForeignKey on_delete option. Must either be callable closure " .
            "or one constant of CASCADE, PROTECT, SET_NULL, SET_DEFAULT");
      } else {
        parent::__construct($connects_to);
        $this->on_delete = $on_delete;
      }
    }

    /**
     * Return the current on_delete value for this object
     * 
     * @return mixed (either a closure or an int constant value)
     */
    public function get_on_delete() {
      return $this->on_delete;
    }

    /**
     * Calls and returns the on_delete property value if it is a closure
     * or false if it is not
     *
     * @return mixed
     */
    public function on_delete() {
      if (is_callable($this->on_delete)) {
        $func = $this->on_delete;
        return $func();
      } else {
        return false;
      }
    }

  }

  class OneToManyField extends ExternalField {
  }

  class ManyToManyField extends ComplexExternalField {
  }

