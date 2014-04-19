<?php

  namespace phrames\models\query;

  abstract class Queryable implements \Countable {

    private $order = [];

    public function get($q) {
      // TODO
    }

    public function filter($q) {
      $this->try_further_refinement("filter");
      return new QueryFilter($this, $q);
    }

    public function exclude($q) {
      $this->try_further_refinement("exclude");
      return new QueryExclude($this, $q);
    }

    public function order_by($args) {
      $clone = clone $this;

      $args = func_get_args();
      if (sizeof($args) == 1 && is_array($args[0])) {
        foreach($args[0] as $order_by_field) {
          $this->order_by($order_by_field);
        }
      } else {
        foreach($args as $arg) {
          if (is_string($arg)) {
            $clone->order[] = $arg;
          } else {
            throw new \InvalidArgumentException(sprintf(
              "Invalid order_by() argument %s, must be string containing the name of a field/table column",
              $arg));
          }
        }
      }

      return $clone;
    }

    public function reverse() {
      $clone = clone $this;

      if (sizeof($this->order) > 0) {
        $existing_order = $clone->order;
        $clone->order = [];
        
        foreach($existing_order as $order) {
          if ($order[0] === '-') {
            $order = substr($order, 1);
          } else {
            $order = '-' . $order;
          }
          $clone->order[] = $order;
        }
      }

      return $clone;
    }

    private function try_further_refinement($method) {
      if (sizeof($this->order) > 0) {
        throw new \BadMethodCallException(sprintf(
          "Cannot perform %s on query when it has already been ordered",
          $method));
      }
    }

    public function values($fields = []) {
    }

    public function values_list($fields = []) {
    }

    public function none() {
      return new EmptyQuery($this);
    }

    public function select_related() {
    }

  }

