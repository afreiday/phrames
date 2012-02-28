<?php

  namespace phrames;

  class Config_phrames {

    /**
     * DATABASE
     */
    public static $dbs = array(
      "primary" => array(
        "driver" => "mysql",
        "host" => "localhost",
        "name" => "test",
        "user" => "root",
        "pass" => "",
      ),
      "db_2" => array(
        "driver" => "mysql",
        "host" => "another_server",
        "name" => "another_db",
        "user" => "root",
        "pass" => "",
      )
    );

    public static function get_declared_models() {
      $models = array();
      foreach(get_declared_classes() as $class) {
        $r = new \ReflectionClass($class);
        if ($r->getParentClass() && $r->getParentClass()->name == "phrames\model\Model")
          $models[] = $class;
      }
      return $models;
    }

    public static function db_create_tables() {
      $tables = array();
      foreach(static::get_declared_models() as $class)
        $tables[] = $class::db_create_table();
      return implode("\n\n", $tables);
    }

  }
