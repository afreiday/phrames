<?php

  namespace phrames\models\manager;

  abstract class Queryable {

    public function all() {
    }

    public function get($q = []) {
    }

    public function filter($q = []) {
    }

    public function exclude($q = []) {
    }

    public function delete() {
    }

    public function order_by($order) {
      // TODO: Allow $orderby to be any format (single, array, args)
    }

  }

