<?php

  define('BASEPATH', '/home/andrew/Projects/phrames/');

  function __autoload($class) {
    $space = explode("\\", $class);
    $space[0] = "lib"; // rid 'phrames' namespace prefix
    $class = array_pop($space);
    $class_file = BASEPATH . implode(DIRECTORY_SEPARATOR, $space) . DIRECTORY_SEPARATOR . "{$class}.class.php";
    if (file_exists($class_file)) {
      require_once($class_file);
    }
  }

  spl_autoload_register('__autoload', false);

