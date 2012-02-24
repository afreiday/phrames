<?php

  namespace phrames\query;

  abstract class ExpressionNode extends Negatable {

    /**
     * Stores this nodes Expression and ExpressionNode arguments
     * to be tied together at time of parsing
     *
     * @var array
     */
    private $expressions = array();

    /**
     * Construct a new ExpressionNode object
     *
     * @param Expression $expression
     */
    public function __construct($expressions) {
      $this->expressions = $expressions;
    }

    /**
     * Return a list of all of this ExpressionNodes arguments
     *
     * @return array
     */
    public function get_expressions() {
      return $this->expressions;
    }

  }

  class ExpressionAnd extends ExpressionNode { }

  function _AND_($args) {
    if (!is_array($args))
      $args = func_get_args();

    if (!sizeof($args))
      throw new Exception("_AND_ must contain at least one Expression");
    else
      return new ExpressionAnd($args);
  }

  class ExpressionOr extends ExpressionNode { }

  function _OR_($args) {
    if (!is_array($args))
      $args = func_get_args();

    if (!sizeof($args))
      throw new Exception("_OR_ must contain at least one Expression");
    else
      return new ExpressionOr($args);
  }
