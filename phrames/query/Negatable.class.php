<?php

  namespace phrames\query;

  /**
   * Defines an object that can be negated, for example an expression
   * or expression node, such as 'NOT field < 123', and stores its
   * state (negated or not)
   */
  abstract class Negatable {

    /**
     * Current state
     *
     * @var bool
     */
    private $is_not = false;

    /**
     * Invert the state of this object
     *
     * @return Negatable
     */
    public function set_not() {
      $this->is_not = ($this->is_not ? false : true);
      return $this;
    }

    /**
     * Returns the state of this Negatable
     *
     * @return bool
     */
    public function is_not() {
      return $this->is_not;
    }
    
  }

  function _NOT_(Negatable $n) {
    $n->set_not();
    return $n;
  }
