<?php

  spl_autoload_register(function($class) {
    $file = dirname(__FILE__) . "/../" . str_replace("\\", "/", $class) . ".class.php";
    if (file_exists($file))
      require_once($file);
  });

  // ModelField is never directly called from outside that file, include it manually
  require_once("model/ModelField.class.php");
