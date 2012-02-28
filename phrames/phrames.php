<?php

  spl_autoload_register(function($class) {
    $file = dirname(__FILE__) . "/../" . str_replace("\\", "/", $class) . ".class.php";
    if (file_exists($file))
      require_once($file);
  });

  function _AND_($args) {
    if (!is_array($args))
      $args = func_get_args();

    if (!sizeof($args))
      throw new Exception("_AND_ must contain at least one Expression");
    else
      return new \phrames\query\ExpressionAnd($args);
  }

  function _OR_($args) {
    if (!is_array($args))
      $args = func_get_args();

    if (!sizeof($args))
      throw new Exception("_OR_ must contain at least one Expression");
    else
      return new ExpressionOr($args);
  }

  // ModelField is never directly called from outside that file, include it manually
  require_once("model/ModelField.class.php");
  require_once("query/ExpressionNode.class.php");
