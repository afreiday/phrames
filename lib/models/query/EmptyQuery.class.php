<?php

  namespace phrames\models\query;

  class EmptyQuery extends Query {

    public function values($fields = []) {
      return [];
    }

    public function values_list($fields = []) {
      return [];
    }

    public function filter($q) {
      return $this;
    }

    public function exclude($q) {
      return $this;
    }

    /**
     * ARRAYACCESS
     */

    public function offsetExists($offset) {
      return false;
    }

    public function offsetGet($offset) {
      return $this;
    }

    public function count_using_offset() {
      return 0;
    }

    /**
     * COUNTABLE
     */

    public function count() {
      return 0;
    }

  }
