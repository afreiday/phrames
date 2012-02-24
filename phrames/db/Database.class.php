<?php

  namespace phrames\db;

  class Database {

    private static $driver;

    public function __construct() {
      if (!(self::$driver instanceof \phrames\db\drivers\DB_Driver)) {
        $driver = self::get_driver();
        self::$driver = new $driver;
      }
    }

    public static function get_driver() {
      $driver = "\\phrames\\db\\drivers\\DB_" . strtoupper(\phrames\Config_phrames::DB_DRIVER);
      return $driver;
    }

    public static function get_operators() {
      $driver = self::get_driver();
      return $driver::get_operators();
    }

    public static function get_math_operators() {
      $driver = self::get_driver();
      return $driver::get_math_operators();
    }

    public function get_row($table, $id_field, $id) {
      return self::$driver->get_row($table, $id_field, $id);
    }

    public function update_row($table, $id_field, $id, $data) {
      return self::$driver->update_row($table, $id_field, $id, $data);
    }

    public function insert_row($table, $id_field, $data) {
      return self::$driver->insert_row($table, $id_field, $data);
    }

    public function delete_row($table, $id_field, $id) {
      return self::$driver->delete_row($table, $id_field, $id);
    }

    public function create_where($query) {
      return self::$driver->create_where($query);
    }

    public function get_keys($query) {
      return self::$driver->get_keys($query);
    }

    public function get_query_count($query) {
      return self::$driver->get_query_count($query);
    }

    public function create_table($model) {
      return self::$driver->create_table($model);
    }

  }
