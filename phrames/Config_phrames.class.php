<?php

  namespace phrames;

  class Config_phrames {

    /**
     * DATABASE
     */
    const DB_DRIVER = "mysql";
    const DB_HOST = "localhost";
    const DB_NAME = "test";
    const DB_USER = "root";
    const DB_PASS = "";

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
