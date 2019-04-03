<?php

function loadClass($className) {
  $possible = [];
  if (substr($className, 0, 3) == 'Api') {
    $possible[] = 'class/api/' . substr($className, 3) . '.class.php';
  } else if (substr($className, 0, 2) == 'Db') {
    $possible[] = 'class/db/' . substr($className, 2) . '.class.php';
  }
  $possible[] = 'class/mock/' . $className . '.class.php';
  foreach ($possible as &$path) {
    if (file_exists($path)) {
      require_once $path;
    }
  }
  if (!class_exists($className)) {
    throw new Exception('Class not found: ' . $className);
  }
}
spl_autoload_register('loadClass');

require_once 'class/Api.class.php';

try {
  require_once 'config.php';

  $a = isset($_GET['a']) && $_GET['a'] != '' ? $_GET['a'] : 'Index';
  if (substr($a, 0, 1) == '/') {
    $a = substr($a, 1);
  }
  $a = explode('/', $a, 2);
  $api = 'Api'.$a[0];
  $args = isset($a[1]) ? (String) $a[1] : '';
  unset($a);
} catch (Throwable $e) {
  new ApiError(DEBUG_MODE === true ? $e->__toString() : $e->getMessage());
  exit;
}

try {
  loadClass($api);
} catch (Exception $e) {
  new ApiNotFound();
  exit;
}

try {
  new $api;
} catch (Throwable $e) {
  new ApiError(DEBUG_MODE === true ? $e->__toString() : $e->getMessage());
}