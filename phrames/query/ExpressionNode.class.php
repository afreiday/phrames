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

  class ExpressionOr extends ExpressionNode { }

