<?php

  namespace phrames\db;

  class Database {

    private $driver = null;

    private $conn_info = array();

    public function __construct($conn_info = array()) {
      if (!sizeof($conn_info))
        $conn_info = static::get_default_conn_info();

      $this->conn_info = $conn_info;
      $driver = $this->get_driver_name();
      $this->driver = new $driver($conn_info);
    }

    public static function get_default_conn_info() {
      $dbs = \phrames\Config_phrames::$dbs;
      if (!sizeof($dbs)) {
        throw new Exception("No database profiles found.");
        return array();
      } else {
        return isset($dbs["primary"]) ? $dbs["primary"] : $dbs[0];
      }
    }

    public function get_conn_info() {
      return $this->conn_info;
    }

    public function get_driver_name() {
      return "\\phrames\\db\\drivers\\DB_" . strtoupper($this->conn_info["driver"]);
    }

    public function get_operators() {
      $driver = $this->get_driver_name();
      return $driver::get_operators();
    }

    public function get_math_operators() {
      $driver = $this->get_driver_name();
      return $driver::get_math_operators();
    }

    public function get_row($table, $id_field, $id) {
      return $this->driver->get_row($table, $id_field, $id);
    }

    public function update_row($table, $id_field, $id, $data) {
      return $this->driver->update_row($table, $id_field, $id, $data);
    }

    public function insert_row($table, $id_field, $data) {
      return $this->driver->insert_row($table, $id_field, $data);
    }

    public function delete_row($table, $id_field, $id) {
      return $this->driver->delete_row($table, $id_field, $id);
    }

    public function create_where($query) {
      return $this->driver->create_where($query);
    }

    public function get_keys($query) {
      return $this->driver->get_keys($query);
    }

    public function get_query_count($query) {
      return $this->driver->get_query_count($query);
    }

    public function create_table($model) {
      return $this->driver->create_table($model);
    }

  }
