<?php

  interface DB_Driver {

    public static function operators();

    public static function math_operators();

    public function get_row($table, $id_field, $id);

    public function insert_row($table, $id_field, $data);

    public function update_row($table, $id_field, $id, $data);

    public function delete_row($table, $id_field, $id);

    public function get_keys(QuerySet $query);

    public function get_query_count(QuerySet $query);

    public function create_table($model);

  }
